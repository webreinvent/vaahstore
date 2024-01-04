<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Faker\Factory;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\Vendor;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use WebReinvent\VaahCms\Entities\Taxonomy;


class Product extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_products';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------

    protected $fillable = [
        'uuid',
        'id',
        'name',
        'slug',
        'taxonomy_id_product_type',
        'vh_st_store_id',
        'vh_st_brand_id', 'vh_cms_content_form_field_id',
        'quantity', 'in_stock', 'is_active',
        'taxonomy_id_product_status', 'status_notes', 'meta',
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

    public function brand()
    {
        return $this->hasOne(Brand::class,'id','vh_st_brand_id')
                    ->select('id','name','slug','is_default');
    }
    //-------------------------------------------------

    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vh_st_vendor_id','id')
            ->select('id','name','slug');
    }

    //-------------------------------------------------

    public function store()
    {
        return $this->belongsTo(Store::class,'vh_st_store_id','id')->select('id','name','slug', 'is_default');
    }

    //-------------------------------------------------
    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_product_status')
            ->select('id','name','slug');
    }

    //-------------------------------------------------
    public function type()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_product_type')
            ->select('id','name','slug');
    }

    //-------------------------------------------------
    public function productAttributes()
    {
        return $this->belongsToMany(Attribute::class,'vh_st_product_attributes',
            'vh_st_attribute_id',
            'vh_st_product_variation_id');
    }
    //-------------------------------------------------
    public function productVariations()
    {
        return $this->hasMany(ProductVariation::class,'vh_st_product_id','id')
            ->where('vh_st_product_variations.is_active', 1)
            ->select();
    }

    //-------------------------------------------------
    public function productVendors()
    {
        return $this->hasMany(ProductVendor::class,'vh_st_product_id','id')
            ->where('vh_st_product_vendors.is_active', 1)
            ->select()
            ->with('vendor');
    }

    //-------------------------------------------------

    public function scopeStatusFilter($query, $filter)
    {

        if(!isset($filter['status'])
            || is_null($filter['status'])
            || $filter['status'] === 'null'
        )
        {
            return $query;
        }

        $status = $filter['status'];

        $query->whereHas('status', function ($query) use ($status) {
            $query->where('name', $status)
                ->orWhere('slug',$status);
        });

    }

    //-------------------------------------------------

    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()
            ->getColumnListing($this->getTable());
    }

    //-------------------------------------------------

    public function scopeQuantityFilter($query, $filter)
    {
        if(!isset($filter['quantity'])
            || is_null($filter['quantity'])
            || $filter['quantity'] === 'null'
        )
        {
            return $query;
        }
        else{
            $quantity = $filter['quantity'];
            return $query->where(function ($q) use($quantity) {
                    $q->Where('quantity', '>', $quantity);

            });
        }


    }
    //-------------------------------------------------

    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }

    //-------------------------------------------------

    public static function createVariation($request)
    {
        $input = $request->all();
        $product_id = $input['id'];
        $validation = self::validatedVariation($input['all_variation']);
        if (!$validation['success']) {
            return $validation;
        }

        $all_variation = $input['all_variation']['structured_variation'];
        $all_attribute = $input['all_variation']['all_attribute_name'];

        foreach ($all_variation as $key => $value) {
            // check if product  variation exist for product
            $item = ProductVariation::where('name', $value['variation_name'])->where('vh_st_product_id',$product_id) ->withTrashed()->first();
            if ($item) {
                $response['success'] = false;
                $response['messages'][] = "This Variation name '{$value['variation_name']}' is already exist.";
                return $response;
            }

            $item = new ProductVariation();
            $item->name = $value['variation_name'];
            $item->slug = Str::slug($value['variation_name']);
            $item->in_stock = 'No';
            $item->quantity = 0;
            $taxonomy_status_id = Taxonomy::getTaxonomyByType('product-variation-status')->where('name', 'Pending')->pluck('id')->first();
            $item->taxonomy_id_variation_status = $taxonomy_status_id;
            $item->vh_st_product_id = $product_id;
            $item->is_active = 1;
            $item->save();
            foreach ($all_attribute as $k => $v) {
                $item2 = new ProductAttribute();
                $item2->vh_st_product_variation_id = $item->id;
                $item2->vh_st_attribute_id = $value[$v]['vh_st_attribute_id'];
                $item2->save();

                $item3 = new ProductAttributeValue();
                $item3->vh_st_product_attribute_id = $item2->id;
                $item3->vh_st_attribute_value_id = $value[$v]['id'];
                $item3->value = $value[$v]['value'];
                $item3->save();
            }
        }

        $response = self::getItem($product_id);
        $response['messages'][] = 'Variation Saved successfully.';
        return $response;
    }

    //-------------------------------------------------
    public static function validatedVariation($variation){

        if (isset($variation['structured_variation']) && !empty($variation['structured_variation'])){
            $error_message = [];
            $all_variation = $variation['structured_variation'];
            $all_arrtibute = $variation['all_attribute_name'];

            foreach ($all_variation as $key=>$value){

                if (!isset($value['variation_name']) || empty($value['variation_name'])) {
                    array_push($error_message, "variation name's required");
                }

                foreach ($all_arrtibute as $k => $v){
                    if (!isset($value[$v]) || empty($value[$v])){
                        array_push($error_message, $value["variation_name"]."'s ".$v."'s required");
                    }
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
                'errors' => ['Product Variation is empty']
            ];
        }
    }

    //-------------------------------------------------

    public static function validatedVendor($data){
        if (isset($data) && !empty($data)){
            $error_message = [];

            foreach ($data as $key=>$value){
                if (!isset($value['status']) || empty($value['status'])){
                    array_push($error_message, 'Status required');
                }else if($value['status']['slug']=='rejected' && empty($value['status_notes'])){
                    array_push($error_message, 'The Status notes field is required for "Rejected" Status');
                }
                if (!isset($value['vendor']) || empty($value['vendor'])){
                    array_push($error_message, 'Vendor required');
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
                'errors' => ['Vendor is empty.']
            ];
        }
    }

    //-------------------------------------------------
    public static function createVendor($request){

        $input = $request->all();
        $product_id = $input['id'];
        $validation = self::validatedVendor($input['vendors']);
        if (!$validation['success']) {
            return $validation;
        }
        $vendor_data = $input['vendors'];

        $active_user = auth()->user();

        foreach ($vendor_data as $key=>$value){

            $product_vendor = ProductVendor::where(['vh_st_vendor_id'=> $value['vendor']['id'], 'vh_st_product_id' => $product_id])->first();

            if (isset($value['id']) && !empty($value['id'])){

                $item = ProductVendor::where('id',$value['id'])->first();
                $item->vh_st_product_id = $product_id;
                $item->vh_st_vendor_id = $value['vendor']['id'];
                $item->added_by = $active_user->id;
                $item->can_update = $value['can_update'];
                $item->taxonomy_id_product_vendor_status = $value['status']['id'];
                $item->status_notes = $value['status_notes'];
                $item->is_active = 1;
                $item->save();
            }else if($product_vendor){
                $response['errors'][] = "This Vendor '{$value['vendor']['name']}' is already exist.";
                return $response;
            }else {

                $item = new ProductVendor();
                $item->vh_st_product_id = $product_id;
                $item->vh_st_vendor_id = $value['vendor']['id'];
                $item->added_by = $active_user->id;
                $item->can_update = $value['can_update'];
                $item->taxonomy_id_product_vendor_status = $value['status']['id'];
                $item->status_notes = $value['status_notes'];
                $item->is_active = 1;
                $item->save();
            }
        }

        $response = self::getItem($product_id);
        $response['messages'][] = 'Vendor Added successfully.';
        return $response;

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

        $item = new self();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }

    //-------------------------------------------------

    public static function removeVendor($request ,$id){

        ProductVendor::where('id', $id)->update(['is_active'=>0]);
        $product = ProductVendor::select('vh_st_product_id')->where('id', $id)->first();
        $response = self::getItem($product->vh_st_product_id);
        $response['messages'][] = 'Action Successful.';
        return $response;

    }

    //------------------------------------------------

    public static function bulkRemoveVendor($request ,$id){

        ProductVendor::where('vh_st_product_id', $id)->update(['is_active'=>0]);
        $response['messages'][] = 'Action Successful';
        return $response;
    }

    //------------------------------------------------

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
        $keywords = explode(' ',$filter['q']);
        foreach($keywords as $search)
        {
            $query->where(function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('slug', 'LIKE', '%' . $search . '%');
                })

                    ->orWhere('id', 'LIKE', '%' . $search . '%');

            });
        }

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('brand','store','type','status', 'productVariations', 'productVendors');

        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->quantityFilter($request->filter);
        $list->productVariationFilter($request->filter);
        $list->vendorFilter($request->filter);
        $list->storeFilter($request->filter);
        $list->dateFilter($request->filter);

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
        ProductVendor::deleteProducts($items_id);
        ProductVariation::deleteProducts($items_id);
        ProductMedia::deleteProducts($items_id);
        ProductPrice::deleteProducts($items_id);
        ProductStock::deleteProducts($items_id);

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
                    ProductVendor::deleteProducts($items_id);
                    ProductVariation::deleteProducts($items_id);
                    ProductMedia::deleteProducts($items_id);
                    ProductPrice::deleteProducts($items_id);
                    ProductStock::deleteProducts($items_id);
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
                break;
            case 'delete-all':
                $items_id = self::all()->pluck('id')->toArray();
                self::withTrashed()->forceDelete();
                ProductVendor::deleteProducts($items_id);
                ProductVariation::deleteProducts($items_id);
                ProductMedia::deleteProducts($items_id);
                ProductPrice::deleteProducts($items_id);
                ProductStock::deleteProducts($items_id);
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser',
                'brand','store','type','status',
            ])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }
        $array_item = $item->toArray();
        $product_vendor = [];
        if (!empty($array_item['product_vendors'])){
            forEach($array_item['product_vendors'] as $key=>$value){
                $new_array = [];
                $new_array['id'] = $value['id'];
                $new_array['is_selected'] = false;
                $new_array['can_update'] = $value['can_update'] == 1 ? true : false;
                $new_array['status_notes'] = $value['status_notes'];
                $new_array['vendor'] = Vendor::where('id',$value['vh_st_vendor_id'])->get(['id','name','slug','is_default'])->toArray()[0];
                $new_array['status'] = Taxonomy::where('id',$value['taxonomy_id_product_vendor_status'])->get()->toArray()[0];
                array_push($product_vendor, $new_array);
            }
            $item['vendors'] = $product_vendor;
        }else{
            $item['vendors'] = [];
        }

        $item['product_variation'] = null;
        $item['all_variation'] = [];
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
        $item = self::where('id', '!=', $id)
            ->withTrashed()
            ->where('name', $inputs['name'])->first();

        if ($item) {
            $response['success'] = false;
            $response['errors'][] = "This name is already exist.";
            return $response;
        }

        // check if slug exist
        $item = self::where('id', '!=', $id)
            ->withTrashed()
            ->where('slug', $inputs['slug'])->first();

        if ($item) {
            $response['success'] = false;
            $response['errors'][] = "This slug is already exist.";
            return $response;
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->in_stock = $inputs['quantity'] > 0 ? 1 : 0 ;
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
        ProductVendor::deleteProduct($item->id);
        ProductVariation::deleteProduct($item->id);
        ProductMedia::deleteProduct($item->id);
        ProductPrice::deleteProduct($item->id);
        ProductStock::deleteProduct($item->id);
        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Record has been deleted';

        return $response;
    }
    //-------------------------------------------------

    public static function searchStore($request)
    {

        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $stores = Store::take(10)
                ->get();
        }

        else{

            $stores = Store::where('name', 'like', "%$query%")
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $stores;
        return $response;

    }

    //-------------------------------------------------

    public static function searchBrand($request)
    {

        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $brands = Brand::take(10)
                ->get();
        }

        else{

            $brands = Brand::where('name', 'like', "%$query%")
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $brands;
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
    public static function  validation($inputs)
    {
        $rules = validator($inputs, [
            'name' => 'required|max:250',
            'slug' => 'required|max:250',
            'vh_st_store_id'=> 'required',
            'vh_st_brand_id'=> 'required',
            'taxonomy_id_product_type'=> 'required',
            'quantity'  => 'required|numeric|min:0|digits_between:1,9',
            'taxonomy_id_product_status'=> 'required',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:250'
            ],
            'in_stock'=> 'required|numeric',

        ],
            [    'name.required' => 'The Name field is required',
                'slug.required' => 'The Slug field is required',
                'taxonomy_id_product_status.required' => 'The Status field is required',
                'status_notes.required_if' => 'The Status notes is required for "Rejected" Status',
                'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
                'vh_st_brand_id.required' => 'The Brand field is required',
                'vh_st_store_id.required' => 'The Store field is required',
                'taxonomy_id_product_type.required' => 'The Type field is required',
                'status_notes.*' => 'The Status notes field is required for "Rejected" Status',
                'quantity.digits_between' => 'The Quantity field must not be greater than 9 digits',
                'quantity.required' => 'The Product Quantity is required',
                'quantity.min' => 'The Product Quantity is required',
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

       // fill the name field here
        $max_chars = rand(5,250);
        $inputs['name']=$faker->text($max_chars);

        // fill the store field here
        $stores = Store::where('is_active',1)->get();
        $store_ids = $stores->pluck('id')->toArray();
        $store_id = $store_ids[array_rand($store_ids)];
        $store = $stores->where('id',$store_id)->first();
        $inputs['store'] = $store;
        $inputs['vh_st_store_id'] = $store_id ;

        // fill the Brand field here
        $brands = Brand::where('is_active',1);
        $brand_ids = $brands->pluck('id')->toArray();
        $brand_id = $brand_ids[array_rand($brand_ids)];
        $brand = $brands->where('id',$brand_id)->first();
        $inputs['brand'] = $brand;
        $inputs['vh_st_brand_id'] = $brand_id;

        // fill the taxonomy status field here
        $taxonomy_status = Taxonomy::getTaxonomyByType('store-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        $status_id = $status_ids[array_rand($status_ids)];
        $inputs['taxonomy_id_product_status'] = $status_id;
        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['status']=$status;

        $inputs['is_active'] = 1;
        $inputs['quantity'] = rand(1,10000000);
        $inputs['in_stock'] = 1;
        // fill the product type field here
        $types = Taxonomy::getTaxonomyByType('product-types');
        $type_ids = $types->pluck('id')->toArray();
        $type_id = $type_ids[array_rand($type_ids)];
        $type = $types->where('id',$type_id)->first();
        $inputs['type'] = $type;
        $inputs['taxonomy_id_product_type'] = $type_id ;

        $number_of_characters = rand(5,250);
        $inputs['status_notes']=$faker->text($number_of_characters);

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
            self::where('vh_st_store_id',$items_id)->forcedelete();
            $response['success'] = true;
            $response['data'] = true;
        }else{
            $response['error'] = true;
            $response['data'] = false;
        }

    }

    //-------------------------------------------------

    public function scopeDateFilter($query, $filter)
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

    //-------------------------------------------------

    public static function searchProductVariation($request)
    {
        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $product_variations = ProductVariation::select('id','name','slug')
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        else{

            $product_variations = ProductVariation::where('name', 'like', "%$query%")
                ->orWhere('slug','like',"%$query%")
                ->select('id','name','slug')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $product_variations;
        return $response;

    }

    //-------------------------------------------------

    public static function searchVendor($request)
    {
        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $vendors = Vendor::select('id','name','slug')
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        else{

            $vendors = Vendor::where('name', 'like', "%$query%")
                ->orWhere('slug','like',"%$query%")
                ->select('id','name','slug')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $vendors;
        return $response;

    }

    //-------------------------------------------------

    public function scopeVendorFilter($query, $filter)
    {

        if(!isset($filter['vendors'])
            || is_null($filter['vendors'])
            || $filter['vendors'] === 'null'
        )
        {
            return $query;
        }

        $vendors = $filter['vendors'];

        $query->whereHas('productVendors.vendor', function ($query) use ($vendors) {
            $query->whereIn('slug', $vendors);

        });

    }

    //-------------------------------------------------

    public function scopeProductVariationFilter($query, $filter)
    {

        if(!isset($filter['product_variations'])
            || is_null($filter['product_variations'])
            || $filter['product_variations'] === 'null'
        )
        {
            return $query;
        }

        $product_variations = $filter['product_variations'];

        $query->whereHas('productVariations', function ($query) use ($product_variations) {
            $query->whereIn('slug', $product_variations);

        });

    }

    //-------------------------------------------------

    public function scopeStoreFilter($query, $filter)
    {
        if(!isset($filter['store'])
            || is_null($filter['store'])
            || $filter['store'] === 'null'
        )
        {
            return $query;
        }

        $store = $filter['store'];
        $query->whereHas('store', function ($query) use ($store) {
            $query->where('slug', $store);
        });

    }



}
