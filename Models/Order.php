<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Entities\User;

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
    protected $appends = [
    ];

    //-------------------------------------------------
    protected function serializeDate(DateTimeInterface $date)
    {
        $date_time_format = config('settings.global.datetime_format');
        return $date->format($date_time_format);
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
    public function statusOrder()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_order_status')->select('id','name','slug');
    }

    //-------------------------------------------------
    public function user()
    {
        return $this->hasOne(User::class,'id','vh_user_id')->select('id','first_name', 'email');
    }

    //-------------------------------------------------
    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class,'id','vh_st_payment_method_id')->select('id','name','slug');
    }
    //-------------------------------------------------
    public function Items()
    {
        return $this->hasOne(OrderItem::class,'vh_st_order_id','id')->select();
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
    public static function createOrderItem($request){
        $inputs = $request->all();

        $validation = self::validationOrderItem($inputs);
        if (!$validation['success']) {
            return $validation;
        }
        $check = OrderItem::where('vh_st_order_id',$inputs['id'])->first();
        if($check){
            $check->fill($inputs);
            $check->vh_st_order_id = $inputs['id'];
            $check->vh_user_id  = $inputs['vh_user_id']['id'];
            $check->taxonomy_id_order_items_types = $inputs['types']['id'];
            $check->taxonomy_id_order_items_status = $inputs['status_order_items']['id'];
            $check->vh_st_product_id = $inputs['product']['id'];
            $check->vh_st_product_variation_id = $inputs['product_variation']['id'];
            $check->vh_st_customer_group_id = $inputs['customer_group']['id'];
            $check->vh_st_vendor_id = $inputs['vendor']['id'];
            $check->save();
            $response['messages'][] = 'Saved successfully update.';
            return $response;
        }
        $order_item = new OrderItem;
        $order_item->fill($inputs);
        $order_item->vh_st_order_id = $inputs['id'];
        $order_item->vh_user_id  = $inputs['vh_user_id']['id'];
        $order_item->taxonomy_id_order_items_types = $inputs['types']['id'];
        $order_item->taxonomy_id_order_items_status = $inputs['status_order_items']['id'];
        $order_item->vh_st_product_id = $inputs['product']['id'];
        $order_item->vh_st_product_variation_id = $inputs['product_variation']['id'];
        $order_item->vh_st_customer_group_id = $inputs['customer_group']['id'];
        $order_item->save();
        $response['messages'][] = 'Saved successfully.';
        return $response;
    }
    //-------------------------------------------------
    public static function createItem($request)
    {

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }


        // check if name exist
        $item = self::where('vh_user_id', $inputs['vh_user_id']['id'])->withTrashed()->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This name is already exist.";
            return $response;
        }

        $item = new self();
        $item->fill($inputs);
        $item->status_notes = $inputs['status_notes'];
        $item->taxonomy_id_order_status = $inputs['taxonomy_id_order_status']['id'];
        $item->vh_user_id = $inputs['vh_user_id']['id'];
        $item->vh_st_payment_method_id = $inputs['vh_st_payment_method_id']['id'];
        if($inputs['is_paid']==1 && $inputs['paid']==0){
            $response['messages'][] = 'The paid should be more then 1';
            return $response;
        }else{
            $item->paid = $inputs['paid'];
            $item->is_paid = $inputs['is_paid'];
        }
        if($inputs['is_paid']==0){
            $item->paid = 0;
        }
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
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
            return $query->whereNotNull('is_active');
        } else{
            return $query->whereNull('is_active');
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
            $q->where('name', 'LIKE', '%' . $search . '%')
                ->orWhere('slug', 'LIKE', '%' . $search . '%');
        });

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('statusOrder','paymentMethod','user');
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
                break;
            case 'restore':
                self::whereIn('id', $items_id)->restore();
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
            $response['failed'] = true;
            $response['messages'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        self::whereIn('id', $items_id)->forceDelete();
        OrderItem::deleteOrder($items_id);

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
                }
                break;
            case 'restore':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->restore();
                }
                break;
            case 'delete':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->forceDelete();
                    OrderItem::deleteOrder($items_id);
                }
                break;
            case 'activate-all':
                self::query()->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                self::query()->update(['is_active' => null]);
                break;
            case 'trash-all':
                self::query()->delete();
                break;
            case 'restore-all':
                self::withTrashed()->restore();
                break;
            case 'delete-all':
                $items_id = self::all()->pluck('id')->toArray();
                self::withTrashed()->forceDelete();
                OrderItem::deleteOrder($items_id);
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','statusOrder','paymentMethod','user','Items'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }
        $array_item = $item->toArray();
        if($item['items']!=null){
            $item['types'] = Taxonomy::where('id',$array_item['items']['taxonomy_id_order_items_types'])->get()->toArray()[0];
            $item['product'] = Product::where('id',$array_item['items']['vh_st_product_id'])->get(['id','name','slug','is_default'])->toArray()[0];
            $item['product_variation'] = ProductVariation::where('id',$array_item['items']['vh_st_product_variation_id'])->get(['id','name','slug','is_default'])->toArray()[0];
            $item['vendor'] = Vendor::where('id',$array_item['items']['vh_st_vendor_id'])->get(['id','name','slug'])->toArray()[0];
            $item['customer_group'] = CustomerGroup::where('id',$array_item['items']['vh_st_customer_group_id'])->get(['id','name','slug'])->toArray()[0];
            $item['status_order_items'] = Taxonomy::where('id',$array_item['items']['taxonomy_id_order_items_status'])->get()->toArray()[0];
            $item['status_notes_order'] = $array_item['items']['status_notes'];
            $item['is_active_order_item'] = $array_item['items']['is_active'];
            $item['is_invoice_available'] = $array_item['items']['is_invoice_available'];
            $item['invoice_url'] = $array_item['items']['invoice_url'];
            $item['tracking'] = $array_item['items']['tracking'];
        }
        else{
            $item['is_invoice_available'] = 1;
            $item['is_active_order_item'] = 1;
        }

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

        // check if name exist
        $item = self::where('id', '!=', $inputs['id'])
            ->withTrashed()
            ->where('vh_user_id', $inputs['vh_user_id']['id'])->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This name is already exist.";
            return $response;
        }
