<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use VaahCms\Modules\Store\Models\Product;
use WebReinvent\VaahCms\Entities\Taxonomy;
use Faker\Factory;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use VaahCms\Modules\Store\Models\Vendor;
use WebReinvent\VaahCms\Models\TaxonomyType;

class ProductVendor extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_product_vendors';
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
        'vh_st_product_id',
        'vh_st_vendor_id',
        'added_by',
        'can_update',
        'taxonomy_id_product_vendor_status',
        'status_notes',
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
    public function storeVendorProduct()
    {
        return $this->belongsToMany(Store::class, 'vh_st_vendor_pro_stores', 'vh_st_vendor_product_id', 'vh_st_store_id')->withTrashed();
    }

    //-------------------------------------------------
//    public function productVariations()
//    {
//        return $this->hasMany(ProductVariation::class, 'vh_st_product_id', 'id')
//            ->withTrashed();
//    }
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
    public function addedByUser()
    {
        return $this->hasOne(User::class,'id','added_by')->select('id','first_name');
    }
    //-------------------------------------------------
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'vh_st_product_id')->withTrashed()->select('id', 'name', 'slug','deleted_at');
    }
    //-------------------------------------------------
    public function stores()
    {
        return $this->hasMany(Store::class, 'id', 'vh_st_store_id')->select('id', 'name', 'slug');
    }

    //----------------------------------------------------------------------------------
    public function vendor()
    {
        return $this->hasOne(Vendor::class,'id','vh_st_vendor_id')->withTrashed()->select('id','name', 'slug','is_default','deleted_at');
    }


    public function productVariationPrices()
    {
        return $this->belongsToMany(ProductVariation::class,
            'vh_st_product_prices','vh_st_vendor_product_id', 'vh_st_product_variation_id',
        )->withPivot(['id','amount']);
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
    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_product_vendor_status')->select('id','name','slug');
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

    //--------------------Add and update product price-----------------------------
//    public static function createProductPrice($request)
//    {
//        $inputs = $request->all();
////        $vhStVendorProductId = $inputs['id'];
////        dd($inputs);
//        $validation = self::validationProductPrice($inputs);
//        if (!$validation['success']) {
//            return $validation;
//        }
//
//        $response = [];
//        $saved_variations = 0;
//        foreach ($inputs['product_variation'] as $key => $variation) {
//            $variation_price = ProductPrice::where([
//                'vh_st_vendor_id' => $inputs['vh_st_vendor_id'],
//                'vh_st_product_id' => $inputs['vh_st_product_id'],
//                'vh_st_product_variation_id' => $variation['id']
//            ])->first();
//            if (isset($variation['deleted_at'])) {
//                continue;
//            }
//            if ($variation_price) {
//                if ($variation['amount'] === null) {
//                    $variation_price->forceDelete();
//                } else {
//                    // 'amount' is provided, update the record
//                    $variation_price->fill([
//                        'vh_st_vendor_id' => $inputs['vh_st_vendor_id'],
//                        'vh_st_product_id' => $inputs['vh_st_product_id'],
//                        'vh_st_product_variation_id' => $variation['id'],
//                        'amount' => $variation['amount'],
//                    ]);
//                    $variation_price->save();
//                }
//                $saved_variations++;
//            }
//            if (!$variation_price && isset($variation['amount'])) {
//                $new_variation_price = new ProductPrice;
//                $new_variation_price->fill([
//                    'vh_st_vendor_id' => $inputs['vh_st_vendor_id'],
//                    'vh_st_product_id' => $inputs['vh_st_product_id'],
//                    'vh_st_product_variation_id' => $variation['id'],
//                    'amount' => $variation['amount'],
//                ]);
//                $new_variation_price->save();
//                $saved_variations++;
//
//            }
//
//
//        }
//        if ($saved_variations > 0) {
//            $response['messages'][] = trans("vaahcms-general.saved_successfully");
//        }
//        return $response;
//    }
    public static function createProductPrice($request)
    {
        $inputs = $request->all();
        $vhStVendorProductId = $inputs['id'];
        $validation = self::validationProductPrice($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        $response = [];
        $saved_variations = 0;
        foreach ($inputs['product_variation'] as $key => $variation) {
            $variation_price = ProductPrice::where([
                'vh_st_vendor_id' => $inputs['vh_st_vendor_id'],
                'vh_st_product_id' => $inputs['vh_st_product_id'],
                'vh_st_product_variation_id' => $variation['id'],
            ])->first();

            if (isset($variation['deleted_at'])) {
                continue;
            }

            if ($variation_price) {
                if ($variation['amount'] === null) {
                    $variation_price->forceDelete();
                } else {
                    // 'amount' is provided, update the record
                    $variation_price->fill([
                        'vh_st_vendor_id' => $inputs['vh_st_vendor_id'],
                        'vh_st_product_id' => $inputs['vh_st_product_id'],
                        'vh_st_product_variation_id' => $variation['id'],
                        'vh_st_vendor_product_id' => $vhStVendorProductId,
                        'amount' => $variation['amount'],
                    ]);
                    $variation_price->save();
                }
                $saved_variations++;
            }

            if (!$variation_price && isset($variation['amount'])) {
                $new_variation_price = new ProductPrice;
                $new_variation_price->fill([
                    'vh_st_vendor_id' => $inputs['vh_st_vendor_id'],
                    'vh_st_product_id' => $inputs['vh_st_product_id'],
                    'vh_st_product_variation_id' => $variation['id'],
                    'vh_st_vendor_product_id' => $vhStVendorProductId,
                    'amount' => $variation['amount'],
                ]);
                $new_variation_price->save();
                $saved_variations++;
            }
        }

        if ($saved_variations > 0) {
            $response['messages'][] = trans("vaahcms-general.saved_successfully");
        }


        $response = self::getItem($vhStVendorProductId);



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


           //  check if product vendor exist
            $item = self::where('vh_st_vendor_id', $inputs['vendor']['id'])
            ->where('vh_st_product_id', $inputs['product']['id'])
            ->withTrashed()
            ->first();

            if ($item) {
                $error_message = "This vendor and product (" . $inputs['product']['name'] . ") is already exists".($item->deleted_at?' in trash.':'.');
                $response['errors'][] = $error_message;
                return $response;
            }

            $item = new self();
            $item->fill($inputs);
            if (isset($inputs['taxonomy_id_product_vendor_status']['id'])) {
                $item->taxonomy_id_product_vendor_status = $inputs['taxonomy_id_product_vendor_status']['id'];
              } else {
                $item->taxonomy_id_product_vendor_status = $inputs['taxonomy_id_product_vendor_status'];
              }
            $item->save();

            // Save value in the pivot table
            if (isset($inputs['store_vendor_product']) && is_array($inputs['store_vendor_product'])) {
                  foreach ($inputs['store_vendor_product'] as $store) {
                  $item->storeVendorProduct()->attach($store['id']);
                  }
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

        if(!isset($filter['q']))
        {
            return $query;
        }
        $keywords = explode(' ',$filter['q']);
        foreach($keywords as $search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('vendor', function ($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('slug', 'LIKE', '%' . $search . '%');
                    });
            });
        }

    }
    //-------------------------------------------------
    public function scopeStatusFilter($query, $filter)
    {

        if (!isset($filter['status'])) {
            return $query;
        }
        $status = $filter['status'];
        $query->whereHas('status', function ($q) use ($status) {
            $q->whereIn('name', $status);
        });
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
    public function scopeProductFilter($query, $filter)
    {
        if(!isset($filter['product']))
        {
            return $query;
        }
        $product = $filter['product'];
        $query->whereHas('product',function ($q) use ($product) {
            $q->whereIn('slug',$product);
        });

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)
            ->with([
                'product.productVariationsForVendorProduct' => function ($query) {
                    $query->get('id', 'name', 'price');
                },
                'vendor',
                'addedByUser',
                'status',
                'stores',
                'productVariationPrices',
            ]);
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->productFilter($request->filter);
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
                $items->update(['deleted_by' => auth()->user()->id]);
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

        // delete value from pivot table
        $item_ids = collect($inputs['items'])->pluck('id')->toArray();
        foreach ($item_ids as $key => $value) {
            $item = self::find($value);
            $item->storeVendorProduct()->detach();
        }
        $vendors_id = collect($inputs['items'])->pluck('vh_st_vendor_id')->toArray();
        ProductPrice::whereIn('vh_st_vendor_id', $vendors_id)->forceDelete();
        self::whereIn('id', $item_ids)->forceDelete();

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
                    self::whereIn('id', $items_id)->forceDelete();
                }
                break;
            case 'activate-all':
                $list->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                $list->update(['is_active' => null,]);
                break;
            case 'trash-all':
                $list->update(['deleted_by'  => auth()->user()->id]);
                $list->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->update(['deleted_by' => null]);
                $list->restore();
                break;
            case 'delete-all':
                $vendor_ids = self::withTrashed()->pluck('vh_st_vendor_id')->toArray();
                $item_ids = self::withTrashed()->pluck('id')->toArray();
                foreach ($item_ids as $item_id) {
                    $item = self::where('id',$item_id)->withTrashed()->first();
                    $item->storeVendorProduct()->detach();
                }
                ProductPrice::whereIn('vh_st_vendor_id', $vendor_ids)->forceDelete();
                $list = self::withTrashed();
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

    //-------------------------------------------------
    public static function getItem($id)
    {
        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser', 'product', 'vendor', 'addedByUser', 'status', 'stores', 'storeVendorProduct'])
            ->withTrashed()
            ->first();

        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: ' . $id;
            return $response;
        }

        $itemProduct = Product::withTrashed()->find($item->vh_st_product_id);

        $item['productList'] = Product::where('vh_st_store_id', $itemProduct->vh_st_store_id)->select('id', 'name', 'slug');

        // To get data for dropdown of product price
        $array_item = $item->toArray();
        $variations = [];
        $variations_data = ProductVariation::where('vh_st_product_id', $array_item['vh_st_product_id'])
            ->select('id', 'name', 'slug', 'is_default', 'price','deleted_at')
            ->withTrashed()
            ->get();
        foreach($variations_data as $variation_data)
        {
            $price = ProductPrice::where('vh_st_vendor_id', $array_item['vh_st_vendor_id'])
                ->where('vh_st_product_variation_id', $variation_data['id'])
                ->pluck('amount')
                ->first();
            $variations[] = [
                'name' => $variation_data['name'],
                'id' => $variation_data['id'],
                'amount' => $price === null ? $variation_data['price']:$price,
                'deleted_at'=>$variation_data['deleted_at'],
            ];


        }

        $item['product_variations'] = $variations;

        $item->storeVendorProduct->each(function ($store_vendor) {
            unset($store_vendor->pivot);
        });

        $response['success'] = true;
        $response['data'] = $item;
        return $response;
    }

    //-------------------------------------------------


    //-------------------------------------------------
    public static function updateItem($request, $id)
    {

        $inputs = $request->all();
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // check if vendor exist
            $item = self::where('id', '!=', $inputs['id'])
                ->withTrashed()
                ->where('vh_st_vendor_id', $inputs['vendor']['id'])
                ->where('vh_st_product_id', $inputs['product']['id'])->first();

            if ($item) {
                $response['success'] = false;
                $response['errors'][] = "This vendor and product (" . $inputs['product']['name'] . ") is already exist".($item->deleted_at?' in trash.':'.');
                return $response;
            }

            $item = self::where('id', $id)->withTrashed()->first();
            $item->fill($inputs);

            if(is_int($inputs['taxonomy_id_product_vendor_status'])) {
                $item->taxonomy_id_product_vendor_status = $inputs['taxonomy_id_product_vendor_status'];
            }
            if(is_array($inputs['taxonomy_id_product_vendor_status'])) {
                $item->taxonomy_id_product_vendor_status = $inputs['taxonomy_id_product_vendor_status']['id'];
            }

            $item->save();

           // Update the relationship with the stores
            $storeData = $inputs['store_vendor_product'];
            $storeIds = [];
            foreach ($storeData as $store) {
                $storeIds[] = $store['id'];
            }
            $item->storeVendorProduct()->sync($storeIds);

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

        $vendor_id = self::withTrashed()->where('id',$id)->pluck('vh_st_vendor_id')->first();
        ProductPrice::where('vh_st_vendor_id', $vendor_id)->forceDelete();
        $item->storeVendorProduct()->detach();
        $item->forceDelete();

        $response['success'] = true;
        $response['data'] = [];
        $response['errors'][] = trans("vaahcms-general.record_has_been_deleted");

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
        $rules = validator($inputs, [
            'vh_st_vendor_id'=> 'required',
            'store_vendor_product' => 'required',
            'vh_st_product_id'=> 'required',
            'taxonomy_id_product_vendor_status'=> 'required',
            'added_by'=> 'required|max:150',
            'can_update'=> 'required|max:150',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:100'
                             ],
        ],

        [
            'vh_st_vendor_id.required' => 'The Vendor field is required',
            'store_vendor_product.required' => 'The Store field is required',
            'vh_st_product_id.required' => 'The Product field is required',
            'added_by.required' => 'The Added By field is required',
            'taxonomy_id_product_vendor_status.required' => 'The Status field is required',
            'status_notes.required_if' => 'The Status notes field is required for "Rejected" Status',
            'status_notes.max' => 'The Status notes field may not be greater than :max characters.',

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

    //-------------------validation for product price------------------------------
    public static function validationProductPrice($inputs)
    {

        $rules = validator($inputs, [
            'vh_st_product_id'=> 'required',
            'product_variation.*.amount' => 'nullable|min:1|max:9999999',
        ], [
            'vh_st_product_id.required' => 'The Product field is required',
//            'product_variation.*.amount.numeric' => 'The Price field must be a number',
            'product_variation.*.amount.max' => 'The Price field cannot be greater than :max.',
//            'product_variation.*.amount.min' => 'The Price field cannot be less than :min.',
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
            ->withTrashed()
            ->first();
        return $item;
    }
//-------------------------------------------------
    public static function deleteVendor($item_id){

        $response=[];

        if ($item_id) {
            $item_exist = self::where('vh_st_vendor_id', $item_id)->first();
            if ($item_exist) {

                self::where('vh_st_vendor_id', $item_id)->forceDelete();
                $response['success'] = true;
            }
        } else {
            $response['success'] = false;
        }

        return $response;

    }
    //-----------------------------------------------------

    public static function deleteVendors($items_id){
        $response=[];
        if ($items_id) {
            $items_exist = self::whereIn('vh_st_vendor_id', $items_id)->get();

            if ($items_exist) {
                self::whereIn('vh_st_vendor_id', $items_id)->forceDelete();
                $response['success'] = true;
            }
        }

        $response['success'] = false;

        return $response;

    }
    //-------------------------------------------------
    public static function deleteProduct($items_id){

        $response=[];
        if($items_id){
            self::where('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }
    //-------------------------------------------------
    public static function deleteProducts($items_id){

        $response=[];
        if($items_id){
            self::whereIn('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;
    }
    //-------------------------------------------------
    public static function searchVendor(Request $request): array
    {

        $user = Auth::user();

        $query = $request->input('filter.q.query');
        $vendor = Vendor::where('is_active', 1, $user->id)
            ->select('id', 'name', 'slug');

        if ($query !== null) {
            $vendor->where('name', 'like', "%$query%");
        }

        $vendor = $vendor->get();

        $response['success'] = true;
        $response['data'] = $vendor;

        return $response;
    }

    //-------------------------------------------------

    public static function searchAddedBy(Request $request): array
    {
        $user = auth()->user();

        $query = $request->input('filter.q.query');
        $added_users = User::where('is_active', 1);

        if ($query !== null) {
            $added_users->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%$query%")
                    ->orWhere('last_name', 'like', "%$query%")
                    ->orWhere('email', 'like', "%$query%");
            });
        }

        $active_users = $added_users->get();

        $response['success'] = true;
        $response['data'] = $active_users;

        return $response;
    }

    //-------------------------------------------------

    public static function searchStatus($request)
    {
        $query = $request->input('filter.q.query');

        if (empty($query)) {
            $item = Taxonomy::getTaxonomyByType('product-vendor-status');
        } else {
            $status = TaxonomyType::getFirstOrCreate('product-vendor-status');
            $item = [];

            if (!$status) {
                return $item;
            }

            $item = Taxonomy::whereNotNull('is_active')
                ->where('vh_taxonomy_type_id', $status->id)
                ->where('name', 'LIKE', '%' . $query . '%')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $item;
        return $response;
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
            $item->storeVendorProduct()->attach(
                $inputs['vh_st_store_id']
            );

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

        $taxonomy_status = Taxonomy::getTaxonomyByType('product-vendor-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        $status_id = $status_ids[array_rand($status_ids)];
        $inputs['taxonomy_id_product_vendor_status'] = $status_id;
        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['status']=$status;

        // fill the store field here

        $vendors = Vendor::where('is_active', 1)->get();
        $vendors_ids = $vendors->pluck('id')->toArray();
        if (!empty($vendors_ids)) {
            $vendors_id = $vendors_ids[array_rand($vendors_ids)];
            $vendor = $vendors->where('id', $vendors_id)->first();
            $inputs['vendor'] = $vendor;
        }

        if (!isset($inputs['vendor'])) {
            $response['success'] = false;
            $response['errors'][] = 'No vendor exist.';
            return $response;
        }

        $inputs['vh_st_vendor_id'] = $vendors_id;


        $stores = Store::where('is_active', 1)->get();
        $store_ids = $stores->pluck('id')->toArray();
        $store_id = null;

        if (!empty($store_ids)) {
            $store_id = $store_ids[array_rand($store_ids)];
            $store = $stores->where('id', $store_id)->first();

            $inputs['store_vendor_product'] = $store;
        }

        if (!isset($inputs['store_vendor_product'])) {
            $response['success'] = false;
            $response['errors'][] = 'No store exist.';
            return $response;
        }

        $inputs['vh_st_store_id'] = $store_id;


        $products = Product::where('is_active', 1)
            ->where('vh_st_store_id', $store_id)
            ->get();

        if ($products->isEmpty()) {
            $response['success'] = false;
            $response['errors'][] = 'No products exist for the selected store.';
            return $response;
        }

        $product_ids = $products->pluck('id')->toArray();
        $product_ids = $product_ids[array_rand($product_ids)];
        $products = $products->where('id', $product_ids)->first();
        $inputs['product'] = $products;
        $inputs['vh_st_product_id'] = $product_ids;

        $users = User::where('is_active',1)->get();
        $user_ids = $users->pluck('id')->toArray();
        $user_id = $user_ids[array_rand($user_ids)];
        $user = $users->where('id',$user_id)->first();
        $inputs['added_by_user'] = $user;
        $inputs['added_by'] = $user_id ;





        $inputs['can_update'] =  rand(0,1);
        $inputs['is_active'] = 1;
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

    public static function searchActiveStores($request){
        $active_store = Store::select('id', 'name','slug')->where('is_active',1);
        if ($request->has('query') && $request->input('query')) {
            $active_store->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $active_stores = $active_store->limit(10)->get();

        $response['success'] = true;
        $response['data'] = $active_stores;
        return $response;

    }

    //-------------------------------------------------

    public static function getProduct($request)
    {
        $query = $request->input('query');
        if($query === null)
        {
            $product_name = Product::select('id','name','slug')
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        else{

            $product_name = Product::where('name', 'like', "%$query%")
                ->orWhere('slug','like',"%$query%")
                ->select('id','name','slug')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $product_name;
        return $response;

    }

    //-------------------------------------------------

    public static function getProductsBySlug($request)
    {
        $query = $request['filter']['product'];

        $products= Product::whereIn('name',$query)
            ->orWhereIn('slug',$query)
            ->select('id','name','slug')->get();

        $response['success'] = true;
        $response['data'] = $products;
        return $response;
    }
    //-------------------------------------------------


    //-------------------------------------------------

    public static function searchVariationOfProduct($request)
    {
        $input = $request->all();
        $id = $input['id'];
        $product_variations = ProductVariation::where('vh_st_product_id', $id)->withTrashed()
            ->get();

        $response['success'] = true;
        $response['data'] = $product_variations;

        return $response;


    }

    //-------------------------------------------------

    public static function productForStore($request)
    {
        $inputs = $request->all();
        $response = [];
        $ids = $inputs['id'];
        $q = $inputs['q'];

        $product = Product::where('is_active', 1)
            ->whereIn('vh_st_store_id', $ids);

        if (!empty($q)) {
            $product->where(function ($sub_query) use ($q) {
                $sub_query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('slug', 'like', '%' . $q . '%');
            });
        }

        $product_for_store = $product->with('store')
            ->orderBy('vh_st_store_id')
            ->orderBy('id')
            ->when(empty($q), function ($query) {
                return $query->take(10);
            })
            ->get(['id', 'name', 'slug', 'vh_st_store_id']);

        $response['success'] = true;
        $response['data'] = $product_for_store;
        return $response;
    }

    //-------------------------------------------------



    public static function getdefaultValues($request)
    {
        $defaultStore = Store::where(['is_active' => 1,'deleted_at'=>null, 'is_default' => 1])->first(['id', 'name', 'slug']);
        $defaultVendor = Vendor::where(['is_active'=>1,'deleted_at'=>null,'is_default'=>1])->first(['id', 'name', 'slug']);

        $response['success'] = true;
        $response['data'] = [
            'default_store' => $defaultStore ?: null,
            'default_vendor' => $defaultVendor ?: null,
            ];

        if ($defaultStore === null && $defaultVendor === null) {
            $response['success'] = false;
            $response['data'] = null;
        }

        return $response;
    }




}
