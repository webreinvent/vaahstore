<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
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
        return $this->belongsTo(Product::class,'vh_st_product_id','id')
            ->select('id','name','slug');
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }


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

        /*if($inputs['in_stock'] == 1)
        {
            if($inputs['quantity'] < 1)
            {
                $response['success'] = false;
                $response['messages'][] = "Please Enter Quantity.";
                return $response;
            }
        }*/

        // handle if current record is default
        if($inputs['is_default']){
            self::where('is_default',1)->update(['is_default' => 0]);
        }

        $item = new self();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

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


        if(!isset($filter['status'])
            || is_null($filter['status'])
            || $filter['status'] === 'null'
        )
        {
            return $query;
        }

        $status = $filter['status'];

        $query->whereHas('status', function ($query) use ($status) {
            $query->where('name', $status)
                ->orWhere('slug',$status);
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
            $query->where(function ($query) {
                $query->where('in_stock', false)->orWhere('quantity', 0);
            });
        } elseif ($in_stock === 'true') {
            $query->where('in_stock', true)->where('quantity', '>', 1);
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

        $minQuantity = $filter['quantity'][0];
        $maxQuantity = $filter['quantity'][1];

        return $query->whereBetween('quantity', [$minQuantity, $maxQuantity]);
    }


    //-------------------------------------------------


    public function scopeProductFilter($query, $filter)
    {
        if(!isset($filter['product'])
            || is_null($filter['product'])
            || $filter['product'] === 'null'
        )
        {
            return $query;
        }

        $store = $filter['product'];

        $query->whereHas('product', function ($query) use ($store) {
            $query->where('slug', $store);

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
        $list = self::getSorted($request->filter)->with('status','product');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->stockFilter($request->filter);
        $list->defaultFilter($request->filter);
        $list->dateRangeFilter($request->filter);
        $list->quantityFilter($request->filter);
        $list->productFilter($request->filter);
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }

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
        $response['messages'][] = 'Action was successful.';

        return $response;
    }

    //-------------------------------------------------
    public static function deleteList($request): array
    {
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }

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
        ProductMedia::deleteProductVariations($items_id);
        ProductPrice::deleteProductVariations($items_id);
        ProductAttribute::deleteProductVariations($items_id);
        self::whereIn('id', $items_id)->forceDelete();
        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = 'Action was successful.';

        return $response;
    }
    //-------------------------------------------------
    public static function listAction($request, $type): array
    {
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
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
                    self::whereIn('id', $items_id)->forceDelete();
                    ProductMedia::deleteProductVariations($items_id);
                    ProductPrice::deleteProductVariations($items_id);
                    ProductAttribute::deleteProductVariations($items_id);
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
                $items_id = self::all()->pluck('id')->toArray();
                self::withTrashed()->forceDelete();
                ProductMedia::deleteProductVariations($items_id);
                ProductPrice::deleteProductVariations($items_id);
                ProductAttribute::deleteProductVariations($items_id);

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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','status','product'])
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
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
            $response['success'] = false;
            $response['errors'][] = "This name is already exist.";
            return $response;
        }

        // check if slug exist
        $item = self::where('id', '!=', $id)
            ->withTrashed()
            ->where('slug', $inputs['slug'])->first();

        if ($item) {
            $response['success'] = false;
            $response['errors'][] = "This slug is already exist.";
            return $response;
        }

        // handle if current record is default
        if($inputs['is_default'] == 1 || $inputs['is_default'] == true){
            self::where('is_default',1)->update(['is_default' => 0]);
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }

        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = 'Record does not exist.';
            return $response;
        }
        ProductMedia::deleteProductVariation($item->id);
        ProductPrice::deleteProductVariation($item->id);
        ProductAttribute::deleteProductVariation($item->id);
        $item->forceDelete();
        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Record has been deleted';

        return $response;
    }
    //-------------------------------------------------
    public static function itemAction($request, $id, $type): array
    {
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
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
            'price' => 'required',

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
                'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
                'quantity.digits_between' => 'The quantity field must not be greater than 9 digits',
                'description.max' => 'The Description field may not be greater than :max characters.',
                'price.required'=>'The Price field is required.',


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
        if($items_id){
            self::whereIn('vh_st_product_id',$items_id)->forcedelete();
            $response['success'] = true;
            $response['data'] = true;
        }else{
            $response['error'] = true;
            $response['data'] = false;
        }

    }

    //------------------------------------------------

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
        $product_id = $product_ids[array_rand($product_ids)];
        $product = Product::where(['is_active' => 1, 'id' => $product_id])->first();
        $inputs['vh_st_product_id'] = $product_id;
        $inputs['product'] = $product;

        $inputs['price'] = rand(1,100000);
        $inputs['is_active'] = rand(0,1);
        $inputs['is_default'] = 0;
        $taxonomy_status = Taxonomy::getTaxonomyByType('product-variation-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        $status_id = $status_ids[array_rand($status_ids)];

        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['taxonomy_id_variation_status'] = $status_id;
        $inputs['status']=$status;
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

    //-------------------------------------------------

    public static function sendMailForStock()
    {

        try{


            $list_data = ProductVariation::all();
            $name_data = $list_data->pluck('name')->toArray();
            $quantities = $list_data->pluck('quantity')->toArray();

            $filteredData = array_filter(array_combine($name_data, $quantities), function ($quantity) {
                return $quantity == 0 || $quantity < 10;
            });

            $filteredNames = array_keys($filteredData);


            $email_data = array_map(function ($name) {
                $name_without_spaces = str_replace(' ', '', $name);
                return $name_without_spaces . '@gmail.com';
            }, $filteredNames);


            if (!empty($filteredNames)) {
                $subject = 'Low Stock Alert';

                $message = 'Hello Everyone, the following items are low in stock:' . PHP_EOL;
                foreach ($filteredNames as $index => $name) {
                    $message .= ($index + 1) . '. <strong>' . $name . '</strong>' . PHP_EOL;
                }

                $send_mail = UserBase::notifySuperAdmins($subject, $message);
            }

            $response['success'] = true;


        } catch (\Exception $e) {
            $response = [];
            $response['success'] = false;

            if(env('APP_DEBUG')){
                $response['errors'][] = $e->getMessage();
                $response['hint'][] = $e->getTrace();
            } else {
                $response['errors'][] = 'Something went wrong.';
            }
        }

        return $response;

    }

}
