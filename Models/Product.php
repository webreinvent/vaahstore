<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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


    public function productCategories()
    {
        return $this->belongsToMany(Category::class, 'vh_st_product_categories', 'vh_st_product_id', 'vh_st_category_id');
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
            ->with('productAttributes')
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
    public function cartsProduct()
    {
        return $this->belongsToMany(Cart::class, 'vh_st_cart_products', 'vh_st_product_id', 'vh_st_cart_id')
            ->withPivot('vh_st_product_variation_id', 'quantity');
    }
    //-------------------------------------------------
    public function wishlists()
    {
        return $this->belongsToMany(Wishlist::class, 'vh_st_wishlist_products', 'vh_st_product_id', 'vh_st_wishlist_id');
    }
    //-------------------------------------------------
    public  function medias()
    {
        return $this->belongsToMany(ProductMedia::class, 'vh_st_product_variation_medias', 'vh_st_product_id', 'vh_st_product_media_id')->withTrashed()
            ->withPivot('id');
    }
    //-------------------------------------------------
    public function productVariationMedia()
    {
        return $this->belongsToMany(ProductVariation::class, 'vh_st_product_variation_medias', 'vh_st_product_id', 'vh_st_product_variation_id')
            ->withPivot('vh_st_product_media_id');
    }
    //-------------------------------------------------

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'vh_st_product_id');
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
            !isset($filter['min_quantity']) ||
            is_null($filter['min_quantity']) ||
            !isset($filter['max_quantity']) ||
            is_null($filter['max_quantity'])
        ) {
            // If any of them are null, return the query without applying any filter
            return $query;
        }


        $min_quantity = $filter['min_quantity'];
        $max_quantity = $filter['max_quantity'];
        return $query->whereBetween('quantity', [$min_quantity, $max_quantity]);


    }

    //-------------------------------------------------

    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }

    //-------------------------------------------------

    public static function generateVariation($request,$id)
    {
        $permission_slug = 'can-update-module';

        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $input = $request->all();
        $product_id = $id;
        $item = self::where('id', $id)
            ->first();

        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_not_found_with_id") . $id;
            return $response;
        }
        $validation = self::validatedVariation($input['product_variations']);
        if (!$validation['success']) {
            return $validation;
        }

        $all_variation = $input['product_variations']['structured_variations'];
        $all_attribute = $input['product_variations']['all_attribute_names'];

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
            $item->price = 0;
            $taxonomy_status_id = Taxonomy::getTaxonomyByType('product-variation-status')->where('name', 'Pending')->pluck('id')->first();
            $item->taxonomy_id_variation_status = $taxonomy_status_id;
            $item->vh_st_product_id = $product_id;
            $item->is_active = 1;
            if (isset($value['is_default']) && $value['is_default']) {
                ProductVariation::where('vh_st_product_id', $product_id)
                    ->where('is_default', 1)
                    ->update(['is_default' => 0]);
                $item->is_default = 1;
            }
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

        if (isset($variation['structured_variations']) && !empty($variation['structured_variations'])){
            $error_message = [];
            $all_variation = $variation['structured_variations'];
            $all_arrtibute = $variation['all_attribute_names'];

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

                if (!isset($value['id']) || empty($value['id'])) {
                    array_push($error_message, 'Vendor ID is required.');
                }
                if (!isset($value['name']) || empty($value['name'])) {
                    array_push($error_message, 'Vendor name is required.');
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
                'errors' => ['Vendors data is empty.'],
            ];
        }
    }

    //-------------------------------------------------
    public static function attachVendors($request,$id){

        $permission_slug = 'can-update-module';

        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        $input = $request->all();
        $product_id = $id;
        $store = $request->input('store');
        $vendor_data = $request->input('vendors');
        $validation = self::validatedVendor($vendor_data);
        if (!$validation['success']) {
            return $validation;
        }


        $active_user = auth()->user();

        foreach ($vendor_data as $key=>$vendor){
            $product_vendor = ProductVendor::where(['vh_st_vendor_id'=> $vendor['id'], 'vh_st_product_id' => $product_id])->first();

            if($product_vendor){
                $response['errors'][] = "This Vendor '{$vendor['name']}' already exists.";
                return $response;
            }

            $item = new ProductVendor();
            $item->vh_st_product_id = $product_id;
            $item->vh_st_vendor_id = $vendor['id'];

            $item->added_by = $active_user->id;

            $item->can_update = $vendor['can_update'];

            $item->taxonomy_id_product_vendor_status = $vendor['taxonomy_id_vendor_status'];
            if($vendor['status_notes'])
            {
                $item->status_notes = $vendor['status_notes'];
            }

            $item->is_active = 1;
            $item->save();
            $item->storeVendorProduct()->attach([$store['id']]);
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


        if (isset($inputs['categories'])) {
            $selected_category_ids = array_keys(array_filter($inputs['categories'], function($value) {
                return $value === true;
            }));

            $item->productCategories()->attach($selected_category_ids, ['vh_st_product_id' => $item->id]);
        }



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


    public function scopeCategoryFilter($query, $filter)
    {
        if (isset($filter['category']) && is_array($filter['category'])) {
            $categories = $filter['category'];

            $category_slugs = array_filter($categories, function ($value) {
                return !is_numeric($value);
            });

            $category_ids = array_filter($categories, function ($value) {
                return is_numeric($value);
            });

            if (!empty($category_slugs)) {
                $category_ids_from_slugs = Category::whereIn('slug', $category_slugs)->pluck('id')->toArray();
                $category_ids = array_merge($category_ids, $category_ids_from_slugs);
            }

            $subCategory_ids = Category::whereIn('parent_id', $category_ids)->pluck('id')->toArray();

            $all_category_ids = array_merge($category_ids, $subCategory_ids);

            $query->whereHas('productCategories', function ($q) use ($all_category_ids) {
                $q->whereIn('vh_st_categories.id', $all_category_ids);
            });
        }

        return $query;
    }








    //-------------------------------------------------


    public function scopePriceFilter($query, $filter)
    {
        $min_price = $filter['min_price'] ?? null;
        $max_price = $filter['max_price'] ?? null;

        if ($min_price !== null || $max_price !== null) {
            $product_price_query = ProductPrice::query();
            if ($min_price !== null) {
                $product_price_query->where('amount', '>=', $min_price);
            }

            if ($max_price !== null) {
                $product_price_query->where('amount', '<=', $max_price);
            }

            $product_ids_from_price = $product_price_query->pluck('vh_st_product_id')->toArray();

            $product_variation_price_query = ProductVariation::query();

            if ($min_price !== null) {
                $product_variation_price_query->where('price', '>=', $min_price);
            }

            if ($max_price !== null) {
                $product_variation_price_query->where('price', '<=', $max_price);
            }

            $product_ids_from_variation = $product_variation_price_query->pluck('vh_st_product_id')->toArray();

            $product_ids = array_merge($product_ids_from_price, $product_ids_from_variation);

            $query->whereIn('id', $product_ids);
        }

        return $query;
    }
    //-------------------------------------------------

    public function scopeFeaturedHomePageFilter($query, $filter)
    {
        if (isset($filter['featured_on_homepage']) && $filter['featured_on_homepage'] === 'true') {
            return $query->where('is_featured_on_home_page', 1);
        }
        return $query;
    }
    //-------------------------------------------------

    public function scopeFeaturedCategoryPageFilter($query, $filter)
    {
        if (isset($filter['featured_on_category_page']) && $filter['featured_on_category_page'] === 'true') {
            return $query->where('is_featured_on_category_page', 1);
        }
        return $query;
    }

    //-------------------------------------------------
    public function scopeNewArrivalsFilter($query, $filter)
    {
        if (isset($filter['new_arrivals']) && $filter['new_arrivals'] === 'true') {
            if (isset($filter['from'], $filter['to'])) {
                $from = Carbon::parse($filter['from'])->startOfDay();
                $to = Carbon::parse($filter['to'])->endOfDay();
                $query->whereBetween('available_at', [$from, $to]);
            }
        }

    }
    //-------------------------------------------------
    public function scopeTopSellingsFilter($query, $filter)
    {
        if (isset($filter['top_selling']) && $filter['top_selling'] === 'true') {
            $query->whereHas('orderItems', function ($q) {
                $q->selectRaw('vh_st_product_id, SUM(quantity) as total_sales')
                    ->groupBy('vh_st_product_id')
                    ->orderByDesc('total_sales');
            });
        }

        return $query;
    }


    //-------------------------------------------------
    public static function getList($request)
    {
        $include = request()->query('include', []);
        $exclude = request()->query('exclude', []);
        $user = null;
        $cart_records = 0;
        if ($user_id = session('vh_user_id')) {
            $user = User::find($user_id);
            if ($user) {
                $cart = self::findOrCreateCart($user);
                $cart_records = $cart->products()->count();
            }
        }

        $relationships = ['brand', 'store', 'type', 'status',  'productVendors', 'productCategories'];
        foreach ($include as $key => $value) {
            if ($value === 'true') {
                $keys = explode(',', $key); // Split comma-separated values
                foreach ($keys as $relationship) {
                    $relationship = trim($relationship);
                    if (method_exists(self::class, $relationship)) {
                        $relationships[] = $relationship;
                    }
                }
            }
        }
        $list = self::getSorted($request->filter)->with($relationships)->withCount('productVariations');
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
        $list->categoryFilter($request->filter);
        $list->priceFilter($request->filter);
        $list->featuredHomePageFilter($request->filter);
        $list->featuredCategoryPageFilter($request->filter);
        $list->newArrivalsFilter($request->filter);
        $list->topSellingsFilter($request->filter);

        if ($request->has('ids')) {
            $ids = json_decode($request->ids, true);  // Decode the JSON array
            if (is_array($ids)) {
                $list = $list->whereIn('id', $ids);
            }
        }

        $rows = config('vaahcms.per_page');

        if($request->has('rows'))
        {
            $rows = $request->rows;
        }

        $list = $list->paginate($rows);
        $keys_to_exclude = [];
        foreach ($exclude as $key => $value) {
            if ($value === 'true') {
                $keys_to_exclude = array_merge($keys_to_exclude, array_map('trim', explode(',', $key)));
            }
        }

        $keys_to_exclude = array_unique($keys_to_exclude);
        foreach($list as $item) {

            $item->product_price_range = self::getPriceRangeOfProduct($item->id)['data'];
            $message = self::getVendorsListForPrduct($item->id)['message'];
            $item->is_attached_default_vendor = $message ? false : null;

            foreach ($keys_to_exclude as $single_key) {
                if (isset($item[$single_key])) {
                    unset($item[$single_key]);
                }
            }
            $all_grouped_attributes = [];
            foreach ($item->productVariations as $variation) {
                self::groupedAttributes($variation, $all_grouped_attributes);
            }
            unset($item->productVariations);
            $item->grouped_attributes = collect($all_grouped_attributes)->map(function ($values, $key) {
                return [
                    'attribute' => $key,
                    'values' => array_unique($values),
                ];
            })->values();

        }
        $response['success'] = true;
        $response['data'] = $list;
        $response['active_cart_user'] = $user;
        if ($user) {
            $response['active_cart_user']['cart_records'] = $cart_records;
            $response['active_cart_user']['vh_st_cart_id'] = $cart->id;
        }
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
        self::with('productCategories')->whereIn('id', $items_id)->each(function ($item) {
            $item->productCategories()->detach();
        });
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
                $items = self::withTrashed()->get();
                $items_id = self::withTrashed()->pluck('id')->toArray();
                foreach ($items as $item) {
                    $item->productCategories()->detach();
                }
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
        $include = request()->query('include', []);
        $exclude = request()->query('exclude', []);

        $relationships = [
            'createdByUser', 'updatedByUser', 'deletedByUser',
            'brand', 'store', 'type', 'status', 'productCategories'
        ];

           foreach ($include as $key => $value) {
            if ($value === 'true') {
                $keys = explode(',', $key); // Split comma-separated relationships
                foreach ($keys as $relationship) {
                    $relationship = trim($relationship);
                    if (method_exists(self::class, $relationship)) {
                        $relationships[] = $relationship; // Add to relationships if method exists
                    }
                }
            }
        }

        $item = self::where('id', $id)
            ->with($relationships)
            ->withTrashed()
            ->first();

        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_not_found_with_id") . $id;
            return $response;
        }
        $all_grouped_attributes = [];
        if ($item->productVariations){
            foreach ($item->productVariations as $variation) {
                self::groupedAttributes($variation, $all_grouped_attributes);
            }
        }
        $array_item = $item->toArray();


        $product_vendor = [];
        if (!empty($array_item['product_vendors'])) {
            foreach ($array_item['product_vendors'] as $vendor) {
                $new_array = [
                    'id' => $vendor['id'],
                    'is_selected' => false,
                    'can_update' => $vendor['can_update'] == 1,
                    'status_notes' => $vendor['status_notes'],
                    'vendor' => Vendor::where('id', $vendor['vh_st_vendor_id'])
                            ->get(['id', 'name', 'slug', 'is_default'])
                            ->toArray()[0] ?? [],
                    'status' => Taxonomy::where('id', $vendor['taxonomy_id_product_vendor_status'])
                            ->get()
                            ->toArray()[0] ?? [],
                ];
                array_push($product_vendor, $new_array);
            }
            $array_item['vendors'] = $product_vendor;
        } else {
            $array_item['vendors'] = [];
        }

        $array_item['launch_at'] = date('Y-m-d', strtotime($array_item['launch_at']));
        $array_item['available_at'] = date('Y-m-d', strtotime($array_item['available_at']));
        $array_item['seo_meta_keyword'] = json_decode($array_item['seo_meta_keyword']);
        $array_item['product_variation'] = null;
        $array_item['all_variation'] = [];
        $array_item['grouped_attributes'] = collect($all_grouped_attributes)->map(function ($values, $key) {
            return [
                'attribute' => $key,
                'values' => array_unique($values),
            ];
        })->values();
        $keys_to_exclude = [];
        foreach ($exclude as $key => $value) {
            if ($value === 'true') {
                $keys = explode(',', $key); // Split comma-separated exclusions
                foreach ($keys as $exclude_key) {
                    $exclude_key = trim($exclude_key);
                    if (array_key_exists($exclude_key, $array_item)) {
                        $keys_to_exclude[] = $exclude_key;
                    }
                }
            }
        }
        foreach ($keys_to_exclude as $key_to_remove) {
            unset($array_item[$key_to_remove]);
        }

        $response['success'] = true;
        $response['data'] = $array_item;

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
        if (isset($inputs['categories'])) {
            $selected_category_ids = array_keys(array_filter($inputs['categories'], function($value) {
                return $value === true;
            }));
            $item->productCategories()->sync($selected_category_ids);
        }

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
        $categories_ids = $item->categories->pluck('id')->toArray();
        foreach ($categories_ids as $category_id) {
            $item->productCategories()->detach($category_id);
        }
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
            if (isset($inputs['category'])) {
                $item->productCategories()->attach($inputs['category']->id, ['vh_st_product_id' => $item->id]);
            }
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

        $random_category = Category::whereNull('parent_id') ->where('is_active', 1)->inRandomOrder()->first();
        $inputs['category'] = $random_category;


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

    //----------------------------------------------------------

    public static function searchUsers($request){
        $active_customer = User::select('id', 'first_name', 'last_name','display_name','email','phone')
            ->whereHas('activeRoles', function ($query) {
                $query->where('slug', 'customer');
            })
            ->where('is_active', 1);

        if ($request->has('query') && $request->input('query')) {
            $query = $request->input('query');

            $active_customer->where(function ($q) use ($query) {
                $q->where('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('phone', 'LIKE', '%' . $query . '%')
                    ->orWhere('uuid', 'LIKE', '%' . $query . '%');
            });
        }

        $users = $active_customer->limit(10)->get()->map(function ($user) {
            $user['name'] = $user['display_name'] ?? '';
            return $user;
        });

        $response['success'] = true;
        $response['data'] = $users;
        return $response;

    }

    //----------------------------------------------------------




    public static function addProductToCart($request)
    {
        $response = [];
        $default_vendor = Vendor::where('is_default', 1)->first();
        $errors = [];
        $messages = [];

        // Handle user data (optional)
        $user_info = $request->input('user');
        $user = is_array($user_info) && isset($user_info['id'])
            ? self::findOrCreateUser(['id' => $user_info['id']])
            : null;

        // Validation for each product
        $validated_products = [];
        foreach ($request->products as $product_data) {
            $product_id = $product_data['id'];
            $active_selected_vendor = self::getPriceRangeOfProduct($product_id)['data'] ?? null;
            $selected_vendor = $active_selected_vendor['selected_vendor'] ?? $default_vendor;

            if (!$selected_vendor) {
                $errors[] = "Product ID {$product_id} is out of stock.";
                continue;
            }

            $product = Product::find($product_id);
            if (!$product) {
                $errors[] = "Invalid product ID {$product_id}.";
                continue;
            }

            $product_with_variants = self::getDefaultVariation($product);
            if (!$product_with_variants || !isset($product_with_variants['variation_id'])) {
                $errors[] = "No default variation found for Product ID {$product_id}.";
                continue;
            }

            $quantity = $product_data['quantity'] ?? 1;

            // Store valid product data for cart creation
            $validated_products[] = [
                'product' => $product,
                'variation' => $product_with_variants,
                'vendor' => $selected_vendor,
                'quantity' => $quantity,
            ];
        }

        // Return errors if validation failed for any product
        if (!empty($errors)) {
            return [
                'errors' => $errors,
                'data' => null,
            ];
        }

        // Create the cart and add validated products
        $cart = self::findOrCreateCart($user);
        foreach ($validated_products as $item) {
            self::handleCart(
                $cart,
                $item['product'],
                $item['variation'],
                $item['vendor'],
                $item['quantity']
            );
        }

        // Update user session if applicable
        if ($user) {
            self::updateSession($user);
        }

        // Prepare success response
        $messages[] = trans("vaahcms-general.saved_successfully");
        return [
            'messages' => $messages,
            'data' => [
                'user' => $user,
                'cart' => $cart,
            ],
        ];
    }


    //----------------------------------------------------------


    private static function handleCart($cart, $product, $product_with_variants, $selected_vendor,$quantity = 1)
    {
        if ($cart->products->contains($product->id)) {
            $existing_cart_item = self::findCartItem($cart, $product_with_variants['variation_id'], $selected_vendor['id']??null);
            if ($existing_cart_item) {
                self::updateQuantity($cart, $product->id,$product_with_variants['variation_id'], $selected_vendor,$quantity);
            } else {
                self::attachProductToCart($cart, $product, $product_with_variants, $selected_vendor['id'],$quantity);
            }
        } else {
            self::attachProductToCart($cart, $product, $product_with_variants, $selected_vendor['id'],$quantity);
        }
    }
    //----------------------------------------------------------

    private static function findCartItem($cart, $variation_id, $selected_vendor_id)
    {
        return $cart->productVariations()
            ->where('vh_st_product_variation_id', $variation_id)
            ->where('vh_st_vendor_id', $selected_vendor_id )
            ->first();
    }
    //----------------------------------------------------------

    private static function updateQuantity($cart, $product_id,$variation_id, $selected_vendor,$quantity)
    {
            $pivot_record = $cart->products()
                        ->where('vh_st_product_id', $product_id)
                        ->where('vh_st_product_variation_id', $variation_id)
                        ->where('vh_st_vendor_id', $selected_vendor['id'])
                        ->withPivot('id', 'quantity')
                        ->first();
            if ($pivot_record) {
                        $pivot_record->pivot->quantity += $quantity;
                        $pivot_record->pivot->save();
            }
    }
    //----------------------------------------------------------

    private static function updateSession($user)
    {
        if (!Session::has('vh_user_id')) {
            Session::put('vh_user_id', $user->id);
        }
    }



    //----------------------------------------------------------

    protected static function findOrCreateUser($user_data)
    {
        $user = User::findOrFail($user_data['id']);
        return $user;
    }
    //----------------------------------------------------------

    public static function findOrCreateCart($user)
    {

        if ($user) {

            $existing_cart = Cart::where('vh_user_id', $user->id)->first();

            if ($existing_cart) {
                return $existing_cart;
            } else {
                $cart = new Cart();
                $cart->vh_user_id = $user->id;
                $cart->save();
                return $cart;
            }
        } else {
            $cart = new Cart();
            $cart->save();

        }

        return $cart;
    }
    //----------------------------------------------------------

    protected static function attachProductToCart($cart, $product, $product_with_variants,$selected_vendor_id, $quantity = 1)
    {
        if ($product_with_variants) {
            $cart->products()->attach($product->id, [
                'vh_st_product_variation_id' => $product_with_variants['variation_id'],
                'vh_st_vendor_id' => $selected_vendor_id,
                'quantity' =>$quantity
            ]);
        } else {
            $cart->products()->attach($product->id,  ['vh_st_vendor_id' => $selected_vendor_id, 'quantity' => $quantity]);
        }
    }
    //----------------------------------------------------------

    protected static function getDefaultVariation($product)
    {
        $default_variation = $product->productVariations()->where('is_default', 1)->first();

        if (!$default_variation) {
            return null;
        }

        return [
            'variation_id' => $default_variation->id,
            'quantity' => $default_variation->quantity,
            'price' => $default_variation->price,
        ];
    }

    //---------------------------API Method For Cart Generate-------------------------------


    public static function generateCart($request)
    {
        $response = [];
        $default_vendor = Vendor::where('is_default', 1)->first();
        $errors = [];
        $messages = [];

        // Handle user data (optional)
        $user_info = $request->input('user');
        $user = is_array($user_info) && isset($user_info['id'])
            ? self::findOrCreateUser(['id' => $user_info['id']])
            : null;

        $has_valid_product = false;

        $cart = null;
        if ($request->has('uuid') && !empty($request->input('uuid'))) {
            $cart = Cart::findByIdOrUuid($request->input('uuid'))->first();
            if (!$cart) {
                $response['success'] = false;
                $response['errors'][] = "Cart with given UUID {$request->input('cart_uuid')} not found.";
                return $response;
            }
        }
        foreach ($request->products as $product_data) {
            $product_id = $product_data['id'];
            $product = Product::find($product_id);
            if (!$product) {
                $errors[] = "Invalid product ID {$product_id}.";
                continue;
            }
            $variation_id = $product_data['variation_id'] ?? null;
            $product_with_variants = $variation_id
                ? self::getVariationById($product_id, $variation_id)
                : self::getDefaultVariation($product);

            if (!$product_with_variants || !isset($product_with_variants['variation_id'])) {
                $errors[] = "No default variation found for Product ID {$product_id}.";
                continue;
            }

            $selected_vendor = null;

            $selected_vendor = self::getActiveSelectedVendor(
                $product_data['vendor_id'] ?? null,
                $product_id,
                $default_vendor
            );

            if (!$selected_vendor) {
                $errors[] = "No vendor is selected for product ID {$product_id}.";
                continue;
            }

            // Validate product-vendor relationship
            if ($selected_vendor->id !== $default_vendor->id) {
                $product_vendor = $product->productVendors()
                    ->where('vh_st_vendor_id', $selected_vendor->id)
                    ->first();

                if (!$product_vendor) {
                    $errors[] = "product ID {$product_id} is not associated with vendor ID {$selected_vendor->id}.";
                    continue;
                }
            }

            // Check item stock
            $stock_status = self::getItemQuantity($selected_vendor, $product_id, $product_with_variants['variation_id']);

            if (!$stock_status['available'] || $stock_status['quantity'] < ($product_data['quantity'] ?? 1)) {
                $errors[] = "Insufficient stock for variation ID {$product_with_variants['variation_id']} from vendor ID {$selected_vendor->id}.";
                continue;
            }
            // If everything is valid, ensure cart is created
            if (!$cart) {
                $cart = self::findOrCreateCart($user);
            }
            // If everything is valid, save to cart
            $quantity = $product_data['quantity'] ?? 1;
            self::handleCart($cart, $product, $product_with_variants, $selected_vendor, $quantity);
            $has_valid_product = true;
            if ($user) {
                self::updateSession($user);
            }
        }
        if (!$has_valid_product) {
            return [
                'success'=>false,
                'errors' => ['No valid products or variations added to the cart.'],
            ];
        }
        if (!empty($errors)) {
            $response['success'] = false;
            $response['errors'] = $errors;
        }

        $messages[] = trans("vaahcms-general.saved_successfully");
        $response['success'] = true;
        $response['messages'] = $messages;

        $response['data'] = $cart->load('user:id,email,username,phone', 'products')->toArray();

        $response['data']['products'] = collect($response['data']['products'])->map(function ($product) {
            return [
                'id' => $product['id'],
                'name' => $product['name'],
                'vh_st_cart_id' => $product['pivot']['vh_st_cart_id'],
                'vh_st_product_variation_id' => $product['pivot']['vh_st_product_variation_id'],
                'quantity' => $product['pivot']['quantity'],
                'vh_st_vendor_id' => $product['pivot']['vh_st_vendor_id'],
            ];
        })->toArray();

        return $response;


    }
    //----------------------------------------------------------

    protected static function getVariationById($product_id, $variation_id)
    {
        $variation = ProductVariation::where('vh_st_product_id', $product_id)
            ->where('id', $variation_id)
            ->first();

        return $variation
            ? [
                'variation_id' => $variation->id,
                'quantity' => $variation->quantity,
                'price' => $variation->price,
            ]
            : null;
    }
    //----------------------------------------------------------

    public static function getItemQuantity($vendor, $product_id, $variation_id)
    {
        if ($vendor === null || $product_id === null || $variation_id === null) {
            return ['available' => false, 'quantity' => 0];
        }

        $product_stock = $vendor->productStocks()
            ->where('vh_st_product_id', $product_id)
            ->where('vh_st_product_variation_id', $variation_id)
            ->where('is_active', 1)
            ->first();

        if ($product_stock) {
            return ['available' => true, 'quantity' => $product_stock->quantity];
        }

        return ['available' => false, 'quantity' => 0];
    }
    //----------------------------------------------------------

    protected static function getActiveSelectedVendor($input_vendor_id, $product_id, $default_vendor)
    {
        if ($input_vendor_id) {
            return Vendor::find($input_vendor_id);
        }

        $active_selected_vendor = self::getPriceRangeOfProduct($product_id)['data']['selected_vendor'] ?? null;

        return $active_selected_vendor ?: $default_vendor;
    }

    //----------------------------------------------------------

    public static function deleteCategory($request){
        $product_id = $request->vh_st_product_id;
        $category_id = $request->vh_st_category_id;

        $product = Product::find($product_id);

        if (!$product) {
            $response['errors'][] = trans("Product not found");
            return $response;
        }

        $category = Category::find($category_id);

        if (!$category) {
            $response['errors'][] = trans("Category not found");
            return $response;
        }

        $product->productCategories()->detach($category_id);
        $response['data']['product'] = $product;
        $response['messages'][] = trans("vaahcms-general.action_successful");
        return $response;
    }

    //----------------------------------------------------------

    public static function searchCategoryUsingSlug($request)
    {
        $response = [
            'success' => false,
            'data' => false
        ];

        if (!$request->has('filter')) {
            return $response;
        }

        $filter = $request->input('filter');

        if (!isset($filter['category'])) {
            return $response;
        }

        $categories_slug = is_array($filter['category']) ? $filter['category'] : [$filter['category']];

        $categories = Category::with('subCategories')->whereIn('slug', $categories_slug)->get();

        $formatted_data = [];

        foreach ($categories as $category) {
            $formatted_category = [
                'id' => $category->id,
                'uuid' => $category->uuid,
                'name' => $category->name,
                'subCategories' => $category->subCategories->map(function ($subCategory) {
                    return [
                        'id' => $subCategory->id,
                        'name' => $subCategory->name
                    ];
                })->toArray()
            ];

            $formatted_data[$category->slug] = $formatted_category;
        }

        return [
            'success' => true,
            'data' => $formatted_data
        ];
    }


    //----------------------------------------------------------

    public static function getPriceRangeOfProduct($id)
    {
        $preferred_vendor_product_id = static::getPreferredProductVendorIds($id);
        $vendor = static::buildVendorQuery($preferred_vendor_product_id, $id)->first();

        if ($vendor && static::isVendorStockActive($vendor, $id)) {
            $data = static::getVendorPriceAndQuantity($vendor, $id);
        } else {
            $data = static::getRandomVendor($id);
        }

        return ['success' => true, 'data' => $data];
    }
    //----------------------------------------------------------

    protected static function getPreferredProductVendorIds($id)
    {
        return ProductVendor::where('vh_st_product_id', $id)
            ->where('is_preferred', 1)
            ->pluck('vh_st_vendor_id')
            ->toArray();
    }
    //----------------------------------------------------------

    protected static function buildVendorQuery($preferred_vendor_product_id, $id)
    {
        $vendors_query = Vendor::query();

        if (!empty($preferred_vendor_product_id)) {
            $vendors_query->whereIn('id', $preferred_vendor_product_id);
        } else {
            $vendors_query->where('is_default', 1)
                ->orWhere(function ($query) use ($id) {
                    $query->whereHas('productStocks', function ($query) use ($id) {
                        $query->where('vh_st_product_id', $id)
                            ->where('quantity', '>', 0)
                            ->where('is_active', 1);
                    })->whereHas('productPrices', function ($query) use ($id) {
                        $query->where('vh_st_product_id', $id)
                            ->where('amount', '>', 0);
                    });
                });
        }

        return $vendors_query;
    }

    //----------------------------------------------------------

    protected static function isVendorStockActive($vendor, $id)
    {
        return $vendor->productStocks()
            ->where('vh_st_product_id', $id)
            ->where('quantity', '>', 0)
            ->where('is_active', 1)
            ->exists();
    }

    //----------------------------------------------------------

    protected static function getRandomVendor($id)
    {
        $vendor = Vendor::whereHas('productStocks', function ($query) use ($id) {
            $query->where('vh_st_product_id', $id)->where('is_active', 1)
                ->where('quantity', '>', 0);
        })
            ->select('vh_st_vendors.*')
            ->withCount(['productStocks as quantity' => function ($query) use ($id) {
                $query->where('vh_st_product_id', $id)->where('is_active', 1);
            }])
            ->orderByDesc('quantity')
            ->first();

        if ($vendor) {
            $quantity = $vendor->productStocks()
                ->where('vh_st_product_id', $id)
                ->where('is_active', 1)
                ->sum('quantity');

            $price_range = ProductPrice::where('vh_st_vendor_id', $vendor->id)
                ->where('vh_st_product_id', $id)
                ->whereNotNull('amount')
                ->pluck('amount')
                ->toArray();

            if (empty($price_range)) {
                $price_range = ProductVariation::where('vh_st_product_id', $id)
                    ->whereNotNull('price')
                    ->pluck('price')
                    ->toArray();
            }
            $min_price = !empty($price_range) ? min($price_range) : null;
            $max_price = !empty($price_range) ? max($price_range) : null;

            return [
                'price_range' => ($min_price !== null && $min_price === $max_price) ? [$min_price] : [$min_price, $max_price],
                'quantity' => $quantity,
                'selected_vendor' => $vendor,
            ];
        }

        $default_price_range = ProductVariation::where('vh_st_product_id', $id)
            ->whereNotNull('price')
            ->pluck('price')
            ->toArray();
        $min_price = !empty($default_price_range) ? min($default_price_range) : null;
        $max_price = !empty($default_price_range) ? max($default_price_range) : null;


        return [
            'price_range' => ($min_price !== null && $min_price === $max_price)
                ? [$min_price]
                : ($min_price !== null ? [$min_price, $max_price] : [])
        ];
    }

    //----------------------------------------------------------


    protected static function getVendorPriceAndQuantity($vendor, $id)
    {
        $quantity = $vendor->productStocks()
            ->where('vh_st_product_id', $id)
            ->where('is_active', 1)
            ->sum('quantity');

        $price_range = ProductPrice::where('vh_st_vendor_id', $vendor->id)
            ->where('vh_st_product_id', $id)
            ->whereNotNull('amount')
            ->pluck('amount')
            ->toArray();

        if (empty($price_range)) {
            $price_range = ProductVariation::where('vh_st_product_id', $id)
                ->whereNotNull('price')
                ->pluck('price')
                ->toArray();
        }



        $min_price = !empty($price_range) ? min($price_range) : null;
        $max_price = !empty($price_range) ? max($price_range) : null;

        return [
            'price_range' =>($min_price !== null && $min_price === $max_price) ? [$min_price] : [$min_price, $max_price],
            'quantity' => $quantity,
            'selected_vendor' => $vendor,
        ];
    }





    //----------------------------------------------------------


    public static function getVendorsListForPrduct($id)
    {
        $product_vendors = ProductVendor::where('vh_st_product_id', $id)
            ->select('id', 'vh_st_vendor_id', 'is_preferred')
            ->get();

        $vendor_ids = $product_vendors->pluck('vh_st_vendor_id')->toArray();

        $vendors_data = Vendor::whereIn('id', $vendor_ids)
            ->select('id', 'name', 'slug', 'is_default')
            ->get();

        // Check if there are any default vendors missing
        $default_vendor_id = $vendors_data->where('is_default', 1)->pluck('id')->toArray();
        $missing_default_vendor = Vendor::whereNotIn('id', $default_vendor_id)
            ->where('is_default', 1)
            ->select('id', 'name', 'slug', 'is_default')
            ->get();
        $message = $missing_default_vendor->isNotEmpty();

        $vendors = $vendors_data->merge($missing_default_vendor);


        $product_prices = ProductPrice::where('vh_st_product_id', $id)
            ->whereIn('vh_st_vendor_id', $vendor_ids)
            ->get();

        $product_price_range_with_vendors = $product_prices->groupBy('vh_st_vendor_id');

        $vendors->each(function ($vendor) use ($product_vendors, $product_price_range_with_vendors, $id) {
            $quantity = ProductStock::where('vh_st_vendor_id', $vendor->id)
                ->where('vh_st_product_id', $id)->where('is_active', 1)
                ->sum('quantity');

            $vendor->quantity = $quantity;
            $vendor->product_price_range = [];

            $vendor->product_price_range = isset($product_price_range_with_vendors[$vendor->id])
                ? $product_price_range_with_vendors[$vendor->id]->pluck('amount')->toArray()
                : [];

            if (empty($vendor->product_price_range)) {
                // Fetch prices from ProductVariation
                $default_product_price = ProductVariation::where('vh_st_product_id', $id)
                    ->pluck('price')
                    ->toArray();
                // Filter out null or empty prices
                $default_product_price_array = array_filter($default_product_price, function($value) {
                    return $value !== null && $value !== '';
                });
                // Merge ProductVariation prices with existing variation_prices
                $vendor->product_price_range = array_merge($vendor->product_price_range, $default_product_price_array);
            }

            $vendor->product_vendor_id = null;
            $vendor->is_preferred = null;

            $product_vendor = $product_vendors->where('vh_st_vendor_id', $vendor->id)->first();

            if ($product_vendor) {
                $vendor->product_vendor_id = $product_vendor->id;
                $vendor->vh_st_product_id = $id;
                $vendor->is_preferred = $product_vendor->is_preferred;
            }
        });

        return [
            'success' => true,
            'data' => $vendors,
            'message' => $message,
        ];
    }

    //----------------------------------------------------------

    public static function vendorPreferredAction(Request $request, $id, $vendor_id): array
    {

        $product_vendor = ProductVendor::where('vh_st_product_id', $id)
            ->where('vh_st_vendor_id', $vendor_id)
            ->first();

        if (!$product_vendor) {
            return [
                'success' => false,
                'message' => 'Product vendor not found.',
            ];
        }

        $action = $request->get('action');
        $is_preferred = ($action === 'preferred') ? 1 : null;

        ProductVendor::where('vh_st_product_id', $product_vendor->vh_st_product_id)->update(['is_preferred' => null]);
        ProductVendor::where('id', $product_vendor->id)->update(['is_preferred' => $is_preferred]);

        return [
            'success' => true,
            'data' => Product::find($product_vendor->vh_st_product_id),
            'message' => 'Success.',
        ];
    }

    //----------------------------------------------------------

    public static function topSellingProducts($request)
    {
        $default_limit = 5;
        $limit = $request->has('limit') ? (int)$request->limit : $default_limit;
        $start_date = isset($request->start_date) ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
        $end_date = isset($request->end_date) ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $apply_date_range = !isset($request->filter_all) || !$request->filter_all;

        $query = OrderItem::query();
        if ($apply_date_range) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }
        $rows = config('vaahcms.per_page');

        if ($request->has('rows')) {
            $rows = $request->rows;
        }
        $top_selling_products = $query
            ->select('vh_st_product_id')
            ->with(['product' => function ($q) {
                $q->whereNull('deleted_at');
                $q->with('medias','brand');
            }])
            ->groupBy('vh_st_product_id')->paginate($rows);



        $top_selling_products = $top_selling_products->map(function ($item) use ($apply_date_range, $start_date, $end_date) {
            if (!$item->vh_st_product_id) {
                return null;
            }



            $sales_query = OrderItem::where('vh_st_product_id', $item->vh_st_product_id);

            if ($apply_date_range) {
                $sales_query->whereBetween('created_at', [$start_date, $end_date]);
            }

            $total_sales = $sales_query->sum('quantity');
            $products = $item->product;



            if (!$products) {
                return null;
            }

            $product_media_ids = $products->medias
                ? $products->medias->map(function ($media) {
                    return $media->pivot->vh_st_product_media_id ?? null;
                })->filter()
                : collect();

            $image_urls = self::getImageUrls($product_media_ids);
            $brand = $products->brand;
            return [
                'id' => $products->id ?? null,
                'name' => $products->name ?? null,
                'slug' => $products->slug ?? null,
                'total_sales' => $total_sales ?? 0,
                'image_urls' => $image_urls ?? [],
                'brand' => [
                    'id' => $brand->id ?? null,
                    'name' => $brand->name ?? null,
                ],
            ];
        })
            ->filter()
            ->sortByDesc('total_sales');

        if ($apply_date_range) {
            $top_selling_products = $top_selling_products->take($limit);
        }
        return [
            'success' => true,
            'data' => $top_selling_products->values(),
        ];
    }




    //----------------------------------------------------------

    public static function topSellingBrands($request)
    {
        $default_limit = 5;
        $limit = $request->has('limit') ? (int)$request->limit : $default_limit;
        $start_date = isset($request->start_date)
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfDay();
        $end_date = isset($request->end_date)
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();
        $apply_date_range = !isset($request->filter_all) || !$request->filter_all;

        $query = OrderItem::query()
            ->with(['product.brand'])
            ->when($apply_date_range, function ($query) use ($start_date, $end_date) {
                $query->whereBetween('created_at', [$start_date, $end_date]);
            })
            ->get()
            ->groupBy('product.brand.id')
            ->map(function ($items, $brand_id) {
                $brand = $items->first()->product->brand ?? null;
                $total_sales = $items->sum('quantity');

                if ($brand) {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'slug' => $brand->slug,
                        'total_sales' => $total_sales,
                    ];
                }
                return null;
            })
            ->filter()
            ->sortByDesc('total_sales')
            ->take($limit);
        return [
            'success' => true,
            'data' => $query->values(),
        ];

    }


    //----------------------------------------------------------

    public static function topSellingCategories($request)
    {
        $default_limit = 5;
        $limit = $request->has('limit') ? (int)$request->limit : $default_limit;
        $start_date = isset($request->start_date) ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
        $end_date = isset($request->end_date) ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $apply_date_range = !isset($request->filter_all) || !$request->filter_all;
        $query = OrderItem::query();

        if ($apply_date_range) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $top_categories_by_product = $query
            ->select('vh_st_product_id')
            ->with(['product' => function ($query) {
                $query->with(['productCategories.parentCategory']);
            }])
            ->groupBy('vh_st_product_id')
            ->get();

        $top_categories = $top_categories_by_product->flatMap(function ($item) {
            $product = $item->product;

            if ($product && $product->productCategories) {
                return $product->productCategories->map(function ($category) {
                    $final_parent = $category;
                    while ($final_parent && $final_parent->parentCategory) {
                        $final_parent = $final_parent->parentCategory;
                    }
                    return $final_parent;
                });
            }

            return [];
        })
            ->filter()
            ->unique('id')
            ->take($limit)
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'slug' => $category->slug,
                    'name' => $category->name,
                ];
            });
        return [
            'success' => true,
            'data' => $top_categories->values(),
        ];
    }



    //----------------------------------------------------------

    private static function getImageUrls($product_media_ids)
    {
        $image_urls = [];
        foreach ($product_media_ids as $product_media_id) {
            $product_media_image = ProductMediaImage::where('vh_st_product_media_id', $product_media_id)->first();
            if ($product_media_image) {
                $image_urls[] = $product_media_image->url;
            }
        }
        return $image_urls;
    }

    //----------------------------------------------------------

    public static function exportData($request)
    {
        $inputs = $request->all();
        $column_to_export = $request->input('columns', []);
        $is_custom_meta = $request->input('is_export_custom_meta', false);
        if (empty($column_to_export)) {
            return [
                'success' => false,
                'errors' => [trans("vaahcms-general.no_columns_selected")],
            ];
        }
        $rules = [
            'type' => 'required',
        ];
        $messages = [
            'type.required' => trans("vaahcms-general.action_type_is_required"),
        ];
        $validator = \Validator::make($inputs, $rules, $messages);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => errorsToArray($validator->errors()),
            ];
        }

        // Define column headers and mapping
        $header_mapping = [
            'id' => 'Id',
            'name' => 'Name',
            'slug' => 'Slug',
            'summary' => 'Summary',
            'details' => 'Details',
            'quantity' => 'Quantity',
            'taxonomy_id_product_type' => 'Product Type',
            'taxonomy_id_product_status' => 'Product Status',
            'status_notes' => 'Status Notes',
            'vh_st_store_id' => 'Store',
            'vh_st_brand_id' => 'Brand',
            'is_active' => 'Is Active',
            'is_featured_on_home_page' => 'Is Homepage Featured',
            'is_featured_on_category_page' => 'Is Category Page Featured',
            'launch_at' => 'Launch At',
            'available_at' => 'Available At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'deleted_by' => 'Deleted By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
            'product_categories' => 'Categories',
            'product_variations' => 'Product Variations',
        ];

        if ($is_custom_meta) {
            $header_mapping['meta'] = 'Meta';
            $header_mapping['seo_title'] = 'SEO Title';
            $header_mapping['seo_meta_description'] = 'SEO Meta Description';
            $header_mapping['seo_meta_keyword'] = 'SEO Meta Keyword';
            if (!in_array('meta', $column_to_export)) {
                $column_to_export[] = 'meta';
            }
            if (!in_array('seo_title', $column_to_export)) {
                $column_to_export[] = 'seo_title';
            }
            if (!in_array('seo_meta_description', $column_to_export)) {
                $column_to_export[] = 'seo_meta_description';
            }
            if (!in_array('seo_meta_keyword', $column_to_export)) {
                $column_to_export[] = 'seo_meta_keyword';
            }
        }

        if (in_array("export_all_columns", $column_to_export)) {
            $column_to_export = array_keys($header_mapping); // Export all columns
        }

        // Filter columns based on the request
        if (!empty($column_to_export)) {
            $header_mapping = array_filter($header_mapping, function ($key) use ($column_to_export) {
                return in_array($key, $column_to_export);
            }, ARRAY_FILTER_USE_KEY);
        }

        // Fetch records based on provided items or all records
        $records = isset($inputs['items']) && !empty($inputs['items'])
            ? self::withTrashed()->with(['brand', 'store', 'type', 'status', 'productVariations', 'productVendors', 'productCategories'])->whereIn('id', collect($inputs['items'])->pluck('id'))->get()
            : self::withTrashed()->with(['brand', 'store', 'type', 'status', 'productVariations', 'productVendors', 'productCategories'])->get();

        // Initialize CSV content with headers
        $csv_content = implode(',', array_values($header_mapping)) . "\n";

        // Mapping special attributes for export
        $attribute_map = [
            'is_active' => fn($record) => $record->is_active ? 'true' : 'false',
            'is_featured_on_home_page' => fn($record) => $record->is_featured_on_home_page ? 'true' : 'false',
            'is_featured_on_category_page' => fn($record) => $record->is_featured_on_category_page ? 'true' : 'false',
            'created_by' => fn($record) => $record->createdByUser ? $record->createdByUser->email : '',
            'updated_by' => fn($record) => $record->updatedByUser ? $record->updatedByUser->email : '',
            'deleted_by' => fn($record) => $record->deletedByUser ? $record->deletedByUser->email : '',
            'product_categories' => fn($record) => $record->productCategories ? $record->productCategories->pluck('name')->join(', ') : '',
            'product_variations' => fn($record) => $record->productVariations ? $record->productVariations->pluck('name')->join(', ') : '',
            'vh_st_store_id' => fn($record) => $record->store ? $record->store->name : '',
            'vh_st_brand_id' => fn($record) => $record->brand ? $record->brand->name : '',
            'taxonomy_id_product_type' => fn($record) => $record->type ? $record->type->name : '',
            'taxonomy_id_product_status' => fn($record) => $record->status ? $record->status->name : '',
            'meta' => fn($record) => $record->meta ?? '',
            'seo_title' => fn($record) => $record->seo_title ?? '',
            'seo_meta_description' => fn($record) => $record->seo_meta_description ?? '',
            'seo_meta_keyword' => fn($record) => $record->seo_meta_keyword ?? '',
        ];

        foreach ($records as $record) {
            $values = [];

            foreach ($header_mapping as $attribute => $header) {
                if (isset($attribute_map[$attribute])) {
                    $value = $attribute_map[$attribute]($record);
                } else {
                    // Default behavior for other attributes
                    $value = $record->{$attribute} ?? '';
                }

                // Sanitize the value (e.g., escape commas or newlines)
                $values[] = '"' . str_replace('"', '""', $value) . '"';
            }

            // Append the row to the CSV content
            $csv_content .= implode(',', $values) . "\n";
        }

        // Prepare response
        return [
            'success' => true,
            'data' => [
                'content' => $csv_content,
                'headers' => [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="exported-data.csv"',
                ],
            ],
            'messages' => [trans("vaahcms-general.action_successful")],
        ];
    }

    public static function groupedAttributes($variation, &$all_grouped_attributes)
    {
        $grouped_attributes = [];

        foreach ($variation->productAttributes as $attribute) {
            foreach ($attribute->values as $value) {
                $value_array = $value->toArray();
                $attribute_name = $attribute->attribute['name'];
                $grouped_attributes[$attribute_name][] = $value_array['attribute_value']['name'];
            }
        }

        foreach ($grouped_attributes as $key => $values) {
            $values = array_unique($values);

            if (!isset($all_grouped_attributes[$key])) {
                $all_grouped_attributes[$key] = $values;
            } else {
                $all_grouped_attributes[$key] = array_unique(array_merge($all_grouped_attributes[$key], $values));
            }
        }

        unset($variation->productAttributes);
    }


}
