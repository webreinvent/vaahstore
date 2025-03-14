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
use function PHPUnit\Framework\isEmpty;

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
        'vh_st_vendor_id','vh_st_product_id',
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

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $validation_result = self::validation($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }

        $inputs = $request->all();

        // check if stock already exist for this variation

        $conditions = [
            ['vh_st_vendor_id',$inputs['vh_st_vendor_id']],
            ['vh_st_product_variation_id',$inputs['vh_st_product_variation_id']],
        ];
        $item = self::where($conditions)->withTrashed()->first();

        if ($item) {
            $error_message = "Product Stock already exists for this variation".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }


        $item = new self();
        $item->fill($inputs);
        $item->save();

        //update quantity in product variation
        $product_variation = ProductVariation::where('id', $inputs['vh_st_product_variation_id'])
            ->withTrashed()->first();
        $product_variation->quantity += $inputs['quantity'];

        if ($product_variation->quantity < 10) {
            $product_variation->is_quantity_low = 1;
            $product_variation->is_mail_sent = 1;
            $product_variation->low_stock_at = now('Asia/Kolkata');
        } else {
            $product_variation->is_quantity_low = 0;
            $product_variation->is_mail_sent = 0;
            $product_variation->low_stock_at = null;
        }

        $product_variation->save();

        //update quantity in product
        $product = Product::where('id', $inputs['vh_st_product_id'])->withTrashed()->first();

        $product->quantity = $product->productVariations->sum('quantity');
        $product->save();

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;

    }

    //-------------------------------------------------
    public static function validation($requestData){

        $validated_data = validator($requestData, [
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
            [
                'vendor.required' => 'The Vendor field is required',
                'product.required' => 'The Product field is required',
                'product_variation' => 'The Product Variation field is required',
                'quantity' => 'The Quantity field is required',
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
        $search = $filter['q'];

        $query->where(function ($q) use ($search) {
            $q->Where('id', 'LIKE', '%' . $search . '%');

        });

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
    public function scopeStockFilter($query, $filter)
    {
        if (!isset($filter['stocks']) || is_null($filter['stocks']) || $filter['stocks'] === 'null') {
            return $query;
        }

        $stock_statuses = is_array($filter['stocks']) ? $filter['stocks'] : [$filter['stocks']];

        foreach ($stock_statuses as $status) {
            if ($status === 'low') {
                $query->orWhere(function ($query) {
                    $query->where('quantity', '>=', 1)
                        ->where('quantity', '<', 10);
                });
            } elseif ($status === 'high') {
                $query->orWhere('quantity', '>', 10);
            }
        }

        return $query;
    }

    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('status','product','productVariation','vendor');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->vendorsFilter($request->filter);
        $list->productsFilter($request->filter);
        $list->variationsFilter($request->filter);
        $list->warehousesFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->dateFilter($request->filter);
        $list->quantityFilter($request->filter);
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

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
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
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }

    //-------------------------------------------------
    public static function deleteList($request): array
    {
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
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
        foreach($items_id as $item_id)
        {
            self::updateStock($item_id);
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
        if (!\Auth::user()->hasPermission($permission_slug)) {
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
                }
                break;
            case 'delete':
                if(isset($items_id) && count($items_id) > 0) {
                    foreach ($items_id as $item_id) {
                        self::updateStock($item_id);
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
                $list->update(['deleted_by'  => auth()->user()->id]);
                $list->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->update(['deleted_by'  => null]);
                $list->restore();
                break;
            case 'delete-all':
                $item_ids = self::withTrashed()->pluck('id')->toArray();
                foreach($item_ids as $item_id)
                {
                    self::updateStock($item_id);
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

        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','status','vendor','product','productVariation','warehouse'])
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
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        $validation_result = self::validation($request->all());

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

        $is_exist = self::where($conditions)
            ->whereNot('id',$item->id)
            ->withTrashed()->first();

        if ($is_exist) {
            $error_message = "Product Stock already exists for this variation".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        $difference_in_quantity = $inputs['quantity'] - $item->quantity;
        // calculate difference between new and old quantity
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        //update the quantity in variation table
        $product_variation = ProductVariation::where('id', $inputs['vh_st_product_variation_id'])
            ->withTrashed()->first();

        $old_quantity = $product_variation->quantity;
        $product_variation->quantity += $difference_in_quantity;
        $send_mail = false;

        if ($product_variation->quantity < 10) {
            $product_variation->is_quantity_low = 1;
            $product_variation->is_mail_sent = 1;
            $product_variation->low_stock_at = now('Asia/Kolkata');
            if ($old_quantity >= 10) {
                $send_mail = true;
            }
        } else {
            $product_variation->is_quantity_low = 0;
            $product_variation->is_mail_sent = 0;
            $product_variation->low_stock_at = null;
        }

        $product_variation->save();

        //update the quantity of products
        $product = Product::where('id', $inputs['vh_st_product_id'])->withTrashed()->first();

        $product->quantity = $product->productVariations->sum('quantity');
        $product->save();
        if ($send_mail) {
            ProductVariation::sendMailForStock();
        }
        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }

        self::updateStock($id);
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
        if (!\Auth::user()->hasPermission($permission_slug)) {
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


        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $i = 0;

        while($i < $records)
        {
            $inputs = self::fillItem(false);

            $item =  new self();
            $item->fill($inputs);
            $item->save();

            $product_variation = ProductVariation::where('id', $inputs['vh_st_product_variation_id'])
                ->withTrashed()->first();
if ($product_variation) {
    $product_variation->quantity += $inputs['quantity'];
    $product_variation->save();

    $product = Product::where('id', $inputs['vh_st_product_id'])->withTrashed()->first();

    $product->quantity = $product->productVariations->sum('quantity');
    $product->save();
}



            $i++;

        }

    }


    //-------------------------------------------------
    public static function fillItem($is_response_return = true)
    {
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
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
        $vendor = Vendor::whereHas('productVendors.product.productVariations') // Ensure vendor has related products with variations
        ->where('is_active', 1)
            ->with('productVendors.product.productVariations')
            ->inRandomOrder()
            ->select('id', 'name', 'slug', 'is_default')
            ->first();

        $inputs['vh_st_vendor_id'] = null;
        $inputs['vendor'] = null;
        if (!empty($vendor)) {
            $inputs['vh_st_vendor_id'] = $vendor->id;
            $inputs['vendor'] = $vendor;
        }


        if ($vendor && $vendor->productVendors->isNotEmpty()) {
            // Get a random productVendor with a product that has variations
            $validProductVendors = $vendor->productVendors->filter(function ($productVendor) {
                return $productVendor->product && $productVendor->product->productVariations->isNotEmpty();
            });

            if ($validProductVendors->isNotEmpty()) {
                $product_vendor = $validProductVendors->random(); // Get a random productVendor from filtered list

                $product_id = $product_vendor->product->id; // Get product ID
                $product = $product_vendor->product; // Get product data

                $inputs['vh_st_product_id'] = $product_id;
                $inputs['product'] = $product;

                $product_variation = $product->productVariations->random();

                $inputs['vh_st_product_variation_id'] = $product_variation->id;
                $inputs['product_variation'] = $product_variation;
            }
        }

        $warehouse = Warehouse::where('is_active', 1)
            ->where('vh_st_vendor_id', $inputs['vh_st_vendor_id'])
            ->inRandomOrder()
            ->select('id', 'name', 'slug')
            ->first();
        if (empty($warehouse)) {
            $warehouse = Warehouse::where('is_active', 1)
                ->inRandomOrder()
                ->select('id', 'name', 'slug')
                ->first();
        }
        $inputs['vh_st_warehouse_id'] = $warehouse->id ?? null;
        $inputs['warehouse'] = $warehouse ?? null;

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

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        if($items_id){
            self::whereIn('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }
    //-------------------------------------------------

    public static function deleteProduct($items_id){

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        if($items_id){
            self::where('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

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
        $vendor_id = $request->input('vendor_id');
        $products = Product::whereHas('productVendors', function ($query) use ($vendor_id) {
            $query->where('vh_st_vendor_id', $vendor_id);
        })
            ->select('id', 'name', 'slug', 'is_default')
            ->where('is_active', 1);
        if ($request->has('search.query')) {
            $products->where('name', 'LIKE', '%' .$request->input('search')['query'] . '%');
        }

        $products = $products->take(10)->get();

        $response['success'] = true;
        $response['data'] = $products;
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
            ->withTrashed()
            ->select(['id','name','slug','deleted_at']);
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
            ->withTrashed()
            ->select(['id','name','slug','deleted_at'])->withTrashed();
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
            ->withTrashed()
            ->select(['id','name','slug','deleted_at']);
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

    public static function getVendorBySlug($request)
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

    public static function getProductBySlug($request)
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

    public static function getVariationBySlug($request)
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

    public static function getWarehouseBySlug($request)
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

    public static function updateStock($id)
    {
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }
        $item = self::where('id',$id)->withTrashed()->first();
        $product_variation = ProductVariation::where('id', $item->vh_st_product_variation_id)
            ->withTrashed()->first();
        if ($product_variation) {
            if ($product_variation->quantity) {
                $product_variation->quantity -= $item->quantity;
            }

            if ($product_variation->quantity < 10) {
                $product_variation->is_quantity_low = 1;
                $product_variation->is_mail_sent = 1;
                $product_variation->low_stock_at = now('Asia/Kolkata');
            } else {
                $product_variation->is_quantity_low = 0;
                $product_variation->is_mail_sent = 0;
                $product_variation->low_stock_at = null;
            }

            $product_variation->save();

            $product = Product::where('id', $item->vh_st_product_id)->withTrashed()->first();
            $product->quantity = $product->productVariations->sum('quantity');
            $product->save();
        }
    }

    //-------------------------------------------------

    public static function scopeStatusFilter($query, $filter)
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
            $query->whereIn('name', $status)
                ->orWhereIn('slug',$status);
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
    public static function searchVariations($request){

        $product_variations = ProductVariation::select('id', 'name','slug','is_default')->where('is_active',1);
        if ($request->has('query') && $request->input('query')) {
            $product_variations->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $product_variations = $product_variations->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $product_variations;
        return $response;

    }

    //-------------------------------------------------
    public static function searchWarehouses($request){

        $warehouses = Warehouse::select('id', 'name','slug')->where('is_active',1);
        if ($request->has('query') && $request->input('query')) {
            $warehouses->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $warehouses = $warehouses->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $warehouses;
        return $response;

    }
    //-------------------------------------------------
    public static function defaultVendor($request)
    {
        $default_vendor = Vendor::where(['is_active'=>1,'deleted_at'=>null,'is_default'=>1])->first(['id', 'name', 'slug']);


        if($default_vendor)
        {
            $response['success'] = true;
            $response['data'] = $default_vendor;
        }
        else {
            $response['success'] = false;
            $response['data'] = null;
        }
        return $response;
    }

    //-------------------------------------------------


    public static function getStocksChartData($request)
    {
        $start_date = isset($request->start_date) ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
        $end_date = isset($request->end_date) ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $highest_stocks = self::where('quantity', '>', 10)
            ->whereBetween('updated_at', [$start_date, $end_date])
            ->orderBy('quantity', 'desc')
            ->take(1)
            ->with(['product','product.medias', 'productVariation', 'vendor', 'productVariation.medias'])
            ->get(['id', 'quantity', 'vh_st_product_id', 'vh_st_vendor_id', 'vh_st_product_variation_id']);

        $lowest_stocks = self::whereBetween('quantity', [0, 10])
            ->whereBetween('updated_at', [$start_date, $end_date])
            ->orderBy('quantity', 'asc')
            ->take(1)
            ->with(['product','product.medias', 'productVariation', 'vendor', 'productVariation.medias'])
            ->get(['id', 'quantity', 'vh_st_product_id', 'vh_st_vendor_id', 'vh_st_product_variation_id']);

        $all_stocks = self::whereBetween('updated_at', [$start_date, $end_date])->sum('quantity');

        $map_stocks = function ($stocks) use ($all_stocks) {

            return $stocks->map(function ($stock) use ($all_stocks) {
                $product_variation = $stock->productVariation;

                $product_media_ids = $product_variation->medias->pluck('pivot.vh_st_product_media_id');

                if($product_media_ids->isEmpty()){
                    $product_media_ids = ProductMedia::where('vh_st_product_id', $stock->product->id)
                        ->pluck('id');
                }

                $image_urls = self::getImageUrls($product_media_ids);

                $stock_percentage = $all_stocks > 0 ? ($stock->quantity / $all_stocks) * 100 : 0;

                return (object)[
                    'id' => $stock->id,
                    'stock' => $stock->quantity,
                    'vendor' => $stock->vendor,
                    'product' => $stock->product,
                    'productVariation' => $stock->productVariation,
                    'image_urls' => $image_urls,
                    'stock_percentage' => round($stock_percentage, 2),
                ];
            });
        };

        $top_stocks = $map_stocks($highest_stocks)->toArray();
        $lowest_stocks_data = $map_stocks($lowest_stocks)->toArray();

        $highest_stock_quantity = $highest_stocks->first()->quantity ?? 0;
        $lowest_stock_quantity = $lowest_stocks->first()->quantity ?? 0;

        $highest_stock_percentage = $all_stocks > 0 ? ($highest_stock_quantity / $all_stocks) * 100 : 0;
        $lowest_stock_percentage = $all_stocks > 0 ? ($lowest_stock_quantity / $all_stocks) * 100 : 0;

        return [
            'data' => [
                'top_stocks' => $top_stocks,
                'lowest_stocks' => $lowest_stocks_data,
                'all_stocks' => $all_stocks,
                'highest_stock_percentage' => round($highest_stock_percentage, 2),
                'lowest_stock_percentage' => round($lowest_stock_percentage, 2),
            ],
        ];
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
}