//
//        // check if slug exist
//        $item = self::where('id', '!=', $inputs['id'])
//            ->withTrashed()
//            ->where('slug', $inputs['slug'])->first();
//
//        if ($item) {
//            $response['success'] = false;
//            $response['messages'][] = "This slug is already exist.";
//            return $response;
//        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->taxonomy_id_order_status = $inputs['taxonomy_id_order_status']['id'];
        $item->vh_user_id = $inputs['user']['id'];
        $item->status_notes = $inputs['status_notes'];
        $item->vh_st_payment_method_id = $inputs['vh_st_payment_method_id']['id'];
        if($inputs['is_paid']==1 && $inputs['paid']==0){
            $response['messages'][] = 'The paid should be more then 1';
            return $response;
        }else{
            $item->paid = $inputs['paid'];
            $item->is_paid = $inputs['is_paid'];
        }
        if($inputs['is_paid']==0){
            $item->paid = 0;
        }
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request, $id): array
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
                self::find($id)->delete();
                break;
            case 'restore':
                self::where('id', $id)
                    ->withTrashed()
                    ->restore();
                break;
        }

        return self::getItem($id);
    }
    //-------------------------------------------------

    public static function validation($inputs)
    {

        $rules = validator($inputs, [
            'taxonomy_id_order_status' => 'required|max:150',
            'vh_user_id' => 'required|max:150',
            'status_notes' => 'required_if:taxonomy_id_order_status.slug,==,rejected',
            'vh_st_payment_method_id' => 'required|max:150',
            'amount' => 'required|min:1|numeric',
            'delivery_fee' => 'required|min:0|numeric',
            'taxes' => 'required|min:0|numeric',
            'discount' => 'required|min:0|numeric',
            'payable' => 'required|min:0|numeric',
            'paid' => 'required|min:0|numeric'
                ],
        [
            'vh_st_payment_method_id.required' => 'The Payment Method field is required',
            'vh_user_id.required' => 'The User field is required',
            'taxonomy_id_order_status.required' => 'The Status field is required',
            'status_notes.*' => 'The Status notes field is required for "Rejected" Status',
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

    public static function validationOrderItem($inputs)
    {
        $rules = validator($inputs,
            [
            'types' => 'required|max:150',
            'product_variation' => 'required|max:150',
            'product' => 'required|max:150',
            'vendor' => 'required',
            'customer_group' => 'required',
            'invoice_url' => 'required',
            'tracking' => 'required',
            'status_order_items' => 'required',
            'status_notes_order' => 'required_if:status_order_items.slug,==,rejected',
                ],
            [
                'status_order_items.required' => 'The Status field is required',
                'status_notes_order.*' => 'The Status notes field is required for "Rejected" Status',
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
    public static function getActiveItems()
    {
        $item = self::where('is_active', 1)
            ->first();
        return $item;
    }

    //-------------------------------------------------
    //-------------------------------------------------
    //-------------------------------------------------


}
