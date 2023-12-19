<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use VaahCms\Modules\Store\Models\PaymentMethod;
use Faker\Factory;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class Order extends Model
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_orders';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'vh_user_id',
        'taxonomy_id_order_status',
        'amount',
        'delivery_fee',
        'taxes',
        'discount',
        'payable',
        'paid',
        'is_paid',
        'vh_st_payment_method_id',
        'meta',
        'status_notes',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    //-------------------------------------------------
    protected $fill_except = [

    ];

    //-------------------------------------------------
    protected $appends = [
    ];

    //-------------------------------------------------
    protected function serializeDate(DateTimeInterface $date)
    {
        $date_time_format = config('settings.global.datetime_format');
        return $date->format($date_time_format);
    }

    //-------------------------------------------------
    public static function getUnFillableColumns()
    {
        return [
            'uuid',
            'created_by',
            'updated_by',
            'deleted_by',
        ];
    }
    //-------------------------------------------------

    public static function getFillableColumns()
    {
        $model = new self();
        $except = $model->fill_except;
        $fillable_columns = $model->getFillable();
        $fillable_columns = array_diff(
            $fillable_columns, $except
        );
        return $fillable_columns;
    }

    //-------------------------------------------------
    public static function getEmptyItem()
    {
        $model = new self();
        $fillable = $model->getFillable();
        $empty_item = [];
        foreach ($fillable as $column)
        {
            $empty_item[$column] = null;
        }
        $empty_item['is_active'] = null;
        $empty_item['is_active_order_item'] = null;
        $empty_item['is_paid'] = null;
        $empty_item['paid'] = 0;
        $empty_item['amount'] = 0;
        $empty_item['paid'] = 0;
        $empty_item['delivery_fee'] = 0;
        $empty_item['taxes'] = 0;
        $empty_item['discount'] = 0;
        $empty_item['paid'] = 0;
        $empty_item['payable'] = 0;
        $empty_item['user'] = null;
        $empty_item['types'] = null;
        $empty_item['product'] = null;
        $empty_item['product_variation'] = null;
        $empty_item['vendor'] = null;
        $empty_item['customer_group'] = null;
        $empty_item['status_order_items'] = null;
        $empty_item['status_notes_order_item'] = null;
        $empty_item['taxonomy_id_order_items_types'] = null;
        $empty_item['vh_st_product_id'] = null;
        $empty_item['vh_st_product_variation_id'] = null;
        $empty_item['vh_st_vendor_id'] = null;
        $empty_item['vh_st_customer_group_id'] = null;
        $empty_item['taxonomy_id_order_items_status'] = null;
        $empty_item['is_invoice_available'] = null;
        $empty_item['invoice_url'] = null;
        $empty_item['tracking'] = null;

        return $empty_item;
    }

    //-------------------------------------------------

    public function createdByUser()
    {
        return $this->belongsTo(User::class,
            'created_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function updatedByUser()
    {
        return $this->belongsTo(User::class,
            'updated_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------

    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class,'id','vh_st_payment_method_id')->select('id','name','slug');
    }
    //-------------------------------------------------
    public function items()
    {
        return $this->hasMany(OrderItem::class,'vh_st_order_id','id');
    }

    //-------------------------------------------------

    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_order_status')->select('id','name','slug');
    }

    //-------------------------------------------------
    public function user()
    {
        return $this->hasOne(User::class,'id','vh_user_id')->select('id','first_name', 'email');
    }

    //-------------------------------------------------

    public function deletedByUser()
    {
        return $this->belongsTo(User::class,
            'deleted_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()
            ->getColumnListing($this->getTable());
    }

    //-------------------------------------------------
    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }

    //-------------------------------------------------
    public function scopeBetweenDates($query, $from, $to)
    {

        if ($from) {
            $from = \Carbon::parse($from)
                ->startOfDay()
                ->toDateTimeString();
        }

        if ($to) {
            $to = \Carbon::parse($to)
                ->endOfDay()
                ->toDateTimeString();
        }

        $query->whereBetween('updated_at', [$from, $to]);
    }

    //-------------------------------------------------

    public static function createItem($request)
    {

        $inputs = $request->all();
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        $item = new self();
        $item->fill($inputs);
        $item->is_paid = $inputs['paid'] > 0 ? 1 : 0;
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }

    //-------------------------------------------------

    public static function createOrderItem($request){

        $inputs = $request->all();
        $validation = self::validationOrderItem($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        $order_item = new OrderItem;
        $order_item->fill($inputs);
        $order_item->vh_st_order_id = $inputs['id'];
        $order_item->is_active = $inputs['is_active_order_item'];
        $order_item->status_notes = $inputs['status_notes_order_item'];
        $order_item->save();
        $response['data'] = true;
        $response['messages'][] = 'order placed successfully.';
        return $response;
    }

    //-------------------------------------------------

    public static function searchProduct($request)
    {

        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $products = Product::where('is_active',1)->select('id','name')
                ->inRandomOrder()
                ->take(10)
                ->get();

        }

        else{

            $products = Product::where('is_active',1)
                ->where('name', 'like', "%$query%")
                ->select('id','name')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $products;
        return $response;

    }

    //-------------------------------------------------

    public static function searchUser($request)
    {

        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $users = User::where('is_active',1)
                ->inRandomOrder()
                ->take(10)
                ->get();

        }

        else{

            $users = User::where('is_active',1)
                ->where('first_name','like', "%$query%")
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $users;
        return $response;

    }

    //-------------------------------------------------
    public static function searchProductVariation($request)
    {

        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $product_variations = ProductVariation::where('is_active',1)->select('id','name')
                ->inRandomOrder()
                ->take(10)
                ->get();

        }

        else{

            $product_variations = ProductVariation::where('is_active',1)
                ->where('name', 'like', "%$query%")
                ->select('id','name')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $product_variations;
        return $response;

    }

    //-------------------------------------------------

    public static function searchCustomerGroup($request)
    {

        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $customer_groups = CustomerGroup::select('id','name')
                ->inRandomOrder()
                ->take(10)
                ->get();

        }

        else{

            $customer_groups = CustomerGroup::where('name', 'like', "%$query%")
                ->select('id','name')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $customer_groups;
        return $response;

    }

    //-------------------------------------------------


    public static function searchVendor($request)
    {

        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $vendors = Vendor::where('is_active',1)->select('id','name')
                ->inRandomOrder()
                ->take(10)
                ->get();

        }

        else{

            $vendors = Vendor::where('is_active',1)
                ->where('name', 'like', "%$query%")
                ->select('id','name')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $vendors;
        return $response;

    }

    //-------------------------------------------------

    public function scopeGetSorted($query, $filter)
    {

        if(!isset($filter['sort']))
        {
            return $query->orderBy('id', 'desc');
        }

        $sort = $filter['sort'];


        $direction = Str::contains($sort, ':');

        if(!$direction)
        {
            return $query->orderBy($sort, 'asc');
        }

        $sort = explode(':', $sort);

        return $query->orderBy($sort[0], $sort[1]);
    }
    //-------------------------------------------------
    public function scopeIsActiveFilter($query, $filter)
    {

        if(!isset($filter['is_active'])
            || is_null($filter['is_active'])
            || $filter['is_active'] === 'null'
        )
        {
            return $query;
        }
        $is_active = $filter['is_active'];

        if($is_active === 'true' || $is_active === true)
        {
            return $query->where('is_active', 1);
        } else{
            return $query->where(function ($q){
                $q->whereNull('is_active')
                    ->orWhere('is_active', 0);
            });
        }

    }
    //-------------------------------------------------
    public function scopeTrashedFilter($query, $filter)
    {

        if(!isset($filter['trashed']))
        {
            return $query;
        }
        $trashed = $filter['trashed'];

        if($trashed === 'include')
        {
            return $query->withTrashed();
        } else if($trashed === 'only'){
            return $query->onlyTrashed();
        }

    }
    //-------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if(!isset($filter['q']))
        {
            return $query;
        }
        $search = $filter['q'];
        $query->where(function ($q) use ($search) {
            $q->where('id', 'LIKE', '%' . $search . '%')
                ->orwhereHas('user', function ($query) use ($search) {
                    $query->where('first_name','LIKE', '%'.$search.'%');
                });
        });

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('status','paymentMethod','user','items');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);

        $rows = config('vaahcms.per_page');

        if($request->has('rows'))
        {
            $rows = $request->rows;
        }

        $list = $list->paginate($rows);

        $response['success'] = true;
        $response['data'] = $list;

        return $response;


    }

    //-------------------------------------------------
    public static function updateList($request)
    {

        $inputs = $request->all();
        $rules = array(
            'type' => 'required',
        );

        $messages = array(
            'type.required' => 'Action type is required',
        );


        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        if(isset($inputs['items']))
        {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();
        }


        $items = self::whereIn('id', $items_id)
            ->withTrashed();

        switch ($inputs['type']) {
            case 'deactivate':
                $items->update(['is_active' => null]);
                break;
            case 'activate':
                $items->update(['is_active' => 1]);
                break;
            case 'trash':
                self::whereIn('id', $items_id)->delete();
                $user_id = auth()->user()->id;
                $items->update(['deleted_by' => $user_id]);
                break;
            case 'restore':
                self::whereIn('id', $items_id)->restore();
                $items->update(['deleted_by' => null]);
                break;
        }

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = 'Action was successful.';

        return $response;
    }

    //-------------------------------------------------
    public static function deleteList($request): array
    {
        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
            'items' => 'required',
        );

        $messages = array(
            'type.required' => 'Action type is required',
            'items.required' => 'Select items',
        );

        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        self::whereIn('id', $items_id)->forceDelete();

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = 'Action was successful.';

        return $response;
    }
    //-------------------------------------------------
    public static function listAction($request, $type): array
    {
        $inputs = $request->all();

        if(isset($inputs['items']))
        {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();

            $items = self::whereIn('id', $items_id)
                ->withTrashed();
        }

        $list = self::query();

        if($request->has('filter')){
            $list->getSorted($request->filter);
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
        }

        switch ($type) {
            case 'deactivate':
                if($items->count() > 0) {
                    $items->update(['is_active' => null]);
                }
                break;
            case 'activate':
                if($items->count() > 0) {
                    $items->update(['is_active' => 1]);
                }
                break;
            case 'trash':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->delete();
                    $items->update(['deleted_by' => auth()->user()->id]);
                }

                break;
            case 'restore':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->restore();
                    $items->update(['deleted_by' => null]);
                }
                break;
            case 'delete':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->forceDelete();
                    OrderItem::deleteOrder($items_id);
                }
                break;
            case 'activate-all':
                $list->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                $list->update(['is_active' => null]);
                break;
            case 'trash-all':
                $user_id = auth()->user()->id;
                $list->update(['deleted_by' => $user_id]);
                $list->delete();
                break;
            case 'restore-all':
                $list->update(['deleted_by' => null]);
                $list->restore();
                OrderItem::deleteOrder($items_id);
                break;
            case 'delete-all':
                $list->forceDelete();
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
        $response['messages'][] = 'Action was successful.';

        return $response;
    }
    //-------------------------------------------------
    public static function getItem($id)
    {

        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','status','paymentMethod','user','items'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }
        //To get data for dropdown of order items
        $array_item = $item->toArray();
//        if($item['items']!=null){
//            $item['types'] = Taxonomy::where('id',$array_item['items']['taxonomy_id_order_items_types'])->get()->toArray()[0];
//            $item['product'] = Product::where('id',$array_item['items']['vh_st_product_id'])->get(['id','name','slug','is_default'])->toArray()[0];
//            $item['product_variation'] = ProductVariation::where('id',$array_item['items']['vh_st_product_variation_id'])->get(['id','name','slug','is_default'])->toArray()[0];
//            $item['vendor'] = Vendor::where('id',$array_item['items']['vh_st_vendor_id'])->get(['id','name','slug'])->toArray()[0];
//            $item['customer_group'] = CustomerGroup::where('id',$array_item['items']['vh_st_customer_group_id'])->get(['id','name','slug'])->toArray()[0];
//            $item['status_order_items'] = Taxonomy::where('id',$array_item['items']['taxonomy_id_order_items_status'])->get()->toArray()[0];
//            $item['status_notes_order_item'] = $array_item['items']['status_notes'];
//            $item['is_active_order_item'] = $array_item['items']['is_active'];
//            $item['is_invoice_available'] = $array_item['items']['is_invoice_available'];
//            $item['invoice_url'] = $array_item['items']['invoice_url'];
//            $item['tracking'] = $array_item['items']['tracking'];
//        }
//        else{
//            $item['is_invoice_available'] = 1;
//            $item['is_active_order_item'] = 1;
//        }

        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }

    //-------------------------------------------------
    public static function updateItem($request, $id)
    {

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->is_paid = $inputs['paid'] > 0 ? 1 : 0;
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }

    //-------------------------------------------------

    public static function deleteItem($request, $id)
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['messages'][] = 'Record does not exist.';
            return $response;
        }
        $item->forceDelete();
        OrderItem::deleteOrder($item->id);

    $response['success'] = true;
    $response['data'] = [];
    $response['messages'][] = 'Record has been deleted';

    return $response;
    }

    //-------------------------------------------------

    public static function itemAction($request, $id, $type): array
    {

        switch($type)
        {
            case 'activate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => 1]);
                break;
            case 'deactivate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => null]);
                break;
            case 'trash':
                self::where('id', $id)
                ->withTrashed()
                ->delete();
                $item = self::where('id',$id)->withTrashed()->first();
                $item->deleted_by = auth()->user()->id;
                $item->save();
                break;
            case 'restore':
                self::where('id', $id)
                    ->withTrashed()
                    ->restore();
                $item = self::where('id',$id)->withTrashed()->first();
                $item->deleted_by = null;
                $item->save();
                break;
        }

        return self::getItem($id);
    }
    //-------------------------------------------------

    public static function validation($inputs)
    {

        $rules = validator($inputs, [
            'vh_user_id' => 'required',
            'amount' => 'required|numeric|min:1|regex:/^\d{1,10}(\.\d{1,2})?$/',
            'delivery_fee' => 'required|regex:/^\d{0,10}(\.\d{1,2})?$/',
            'taxes' => 'required|regex:/^\d{0,10}(\.\d{1,2})?$/',
            'discount' => 'required|regex:/^\d{0,10}(\.\d{1,2})?$/',
            'payable' => 'required|numeric|gt:0|regex:/^[+-]?\d{0,10}(\.\d{1,2})?$/',
            'paid' => [
                'required',
                'numeric',
                'min:1',
                'regex:/^\d{1,10}(\.\d{1,2})?$/',
                function ($attribute, $value, $fail) use ($inputs) {
                    if ($value > $inputs['payable']) {
                        $fail('The '.$attribute.' amount must not exceed the payable amount.');
                    }

                },
            ],
            'vh_st_payment_method_id' => 'required',
            'taxonomy_id_order_status' => 'required',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:250'
            ],
        ],
            [
                'vh_user_id.required' => 'The User field is required',
                'amount.min' => 'The Amount field is required',
                'amount.required' => 'The Amount field is required',
                'payable.min' => 'The Payable field is required',
                'payable.required' => 'The Payable field is required',
                'taxes.required' => 'The Taxes field is required',
                'discount.required' => 'The Discount field is required',
                'paid.min' => 'The Paid field is required',
                'paid.required' => 'The Paid field is required',
                'delivery_fee.required' => 'The Delivery Fee field is required',
                'taxonomy_id_order_status.required' => 'The Status field is required',
                'status_notes.required_if' => 'The Status notes field is required for "Rejected" Status',
                'amount.regex' => 'amount must be between 1 to 10 digits',
                'delivery_fee.regex' => 'The Delivery charges must be between 1 to 10 digits',
                'taxes.regex' => 'The Tax amount must be between 1 to 10 digits',
                'discount.regex' => 'The Discount value must be between 1 to 10 digits',
                'payable.regex' => 'The Payable amount must be between 1 to 10 digits',
                'payable.gt' => 'The Payable amount must be greater than 0',
                'paid.regex' => 'The Paid amount must be between 1 to 10 digits',
                'paid.max' => 'The Paid amount must be less than payable amount',
                'vh_st_payment_method_id.required' => 'The Payment Method field is required',

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

    //-------------------------------------------------
    public static function getActiveItems()
    {
        $item = self::where('is_active', 1)
            ->withTrashed()
            ->first();
        return $item;
    }

    //-------------------------------------------------

    public static function seedSampleItems($records=100)
    {

        $i = 0;

        while($i < $records)
        {
            $inputs = self::fillItem(false);

            $item =  new self();
            $item->fill($inputs);
            $item->save();

            $i++;

        }

    }

    //-------------------------------------------------

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
        $inputs = $fillable['data']['fill'];
        $faker = Factory::create();

        /*
         * You can override the filled variables below this line.
         * You should also return relationship from here
         */


        // fill the user field with any random user here
        $users = User::where(['is_active'=>1,'deleted_at'=>null]);
        $user_ids = $users->pluck('id')->toArray();
        $user_id = $user_ids[array_rand($user_ids)];
        $user = $users->where('id',$user_id)->first();
        $inputs['vh_user_id']=$user_id;
        $inputs['user']=$user;

        $inputs['amount'] = rand(1,10000000);
        $inputs['delivery_fee'] = rand(1,1000);
        $inputs['taxes'] = rand(1,1000);
        $inputs['discount'] = rand(1,1000);
        $payable_amount = $inputs['amount'] + $inputs['delivery_fee'] + $inputs['taxes'] - $inputs['discount'];
        $inputs['payable'] = $payable_amount;
        $inputs['paid'] = rand(1,$payable_amount);
        $inputs['status_notes']=$faker->text(rand(5,250));

        // fill the payment method column here
        $payment_methods = PaymentMethod::where(['is_active'=>1,'deleted_at'=>null]);
        $payment_method_ids = $payment_methods->pluck('id')->toArray();
        $payment_method_id = $payment_method_ids[array_rand($payment_method_ids)];
        $payment_method = $payment_methods->where('id',$payment_method_id)->first();
        $inputs['vh_st_payment_method_id']=$payment_method_id;
        $inputs['payment_method']=$payment_method;

        // fill the taxonomy status field here
        $taxonomy_status = Taxonomy::getTaxonomyByType('order-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        $status_id = $status_ids[array_rand($status_ids)];
        $inputs['taxonomy_id_order_status'] = $status_id;
        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['status']=$status;
        $inputs['is_active'] = 1;

        // fill the taxonomy status while placing order
        $taxonomy_order_item_status = Taxonomy::getTaxonomyByType('order-items-status');
        $status_order_item_ids = $taxonomy_order_item_status->pluck('id')->toArray();
        $status_order_item_id = $status_order_item_ids[array_rand($status_order_item_ids)];
        $inputs['taxonomy_id_order_items_status'] = $status_order_item_id;
        $status_order_item = $taxonomy_order_item_status->where('id',$status_order_item_id)->first();
        $inputs['status_order_items']=$status_order_item;
        $inputs['is_active_order_item'] = 1;

        $number_of_characters = rand(5,250);
        $inputs['status_notes_order_item']=$faker->text($number_of_characters);

        // fill the types field here
        $types = Taxonomy::getTaxonomyByType('order-items-types');
        $type_ids = $types->pluck('id')->toArray();
        $type_id = $type_ids[array_rand($type_ids)];
        $type = $types->where('id',$type_id)->first();
        $inputs['types'] = $type;
        $inputs['taxonomy_id_order_items_types'] = $type_id ;

        // fill the product field here
        $products = Product::where('is_active',1);
        $product_ids = $products->pluck('id')->toArray();
        $product_id = $product_ids[array_rand($product_ids)];
        $product = $products->where('id',$product_id)->first();
        $inputs['product'] = $product;
        $inputs['vh_st_product_id'] = $product_id ;

        // fill the product variation field here
        $product_variations = ProductVariation::where('is_active',1);
        $product_variation_ids = $product_variations->pluck('id')->toArray();
        $product_variation_id = $product_variation_ids[array_rand($product_variation_ids)];
        $product_variation = $product_variations->where('id',$product_variation_id)->first();
        $inputs['product_variation'] = $product_variation;
        $inputs['vh_st_product_variation_id'] = $product_variation_id;

        // fill the vendor field here
        $vendors = Vendor::where('is_active',1);
        $vendor_ids = $vendors->pluck('id')->toArray();
        $vendor_id = $vendor_ids[array_rand($vendor_ids)];
        $vendor = $vendors->where('id',$vendor_id)->first();
        $inputs['vendor'] = $vendor;
        $inputs['vh_st_vendor_id'] = $vendor_id;

        // fill the Customer Group field here
        $customer_groups = CustomerGroup::all();
        $customer_group_ids = $customer_groups->pluck('id')->toArray();
        $customer_group_id = $customer_group_ids[array_rand($customer_group_ids)];
        $customer_group = $customer_groups->where('id',$customer_group_id)->first();
        $inputs['customer_group'] = $customer_group;
        $inputs['vh_st_customer_group_id'] = $customer_group_id;
        $inputs['invoice_url'] = $faker->url;
        $inputs['tracking'] = $faker->url;
        $inputs['is_invoice_available'] = 1;


        if(!$is_response_return){
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }

    //-----------------validation for product price--------------------------------

    public static function validationOrderItem($inputs)
    {
        $rules = validator($inputs,
            [
                'types' => 'required',
                'product' => 'required',
                'product_variation' => 'required',
                'vendor' => 'required',
                'customer_group' => 'required',
                'invoice_url' => 'required',
                'tracking' => 'required',
                'status_order_items' => 'required',
                'status_notes_order_item' => [
                    'required_if:status_order_items.slug,==,rejected',
                    'max:250'
                ],
            ],
            [
                'types.required' => 'The Payment Type field is required',
                'product.required' => 'The Product field is required',
                'product_variation.required' => 'The Product Variation field is required',
                'vendor.required' => 'The Vendor field is required',
                'customer_group.required' => 'The Customer Groups field is required',
                'invoice_url.required' => 'The Invoice URL field is required',
                'status_order_items.required' => 'The Status field is required',
                'tracking.required' => 'The Tracking field is required',
                'status_notes_order_item.required_if' => 'The Status Notes is required for rejected status',
                'status_notes_order_item.max' => 'The Status notes field may not be greater than :max characters.',
            ]);

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

    //-------------------------------------------------


}
