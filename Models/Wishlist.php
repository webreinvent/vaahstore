<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Faker\Factory;
use VaahCms\Modules\Store\Traits\ApiAuthUser;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use WebReinvent\VaahCms\Models\Taxonomy;
use WebReinvent\VaahCms\Models\TaxonomyType;
use VaahCms\Modules\Store\Models\Product;

class Wishlist extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;
    use ApiAuthUser;

    //-------------------------------------------------
    protected $table = 'vh_st_wishlists';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
//        'vh_user_id',
        'name',
        'slug',
        'type',
        'taxonomy_id_whishlists_status',
        'is_default',
        'status_notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    //-------------------------------------------------
    protected $fill_except = [

    ];

    //-------------------------------------------------
    protected $appends = [
        'user_wishlist_products',
        'user'];
    //-------------------------------------------------
    public static function isIncludeKey($key)
    {
        $include = request()->query('include', []);

        $includes = [];

        if (is_string($include)) {
            // handle ?include=price,vendor_product_data
            $includes = array_map('trim', explode(',', $include));
        } elseif (is_array($include)) {
            // handle ?include[price,vendor_product_data]=true
            foreach ($include as $k => $v) {
                if ($v === 'true') {
                    $keys = array_map('trim', explode(',', $k));
                    $includes = array_merge($includes, $keys);
                }
            }
        }

        return in_array($key, $includes);
    }
   public function getUserWishlistProductsAttribute()
    {
        if (!self::isIncludeKey('user_wishlist_products')) {
            return null;
        }
        $active_user_id = $this->getApiAuthUserId();

        $query = UserWishlist::with(['user', 'products'])
            ->where('vh_st_wishlist_id', $this->id);

        // Filter by authenticated user
        if ($active_user_id) {
            $query->where('vh_user_id', $active_user_id);
        }

        // Optional: Filter products by selected store
        $selected_store = request('selected_store');
        $store_id = null;

        if ($selected_store) {
            $store = Store::where(function ($q) use ($selected_store) {
                $q->where('id', $selected_store)
                    ->orWhere('slug', $selected_store);
            })->first();

            $store_id = $store?->id;
        }

        $user_wishlists = $query->get();
        $include_variations = request()->boolean('include.variations');

        return $user_wishlists->map(function ($user_wishlist) use ($include_variations, $store_id) {
            $user = $user_wishlist->user;
            $user_array = $user ? $user->only(['id', 'first_name','username','last_name', 'email']) : [];


            $filtered_products = $user_wishlist->products;

            // Filter by selected store
            if ($store_id) {
                $filtered_products = $filtered_products->where('vh_st_store_id', $store_id);
            }

            // Map and convert each product to array
            $user_array['products'] = $filtered_products->map(function ($product) use ($include_variations) {
                if (!$product->relationLoaded('brand')) {
                    $product->load('brand');
                }

                if ($include_variations) {
                    $selected_vendor_id = $product->vendor_product_data['selected_vendor']['id'] ?? null;
                    $product->variations = $product->loadVariations($selected_vendor_id);
                }

                $product_array = $product->toArray();
                if ($product->brand) {
                    $product_array['brand'] = [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name,
                        'slug' => $product->brand->slug,
                        'media' => $product->brand->media,
                    ];
                }
                $variation_id = $product->pivot->vh_st_product_variation_id ?? null;

                if ($variation_id) {
                    $variation = ProductVariation::with('medias')->find($variation_id);
                    $variation_array = $variation->toArray();
                    $vendor = data_get($product, 'vendor_product_data.selected_vendor');
                    unset($variation_array['product']);
                    $product_array['product_variation'] = Product::buildResolvedVariationResponse
                    ($product, $variation,'fallback',$vendor);
                }
                // If variations were loaded, make sure they're included in array
                if ($include_variations && isset($product->variations)) {
                    $product_array['variations'] = $product->variations;
                }

                return $product_array;
            })->values(); // Reset keys just in case

            return $user_array;
        });
    }

    //-------------------------------------------------

    public function getUserAttribute()
    {
        $sharedNames = ['Save For Later', 'My List'];

        if (in_array($this->name, $sharedNames)) {
            return null; // Don't return anything for shared names
        }

        return $this->users()->first(); // Single user object
    }
    //-------------------------------------------------
    public function user()
    {
        return $this->belongsTo(User::class, 'vh_user_id');
    }
    //-------------------------------------------------
    protected function serializeDate(DateTimeInterface $date)
    {
        $date_time_format = config('settings.global.datetime_format');
        return $date->format($date_time_format);
    }
    //-------------------------------------------------
    public function status(){
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id_whishlists_status', 'id')->select(['id','name','slug']);
    }

    //-------------------------------------------------
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'vh_st_user_wishlists',
            'vh_st_wishlist_id',
            'vh_user_id'
        )->withTimestamps();
    }
    public function userWishlists()
    {
        return $this->hasMany(UserWishlist::class, 'vh_st_wishlist_id');
    }


    //-------------------------------------------------


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

    public static function isReservedNameOrSlug($value): bool
    {
        $reserved = ['save-for-later', 'my-list', 'Save For Later', 'My List'];

        return in_array(trim($value), $reserved);
    }


    //-------------------------------------------------
    public static function createItem($request)
    {
        $inputs = $request->all();
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }
        $self = new self();
        $user_id_to_sync = $self->getApiAuthUserId() ?? ($inputs['vh_user_id'] ?? null);
        if ($user_id_to_sync && !empty($inputs['name'])) {
            $existing = Wishlist::withTrashed()
                ->where('name', $inputs['name'])
                ->whereHas('users', function ($q) use ($user_id_to_sync) {
                    $q->where('vh_user_id', $user_id_to_sync);
                })
                ->first();
            if ($existing) {
                return [
                    'success' => false,
                    'errors' => [ "This name already exists" . ($existing->deleted_at ? ' in trash.' : '.')]
                ];
            }
        }

        if (self::isReservedNameOrSlug($inputs['name'] ?? '') || self::isReservedNameOrSlug($inputs['slug'] ?? '')) {
            return [
                'success' => false,
                'errors' => ['This name already exists']
            ];
        }
        if (!empty($inputs['is_default']) && $user_id_to_sync) {
            self::where('is_default', 1)
                ->whereHas('users', function ($q) use ($user_id_to_sync) {
                    $q->where('vh_user_id', $user_id_to_sync);
                })
                ->update(['is_default' => 0]);
        }

        $item = new self();
        $item->fill($inputs);
        $item->save();

        if ($user_id_to_sync) {
            $item->users()->syncWithoutDetaching([$user_id_to_sync]);
        }
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

        foreach($search_terms as $search)
        {
            $query->where(function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('slug', 'LIKE', '%' . $search . '%');
                })

                    ->orWhere('id', 'LIKE', '%' . $search . '%');
            });
        }

        return $query;

    }
    //-------------------------------------------------
    public function scopeWishlistStatusFilter($query, $filter)
    {

        if(!isset($filter['wishlist_status']))
        {
            return $query;
        }
        $search = $filter['wishlist_status'];
        $query->whereHas('status' , function ($q) use ($search){
                      $q->whereIn('name' ,$search);
        });
    }
    //-------------------------------------------------
    public function scopeWishlistTypeFilter($query, $filter)
    {

        if(!isset($filter['wishlist_type']))
        {
            return $query;
        }
        $search = $filter['wishlist_type'];
        $query->whereHas('whishlistType',function ($q) use ($search) {
                $q->whereIn('name',$search);
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
    //------------------------------------------------------

    public static function getList($request)
    {
        $include = request()->query('include', []);
        $exclude = request()->query('exclude', []);
        $relationships = [
            'status'
        ];
        $self = new self();
        $api_auth_user_id = $self->getApiAuthUserId();

        foreach ($include as $key => $value) {
            if ($value === 'true') {
                $keys = explode(',', $key);
                foreach ($keys as $relationship) {
                    $relationship = trim($relationship);
                    if (method_exists(self::class, $relationship)) {
                        $relationships[] = $relationship;
                    }
                }
            }
        }

        // Check if the current user has a custom default wishlist (excluding 'my-list' and 'save-for-later')
        $user_default_exists = false;
        if ($api_auth_user_id) {
            $user_default_exists = self::where('is_default', 1)
                ->whereNotIn('slug', ['my-list', 'save-for-later'])
                ->exists();
        }

        // Main query
        $list = self::with($relationships)
            ->withCount('users');


        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->wishlistStatusFilter($request->filter);
        $list->wishlistTypeFilter($request->filter);
        $list->dateRangeFilter($request->filter);
        $list->userFilter($request->filter);
        $list->productFilter($request->filter);

        if ($api_auth_user_id) {
            $list->whereHas('users', function ($query) use ($api_auth_user_id) {
                $query->where('vh_user_id', $api_auth_user_id);
            });

            $list = $list->orderByRaw(
                $user_default_exists
                    ? "
                    CASE
                        WHEN is_default = 1 THEN 0
                        WHEN slug = 'my-list' THEN 1
                        WHEN slug = 'save-for-later' THEN 2
                        ELSE 3
                    END
                  "
                            : "
                    CASE
                        WHEN slug = 'my-list' THEN 0
                        WHEN slug = 'save-for-later' THEN 1
                        ELSE 2
                    END
                  "
            )
                ->orderByRaw(
                    $user_default_exists
                        ? "
                    CASE
                        WHEN is_default = 1 OR slug IN ('my-list', 'save-for-later') THEN NULL
                        ELSE slug
                    END ASC
                  "
                                : "
                    CASE
                        WHEN slug IN ('my-list', 'save-for-later') THEN NULL
                        ELSE slug
                    END ASC
                  "
                )
                ->orderBy('id', 'desc');
        }

        $rows = config('vaahcms.per_page');
        if ($request->has('rows')) {
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

        foreach ($list as $item) {
            foreach ($keys_to_exclude as $single_key) {
                if (isset($item[$single_key])) {
                    unset($item[$single_key]);
                }
                if (in_array($single_key, $item->getAppends())) {
                    $item->setAppends(array_diff($item->getAppends(), [$single_key]));
                }
            }
        }

        $default_wishlist = self::where('is_default', 1)->first();

        $response['success'] = true;
        $response['data'] = $list;
        $response['message'] = !$default_wishlist ? true : null;

        return $response;
    }

    //-------------------------------------------------
    public static function updateList($request)
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

        if(isset($inputs['items']))
        {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();
        }


        $items = self::whereIn('id', $items_id)
            ->withTrashed();

        $taxonomy_status = Taxonomy::getTaxonomyByType('whishlists-status');
        $approved_id = $taxonomy_status->where('slug','approved')->pluck('id')->first();
        $pending_id = $taxonomy_status->where('slug','pending')->pluck('id')->first();
        $rejected_id = $taxonomy_status->where('slug','rejected')->pluck('id')->first();

        switch ($inputs['type']) {
            case 'approve':
                $items->update(['taxonomy_id_whishlists_status' => $approved_id]);
                break;
            case 'pending':
                $items->update(['taxonomy_id_whishlists_status' => $pending_id]);
                break;
            case 'reject':
                $items->update(['taxonomy_id_whishlists_status' => $rejected_id]);
                break;
            case 'trash':
                self::whereIn('id', $items_id)->delete();
                $items->update(['deleted_by' => auth()->user()->id,'is_default' => 0]);
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
        $items = self::whereIn('id', $items_id)
            ->withTrashed()
            ->get();

        foreach ($items as $item) {

            if (self::isReservedNameOrSlug($item->slug)) {
                continue;
            }

            foreach ($item->userWishlists as $user_wishlist) {
                $user_wishlist->products()->detach();
                $user_wishlist->forceDelete();
            }

            $item->forceDelete();
        }

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

        $taxonomy_status = Taxonomy::getTaxonomyByType('whishlists-status');
        $approved_id = $taxonomy_status->where('slug','approved')->pluck('id')->first();
        $pending_id = $taxonomy_status->where('slug','pending')->pluck('id')->first();
        $rejected_id = $taxonomy_status->where('slug','rejected')->pluck('id')->first();

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
                    $items->update(['deleted_by' => auth()->user()->id,'is_default' => 0]);
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
                    foreach($items_id as $item_id)
                    {
                        $item = self::where('id', $item_id)->withTrashed()->first();

                    }
                    self::whereIn('id', $items_id)->forceDelete();
                }
                break;
            case 'approved-all':
                $list->update(['taxonomy_id_whishlists_status' => $approved_id]);
                break;
            case 'pending-all':
                $list->update(['taxonomy_id_whishlists_status' => $pending_id]);
                break;
            case 'reject-all':
                $list->update(['taxonomy_id_whishlists_status' => $rejected_id]);
                break;
            case 'approved':
                if($items->count() > 0) {
                    $items->update(['taxonomy_id_whishlists_status' => $approved_id]);
                }
                break;
            case 'pending':
                if($items->count() > 0) {
                    $items->update(['taxonomy_id_whishlists_status' => $pending_id]);
                }
                break;
            case 'reject':
                if($items->count() > 0) {
                    $items->update(['taxonomy_id_whishlists_status' => $rejected_id]);
                }
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
                $item_ids = $list
                    ->whereNotIn('name', ['Save For Later', 'My List'])
                    ->pluck('id')
                    ->toArray();
                foreach ($item_ids as $item_id) {
                    $item = self::withTrashed()->find($item_id);
                    if ($item) {
                        $user_wishlists = $item->userWishlists;
                        foreach ($user_wishlists as $user_wishlist) {
                            $user_wishlist->products()->detach();
                        }
                        $item->users()->detach();
                    }
                }
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
        $includes = request()->query('include', []);
        $exclude_param = request()->query('exclude', []);

        $excludes = [];
        foreach ($exclude_param as $key => $value) {
            $excludes = array_merge($excludes, explode(',', $key));
        }

        $excludes = array_map(function($key) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
        }, $excludes);

        $all_relations = ['createdByUser', 'updatedByUser', 'deletedByUser', 'user', 'status'];

        if (!empty($includes)) {
            $relationships = array_intersect($all_relations, $includes);
        } else {
            $relationships = $all_relations;
        }

        $relationships = array_filter($relationships, fn($r) => !in_array($r, $excludes));

        $item = self::where('id', $id)
            ->with($relationships)
            ->withTrashed()
            ->first();

        if (!$item) {
            return [
                'success' => false,
                'errors' => [trans("vaahcms-general.record_not_found_with_id") . $id]
            ];
        }

        return [
            'success' => true,
            'data' => $item->toArray(),
        ];
    }



    //-------------------------------------------------

    public static function updateItem($request, $id)
    {
        $inputs = $request->all();
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }
        // Ensure only one default per user
        $wishlist = self::with(['users'])->withTrashed()->findOrFail($id);
        $user_id_to_sync = (new self)->getApiAuthUserId() ?? ($wishlist->users->first()->pivot->vh_user_id ?? null);
        $is_protected = self::isReservedNameOrSlug($wishlist->name) && self::isReservedNameOrSlug($wishlist->slug);

        if (!$is_protected) {
            self::handleDefaultToggle($inputs, $user_id_to_sync, $wishlist, $id);
            self::handleNameAndSlug($inputs, $wishlist);
        } else {
            $inputs = Arr::except($inputs, ['name', 'slug', 'is_default']);
        }
        // Fill other fields (excluding name and slug if non-editable)
        $wishlist->fill(Arr::except($inputs, ['name', 'slug','is_default']));
        $wishlist->save();

        self::handleProtectedWishlistSync($wishlist, $inputs, $is_protected);
        $response = self::getItem($wishlist->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;
    }
    //-------------------------------------------------

    protected static function handleDefaultToggle($inputs, $user_id_to_sync, $wishlist, $id)
    {
        if (isset($inputs['is_default'])) {
            if ($inputs['is_default']) {
                self::where('is_default', 1)
                    ->whereHas('users', function ($q) use ($user_id_to_sync) {
                        $q->where('vh_user_id', $user_id_to_sync);
                    })
                    ->where('id', '!=', $id)
                    ->update(['is_default' => 0]);

                $wishlist->is_default = 1;
            } else {
                $wishlist->is_default = 0;
            }
        }
    }
    //-------------------------------------------------

    protected static function handleNameAndSlug($inputs, $wishlist)
    {
        if (empty(self::isReservedNameOrSlug($wishlist->name)) && empty(self::isReservedNameOrSlug($wishlist->slug))) {
            $wishlist->name = $inputs['name'] ?? $wishlist->name;
            $wishlist->slug = $inputs['slug'] ?? $wishlist->slug;
        }
    }
    //-------------------------------------------------

    protected static function handleProtectedWishlistSync($wishlist, $inputs, $is_protected)
    {
        if ($is_protected && !empty($inputs['user'])) {
            $user_id = $inputs['user']['id'];
            $wishlist->users()->syncWithoutDetaching([$user_id]);

            $user_wishlist = UserWishlist::firstOrCreate([
                'vh_st_wishlist_id' => $wishlist->id,
                'vh_user_id' => $user_id
            ]);

            if (!empty($inputs['products'])) {
                foreach ($inputs['products'] as $product) {
                    $product_id = $product['id'];
                    $variation_id = $product['vh_st_product_variation_id'] ?? null;

                    $exists = $user_wishlist->products()
                        ->where('vh_st_product_id', $product_id)
                        ->when($variation_id, fn($q) => $q->wherePivot('vh_st_product_variation_id', $variation_id))
                        ->exists();

                    if (!$exists) {
                        $user_wishlist->products()->attach($product_id, [
                            'vh_st_product_variation_id' => $variation_id
                        ]);
                    }
                }
            }
        }
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
        // Prevent deletion for special slugs
        if (self::isReservedNameOrSlug($item->name) || self::isReservedNameOrSlug($item->slug)) {
            return [
                'success' => false,
                'errors' => ['This wishlist cannot be deleted: ' . $item->slug]
            ];
        }
        foreach ($item->userWishlists as $user_wishlist) {
            $user_wishlist->products()->detach();
            $user_wishlist->forceDelete();
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
                $item = self::where('id', $id)->withTrashed()->first();
                if($item->delete()){
                    $item->deleted_by = auth()->user()->id;
                    $item->is_default = 0;
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
            'vh_user_id'=> 'nullable',
            'name' => 'required|max:100',
            'slug' => 'required|max:100',
            'type' => '',
            'taxonomy_id_whishlists_status'=> 'nullable',
            'status_notes' => 'max:250',
        );

        $customMessages = array(
            'vh_user_id.required' => 'The User field is required.',
            'name.required' => 'The Name field is required.',
            'name.max' => 'The Name field may not be greater than :max characters.',
            'slug.required' => 'The Slug field is required.',
            'slug.max' => 'The Slug field may not be greater than :max characters.',
            'taxonomy_id_whishlists_status.required' => 'The Status field is required.',
            'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
        );

        $validator = \Validator::make($inputs, $rules, $customMessages);
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

        // fill the user field here

        $users_ids = User::where('is_active',1)->pluck('id')->toArray();
        $users_id = $users_ids[array_rand($users_ids)];
        $users_id_data = User::where('is_active',1)->where('id',$users_id)->first();
        $inputs['vh_user_id'] =$users_id;
        $inputs['user'] = $users_id_data;

        // fill the taxonomy status field here

        $taxonomy_status = Taxonomy::getTaxonomyByType('whishlists-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        if($taxonomy_status->isEmpty())
        {
            $response['success'] = false;
            $response['errors'][] = 'No Wishlist Status Found , Create Wishlist Status From Taxonomies ';
            return $response;

        }
        $status_id = $status_ids[array_rand($status_ids)];
        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['taxonomy_id_whishlists_status'] = $status_id;
        $inputs['status']=$status;

        // fill the name field here
        $max_chars = rand(5,100);
        $inputs['name']=$faker->text($max_chars);

        // fill the slug field here
        $inputs['slug']=Str::slug($inputs['name']);

        // fill the is default field here

        $inputs['type'] = rand(0,1);
        $inputs['is_default'] = 0;


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
    public static function searchVaahUsers($request)
    {
        $query = $request->input('query');
        $search_approved = User::select('id', 'first_name','username')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_approved->where(function($q) use ($query) {

                $q->where('username', 'LIKE', '%' . $query . '%');
            });
        }
        $search_approved = $search_approved->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_approved;
        return $response;
    }
    //-------------------------------------------------
    public static function searchType($request)
    {
        $query = $request->input('query');
        if(empty($query)) {
            $item = Taxonomy::getTaxonomyByType('wishlists-types');
        } else {
            $tax_type = TaxonomyType::getFirstOrCreate('wishlists-types');

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

    public static function searchStatus($request)
    {
        $query = $request->input('query');
        if(empty($query)) {
            $item = Taxonomy::getTaxonomyByType('whishlists-status');
        } else {
            $tax_type = TaxonomyType::getFirstOrCreate('whishlists-status');

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
    public static function searchProduct($request){
        $query_text = $request->input('search');
        $selected_store = $request->input('selected_store');
        $products = Product::query()
            ->where('is_active', 1)
            ->when($selected_store, function ($q) use ($selected_store) {
                $q->where('vh_st_store_id', $selected_store);
            })
            ->when($query_text, function ($q) use ($query_text) {
                $q->where('name', 'like', "%{$query_text}%");
            }, function ($q) {
                $q->inRandomOrder()->take(10);
            })
            ->select('id', 'name', 'slug')
            ->get();

        return [
            'success' => true,
            'data' => $products,
        ];

    }

    //-------------------------------------------------

    public function scopeUserFilter($query, $filter)
    {
        if(!isset($filter['users'])
            || is_null($filter['users'])
            || $filter['users'] === 'null'
        )
        {
            return $query;
        }

        $users = $filter['users'];

        return $query->whereHas('user', function ($query) use ($users) {
            $query->whereIn('username', $users);
        });
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

        $products = $filter['products'];

        return $query->whereHas('products', function ($query) use ($products) {
            $query->whereIn('slug', $products);
        });
    }

    //-------------------------------------------------

    public static function searchProductBySlug($request)
    {

        $query = $request['filter']['product'];
        $products = Product::whereIn('name',$query)
            ->orWhereIn('slug',$query)
            ->select('id','name','slug')->get();

        $response['success'] = true;
        $response['data'] = $products;
        return $response;
    }

    //-------------------------------------------------

    public static function searchUserBySlug($request)
    {

        $query = $request['filter']['user'];

        $users = User::whereIn('username',$query)
            ->select('id','username')->get();

        $response['success'] = true;
        $response['data'] = $users;
        return $response;
    }

    //-------------------------------------------------

    public static function updateUserWishlistProducts($request, $id)
    {
        $self = new self();

        $user_id_to_sync = $self->getApiAuthUserId() ?? ($request->vh_user_id ?? null);
        if (!$user_id_to_sync) {
            return [
                'errors' => ['User ID is required'],
            ];
        }
        $wishlist = Wishlist::withTrashed()->find($id);
        $cart = Cart::where('vh_user_id', $user_id_to_sync)->first();
        if(!$wishlist)
        {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_not_found_with_id").$id;
            return $response;
        }
        // Link user to wishlist
        $wishlist->users()->syncWithoutDetaching([$user_id_to_sync]);

        // Get or create UserWishlist
        $user_wishlist = UserWishlist::firstOrCreate([
            'vh_st_wishlist_id' => $wishlist->id,
            'vh_user_id' => $user_id_to_sync
        ]);

        $action = $request->action ?? 'add';
        $products = collect($request->products);

        // Cache existing pivot records if action is "add"
        $existing_pivots = [];

        if (in_array($action, ['add', 'move'])) {
            $existing_pivots = $user_wishlist->products()
                ->withPivot('vh_st_product_variation_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [ $item->id . '_' . ($item->pivot->vh_st_product_variation_id ?? 'null') => true ];
                });
        }
        foreach ($products as $product) {
            $product_id = $product['id'];
            $variation_id = $product['vh_st_product_variation_id'] ?? null;
            $vendor_id = $product['vh_st_vendor_id'] ?? null;
            $key = $product_id . '_' . ($variation_id ?? 'null');

            if ($action === 'delete') {
                $detach_query = $user_wishlist->products();

                $detach_query->wherePivot('vh_st_product_id', $product_id);
                if ($variation_id !== null) {
                    $detach_query->wherePivot('vh_st_product_variation_id', $variation_id);
                }

                $detach_query->detach();

            } if ($action === 'move') {
                if (isset($existing_pivots[$key])) {
                    // Already in wishlist → remove from wishlist
                    $user_wishlist->products()
                        ->wherePivot('vh_st_product_variation_id', $variation_id)
                        ->detach($product_id);
                } else {
                    // Add to wishlist
                    $user_wishlist->products()->attach($product_id, [
                        'vh_st_product_variation_id' => $variation_id,
                    ]);

                    // Then remove from cart
                    if ($cart && $variation_id && $vendor_id) {
                        $cart->products()
                            ->wherePivot('vh_st_product_id', $product_id)
                            ->wherePivot('vh_st_product_variation_id', $variation_id)
                            ->wherePivot('vh_st_vendor_id', $vendor_id)
                            ->detach();
                    }
                }
            } elseif ($action === 'add') {
                if (!isset($existing_pivots[$key])) {
                    $user_wishlist->products()->attach($product_id, [
                        'vh_st_product_variation_id' => $variation_id,
                    ]);
                }
            }
        }
        if ($action === 'move' && $cart) {
//            return Cart::getItem($request, $cart->id);
            $cart_response = Cart::getItem($request, $cart->id);
            $cart_response['messages'][] = 'Items moved to wishlist successfully.';
            return $cart_response;

        }
        return [
            'success' => true,
            'data' => $wishlist->fresh(),
            'messages' => [trans("vaahcms-general.saved_successfully")],
        ];
    }
    //-------------------------------------------------

    public static function getWishlistUsers( Request $request,$id)
    {   $self = new self();
        $active_user_id = $self->getApiAuthUserId();
        $query = UserWishlist::with(['user', 'products'])
            ->where('vh_st_wishlist_id', $id);
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('first_name', 'like', '%' . $search . '%');
            });
        }


        // Filter by authenticated user
        if ($active_user_id) {
            $query->where('vh_user_id', $active_user_id);
        }

        // Optional: Filter by selected store
        $store_id = null;
        if ($selected_store = $request->input('selected_store')) {
            $store = Store::where(function ($q) use ($selected_store) {
                $q->where('id', $selected_store)->orWhere('slug', $selected_store);
            })->first();
            $store_id = $store?->id;
        }

        $include_variations = $request->boolean('include.variations', false);
        $per_page = $request->has('per_page') && is_numeric($request->input('per_page'))
            ? (int) $request->input('per_page')
            : 20;

        $paginated = $query->paginate($per_page);

        $mapped = $paginated->getCollection()->map(function ($user_wishlist) use ($include_variations, $store_id) {
            $user = $user_wishlist->user;
            $user_array = $user ? $user->toArray() : [];

            $filtered_products = $user_wishlist->products;

            if ($store_id) {
                $filtered_products = $filtered_products->where('vh_st_store_id', $store_id);
            }

            $user_array['products'] = $filtered_products->map(function ($product) use ($include_variations) {
                if ($include_variations) {
                    $selected_vendor_id = $product->vendor_product_data['selected_vendor']['id'] ?? null;
                    $product->variations = $product->loadVariations($selected_vendor_id);
                }

                $product_array = $product->toArray();
                $variation_id = $product->pivot->vh_st_product_variation_id ?? null;
                // If a variation is selected in pivot, include it
                if ($variation_id) {
                    $variation = ProductVariation::with('medias')->find($variation_id);
                    if ($variation) {
                        $product_array['selected_variation'] = $variation;
                    }
                }
                if ($include_variations && isset($product->variations)) {
                    $product_array['variations'] = $product->variations;
                }

                return $product_array;
            })->values();

            return $user_array;
        });

        $paginated->setCollection($mapped);
        $response['success'] = true;
        $response['data'] = $paginated;
        return $response;
    }
    //-------------------------------------------------

    public static function moveWishlistToCart($request, $wishlist_id)
    {
        $self = new self();

        $user_id = $self->getApiAuthUserId()
            ?? ($request->input('user.id') ?? null);

        if (!$user_id) {
            return ['success' => false, 'errors' => ['User ID is required']];
        }
        $wishlist = Wishlist::find($wishlist_id);
        // Fetch UserWishlist
        $user_wishlist = UserWishlist::where('vh_st_wishlist_id', $wishlist_id)
            ->where('vh_user_id', $user_id)
            ->first();

        if (!$user_wishlist) {
            return ['success' => false, 'errors' => ['Wishlist not found']];
        }

        // Load wishlist products with variation pivot
        $wishlist_products = $user_wishlist->products()
            ->withPivot('vh_st_product_variation_id')
            ->get();

        if ($wishlist_products->isEmpty()) {
            return ['success' => false, 'errors' => ['No products found in wishlist']];
        }

        $payload_products = collect($request->input('products', []));

        // Build payload for generateCart using payload vendor_id
        $product_payload = $wishlist_products->map(function ($product) use ($payload_products) {
            $product_id = $product->id;
            $variation_id = $product->pivot->vh_st_product_variation_id;

            $matched = $payload_products->first(function ($p) use ($product_id, $variation_id) {
                return $p['id'] == $product_id && $p['variation_id'] == $variation_id;
            });

            if (!$matched || empty($matched['vendor_id'])) {
                return null;
            }

            return [
                'id' => $product_id,
                'variation_id' => $variation_id,
                'vendor_id' => $matched['vendor_id'],
                'quantity' => $matched['quantity'] ?? 1
            ];
        })->filter()->values();

        if ($product_payload->isEmpty()) {
            return ['success' => false, 'errors' => ['No valid items to move']];
        }

        // Create request for generateCart
        $cart_request = new Request([
            'user' => ['id' => $user_id],
            'products' => $product_payload,
        ]);

        // Call existing method
        $cart_response = Product::generateCart($cart_request);

        // If cart update failed
        if (!$cart_response['success']) {
            return [
                'success' => false,
                'data' => null,
                'errors' => $cart_response['errors'] ?? ['Something went wrong while updating cart'],

            ];
        }
        if ($wishlist && $wishlist->slug === 'save-for-later') {
        foreach ($product_payload as $p) {
            $user_wishlist->products()
                ->wherePivot('vh_st_product_variation_id', $p['variation_id'])
                ->detach($p['id']);
        }
        }

        // Fetch cart count
        $cart = Cart::where('vh_user_id', $user_id)->first();
        $cart_count = $cart ? $cart->cart_products_count : 0;

        // Get refreshed wishlist
        $wishlist_data = self::getItem($wishlist_id);

        if (isset($wishlist_data['data']) && is_array($wishlist_data['data'])) {
            $wishlist_data['data']['cart_products_count'] = $cart_count;
        }
        return [
            'success' => true,
            'data' => $wishlist_data['data'] ?? null,
            'messages' => ['Wishlist items moved to cart successfully.']
        ];
    }


}
