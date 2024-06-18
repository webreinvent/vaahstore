<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Faker\Factory;
use WebReinvent\VaahCms\Libraries\VaahMail;
use WebReinvent\VaahCms\Mail\SecurityOtpMail;
use WebReinvent\VaahCms\Models\Notification;
use WebReinvent\VaahCms\Models\UserBase;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use WebReinvent\VaahCms\Entities\Taxonomy;
class ProductVariation extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_product_variations';
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
        'vh_st_product_id',
        'sku',
        'quantity',
        'is_default',
        'in_stock',
        'has_media',
        'meta',
        'taxonomy_id_variation_status',
        'status_notes',
        'description',
        'price',
        'meta_description',
        'meta_title',
        'meta_keywords',
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
    protected $casts =[
        'meta_keywords'=>'array',
    ];

    //-------------------------------------------------
    protected function serializeDate(DateTimeInterface $date)
    {
        $date_time_format = config('settings.global.datetime_format');
        return $date->format($date_time_format);
    }

    //-------------------------------------------------
    public  function medias()
    {
        return $this->belongsToMany(ProductMedia::class, 'vh_st_product_variation_medias', 'vh_st_product_variation_id', 'vh_st_product_media_id')->withTrashed()
            ->withPivot('id','vh_st_product_id');
    }
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
        $empty_item['is_default'] = 0;
        $empty_item['is_active'] = 1;
        $empty_item['in_stock'] = 0;
        $empty_item['has_media'] = 0;
        $empty_item['quantity'] = 0;
        return $empty_item;
    }

    //----------------------------------------------

    protected static function booted()
    {
        static::updated(function ($productVariation) {
                if ($productVariation->isDirty('quantity')) {
                    self::sendMailForStock();
                }
        });
    }




    //-------------------------------------------------

    public static function searchProduct($request)
    {

         $query=$request->input('query');
        if($query === null)
        {
            $products = Product::where('is_active',1)->select('id','name','slug')
                ->inRandomOrder()
                ->take(10)
                ->get();
        }
        else{

            $products = Product::where('is_active',1)
                ->where('name', 'like', "%$query%")
                ->select('id','name','slug')
                ->get();
        }
        $response['success'] = true;
        $response['data'] = $products;
        return $response;

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

    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_variation_status')
            ->select('id','name','slug');
    }

    //-------------------------------------------------
    public function product()
    {
        return $this->belongsTo(Product::class,'vh_st_product_id','id')->withTrashed()
            ->select('id','name','slug','deleted_at');
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
    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'vh_st_cart_products', 'vh_st_product_variation_id', 'vh_st_cart_id');
    }
    //-------------------------------------------------

    public function scopeDefaultFilter($query, $filter)
    {
        if(!isset($filter['default'])
            || is_null($filter['default'])
            || $filter['default'] === 'null'
        )
        {
            return $query;
        }

        $default = $filter['default'];
        if($default == 'true')
        {
            return $query->where(function ($q){
                $q->Where('is_default', 1);
            });
        }
        else{
            return $query->where(function ($q){
                $q->whereNull('is_default')
                    ->orWhere('is_default', 0);
            });
        }

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
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }


        // check if name exist
        $item = self::where('name', $inputs['name'])->withTrashed()->first();

        if ($item) {
            $error_message = "This name is already exist".($item->deleted_at?' in trash.':'.');
            $response['success'] = false;
            $response['messages'][] = $error_message;
            return $response;
        }

        // check if slug exist
        $item = self::where('slug', $inputs['slug'])->withTrashed()->first();

        if ($item) {
            $error_message = "This slug is already exist".($item->deleted_at?' in trash.':'.');
            $response['success'] = false;
            $response['messages'][] = $error_message;
            return $response;
        }

        // handle if current record is default
        if ($inputs['is_default'] && isset($inputs['product'])) {
            $product_variations = self::where('vh_st_product_id', $inputs['product']['id'])->get();

            foreach ($product_variations as $variation) {
                if ($variation->is_default == 1) {
                    $variation->is_default = 0;
                    $variation->save();
                }
            }
        }

        $item = new self();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
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
        if (!isset($filter['q'])) {
            return $query;
        }

        $search_terms = explode(' ', $filter['q']);

        $query->where(function ($query) use ($search_terms) {
            foreach ($search_terms as $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('id', 'LIKE', '%' . $term . '%')
                        ->orWhere('name', 'LIKE', '%' . $term . '%')
                        ->orWhere('slug', 'LIKE', '%' . $term . '%');
                });
            }

            $query->orWhereHas('product', function ($productQuery) use ($search_terms) {
                foreach ($search_terms as $term) {
                    $productQuery->where('name', 'LIKE', '%' . $term . '%')
                        ->orWhere('slug', 'LIKE', '%' . $term . '%');
                }
            });
        });

        return $query;
    }

    //-------------------------------------------------

    public function scopeStatusFilter($query, $filter)
    {


        if(!isset($filter['product_variation_status']))
        {
            return $query;
        }
        $search = $filter['product_variation_status'];
        $query->whereHas('status' , function ($q) use ($search){
            $q->whereIn('name' ,$search);
            $q->whereIn('slug' ,$search);
        });

    }



    //-------------------------------------------------

    public function scopeStockFilter($query, $filter)
    {
        if (!isset($filter['in_stock']) || is_null($filter['in_stock']) || $filter['in_stock'] === 'null') {
            return $query;
        }

        $in_stock = $filter['in_stock'];

        if ($in_stock === 'false') {
            $query->where('quantity', '=', 0);
        } elseif ($in_stock === 'true') {
            $query->where('quantity', '>', 0);
        }

        return $query;
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


    public function scopeProductFilter($query, $filter)
    {
        if(!isset($filter['products'])
            || is_null($filter['products'])
            || $filter['products'] === 'null'
        )
        {
            return $query;
        }

        $product = $filter['products'];



        $query->whereHas('product', function ($query) use ($product) {
            $query->whereIn('slug', $product);

        });

    }



    //-------------------------------------------------
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


    //-------------------------------------------------

    public static function getList($request)
    {
        $user = null;
        $cart_records = 0;
        if ($user_id = session('vh_user_id')) {
            $user = User::find($user_id);
            if ($user) {
                $cart = self::findOrCreateCart($user);
                $cart_records = $cart->products()->count();
            }
        }
        $default_variation = self::where('is_default', 1)->first();
        $list = self::getSorted($request->filter)->with('status','product');
        if ($request->has('filter')) {
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
            $list->statusFilter($request->filter);
            $list->stockFilter($request->filter);
            $list->defaultFilter($request->filter);
            $list->dateRangeFilter($request->filter);
            $list->quantityFilter($request->filter);
            $list->productFilter($request->filter);
        }

        $default_variation_exists = $default_variation;

        $rows = config('vaahcms.per_page');

        if($request->has('rows'))
        {
            $rows = $request->rows;
        }

        $list = $list->paginate($rows);


        $response = [
            'success' => true,
            'data' => $list,
        ];

        $response['active_cart_user'] = $user;

        if ($user) {
            $response['active_cart_user']['cart_records'] = $cart_records;
            $response['active_cart_user']['vh_st_cart_id'] = $cart->id;
        }


        if (!$default_variation_exists) {
            $response['message'] = true;
        }

        return $response;


    }

    //-------------------------------------------------
    public static function updateList($request)
    {
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
        }

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

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
        }

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
            $item = self::where('id', $item_id)->withTrashed()->first();
            self::deleteRelatedItem($item_id, ProductPrice::class);
            self::deleteRelatedItem($item_id, ProductStock::class);
            self::deleteProductAttribute($item_id);
            $product_media_id = $item->medias()->pluck('vh_st_product_media_id')->first();
            $item->medias()->detach();

            if ($product_media_id) {
                $product_media = ProductMedia::where('id', $product_media_id)->withTrashed()->first();

                if ($product_media) {
                    if ($product_media->productVariationMedia()) {
                        $is_count = $product_media->productVariationMedia()->count();
                    }

                    if (!isset($is_count) || !$is_count) {
                        $product_media->forceDelete();
                    }
                }
            }

            self::updateQuantity($item->id);

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
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
        }

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

                    foreach ($items_id as $item_id)
                    {
                        $item = self::where('id', $item_id)->withTrashed()->first();
                        self::deleteRelatedItem($item_id, ProductMedia::class);
                        self::deleteRelatedItem($item_id, ProductPrice::class);
                        self::deleteRelatedItem($item_id, ProductStock::class);
                        self::deleteProductAttribute($item_id);
                        $product_media_id = $item->medias()->pluck('vh_st_product_media_id')->first();
                        $item->medias()->detach();

                        if ($product_media_id) {
                            $product_media = ProductMedia::where('id', $product_media_id)->withTrashed()->first();

                            if ($product_media) {
                                if ($product_media->productVariationMedia()) {
                                    $is_count = $product_media->productVariationMedia()->count();
                                }

                                if (!isset($is_count) || !$is_count) {
                                    $product_media->forceDelete();
                                }
                            }
                        }

                        self::updateQuantity($item->id);

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
                $list->update(['is_default' => 0]);
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
                    $item = self::where('id', $item_id)->withTrashed()->first();
                    self::deleteRelatedItem($item_id, ProductMedia::class);
                    self::deleteRelatedItem($item_id, ProductPrice::class);
                    self::deleteRelatedItem($item_id, ProductStock::class);
                    self::deleteProductAttribute($item_id);
                    $product_media_id = $item->medias()->pluck('vh_st_product_media_id')->first();
                    $item->medias()->detach();

                    if ($product_media_id) {
                        $product_media = ProductMedia::where('id', $product_media_id)->withTrashed()->first();

                        if ($product_media) {
                            if ($product_media->productVariationMedia()) {
                                $is_count = $product_media->productVariationMedia()->count();
                            }

                            if (!isset($is_count) || !$is_count) {
                                $product_media->forceDelete();
                            }
                        }
                    }

                    self::updateQuantity($item->id);

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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','status','product'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_not_found_with_id").$id;
            return $response;
        }
        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }
    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
        }

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
            $error_message = "This name is already exist".($item->deleted_at?' in trash.':'.');
            $response['success'] = false;
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if slug exist
        $item = self::where('id', '!=', $id)
            ->withTrashed()
            ->where('slug', $inputs['slug'])->first();

        if ($item) {
            $error_message = "This slug is already exist".($item->deleted_at?' in trash.':'.');
            $response['success'] = false;
            $response['errors'][] = $error_message;
            return $response;
        }

        // handle if current record is default
        if (isset($inputs['product']) && $inputs['is_default']) {
            $product_variations = self::where('vh_st_product_id', $inputs['product']['id'])->get();

            foreach ($product_variations as $variation) {
                if ($variation->is_default == 1) {
                    $variation->is_default = 0;
                    $variation->save();
                    break;
                }
            }
        }
        $product_stocks = ProductStock::where('vh_st_product_variation_id', $id)->get();
        if ($product_stocks->isNotEmpty()) {
            foreach ($product_stocks as $product_stock) {
                $product_stock->vh_st_product_id = $inputs['product']['id'];
                $product_stock->save();
            }
            $product_variation = self::findOrFail($id);
            $old_product = Product::findOrFail($product_variation->vh_st_product_id);
            $new_product = Product::findOrFail($inputs['product']['id']);
            $old_total_quantity = self::where('vh_st_product_id', $old_product->id)->sum('quantity');
            $new_total_quantity = self::where('vh_st_product_id', $new_product->id)->sum('quantity');
            $old_product->quantity = $old_total_quantity - $product_variation->quantity;
            $old_product->save();
            $new_product->quantity = $new_total_quantity + $product_variation->quantity;
            $new_product->save();
        }


        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }
        self::deleteRelatedItem($item->id, ProductPrice::class);
        self::deleteRelatedItem($item->id, ProductStock::class);
        self::deleteProductAttribute($item->id);

        $product_media_id = $item->medias()->pluck('vh_st_product_media_id')->first();
        $item->medias()->detach();

        if ($product_media_id) {
            $product_media = ProductMedia::where('id', $product_media_id)->withTrashed()->first();

            if ($product_media) {
                if ($product_media->productVariationMedia()) {
                    $is_count = $product_media->productVariationMedia()->count();
                }

                if (!isset($is_count) || !$is_count) {
                    $product_media->forceDelete();
                }
            }
        }

        self::updateQuantity($item->id);
        $item->forceDelete();
        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans("vaahcms-general.record_has_been_deleted");

        return $response;
    }
    //-------------------------------------------------
    public static function itemAction($request, $id, $type): array
    {
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
        }
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
                $item->is_default = null;
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
            'product'=> 'required',
            'name' => 'required|min:1|max:100',
            'slug' => 'required|min:1|max:100',
            'sku' => 'required|min:1|max:50',
            'description'=>'max:255',
            'taxonomy_id_variation_status'=> 'required',
            'price' => 'required|numeric|min:0|max:9999999',
            'meta_title' => 'nullable|max:100',
            'meta_description' => 'nullable|max:100',
            'meta_keywords' => 'nullable|array|max:15',
            'meta_keywords.*' => 'max:100',



            'status_notes' => [
                'max:100',
            ],
        ],
            [
                'product.required' => 'Please Choose a Product',
                'name.required'=>'The Name field is required.',
                'slug.required'=>'The Slug field is required.',
                'taxonomy_id_variation_status.required' => 'The Status field is required.',
                'sku.required'=>'The SKU field is required.',
                'price.required'=>'The Price field is required.',
                'price.min' => 'The Price field cannot be less than :min digits.',
                'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
                'quantity.digits_between' => 'The quantity field must not be greater than 9 digits',
                'description.max' => 'The Description field may not be greater than :max characters.',
                'price.max'=>'The Price field may not be greater than :max digits.',
                'meta_title.max' => 'The Meta Title field must not exceed :max characters.',
                'meta_description.max' => 'The Meta Description field must not exceed :max characters.',
                'meta_keywords.max' => 'The Meta Keywords field must not have more than :max items.',
                'meta_keywords.*' => 'The Meta Keyword field may not have greater than :max characters',



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

    public static function deleteProducts($items_id){

        $response=[];
        if($items_id){
            $item_ids = self::whereIn('vh_st_product_id',$items_id)->pluck('id');
            ProductAttribute::deleteProductVariations($item_ids);
            self::whereIn('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }

    //------------------------------------------------

    public static function deleteProduct($items_id){

        $response=[];
        if($items_id){
            $item_id = self::where('vh_st_product_id',$items_id)->pluck('id')->first();
            ProductAttribute::deleteProductVariation($item_id);
            self::where('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }

    //------------------------------------------------

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
        $product_ids = Product::where(['is_active'=>1,'deleted_at'=>null])->pluck('id')->toArray();

        $inputs['vh_st_product_id'] = null;
        $inputs['product'] = null;
        if (!empty($product_ids)) {
            $product_id = $product_ids[array_rand($product_ids)];
            $product = Product::where(['is_active' => 1, 'id' => $product_id])->first();
            $inputs['vh_st_product_id'] = $product_id;
            $inputs['product'] = $product;
        }

            $inputs['price'] = rand(1,100000);
        $inputs['is_active'] = 1;
        $inputs['is_default'] = 0;
        $inputs['quantity'] = 0;
        $taxonomy_status = Taxonomy::getTaxonomyByType('product-variation-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        $status_id = $status_ids[array_rand($status_ids)];

        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['taxonomy_id_variation_status'] = $status_id;
        $inputs['status']=$status;
        $faker = Factory::create();

        $max_words = 10;

        $inputs['meta_keywords'] = array_map(function () use ($faker) {
            return $faker->word;
        }, range(1, $faker->numberBetween(2, $max_words)));

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

    public static function sendMailForStock()
    {
        try {
            $list_data = ProductVariation::with('product')->get();

            $filtered_data = $list_data->filter(function ($item) {
                return $item->quantity >= 0 && $item->quantity < 10;
            });
            $mailers = config('mail.mailers.smtp', []);
            if (empty($mailers['host']) || empty($mailers['port'])|| empty($mailers['username'])|| empty($mailers['password'])) {
                $response['success'] = false;
                $response['errors'][] = 'mail configuration not set.';
                return $response;
            }
            foreach ($list_data as $item) {
                if ($item->quantity > 10 && ($item->is_mail_sent === null || $item->is_mail_sent === 1)) {
                    $item->is_mail_sent = 0;
                    $item->save();
                }
            }

            $product_variation_count = $filtered_data->groupBy('vh_st_product_id')->map(function ($variations) {
                $min_quantity = $variations->min('quantity');
                $max_quantity = $variations->max('quantity');

                // If min_quantity is same as max_quantity, set min_quantity to 0
                if ($min_quantity === $max_quantity) {
                    $min_quantity = 0;
                }

                return [
                    'count' => $variations->count(),
                    'min_quantity' => $min_quantity,
                    'max_quantity' => $max_quantity
                ];
            });

            $subject = 'Low Stock Alert';
            $message = '<html><body>';
            $message .= '<p>Hello Everyone, the following items are low in stock:</p>';
            $message .= '<table border="1">';
            $message .= '<tr><th>Product Name</th><th>Low Quantity Variations Count</th><th>Quantity Range Between </th><th>Link</th></tr>';

            $processed_products = []; // Track processed products

            foreach ($filtered_data as $item) {
                $product_name = isset($item->product) ? $item->product->name : '';
                $product_slug = isset($item->product) ? $item->product->slug : '';
                $product_id = $item->vh_st_product_id;

                if (!in_array($product_name, $processed_products)) {
                    $processed_products[] = $product_name;

                    $message .= '<tr>';
                    $message .= '<td>' . $product_name . '</td>';
                    $message .= '<td>' . $product_variation_count[$product_id]['count'] . '</td>';
                    $message .= '<td>' . $product_variation_count[$product_id]['min_quantity'] . ' to ' . $product_variation_count[$product_id]['max_quantity'] . '</td>';
                    $message .= '<td><a href="'.url("/").'/backend/store#/productvariations?page=1&rows=20&filter[products][]=' . $product_slug. '&filter[quantity][]=' . $product_variation_count[$product_id]['min_quantity'] . '&filter[quantity][]=' . $product_variation_count[$product_id]['max_quantity'] . '">View</a></td>';
                    $message .= '</tr>';
                }
            }

            $message .= '</table>';
            if ($filtered_data->isNotEmpty()) {
                // Send mail
                $send_mail = UserBase::notifySuperAdmins($subject, $message);

                // Update is_mail_sent flag
                foreach ($filtered_data as $item) {
                    if ($item->is_mail_sent === null || $item->is_mail_sent === 0) {
                        $item->is_mail_sent = 1;
                        $item->save();
                    }
                }
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'][] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }

        return $response;
    }





    //--------------------------------------------------------------

    public static function getMinMaxQuantity()
    {
        try {
            $list_data = self::all();
            if ($list_data->isEmpty()) {
                $min_quantity = 0;
                $max_quantity = 0;
            } else {
                $quantities = $list_data->pluck('quantity')->toArray();
                $min_quantity = min($quantities);
                $max_quantity = max($quantities);
            }
            return [
                'min_quantity' => $min_quantity,
                'max_quantity' => $max_quantity,
            ];
        } catch (\Exception $e) {
            $response['success'] = false;

            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'][] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }
    }

    //--------------------------------------------------------------
    public static function updateQuantity($id)
    {


        $item=self::where('id', $id)->withTrashed()->first();

        $product = Product::where('id',$item->vh_st_product_id)->withTrashed()->first();
            if ($product) {
                $product->quantity -= $item->quantity;
                $product->save();
            }

    }

    //--------------------------------------------------------------------------------------


    public static function deleteRelatedItem($item_id, $related_model)
    {
        $response = [];

        if ($item_id) {
            $item_exist = $related_model::where('vh_st_product_variation_id', $item_id)->first();
            if ($item_exist) {
                $related_model::where('vh_st_product_variation_id', $item_id)->forceDelete();
                $response['success'] = true;
            }
        } else {
            $response['success'] = false;
        }

        return $response;
    }
    //--------------------------------------------------------------------------------------

    public static function deleteProductAttribute($item_id){

        $response=[];

        if ($item_id) {
            $item_exist = ProductAttribute::where('vh_st_product_variation_id', $item_id)->withTrashed()->first();

            if ($item_exist) {

                $attribute_ids = $item_exist->pluck('id')->toArray();

                foreach ($attribute_ids as $attribute_id) {
                    $attribute_values = ProductAttributeValue::where('vh_st_product_attribute_id', $attribute_id)->get();

                    if ($attribute_values) {
                        $attribute_values->each(function ($value) {
                            $value->forceDelete();
                        });
                    }
                }
                ProductAttribute::where('vh_st_product_variation_id', $item_id)->forceDelete();
                $response['success'] = true;
            }
        } else {
            $response['success'] = false;
        }

        return $response;

    }

    //------------------------------------------------------------------------------

    public static function setProductInFilter($request)
    {

        if(isset($request['filter']['product']) && !empty($request['filter']['product'])) {
            $query = $request['filter']['product'];
            $products = Product::whereIn('name', $query)
                ->orWhereIn('slug', $query)
                ->select('id', 'name', 'slug')
                ->get();
            $response['success'] = true;
            $response['data'] = $products;
        } else {
            $response['success'] = false;
            $response['message'] = 'No filter or products provided';
            $response['data'] = [];
        }
        return $response;
    }
    //----------------------------------------------------------

    public static function getPriceOfProductVariants($variation_id)
    {
        $variation = self::find($variation_id);

        if (!$variation) {
            return null;
        }
        return $variation->price;
    }

    //----------------------------------------------------------



    //----------------------------------------------------------

    public static function addVariationToCart($request)
    {
        $response = [];

        $user_info = $request->input('user_info');
        $product_variation_id = $request->input('product_variation.id');
        $product_variation = ProductVariation::find($product_variation_id);
        $user_data = ['id' => $user_info['id']];

        if (!$user_data) {
            $error_message = "Please enter valid user";
            $response['errors'][] = $error_message;
            return $response;
        }

        $user = self::findOrCreateUser($user_data);
        $cart = self::findOrCreateCart($user);

        $selected_vendor = self::getSelectedVendor($product_variation);
        $selected_vendor_id=$selected_vendor['id'];

        $quantity_info = self::getItemQuantity($selected_vendor, $product_variation->vh_st_product_id, $product_variation_id);
        if ($selected_vendor_id === null || $quantity_info['quantity']===0) {
            $error_message = "This product variation is out of stock";
            $response['errors'][] = $error_message;
            return $response;
        }

        $existing_cart_item = self::findExistingCartItem($cart, $product_variation_id, $selected_vendor_id);

        if ($existing_cart_item) {
            if ($existing_cart_item->pivot->quantity < $request->input('product_variation.quantity')) {
                $pivot_record = $cart->productVariations()
                    ->where('vh_st_product_variation_id', $product_variation_id)
                    ->where('vh_st_vendor_id', $selected_vendor_id)
                    ->withPivot('id', 'quantity')
                    ->first();
                if ($pivot_record) {
                    $pivot_record->pivot->quantity += 1;
                    $pivot_record->pivot->save();
                }
            }
        } else {
            self::attachVariantionToCart($cart, $product_variation, $selected_vendor_id);
        }

        if (!Session::has('vh_user_id')) {
            Session::put('vh_user_id', $user->id);
        }

        $response['success'] = true;
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        $response['data'] = $user;

        return $response;
    }
    //----------------------------------------------------------

    private static function getSelectedVendor($product_variation)
    {
        $selected_vendor = Product::getPriceRangeOfProduct($product_variation->vh_st_product_id);
        $variation_selected_vendor = null;

        foreach ($selected_vendor as $vendor) {
            if (isset($vendor['selected_vendor'])) {
                $variation_selected_vendor = $vendor['selected_vendor'];
                break;
            }
        }

        return $variation_selected_vendor;
    }
    //----------------------------------------------------------

    private static function findExistingCartItem($cart, $product_variation_id, $selected_vendor_id)
    {
        return $cart->productVariations()
            ->where('vh_st_product_variation_id', $product_variation_id)
            ->where('vh_st_vendor_id', $selected_vendor_id)
            ->first();
    }
    //----------------------------------------------------------

    public static function getItemQuantity($vendor, $product_id, $variation_id)
    {
        if ($vendor === null || $product_id === null || $variation_id === null) {
            return ['available' => false, 'quantity' => 0];
        }

        $productStock = $vendor->productStocks()
            ->where('vh_st_product_id', $product_id)
            ->where('vh_st_product_variation_id', $variation_id)
            ->where('is_active', 1)
            ->first();

        if ($productStock) {
            return ['available' => true, 'quantity' => $productStock->quantity];
        }

        return ['available' => false, 'quantity' => 0];
    }
    //----------------------------------------------------------

    protected static function findOrCreateUser($user_data)
    {
        $user = User::findOrFail($user_data['id']);
        return $user;
    }
    //----------------------------------------------------------

    protected static function findOrCreateCart($user)
    {
        $existing_cart = Cart::where('vh_user_id', $user->id)->first();
        if ($existing_cart) {
            return $existing_cart;
        } else {
            $cart = new Cart();
            $cart->vh_user_id = $user->id;
            $cart->save();
            return $cart;
        }
    }
    //----------------------------------------------------------

    protected static function attachVariantionToCart($cart,$product_variation,$selected_vendor_id)
    {
        $cart->productVariations()->attach([
            $product_variation->id => [
                'vh_st_product_id' => $product_variation->vh_st_product_id,
                'vh_st_vendor_id' => $selected_vendor_id,
                'quantity' => 1,
            ]
        ]);

    }

}
