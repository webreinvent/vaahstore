<?php namespace VaahCms\Modules\Store\Models;

use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\User as UserBase;
use WebReinvent\VaahExtend\Facades\VaahCountry;

class User extends UserBase
{

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

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class,
            'vh_st_user_customer_groups', 'vh_st_user_id','vh_st_customer_group_id'
        );
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
        if (!\Auth::user()->hasPermission('can-create-users')) {
            $response['success'] = false;
            $response['errors'][] = trans('vaahcms-general.permission_denied');

            return $response;
        }

        $inputs = $request->all();

        $validate = self::validation($inputs);

        if (isset($validate['success']) && !$validate['success']) {
            return $validate;
        }

        $rules = array(
            'password' => 'required',
        );

        $messages = array(
            'password.required' => trans('vaahcms-validation.the').' :attribute '.trans('vaahcms-validation.field_is_required'),
        );

        $validator = \Validator::make( $inputs, $rules, $messages);

        if ( $validator->fails() ) {

            $errors             = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        // check if already exist
        $user = self::withTrashed()->where('email',$inputs['email'])->first();

        if ($user) {
            $error_message = trans('vaahcms-user.email_already_registered').($user->deleted_at?' and exists in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if username already exist
        $user = self::withTrashed()->where('username',$inputs['username'])->first();

        if ($user) {
            $error_message = trans('vaahcms-user.username_already_registered').($user->deleted_at?' and exists in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        if (!isset($inputs['username'])) {
            $inputs['username'] = Str::slug($inputs['email']);
        }

        if ($inputs['is_active'] === '1' || $inputs['is_active'] === 1 ) {
            $inputs['is_active'] = 1;
        } else {
            $inputs['is_active'] = 0;
        }

        $inputs['created_ip'] = request()->ip();

        $reg = new static();
        $reg->fill($inputs);
        $reg->save();

        Role::syncRolesWithUsers();
        $registered_role = Role::where('slug', 'customer')->first();
        $registered_role?->users()->updateExistingPivot($reg, ['is_active' => 1]);

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
                $items_id = self::whereHas('activeRoles', function ($query) {
                    $query->where('slug', 'customer');
                })->withTrashed()->get();
                $items_id->each(function ($item_id) {
                    $item_id->customerGroups()->detach();
                });
                $list->whereIn('id', $items_id->pluck('id'))->forceDelete();
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

        $item->roles()->detach();
        $item->forceDelete();

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans('vaahcms-general.record_has_been_deleted');

        return $response;
    }


}
