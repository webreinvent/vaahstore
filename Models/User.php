<?php namespace VaahCms\Modules\Store\Models;

use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\User as UserBase;
use WebReinvent\VaahExtend\Facades\VaahCountry;

class User extends UserBase
{
    use HasApiTokens, Notifiable;
    public static function getUnFillableColumns()
    {
        return [
            'uuid',
            'created_by',
            'updated_by',
            'deleted_by',
        ];
    }
    //----------------------------------------------------------

    protected $appends = [
        'cart_uuid','cart_products_count',
    ];
    //----------------------------------------------------------

    protected $hidden = [
        'cart','password'
    ];
    //----------------------------------------------------------

    public function getCartUuidAttribute()
    {
        return $this->cart?->uuid ?? null;
    }
    //----------------------------------------------------------
    public function getCartProductsCountAttribute()
    {
        return $this->cart?->cart_products_count ?? 0;
    }
    //----------------------------------------------------------

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class,
            'vh_st_user_customer_groups', 'vh_st_user_id','vh_st_customer_group_id'
        );
    }

    //----------------------------------------------------------
    public function addresses()
    {
        return $this->hasMany(Address::class,
            'vh_user_id','id'
        );
    }
    //----------------------------------------------------------

    public function wishlists()
    {
        return $this->belongsToMany(Wishlist::class, 'vh_st_user_wishlists', 'vh_user_id', 'vh_st_wishlist_id');
    }

    //----------------------------------------------------------
    public function cart()
    {
        return $this->hasOne(Cart::class, 'vh_user_id', 'id');
    }
    //----------------------------------------------------------
    public function scopeCustomerGroupFilter($query, $filter)
    {

        if(!isset($filter['customer_group']))
        {
            return $query;
        }
        $search = $filter['customer_group'];
        $query->whereHas('customerGroups',function ($q) use ($search) {
            $q->whereIn('slug',$search);
        });

    }
    //----------------------------------------------------------


    public function scopeDateRangeFilter($query, $filter)
    {

        if(!isset($filter['date'])
            || is_null($filter['date'])
        )
        {
            return $query;
        }

        $dates = $filter['date'];
        $from = \Carbon::parse($dates[0])
            ->startOfDay()
            ->toDateTimeString();

        $to = \Carbon::parse($dates[1])
            ->endOfDay()
            ->toDateTimeString();

        return $query->whereBetween('created_at', [$from, $to]);

    }

    //----------------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if(!isset($filter['q']))
        {
            return $query;
        }
        $keywords = explode(' ',$filter['q']);
        foreach($keywords as $search) {
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('id', 'LIKE', '%' . $search . '%');
            });
        }

    }
    //----------------------------------------------------------

    public static function getList($request,$excluded_columns = [])
    {
        if (isset($request['recount']) && $request['recount'] == true) {
            Role::syncRolesWithUsers();
        }

        $list = self::getSorted($request->filter);
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->customerGroupFilter($request->filter);
        $list->dateRangeFilter($request->filter);

        if (isset($request['from']) && isset($request['to'])) {
            $list->betweenDates($request['from'],$request['to']);
        }

        $rows = config('vaahcms.per_page');
        $list->with('customerGroups');

        if ($request->has('rows')) {
            $rows = $request->rows;
        }

        $list->withCount(['activeRoles']);

        $list->whereHas('activeRoles', function ($query) {
            $query->where('slug', 'customer');
        });

        $list = $list->paginate($rows);
        $countRole = Role::all()->count();

        $response['success'] = true;
        $response['data']['totalRole'] = $countRole;
        $response['data'] = $list;

        return $response;
    }

    //----------------------------------------------------------

    public static function createItem($request)
    {


        $inputs = $request->all();
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }


        // check if already exist
        $user = self::withTrashed()->where('email',$inputs['email'])->first();

        if ($user) {
            $error_message = trans('vaahcms-user.email_already_registered').($user->deleted_at?' and exists in trash.':'.');
            $response['success'] = false;
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if username already exist
        $user = self::withTrashed()->where('username',$inputs['username'])->first();

        if ($user) {
            $error_message = trans('vaahcms-user.username_already_registered').($user->deleted_at?' and exists in trash.':'.');
            $response['success'] = false;
            $response['errors'][] = $error_message;
            return $response;
        }

        if (!isset($inputs['username'])) {
            $inputs['username'] = Str::slug($inputs['email']);
        }

        $inputs['is_active'] = isset($inputs['is_active']) && $inputs['is_active'] == 0 ? 0 : 1;


        $inputs['created_ip'] = request()->ip();

        $reg = new static();
        $reg->fill($inputs);
        $reg->save();

        Role::syncRolesWithUsers();
        $registered_role = Role::where('slug', 'customer')->first();
        $registered_role?->users()->updateExistingPivot($reg, ['is_active' => 1]);
        $wishlist_names = [
            ['name' => 'Save For Later', 'slug' => 'save-for-later'],
            ['name' => 'My List', 'slug' => 'my-list']
        ];

        foreach ($wishlist_names as $wishlist_data) {
            // Check if the wishlist already exists
            $wishlist = Wishlist::firstOrCreate([
                'name' => $wishlist_data['name'],
                'slug' => $wishlist_data['slug']
            ]);

            // Attach the wishlist to the user
            $reg->wishlists()->attach($wishlist);
        }
        $response['success'] = true;
        $response['data']['item'] = $reg;
        $response['messages'][] = trans('vaahcms-general.saved_successfully');
        return $response;

    }
    //----------------------------------------------------------

    public static function searchCustomerGroups($request)
    {
        $query = $request->input('query');
        if($query === null)
        {
            $customer_group_name = CustomerGroup::select('id','name','slug')
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        else{

            $customer_group_name = CustomerGroup::where('name', 'like', "%$query%")
                ->orWhere('slug','like',"%$query%")
                ->select('id','name','slug')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $customer_group_name;
        return $response;

    }

    //----------------------------------------------------------

    public static function getCustomerGroupsBySlug($request)
    {
        $query = $request['filter']['customer_group'];

        $customer_group = CustomerGroup::whereIn('name',$query)
            ->orWhereIn('slug',$query)
            ->select('id','name','slug')->get();

        $response['success'] = true;
        $response['data'] = $customer_group;
        return $response;
    }

    //----------------------------------------------------------
    public static function deleteList($request): array
    {
        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
            'items' => 'required',
        );

        $messages = array(
            'type.required' => trans('vaahcms-general.action_type_is_required'),
            'items.required' => trans('vaahcms-general.select_items'),
        );

        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {
            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        $response['errors'] = [];

        foreach($inputs['items'] as $item) {
            $is_restricted = self::restrictedActions('delete', $item['id']);

            if(isset($is_restricted['success']) && !$is_restricted['success'])
            {
                $response['errors'][] = '<b>'.$item['email'].'</b>: '.$is_restricted['errors'][0];
                continue;
            }

            $item = self::query()->where('id', $item['id'])->withTrashed()->first();
            if ($item) {
                if ($item->customerGroups()->exists()) {
                    $item->customerGroups()->detach();
                }

                self::deleteUserWishlistsAndProducts($item);


                $item->roles()->detach();
                $item->forceDelete();
            }
        }

        $response['success'] = true;
        $response['data'] = true;

        if(count($inputs['items']) !== count($response['errors'])){
            $response['messages'][] = trans('vaahcms-general.action_successful');
        }

        return $response;
    }
    //----------------------------------------------------------

    public static function getItem($id,$excluded_columns = [], $type=null)
    {
        $item = self::where('id', $id)->with(['createdByUser',
            'updatedByUser', 'deletedByUser'])
            ->withTrashed();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = trans('vaahcms-general.record_not_found_with_id').': '.$id;
            return $response;
        }
        $item = $item->first();
        $response['success'] = true;
        $response['data'] = $item;
        return $response;

    }
    //----------------------------------------------------------
    public static function listAction($request, $type): array
    {
        $response = [];
        $inputs = $request->all();

        $list = self::getSorted($inputs['query']['filter']);
        $list->isActiveFilter($inputs['query']['filter']);
        $list->trashedFilter($inputs['query']['filter']);
        $list->searchFilter($inputs['query']['filter']);

        if (isset($request['from']) && isset($request['to'])) {
            $list->betweenDates($request['from'],$request['to']);
        }

        $list_array = $list->get()->toArray();

        foreach($list_array as $item){
            $is_restricted = self::restrictedActions($type, $item['id']);

            if(isset($is_restricted['success']) && !$is_restricted['success'])
            {
                $response['errors'][] = '<b>'.$item['email'].'</b>: '.$is_restricted['errors'][0];
                $list->where('id','!=',$item['id']);
            }
        }

        switch ($type) {
            case 'activate-all':
                $list->whereHas('activeRoles', function ($query) {
                    $query->where('slug', 'customer');
                })->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                $list->whereHas('activeRoles', function ($query) {
                    $query->where('slug', 'customer');
                })->update(['is_active' => null]);
                break;
            case 'trash-all':
                $list->whereHas('activeRoles', function ($query) {
                    $query->where('slug', 'customer');
                })->delete();
                break;
            case 'restore-all':
                $list->whereHas('activeRoles', function ($query) {
                    $query->where('slug', 'customer');
                })->withTrashed()->restore();
                break;
            case 'delete-all':
                \DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $items = self::whereHas('activeRoles', function ($query) {
                    $query->where('slug', 'customer');
                })->withTrashed()->get();

                foreach($items as $item)
                {
                    self::deleteUserWishlistsAndProducts($item);
                }

                $items->each(function ($item_id) {
                    $item_id->customerGroups()->detach();
                });
                $list->whereIn('id', $items->pluck('id'))->forceDelete();
                \DB::statement('SET FOREIGN_KEY_CHECKS=1');
                break;

            case 'create-100-records':
            case 'create-1000-records':
            case 'create-5000-records':
            case 'create-10000-records':

                if(!config('store.is_dev')){
                    $response['success'] = false;
                    $response['errors'][] = 'User is not in the development environment.';

                    return $response;
                }

                preg_match('/-(.*?)-/', $type, $matches);

                if(count($matches) !== 2){
                    break;
                }

                self::seedSampleItems($matches[1]);
                break;

        }

        $response['success'] = true;
        $response['data'] = true;

        if(!isset($response['errors']) ||
            (count($list_array) !== count($response['errors']))){

            $response['messages'][] = trans('vaahcms-general.action_successful');

        }

        return $response;
    }

    //----------------------------------------------------------
    //----------------------------------------------------------
    public static function seedSampleItems($records=100)
    {

        $i = 0;

        while($i < $records)
        {
            $inputs = self::fillItem(false);

            $item =  new self();
            $item->fill($inputs);
            $item->save();
            Role::syncRolesWithUsers();
            $registered_role = Role::where('slug', 'customer')->first();
            $registered_role->users()->updateExistingPivot($item, ['is_active' => 1]);

            $i++;

        }

    }
    //----------------------------------------------------------
    public static function fillItem($is_response_return = true)
    {
        $request = new Request([
            'model_namespace' => self::class,
            'except' => self::getUnFillableColumns()
        ]);
        $fillable = VaahSeeder::fill($request);
        if(!$fillable['success']){
            return $fillable;
        }

        $country_list = VaahCountry::getList();
        $countries = array_column($country_list, 'name');
        $inputs = $fillable['data']['fill'];
        $random_country = $countries[array_rand($countries)];
        $selected_country = collect($country_list)->where('name', $random_country)->first();

        $inputs['country'] = $random_country;
        $inputs['country_calling_code'] = $selected_country ? $selected_country['calling_code'] : null;
        $inputs['is_active'] = rand(0,1);



        $name_titles = vh_name_titles();
        $random_title = collect($name_titles)->random()['name'];

        $inputs['title'] = $random_title;

        $timezones = vh_get_timezones();
        $random_zone = collect($timezones)->random()['slug'];
        $inputs['timezone'] = $random_zone;
        $inputs['is_active'] = 1;

        $faker = Factory::create();
        $phone_number_length = 10;
        $random_phone_number= $faker->numerify(str_repeat('#', $phone_number_length));
        $inputs['phone'] = $random_phone_number;
        $random_dob = $faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d');
        $inputs['birth'] = $random_dob;
        $inputs['foreign_user_id'] = rand(1,10);
        $status_options = [
            [
                'label' => 'Active',
                'value' => 'active'
            ],
            [
                'label' => 'Inactive',
                'value' => 'inactive'
            ],
            [
                'label' => 'Blocked',
                'value' => 'blocked'
            ],
            [
                'label' => 'Banned',
                'value' => 'banned'
            ],
        ];

        $random_index = array_rand($status_options);
        $random_status = $status_options[$random_index]['value'];

        $inputs['status'] = $random_status;

        /*
         * You can override the filled variables below this line.
         * You should also return relationship from here
         */

        if(!$is_response_return){
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }

    public static function deleteItem($request, $id): array
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans('vaahcms-general.record_does_not_exist');
            return $response;
        }

        if($item->customerGroups)
        {
            $item->customerGroups()->detach();
        }
        $user_wishlists = $item->wishlists()->withTrashed()->get();
        foreach ($user_wishlists as $wishlist) {
            $wishlist->users()->detach($id);

            $user_wishlist = UserWishlist::where([
                'vh_user_id' => $id,
                'vh_st_wishlist_id' => $wishlist->id,
            ])->first();

            if ($user_wishlist) {
                $user_wishlist->products()->detach();
                $user_wishlist->forceDelete();
            }

            // If no more users linked to this wishlist, delete the wishlist
            $remaining_user_count = UserWishlist::where('vh_st_wishlist_id', $wishlist->id)->count();
            if ($remaining_user_count === 0 && !Wishlist::isReservedNameOrSlug($wishlist->name)) {
                $wishlist->forceDelete();
            }
        }

        $item->roles()->detach();
        $item->forceDelete();

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans('vaahcms-general.record_has_been_deleted');

        return $response;
    }


    //----------------------------------------------------------





    public static function fetchCustomerCountChartData(Request $request)
    {
        $start_date = isset($request->start_date) ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
        $end_date = isset($request->end_date) ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $selected_store_id = $request->input('selected_store') ??
            Store::where('is_default', 1)->value('id');
        // Get all customers with the role "customer"
        $list = User::whereHas('activeRoles', function ($query) {
            $query->where('slug', 'customer');
        });

        // Get chart data query using Eloquent
        $chart_data_query = $list
            ->whereBetween('created_at', [$start_date, $end_date])
            ->selectRaw("
        DATE(created_at) as date,
        COUNT(*) as joined,
        (
            SELECT COUNT(*) FROM vh_st_orders
            WHERE vh_st_orders.vh_user_id = vh_users.id
            AND vh_st_orders.created_at BETWEEN ? AND ?
            AND vh_st_orders.vh_st_store_id = ?
        ) as customer_order_activity
    ", [$start_date, $end_date, $selected_store_id])
            ->groupByRaw("DATE(created_at)")
            ->orderByRaw("DATE(created_at) ASC")
            ->get();


        // Initialize data array for the chart
        $data = [
            // ['name' => 'Total', 'data' => []], // Commented out the "Total" series
            ['name' => 'Joined', 'data' => []],
            ['name' => 'Order Activity', 'data' => []],
        ];

        $initial_customer_count = User::where('created_at', '<', $start_date)->count();
        $cumulative_count = $initial_customer_count;
        $labels = [];

        foreach ($chart_data_query as $item) {
            $date = Carbon::parse($item->date);
            $labels[] = $date->format('Y-m-d');

            $cumulative_count += $item->joined;

            $data[0]['data'][] = $item->joined;
            $data[1]['data'][] = $item->customer_order_activity;
        }

        // Get total orders using Eloquent
        $total_orders_query = Order::whereBetween('created_at', [$start_date, $end_date])
            ->where('vh_st_store_id', $selected_store_id);

        $total_orders = $total_orders_query->count();

        $unique_customers_with_multiple_orders = (clone $total_orders_query)
            ->distinct('vh_user_id')
            ->count('vh_user_id');
        $total_order_value = (clone $total_orders_query)->sum('payable');

        // Calculate the average orders per customer
        $avg_orders_per_customer = $unique_customers_with_multiple_orders > 0
            ? round($total_orders / $unique_customers_with_multiple_orders, 2)
            : 0;


        // Calculate average order value
        $average_order_value = $total_orders > 0
            ? round($total_order_value / $total_orders, 2)
            : 0;

        // Return the data for the chart
        return [
            'data' => [
                'chart_series' => $data,
                'chart_options' => [
                    'chart' => [
                        'id' => 'dynamic-chart',
                        'toolbar' => ['show' => true],
                        'zoom' => ['enabled' => false],
                    ],
                    'xaxis' => [
                        'categories' => $labels,
                        'labels' => [
                            'show' => false,
                            'style' => [
                                'colors' => '#000',
                                'fontSize' => '12px',
                                'fontFamily' => 'Arial, sans-serif',
                            ],
                        ],
                    ],
                ],
                'summary' => [
                    'total_customers' => $unique_customers_with_multiple_orders,
                    'total_orders' => $total_orders,
                    'average_order_value' => $average_order_value,
                    'avg_orders_per_customer' => $avg_orders_per_customer,
                ],
            ],
        ];
    }




    //----------------------------------------------------------
    public static function deleteUserWishlistsAndProducts($user)
    {
        if (!$user) {
            return;
        }

        $user_wishlists = $user->wishlists()->withTrashed()->get();

        foreach ($user_wishlists as $wishlist) {
            $user_wishlist = UserWishlist::where([
                'vh_user_id' => $user->id,
                'vh_st_wishlist_id' => $wishlist->id,
            ])->first();

            if ($user_wishlist) {
                // Delete pivot data with products
                //  $user_wishlist->products()->detach();
                DB::table('vh_st_user_wishlist_products')
                    ->where('vh_st_user_wishlist_id', $user_wishlist->id)
                    ->delete();

                $user_wishlist->forceDelete();
            }

            // If no more users linked to this wishlist, delete it (if not reserved)
            $remaining = UserWishlist::where('vh_st_wishlist_id', $wishlist->id)->count();
            if ($remaining === 0 && !Wishlist::isReservedNameOrSlug($wishlist->name)) {
                $wishlist->forceDelete();
            }
        }
    }
    //----------------------------------------------------------

    public static function updateAuthUserProfile(Request $request, $id)
    {
        $inputs = $request->only([
            'email', 'first_name', 'last_name','username', 'phone'
        ]);

        $validate = self::profileValidation($inputs);

        if(isset($validate['success']) && !$validate['success'])
        {
            return $validate;
        }

        if(isset($inputs['phone']))
        {
            $rules['phone'] = 'integer';

            $validator = \Validator::make( $request->all(), $rules);
            if ( $validator->fails() ) {

                $errors             = errorsToArray($validator->errors());
                $response['success']  = false;
                $response['errors'] = $errors;
                return $response;
            }
        }
        $item = self::withTrashed()->find($id);
        if (!$item) {
            return [
                'success' => false,
                'errors' => [trans('vaahcms-user.registration_not_found')]
            ];
        }

        if (!empty($inputs['email'])) {
            $existing_user = self::where('id', '!=', $item->id)
                ->where('email', $inputs['email'])
                ->withTrashed()
                ->first();

            if ($existing_user) {
                return [
                    'success' => false,
                    'errors' => [trans('vaahcms-user.email_already_registered')]
                ];
            }
        }
        $inputs = array_filter($inputs, function ($value) {
            return $value !== null && $value !== '';
        });

        $item->fill($inputs);
        $item->save();

        $response['success'] = true;
        $response['messages'][] = trans('vaahcms-general.saved');
        $response['data'] = $item;

        return $response;
    }
    //----------------------------------------------------------

    public static function profileValidation($inputs)
    {
        $rules = array(

            'email' => [
                'nullable',
                'string',
                'email',
                'max:50',
            ],

            'first_name' => [
                'nullable',
                'string',
                'min:1',
                'max:20',
                "regex:/^[A-Za-zÀ-ÿ' -]{1,50}$/",
            ],
            'last_name' => [
                'nullable',
                'string',
                'min:1',
                'max:20',
                "regex:/^[A-Za-zÀ-ÿ' -]{1,50}$/",
            ],
            'phone' => 'nullable|regex:/^\d{10,15}$/',
        );
        $messages = [
            'email.email'          => 'Email must be a valid email address.',
            'email.min'            => 'Email must be at least :min characters.',
            'email.max'            => 'Email may not exceed :max characters.',

            // First Name
            'first_name.min'       => 'First name must be at least :min character.',
            'first_name.max'       => 'First name may not exceed :max characters.',
            'first_name.regex'     => 'First name can only contain letters, spaces, hyphens or apostrophes.',

            // Last Name
            'last_name.min'        => 'Last name must be at least :min character.',
            'last_name.max'        => 'Last name may not exceed :max characters.',
            'last_name.regex'      => 'Last name can only contain letters, spaces, hyphens or apostrophes.',
            // Phone
            'phone.regex'          => 'Phone must be between 10 and 15 digits.',
        ];

        if(isset($inputs['username']))
        {
            $rules['username'] = [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[A-Za-z][A-Za-z0-9._]{2,19}$/'
            ];
        }

        if (array_key_exists('username', $inputs)) {
            $rules['username'] = [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[A-Za-z][A-Za-z0-9._]{2,19}$/',
            ];

            $messages = array_merge($messages, [
                'username.required' => 'Username is required.',
                'username.min'      => 'Username must be at least :min characters.',
                'username.max'      => 'Username may not exceed :max characters.',
                'username.regex'    => 'Username must start with a letter and contain only letters, numbers, dots or underscores.',
            ]);
        }

        $validator = \Validator::make($inputs,$rules,$messages);

        if ( $validator->fails() ) {

            $errors             = errorsToArray($validator->errors());
            $response['success']  = false;
            $response['errors'] = $errors;
            return $response;
        }

    }
    //----------------------------------------------------------

    public static function validation($inputs)
    {
        $rules = validator($inputs, [
            'first_name' => [
                'required',
                'string',
                'min:1',
                'max:20',
                "regex:/^[A-Za-zÀ-ÿ' -]{1,50}$/",
            ],
            'last_name' => [
                'nullable',
                'string',
                'min:1',
                'max:20',
                "regex:/^[A-Za-zÀ-ÿ' -]{1,50}$/",
            ],

            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:20',
                'regex:/^[A-Za-z][A-Za-z0-9._]{2,19}$/'
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:50',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'max:64',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,64}$/'
            ],

            'phone' => 'nullable|regex:/^\d{10,15}$/',
        ],
            [
                // Email
                'email.required'       => 'Email is required.',
                'email.email'          => 'Email must be a valid email address.',
                'email.min'            => 'Email must be at least :min characters.',
                'email.max'            => 'Email may not exceed :max characters.',

                // First Name
                'first_name.required'  => 'First name is required.',
                'first_name.min'       => 'First name must be at least :min character.',
                'first_name.max'       => 'First name may not exceed :max characters.',
                'first_name.regex'     => 'First name can only contain letters, spaces, hyphens or apostrophes.',

                // Last Name
                'last_name.min'        => 'Last name must be at least :min character.',
                'last_name.max'        => 'Last name may not exceed :max characters.',
                'last_name.regex'      => 'Last name can only contain letters, spaces, hyphens or apostrophes.',

                // Username
                'username.min'         => 'Username must be at least :min characters.',
                'username.max'         => 'Username may not exceed :max characters.',
                'username.regex'       => 'Username must start with a letter and contain only letters, numbers, dots or underscores.',

                // Password
                'password.required' => 'The Password field is required.',
                'password.string' => 'The Password must be a string.',
                'password.min' => 'The Password must be at least :min characters.',
                'password.regex' => 'The Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#).',
                'password.confirmed' => 'The Password confirmation does not match.',

                // Phone
                'phone.regex'          => 'Phone must be between 10 and 15 digits.',
            ]
        );

        if($rules->fails()){
            return [
                'success' => false,
                'errors' => $rules->errors()->all()
            ];
        }
        $rules = $rules->validated();

        return [
            'success' => true,
            'data' => $rules
        ];

    }
    //----------------------------------------------------------

}
