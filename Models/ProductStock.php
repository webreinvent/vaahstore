<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use Faker\Factory;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use VaahCms\Modules\Store\Models\Vendor;
use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\Warehouse;

class ProductStock extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_product_stocks';
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
        'slug','vh_st_vendor_id','vh_st_product_id',
        'vh_st_product_variation_id','vh_st_warehouse_id',
        'quantity','taxonomy_id_product_stock_status','status_notes',
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
    public function status(){
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id_product_stock_status')
            ->select(['id','name', 'slug']);
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
    public static function createItem($request)
    {

        $validation_result = self::productStockInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }

        $inputs = $request->all();

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

        // check if stock already exist for this variation

        $conditions = [
            ['vh_st_vendor_id',$inputs['vh_st_vendor_id']],
            ['vh_st_product_variation_id',$inputs['vh_st_product_variation_id']],
        ];
        $is_stock_exist = self::where($conditions)->withTrashed()->first();

        if ($is_stock_exist) {
            $response = [];
            $response['errors'][] = 'Product Stock already exist for this variation.';
            return $response;
        }

        $item = new self();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        $product_variation = ProductVariation::find($inputs['vh_st_product_variation_id']);
        $product_variation->quantity += $inputs['quantity'];
        $product_variation->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }

    //-------------------------------------------------
    public static function productStockInputValidator($requestData){

        $validated_data = validator($requestData, [
            'name' => 'required|max:100',
            'slug' => 'required|max:100',
            'vendor' => 'required',
            'product' => 'required',
            'product_variation' => 'required',
            'vh_st_warehouse_id' => 'required',
            'quantity' => 'required',
            'status' => 'required',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:100'
            ],
            'is_active' => 'required',
        ],
            [    'name.required' => 'The Name field is required',
                'name.max' => 'The Name field cannot be greater than :max characters',
                'slug.required' => 'The Slug field is required',
                'slug.max' => 'The Slug field cannot be greater than :max characters',
                'vendor.required' => 'The Vendor field is required',
                'product.required' => 'The Product field is required',
                'product_variation' => 'The Product Variation field is required',
                'vh_st_warehouse_id' => 'The Warehouse field is required',
                'vh_st_warehouse_id.required' => 'The Warehouse field is required',
                'status.required' => 'The Status field is required',
                'status_notes.*' => 'The Status notes field is required for "Rejected" Status',
                'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
            ]
        );

        if($validated_data->fails()){
            return [
                'success' => false,
                'errors' => $validated_data->errors()->all()
            ];
        }

        $validated_data = $validated_data->validated();

        return [
            'success' => true,
            'data' => $validated_data
        ];

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
    public function scopeProductStockFilter($query, $filter)
    {

        if(!isset($filter['product_stock_status']))
        {
            return $query;
        }
        $search = $filter['product_stock_status'];
        $query->whereHas('status',function ($q) use ($search) {
            $q->whereIn('name',$search);
        });

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('status');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->vendorsFilter($request->filter);
        $list->productsFilter($request->filter);
        $list->variationsFilter($request->filter);
        $list->warehousesFilter($request->filter);
        $list->stockFilter($request->filter);
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
                foreach ($items_id as $item_id)
                {
                    self::updateProductVariationAfterTrash($item_id);
                }
                break;
            case 'restore':
                self::whereIn('id', $items_id)->restore();
                $items->update(['deleted_by' => null]);
                foreach ($items_id as $item_id)
                {
                    self::updateProductVariationAfterRestore($item_id);
                }
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
                    foreach ($items_id as $item_id) {
                        self::updateProductVariationAfterTrash($item_id);
                    }
                }
                break;
            case 'restore':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->restore();
                    foreach ($items_id as $item_id) {
                        self::updateProductVariationAfterRestore($item_id);
                    }
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
                $list->update(['is_active' => null]);
                break;
            case 'trash-all':
                $list->update(['deleted_by'  => auth()->user()->id]);
                $item_list = self::query();
                $item_ids = $item_list->pluck('id')->toArray();
                foreach($item_ids as $item_id)
                    {
                        self::updateProductVariationAfterTrash($item_id);
                    }
                $list->delete();

                break;
            case 'restore-all':
                $list->update(['deleted_by'  => null]);
                $list->restore();
                $item_list = self::query();
                $item_ids = $item_list->pluck('id')->toArray();
                foreach($item_ids as $item_id)
                {
                    self::updateProductVariationAfterRestore($item_id);
                }
                break;
            case 'delete-all':
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
        $response['messages'][] = 'Action was successful.';

        return $response;
    }
    //-------------------------------------------------
    public static function getItem($id)
    {

        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','status','vendor','product','productVariation','warehouse'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }


        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }
    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $validation_result = self::productStockInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }

        $inputs = $request->all();

        $item = self::where('id', $id)->withTrashed()->first();

        //check stock we are adding for the variation is unique
        $conditions = [
            ['vh_st_vendor_id',$inputs['vh_st_vendor_id']],
            ['vh_st_product_variation_id',$inputs['vh_st_product_variation_id']],
        ];
        $is_stock_exist = self::where($conditions)
            ->whereNot('id',$item->id)
            ->withTrashed()->first();
        if ($is_stock_exist) {
            $response = [];
            $response['errors'][] = 'Product Stock already exist for this variation.';
            return $response;
        }


        // calculate difference between new and old quantity
        $difference_in_quantity =$inputs['quantity'] - $item->quantity;
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        //update the quantity in variation table also
        $product_variation = ProductVariation::find($inputs['vh_st_product_variation_id']);
        $product_variation->quantity += $difference_in_quantity;
        $product_variation->save();


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
                self::updateProductVariationAfterTrash($id);
                break;

            case 'restore':
                self::where('id', $id)
                    ->withTrashed()
                    ->restore();
                    $item = self::where('id',$id)->withTrashed()->first();
                    $item->deleted_by = null;
                    $item->save();
                    self::updateProductVariationAfterRestore($id);
                break;
        }

        return self::getItem($id);
    }
    //-------------------------------------------------

    public static function validation($inputs)
    {

        $rules = array(
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
        );

        $validator = \Validator::make($inputs, $rules);
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

            //fill the Vendor field here
           $vendor_id = Vendor::where('is_active', 1)->inRandomOrder()->value('id');
           $vendor_id_data = Vendor::where('is_active',1)->where('id',$vendor_id)->first();
           $inputs['vh_st_vendor_id'] =$vendor_id;
           $inputs['vendor'] = $vendor_id_data;

            //fill the product field here
           $product_id = Product::where('is_active', 1)->inRandomOrder()->value('id');
           $product_id_data = Product::where('is_active',1)->where('id',$product_id)->first();
           $inputs['vh_st_product_id'] =$product_id;
           $inputs['product'] = $product_id_data;

            //fill the product variation field on the basis of product selected

            $inputs['vh_st_product_variation_id'] = ProductVariation::where('is_active', 1)
                ->where('vh_st_product_id',$inputs['vh_st_product_id'])
                ->inRandomOrder()->value('id');

           $inputs['product_variation'] = ProductVariation::where('is_active',1)
               ->where('id',$inputs['vh_st_product_variation_id'])->first();

            //fill the Brand field on the basis of product selected

            $brands = Brand::where('is_active',1);
            $brand_ids = $brands->pluck('id')->toArray();
            $brand_id = $brand_ids[array_rand($brand_ids)];
            $brand = $brands->where('id',$brand_id)->first();
            $inputs['brand'] = $brand;
            $inputs['vh_st_brand_id'] = $brand_id;


            //fill the warehouse field on the basis of warehouse selected

           $warehouse_id = Warehouse::where('is_active', 1)
                  ->where('vh_st_vendor_id',$inputs['vh_st_vendor_id'])
                  ->inRandomOrder()->value('id');

           $warehouse_id_data = Warehouse::where('is_active',1)->where('id',$warehouse_id)->first();
           $inputs['vh_st_warehouse_id'] =$warehouse_id;
           $inputs['warehouse'] = $warehouse_id_data;

           $taxonomy_status = Taxonomy::getTaxonomyByType('product-stock-status');
           $status_id = $taxonomy_status->pluck('id')->random();
           $status = $taxonomy_status->where('id',$status_id)->first();
           $inputs['taxonomy_id_product_stock_status'] = $status_id;
           $inputs['status']=$status;

           $inputs['quantity'] = rand(1,5000);

         $faker = Factory::create();

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
    public static function searchVendor($request){

        $vendor = Vendor::select('id', 'name','slug','is_default')->where('is_active',1);
        if ($request->has('query') && $request->input('query')) {
            $vendor->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $vendor = $vendor->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $vendor;
        return $response;

    }
    //-------------------------------------------------
    public static function searchProduct($request){

        $product = Product::select('id', 'name','slug','is_default')->where('is_active',1);
        if ($request->has('query') && $request->input('query')) {
            $product->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $product = $product->limit(10)->get();

        $response['success'] = true;
        $response['data'] = $product;
        return $response;

    }
    //-------------------------------------------------
    public static function searchProductVariation($request){

        $product_id = $request->input('product_id');

        $product_variations = ProductVariation::select('id', 'name', 'slug', 'is_default', 'vh_st_product_id')
            ->where('is_active', 1)
            ->where('vh_st_product_id', $product_id);

        if ($request->has('search.query')) {
            $product_variations->where('name', 'LIKE', '%' .$request->input('search')['query'] . '%');
        }
        $product_variations = $product_variations->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $product_variations;
        return $response;

    }
    //-------------------------------------------------
    public static function searchWarehouse($request){

        $vendor_id = $request->input('vendor_id');
        $warehouse = Warehouse::select('id', 'name','slug','vh_st_vendor_id')
            ->where('is_active',1)
            ->where('vh_st_vendor_id',$vendor_id);

        if ($request->has('search.query')){
            $warehouse->where('name', 'LIKE', '%' . $request->input('search')['query'] . '%');
        }
        $warehouse = $warehouse->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $warehouse;
        return $response;

    }
    //-------------------------------------------------
    public function scopeVendorsFilter($query, $filter)
    {

        if(!isset($filter['vendors'])
            || is_null($filter['vendors'])
            || $filter['vendors'] === 'null'
        )
        {
            return $query;
        }

        $vendors = $filter['vendors'];

        $query->whereHas('vendor', function ($query) use ($vendors) {
            $query->whereIn('slug', $vendors);
        });

    }

    //-------------------------------------------------

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vh_st_vendor_id', 'id')
            ->select(['id','name','slug']);
    }

    //-------------------------------------------------

    public function scopeProductsFilter($query, $filter)
    {

        if(!isset($filter['products'])
            || is_null($filter['products'])
            || $filter['products'] === 'null'
        )
        {
            return $query;
        }

        $products = $filter['products'];

        $query->whereHas('product', function ($query) use ($products) {
            $query->whereIn('slug', $products);
        });

    }

    //-------------------------------------------------
    public function product(){
        return $this->belongsTo(Product::class, 'vh_st_product_id', 'id')
            ->select(['id','name','slug']);
    }

    //-------------------------------------------------

    public function scopeVariationsFilter($query, $filter)
    {

        if(!isset($filter['variations'])
            || is_null($filter['variations'])
            || $filter['variations'] === 'null'
        )
        {
            return $query;
        }

        $variations = $filter['variations'];

        $query->whereHas('productVariation', function ($query) use ($variations) {
            $query->whereIn('slug', $variations);
        });

    }

    //-------------------------------------------------

    public function productVariation(){
        return $this->belongsTo(ProductVariation::class, 'vh_st_product_variation_id', 'id')
            ->select(['id','name','slug']);
    }

    //-------------------------------------------------

    public function warehouse(){
        return $this->belongsTo(Warehouse::class, 'vh_st_warehouse_id', 'id')
            ->select(['id','name','slug']);
    }

    //-------------------------------------------------


    public function scopeWarehousesFilter($query, $filter)
    {

        if(!isset($filter['warehouses'])
            || is_null($filter['warehouses'])
            || $filter['warehouses'] === 'null'
        )
        {
            return $query;
        }

        $warehouses = $filter['warehouses'];

        $query->whereHas('warehouse', function ($query) use ($warehouses) {
            $query->whereIn('slug', $warehouses);
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

    public static function searchProductUsingUrlSlug($request)
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

    public static function searchWarehouseUsingUrlSlug($request)
    {

        $query = $request['filter']['warehouse'];
        $warehouses = Warehouse::whereIn('name',$query)
            ->orWhereIn('slug',$query)
            ->select('id','name','slug')->get();
        $response['success'] = true;
        $response['data'] = $warehouses;
        return $response;
    }

    //-------------------------------------------------

    public function scopeStockFilter($query, $filter)
    {
        if (!isset($filter['in_stock']) || is_null($filter['in_stock']) || $filter['in_stock'] === 'null') {
            return $query;
        }

        $in_stock = $filter['in_stock'];

        if ($in_stock === 'false') {
            $query->where(function ($query) {
                $query->Where('quantity',0);
            });
        } elseif ($in_stock === 'true') {
            $query->where('quantity', '>=',1);
        }

        return $query;
    }

    //-------------------------------------------------

    public static function updateProductVariationAfterTrash($id)
    {
        $item = self::where('id',$id)->withTrashed()->first();

        $product_variation = ProductVariation::find($item->vh_st_product_variation_id);
        $product_variation->quantity -= $item->quantity;
        $product_variation->save();
    }

    //-------------------------------------------------

    public static function updateProductVariationAfterRestore($id)
    {
        $item = self::where('id',$id)->withTrashed()->first();

        $product_variation = ProductVariation::find($item->vh_st_product_variation_id);
        $product_variation->quantity += $item->quantity;
        $product_variation->save();
    }

}
