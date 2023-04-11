<?php namespace VaahCms\Modules\Store\Models;

use DateTimeInterface;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Entities\User;

class Product extends Model
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

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
        'taxonomy_id_product_type',
        'vh_st_store_id',
        'vh_st_brand_id', 'vh_cms_content_form_field_id',
        'quantity', 'in_stock', 'is_active',
        'taxonomy_id_product_status', 'status_notes', 'meta',
        'created_by',
        'updated_by',
        'deleted_by',
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
    public function brand()
    {
        return $this->hasOne(Brand::class,'id','vh_st_brand_id')->select('id','name','slug');
    }

    //-------------------------------------------------
    public function store()
    {
        return $this->hasOne(Store::class,'id','vh_st_store_id')->select('id','name','slug');
    }

    //-------------------------------------------------
    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_product_status')
            ->select('id','name','slug');
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
    public function variationCount()
    {
        return $this->hasMany(ProductVariation::class,'vh_st_product_id','id')
            ->where('vh_st_product_variations.is_active', 1)
            ->select();
    }

    //-------------------------------------------------
    public function vendorsCount()
    {
        return $this->hasMany(ProductVendor::class,'vh_st_product_id','id')
            ->where('vh_st_product_vendors.is_active', 1)
            ->select();
    }

//    public function productVendor(){
//        return $this->hasMany(Product)
//    }

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
    public static function createVariation($request)
    {
        $input = $request->all();
        $product_id = $input['id'];
        $validation = self::validatedVariation($input['all_variation']);
        if (!$validation['success']) {
            return $validation;
        }

        $all_variation = $input['all_variation']['structured_variation'];
        $all_attribute = $input['all_variation']['all_attribute_name'];

        foreach ($all_variation as $key => $value) {

            $item = new ProductVariation();
            $item->name = $value['variation_name'];
            $item->slug = Str::slug($value['variation_name']);

            $item->vh_st_product_id = $product_id;
            $item->is_active = 1;
//            $item->sku
            $item->save();

            foreach ($all_attribute as $k => $v) {
                $item2 = new ProductAttribute();
                $item2->vh_st_product_variation_id = $item->id;
                $item2->vh_st_attribute_id = $value[$v]['vh_st_attribute_id'];
                $item2->save();

                $item3 = new ProductAttributeValue();
                $item3->vh_st_product_attribute_id = $item2->id;
                $item3->vh_st_attribute_value_id = $value[$v]['vh_st_attribute_values_id'];
                $item3->value = $value[$v]['value'];
                $item3->save();
            }
        }

        $response = self::getItem($product_id);
        $response['messages'][] = 'Saved successfully.';
        return $response;
    }

    //-------------------------------------------------
    public static function validatedVariation($variation){

        if (isset($variation['structured_variation']) && !empty($variation['structured_variation'])){
            $error_message = [];
            $all_variation = $variation['structured_variation'];
            $all_arrtibute = $variation['all_attribute_name'];
//            dd($all_variation);
            foreach ($all_variation as $key=>$value){

                if (!isset($value['variation_name']) || empty($value['variation_name'])) {
                    array_push($error_message, "variation name's required");
                } elseif (!isset($value['media']) || empty($value['media'])){
                    array_push($error_message, $value["variation_name"]."'s media required");
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
                'errors' => 'Product Variation is empty.'
            ];
        }
    }

    //-------------------------------------------------
    public static function createItem($request)
    {

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

        $item = new self();
        $item->fill($inputs);
        $item->name = $inputs['name'];
        $item->slug = Str::slug($inputs['slug']);
        $item->taxonomy_id_product_type = $inputs['taxonomy_id_product_type']['id'];
        $item->status_notes = $inputs['status_notes'];
        $item->taxonomy_id_product_status = $inputs['taxonomy_id_product_status']['id'];
        if(is_string($inputs['vh_st_brand_id']['name'])){
            $item->vh_st_brand_id = $inputs['vh_st_brand_id']['id'];
        }
        if(is_string($inputs['vh_st_store_id']['name'])){
            $item->vh_st_store_id = $inputs['vh_st_store_id']['id'];
        }
        if($inputs['in_stock']==1 && $inputs['quantity']==0){
            $response['messages'][] = 'The quantity should be more then 1.';
            return $response;
        }else{
            $item->quantity = $inputs['quantity'];
            $item->in_stock = 1;
        }
        if($inputs['quantity']==0){
            $item->in_stock = 0;
        }
        $item->is_active = $inputs['is_active'];


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
            return $query->whereNotNull('is_active');
        } else{
            return $query->whereNull('is_active');
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
            $q->where('name', 'LIKE', '%' . $search . '%')
                ->orWhere('slug', 'LIKE', '%' . $search . '%');
        });

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('brand','store','type','status', 'variationCount', 'vendorsCount');
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
                break;
            case 'restore':
                self::whereIn('id', $items_id)->restore();
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
            $response['failed'] = true;
            $response['messages'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        self::whereIn('id', $items_id)->forceDelete();
        ProductVendor::deleteProducts($items_id);
        ProductVariation::deleteProducts($items_id);
        ProductMedia::deleteProducts($items_id);
        ProductPrice::deleteProducts($items_id);
        ProductStock::deleteProducts($items_id);

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
                }
                break;
            case 'restore':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->restore();
                }
                break;
            case 'delete':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->forceDelete();
                    ProductVendor::deleteProducts($items_id);
                    ProductVariation::deleteProducts($items_id);
                    ProductMedia::deleteProducts($items_id);
                    ProductPrice::deleteProducts($items_id);
                    ProductStock::deleteProducts($items_id);
                }
                break;
            case 'activate-all':
                self::query()->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                self::query()->update(['is_active' => null]);
                break;
            case 'trash-all':
                self::query()->delete();
                break;
            case 'restore-all':
                self::withTrashed()->restore();
                break;
            case 'delete-all':
                $items_id = self::all()->pluck('id')->toArray();
                self::withTrashed()->forceDelete();
                ProductVendor::deleteProducts($items_id);
                ProductVariation::deleteProducts($items_id);
                ProductMedia::deleteProducts($items_id);
                ProductPrice::deleteProducts($items_id);
                ProductStock::deleteProducts($items_id);
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser',
                'brand','store','type','status', 'productAttributes',
            ])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }
        $item['product_variation'] = null;
        $item['all_variation'] = [];
        $item['selected_vendor'] = [];
        $response['success'] = true;
        $response['data'] = $item;

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

        // check if name exist
        $item = self::where('id', '!=', $inputs['id'])
            ->withTrashed()
            ->where('name', $inputs['name'])->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This name is already exist.";
            return $response;
        }

        // check if slug exist
        $item = self::where('id', '!=', $inputs['id'])
            ->withTrashed()
            ->where('slug', $inputs['slug'])->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This slug is already exist.";
            return $response;
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->name = $inputs['name'];
        $item->slug = Str::slug($inputs['slug']);
        if(is_string($inputs['vh_st_brand_id']['name'])){
            $item->vh_st_brand_id = $inputs['vh_st_brand_id']['id'];
        }else{
            $item->vh_st_brand_id = $inputs['vh_st_brand_id']['name']['id'];
        }
        if(is_string($inputs['vh_st_store_id']['name'])){
            $item->vh_st_store_id = $inputs['vh_st_store_id']['id'];
        }else{
            $item->vh_st_store_id = $inputs['vh_st_store_id']['name']['id'];
        }
        $item->taxonomy_id_product_type = $inputs['taxonomy_id_product_type']['id'];
        $item->taxonomy_id_product_status = $inputs['taxonomy_id_product_status']['id'];
        $item->status_notes = $inputs['status_notes'];

        if($inputs['in_stock']==1 && $inputs['quantity']==0){
            $response['messages'][] = 'The quantity should be more then 1';
            return $response;
        }else{
            $item->quantity = $inputs['quantity'];
            $item->in_stock = 1;
        }
        if($inputs['quantity']==0){
            $item->in_stock = 0;
        }
        $item->is_active = $inputs['is_active'];
        $item->save();

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
            $response['messages'][] = 'Record does not exist.';
            return $response;
        }
        $item->forceDelete();
        ProductVendor::deleteProducts($item->id);
        ProductVariation::deleteProducts($item->id);
        ProductMedia::deleteProducts($item->id);
        ProductPrice::deleteProducts($item->id);
        ProductStock::deleteProducts($item->id);

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
                self::find($id)->delete();
                break;
            case 'restore':
                self::where('id', $id)
                    ->withTrashed()
                    ->restore();
                break;
        }

        return self::getItem($id);
    }
    //-------------------------------------------------

    public static function validation($inputs)
    {
        $rules = validator($inputs, [
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
            'taxonomy_id_product_status'=> 'required',
            'status_notes' => 'required_if:taxonomy_id_product_status.slug,==,rejected',
            'in_stock'=> 'required|numeric',
            'vh_st_brand_id'=> 'required',
            'vh_st_store_id'=> 'required',
            'taxonomy_id_product_type'=> 'required',
            'quantity'  => 'required'
       ],
       [
            'taxonomy_id_product_status.required' => 'The Status field is required',
            'vh_st_brand_id.required' => 'The Brand field is required',
            'vh_st_store_id.required' => 'The Store field is required',
            'taxonomy_id_product_type.required' => 'The Type field is required',
           'status_notes.*' => 'The Status notes field is required for "Rejected" Status',
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
            ->first();
        return $item;
    }

    //-------------------------------------------------
    //-------------------------------------------------
    //-------------------------------------------------


}
