<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Faker\Factory;
use VaahCms\Modules\Store\Models\ProductMedia;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\Vendor;
use VaahCms\Modules\Store\Models\ProductVendor;
use VaahCms\Modules\Store\Models\ProductAttribute;
use VaahCms\Modules\Store\Models\ProductPrice;
use VaahCms\Modules\Store\Models\ProductStock;
use VaahCms\Modules\Store\Services\CurrencyConverterService;
use VaahCms\Modules\Store\Traits\ApiAuthUser;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\TaxonomyType;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class Product extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;
    use ApiAuthUser;

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
        'price',
        'is_default_vendor_attached',
        'vendor_product_data',
        'media',
        'grouped_attributes',
        'wishlist_ids'
    ];
    //-------------------------------------------------


    /**
     * Get wishlist IDs for the current product and API auth user.
     * Uses static cache for efficiency.
     */
    public function getWishlistIdsAttribute()
    {
        $api_auth_user_id = $this->getApiAuthUserId();
        if (!$api_auth_user_id) {
            return [];
        }

        static $product_to_wishlist_map = null;

        if ($product_to_wishlist_map === null) {
            $product_to_wishlist_map = UserWishlist::with(['wishlist', 'products'])
                ->where('vh_user_id', $api_auth_user_id)
                ->get()
                ->flatMap(fn($wishlist) => $wishlist->products->map(fn($product) => [
                    'product_id' => $product->id,
                    'wishlist_id' => $wishlist->wishlist->id ?? null,
                ]))
                ->filter(fn($item) => !is_null($item['wishlist_id']))
                ->groupBy('product_id')
                ->map(fn($group) => $group->pluck('wishlist_id')->unique()->values()->toArray())
                ->toArray();
        }
        return $product_to_wishlist_map[$this->id] ?? [];
    }
    //-------------------------------------------------

    public function getGroupedAttributesAttribute()
    {
        return self::getGroupedAttributesViaQuery($this->id);
    }
    //-------------------------------------------------

    public function getMediaAttribute()
    {

        $product_medias = $this->hasMany(ProductMedia::class, 'vh_st_product_id')
            ->with(['images:id,vh_st_product_media_id,url,url_thumbnail,type', 'productVariationMedia:id,is_default'])
            ->get();

        $medias_to_return = $product_medias->filter(fn($media) =>
        $media->productVariationMedia->contains('is_default', 1)
        )->whenEmpty(fn() => $product_medias);

        return $medias_to_return->map(fn($media) => [
            'title' => $media->name,
            'images' => $media->images->map(fn($image) => [
                'webp_url' => $image->webp_url,
                'url' => $image->url,
                'url_thumbnail' => $image->url_thumbnail,
                'type' => $image->type,
            ]),
            'is_default' => (int) $media->productVariationMedia->contains('is_default', 1),
        ])->values();
    }
    //-------------------------------------------------

    public function getIsDefaultVendorAttachedAttribute()
    {
        return self::hasDefaultVendorAttachedToProduct($this->id);
    }
    //-------------------------------------------------




    public function loadVariations($vendor_id = null)
    {
        $variations = $this->productVariations()->with('stocks')->get();

        return $variations->map(function ($variation) use ($vendor_id) {
            $data = $variation->toArray();

            if ($vendor_id) {
                $vendor_stock = $variation->getVendorStock($vendor_id);
                $data['vendor_variation_data'] = [
                    'selected_vendor_id' => $vendor_id,
                    'quantity' => $vendor_stock->quantity ?? 0,
                ];
            }

            return $data;
        })->toArray();
    }






    //-------------------------------------------------

    protected function getStoreAndCurrencyInfo(): array
    {
        $request = request();
        $currency_code = $request->input('currency');
        $selected_store_id = $request->input('selected_store');

        $store = null;
        if ($selected_store_id && $this->store_id == $selected_store_id) {
            $store = Store::with('defaultCurrency')->find($selected_store_id);
        }

        // Fallback to product's store
        if (!$store) {
            $store = $this->store()->with('defaultCurrency')->first();
        }

        return [
            'currency_code' => $currency_code,
            'store' => $store,
        ];
    }
    //-------------------------------------------------


    public function getPriceAttribute()
    {
        $price_info = self::getDefaultPriceOfProduct($this->id);
        $price = (float) $price_info['amount'];
        $price = is_numeric($price) ? $price : 0;
        $source = $price_info['source'];

        $store_currency_info = $this->getStoreAndCurrencyInfo();
        $currency_code = $store_currency_info['currency_code'];
        $store = $store_currency_info['store'];

        if (!$store || !$store->defaultCurrency) {
            return [
                'amount' => number_format($price, 2, '.', ''),
                'currency' => [
                    'code' => null,
                    'symbol' => null,
                    'rate' => 1,
                ],
            ];
        }
        $base_currency_code = $store->defaultCurrency->code;
        $base_currency_symbol = $store->defaultCurrency->symbol;

        if (!$currency_code || $currency_code === $base_currency_code) {
            return [
                'amount' => number_format($price, 2, '.', ''),
                'currency' => [
                    'code' => $base_currency_code,
                    'symbol' => $base_currency_symbol,
                    'rate' => 1,
                ],
            ];
        }

        $conversion_data = $this->getCurrencyConversionData($price, $currency_code, $store);
        if ($source === 'product_variation') {
            return [
                'amount' => number_format($price, 2, '.', ''),
                'currency' => [
                    'code' => $conversion_data['currency'],
                    'symbol' => $conversion_data['currency_symbol'],
                    'rate' => $conversion_data['conversion_rate'],
                ],
            ];
        }


        return [
            'amount' => number_format($conversion_data['converted_amount'], 2, '.', ''),
            'currency' => [
                'code' => $conversion_data['currency'],
                'symbol' => $conversion_data['currency_symbol'],
                'rate' => $conversion_data['conversion_rate'],
            ]
        ];
    }

    //-------------------------------------------------

    protected function getCurrencyConversionData($original_amount = 1, $currency_code = null, $store = null)

    {
        $base_currency_code = $store?->defaultCurrency?->code;
        $currency_symbol = $store?->defaultCurrency?->symbol ?? null;

        // Step 2: Fallback to default store
        if (!$base_currency_code) {
            $default_store = Store::where('is_default', 1)
                ->with(['defaultCurrency', 'currencies'])
                ->first();

            $base_currency_code = $default_store?->defaultCurrency?->code;
            $currency_symbol = $default_store?->defaultCurrency?->symbol ?? null;
            $store = $default_store;
        }

        // Step 3: If still no base currency
        if (!$base_currency_code) {
            return [
                'conversion_rate' => 1,
                'currency' => null,
                'currency_symbol' => null,
                'converted_amount' => $original_amount,
            ];
        }

        $cache_key = 'conversion_rates_USD';
        $conversion_rates = Cache::remember($cache_key, now()->addDay(), function () {
            $converter = new CurrencyConverterService();
            return $converter->fetchAllRates('USD');
        });
        // Step 4: Conversion Logic
        $store_currency_codes = $store?->currencies->pluck('code')->toArray() ?? [];
        $store_currency_symbols = $store?->currencies->pluck('symbol', 'code')->toArray() ?? [];

        $convert_to_currency = ($currency_code && in_array($currency_code, $store_currency_codes))
            ? $currency_code
            : $base_currency_code;

        $conversion_rate = 1;


        $base_currency_rate = $conversion_rates[$base_currency_code] ?? null;
        $rate_to_convert = $conversion_rates[$convert_to_currency] ?? null;
        $conversion_rate = $this->calculateConversionRate($base_currency_rate, $rate_to_convert);



        $converted_amount = round($original_amount * $conversion_rate, 6);

        return [
            'conversion_rate' => round($conversion_rate, 6),
            'currency' => $convert_to_currency,
            'currency_symbol' => $store_currency_symbols[$convert_to_currency] ?? $currency_symbol,
            'converted_amount' => $converted_amount,
        ];
    }


    //-------------------------------------------------

    /**
     * Get converted Vendor Product Data
     */

    public function getVendorProductDataAttribute()
    {
        $vendor_product_data = self::fetchVendorProductPriceRangeAndQuantity($this->id)['data'];

        $store_currency_info = $this->getStoreAndCurrencyInfo();
        $currency_code = $store_currency_info['currency_code'];
        $store = $store_currency_info['store'];

        $conversion_data = $this->getCurrencyConversionData(1, $currency_code, $store); // `1` as base for rate
        $conversion_rate = $conversion_data['conversion_rate'];
        // Convert prices only if:
        // - price_range exists and is array
        // - price source is not from product_variation
        if (!empty($vendor_product_data['price_range']) && is_array($vendor_product_data['price_range']) &&
            ($vendor_product_data['price_source'] ?? '') !== 'product_variation') {

            $vendor_product_data['price_range'] = array_map(function ($price) use ($conversion_rate) {
                return number_format((float)$price * $conversion_rate, 2, '.', '');
            }, $vendor_product_data['price_range']);
        }
        return $vendor_product_data;
    }

    //-------------------------------------------------



    //-------------------------------------------------


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
    public  function productMedias()
    {
        return $this->hasMany(ProductMedia::class, 'vh_st_product_id', 'id');
    }
    //-------------------------------------------------

    public function brand()
    {
        return $this->hasOne(Brand::class,'id','vh_st_brand_id')
               ->withTrashed()
               ->select('id','name','slug','is_default','image','deleted_at');
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
        return $this->belongsTo(Store::class, 'vh_st_store_id', 'id')
            ->withTrashed()
            ->select('id', 'name', 'slug', 'is_default', 'deleted_at')
            ->with('defaultCurrency');
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
    public function productStocks()
    {
        return $this->hasMany(
            ProductStock::class,
            'vh_st_product_id',
            'id'
        );
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
        $selected_store_id = $request->input('selected_store') ?? null;
        $vendor_data = $request->input('vendors');
        $validation = self::validatedVendor($vendor_data);
        if (!$validation['success']) {
            return $validation;
        }


        $active_user = auth()->user();

        foreach ($vendor_data as $key=>$vendor){
            $ownership_check = self::validateVendorAndProductToStore(
                $selected_store_id,null,
                $vendor['id'] ?? null,
            );

            if (!$ownership_check['success']) {
                return $ownership_check;
            }
            $product_vendor = ProductVendor::where(['vh_st_vendor_id'=> $vendor['id'], 'vh_st_product_id' => $product_id])->first();

            if($product_vendor){
                $response['errors'][] = "This Vendor '{$vendor['name']}' already exists.";
                return $response;
            }

            $item = new ProductVendor();
            $item->vh_st_store_id = $selected_store_id;
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
        $store_id = $inputs['vh_st_store_id'] ?? null;

        $item = self::withTrashed()
            ->where('vh_st_store_id', $store_id)
            ->where(function ($query) use ($inputs) {
                $query->where('name', $inputs['name'])
                    ->orWhere('slug', $inputs['slug']);
            })
            ->first();

        if ($item) {
            $product_name_or_slug = $item->name === $inputs['name'] ? 'name' : 'slug';
            $error_message = "This product already exists with this store" . ($item->deleted_at ? ' (in trash).' : '.');
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
// Prevent SQL sorting by accessor
        if(Str::startsWith($sort, 'price')) {
            return $query->orderBy('id', 'desc');
        }

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

    public function scopeFilterBySelectedStore(Builder $query)
    {
        if ($selected_store = request('selected_store')) {
            $query->where('vh_st_store_id', $selected_store);
        }

        return $query;
    }
    //-------------------------------------------------
    /**
     * Sort paginated collection by product price accessor (in-memory).
     * Accessors like `price` can't be sorted via DB, so we do it after pagination.
     */
    protected static function sortCollectionByPrice($paginated_list, $sort)
    {
        if (!$sort || (!str_contains($sort, 'price') && !str_contains($sort, 'price:'))) {
            return $paginated_list;
        }

        $direction = 'asc';
        if (str_contains($sort, ':')) {
            [, $sort_dir] = explode(':', $sort);
            $direction = strtolower($sort_dir) === 'desc' ? 'desc' : 'asc';
        }

        $sorted = $paginated_list->getCollection()->sortBy(function ($item) {
            return isset($item['product_variation']['sale_price'])
                ? (float)$item['product_variation']['sale_price']
                : 0;
        }, SORT_REGULAR, $direction === 'desc');
        return $paginated_list->setCollection($sorted->values());
    }
    //-------------------------------------------------


    public static function getList($request)
    {
        $selected_store_id = $request->input('selected_store') ??
            Store::where('is_default', 1)->value('id');
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

        $default_relationships = [
            'brand:id,name',
            'store',
            'type',
            'status',
            'productCategories:id,name,slug,parent_id'
        ];

        // Prepare excluded relationship keys
        $excluded_relationships = collect($exclude)
            ->filter(fn($val) => $val === 'true')
            ->keys()
            ->flatMap(fn($key) => array_map('trim', explode(',', $key)))
            ->unique()
            ->values();

        $relationships = array_filter($default_relationships, function ($rel) use ($excluded_relationships) {
            return !in_array(Str::before($rel, '.'), $excluded_relationships->toArray());
        });

        foreach ($include as $key => $value) {
            if ($value === 'true') {
                foreach (explode(',', $key) as $relationship) {
                    $relationship = trim($relationship);
                    if (method_exists(self::class, $relationship) && !in_array($relationship, $relationships)) {
                        $relationships[] = $relationship;
                    }
                }
            }
        }

        $list = self::query()->where('vh_st_store_id', $selected_store_id);
        $filter = $request->filter ?? [];


        $list = $list->getSorted($filter)
            ->with($relationships)
            ->withCount(['productVariations', 'productVendors']);
        // Apply filters
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->quantityFilter($request->filter);
        $list->productVariationFilter($request->filter);
        $list->vendorFilter($request->filter);
        $list->brandFilter($request->filter);
        $list->dateFilter($request->filter);
        $list->productTypeFilter($request->filter);
        $list->categoryFilter($request->filter);
        $list->priceFilter($request->filter);
        $list->featuredHomePageFilter($request->filter);
        $list->featuredCategoryPageFilter($request->filter);
        $list->newArrivalsFilter($request->filter);
        $list->topSellingsFilter($request->filter);

        // Filter by product IDs (if provided)
        if ($request->has('ids')) {
            $ids = json_decode($request->ids, true);
            if (is_array($ids) && !empty($ids)) {
                $list->whereIn('id', $ids);
            }
        }

        // Pagination
        $rows = $request->get('rows', config('vaahcms.per_page'));
        $list = $list->paginate($rows);



        // Transform list items: hide keys + attach variation
        $filtered = $list->getCollection()->filter(function ($item) use ($request) {

            if ($request->input('filter.only_with_vendor') === 'true') {
                return optional($item->vendor_product_data)['selected_vendor'] !== null;
            }
            return true;
        })->values()->transform(function ($item) use ($excluded_relationships) {
            $item->makeHidden($excluded_relationships->toArray());


                $resolved_variation = self::getResolvedVariationWithVendor($item['id']);
                if ($resolved_variation) {
                    $item['product_variation'] = $resolved_variation;
                }


            return $item;
        });

// Replace the original collection with the filtered one
        $list->setCollection($filtered);
// Check for price-based sort and apply it in-memory after pagination
        $sort = $request->input('filter.sort') ?? null;
        $list = self::sortCollectionByPrice($list, $sort);

        $response = [
            'success' => true,
            'data' => $list->toArray(),
        ];

        $response['data']['active_cart_user'] = null;

        if ($user) {
            $user['cart_records'] = $cart_records;
            $user['vh_st_cart_id'] = $cart->id;

            $response['data']['active_cart_user'] = $user;
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
            case 'create-10-records':
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

    public static function getItem($id, $request = null)
    {
        $include = request()->query('include', []);
        $exclude = request()->query('exclude', []);

        $relationships = [
            'createdByUser', 'updatedByUser', 'deletedByUser',
            'brand', 'store', 'type', 'status', 'productCategories',
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
            ->with($relationships)->withCount('productVendors')
            ->withTrashed()
            ->first();
        if ($item && $item->is_default_vendor_attached) {
            $item->product_vendors_count += 1;
        }
        if ($request && $request->boolean('include.variations')) {
            $vendor_id = $item->vendor_product_data['selected_vendor']['id'] ?? null;

            $response['data']['variations'] = $item->loadVariations($vendor_id);
        }
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_not_found_with_id") . $id;
            return $response;
        }
        $array_item = $item->toArray();


        $resolved_variation = $item::getResolvedVariationWithVendor($item->id);

        if ($resolved_variation) {
            $array_item['product_variation'] = $resolved_variation;

            //  Prefer attribute from URL, fallback to resolved variation's attribute_query
            $attribute_from_url = request()->query('attribute', []);
            $attribute_query_array = [];

            if (!empty($attribute_from_url)) {
                // Use attributes directly from query string (?attribute[color]=black&attribute[size]=medium)
                $attribute_query_array = $attribute_from_url;
            } elseif (!empty($resolved_variation['attribute_query'])) {
                // Parse attribute_query from variation fallback if URL has no attribute
                parse_str(ltrim($resolved_variation['attribute_query'], '?'), $parsed);
                $attribute_query_array = $parsed['attribute'] ?? [];
            }

            // If we have any attribute input to work with
            if (!empty($attribute_query_array)) {
                // Merge into request so getAvailableCombinations() can use it
                request()->merge(['attribute' => $attribute_query_array]);

                //  Fetch available combinations using selected/fallback attributes
                $combinations = self::getAvailableCombinationsWithVariation(request(), $item->id);

                if (!empty($combinations['data']['attributes_combinations'])) {
                    $array_item['product_variation']['attributes_combinations'] = $combinations['data']['attributes_combinations'];
                }
            }
        }



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

    public function findVariationFromQueryParams($query_params)
    {
        if (empty($query_params)) {
            return null;
        }

        // Always treat each param as an array of values, slug everything
        $normalized = collect($query_params)->mapWithKeys(function ($value, $key) {
            $slugged_key = \Illuminate\Support\Str::slug($key);
            $values = is_array($value) ? $value : [$value];
            return [$slugged_key => collect($values)->map(fn($v) => \Illuminate\Support\Str::slug($v))->all()];
        });

        // Find the first matching variation using collections
        return $this->productVariations->first(function ($variation) use ($normalized) {
            $attributes = collect($variation->productAttributes)->mapWithKeys(function ($attribute) {
                $attr_name = \Illuminate\Support\Str::slug($attribute->attribute->name ?? '');
                $attr_value = \Illuminate\Support\Str::slug(optional($attribute->values->first())->value ?? '');
                return [$attr_name => $attr_value];
            });

            // All query param keys must exist and at least one value must match
            return $normalized->every(function ($values, $key) use ($attributes) {
                return isset($attributes[$key]) && in_array($attributes[$key], $values, true);
            });
        });
    }
    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }
        $store_id = $inputs['vh_st_store_id'] ?? null;
        // check if name exist
        $existing_item = self::withTrashed()
            ->where('vh_st_store_id', $store_id)
            ->where(function ($query) use ($inputs) {
                $query->where('name', $inputs['name'])
                    ->orWhere('slug', $inputs['slug']);
            })
            ->where('id', '!=', $id)
            ->first();

        if ($existing_item) {
            $product_name_or_slug = $existing_item->name === $inputs['name'] ? 'name' : 'slug';
            $error_message = "This $product_name_or_slug already exists with this store" . ($existing_item->deleted_at ? ' (in trash).' : '.');
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
            'summary' => 'nullable',
//            'vh_st_store_id'=> 'required',
            'taxonomy_id_product_type'=> 'required',
            'details' => 'nullable',
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
        $inputs = self::seedProduct();


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
        $inputs['name'] = $faker->name;
        $inputs['slug'] = Str::slug($inputs['name']);

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
        $query_text = $request->input('search');
        $selected_store = $request->input('selected_store');

        $vendors = Vendor::with('status')
            ->select('id', 'name', 'slug', 'taxonomy_id_vendor_status')
            ->where('is_active', 1)
            ->when($selected_store, function ($query) use ($selected_store) {
                $query->whereHas('store', function ($q) use ($selected_store) {
                    $q->where('id', $selected_store);
                });
            })
            ->when($query_text, function ($query) use ($query_text) {
                $query->where('name', 'like', "%{$query_text}%");
            })
            ->limit(10)
            ->get();

        return [
            'success' => true,
            'data' => $vendors,
        ];
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
            $active_selected_vendor = self::fetchVendorProductPriceRangeAndQuantity($product_id)['data'] ?? null;
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

    public static function findCartItem($cart, $variation_id, $selected_vendor_id)
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
        $errors = collect();
        $messages = [];

        $default_vendor = Vendor::where('is_default', 1)->first();

        // Optional: Handle user
        $user_info = $request->input('user');
        $user = is_array($user_info) && isset($user_info['id'])
            ? self::findOrCreateUser(['id' => $user_info['id']])
            : null;
        // Prepare product collection with validation and enrichment
        $valid_products = collect($request->products)
            ->map(function ($product_data) use ($errors, $default_vendor) {
                $product_id = $product_data['id'] ?? null;
                $variation_id = $product_data['variation_id'] ?? null;
                $vendor_id = $product_data['vendor_id'] ?? null;
                $quantity = $product_data['quantity'] ?? 1;

                $product = Product::find($product_id);
                if (!$product) {
                    $errors->push("Invalid product ID {$product_id}.");
                    return null;
                }

                $variation = $variation_id
                    ? self::getVariationById($product_id, $variation_id)
                    : self::getDefaultVariation($product);

                if (!$variation || !isset($variation['variation_id'])) {
                    $errors->push("No variation found for product ID {$product_id}.");
                    return null;
                }

                $vendor = self::getActiveSelectedVendor(
                    $vendor_id,
                    $product_id,
                    $default_vendor
                );

                if (!$vendor) {
                    $errors->push("No vendor selected for product ID {$product_id}.");
                    return null;
                }

                if ($vendor->id !== $default_vendor?->id) {
                    $product_vendor = $product->productVendors()
                        ->where('vh_st_vendor_id', $vendor->id)
                        ->first();

                    if (!$product_vendor) {
                        $errors->push("Product ID {$product_id} is not associated with vendor ID {$vendor->id}.");
                        return null;
                    }
                }

                return [
                    'product_id' => $product_id,
                    'variation_id' => $variation_id,
                    'vendor_id' => $vendor_id,
                    'quantity' => $quantity,
                    'product' => $product,
                    'variation' => $variation,
                    'vendor' => $vendor,
                ];
            })
            ->filter()
            ->values();


        // If no valid products found
        if ($valid_products->isEmpty()) {
            return [
                'success' => false,
                'errors' => $errors->isNotEmpty()
                    ? $errors->toArray()
                    : ['No products or variations added to the cart.'],
            ];
        }

        // Retrieve or create a single cart for the user
        $cart = null;
        $cart_uuid = $request->input('uuid');

        // If a user is logged in, prioritize their existing cart
        if ($user) {
            $cart = Cart::where('vh_user_id', $user->id)->first();
        }

        // If no cart for user and UUID is passed, try UUID lookup (non-auth fallback)
        if (!$cart && $cart_uuid) {
            $cart = Cart::findByIdOrUuid($cart_uuid)->first();
        }

        if (!$cart) {
            $cart = self::findOrCreateCart($user);
        } elseif ($user && !$cart->vh_user_id) {
            // Attach user if not already set
            $cart->vh_user_id = $user->id;
            $cart->save();
        }

        // Save all valid products to cart
        $valid_products->each(function ($item) use ($cart, $user) {
            self::handleCart(
                $cart,
                $item['product'],
                $item['variation'],
                $item['vendor'],
                $item['quantity']
            );
        });

        // Prepare final response
        if ($errors->isNotEmpty()) {
            $response['success'] = false;
            $response['errors'] = $errors->toArray();
        }

        $messages[] = trans("vaahcms-general.saved_successfully");

        $response['success'] = true;
        $response['messages'] = $messages;
        $cart->load('products');

        $cart->makeHidden(['products']);

        $response['data'] = $cart;
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

        $active_selected_vendor = self::fetchVendorProductPriceRangeAndQuantity($product_id)['data']['selected_vendor'] ?? null;

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

    public static function fetchVendorProductPriceRangeAndQuantity($id)
    {
        $preferred_vendor_product_id = static::getPreferredProductVendorIds($id);
//        dd( $preferred_vendor_product_id);
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
            $query->where('vh_st_product_id', $id)
                ->where('is_active', 1)
                ->where('quantity', '>', 0);
        })
            ->select('vh_st_vendors.*')
            ->withCount(['productStocks as quantity' => function ($query) use ($id) {
                $query->where('vh_st_product_id', $id)->where('is_active', 1);
            }])
            ->orderByDesc('quantity')
            ->first();

        if ($vendor) {
            $price_data = self::getPriceRange($vendor->id, $id);
            return [
                'price_range' => $price_data['price_range'],
                'price_source' => $price_data['price_source'],
                'quantity' => $vendor->productStocks()
                    ->where('vh_st_product_id', $id)
                    ->where('is_active', 1)
                    ->sum('quantity'),
                'selected_vendor' => $vendor,
            ];
        }

        // Check for default vendor
        $default_vendor = Vendor::where('is_default', 1)->first();
        if ($default_vendor) {
            $price_data = self::getPriceRange($default_vendor->id, $id);
            return [
                'price_range' => $price_data['price_range'],
                'price_source' => $price_data['price_source'],
                'quantity' => $default_vendor->productStocks()
                    ->where('vh_st_product_id', $id)
                    ->where('is_active', 1)
                    ->sum('quantity'),
                'selected_vendor' => $default_vendor,
            ];
        }
        // No vendor found, fallback to product variations
        $price_data = self::getPriceRange(null, $id);
        return [
            'price_range' => $price_data['price_range'],
            'price_source' => $price_data['price_source'],
            'quantity' => 0,
            'selected_vendor' => null,
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
        $price_source = 'product_price';
        if (empty($price_range)) {
            $price_range = ProductVariation::where('vh_st_product_id', $id)
                ->whereNotNull('price')
                ->pluck('price')
                ->toArray();
            $price_source = 'product_variation';
        }



        $min_price = !empty($price_range) ? min($price_range) : null;
        $max_price = !empty($price_range) ? max($price_range) : null;

        return [
            'price_range' =>($min_price !== null && $min_price === $max_price) ? [$min_price] : [$min_price, $max_price],
            'quantity' => $quantity,
            'selected_vendor' => $vendor,
            'price_source' => $price_source,
        ];
    }





    //----------------------------------------------------------


    public static function getVendorsListForPrduct($id, $variation = null)
    {
        $product=self::findOrFail($id);
        $rate = data_get($product, 'price.currency.rate', 1);
        $product_vendors = ProductVendor::where('vh_st_product_id', $id)
            ->select('id', 'vh_st_vendor_id', 'is_preferred')
            ->get();
        $vendor_ids = $product_vendors->pluck('vh_st_vendor_id')->toArray();

        $vendors_data = Vendor::whereIn('id', $vendor_ids)
            ->select('id', 'name', 'slug', 'is_default', 'email', 'phone_number', 'years_in_business', 'services_offered', 'status_notes')
            ->get();

        $default_vendor_id = $vendors_data->where('is_default', 1)->pluck('id')->toArray();
        $missing_default_vendor = Vendor::whereNotIn('id', $default_vendor_id)
            ->where('is_default', 1)
            ->select('id', 'name', 'slug', 'is_default', 'email', 'phone_number', 'years_in_business', 'services_offered', 'status_notes')
            ->get();
        $message = $missing_default_vendor->isNotEmpty();

        $vendors = $vendors_data->merge($missing_default_vendor);

        // If a variation is passed and has an id, use it, else fallback to default
        $default_variation = $variation ?: ProductVariation::where('vh_st_product_id', $id)
                ->where('is_default', 1)
                ->first() ?? ProductVariation::where('vh_st_product_id', $id)->first();

        $default_variation_id = $default_variation ? $default_variation->id : null;

        // If variation is given, filter product prices accordingly
        $product_prices = ProductPrice::where('vh_st_product_id', $id)
            ->when($variation && $variation->id, function ($query) use ($variation) {
                $query->where('vh_st_product_variation_id', $variation->id);
            })
            ->whereIn('vh_st_vendor_id', $vendor_ids)
            ->get();

        $product_price_range_with_vendors = $product_prices->groupBy('vh_st_vendor_id');

        $default_product_prices = ProductPrice::where('vh_st_product_id', $id)
            ->where('vh_st_product_variation_id', $default_variation_id)
            ->whereIn('vh_st_vendor_id', $vendor_ids)
            ->get()
            ->groupBy('vh_st_vendor_id');

        $vendors->each(function ($vendor) use ($product_vendors, $product_price_range_with_vendors, $default_product_prices, $id, $default_variation,$rate,$product,$variation) {
            $price_range = static::getVendorPriceAndQuantity($vendor, $id);

                $converted_range = array_map(fn($val) => round($val * $rate, 2), $price_range['price_range']);
                $vendor->product_price_range = $converted_range;


            $quantity = ProductStock::where('vh_st_vendor_id', $vendor->id)
                ->where('vh_st_product_id', $id)
                ->when($variation && $variation->id, function ($query) use ($variation) {
                    $query->where('vh_st_product_variation_id', $variation->id);
                })
                ->where('is_active', 1)
                ->sum('quantity');



            $vendor->quantity = $quantity;

            $prices = isset($product_price_range_with_vendors[$vendor->id])
                ? $product_price_range_with_vendors[$vendor->id]->pluck('amount')->filter()->map('floatval')->toArray()
                : [];

            if (empty($prices)) {
                $prices = ProductVariation::where('vh_st_product_id', $id)
                    ->pluck('price')
                    ->filter()
                    ->map('floatval')
                    ->toArray();
            }

            $vendor->product_vendor_id = null;
            $vendor->is_preferred = null;

            $product_vendor = $product_vendors->where('vh_st_vendor_id', $vendor->id)->first();

            if ($product_vendor) {
                $vendor->product_vendor_id = $product_vendor->id;
                $vendor->vh_st_product_id = $id;
                $vendor->is_preferred = $product_vendor->is_preferred;
            }

            $vendor->price = isset($default_product_prices[$vendor->id])
                ? round($default_product_prices[$vendor->id]->first()->amount * $rate, 2)
                : null;


            if (is_null($vendor->price) && $default_variation) {
                $vendor->price = $default_variation->price;
            }
        });

        return [
            'success' => true,
            'data' => $vendors,
            'message' => $message,
        ];
    }
    //----------------------------------------------------------

    public static function hasDefaultVendorAttachedToProduct($product_id)
    {

        $default_vendor_id = Vendor::where('is_default', 1)->value('id');
        if (!$default_vendor_id) {
            return true;
        }

        $is_attached = ProductVendor::where('vh_st_product_id', $product_id)
            ->where('vh_st_vendor_id', $default_vendor_id)
            ->exists();

        return !$is_attached;


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
        $default_limit = 10;
        $filter_all = $request->boolean('filter_all', false);
        $limit = $filter_all ? null : (int) $request->input('limit', $default_limit);

        $start_date = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end_date = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

        $store_id = $request->input('selected_store') ?? Store::where('is_default', 1)->value('id');

        $products_query = Product::query()
            ->whereNull('deleted_at')
            ->whereHas('store', fn($q) => $q->where('id', $store_id))
            ->with(['brand:id,name', 'store:id,name,slug'])
            ->withSum(['orderItems as total_sales' => function ($query) use ($filter_all, $start_date, $end_date) {
                if (!$filter_all && $start_date && $end_date) {
                    $query->whereBetween('created_at', [$start_date, $end_date]);
                }
            }], 'quantity')
            ->orderByDesc('total_sales');

        if ($limit) {
            $products_query->limit($limit);
        }

        $products = $products_query->get();

        $items = $products->map(function ($product) {
            $product_media_ids = $product->medias->pluck('pivot.vh_st_product_media_id')->filter();

            if ($product_media_ids->isEmpty()) {
                $product_media_ids = ProductMedia::where('vh_st_product_id', $product->id)->pluck('id');
            }

            $image_urls = self::getImageUrls($product_media_ids);

            return [
                'id'          => $product->id,
                'name'        => $product->name,
                'slug'        => $product->slug,
                'total_sales' => (int) ($product->total_sales ?? 0),
                'media'       => $image_urls->values(),
                'brand'       => [
                    'id'   => $product->brand->id ?? null,
                    'name' => $product->brand->name ?? null,
                ],
                'store'       => [
                    'id'   => $product->store->id ?? null,
                    'name' => $product->store->name ?? null,
                    'slug' => $product->store->slug ?? null,
                ],
            ];
        });

        return [
            'success' => true,
            'data'    => $items,
        ];
    }

    /**
     * Get top selling brands for a store and date range.
     * Uses collections for grouping and sorting.
     */
    public static function topSellingBrands($request)
    {
        $default_limit = 10;
        $limit = $request->has('limit') ? (int) $request->limit : $default_limit;

        $start_date = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end_date = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

        $filter_all = $request->boolean('filter_all', false);

        $store_id = $request->input('selected_store') ?? Store::where('is_default', 1)->value('id');

        $query = OrderItem::query()
            ->whereHas('product', function ($q) use ($store_id) {
                if ($store_id) {
                    $q->where('vh_st_store_id', $store_id);
                }
            })
            ->with(['product.brand']);

        if (!$filter_all && $start_date && $end_date) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }

        $order_items = $query->get();

        $grouped = $order_items
            ->groupBy(fn($item) => optional($item->product->brand)->id)
            ->map(function ($items, $brand_id) {
                $brand = $items->first()->product->brand ?? null;
                $total_sales = $items->sum('quantity');

                if ($brand) {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'slug' => $brand->slug,
                        'image_urls' => $brand->image
                            ? [ltrim(Storage::url('brands/' . $brand->image), '/')]
                            : [],
                        'total_sales' => $total_sales,
                    ];
                }
                return null;
            })
            ->filter()
            ->sortByDesc('total_sales');

        $brands = $filter_all ? $grouped->values() : $grouped->take($limit)->values();

        return [
            'success' => true,
            'data' => $brands,
        ];
    }

    /**
     * Get top selling categories for a store and date range
     */
    public static function topSellingCategories($request)
    {
        $default_limit = 10;
        $limit = $request->input('limit', $default_limit);

        $start_date = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
        $end_date = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;

        $apply_date_range = !$request->boolean('filter_all', false);
        $store_id = $request->input('selected_store') ?? Store::where('is_default', 1)->value('id');

        $order_items = OrderItem::query()
            ->when($store_id, fn($q) => $q->whereHas('product', fn($q) => $q->where('vh_st_store_id', $store_id)))
            ->when($apply_date_range && $start_date && $end_date, fn($q) => $q->whereBetween('created_at', [$start_date, $end_date]))
            ->with(['product.productCategories.parentCategory'])
            ->get();

        $category_counts = [];

        foreach ($order_items as $order_item) {
            $product = $order_item->product;
            if (!$product || $product->productCategories->isEmpty()) {
                continue;
            }

            foreach ($product->productCategories as $category) {
                $final_parent = $category;
                while ($final_parent && $final_parent->parentCategory) {
                    $final_parent = $final_parent->parentCategory;
                }
                if ($final_parent) {
                    $category_id = $final_parent->id;
                    if (!isset($category_counts[$category_id])) {
                        $category_counts[$category_id] = [
                            'id' => $final_parent->id,
                            'slug' => $final_parent->slug,
                            'name' => $final_parent->name,
                            'count' => 0,
                            'media' => $final_parent->media,
                        ];
                    }
                    $category_counts[$category_id]['count']++;
                }
            }
        }
        $top_categories = collect($category_counts)
            ->sortByDesc('count')
            ->take($limit)
            ->map(function ($category) {
                $media = $category['media'];
                $image_urls = [];

                if ($media && isset($media['webp_url'])) {
                    $image_urls[] = $media['webp_url'];
                }
                return [
                    'id' => $category['id'],
                    'slug' => $category['slug'],
                    'name' => $category['name'],
                    'media' => $category['media'],
                    'image_urls' => $image_urls,
                ];
            })->values();

        return [
            'success' => true,
            'data' => $top_categories,
        ];
    }



    //----------------------------------------------------------


    private static function getImageUrls($product_media_ids)
    {
        $media = ProductMedia::with('images')
        ->whereIn('id', $product_media_ids)
            ->get();

        return $media->map(function ($media_item) {
            return [
                'title'      => $media_item->title,
                'images'     => $media_item->images->map(function ($image) {
                    return [
                        'webp_url'      => $image->webp_url,
                        'url'           => $image->url,
                        'url_thumbnail' => $image->url_thumbnail,
                        'type'          => $image->type,
                    ];
                })->values(),
                'is_default' => $media_item->is_default,
            ];
        })->values();
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




    public static function seedProduct(){
        $json_file = __DIR__ . DIRECTORY_SEPARATOR . "../Database/Seeds/json/products.json";
        $jsonString = file_get_contents($json_file);
        $products = json_decode($jsonString, true);


        foreach ($products as $product) {

            $existing_product = Product::where('name', $product['name'])->first();

            if ($existing_product) {
                continue; // Skip to the next product if it already exists
            }

            $inputs = [];
            $inputs['name'] = $product['name'];
            $inputs['slug'] = Str::slug($inputs['name']);
            $inputs['summary'] = $product['info'];

            // Assign a random active store
            $stores = Store::where('is_active', 1)->pluck('id')->toArray();
            if (!empty($stores)) {
                $inputs['vh_st_store_id'] = $stores[array_rand($stores)];
            }

            //Assign or create a brand
            $brand = Brand::firstOrCreate(
                ['name' => $product['brand']['name']],
                ['slug' => Str::slug($product['brand']['name'])]
            );
            $inputs['vh_st_brand_id'] = $brand->id;



            // Assign a random product status
            $taxonomy_status = Taxonomy::where('name', 'Approved')
                ->whereHas('type', function ($query) {
                    $query->where('name', 'Product Status');
                })
                ->first();

           if (!empty($taxonomy_status)) {
                $inputs['taxonomy_id_product_status'] = $taxonomy_status->id;
            }

            // Assign a random product type
            $taxonomy = Taxonomy::firstOrCreate(
                ['name' => $product['articleType']],
                [
                    'slug' => Str::slug($product['articleType']),
                    'vh_taxonomy_type_id' => TaxonomyType::firstOrCreate(['name' => 'Product Types'])->id
                ]
            );

            $inputs['taxonomy_id_product_type'] = $taxonomy->id;
            $inputs['is_active'] = 1;

            if (!empty($product['defaultImage']['src'])) {
                $image_url = $product['defaultImage']['src'];
                $image_name =  $inputs['slug'] . '.jpg'; // Generate a unique image name
                $image_path = 'media/' . $image_name; // Define storage path

                try {
                    $image_contents = file_get_contents($image_url);
                    Storage::disk('public')->put($image_path, $image_contents); // Save the image in storage

                    // Set the image path in the inputs array
                    $inputs['image_path'] = 'storage/' . $image_path;
                } catch (\Exception $e) {
                    \Log::error("Failed to download image: " . $e->getMessage());
                }
            }
            // Save the product
            $product = Product::create($inputs);

            $json_file_variants = __DIR__ . DIRECTORY_SEPARATOR . "../Database/Seeds/json/attributes.json";
            $jsonString = file_get_contents($json_file_variants);
            $attributes = json_decode($jsonString, true);

            // Create variations
            self::createProductVariations($product, $attributes);


            $Product_media = new ProductMedia();
            $Product_media->vh_st_product_id = $product->id;
            $Product_media_taxonomy_status = Taxonomy::where('name', 'Approved')
                ->whereHas('type', function ($query) {
                    $query->where('slug', 'Product-medias-status');
                })
                ->first();
            if (!empty($Product_media_taxonomy_status)) {
                $Product_media->taxonomy_id_product_media_status  = $Product_media_taxonomy_status->id;
            }

            $Product_media->name = $inputs['slug'] . '.jpg';
            $Product_media->type = 'image';
            $Product_media->is_active = 1;
            $Product_media->save();


            self::saveProductImages($Product_media,$inputs,$image_path);


        }
    }

    public static function saveProductImages($Product_media = null ,$inputs = null,$image_path=null){

        if (empty($Product_media) || empty($inputs) || empty($image_path)) {
            return false;
        }

        $Product_media_image = new ProductMediaImage();
        $Product_media_image->vh_st_product_media_id = $Product_media->id;
        $Product_media_image->name = $inputs['slug'] . '.jpg';
        $Product_media_image->slug = Str::slug($inputs['slug'] . '.jpg');
        $Product_media_image->url = $inputs['image_path'];
        $Product_media_image->path = 'storage/app/public/' . $image_path;
        $Product_media_image->url_thumbnail = 'storage/app/public/' . $image_path;
        $Product_media_image->save();

    }

    public static function createProductVariations($product, $attributes)
    {
        $faker = Factory::create();

        $variation_attributes = ['color', 'size', 'gender'];

        $filtered_attributes = array_filter($attributes, function($key) use ($variation_attributes) {
            return in_array($key, $variation_attributes);
        }, ARRAY_FILTER_USE_KEY);

        // Generate combinations of selected attributes and their values
        $attribute_combinations = [];

        foreach ($filtered_attributes as $attribute_key => $attribute) {
            foreach ($attribute['values'] as $value) {
                $attribute_combinations[$attribute_key][] = $value;
            }
        }

        // Generate variations by combining values from each attribute
        $combinations = self::combineAttributes($attribute_combinations);

        // Create product variations for each combination
        foreach ($combinations as $combination) {
            $variation_name = $product->name . ' - ' . implode('/', $combination);
            $variation_slug = Str::slug($product->name . ' ' . implode(' ', $combination));

            ProductVariation::firstOrCreate([
                'vh_st_product_id' => $product->id,
                'name' => $variation_name,
                'slug' => $variation_slug,
                'quantity' => 0,
                'price' => $faker->randomFloat(2, 10, 500),
                'is_active' => 1,
            ]);
        }
    }

    /**
     * Helper function to generate combinations of attribute values.
     */
    public static function combineAttributes($attributes)
    {
        $result = [[]];

        foreach ($attributes as $attribute_values) {
            $new_result = [];
            foreach ($result as $combination) {
                foreach ($attribute_values as $value) {
                    $new_result[] = array_merge($combination, [$value]);
                }
            }
            $result = $new_result;
        }

        return $result;
    }
    /**
     * Helper function to get the default price of product.
     */

    public static function getDefaultPriceOfProduct($product_id)
    {
        $price_data_with_vendor = self::fetchVendorProductPriceRangeAndQuantity($product_id)['data'];
        $default_variation = ProductVariation::where('vh_st_product_id', $product_id)
            ->where('is_default', 1)
            ->first();

        if (!$default_variation) {
            return [
                'amount' => null,
                'source' => null,
            ];
        }

        if (!empty($price_data_with_vendor['selected_vendor'])) {
            $product_price = ProductPrice::where([
                'vh_st_vendor_id' => $price_data_with_vendor['selected_vendor']['id'],
                'vh_st_product_variation_id' => $default_variation->id
            ])->value('amount');

            if (!is_null($product_price)) {
                return [
                    'amount' => $product_price,
                    'source' => 'vendor_price',
                ];
            }
        }

        return [
            'amount' => $default_variation->price,
            'source' => 'product_variation', //  already converted, no further conversion needed
        ];
    }

    /**
     * Get the price range based on vendor or product variations
     */
    protected static function getPriceRange($vendor_id, $product_id)
    {
        // Try to get price from ProductPrice if a vendor exists
        $price_range = [];
        $price_source = null;
        if ($vendor_id) {
            $price_range = ProductPrice::where('vh_st_vendor_id', $vendor_id)
                ->where('vh_st_product_id', $product_id)
                ->whereNotNull('amount')
                ->pluck('amount')
                ->toArray();
            if (!empty($price_range)) {
                $price_source = 'product_price';
            }
        }

        // If no price found, fallback to ProductVariation
        if (empty($price_range)) {
            $price_range = ProductVariation::where('vh_st_product_id', $product_id)
                ->whereNotNull('price')
                ->pluck('price')
                ->toArray();
            if (!empty($price_range)) {
                $price_source = 'product_variation';
            }
        }

        // Determine min and max price
        $min_price = !empty($price_range) ? min($price_range) : null;
        $max_price = !empty($price_range) ? max($price_range) : null;

        $final_price_range = ($min_price !== null && $min_price === $max_price)
            ? [$min_price]
            : ($min_price !== null ? [$min_price, $max_price] : []);

        return [
            'price_range' => $final_price_range,
            'price_source' => $price_source,
        ];
    }
    //----------------------------------------------------------

    public static function existenceWithStore($request){
        $store_id = $request->get('store_id');
        $product_name = $request->get('name');

        if (!$store_id || !$product_name) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Store ID and Product Name are required.'
            ];
        }
        $product = Product::where('name', $product_name)->first();
        $response = [
            'exists_in_other_store' => false,
            'store_id' => $store_id,
            'product_name' => $product_name,
        ];

        if ($product) {
            if ($product->vh_st_store_id != $store_id) {
                $response['exists_in_other_store'] = true;
                $response['message'] = 'Product exists with another store.';
            }

        }

        return [
            'success' => true,
            'data' => $response
        ];

    }
    protected function calculateConversionRate($base_currency_rate, $rate_to_convert)
    {
        $is_valid_base = $base_currency_rate && $base_currency_rate > 0;
        $is_valid_target = $rate_to_convert && $rate_to_convert > 0;

        $can_convert_to_target = $is_valid_base && $is_valid_target;
        $can_convert_from_usd = !$can_convert_to_target && $is_valid_target;

        return $can_convert_to_target
            ? (1 / $base_currency_rate) * $rate_to_convert
            : ($can_convert_from_usd ? $rate_to_convert : 1);
    }

    //----------------------------------------------------------

    public static function validateVendorAndProductToStore($store_id, $product_id = null,$vendor_id = null)
    {
        $response = ['success' => true, 'errors' => []];
        if ($vendor_id) {
            $vendor = Vendor::where('id', $vendor_id)
                ->where('vh_st_store_id', $store_id)
                ->first();

            if (!$vendor) {
                $response['success'] = false;
                $response['errors'][] = 'Selected vendor does not belong to the selected store.';
            }
        }

        if ($product_id) {
            $product = Product::where('id', $product_id)
                ->where('vh_st_store_id', $store_id)
                ->first();

            if (!$product) {
                $response['success'] = false;
                $response['errors'][] = 'Selected product does not belong to the selected store.';
            }
        }

        return $response;
    }
    //----------------------------------------------------------
    public static function getGroupedAttributesViaQuery($product_id)
    {
        $attributes = \DB::table('vh_st_product_variations as pv')
            ->join('vh_st_product_attributes as pa', 'pv.id', '=', 'pa.vh_st_product_variation_id')
            ->join('vh_st_attributes as a', 'pa.vh_st_attribute_id', '=', 'a.id')
            ->join('vh_st_product_attribute_values as pav', 'pa.id', '=', 'pav.vh_st_product_attribute_id')
            ->join('vh_st_attribute_values as av', 'pav.vh_st_attribute_value_id', '=', 'av.id')
            ->where('pv.vh_st_product_id', $product_id)
            ->whereNull('pv.deleted_at')
            ->whereNull('pa.deleted_at')
            ->whereNull('pav.deleted_at')
            ->select(
                'a.id as attribute_id',
                'a.name as attribute',
                'av.id as attribute_value_id',
                'av.value as attribute_value',
                'pv.is_default'
            )
            ->get();

        // Get default selected attributes
        $default_attributes = $attributes
            ->where('is_default', 1)
            ->keyBy('attribute_id');

        // Group and structure data
        return $attributes->groupBy('attribute_id')->map(function ($items, $attribute_id) use ($default_attributes) {
            return [
                'attribute_id' => $attribute_id,
                'attribute' => $items->first()->attribute,
                'values' => $items->map(fn($item) => [
                    'id' => $item->attribute_value_id,
                    'value' => $item->attribute_value,
                ])->unique('id')->values(),
                'selected' => $default_attributes[$attribute_id]->attribute_value_id ?? null,
            ];
        })->values();
    }

    //----------------------------------------------------------
    //----------------------------------------------------------


    public static function getResolvedVariationWithVendor($product_id, $vendor = null, $variation = null, $item = null)
    {
//        $item = Product::with(['productVariations'])->find($product_id);
        if (!$item) {
            $item = Product::with(['productVariations'])->find($product_id);
        }
        if (!$item) {
            return null;
        }
        if (!$variation && ($uuid = request()->query('variation'))) {
            $variation = $item->productVariations->firstWhere('uuid', $uuid);
            if ($variation) {
                return self::buildResolvedVariationResponse($item, $variation, 'uuid', $vendor);
            }
        }

        // 2. Otherwise, resolve via attribute query (?attribute[size]=small)
        if (!$variation) {
            $attribute_query = request()->query('attribute', []);
            $variation = $item->findVariationFromQueryParams($attribute_query);
            if ($variation) {
                return self::buildResolvedVariationResponse($item, $variation, 'attribute', $vendor);
            }
        }

        // 3. Fallback: default or first variation
        $variation = $variation
            ?? $item->productVariations->firstWhere('is_default', 1)
            ?? $item->productVariations->first();
        $selected_vendor=$item->vendor_product_data['selected_vendor'];

        if ($variation && $selected_vendor) {
            $vendor_id = $selected_vendor['id'];

            //  preload all quantities once
            $variation_ids = $item->productVariations->pluck('id')->toArray();

            // One query to get quantities for all variations for this vendor
            $quantities = DB::table('vh_st_product_stocks')
                ->select('vh_st_product_variation_id', DB::raw('SUM(quantity) as available'))
                ->where('vh_st_product_id', $item->id)
                ->where('vh_st_vendor_id', $vendor_id)
                ->whereIn('vh_st_product_variation_id', $variation_ids)
                ->groupBy('vh_st_product_variation_id')
                ->pluck('available', 'vh_st_product_variation_id'); // [variation_id => available_quantity]

            $current_qty = $quantities[$variation->id] ?? 0;

            if ($current_qty < 1) {
                // Find first variation with available quantity > 0
                $in_stock_variation = $item->productVariations->first(function ($v) use ($quantities) {
                    return ($quantities[$v->id] ?? 0) > 0;
                });

                if ($in_stock_variation) {
                    $variation = $in_stock_variation;
                }
            }
        }

        if (!$variation) {
            return null;
        }
        return self::buildResolvedVariationResponse($item, $variation, 'fallback', $vendor);
    }


    //----------------------------------------------------------


    protected static function buildResolvedVariationResponse($item, $variation, $resolvedBy = 'fallback', $vendor = null)
    {
        if (!$vendor) {
            $vendor_id = request()->query('vendor');

            if ($vendor_id) {
                $temp_vendor = Vendor::find($vendor_id);
                $is_attached = ProductVendor::where('vh_st_product_id', $item->id)
                    ->where('vh_st_vendor_id', $vendor_id)
                    ->exists();

                if ($temp_vendor && $is_attached) {
                    $vendor = $temp_vendor;
                }
            }

            // fallback to selected_vendor from product data if not resolved above

            if (!$vendor && isset($item->vendor_product_data['selected_vendor'])) {
                $vendor = (object) $item->vendor_product_data['selected_vendor'];
            }

        }

        $base_price = $variation->price;
        $available_price = $base_price;

        if ($vendor && $variation) {
            $product_price = ProductPrice::where('vh_st_product_variation_id', $variation->id)
                ->where('vh_st_vendor_id', $vendor->id)
                ->first();

            if ($product_price && !is_null($product_price->amount)) {
                $available_price = round($product_price->amount * ($item->price['currency']['rate'] ?? 1), 2);
            }
        }

        $available_quantity = $vendor
            ? Cart::getAvailableQuantity($vendor, $item->id, $variation->id)
            : 0;

        return [
            'id' => $variation->id,
            'vh_st_product_id' => $variation->vh_st_product_id,
            'uuid' => $variation->uuid,
            'name' => $variation->name,
            'slug' => $variation->slug,
            'wishlist_ids' => $variation->wishlist_ids,
            'is_default' => $variation->is_default,
            'sku' => $variation->sku ?? null,
            'price' => $base_price,
            'sale_price' => $available_price,
            'attribute_query' => $variation->attribute_query ?? null,
            'media' => $variation->medias ?? null,
            'quantity' => $variation->quantity ?? null,
            'available_quantity' => $available_quantity,
            'is_stock_available' => $available_quantity > 0 ? 1 : 0,
            'type' => $resolvedBy,
            'selected_vendor' => $vendor ? [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'slug' => $vendor->slug,
                'email' => $vendor->email,
                'phone_number' => $vendor->phone_number,
                'address' => $vendor->address,
            ] : null,
            'grouped_attributes' => $item->grouped_attributes,
        ];
    }

    //----------------------------------------------------------
    /**
     * Fast Search Approach For Homepage
     */



    public static function searchProducts(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (!$query) {
            return [
                'success' => true,
                'data' => [
                    'products' => [],
                    'brands' => [],
                    'categories' => [],
                ],
            ];
        }

        $type_limit = 5;
        $product_ids = self::select('id')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%");
            })
            ->limit($type_limit)
            ->pluck('id')
            ->toArray();

        $preloaded = self::with(['productVariations'])
            ->whereIn('id', $product_ids)
            ->get()
            ->keyBy('id');

        $products = collect($product_ids)->map(function ($id) use ($preloaded) {
            $product = $preloaded[$id] ?? null;

            if (!$product) {
                return null;
            }

            $resolved = self::getResolvedVariationWithVendor($product->id, null, null, $product);

            return [
                'type' => 'product',
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'attribute_query' => $resolved['attribute_query']?? null,
            ];
        })->filter()->values();


        // Brands
        $brands = Brand::select('id', 'name', 'slug')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%");
            })
            ->whereHas('products') //  Only brands that have at least one product
            ->limit($type_limit)
            ->get()
            ->map(fn($b) => [
                'type' => 'brand',
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
            ])
            ->values();

        // Categories
        $matched_categories = Category::with(['subCategories' => function ($q) {
            $q->select('id', 'name', 'slug', 'parent_id')
                ->whereHas('products'); //  Only subcategories with products
        }])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('slug', 'like', "%{$query}%");
            })
            ->whereHas('products')
            ->limit($type_limit)
            ->get();

        $categories = collect();

        foreach ($matched_categories as $category) {
            $categories->push([
                'type' => 'category',
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

            foreach ($category->subCategories as $child) {
                $categories->push([
                    'type' => 'category',
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                ]);
            }
        }

        $categories = $categories
            ->unique(fn ($item) => $item['type'] . '-' . $item['id'])
            ->take($type_limit)
            ->values();

        return [
            'success' => true,
            'data' => [
                'products' => $products,
                'brands' => $brands,
                'categories' => $categories,
            ],
        ];
    }




////////////////////////////////////
///
///
    /**
     * Get all available attribute combinations and matched variation for a product.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $product_id
     * @return array
     */
    public static function getAvailableCombinationsWithVariation($request, $product_id)
    {
        $selected_value_ids = self::extractSelectedValueIds($request);

        $vendor = self::resolveVendor($request);
        $selected_values = self::fetchSelectedAttributeValues($selected_value_ids);
        $variation_data = self::fetchVariationData($product_id);

        [$value_combination_map, $value_to_variations, $variation_value_map] =
            self::prepareVariationLookups($variation_data, $selected_value_ids, $vendor);

        $attribute_values = self::fetchAttributeValuePairs($product_id);
        $grouped_attributes = self::groupAttributeValues(
            $attribute_values, $selected_values, $value_to_variations, $variation_value_map
        );

        $matched_variation = self::findMatchingVariation($variation_data, $selected_value_ids);
        $product = Product::find($product_id);

        $resolved = $matched_variation
            ? Product::buildResolvedVariationResponse($product, $matched_variation, 'attribute', $vendor)
            : Product::getResolvedVariationWithVendor($product_id, $vendor);

        $attribute_query = self::buildAttributeQuery($selected_values);

        return [
            'success' => true,
            'data' => array_merge(
                $resolved ?? [],
                [
                    'attribute_query' => $attribute_query,
//                    'selected_attributes' => $selected_values,
                    'attributes_combinations' => $grouped_attributes,
                ]
            ),
        ];
    }
    /**
     * Extract selected attribute value IDs from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected static function extractSelectedValueIds($request)
    {
        $selected_value_ids = $request->input('selected_value_ids') ?? [];

        if (empty($selected_value_ids)) {
            $attribute_inputs = $request->query('attribute', []);
            if (!empty($attribute_inputs)) {
                $selected_value_ids = \DB::table('vh_st_attribute_values as av')
                    ->join('vh_st_attributes as a', 'av.vh_st_attribute_id', '=', 'a.id')
                    ->where(function ($query) use ($attribute_inputs) {
                        foreach ($attribute_inputs as $attribute_slug => $value_slug) {
                            $query->orWhere(function ($q) use ($attribute_slug, $value_slug) {
                                $q->whereRaw("REPLACE(LOWER(a.name), ' ', '-') = ?", [\Str::slug($attribute_slug)])
                                    ->whereRaw("REPLACE(LOWER(av.value), ' ', '-') = ?", [\Str::slug($value_slug)]);
                            });
                        }
                    })->pluck('av.id')->toArray();
            }
        }

        return $selected_value_ids;
    }
    /**
     * Resolve vendor instance from the request input or query string.
     *
     * @param \Illuminate\Http\Request $request
     * @return \VaahCms\Modules\Store\Models\Vendor|null
     */
    protected static function resolveVendor($request)
    {
        $vendor_id = $request->input('vendor.id') ?? $request->input('vendor_id') ?? $request->query('vendor');
        return $vendor_id ? Vendor::find($vendor_id) : null;
    }
    /**
     * Fetch details of selected attribute values using their IDs.
     *
     * @param array $selected_value_ids
     * @return \Illuminate\Support\Collection
     */
    protected static function fetchSelectedAttributeValues($selected_value_ids)
    {
        return \DB::table('vh_st_attribute_values as av')
            ->join('vh_st_attributes as a', 'av.vh_st_attribute_id', '=', 'a.id')
            ->whereIn('av.id', $selected_value_ids)
            ->select(
                'a.id as attribute_id',
                'a.name as attribute',
                'av.id as attribute_value_id',
                'av.value as attribute_value'
            )->get();
    }
    /**
     * Fetch and group product variation data based on attribute values.
     *
     * @param int $product_id
     * @return \Illuminate\Support\Collection
     */
    protected static function fetchVariationData($product_id)
    {
        return \DB::table('vh_st_product_attribute_values as pav')
            ->join('vh_st_product_attributes as pa', 'pav.vh_st_product_attribute_id', '=', 'pa.id')
            ->join('vh_st_product_variations as pv', 'pa.vh_st_product_variation_id', '=', 'pv.id')
            ->where('pv.vh_st_product_id', $product_id)
            ->whereNull('pv.deleted_at')
            ->select(
                'pv.id as variation_id',
                'pv.price',
                'pv.vh_st_product_id as product_id',
                'pav.vh_st_attribute_value_id as value_id'
            )->get()->groupBy('variation_id');
    }
    /**
     * Prepare helper maps for combinations of variations and attribute values.
     *
     * @param \Illuminate\Support\Collection $variation_data
     * @param array $selected_value_ids
     * @param \VaahCms\Modules\Store\Models\Vendor|null $vendor
     * @return array{array, array, array}
     */
    protected static function prepareVariationLookups($variation_data, $selected_value_ids, $vendor)
    {
        $value_combination_map = [];
        $value_to_variations = [];
        $variation_value_map = [];

        foreach ($variation_data as $variation_id => $records) {
            $value_ids = $records->pluck('value_id')->unique()->sort()->values()->toArray();
            $variation_value_map[$variation_id] = $value_ids;

            foreach ($value_ids as $vid) {
                $value_to_variations[$vid][] = $variation_id;
            }

            if (count(array_intersect($value_ids, $selected_value_ids)) > 0) {
                $product_id = $records->first()->product_id;
                $price = $records->first()->price;

                $sale_price = $price;
                if ($vendor) {
                    $product_price = ProductPrice::where('vh_st_product_variation_id', $variation_id)
                        ->where('vh_st_vendor_id', $vendor->id)->first();

                    if ($product_price && !is_null($product_price->amount)) {
                        $sale_price = round($product_price->amount * ($vendor->currency_rate ?? 1), 2);
                    }
                }

                $stock = $vendor
                    ? Cart::getAvailableQuantity($vendor, $product_id, $variation_id)
                    : 0;

                if ($stock > 0) {
                    foreach ($value_ids as $vid) {
                        $value_combination_map[$vid][] = [
                            'variation_id' => $variation_id,
                            'stock_quantity' => $stock,
                            'price' => $price,
                            'sale_price' => $sale_price,
                        ];
                    }
                }
            }
        }

        return [$value_combination_map, $value_to_variations, $variation_value_map];
    }
    /**
     * Fetch all attribute value pairs for a product.
     *
     * @param int $product_id
     * @return \Illuminate\Support\Collection
     */
    protected static function fetchAttributeValuePairs($product_id)
    {
        return \DB::table('vh_st_product_variations as pv')
            ->join('vh_st_product_attributes as pa', 'pv.id', '=', 'pa.vh_st_product_variation_id')
            ->join('vh_st_attributes as a', 'pa.vh_st_attribute_id', '=', 'a.id')
            ->join('vh_st_product_attribute_values as pav', 'pa.id', '=', 'pav.vh_st_product_attribute_id')
            ->join('vh_st_attribute_values as av', 'pav.vh_st_attribute_value_id', '=', 'av.id')
            ->where('pv.vh_st_product_id', $product_id)
            ->select(
                'a.id as attribute_id',
                'a.name as attribute',
                'av.id as attribute_value_id',
                'av.value as attribute_value'
            )->distinct()->get();
    }

    /**
     * Group attribute values by attribute and evaluate valid selections.
     *
     * @param \Illuminate\Support\Collection $attribute_values
     * @param \Illuminate\Support\Collection $selected_values
     * @param array $value_to_variations
     * @param array $variation_value_map
     * @return \Illuminate\Support\Collection
     */
    protected static function groupAttributeValues($attribute_values, $selected_values, $value_to_variations, $variation_value_map)
    {
        $selected_map = collect($selected_values)->pluck('attribute_value_id', 'attribute_id')->toArray();

        return $attribute_values->groupBy('attribute_id')->map(function ($items, $attribute_id) use (
            $value_to_variations, $variation_value_map, $selected_map
        ) {
            $attribute = $items->first()->attribute;

            $values = $items->map(function ($item) use (
                $value_to_variations, $variation_value_map, $selected_map, $attribute_id
            ) {
                $value_id = $item->attribute_value_id;
                $other_selected = collect($selected_map)->except($attribute_id)->values()->toArray();

                $is_valid = collect($value_to_variations[$value_id] ?? [])->contains(function ($variation_id) use (
                    $variation_value_map, $other_selected
                ) {
                    return count(array_intersect($variation_value_map[$variation_id], $other_selected)) === count($other_selected);
                });

                return [
                    'id' => $value_id,
                    'value' => $item->attribute_value,
                    'valid_for_selection' => $is_valid,
                ];
            })->values();

            return [
                'attribute_id' => $attribute_id,
                'attribute' => $attribute,
                'selected' => $selected_map[$attribute_id] ?? null,
                'values' => $values
            ];
        })->values();
    }
    /**
     * Find the exact matching variation based on selected attribute value IDs.
     *
     * @param \Illuminate\Support\Collection $variation_data
     * @param array $selected_value_ids
     * @return \VaahCms\Modules\Store\Models\ProductVariation|null
     */
    protected static function findMatchingVariation($variation_data, $selected_value_ids)
    {
        foreach ($variation_data as $variation_id => $records) {
            $value_ids = $records->pluck('value_id')->unique()->sort()->values()->toArray();
            if (
                count($value_ids) === count($selected_value_ids) &&
                count(array_intersect($value_ids, $selected_value_ids)) === count($selected_value_ids)
            ) {
                return ProductVariation::find($variation_id);
            }
        }
        return null;
    }
    /**
     * Generate query string for selected attribute values.
     *
     * @param \Illuminate\Support\Collection $selected_values
     * @return string
     */
    protected static function buildAttributeQuery($selected_values)
    {
        return '?' . collect($selected_values)
                ->map(fn($v) => 'attribute[' . \Str::slug(strtolower($v->attribute)) . ']=' . \Str::slug(strtolower($v->attribute_value)))
                ->implode('&');
    }

    //----------------------------------------------------------

    public static function getListWithSearch(Request $request)
    {

        [$brand_ids, $category_ids, $type_ids] = self::getFilterIds($request);

        $base_query = self::buildBaseQuery($request, $brand_ids, $category_ids, $type_ids);
        // Apply filters from getList()
        $base_query->isActiveFilter($request->filter);
        $base_query->trashedFilter($request->filter);
        $base_query->searchFilter($request->filter);
        $base_query->statusFilter($request->filter);
        $base_query->quantityFilter($request->filter);
        $base_query->productVariationFilter($request->filter);
        $base_query->vendorFilter($request->filter);
        $base_query->brandFilter($request->filter);
        $base_query->dateFilter($request->filter);
        $base_query->productTypeFilter($request->filter);
        $base_query->categoryFilter($request->filter);
        $base_query->priceFilter($request->filter);
        $base_query->featuredHomePageFilter($request->filter);
        $base_query->featuredCategoryPageFilter($request->filter);
        $base_query->newArrivalsFilter($request->filter);
        $base_query->topSellingsFilter($request->filter);



        $rows = $request->get('rows', config('vaahcms.per_page'));
        $products = (clone $base_query)

            ->orderBy('created_at', 'desc')
            ->paginate($rows)
            ->appends($request->query());

        $matched_product_ids = (clone $base_query)->pluck('id')->toArray();
        $products_with_variations = self::attachResolvedVariations($products);
        $sort = $request->input('filter.sort') ?? null;
        // Apply price sorting if requested
        $products_with_variations = self::sortCollectionByPrice($products_with_variations, $sort);
        $filters = [
            'brands'     => self::getBrandFacet($request, $brand_ids, $category_ids, $type_ids),
            'categories' => self::getCategoryFacet($request, $brand_ids, $category_ids, $type_ids),
            'types'      => self::getTypeFacet($request, $brand_ids, $category_ids, $type_ids),
            'attributes' => self::getAttributeFacet($request, $matched_product_ids)
        ];

        $response = [
            'success' => true,
            'data' => $products_with_variations->toArray(),
        ];
        $response['data']['filters']=$filters;
        return $response;

    }
    //----------------------------------------------------------

    protected static function getFilterIds(Request $request)
    {
        $brand_ids = Brand::whereIn('slug', (array) $request->get('brand', []))->pluck('id')->toArray();
        $category_ids = Category::whereIn('slug', (array) $request->get('category', []))->pluck('id')->toArray();
        $type_ids = Taxonomy::whereIn('slug', (array) $request->get('type', []))->pluck('id')->toArray();

        return [$brand_ids, $category_ids, $type_ids];
    }
    //----------------------------------------------------------

    protected static function buildBaseQuery(Request $request, $brand_ids, $category_ids, $type_ids)
    {
        $search = $request->get('q', null);
// Get selected store ID or fallback to default
        $selected_store_id = $request->input('selected_store') ??
            Store::where('is_default', 1)->value('id');

        $query = self::with(['brand', 'productCategories', 'type', 'store', 'store.defaultCurrency'])
            ->where('vh_st_store_id', $selected_store_id);

        // Apply search terms
        if (!empty($search)) {
            $search_terms = preg_split('/\s+/', $search); // split by space into words

            $query->whereRaw("MATCH(name, slug, summary) AGAINST(? IN BOOLEAN MODE)", [$search])
                ->orWhere(function ($q) use ($search_terms) {
                    foreach ($search_terms as $term) {
                        $q->orWhere('name', 'like', "%{$term}%")
                            ->orWhere('slug', 'like', "%{$term}%")
                            ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$term}%"))
                            ->orWhereHas('productCategories', fn($c) => $c->where('name', 'like', "%{$term}%"));
                    }
                });

        }

        if ($brand_ids) {
            $query->whereIn('vh_st_brand_id', $brand_ids);
        }

        if ($category_ids) {
            $query->whereHas('productCategories', fn($q) => $q->whereIn('vh_st_categories.id', $category_ids));
        }

        if ($type_ids) {
            $query->whereIn('taxonomy_id_product_type', $type_ids);
        }

        return $query;
    }
    //----------------------------------------------------------


    protected static function getBrandFacet(Request $request, $brand_ids, $category_ids, $type_ids)
    {
        return self::facetScope($request, $brand_ids, $category_ids, $type_ids, 'brands')
            ->select('vh_st_brand_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('vh_st_brand_id')
            ->groupBy('vh_st_brand_id')
            ->with('brand:id,name,slug,image')
            ->get()
            ->map(fn($row) => [
                'id'       => $row->vh_st_brand_id,
                'name'     => $row->brand?->name,
                'slug'     => $row->brand?->slug,
                'image'    => $row->brand?->image,
                'count'    => (int) $row->count,
                'selected' => in_array($row->vh_st_brand_id, $brand_ids),
            ])
            ->values();
    }
    //----------------------------------------------------------

    protected static function getCategoryFacet(Request $request, $brand_ids, $category_ids, $type_ids)
    {
        $search = $request->get('q', null);

        $categories = DB::table('vh_st_product_categories as pc')
            ->join('vh_st_products as p', 'p.id', '=', 'pc.vh_st_product_id')
            ->when($search, fn($q) => $q->whereFullText(['p.name', 'p.slug'], $search))
            ->when($brand_ids, fn($q) => $q->whereIn('p.vh_st_brand_id', $brand_ids))
            ->when($type_ids, fn($q) => $q->whereIn('p.taxonomy_id_product_type', $type_ids))
            ->select('pc.vh_st_category_id as id', DB::raw('COUNT(DISTINCT p.id) as count'))
            ->groupBy('pc.vh_st_category_id')
            ->get()
            ->keyBy('id');

        $category_models = Category::whereIn('id', $categories->keys())->get()->keyBy('id');

        return $categories->map(fn($row) => [
            'id'       => $row->id,
            'name'     => $category_models[$row->id]->name ?? null,
            'slug'     => $category_models[$row->id]->slug ?? null,
            'count'    => (int) $row->count,
            'selected' => in_array($row->id, $category_ids),
        ])->values();
    }
    //----------------------------------------------------------

    protected static function getTypeFacet(Request $request, $brand_ids, $category_ids, $type_ids)
    {
        return self::facetScope($request, $brand_ids, $category_ids, $type_ids, 'types')
            ->select('taxonomy_id_product_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('taxonomy_id_product_type')
            ->groupBy('taxonomy_id_product_type')
            ->with('type:id,name,slug')
            ->get()
            ->map(fn($row) => [
                'id'       => $row->taxonomy_id_product_type,
                'name'     => $row->type?->name,
                'slug'     => $row->type?->slug,
                'count'    => (int) $row->count,
                'selected' => in_array($row->taxonomy_id_product_type, $type_ids),
            ])
            ->values();
    }
    //----------------------------------------------------------

    protected static function getAttributeFacet(Request $request, $matchedProductIds)
    {
        $attributes = DB::table('vh_st_product_variations as pv')
            ->join('vh_st_product_attributes as pa', 'pv.id', '=', 'pa.vh_st_product_variation_id')
            ->join('vh_st_attributes as a', 'pa.vh_st_attribute_id', '=', 'a.id')
            ->join('vh_st_product_attribute_values as pav', 'pa.id', '=', 'pav.vh_st_product_attribute_id')
            ->join('vh_st_attribute_values as av', 'pav.vh_st_attribute_value_id', '=', 'av.id')
            ->whereIn('pv.vh_st_product_id', $matchedProductIds)
            ->whereNull('pv.deleted_at')
            ->whereNull('pa.deleted_at')
            ->whereNull('pav.deleted_at')
            ->select(
                'a.id as attribute_id',
                'a.name as attribute',
                'av.id as attribute_value_id',
                'av.value as attribute_value',
                DB::raw('COUNT(DISTINCT pv.vh_st_product_id) as count')
            )
            ->groupBy('a.id', 'a.name', 'av.id', 'av.value')
            ->get();

        return $attributes->groupBy('attribute_id')->map(function ($items, $attribute_id) use ($request) {
            return [
                'attribute_id' => $attribute_id,
                'attribute'    => $items->first()->attribute,
                'values'       => $items->map(function ($item) use ($request, $attribute_id) {
                    $selectedValues = (array) $request->get('attribute_' . $attribute_id, []);
                    return [
                        'id'       => $item->attribute_value_id,
                        'value'    => $item->attribute_value,
                        'count'    => $item->count,
                        'selected' => in_array($item->attribute_value_id, $selectedValues),
                    ];
                })->values(),
            ];
        })->values();
    }
    //----------------------------------------------------------

    protected static function attachResolvedVariations($products)
    {
        $product_ids = $products->pluck('id')->toArray();

        $preloaded = self::with(['productVariations'])
            ->whereIn('id', $product_ids)
            ->get()
            ->keyBy('id');

        $products->getCollection()->transform(function ($item) use ($preloaded) {
            $resolved = self::getResolvedVariationWithVendor($item->id, null, null, $preloaded[$item->id] ?? null);
            if ($resolved) {
                $item->product_variation = $resolved;
            }
            return $item;
        });

        return $products;
    }
    //----------------------------------------------------------

    protected static function facetScope(Request $request, $brand_ids, $category_ids, $type_ids, $exclude)
    {
        $search = $request->get('q', null);
        $q = self::query();

        if ($search) {
            $q->where(function ($q) use ($search) {
                $q->whereFullText(['name', 'slug'], $search)
                    ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('productCategories', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($exclude == 'brands' && $brand_ids) {
            $q->whereIn('vh_st_brand_id', $brand_ids);
        }
        if ($exclude == 'categories' && $category_ids) {
            $q->whereHas('productCategories', fn($sq) => $sq->whereIn('vh_st_categories.id', $category_ids));
        }
        if ($exclude == 'types' && $type_ids) {
            $q->whereIn('taxonomy_id_product_type', $type_ids);
        }

        return $q;
    }
    //----------------------------------------------------------



}
