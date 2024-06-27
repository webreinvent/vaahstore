<?php namespace VaahCms\Modules\Store\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Faker\Factory;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class Cart extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

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
            ->withPivot('vh_st_product_variation_id', 'quantity','vh_st_vendor_id');
    }
    //-------------------------------------------------
    public function productVariations()
    {
        return $this->belongsToMany(ProductVariation::class, 'vh_st_cart_products', 'vh_st_cart_id', 'vh_st_product_variation_id')
            ->withPivot('vh_st_product_id', 'quantity','vh_st_vendor_id')->with('product');
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
                            ->orWhere('first_name', 'LIKE', '%' . $search_item . '%');
                    });
            });
        }

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('user');
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
    public static function getItem($id)
    {

        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','user'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }
        // delete cart if no product available
        if ($item->products->isEmpty()) {
            self::find($id)->delete();
            $response['success'] = true;
            $response['data'] = null;
            return $response;
        }
        $wishlist_id = Wishlist::where('vh_user_id', $item->vh_user_id)->value('id');
        $total_amount = 0;
        foreach ($item->products as $product) {
            $variation_id = $product->pivot->vh_st_product_variation_id;
            $vendor_id = $product->pivot->vh_st_vendor_id;
            $variation = ProductVariation::find($variation_id);
            $variation_name = $variation?->name;
            $selected_vendor = Vendor::find($vendor_id);
            $is_quantity_available = self::isCartItemQuantityAvailable($selected_vendor,$product->id,$variation_id);
            $available_quantity = $is_quantity_available ? self::getAvailableQuantity($selected_vendor, $product->id, $variation_id) : 0;

            $pivot_quantity = $product->pivot->quantity;
            $is_pivot_quantity_valid = $pivot_quantity <= $available_quantity;
            $price = ProductPrice::where('vh_st_product_variation_id', $variation_id)
                ->where('vh_st_vendor_id', $vendor_id)
                ->value('amount');
            if ($price === null) {
                $price = ProductVariation::getPriceOfProductVariants($variation_id);
            }
            $is_wishlisted = $wishlist_id ? $product->wishlists()->where('vh_st_wishlist_id', $wishlist_id)->exists() : false;
            $product->pivot->is_stock_available = $is_quantity_available && $is_pivot_quantity_valid ? 1 : 0;
            $subtotal = $price * $pivot_quantity;
            $total_amount += $subtotal;
            $product->pivot->is_wishlisted = $is_wishlisted ? 1 : 0;
            $product->pivot->cart_product_variation = $variation_name;
            $product->pivot->price = $price;

        }

        $response['success'] = true;
        $response['data'] = $item;
        $response['data']['total_amount'] = $total_amount;
        return $response;

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


    //-------------------------------------------------
    public static function updateQuantity($request){
        $cart_id = $request['cart_product_details']['vh_st_cart_id'];
        $product_id = $request['cart_product_details']['vh_st_product_id'];
        $variation_id = $request['cart_product_details']['vh_st_product_variation_id'];
        $vendor_id = $request['cart_product_details']['vh_st_vendor_id'];
        $new_quantity = $request['quantity'];

        $cart = Cart::find($cart_id);
        if ($new_quantity < 1) {
            $cart_product_table_ids = $cart->products()
                ->wherePivot('vh_st_cart_id', $cart_id)
                ->wherePivot('vh_st_product_id', $product_id)
                ->wherePivot('vh_st_product_variation_id', $variation_id)
                ->wherePivot('vh_st_vendor_id', $vendor_id)
                ->pluck('vh_st_cart_products.id')
                ->toArray();

            if (!empty($cart_product_table_ids)) {

                foreach ($cart_product_table_ids as $cart_product_id) {
                    $cart->cartItems()->detach($cart_product_id);
                }

                $response['messages'][] = trans("vaahcms-general.record_deleted");
            }
        }
        else {
            $cart->products()
                ->wherePivot('vh_st_product_id', $product_id)
                ->wherePivot('vh_st_vendor_id', $vendor_id)
                ->wherePivot('vh_st_product_variation_id', $variation_id)
                ->updateExistingPivot($product_id, [
                    'quantity' => $new_quantity,
                    'vh_st_product_variation_id' => $variation_id
                ]);
            $response['messages'][] = trans("vaahcms-general.saved_successfully");

        }
        $response['data']['cart'] = $cart;
        return $response;
    }

    //-------------------------------------------------
    public static function deleteCartItem($request){
        $cart_id = $request['cart_product_details']['vh_st_cart_id'];
        $variation_id = $request['cart_product_details']['vh_st_product_variation_id'] ?? null;
        $product_id = $request['cart_product_details']['vh_st_product_id'];
        $vendor_id = $request['cart_product_details']['vh_st_vendor_id'];
        $cart = Cart::find($cart_id);

        if ($variation_id === null) {
            $cart->products()->detach($request['cart_product_details']['vh_st_product_id']);
        } else {
            $cart_product_table_ids = $cart->products()
                ->wherePivot('vh_st_cart_id', $cart_id)
                ->wherePivot('vh_st_product_id', $product_id)
                ->wherePivot('vh_st_product_variation_id', $variation_id)
                ->wherePivot('vh_st_vendor_id', $vendor_id)
                ->pluck('vh_st_cart_products.id')
                ->toArray();

            if (!empty($cart_product_table_ids)) {
                foreach ($cart_product_table_ids as $cart_product_id) {
                    $cart->cartItems()->detach($cart_product_id);
                }
            }
        }

        return [
            'data' => ['cart' => $cart],
            'messages' => [trans("vaahcms-general.record_deleted")]
        ];
    }

    //-------------------------------------------------


    public static function getCartItemDetailsAtCheckout($id)
    {
        $response = [];

        $cart = Cart::with(['user', 'products', 'productVariations'])->find($id);

        if (!$cart) {
            return ['success' => false, 'data' => null];
        }

        $response['success'] = true;
        $response['data'] = [
            'product_details' => [],
            'user_addresses' => null,
            'user_billing_addresses' => null,
            'user' => $cart->user,
            'total_mrp' => 0,
        ];

        // Get user address
        $user = $cart->user;
        $taxonomy_id_address_types = Taxonomy::getTaxonomyByType('address-types')->where('slug', 'shipping')->value('id');
        $taxonomy_id_address_type_billing = Taxonomy::getTaxonomyByType('address-types')->where('slug', 'billing')->value('id');
        $user_addresses = Address::where('vh_user_id', $user->id)->where('taxonomy_id_address_types',$taxonomy_id_address_types)->get();
        $user_billing_addresses = Address::where('vh_user_id', $user->id)->where('taxonomy_id_address_types',$taxonomy_id_address_type_billing)->get();
        $response['data']['user_addresses'] = $user_addresses;
        $response['data']['user_billing_addresses'] = $user_billing_addresses;

        foreach ($cart->products as $product) {
            $vendor_id = $product->pivot->vh_st_vendor_id;
            $selected_vendor = Vendor::find($vendor_id);

            if (!is_null($product->pivot->vh_st_product_variation_id)) {
                $variation_id = $product->pivot->vh_st_product_variation_id;

                $quantity_info = ProductVariation::getItemQuantity($selected_vendor, $product->id, $variation_id);

                if ($product->pivot->quantity <= $quantity_info['quantity']) {
                    $variation_price = ProductPrice::where('vh_st_product_variation_id', $variation_id)
                            ->where('vh_st_vendor_id', $vendor_id)
                            ->value('amount') ?? self::getProductPrice($product);

                    if ($variation_price != 0) {
                        $product_media_ids = self::getProductMediaIds($product);
                        $image_urls = self::getImageUrls($product_media_ids);
                        $variation_name = self::getProductVariationName($product);
                        $subtotal = $variation_price * $product->pivot->quantity;
                        $response['data']['total_mrp'] += $subtotal;

                        $response['data']['product_details'][] = [
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'description' => $product->description,
                            'image_urls' => $image_urls,
                            'pivot' => [
                                'cart_product_variation' => $variation_name,
                                'product_variation_id' => $product->pivot->vh_st_product_variation_id,
                                'price' => $variation_price,
                                'quantity' => $product->pivot->quantity,
                                'subtotal' => $subtotal,
                                'selected_vendor_id' => $vendor_id,
                            ],
                        ];
                    }
                }
            }
        }

        return $response;
    }

    //-------------------------------------------------



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
            return $product->productVariationMedia()
                ->where('vh_st_product_variation_id', $product->pivot->vh_st_product_variation_id)
                ->pluck('vh_st_product_media_id')->toArray();
        } else {
            return ProductMedia::where('vh_st_product_id', $product->id)->pluck('id')->toArray();
        }
    }
    //-------------------------------------------------

    private static function getProductMediaIdsAtOrder($product,$product_variation_id)
    {
            return $product->productVariationMedia()
                ->where('vh_st_product_variation_id',$product_variation_id)
                ->pluck('vh_st_product_media_id')->toArray();

    }
    //-------------------------------------------------

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
    //-------------------------------------------------

    private static function getProductVariationName($product)
    {
        $variation_id = $product->pivot->vh_st_product_variation_id;
        $variation = ProductVariation::find($variation_id);
        return $variation ? $variation->name : null;
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

        $order = self::createOrder($request);

        self::createOrderItemsAndUpdateStock($request, $order);

        self::updateProductQuantities($request);

        self::clearSessionAndCart($request);

        $response['data'] = ['order' => $order];

        return $response;
    }
    //-------------------------------------------------

    private static function validateOrderDetails($request)
    {
        if (is_null($request->order_details['shipping_address'])) {
            return ['success' => false, 'errors' => ["Provide shipping details"]];
        }

        if (is_null($request->order_details['billing_address'])) {
            return ['success' => false, 'errors' => ["Provide billing details"]];
        }
        if (is_null($request->order_details['payment_method'])) {
            return ['success' => false, 'errors' => ["Select payment type"]];
        }

        return ['success' => true];
    }
    //-------------------------------------------------

    private static function createOrder($request)
    {
        $taxonomy_order_status = Taxonomy::getTaxonomyByType('order-status')->where('slug', 'pending')->value('id');
        $taxonomy_payment_status_id = Taxonomy::getTaxonomyByType('order-payment-status')
            ->where('slug', 'pending')
            ->value('id');

        $order = new Order();

        $order->vh_user_id = $request->order_details['shipping_address']['vh_user_id'];
        $order->amount = $request->order_details['total_amount'];
        $order->taxonomy_id_order_status = $taxonomy_order_status;
        $order->taxonomy_id_payment_status = $taxonomy_payment_status_id;
        $order->payable = $request->order_details['payable'];
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
        $taxonomy_order_items_type = Taxonomy::getTaxonomyByType('order-items-types')->where('slug', 'cod')->value('id');
        $taxonomy_order_items_status = Taxonomy::getTaxonomyByType('order-items-status')->where('slug', 'approved')->value('id');

        foreach ($request->order_details['order_items'] as $item) {
            $order_item = new OrderItem();

            $order_item->vh_st_order_id = $order->id;
            $order_item->vh_user_id = $order->vh_user_id;
            $order_item->taxonomy_id_order_items_types = $taxonomy_order_items_type;
            $order_item->taxonomy_id_order_items_status = $taxonomy_order_items_status;
            $order_item->vh_shipping_address_id = $request->order_details['shipping_address']['id'];
            $order_item->vh_billing_address_id = $request->order_details['billing_address']['id'];

            $order_item->vh_st_product_id = $item['product_id'];
            $order_item->vh_st_product_variation_id = $item['pivot']['product_variation_id'];
            $order_item->vh_st_vendor_id = $item['pivot']['selected_vendor_id'];
            $order_item->quantity = $item['pivot']['quantity'];
            $order_item->price = $item['pivot']['price'];
            $order_item->is_active = 1;
            $order_item->save();

            self::updateStock($item['pivot']['product_variation_id'], $item['pivot']['quantity'], $item['pivot']['selected_vendor_id']);
        }
    }
    //-------------------------------------------------

    private static function updateProductQuantities($request)
    {
        foreach ($request->order_details['order_items'] as $item) {
            $product = Product::where('id', $item['product_id'])->withTrashed()->first();
            $cart_instance = Cart::find($request->order_details['cart_id']);
            $product_variation = $cart_instance->productVariations()
                ->where('vh_st_product_variation_id', $item['pivot']['product_variation_id'])
                ->where('vh_st_vendor_id', $item['pivot']['selected_vendor_id'])->first();

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
        $request->session()->forget('vh_user_id');
        $cart = Cart::find($request->order_details['cart_id']);
        $is_empty_cart = $cart->products->isEmpty();

        if ($is_empty_cart) {
            $cart->delete();
        }
    }

    //-------------------------------------------------

        public static function getOrderDetails($id)
        {
            $order = Order::find($id);

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

            foreach ($order_items as $order_item) {
                $product = Product::find($order_item->vh_st_product_id);
                $product_variation = ProductVariation::find($order_item->vh_st_product_variation_id);

                if ($product && $product_variation) {
                    $vendor = Vendor::find($order_item->vh_st_vendor_id);
                    $quantity = $order_item->quantity;
                    $price = $order_item->price;

                    $product_media_ids = self::getProductMediaIdsAtOrder($product, $order_item->vh_st_product_variation_id);
                    $imageUrls = self::getImageUrls($product_media_ids);

                    $response['data']['product_details'][] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'image_urls' => $imageUrls,
                        'pivot' => [
                            'cart_product_variation' => $product_variation->name,
                            'product_variation_id' => $product_variation->id,
                            'price' => $price,
                            'quantity' => $quantity,
                            'selected_vendor_id' => $vendor->id,
                        ],
                    ];

                    $response['data']['total_mrp'] += $price * $quantity;
                }
            }
            $response['data']['is_paid'] = $order->payments()->wherePivot('remaining_payable_amount', 0)->exists();

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


    public static function addToWishlist($request){
        $item_detail = $request->get('item_detail');
        $user_detail = $request->get('user_detail');
        $product_id = $item_detail['vh_st_product_id'];
        $taxonomy_wishlist_status = Taxonomy::getTaxonomyByType('whishlists-status')
            ->where('slug', 'approved')
            ->pluck('id')
            ->first();


        $wishlist = Wishlist::where('vh_user_id', $user_detail['id'])->firstOrCreate(
            ['vh_user_id' => $user_detail['id']],
            [
                'uuid' => Str::uuid(),
                'name' => $user_detail['first_name'] . "'s Wishlist",
                'slug' => Str::slug($user_detail['first_name'] . "'s Wishlist"),
                'taxonomy_id_whishlists_status' => $taxonomy_wishlist_status,
                'is_default' => true,
                'status_notes' => 'Created automatically',

            ]
        );

        $wishlist->products()->detach($product_id);
        if ($item_detail['is_wishlisted'] !== 1 && !$wishlist->products->contains($product_id)) {
            $wishlist->products()->attach($product_id);
        }

        $cart = Cart::where('vh_user_id', $user_detail['id'])->first();
        $response['success'] = true;
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        $response['data'] = [
            'cart' => $cart,
        ];

        return $response;
    }
    //-------------------------------------------------




}
