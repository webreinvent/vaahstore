<?php namespace VaahCms\Modules\Store\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Faker\Factory;
use VaahCms\Modules\Store\Services\CurrencyConverterService;
use VaahCms\Modules\Store\Traits\ApiAuthUser;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use VaahCms\Modules\Store\Models\User as StoreUser;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class Cart extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;
    use ApiAuthUser;
    //-------------------------------------------------
    protected $table = 'vh_st_carts';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    //-------------------------------------------------
    protected $fill_except = [

    ];
    //-------------------------------------------------
    protected $appends = ['cart_products_count'
    ];
    //-------------------------------------------------

    public function getCartProductsCountAttribute()
    {
        $store_id = request()->selected_store ?? Store::where('is_default', 1)->value('id');

        return $this->products
            ->filter(fn($product) => $product->vh_st_store_id == $store_id)
            ->sum(fn($product) => $product->pivot->quantity);
    }
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

        $empty_item['is_active'] = 1;

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
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'vh_user_id');
    }
    //-------------------------------------------------
    public function products()
    {
        return $this->belongsToMany(Product::class, 'vh_st_cart_products', 'vh_st_cart_id', 'vh_st_product_id')
            ->withPivot('vh_st_product_variation_id', 'quantity','vh_st_vendor_id','id')
            ->withTimestamps();
    }
    //-------------------------------------------------
    public function productVariations()
    {
        return $this->belongsToMany(ProductVariation::class, 'vh_st_cart_products', 'vh_st_cart_id', 'vh_st_product_variation_id')
            ->withPivot('vh_st_product_id', 'quantity','vh_st_vendor_id')->with('product')
            ->withTimestamps();
    }
    //-------------------------------------------------
    public function cartItems()
    {
        return $this->belongsToMany(self::class, 'vh_st_cart_products', 'vh_st_cart_id', 'id')
            ->withPivot('vh_st_product_variation_id', 'quantity','vh_st_vendor_id');
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
    public function scopeFindByIdOrUuid($query, $value)
    {
        if (is_numeric($value)) {
            return $query->where('id', $value);
        }
        return $query->where('uuid', $value);
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
        $search_array = explode(' ',$filter['q']);
        foreach ($search_array as $search_item){
            $query->where(function ($q1) use ($search_item) {
                $q1->where('name', 'LIKE', '%' . $search_item . '%')
                    ->orWhereHas('user', function ($q) use ($search_item) {
                        $q->where('name', 'LIKE', '%' . $search_item . '%')
                            ->orWhere('username', 'LIKE', '%' . $search_item . '%')
                            ->orWhere('phone', 'LIKE', '%' . $search_item . '%')
                            ->orWhere('email', 'LIKE', '%' . $search_item . '%');
                    });
            });
        }

    }
    //-------------------------------------------------
    public function scopeGuestCartFilter($query, $filter)
    {
        if(!isset($filter['guest'])
            || is_null($filter['guest'])
            || $filter['guest'] === 'null'
        )
        {
            return $query;
        }
        $guest = $filter['guest'];
        if ($guest === 'include') {
            return $query;
        } else if($guest === 'exclude'){
            return $query->whereNotNull('vh_user_id');
        }else if($guest === 'only'){
            return $query->whereNull('vh_user_id');
        }
        return $query;

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $include = request()->query('include', []);
        $exclude = request()->query('exclude', []);
        $active_auth_user_id= self::getApiAuthUserId();
        $excluded_relationships = collect($exclude)
            ->filter(fn($val) => $val === 'true')
            ->keys()
            ->flatMap(fn($key) => array_map('trim', explode(',', $key)))
            ->unique()
            ->values();
        $default_relationships = [];
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
        $list = self::getSorted($request->filter)->with('user')->withCount('products');
        if ($active_auth_user_id) {
            $list->where('vh_user_id', $active_auth_user_id);
        }
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->guestCartFilter($request->filter);

        $rows = config('vaahcms.per_page');

        if($request->has('rows'))
        {
            $rows = $request->rows;
        }

        $list = $list->paginate($rows);
        $list->getCollection()->transform(function ($item) use ($excluded_relationships) {
            // Prevent accessors from being evaluated
            $item->makeHidden($excluded_relationships->toArray());
            return $item;
        });
        return [
            'success' => true,
            'data' => $list->toArray(),
        ];


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

        $items = self::whereIn('id', $items_id);

        switch ($inputs['type']) {

            case 'trash':
                self::whereIn('id', $items_id)
                    ->get()->each->delete();
                break;
            case 'restore':
                self::whereIn('id', $items_id)->onlyTrashed()
                    ->get()->each->restore();
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
        if(session()->has('vh_user_id')) {
            $cart_user_id = collect($inputs['items'])->pluck('user.id')->toArray();
            $session_user_id = session('vh_user_id');

            if (in_array($session_user_id, $cart_user_id)) {
                session()->forget('vh_user_id');
            }
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        self::with('products')->whereIn('id', $items_id)->each(function ($item) {
            $item->products()->detach();
        });
        self::whereIn('id', $items_id)->forceDelete();

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }
    //-------------------------------------------------
     public static function listAction($request, $type): array
    {

        $list = self::query();

        if($request->has('filter')){
            $list->getSorted($request->filter);
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
        }

        switch ($type) {

            case 'trash-all':
                $list->get()->each->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->get()
                    ->each->restore();
                break;
            case 'delete-all':
                $items = self::withTrashed()->get();
                foreach ($items as $item) {
                    $item->products()->detach();
                }
                session()->forget('vh_user_id');
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
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }
    //-------------------------------------------------
    public static function getItem($request, $id)
    {
        $selected_store_id = $request->input('selected_store')
            ?? Store::where('is_default', 1)->value('id');

        $item = self::with([
            'createdByUser',
            'updatedByUser',
            'deletedByUser',
            'user:id,username,email',
            'products' => function ($query) use ($selected_store_id) {
                $query->where('vh_st_store_id', $selected_store_id);
            }
        ])
            ->withTrashed()
            ->findByIdOrUuid($id)
            ->first();

        if (!$item) {
            return [
                'success' => false,
                'errors' => ['Record not found with identifier: ' . $id],
            ];
        }

        if ($item->products->isEmpty()) {
            return ['success' => true, 'data' => $item];
        }

        // Modularized: fetch related data
        [$variations, $vendors, $prices] = self::getProductRelatedData($item->products);

        // Modularized: fetch wishlist data
        [$wishlist_products, $user_wishlists] = self::getUserWishlistData($item->vh_user_id);

        // Modularized: calculate total amount and enrich products
        $total_amount = self::calculateCartTotalAndEnrichProducts(
            $item->products, $variations, $vendors, $prices, $wishlist_products, $user_wishlists
        );
        $item->setAttribute('total_amount', $total_amount);
        return [
            'success' => true,
            'data' => $item,
        ];
    }

    /**
     * Fetch variations, vendors, and prices for products.
     */
    protected static function getProductRelatedData($products)
    {
        $variation_ids = $products->pluck('pivot.vh_st_product_variation_id')->filter()->unique();
        $vendor_ids = $products->pluck('pivot.vh_st_vendor_id')->filter()->unique();

        $variations = ProductVariation::whereIn('id', $variation_ids)->get()->keyBy('id');
        $vendors = Vendor::whereIn('id', $vendor_ids)->get()->keyBy('id');
        $prices = ProductPrice::whereIn('vh_st_product_variation_id', $variation_ids)
            ->whereIn('vh_st_vendor_id', $vendor_ids)
            ->get()
            ->mapWithKeys(fn($p) => [$p->vh_st_product_variation_id . '_' . $p->vh_st_vendor_id => $p->amount]);

        return [$variations, $vendors, $prices];
    }

    /**
     * Fetch wishlist products and user wishlists for a user.
     */
    protected static function getUserWishlistData($user_id)
    {
        $wishlist_products = collect();
        $user_wishlists = collect();

        if ($user_id) {
            $wishlist_ids = UserWishlist::where('vh_user_id', $user_id)->pluck('id');
            if ($wishlist_ids->isNotEmpty()) {
                $wishlist_products = UserWishlistProduct::whereIn('vh_st_user_wishlist_id', $wishlist_ids)
                    ->get()
                    ->keyBy(fn($item) => "{$item->vh_st_product_id}_{$item->vh_st_product_variation_id}");
                $user_wishlists = UserWishlist::whereIn('id', $wishlist_ids)->get()->keyBy('id');
            }
        }

        return [$wishlist_products, $user_wishlists];
    }

    /**
     * Calculate total cart amount and enrich product pivots.
     */
    protected static function calculateCartTotalAndEnrichProducts(
        $products, $variations, $vendors, $prices, $wishlist_products, $user_wishlists
    ) {
        return $products->map(function ($product) use ($variations, $vendors, $prices, $wishlist_products, $user_wishlists) {
            $pivot = $product->pivot;
            $variation_id = $pivot->vh_st_product_variation_id;
            $vendor_id = $pivot->vh_st_vendor_id;
            $product_id = $product->id;

            $pivot_qty = $pivot->quantity;

            //Fetch related data from pre-keyed collections (no DB hits)
            $variation = $variations->get($variation_id);
            $vendor = $vendors->get($vendor_id);
            $variation_name = $variation?->name;

            // Stock checks
            $is_qty_available = self::isCartItemQuantityAvailable($vendor, $product_id, $variation_id);
            $available_qty = $is_qty_available ? self::getAvailableQuantity($vendor, $product_id, $variation_id) : 0;
            $is_valid_qty = $pivot_qty <= $available_qty;

            // Price calculation
            $price_key = "{$variation_id}_{$vendor_id}";
            $raw_price = $prices->get($price_key);
            $price_from_variation = is_null($raw_price);
            $price = $price_from_variation
                ? ProductVariation::getPriceOfProductVariants($variation_id)
                : $raw_price;

            $rate = $product->price['currency']['rate'] ?? 1;
            $converted_price = $price_from_variation ? $price : round($rate * $price, 2);
            $subtotal = $converted_price * $pivot_qty;

            // Wishlist checks
            $wishlist_key = "{$product_id}_{$variation_id}";
            $wishlist_product = $wishlist_products->get($wishlist_key);
            $wishlist_id = optional($user_wishlists->get($wishlist_product?->vh_st_user_wishlist_id))->vh_st_wishlist_id;

            // Fill pivot and product details for frontend use
            $pivot->fill([
                'is_stock_available' => ($is_qty_available && $is_valid_qty) ? 1 : 0,
                'is_wishlisted' => $wishlist_product ? 1 : 0,
                'vh_st_wishlist_id' => $wishlist_id,
                'cart_product_variation' => $variation_name,
                'price' => $converted_price,
            ]);

            $product->available_stock_quantity = $available_qty;

            return $subtotal;
        })->sum();
    }


    //-------------------------------------------------
    public static function getAvailableQuantity($vendor, $product_id, $variation_id)
    {
        $stock = $vendor->productStocks()
            ->where('vh_st_product_id', $product_id)
            ->where('vh_st_product_variation_id', $variation_id)
            ->first();

        return $stock ? $stock->quantity : 0;
    }

    //-------------------------------------------------

    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }
        $item->forceDelete();

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans("vaahcms-general.record_has_been_deleted");

        return $response;
    }
    //-------------------------------------------------
    public static function itemAction($request, $id, $type): array
    {
        switch($type)
        {

            case 'trash':
                self::find($id)
                    ->delete();
                break;
            case 'restore':
                self::where('id', $id)
                    ->onlyTrashed()
                    ->first()->restore();
                break;
        }

        return self::getItem($id);
    }
    //-------------------------------------------------



    public static function validationShippingAddress($inputs)
    {

        $rules = array(
            'country' => 'required',
            'name' => 'required|max:100',
            'phone' => 'required|numeric',
            'address_line_1' => 'required|max:100',
            'pin_code' => 'required|max:10',
            'city' => 'required|max:100',
            'state' => 'required|max:100',
        );

        $messages = array(
            'name.required' => 'The Name field is required.',
            'phone.required' => 'The Phone field is required.',
            'name.max' => 'The Name field may not be greater than :max characters.',
            'country.required' => 'The Country field is required.',
            'state.required' => 'The State field is required.',
            'pin_code.required' => 'The Pin Code field is required.',
            'city.required' => 'The City field is required.',
            'state.max' => 'The State field may not be greater than :max characters.',
            'address_line_1.required' => 'The Address  field is required.',
            'address_line_1.max' => 'The Address  field may not be greater than :max characters.',
            'city.max' => 'The City field may not be greater than :max characters.',
            'pin_code.max' => 'The Pin Code field may not be greater than :max digits.',

        );

        $validator = \Validator::make($inputs, $rules,$messages);
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


    public static function updateQuantity($uuid, $request)
    {
        $products = $request['products']; // List of products with their details

        if (empty($products)) {
            return [
                'success' => false,
                'errors' => ['Cart products list is required.']
            ];
        }

        $active_auth_user_id = self::getApiAuthUserId(); // Auth user ID
        $user = $active_auth_user_id ? StoreUser::find($active_auth_user_id) : null;

        $cart = null;

        if ($active_auth_user_id) {
            $cart = Cart::where('vh_user_id', $user->id)->first();
        }
        // If cart not found by user, try by UUID from request
        if (!$cart && $uuid) {
            $cart = Cart::findByIdOrUuid($uuid)->first();
        }
        // Still no cart? Create one
        if (!$cart) {
            $cart = Product::findOrCreateCart($user);
        }
        // If cart exists but not assigned to user yet
        elseif ($user && !$cart->vh_user_id) {
            $cart->vh_user_id = $user->id;
            $cart->save();
        }

        if (!$cart) {
            return [
                'success' => false,
                'errors' => [trans("vaahcms-general.record_not_found")],
            ];
        }

        $updated_product_keys = [];

        foreach ($products as $product_detail) {
            $product_id = $product_detail['vh_st_product_id'];
            $variation_id = $product_detail['vh_st_product_variation_id'];
            $vendor_id = $product_detail['vh_st_vendor_id'];
            $new_quantity = $product_detail['quantity'];

            if (!is_numeric($new_quantity) || $new_quantity < 0) {
                return [
                    'success' => false,
                    'errors' => ["Invalid quantity for product ID {$product_id}."]
                ];
            }

            $key = "{$product_id}_{$variation_id}_{$vendor_id}";

            if ($new_quantity < 1) {
                $cart_product_ids = $cart->products()
                    ->wherePivot('vh_st_cart_id', $cart->id)
                    ->wherePivot('vh_st_product_id', $product_id)
                    ->wherePivot('vh_st_product_variation_id', $variation_id)
                    ->wherePivot('vh_st_vendor_id', $vendor_id)
                    ->pluck('vh_st_cart_products.id')
                    ->toArray();

                foreach ($cart_product_ids as $cart_product_id) {
                    $cart->cartItems()->detach($cart_product_id);
                }
            } else {
                $existing_cart_product = $cart->products()
                    ->wherePivot('vh_st_product_id', $product_id)
                    ->wherePivot('vh_st_product_variation_id', $variation_id)
                    ->wherePivot('vh_st_vendor_id', $vendor_id)
                    ->first();

                if ($existing_cart_product) {
                    $pivot = $existing_cart_product->pivot;
                    $pivot->quantity = $new_quantity;
                    $pivot->save();
                } else {
                    $cart->products()->attach($product_id, [
                        'quantity' => $new_quantity,
                        'vh_st_product_variation_id' => $variation_id,
                        'vh_st_vendor_id' => $vendor_id,
                    ]);
                }
            }

            $updated_product_keys[] = $key;
        }

        $cart->load('products');

        $selected_store_id = request()->input('selected_store');

        // Apply selected store filtering
        $filtered_cart_products = $cart->products();
        if ($selected_store_id) {
            $filtered_cart_products = $filtered_cart_products
                ->where('vh_st_store_id', $selected_store_id);
        }
        $filtered_cart_products = $filtered_cart_products->get();

        $filtered_products = $filtered_cart_products->filter(function ($product) use ($updated_product_keys) {
            $pivot = $product->pivot;
            $key = "{$pivot->vh_st_product_id}_{$pivot->vh_st_product_variation_id}_{$pivot->vh_st_vendor_id}";
            return in_array($key, $updated_product_keys, true);
        })->values();

        $total_subtotal_amount = $filtered_cart_products->sum(function ($product) {
            $pivot = $product->pivot;
            $vendor_id = $pivot->vh_st_vendor_id;
            $variation_id = $pivot->vh_st_product_variation_id;
            $quantity = $pivot->quantity;

            $raw_price = ProductPrice::where('vh_st_product_variation_id', $variation_id)
                ->where('vh_st_vendor_id', $vendor_id)
                ->value('amount');

            $price_from_variation = false;

            if (is_null($raw_price)) {
                $price = ProductVariation::getPriceOfProductVariants($variation_id);
                $price_from_variation = true;
            } else {
                $price = $raw_price;
            }

            $price_data = $product->price;
            $rate = $price_data['currency']['rate'] ?? 1;
            $converted_price = $price_from_variation ? $price : round($rate * $price, 2);

            return $converted_price * $quantity;
        });

        return [
            'success' => true,
            'messages' => [trans("vaahcms-general.saved_successfully")],
            'data' => [
                'id' => $cart->id,
                'uuid' => $cart->uuid,
                'cart_products_count' => $cart->cart_products_count,
                'user_id' => $cart->vh_user_id,
                'total_amount' => $total_subtotal_amount,
                'products' => self::formatCartProducts($filtered_products),
            ],
        ];
    }


    //-------------------------------------------------
    public static function deleteCartItem($request, $id, $action)
    {
        if ($action !== 'delete') {
            return [
                'data' => null,
                'success' => false,
                'messages' => [trans("vaahcms-general.invalid_action")],
            ];
        }
        $cart = self::findByIdOrUuid($id)->first();
        if (!$cart) {
            return [
                'data' => null,
                'success' => false,
                'messages' => [trans("vaahcms-general.record_not_found_with_id") . $id],
            ];
        }
        $variation_id = $request['item']['vh_st_product_variation_id'] ?? null;
        $cart_item_id = $request['item']['id'];
        $product_id = $request['item']['vh_st_product_id'] ?? null;
        if ($variation_id === null) {
            $cart->products()->detach($product_id);
            Session::forget('vh_user_id');
        } else {
            $cart_product_table_ids = $cart->products()
                ->wherePivot('id', $cart_item_id)
                ->pluck('vh_st_cart_products.id')
                ->toArray();

            if (!empty($cart_product_table_ids)) {
                foreach ($cart_product_table_ids as $cart_product_id) {
                    $cart->cartItems()->detach($cart_product_id);
                    Session::forget('vh_user_id');
                }
            }
        }
        $response = self::getItem(request(), $cart->id);
        $response['messages'][] = trans("vaahcms-general.record_deleted");
        return $response;

    }

    //-------------------------------------------------


    public static function getCartItemDetailsAtCheckout($request, $id)
    {
        $selected_store_id = $request->input('selected_store') ?? Store::where('is_default', 1)->value('id');
        $buy_now = (bool) $request->input('buy_now');
        // Eager load user and products
        $cart = self::with(['user:id,username,email', 'products'])
            ->findByIdOrUuid($id)
            ->first();

        if (!$cart || !$cart->user) {
            return [
                'success' => false,
                'errors' => ['Cart or user not found. Please attach a user to proceed.'],
                'data' => null,
            ];
        }

        $user = $cart->user;
        $taxonomy_id_shipping = Taxonomy::getTaxonomyByType('address-types')->where('slug', 'shipping')->value('id');
        $taxonomy_id_billing = Taxonomy::getTaxonomyByType('address-types')->where('slug', 'billing')->value('id');

        $user_addresses = Address::where('vh_user_id', $user->id)
            ->where('taxonomy_id_address_types', $taxonomy_id_shipping)
            ->get();

        $user_billing_addresses = Address::where('vh_user_id', $user->id)
            ->where('taxonomy_id_address_types', $taxonomy_id_billing)
            ->get();

        //  Filter products based on store and optionally buy_now mode
        $products = $cart->products->filter(function ($product) use ($selected_store_id, $buy_now, $request) {
            if ($product->vh_st_store_id != $selected_store_id) return false;

            if ($buy_now) {
                $match = collect($request->input('products', []))->first(function ($item) use ($product) {
                    return $product->id == $item['id'] &&
                        $product->pivot->vh_st_product_variation_id == $item['variation_id'] &&
                        $product->pivot->vh_st_vendor_id == $item['vendor_id'];
                });
                return !is_null($match);
            }

            return true;
        });
        [$variations, $vendors, $prices] = self::getProductRelatedData($products);
        [$wishlist_products, $user_wishlists] = self::getUserWishlistData($user->id);

        // Build enriched product details
        $enriched_products = $products->map(function ($product) use ($variations, $vendors, $prices, $wishlist_products, $user_wishlists,$request) {
            $pivot = $product->pivot;
            $variation_id = $pivot->vh_st_product_variation_id;
            $vendor_id = $pivot->vh_st_vendor_id;
            $product_id = $product->id;

            $variation = $variations->get($variation_id);
            $vendor = $vendors->get($vendor_id);

            $variation_name = $variation?->name ?? null;
            $is_qty_available = self::isCartItemQuantityAvailable($vendor, $product_id, $variation_id);
            $available_qty = $is_qty_available ? self::getAvailableQuantity($vendor, $product_id, $variation_id) : 0;
            $pivot_qty = $pivot->quantity;
            if ($request->boolean('buy_now')) {
                $match = collect($request->input('products'))->first(function ($item) use ($product_id, $variation_id, $vendor_id) {
                    return $item['id'] == $product_id &&
                        $item['variation_id'] == $variation_id &&
                        $item['vendor_id'] == $vendor_id;
                });

                if ($match && isset($match['quantity'])) {
                    $pivot_qty = $match['quantity'];
                    $pivot->quantity = $pivot_qty;
                }
            }
            // Exclude product if requested quantity is greater than available
            if ($pivot_qty > $available_qty) {
                return null;
            }

            $price_key = "{$variation_id}_{$vendor_id}";
            $raw_price = $prices->get($price_key);
            $price_from_variation = false;

            if (is_null($raw_price)) {
                $price = ProductVariation::getPriceOfProductVariants($variation_id);
                $price_from_variation = true;
            } else {
                $price = $raw_price;
            }
            $price_data = $product->price;
            $rate = $price_data['currency']['rate'] ?? 1;
            $converted_price = $price_from_variation ? $price : round($rate * $price, 2);
            $subtotal = $converted_price * $pivot_qty;

            $wishlist_key = "{$product_id}_{$variation_id}";
            $wishlist_product = $wishlist_products->get($wishlist_key);

            // Use already-fetched vendor and variation for response
            $product_variation = Product::buildResolvedVariationResponse($product, $variation, 'fallback', $vendor);
            $product->setAttribute('product_variation', $product_variation);

            // Enrich the pivot object
            $pivot->is_stock_available = ($is_qty_available && $pivot_qty <= $available_qty) ? 1 : 0;
            $pivot->is_wishlisted = $wishlist_product ? 1 : 0;
            $pivot->vh_st_wishlist_id = optional($user_wishlists->get($wishlist_product?->vh_st_user_wishlist_id))->vh_st_wishlist_id;
            $pivot->cart_product_variation = $variation_name;
            $pivot->price = $converted_price;
            $pivot->available_stock_quantity = $available_qty;
            $pivot->total_price = $subtotal;

            $product->available_stock_quantity = $available_qty;
            $product->makeHidden(['vendor_product_data','is_default_vendor_attached','grouped_attributes']);
            return $product;
        })
        ->filter() // Remove nulls (products with insufficient stock)
        ->values();

        $total_mrp = $enriched_products->sum(fn($product) => $product->pivot->total_price);

        return [
            'success' => true,
            'data' => [
                'products' => $enriched_products,
                'user_addresses' => $user_addresses,
                'user_billing_addresses' => $user_billing_addresses,
                'user' => $user,
                'total_mrp' => $total_mrp,
            ],
        ];
    }


    protected static function isCartItemQuantityAvailable($vendor, $product_id, $variation_id)
    {

        if ($vendor === null || $product_id === null || $variation_id === null) {
            return false;
        }

        return $vendor->productStocks()
            ->where('vh_st_product_id', $product_id)
            ->where('vh_st_product_variation_id', $variation_id)
            ->where('quantity', '>', 0)
            ->where('is_active', 1)
            ->exists();
    }
    //-------------------------------------------------

    private static function getProductMediaIds($product)
    {
        if ($product->pivot->vh_st_product_id && $product->pivot->vh_st_product_variation_id) {
            $media_ids = $product->productVariationMedia()
                ->where('vh_st_product_variation_id', $product->pivot->vh_st_product_variation_id)
                ->pluck('vh_st_product_media_id')
                ->toArray();

            if (!empty($media_ids)) {
                return $media_ids;
            }
        }

        return ProductMedia::where('vh_st_product_id', $product->id)->pluck('id')->toArray();
    }
    //-------------------------------------------------

    public static function getProductMediaIdsAtOrder($product,$product_variation_id)
    {
            return $product->productVariationMedia()
                ->where('vh_st_product_variation_id',$product_variation_id)
                ->pluck('vh_st_product_media_id')->toArray();

    }
    //-------------------------------------------------

    public static function getImageUrls($product_media_ids)
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
    //-------------------------------------------------

    private static function getProductVariation($product)
    {
        $variation_id = $product->pivot->vh_st_product_variation_id;
        $variation = ProductVariation::find($variation_id);
        return $variation ?? null;
    }
    //-------------------------------------------------

    private static function getProductPrice($product)
    {
        $variation_id = $product->pivot->vh_st_product_variation_id;
        return ProductVariation::getPriceOfProductVariants($variation_id);
    }


    //-------------------------------------------------

    public static function saveCartUserAddress($request){
        $inputs = $request->input('user_address');

        $validation = self::validationShippingAddress($inputs);
        if (!$validation['success']) {
            return $validation;
        }
      $address_details = $request->input('user_address');
        $user_id = $request->input('user_data.id');



        $taxonomy_id_address_status = Taxonomy::getTaxonomyByType('address-status')->where('slug', 'approved')->value('id');
        $taxonomy_id_shipping_address_type = Taxonomy::getTaxonomyByType('address-types')->where('slug', 'shipping')->value('id');
        $taxonomy_id_billing_address_type = Taxonomy::getTaxonomyByType('address-types')->where('slug', 'billing')->value('id');

        if (!$taxonomy_id_address_status || !$taxonomy_id_shipping_address_type || !$taxonomy_id_billing_address_type) {
            $response['success'] = false;
            $response['messages'][] = trans("vaahcms-general.error_saving_address");
            return $response;
        }
        $taxonomy_id_address_type = $request->input('type') === 'billing' ? $taxonomy_id_billing_address_type : $taxonomy_id_shipping_address_type;

        $address_details['vh_user_id'] = $user_id;
        $address_details['taxonomy_id_address_status'] = $taxonomy_id_address_status;
        $address_details['taxonomy_id_address_types'] = $taxonomy_id_address_type;

        $address = new Address();
        $address->fill($address_details);
        $address->save();

        $cart = Cart::where('vh_user_id', $user_id)->first();

        $response['success'] = true;
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        $response['data'] = [
            'cart_id' => $cart->id,
        ];

        return $response;
    }
    //-------------------------------------------------

    public static function removeCartUserAddress($request){
        $address_details = $request->input('user_address');
        $address_id = $request->input('user_address.id');

        $vh_user_id = $address_details['vh_user_id'];

        Address::where('id', $address_id)
            ->forceDelete();
        $cart = Cart::where('vh_user_id', $vh_user_id)->first();
        $response['messages'][] = trans("vaahcms-general.successfully_deleted");
        $response['data'] = [
            'cart_id' => $cart->id,
        ];
        return $response;
    }

    //-------------------------------------------------

    public static function updateUserShippingAddress($request){
        if (!$request->has('address_detail') || !$request->has('user_detail')) {
            return ['success' => false, 'errors' => ['Request data is incomplete']];
        }
        $inputs = $request->input('address_detail');

        $validation = self::validationShippingAddress($inputs);
        if (!$validation['success']) {
            return $validation;
        }
        $address_details = $request->input('address_detail');
        $user_id = $request->input('user_detail.id');
        $address_id = $address_details['id'];
        $address = Address::find($address_id);

        if (!$address) {
            $response['success'] = false;
            $response['messages'][] = trans("vaahcms-general.error_saving_address");
            return $response;
        }

        $address->country = $address_details['country'];
        $address->name = $address_details['name'];
        $address->phone = $address_details['phone'];
        $address->address_line_1 = $address_details['address_line_1'];
        $address->pin_code = $address_details['pin_code'];
        $address->city = $address_details['city'];
        $address->state = $address_details['state'];

        $address->save();
        $cart = Cart::where('vh_user_id', $user_id)->first();
        $response['success'] = true;
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        $response['data'] = [
            'cart_id' => $cart->id,
        ];

        return $response;
    }

    //-------------------------------------------------




    //-------------------------------------------------
    public static function placeOrder($request)
    {
        $response = self::validateOrderDetails($request);

        if (!$response['success']) {
            return $response;
        }
        $store_id = $request->order_details['vh_st_store_id'];
// Capture cart UUID before deletion
        $cart_id = $request->order_details['cart_id'] ?? null;
        $cart = $cart_id ? self::findByIdOrUuid($cart_id)->first() : null;
        $cart_uuid = $cart ? $cart->uuid : null;
        $order = self::createOrder($request);

        self::createOrderItemsAndUpdateStock($request, $order);

        self::updateProductQuantities($request);

        self::clearSessionAndCart($request);
        $cart_exists = $cart_id ? self::findByIdOrUuid($cart_id)->first() : null;

        $order_array = $order->toArray();
        $order_array['cart_uuid'] = $cart_exists ? $cart_uuid : null;
        $order_array['cart_products_count'] = $cart_exists?->cart_products_count ?? 0;

        $response['data'] = [
            'order' => $order_array
        ];

        return $response;
    }
    //-------------------------------------------------

    public static function validateOrderDetails($request)
    {
        $errors = [];

        $order_details = $request->order_details ?? [];

        // Validate cart
        $cart = self::findByIdOrUuid($order_details['cart_id'] ?? null)->first();
        if (!$cart) {
            return [
                'success' => false,
                'errors' => ['We couldn’t find your cart. Please refresh the page or try again.']
            ];
        }

        $cart_user_id = $cart->vh_user_id;
        $shipping = $request->order_details['shipping_address'] ?? null;
        if (empty($shipping)) {
            $errors[] = "Please select a shipping address to continue.";
        }
        if (!empty($shipping) && !Address::where('id', $shipping['id'])->where('vh_user_id', $cart_user_id)->exists()) {
            $errors[] = "The selected shipping address doesn't match the cart owner.";
        }

        // Validate billing address
        $billing = $request->order_details['billing_address'] ?? null;
        if (empty($billing)) {
            $errors[] = "Please select a billing address to continue.";
        }
        if (!empty($billing) && !Address::where('id', $billing['id'])->where('vh_user_id', $cart_user_id)->exists()) {
            $errors[] = "The selected billing address doesn't match the cart owner.";
        }

        // Validate payment method
        $payment_method_slug = $order_details['payment_method'] ?? null;

        if (!$payment_method_slug) {
            $errors[] = "Please select a payment method to complete your order.";
        }

        if ($payment_method_slug && !PaymentMethod::where('slug', $payment_method_slug)->exists()) {
            $errors[] = "The selected payment method is invalid. Please choose a valid option.";
        }
        

        // Validate products
        $products = collect($order_details['products'] ?? []);
        $cart_products = optional($cart)->products ?? collect();
        if ($products->isEmpty()) {
            $errors[] = "Your cart seems to be empty. Please add products to proceed.";
        } else {
            $products->each(function ($product) use (&$errors, $cart_products) {
                $pivot = collect($product['pivot'] ?? []);
                $product_id = $pivot->get('vh_st_product_id');
                $variation_id = $pivot->get('vh_st_product_variation_id');
                $quantity = $pivot->get('quantity');
                $vendor_id = $pivot->get('vh_st_vendor_id');

                if (!$product_id || !$variation_id || !$quantity || !$vendor_id) {
                    $errors[] = "Some product information is missing. Please review your cart and try again.";
                    return;
                }
                // Check that this product-variation-vendor combo exists in the cart
                $exists_in_cart = $cart_products->contains(function ($cart_product) use ($product_id, $variation_id, $vendor_id) {
                    $pivot = $cart_product->pivot;
                    return $pivot->vh_st_product_id == $product_id
                        && $pivot->vh_st_product_variation_id == $variation_id
                        && $pivot->vh_st_vendor_id == $vendor_id;
                });

                if (!$exists_in_cart) {
                    $errors[] = "A product in your order does not exist in your cart. Please refresh and try again.";
                    return;
                }
                // Validate product-variation relationship
                $variation_valid = ProductVariation::where('id', $variation_id)
                    ->where('vh_st_product_id', $product_id)
                    ->exists();

                if (!$variation_valid) {
                    $errors[] = "We couldn't verify one of the selected product options. Please remove and re-add it to the cart.";
                    return;
                }

                // Validate vendor stock
                $vendor = Vendor::find($vendor_id);
                $has_stock = $vendor?->productStocks()
                    ->where('vh_st_product_id', $product_id)
                    ->where('vh_st_product_variation_id', $variation_id)
                    ->where('quantity', '>', 0)
                    ->where('is_active', 1)
                    ->exists();

                if (!$has_stock) {
                    $errors[] = "One of the products is out of stock. Please update your cart before proceeding.";
                }
            });
        }

        return empty($errors)
            ? ['success' => true]
            : ['success' => false, 'errors' => $errors];
    }



    //-------------------------------------------------

    private static function createOrder($request)
    {
        $taxonomy_order_status = Taxonomy::getTaxonomyByType('order-status')->where('slug', 'pending')->value('id');
        $taxonomy_payment_status_id = Taxonomy::getTaxonomyByType('order-payment-status')
            ->where('slug', 'pending')
            ->value('id');
        $store_id = $request->order_details['vh_st_store_id'];
        $store = Store::find($store_id);
        $default_currency = $store->defaultCurrency ; // make sure 'currency_code' exists

        $request_currency_code = $request->order_details['currency']['code']??$default_currency['code'] ;

        // If currency is missing, assume amount is already in default currency
        if (!isset($request->order_details['currency']['code'])) {
            $amount = $request->order_details['total_amount'];
            $payable = $request->order_details['payable'];
        } else {
            $amount = self::convertToDefaultCurrency(
                $request->order_details['total_amount'],
                $request_currency_code,
                $default_currency['code']
            );

            $payable = self::convertToDefaultCurrency(
                $request->order_details['payable'],
                $request_currency_code,
                $default_currency['code']
            );
        }

        $order = new Order();

        $order->vh_user_id = $request->order_details['vh_user_id'];
        $order->vh_st_store_id = $request->order_details['vh_st_store_id'];
        $order->amount = $amount;
        $order->order_status = 'Placed';
        $order->taxonomy_id_payment_status = $taxonomy_payment_status_id;
        $order->order_shipment_status = 'Pending';
        $order->payable = $payable;
        $order->discount = $request->order_details['discounts'];
        $order->taxes = $request->order_details['taxes'];
        $order->delivery_fee = $request->order_details['delivery_fee'];
        $order->paid = 0;
        $order->is_paid = null;
        $order->is_active = 1;

        $order->save();

        return $order;
    }
    //-------------------------------------------------

    private static function createOrderItemsAndUpdateStock($request, $order)
    {
        $store_id = $request->order_details['vh_st_store_id'];
        $store = Store::find($store_id);
        $default_currency = $store->defaultCurrency ;
        $request_currency_code = $request->order_details['currency']['code'] ?? $default_currency['code'];
        $taxonomy_order_items_type = Taxonomy::getTaxonomyByType('order-items-types')->where('slug', 'cod')->value('id');
        $taxonomy_order_items_status = Taxonomy::getTaxonomyByType('order-items-status')->where('slug', 'approved')->value('id');

        foreach ($request->order_details['products'] as $item) {
            if (!isset($request->order_details['currency']['code'])) {
                $converted_price = $item['pivot']['price'];
            } else {
                $converted_price = self::convertToDefaultCurrency(
                    $item['pivot']['price'],
                    $request_currency_code,
                    $default_currency['code']
                );
            }
            $order_item = new OrderItem();

            $order_item->vh_st_order_id = $order->id;
            $order_item->vh_user_id = $order->vh_user_id;
            $order_item->taxonomy_id_order_items_types = $taxonomy_order_items_type;
            $order_item->taxonomy_id_order_items_status = $taxonomy_order_items_status;
            $order_item->vh_shipping_address_id = $request->order_details['shipping_address']['id'];
            $order_item->vh_billing_address_id = $request->order_details['billing_address']['id'];

            $order_item->vh_st_product_id =$item['pivot']['vh_st_product_id'];
            $order_item->vh_st_product_variation_id = $item['pivot']['vh_st_product_variation_id'];
            $order_item->vh_st_vendor_id = $item['pivot']['vh_st_vendor_id'];
            $order_item->quantity = $item['pivot']['quantity'];
            $order_item->price = $converted_price;
            $order_item->is_active = 1;
            $order_item->save();

            self::updateStock($item['pivot']['vh_st_product_variation_id'], $item['pivot']['quantity'], $item['pivot']['vh_st_vendor_id']);
        }
    }
    //-------------------------------------------------

    //-------------------------------------------------

    private static function updateProductQuantities($request)
    {
        foreach ($request->order_details['products'] as $item) {
            $product = Product::where('id', $item['pivot']['vh_st_product_id'])->withTrashed()->first();
            $cart_instance = Cart::find($request->order_details['cart_id']);
            $product_variation = $cart_instance->productVariations()
                ->where('vh_st_product_variation_id', $item['pivot']['vh_st_product_variation_id'])
                ->where('vh_st_vendor_id', $item['pivot']['vh_st_vendor_id'])->first();

            if ($product_variation) {
                $pivot_record = $product_variation->pivot;
                $pivot_record->delete();
            }
            $product->quantity = $product->productVariations->sum('quantity');
            $product->save();
        }
    }
    //-------------------------------------------------

    private static function clearSessionAndCart($request)
    {
        session()->forget('vh_user_id');
        $cart = Cart::find($request->order_details['cart_id']);
        $is_empty_cart = $cart->products->isEmpty();

        if ($is_empty_cart) {
            $cart->forceDelete();
        }
    }

    //-------------------------------------------------

        public static function getOrderDetails($id)
        {
            $order = Order::findByIdOrUuid($id)->first();

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Order not found.',
                    'data' => null,
                ];
            }

            $order_items = OrderItem::where('vh_st_order_id', $order->id)->get();

            if ($order_items->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'Order items not found.',
                    'data' => [
                        'order' => [
                            'id' => $order->id,
                            'uuid' => $order->uuid,
                        ],
                        'user' => null,
                        'product_details' => [],
                        'order_items_shipping_address' => null,
                        'order_items_billing_address' => null,
                        'total_mrp' => 0,
                        'ordered_at' => $order->created_at,
                    ],
                ];
            }

            $user = $order->user;

            $response = [
                'success' => true,
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'uuid' => $order->uuid,
                    ],
                    'user' => $user,
                    'ordered_at' => $order->created_at,
                    'unique_order_id' => $order->uuid,
                    'product_details' => [],
                    'order_items_shipping_address' => Address::find($order_items->first()->vh_shipping_address_id),
                    'order_items_billing_address' => Address::find($order_items->first()->vh_billing_address_id),
                    'total_mrp' => 0,
                ],
            ];
            $order_items_collection = collect($order_items);
            foreach ($order_items_collection as $key => $order_item){
                $product = Product::find($order_item->vh_st_product_id);
                $product_variation = ProductVariation::find($order_item->vh_st_product_variation_id);

                if ($product && $product_variation) {
                    $vendor = Vendor::find($order_item->vh_st_vendor_id);
                    $quantity = $order_item->quantity;
                    $price = $order_item->price;

                    $product_media_ids = self::getProductMediaIdsAtOrder($product, $order_item->vh_st_product_variation_id);
                    $image_urls = self::getImageUrls($product_media_ids);

                    $response['data']['product_details'][] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'image_urls' => $image_urls,
                        'pivot' => [
                            'cart_product_variation' => $product_variation->name,
                            'product_variation_id' => $product_variation->id,
                            'price' => $price,
                            'quantity' => $quantity,
                            'selected_vendor_id' => $vendor?->id,
                        ],
                    ];

                    $response['data']['total_mrp'] += $price * $quantity;
                }
            }
            $response['data']['order_paid_amount'] = $order->payments()
                ->sum('payment_amount');

            return $response;

        }
    //-------------------------------------------------

    public static function updateStock($variationId, $quantity, $vendorId)
    {
        $product_stock = ProductStock::where('vh_st_product_variation_id', $variationId)
            ->where('vh_st_vendor_id', $vendorId)
            ->first();

        if ($product_stock) {
            $product_stock->quantity -= $quantity;
            $product_stock->save();
        }

        // Update quantity in ProductVariation table
        $product_variation = ProductVariation::where('id', $variationId)->first();
        if ($product_variation) {
            $product_variation->quantity -= $quantity;
            $product_variation->save();
        }
    }

    //-------------------------------------------------



    public static function addToWishlist($request)
    {
        $item_detail = $request->get('item_detail');
        $user_detail = $request->get('user_detail');

        $product_id = $item_detail['vh_st_product_id'] ?? null;
        $variation_id = $item_detail['vh_st_product_variation_id'] ?? null;
        $vendor_id = $item_detail['vh_st_vendor_id'] ?? null;
        $wishlist_id = $item_detail['vh_st_wishlist_id'] ?? null;
        $selected_store_id = $request->selected_store_id;
        $type = $request->type;
        $cart_check_id = $item_detail['vh_st_cart_id'] ?? null;
        $response = ['success' => false, 'errors' => [], 'messages' => []];

        if (!$user_detail || !isset($user_detail['id'])) {
            $response['errors'][] = "No user attached to the wishlist. Please attach a user to proceed.";
            return $response;
        }

        $user = User::find($user_detail['id']);
        if (!$user) {
            $response['errors'][] = "User does not exist.";
            return $response;
        }
        if (!in_array($type, ['add', 'delete'])) {
            $response['errors'][] = "Invalid or missing action type. Only 'add' or 'delete' are allowed.";
            return $response;
        }
        $product = Product::find($product_id);
        if (!$product) {
            $response['errors'][] = "Invalid product selected.";
            return $response;
        }

        if ($variation_id) {
            $variation = ProductVariation::find($variation_id);
            if (!$variation) {
                $response['errors'][] = "Product Variation does not exist.";
                return $response;
            }
        }
        $cart = Cart::where('vh_user_id', $user->id)->first();
        $user_wishlist = self::getOrCreateUserWishlist($wishlist_id, $user);
        if (!$user_wishlist) {
            $response['errors'][] = "Wishlist not found or could not be created.";
            return $response;
        }

        if ($type === 'delete') {
            $deleted = $user_wishlist->products()
                ->wherePivot('vh_st_product_id', $product_id)
                ->wherePivot('vh_st_product_variation_id', $variation_id)
                ->detach();
            if ($deleted) {
                $data = [];

                if ($cart_check_id) {
                    $data['cart'] = $cart;
                } else {
                    $data['is_wishlisted'] = false;
                }
                return [
                    'success' => true,
                    'messages' => [trans("vaahcms-general.deleted_successfully")],
                    'data' => $data,
                ];
            }

            $response['errors'][] = "Product not found in wishlist.";
            return $response;
        }

        $ownership_check = Product::validateVendorAndProductToStore(
            $selected_store_id,
            $product_id ?? null,
            $vendor_id ?? null,

        );

        if (!$ownership_check['success']) {
            return $ownership_check;
        }

        $exists = UserWishlistProduct::where('vh_st_user_wishlist_id', $user_wishlist->id)
            ->where('vh_st_product_id', $product_id)
            ->when($variation_id, function ($query) use ($variation_id) {
                return $query->where('vh_st_product_variation_id', $variation_id);
            }, function ($query) {
                return $query->whereNull('vh_st_product_variation_id');
            })
            ->exists();

        if (!$exists) {
            $user_wishlist->products()->attach($product_id, [
                'vh_st_product_variation_id' => $variation_id
            ]);
        }
        $data = [];

        if ($cart_check_id) {
            $data['cart'] = $cart;
        } else {
            $data['is_wishlisted'] = true;
        }
        return [
            'success' => true,
            'messages' => [trans("vaahcms-general.saved_successfully")],
            'data' => $data,

        ];
    }
    //-------------------------------------------------

    protected static function getOrCreateUserWishlist($wishlist_id, $user)
    {
        if ($wishlist_id) {
            return UserWishlist::where('vh_st_wishlist_id', $wishlist_id)
                ->where('vh_user_id', $user->id)
                ->first();
        }

        $default_wishlist = Wishlist::where('slug', 'save-for-later')->first();
        if (!$default_wishlist) {
            return null;
        }

        $default_wishlist->users()->syncWithoutDetaching([$user->id]);

        return UserWishlist::firstOrCreate([
            'vh_st_wishlist_id' => $default_wishlist->id,
            'vh_user_id' => $user->id
        ]);
    }

    //---------------------------------------------------------------------

    public static function AddUserToCart($request, $uuid)
    {
        $inputs = $request->all();
        $validation = self::userValidation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        $cart = self::where('uuid', $uuid)->first();
        if (!$cart) {
            return [
                'success' => false,
                'errors' => ['Record not found with Uuid.'. $uuid],
            ];
        }

        $user_id = $request->input('user.id');
        if (!$user_id || !User::where('id', $user_id)->exists()) {
            return [
                'success' => false,
                'errors' => ['User ID is invalid or does not exist.'],
            ];
        }
        if (self::where('vh_user_id', $user_id)->exists()) {
            return [
                'success' => false,
                'errors' => ['This User is already linked to another cart.'],
            ];
        }
        $cart->vh_user_id = $user_id;
        $cart->save();
        $cart->load('products');
        return [
            'success' => true,
            'messages' => ['User added to cart successfully.'],
            'data'     => $cart,
        ];
    }
    //---------------------------------------------------------------------

    public static function userValidation($inputs)
    {
        $rules = [];
        $messages = [
            'user.required' => 'The User field is required.',
            'user.id.required' => 'Enter correct user information',
        ];

        if (isset($inputs['user'])) {
            $rules['user.id'] = 'required';
        } else {
            $rules['user'] = 'required';
        }

        $validator = validator($inputs, $rules, $messages);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->all(),
            ];
        }

        $validated_data = $validator->validated();

        return [
            'success' => true,
            'data' => $validated_data,
        ];
    }

    //---------------------------------------------------------------------

    public static function seedCarts($count=100)
    {
        $users = User::where('is_active', 1)->pluck('id')->toArray();

        $valid_products = Product::with(['productStocks' => function($query) {
                $query->where('quantity', '>', 0);
            }])->whereHas('productVendors')
            ->whereHas('productStocks', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->get();

        if (empty($users) || $valid_products->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $user_id = $users[array_rand($users)] ?? null;
            if (!$user_id) {
                continue;
            }
            $cart = new Cart();
            $cart->vh_user_id = $user_id;
            $cart->save();

            $num_products = rand(1, 2);  // Max products per cart
            $cart_products = $valid_products->random(min($num_products, $valid_products->count()));
            foreach ($cart_products as $product) {
                $product_stock = optional($product->productStocks->first());

                if (!$product_stock || !$product_stock->vh_st_vendor_id) {
                    continue;
                }
                $cart->products()->attach($product->id, [
                    'vh_st_product_variation_id' => $product_stock->vh_st_product_variation_id ?? null,
                    'vh_st_vendor_id' => $product_stock->vh_st_vendor_id,
                    'quantity' => rand(2, 8),
                ]);
            }
        }
    }

    //Fetch all needed vendors and prices before the loop and pass them in as collections.
    public static function formatCartProducts($products)
    {
        $vendor_ids = $products->pluck('pivot.vh_st_vendor_id')->unique()->filter();
        $variation_ids = $products->pluck('pivot.vh_st_product_variation_id')->unique()->filter();

        $vendors = Vendor::whereIn('id', $vendor_ids)->get()->keyBy('id');
        $prices = ProductPrice::whereIn('vh_st_product_variation_id', $variation_ids)
            ->whereIn('vh_st_vendor_id', $vendor_ids)
            ->get()
            ->mapWithKeys(fn($p) => [$p->vh_st_product_variation_id . '_' . $p->vh_st_vendor_id => $p->amount]);

        return $products->map(function ($product) use ($vendors, $prices) {
            $pivot = $product->pivot;
            $pivot_qty = $pivot->quantity;
            $vendor_id = $pivot->vh_st_vendor_id;
            $product_id = $pivot->vh_st_product_id;
            $variation_id = $pivot->vh_st_product_variation_id;

            $vendor = $vendors->get($vendor_id);

            $is_qty_available = self::isCartItemQuantityAvailable($vendor, $product_id, $variation_id);
            $available_qty = $is_qty_available ? self::getAvailableQuantity($vendor, $product_id, $variation_id) : 0;
            $is_valid_qty = $pivot_qty <= $available_qty;

            $price_key = "{$variation_id}_{$vendor_id}";
            $raw_price = $prices->get($price_key);

            $price_from_variation = false;

            if (is_null($raw_price)) {
                $price = ProductVariation::getPriceOfProductVariants($variation_id);
                $price_from_variation = true;
            } else {
                $price = $raw_price;
            }

            $price_data = $product->price; // assumes accessor is defined on the model
            $rate = $price_data['currency']['rate'] ?? 1;
            $converted_price = $price_from_variation ? $price : round($rate * $price, 2);
            $subtotal = $converted_price * $pivot_qty;

            $pivot->available_stock_quantity = $available_qty;
            $pivot->price = $converted_price;
            $pivot->is_stock_available = ($is_qty_available && $is_valid_qty) ? 1 : 0;
            $pivot->total_price = $subtotal;

            return $pivot;
        })->values();
    }

    //---------------------------------------------------------------------
    public static function convertToDefaultCurrency($amount, $from_currency, $to_currency)
    {
        if ($from_currency === $to_currency || !$amount) {
            return $amount;
        }

        $cache_key = 'conversion_rates_USD'; // always based on USD
        $conversion_rates = Cache::remember($cache_key, now()->addDay(), function () {
            $converter = new CurrencyConverterService();
            return $converter->fetchAllRates('USD');
        });


        $from_rate = $conversion_rates[$from_currency] ?? null;
        $to_rate = $conversion_rates[$to_currency] ?? null;

        if (!$from_rate || !$to_rate || $from_rate == 0) {
            throw new \Exception("Conversion rate missing: {$from_rate} or {$to_currency}");
        }

        // Convert from -> USD -> to
        $usd_amount = $amount / $from_rate;
        $converted = $usd_amount * $to_rate;

        return round($converted, 2);
    }

    //---------------------------------------------------------------------

    public static function previewBuyNowAtCheckout($request)
    {
        $buy_now = (bool) $request->input('buy_now');
        $cart_uuid = $request->input('uuid');

        // Handle user detection
        $user_info = $request->input('user');
        $user = is_array($user_info) && isset($user_info['id'])
            ? StoreUser::find($user_info['id'])
            : null;

        // Try to fetch cart
        $cart = null;
        if ($user) {
            $cart = Cart::where('vh_user_id', $user->id)->first();
        }
        if (!$cart && $cart_uuid) {
            $cart = Cart::findByIdOrUuid($cart_uuid)->first();
        }
        if (!$cart) {
            $cart = Product::findOrCreateCart($user);
        } elseif ($user && !$cart->vh_user_id) {
            $cart->vh_user_id = $user->id;
            $cart->save();
        }

        //  Check and only generate if not already in cart
        if ($buy_now) {
            $products_data = $request->input('products', []);
            $should_generate = false;

            foreach ($products_data as $item) {
                $variation_id = $item['variation_id'] ?? null;
                $vendor_id = $item['vendor_id'] ?? null;

                if (!$variation_id || !$vendor_id) continue;

                $existing_cart_item = Product::findCartItem($cart, $variation_id, $vendor_id);

                if (!$existing_cart_item) {
                    $should_generate = true;
                    break; // one missing, go for generateCart
                }
            }

            if ($should_generate) {
                Product::generateCart($request);
            }
        }
        // Return enriched preview
        return self::getCartItemDetailsAtCheckout($request, $cart->uuid ?? $cart->id);
    }


}
