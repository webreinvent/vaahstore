<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use VaahCms\Modules\Store\Models\ProductVendor;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\TaxonomyType;
use Faker\Factory;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use VaahCms\Modules\Store\Models\Store;

class Vendor extends Model
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_vendors';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'vh_st_store_id', 'name', 'slug',
        'owned_by', 'registered_at',
        'auto_approve_products', 'approved_by',
        'approved_at', 'is_default', 'is_active',
        'taxonomy_id_vendor_status', 'status_notes', 'meta',
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
    public function store(){
        return $this->hasOne(Store::class, 'id', 'vh_st_store_id')->select(['id','name', 'is_default','slug']);
    }

    //-------------------------------------------------
    public function approvedByUser(){
        return $this->hasOne(User::class, 'id', 'approved_by')->select(['id','first_name','email']);
    }

    //-------------------------------------------------
    public function ownedByUser(){
        return $this->hasOne(User::class, 'id', 'owned_by')->select(['id','first_name', 'email']);
    }

    //-------------------------------------------------
    public function status(){
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id_vendor_status')->select(['id','name','slug']);
    }

    //-------------------------------------------------
    public function vendorProducts()
    {
        return $this->hasMany(ProductVendor::class,'vh_st_vendor_id','id')
            ->where('vh_st_product_vendors.is_active', 1)
            ->select();
    }

    //-------------------------------------------------
    public static function validatedProduct($data){
        if (isset($data) && !empty($data)){
            $error_message = [];

            foreach ($data as $key=>$value){
                if (!isset($value['status']) || empty($value['status'])){
                    array_push($error_message, 'Status required');
                }else if($value['status']['slug']=='rejected' && empty($value['status_notes'])){
                    array_push($error_message, 'The Status notes field is required for "Rejected" Status');
                }
                if (!isset($value['product']) || empty($value['product'])){
                    array_push($error_message, 'product required');
                }
                if (!isset($value['can_update'])){
                    array_push($error_message, 'Can Update required');
                }
            }

            if (empty($error_message)){
                return [
                    'success' => true
                ];
            }else{
                return [
                    'success' => false,
                    'errors' => $error_message
                ];
            }

        }else{
            return [
                'success' => false,
                'errors' => ['Product is empty.']
            ];
        }
    }

    //-------------------------------------------------
    public static function createProduct($request){

        $input = $request->all();
        $vendor_id = $input['id'];
        $validation = self::validatedProduct($input['products']);
        if (!$validation['success']) {
            return $validation;
        }
        $product_data = $input['products'];
        $active_user = auth()->user();
        ProductVendor::where('vh_st_vendor_id', $vendor_id)->update(['is_active'=>0]);
        foreach ($product_data as $key=>$value){

            $precious_record = ProductVendor::where(['vh_st_vendor_id'=> $vendor_id, 'vh_st_product_id' => $value['product']['id']])->first();
            if (isset($value['id']) && !empty($value['id'])){
                $item = ProductVendor::where('id',$value['id'])->first();
                $item->vh_st_vendor_id = $vendor_id;
                $item->vh_st_product_id = $value['product']['id'];
                $item->added_by = $active_user->id;
                $item->can_update = $value['can_update'];
                $item->taxonomy_id_product_vendor_status = $value['status']['id'];
                $item->status_notes = $value['status_notes'];
                $item->is_active = 1;
                $item->save();
            }else if($precious_record){
                $precious_record->added_by = $active_user->id;
                $precious_record->can_update = $value['can_update'];
                $precious_record->taxonomy_id_product_vendor_status = $value['status']['id'];
                $precious_record->status_notes = $value['status_notes'];
                $precious_record->is_active = 1;
                $precious_record->save();
            }else {
                $item = new ProductVendor();
                $item->vh_st_vendor_id = $vendor_id;
                $item->vh_st_product_id = $value['product']['id'];
                $item->added_by = $active_user->id;
                $item->can_update = $value['can_update'];
                $item->taxonomy_id_product_vendor_status = $value['status']['id'];
                $item->status_notes = $value['status_notes'];
                $item->is_active = 1;
                $item->save();
            }
        }

        $response = self::getItem($vendor_id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }
    //-------------------------------------------------
    public static function bulkProductRemove($request ,$id){
        ProductVendor::where('vh_st_vendor_id', $id)->update(['is_active'=>0]);
        $response['messages'][] = 'Removed successfully.';
        return $response;


    }

    //-------------------------------------------------
    public static function createItem($request)
    {
        $inputs = $request->all();
        $validation_result = self::vendorInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }

        // check if name exist
        $item = self::where('name', $inputs['name'])->withTrashed()->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This name is already exist.";
            return $response;
        }

        // check if slug exist
        $item = self::where('slug', $inputs['slug'])->withTrashed()->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This slug is already exist.";
            return $response;
        }

        // Check if current record is default
        if($inputs['is_default']){
            self::where('is_default',1)->update(['is_default' => 0]);
        }

        $inputs = $validation_result['data'];
        $item = new self();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->registered_at = \Carbon\Carbon::now()->toDateTimeString();
        $item->approved_at = \Carbon\Carbon::now()->toDateTimeString();
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }

    //-------------------------------------------------
    public static function vendorInputValidator($requestData){

        $validated_data = validator($requestData, [
            'name' => 'required|max:100',
            'slug' => 'required|max:100',
            'vh_st_store_id' => 'required',
            'owned_by' => 'required',
            'auto_approve_products' => 'required',
            'approved_by' => 'required',
            'is_default' => 'required',
            'taxonomy_id_vendor_status' => 'required',
            'is_active' => 'required',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:100'
            ],

        ],
            [
                'vh_st_store_id.required' => 'The Store field is required',
                'taxonomy_id_vendor_status.required' => 'The Status field is required',
                'status_notes.required_if' => 'The Status notes field is required for "Rejected" Status',
                'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
            ]
        );

        if($validated_data->fails()){
            return [
                'success' => false,
                'errors' => $validated_data->errors()->all()
            ];
        }

        $validated_data = $validated_data->validated();

        return [
            'success' => true,
            'data' => $validated_data
        ];

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
            $q->where('name', 'LIKE', '%' . $search . '%')
                ->orWhere('slug', 'LIKE', '%' . $search . '%')
                ->orWhere('id', 'LIKE', '%' . $search . '%');
        });

    }
    //-------------------------------------------------
    public function scopeSearchStore($query, $filter)
    {

        if(!isset($filter['vendor_status']))
        {
            return $query;
        }
        $search = $filter['vendor_status'];
        $query->whereHas('status' , function ($q) use ($search){
            $q->whereIn('name' ,$search);
        });

    }
    //-------------------------------------------------
    public function scopeVendorStatus($query, $filter)
    {
        if(!isset($filter['store']))
        {
            return $query;
        }
        $search = $filter['store'];
        $query->whereHas('store',function ($q) use ($search) {
            $q->whereIn('name',$search);
        });

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with(['store', 'approvedByUser', 'ownedByUser', 'status','vendorProducts']);
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->searchStore($request->filter);
        $list->vendorStatus($request->filter);

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
                $items->update(['deleted_by' => auth()->user()->id]);
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
        ProductVendor::deleteVendors($items_id);

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
                }
                break;
            case 'activate-all':
                $list->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                $list->update(['is_active' => null]);
                break;
            case 'trash-all':
                $list->update(['deleted_by'  => auth()->user()->id]);
                $list->delete();
                break;
            case 'restore-all':
                $list->restore();
                $list->update(['deleted_by'  => null]);
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser', 'store', 'approvedByUser','ownedByUser',
                'status','vendorProducts'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }

        $array_item = $item->toArray();
        $vendor_product = [];
        if (!empty($array_item['vendor_products'])){
            forEach($array_item['vendor_products'] as $key=>$value){
                $new_array = [];
                $new_array['id'] = $value['id'];
                $new_array['is_selected'] = false;
                $new_array['can_update'] = $value['can_update'] == 1 ? true : false;
                $new_array['status_notes'] = $value['status_notes'];
                $new_array['product'] = Product::where('id',$value['vh_st_product_id'])->get(['id','name','slug','is_default'])->toArray()[0];
                $new_array['status'] = Taxonomy::where('id',$value['taxonomy_id_product_vendor_status'])->get()->toArray()[0];
                array_push($vendor_product, $new_array);
            }
            $item['products'] = $vendor_product;
        }else{
            $item['products'] = [];
        }

        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }
    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $validation_result = self::vendorInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }

        $inputs = $validation_result['data'];

        // Check if current record is default
        if($inputs['is_default']){
            self::where('is_default',1)->update(['is_default' => 0]);
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->registered_at = \Carbon\Carbon::now()->toDateTimeString();
        $item->approved_at = \Carbon\Carbon::now()->toDateTimeString();
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
            $response['errors'][] = 'Record does not exist.';
            return $response;
        }
        $item->forceDelete();
        ProductVendor::deleteVendors([$item->id]);

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
                if($item->delete()) {
                    $item->deleted_by = auth()->user()->id;
                    $item->save();
                }
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

        $rules = array(
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
        );

        $validator = \Validator::make($inputs, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $response['success'] = false;
            $response['errors'] = $messages->all();
            return $response;
        }

        $response['success'] = true;
        return $response;

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

        $store = Store::where('is_active', 1)->inRandomOrder()->first();
        $inputs['vh_st_store_id'] = $store->id;
        $inputs['store'] = $store;

        $approved_by = User::where('is_active',1)->inRandomOrder()->first();
        $inputs['approved_by'] =$approved_by->id;
        $inputs['approved_by_user'] = $approved_by;

        $owned_by = User::where('is_active',1)->inRandomOrder()->first();
        $inputs['owned_by'] =$owned_by->id;
        $inputs['owned_by_user'] = $owned_by;

        $taxonomy_status = Taxonomy::getTaxonomyByType('vendor-status');
        $status_id = $taxonomy_status->pluck('id')->random();
        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['taxonomy_id_vendor_status'] = $status_id;
        $inputs['status']=$status;

        $faker = Factory::create();

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

    //-------------------------------------------------
    public static function deleteStores($items_id){
        if($items_id){
            self::whereIn('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
            $response['data'] = true;
        }else{
            $response['error'] = true;
            $response['data'] = false;
        }

    }
    //-------------------------------------------------
    public static function searchStore($request)
    {

        $search_store = Store::select('id', 'name','slug')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_store->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%');
                $q->orwhere('slug', 'LIKE', '%' . $query . '%');
            });
        }
        $search_store = $search_store->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_store;
        return $response;
    }
    //-------------------------------------------------
    public static function searchApprovedBy($request)
    {
        $search_approved = User::select('id', 'first_name','last_name','email')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_approved->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', '%' . $query . '%');
                $q->orwhere('email', 'LIKE', '%' . $query . '%');
            });
        }
        $search_approved = $search_approved->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_approved;
        return $response;
    }

    //-------------------------------------------------
    public static function searchOwnedBy($request)
    {
        $search_owned_by = User::select('id', 'first_name','last_name','email')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_owned_by->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', '%' . $query . '%');
                $q->orwhere('email', 'LIKE', '%' . $query . '%');
            });
        }
        $search_owned_by = $search_owned_by->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_owned_by;
        return $response;
    }
    //-------------------------------------------------
    public static function searchStatus($request)
    {
        $query = $request->input('query');
        if(empty($query)) {
            $item = Taxonomy::getTaxonomyByType('vendor-status');
        } else {
            $tax_type = TaxonomyType::getFirstOrCreate('vendor-status');
            $item =array();

            if(!$tax_type){
                return $item;
            }
            $item = Taxonomy::whereNotNull('is_active')
                ->where('vh_taxonomy_type_id',$tax_type->id)
                ->where('name', 'LIKE', '%' . $query . '%')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $item;
        return $response;
    }
    //-------------------------------------------------


}
