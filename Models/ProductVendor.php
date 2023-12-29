<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use Faker\Factory;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use VaahCms\Modules\Store\Models\Vendor;
use VaahCms\Modules\Store\Models\Product;
use WebReinvent\VaahCms\Models\TaxonomyType;

class ProductVendor extends Model
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
        return $this->belongsToMany(Store::class, 'vh_st_vendor_pro_stores', 'vh_st_vendor_product_id', 'vh_st_store_id');
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
    public function addedByUser()
    {
        return $this->hasOne(User::class,'id','added_by');
    }
    //-------------------------------------------------
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'vh_st_product_id')->select('id', 'name', 'slug');
    }
    //-------------------------------------------------
    public function stores()
    {
        return $this->hasMany(Store::class, 'id', 'vh_st_store_id')->select('id', 'name', 'slug');
    }

    //----------------------------------------------------------------------------------
    public function vendor()
    {
        return $this->hasOne(Vendor::class,'id','vh_st_vendor_id')->select('id','name', 'slug','is_default');
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
    public static function createProductPrice($request){

        $inputs = $request->all();

        $validation = self::validationProductPrice($inputs);
        if (!$validation['success']) {
            return $validation;
        }
        $check = ProductPrice::where(['vh_st_vendor_id'=>$inputs['vh_st_vendor_id'],'vh_st_product_id'=>$inputs['vh_st_product_id']])->first();

        if($check){
            $check->fill($inputs);
            $check->vh_st_product_variation_id = $inputs['product_variation']['id'];
            $check->is_active = $inputs['is_active_product_price'];
            $check->amount = $inputs['amount'];
            $check->save();
            $response['messages'][] = 'Updated successfully.';
            return $response;
        }
        $order_item = new ProductPrice;
        $order_item->fill($inputs);
        $order_item->vh_st_product_variation_id = $inputs['product_variation']['id'];
        $order_item->is_active = $inputs['is_active_product_price'];
        $order_item->save();
        $response['messages'][] = 'Saved successfully.';
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
                $response['success'] = false;
                $response['messages'][] = "This vendor and product (" . $inputs['product']['name'] . ") is already exist.";
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
            $response['messages'][] = 'Saved successfully.';
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
        $search = $filter['q'];
        $query->where(function ($q) use ($search) {
            $q->where('id', 'LIKE', '%' . $search . '%')
                ->orWhereHas('vendor', function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('slug', 'LIKE', '%' . $search . '%');
                });
        });

    }
    //-------------------------------------------------
    public function scopeProductVendorFilter($query, $filter)
    {

        if (!isset($filter['product_vendor_status'])) {
            return $query;
        }
        $search = $filter['product_vendor_status'];
        $query->whereHas('status', function ($q) use ($search) {
            $q->whereIn('name', $search);
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
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('product','vendor','addedByUser','status','stores');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->productVendorFilter($request->filter);
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

        // delete value from pivot table
        $item_id = collect($inputs['items'])->pluck('id')->toArray();
        foreach ($item_id as $key => $value) {
            $item = self::find($value);
            $item->storeVendorProduct()->detach();
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        self::whereIn('id', $items_id)->forceDelete();

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
                $list->update(['is_active' => null,]);
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
                $details = ProductVendor::with('storeVendorProduct')->get();

                foreach ($details as $item) {
                    $item->storeVendorProduct()->detach();
                }

                ProductVendor::whereIn('id', $details->pluck('id'))->forceDelete();
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','product','vendor',
                'addedByUser','status','stores','storeVendorProduct'])
            ->withTrashed()
            ->first();
        $itemProduct = Product::where('id',$item->vh_st_product_id)->first();
        $item['productList'] = Product::where('vh_st_store_id',$itemProduct->vh_st_store_id)->select('id','name','slug');
        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }

        //To get data for dropdown of product price
        $array_item = $item->toArray();
        $check = ProductPrice::where('vh_st_vendor_id',$array_item['vh_st_vendor_id'])
            ->where('vh_st_product_id',$array_item['vh_st_product_id'])->first();
        if($check){
            $item['product_variation'] = ProductVariation::where('id',$check['vh_st_product_variation_id'])
                ->get(['id','name','slug','is_default'])->toArray()[0];
            $item['is_active_product_price'] = $check['is_active'];
            $item['amount'] = $check['amount'];
        }else{
            $item['is_active_product_price'] = 1;
        }

        $item->storeVendorProduct->each(function ($store_vendor) {
            unset($store_vendor->pivot);
        });

        $response['success'] = true;
        $response['data'] = $item;
        return $response;

    }
    //-------------------------------------------------
    public static function searchVendor(Request $request): array
    {

        $user = Auth::user();

        $query = $request->input('filter.q.query');
        $vendor = Vendor::where('created_by', $user->id)
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
                $response['errors'][] = "This vendor and product (" . $inputs['product']['name'] . ") is already exist.";
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

        // Detach the record from the storeVendorProduct relationship
        $item->storeVendorProduct()->detach();

        $item->forceDelete();

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
        $rules = validator($inputs, [
            'vh_st_vendor_id'=> 'required',
            'store_vendor_product' => 'required',
            'vh_st_product_id'=> 'required',
            'taxonomy_id_product_vendor_status'=> 'required',
            'added_by'=> 'required|max:150',
            'can_update'=> 'required|max:150',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:250'
                             ],
            ],
        [
            'vh_st_vendor_id.required' => 'The Vendor field is required',
            'store_vendor_product.required' => 'The Store field is required',
            'vh_st_product_id.required' => 'The Product field is required',
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
            'product_variation' => 'required|max:150',
            'amount' => 'nullable|numeric|min:0|digits_between:1,15',
        ], [
            'amount.digits_between' => 'The amount must be between 1 to 15 digits',
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
    public static function deleteVendors($items_id){
        if($items_id){
            self::whereIn('vh_st_vendor_id',$items_id)->forcedelete();
            $response['success'] = true;
            $response['data'] = true;
        }else{
            $response['error'] = true;
            $response['data'] = false;
        }

    }
    //-------------------------------------------------
    public static function deleteProduct($items_id){

        if($items_id){
            self::where('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
            $response['data'] = true;
        }else{
            $response['error'] = true;
            $response['data'] = false;
        }

    }
    //-------------------------------------------------
    public static function deleteProducts($items_id){
        if($items_id){
            self::whereIn('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
            $response['data'] = true;
        }else{
            $response['error'] = true;
            $response['data'] = false;
        }

    }

}
