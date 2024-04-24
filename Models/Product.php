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
use VaahCms\Modules\Store\Models\ProductVendor;
use VaahCms\Modules\Store\Models\ProductAttribute;
use VaahCms\Modules\Store\Models\ProductMedia;
use VaahCms\Modules\Store\Models\ProductPrice;
use VaahCms\Modules\Store\Models\ProductStock;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\TaxonomyType;

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
        'summary',
        'details',
        'quantity',
        'taxonomy_id_product_type',
        'vh_st_store_id',
        'vh_st_brand_id', 'vh_cms_content_form_field_id',
        'is_active',
        'taxonomy_id_product_status', 'status_notes', 'meta',
        'seo_title','seo_meta_description','seo_meta_keyword',
        'created_by',
        'updated_by',
        'deleted_by',
        'is_featured_on_home_page',
        'is_featured_on_category_page',
        'available_at',
        'launch_at',

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
                    ->withTrashed()
                    ->select('id','name','slug','is_default','deleted_at');
    }
    //-------------------------------------------------

    public function vendor()
    {
        return $this->belongsTo(Vendor::class,'vh_st_vendor_id','id')->withTrashed()
            ->select('id','name','slug');
    }

    //-------------------------------------------------

    public function store()
    {
        return $this->belongsTo(Store::class,'vh_st_store_id','id')
            ->withTrashed()
            ->select('id','name','slug', 'is_default','deleted_at');
    }

    //-------------------------------------------------
    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_product_status');
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
            ->where('vh_st_product_variations.is_active', 1)->withTrashed()
            ->select();
    }
    public function productVariationsForVendorProduct()
    {
        return $this->hasMany(ProductVariation::class, 'vh_st_product_id', 'id')
            ->withTrashed();
    }

    //-------------------------------------------------
    public function productVendors()
    {
        return $this->hasMany(ProductVendor::class,'vh_st_product_id','id')
            ->select()
            ->with('vendor');
    }

    //-------------------------------------------------
    public function cart()
    {
        return $this->hasOne(User::class,'vh_st_carts','id');
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
            $query->whereIn('slug', $status);
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
        if (
            !isset($filter['quantity']) ||
            is_null($filter['quantity']) ||
            $filter['quantity'] === 'null' ||
            count($filter['quantity']) < 2 ||
            is_null($filter['quantity'][0]) ||
            is_null($filter['quantity'][1])
        ) {
            return $query;
        }

        $min_quantity = $filter['quantity'][0];
        $max_quantity = $filter['quantity'][1];
        return $query->whereBetween('quantity', [$min_quantity, $max_quantity]);


    }

    //-------------------------------------------------

    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }

    //-------------------------------------------------

    public static function createVariation($request)
    {
        $permission_slug = 'can-update-module';

        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }

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

                $response['errors'][] = "This Variation name '{$value['variation_name']}' already exists.";
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
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
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

        $permission_slug = 'can-update-module';

        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        $input = $request->all();

        $product_id = $input['id'];
        $store_id = $input['vh_st_store_id'];
        $validation = self::validatedVendor($input['vendors']);
        if (!$validation['success']) {
            return $validation;
        }
        $vendor_data = $input['vendors'];

        $active_user = auth()->user();

        foreach ($vendor_data as $key=>$value){

            $product_vendor = ProductVendor::where(['vh_st_vendor_id'=> $value['vendor']['id'], 'vh_st_product_id' => $product_id])->first();

            if($product_vendor){
                $response['errors'][] = "This Vendor '{$value['vendor']['name']}' already exists.";
                return $response;
            }

            $item = new ProductVendor();
            $item->vh_st_product_id = $product_id;
            $item->vh_st_vendor_id = $value['vendor']['id'];

            $item->added_by = $active_user->id;

            $item->can_update = $value['can_update'];

            $item->taxonomy_id_product_vendor_status = $value['status']['id'];
            if($value['status_notes'])
            {
                $item->status_notes = $value['status_notes'];
            }

            $item->is_active = 1;
            $item->save();
            $item->storeVendorProduct()->attach([$store_id]);
        }

        $response = self::getItem($product_id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
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
            $error_message = "This name already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if slug exist
        $item = self::where('slug', $inputs['slug'])->withTrashed()->first();


        if ($item) {
            $error_message = "This slug already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        $item = new self();

        $item->fill($inputs);

        $item->quantity = 0;
        if(isset($item->seo_meta_keyword))
        {
            $item->seo_meta_keyword = json_encode($inputs['seo_meta_keyword']);
        }

        $item->slug = Str::slug($inputs['slug']);

        $item->launch_at = Carbon::parse($item->launch_at)->format('Y-m-d');
        $item->available_at = Carbon::parse($item->available_at)->format('Y-m-d');

        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
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
        $list->brandFilter($request->filter);
        $list->dateFilter($request->filter);
        $list->brandFilter($request->filter);
        $list->productTypeFilter($request->filter);
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
            'type.required' => trans("vaahcms-general.action_type_is_required"),
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
        $response['messages'][] = trans("vaahcms-general.action_successful");

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
            'type.required' => trans("vaahcms-general.action_type_is_required"),
            'items.required' => trans("vaahcms-general.select_items"),
        );

        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        foreach ($items_id as $item_id)
        {
            self::deleteRelatedRecords($item_id);
        }

        self::whereIn('id', $items_id)->forceDelete();
        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

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
                    foreach ($items_id as $item_id) {

                        self::deleteRelatedRecords($item_id);
                    }
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
                $user_id = auth()->user()->id;
                $list->update(['deleted_by' => $user_id]);
                $list->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->update(['deleted_by' => null]);
                $list->restore();
                break;
            case 'delete-all':
                $items_id = self::withTrashed()->pluck('id')->toArray();

                foreach ($items_id as $item_id)
                {
                    self::deleteRelatedRecords($item_id);
                }
                self::withTrashed()->forceDelete();
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
        $response['messages'][] = trans("vaahcms-general.action_successful");

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
            $response['errors'][] = trans("vaahcms-general.record_not_found_with_id").$id;
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

        $item['launch_at'] = date('Y-m-d', strtotime($item['launch_at']));
        $item['available_at'] = date('Y-m-d', strtotime($item['available_at']));
        $item['seo_meta_keyword'] = json_decode($item['seo_meta_keyword']);
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
            $error_message = "This name already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if slug exist
        $item = self::where('id', '!=', $id)
            ->withTrashed()
            ->where('slug', $inputs['slug'])->first();

        if ($item) {
            $error_message = "This slug already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->launch_at = Carbon::parse($item->launch_at)->addDay()->toDateString();
        $item->available_at = Carbon::parse($item->available_at)->addDay()->toDateString();
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }
        self::deleteRelatedRecords($item->id);
        $item->forceDelete();
        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans("vaahcms-general.record_has_been_deleted");

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

        $query = $request['filter']['q']['query'] ?? null;

        if($query === null)
        {
            $brands = Brand::where('is_active', 1)->take(10)
                ->get();
        }

        else{

            $brands = Brand::where('name', 'like', "%$query%")->where('is_active', 1)
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
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
            'summary' => 'max:100',
            'vh_st_store_id'=> 'required',
            'taxonomy_id_product_type'=> 'required',
            'details' => 'max:250',
            'seo_title' => 'max:100',
            'seo_meta_description' => 'max:250',
            'seo_meta_keyword' => 'max:20',
            'seo_meta_keyword.*' => 'max:50',
            'taxonomy_id_product_status'=> 'required',
            'status_notes' => 'max:250',
            'quantity' => '',
            'launch_at' => 'required_without_all:quantity,available_at,0',
            'available_at' => 'required_without_all:quantity,launch_at,0',
        ],
            [    'name.required' => 'The Name field is required',
                 'name.max' => 'The Name field may not be greater than :max characters',
                'slug.required' => 'The Slug field is required',
                'slug.max' => 'The Slug field may not be greater than :max characters',
                'summary.max' => 'The Summary field may not be greater than :max characters',
                'details.max' => 'The Details field may not be greater than :max characters',
                'seo_title.max' => 'The Seo title field may not be greater than :max characters',
                'seo_meta_description.max' => 'The Seo Description field may not be greater than :max characters',
                'seo_meta_keyword.max' => 'The Seo Keywords field may not have greater than :max keywords',
                'seo_meta_keyword.*' => 'The Seo Keyword field may not be greater than :max characters',
                'taxonomy_id_product_status.required' => 'The Status field is required',
                'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
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
            if(isset($item->seo_meta_keyword))
            {
                $item->seo_meta_keyword = json_encode($inputs['seo_meta_keyword']);
            }
            $item->slug = Str::slug($inputs['slug']);

            $item->launch_at = Carbon::parse($item->launch_at)->format('Y-m-d');
            $item->available_at = Carbon::parse($item->available_at)->format('Y-m-d');
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
        $max_chars = rand(5,100);
        $inputs['name']=$faker->text($max_chars);

        // fill the product summary field here
        $max_summary_chars = rand(5,100);
        $inputs['summary']=$faker->text($max_summary_chars);

        // fill the product details field here
        $max_details_chars = rand(5,250);
        $inputs['details']=$faker->text($max_details_chars);

        // fill the Seo title field here
        $max_title_chars = rand(5,50);
        $inputs['seo_title']=$faker->text($max_title_chars);

        // fill the Seo Description field here
        $max_seo_description_chars = rand(5,250);
        $inputs['seo_meta_description']=$faker->text($max_seo_description_chars);

        //fill the available at and launch at fields here

        $inputs['available_at'] = $faker->dateTimeBetween('now', '+1 year')->format('Y-m-d');

        $inputs['launch_at'] = $faker->dateTimeBetween('now', '+1 year')->format('Y-m-d');


        // fill the Seo Keywords field here
        $max_seo_keywords = rand(2,10);
        $seo_key_array = [];
        foreach (range(1, $max_seo_keywords) as $index) {
            $seo_key_array[] = $faker->word;
        }
        $inputs['seo_meta_keyword']=$seo_key_array;

        // fill the Seo title field here
        $max_title_chars = rand(5,50);
        $inputs['seo_title']=$faker->text($max_title_chars);

        // fill the store field here
        $stores = Store::where('is_active',1)->get();
        if ($stores->count() > 0) {
            $store_ids = $stores->pluck('id')->toArray();
            $store_id = $store_ids[array_rand($store_ids)];
            $store = $stores->where('id', $store_id)->first();
            $inputs['store'] = $store;
            $inputs['vh_st_store_id'] = $store_id;
        }

        $default_brand = Brand::where(['is_active' => 1, 'is_default' => 1])->get(['id','name', 'slug', 'is_default'])->first();
        if($default_brand !== null)
        {
            $inputs['brand'] = $default_brand;
            $inputs['vh_st_brand_id'] = $default_brand->id;
        }


        // fill the taxonomy status field here
        $taxonomy_status = Taxonomy::getTaxonomyByType('product-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        $status_id = $status_ids[array_rand($status_ids)];
        $inputs['taxonomy_id_product_status'] = $status_id;
        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['status']=$status;

        $inputs['is_active'] = 1;
        $inputs['is_featured_on_home_page'] = rand(0,1);
        $inputs['is_featured_on_category_page'] = rand(0,1);
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

    public static function searchProductVendor($request)
    {


        $vendors = Vendor::with('status')
            ->where('is_active', 1)
            ->select('id', 'name','slug','taxonomy_id_vendor_status');

        if ($request->has('query') && $request->input('query')) {

            $vendors->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $vendors = $vendors->limit(10)->get();

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

    //----------------------------------------------------

    public function scopeStoreFilter($query, $filter)
    {
        if(!isset($filter['stores'])
            || is_null($filter['stores'])
            || $filter['stores'] === 'null'
        )
        {
            return $query;
        }

        $store = $filter['stores'];
        $query->whereHas('store', function ($query) use ($store) {
            $query->whereIn('slug', $store);
        });

    }

    //-------------------------------------------------

    public function scopeBrandFilter($query, $filter)
    {
        if(!isset($filter['brands'])
            || is_null($filter['brands'])
            || $filter['brands'] === 'null'
        )
        {
            return $query;
        }

        $brand = $filter['brands'];
        $query->whereHas('brand', function ($query) use ($brand) {
            $query->whereIn('slug', $brand);
        });

    }

    //-------------------------------------------------

    public function scopeProductTypeFilter($query, $filter)
    {
        if(!isset($filter['product_types'])
            || is_null($filter['product_types'])
            || $filter['product_types'] === 'null'
        )
        {
            return $query;
        }
        $product_type = $filter['product_types'];
        $query->whereHas('type', function ($query) use ($product_type) {
            $query->whereIn('slug', $product_type);
        });

    }

    //-------------------------------------------------

    public static function searchVendorUsingUrlSlug($request)
    {
        $query = $request['filter']['vendor'];

            $vendors = Vendor::whereIn('name',$query)
                ->orWhereIn('slug',$query)
                ->select('id','name','slug')->get();

            $response['success'] = true;
            $response['data'] = $vendors;
            return $response;
    }

    //-------------------------------------------------

    public static function searchBrandUsingUrlSlug($request)
    {

        $query = $request['filter']['brand'];
        $brands = Brand::whereIn('name',$query)
            ->orWhereIn('slug',$query)
            ->select('id','name','slug')->get();

        $response['success'] = true;
        $response['data'] = $brands;
        return $response;
    }

    //-------------------------------------------------

    public static function searchVariationUsingUrlSlug($request)
    {

        $query = $request['filter']['variation'];

        $variations = ProductVariation::whereIn('name',$query)
            ->orWhereIn('slug',$query)
            ->select('id','name','slug')->get();

        $response['success'] = true;
        $response['data'] = $variations;
        return $response;
    }

    //-------------------------------------------------

    public static function searchStoreUsingUrlSlug($request)
    {

        $query = $request['filter']['store'];
        $stores = Store::whereIn('name',$query)
            ->orWhereIn('slug',$query)
            ->select('id','name','slug')->get();

        $response['success'] = true;
        $response['data'] = $stores;
        return $response;
    }

    //-------------------------------------------------

    public static function searchProductTypeUsingUrlSlug($request)
    {

        $query = $request['filter']['product_type'];
        $product_types = TaxonomyType::getFirstOrCreate('product-types');
        $item = Taxonomy::whereNotNull('is_active')
            ->where('vh_taxonomy_type_id',$product_types->id)
            ->whereIn('slug',$query)
            ->select('id','name','slug')
            ->get();
        $response['success'] = true;
        $response['data'] = $item;
        return $response;

    }

    //-------------------------------------------------

    public static function searchVendor($request)
    {

        $vendors = Vendor::select('id', 'name','slug')->where('is_active',1);
        if ($request->has('query') && $request->input('query')) {

            $vendors->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $vendors = $vendors->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $vendors;
        return $response;

    }

    //-------------------------------------------------

    public static function deleteRelatedRecords($id)
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }
        self::deleteProductVendor($item->id);
        self::deleteProductVariation($item->id);
        self::deleteProductMedia($item->id);
        self::deleteProductPrice($item->id);
        self::deleteProductStock($item->id);

    }

    //-------------------------------------------------
    public static function deleteProductVendor($id)
    {
        $response=[];
        $is_exist = ProductVendor::where('vh_st_product_id',$id)
            ->withTrashed()
            ->get();

        if($is_exist){
            ProductVendor::where('vh_st_product_id',$id)->withTrashed()->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;
    }

    //-------------------------------------------------

    public static function deleteProductVariation($id){

        $response=[];
        $is_exist = ProductVariation::where('vh_st_product_id',$id)
            ->withTrashed()
            ->get();
        if($is_exist){
            $item_ids = ProductVariation::where('vh_st_product_id',$id)->withTrashed()->pluck('id');
            foreach ($item_ids as $item_id)
            {
                ProductAttribute::deleteProductVariation($item_id);
            }
            ProductVariation::where('vh_st_product_id',$id)->withTrashed()->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }

    //-------------------------------------------------
    public static function deleteProductMedia($id){

        $response=[];
        $is_exist = ProductMedia::where('vh_st_product_id',$id)
            ->withTrashed()
            ->get();
        if($is_exist){
            ProductMedia::where('vh_st_product_id',$id)->withTrashed()->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }

    //-------------------------------------------------

    public static function deleteProductPrice($id){

        $response=[];
        $is_exist = ProductPrice::where('vh_st_product_id',$id)
            ->withTrashed()
            ->get();
        if($is_exist){
            ProductPrice::where('vh_st_product_id',$id)->withTrashed()->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }

    //-------------------------------------------------

    public static function deleteProductStock($id){

        $response=[];
        $is_exist = ProductStock::where('vh_st_product_id',$id)
            ->withTrashed()
            ->get();
        if($is_exist){
            ProductStock::where('vh_st_product_id',$id)->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }

    //----------------------------------------------------------

    public static function defaultStore($request)
    {
        $default_store = Store::where(['is_active' => 1, 'is_default' => 1])->first();

        $response['success'] = true;
        $response['data'] = $default_store;

        return $response;
    }


    public static function searchUsers($request){
        $active_users = User::select('id', 'first_name', 'last_name','display_name','email','phone')
            ->where('is_active', 1);

        if ($request->has('query') && $request->input('query')) {
            $query = $request->input('query');

            $active_users->where(function ($q) use ($query) {
                $q->where('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('phone', 'LIKE', '%' . $query . '%');
            });
        }

        $users = $active_users->limit(10)->get()->map(function ($user) {
            $user['name'] = $user['display_name'] ?? '';
            return $user;
        });

        $response['success'] = true;
        $response['data'] = $users;
        return $response;

    }

    public static function saveUserInfo($request){
        $user_data = $request->only(['first_name', 'last_name', 'display_name', 'email', 'phone']);

        if (!$user_data) {
            $error_message = "Please enter valid user";
            $response['errors'][] = $error_message;
            return $response;
        }

            $user = User::firstOrCreate(['email' => $user_data['email']], $user_data);
            $existing_cart = Cart::where('vh_user_id', $user->id)->first();
            if ($existing_cart) {
                $error_message = "This user already have a cart";
                $response['errors'][] = $error_message;
                return $response;
            }
            $cart = new Cart();
            $cart->vh_user_id = $user->id;
            $cart->save();

        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        $response['data']=$user;
        return $response;


    }
}
