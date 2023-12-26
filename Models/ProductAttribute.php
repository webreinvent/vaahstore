<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Faker\Factory;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class ProductAttribute extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_product_attributes';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'vh_st_product_variation_id',
        'vh_st_attribute_id',
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
    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }

    //-------------------------------------------------

    public function productVariation()
    {
        return $this->hasOne(ProductVariation::class,'id','vh_st_product_variation_id')
            ->select('name', 'id', 'is_default');
    }

    //-------------------------------------------------
    public function attribute()
    {
        return $this->hasOne(Attribute::class,'id','vh_st_attribute_id')
            ->select('name', 'id', 'type');
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

        $validation_result = self::productAttributeInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }
        $inputs = $validation_result['data'];

        // check if product variation and attribute value already  exist in the table

        $product_variation_id = $inputs['vh_st_product_variation_id'];
        $attribute_id = $inputs['vh_st_attribute_id'];

        $item = self::where('vh_st_product_variation_id',$product_variation_id)
            ->where('vh_st_attribute_id',$attribute_id)->withTrashed()->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This record is already exist.";
            return $response;
        }

        $item = new self();
        $item->fill($inputs);
        $item->save();
        foreach ($inputs['attribute_values'] as $key=>$value){

            $item1 = new ProductAttributeValue();
            $item1->vh_st_product_attribute_id = $item->id;
            $item1->vh_st_attribute_value_id = $value['id'];
            $item1->value = $value['new_value'] ?? $value['default_value'];
            $item1->save();
        }

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }

    //-------------------------------------------------
    public static function productAttributeInputValidator($requestData){

        $validated_data = validator($requestData, [
            'vh_st_product_variation_id' => 'required',
            'vh_st_attribute_id' => 'required',
            'attribute_values' => '',
            'attribute_values.*.new_value' => 'max:100'
        ],
            [
                'vh_st_product_variation_id.required' => 'The Product Variation field is required',
                'vh_st_attribute_id.required' => 'The Attribute field is required',
                'attribute_values.*.new_value' => 'The Attribute Value field should not exceed :max characters',
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

    public static function searchProductVariation($request)
    {

        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $product_variations = ProductVariation::select('id','name','slug')
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        else{

            $product_variations = ProductVariation::where('name', 'like', "%$query%")
                ->orWhere('slug','like',"%$query%")
                ->select('id','name','slug')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $product_variations;
        return $response;

    }

    //-------------------------------------------------

    public static function searchAttribute($request)
    {


        $query = $request['filter']['q']['query'];

        if($query === null)
        {
            $attributes = Attribute::inRandomOrder()
                ->take(10)
                ->get();
        }

        else{

            $attributes = Attribute::where('name', 'like', "%$query%")->get();
        }

        $response['success'] = true;
        $response['data'] = $attributes;
        return $response;

    }

    //-------------------------------------------------

    public function scopeSearchFilter($query, $filter)
    {

        if(!isset($filter['q']))
        {
            return $query;
        }
        $search = $filter['q'];

        $query->where(function ($query) use ($search) {
            $query->where('id', 'LIKE', '%' . $search . '%')
                ->orwhereHas('productVariation', function ($query) use ($search) {
                    $query->where('name','LIKE', '%'.$search.'%')
                        ->orWhere('slug', 'LIKE', '%' . $search . '%');
                });
        });

    }

    //-------------------------------------------------

    public function scopeProductVariationFilter($query, $filter)
    {

        if(!isset($filter['product_variation'])
            || is_null($filter['product_variation'])
            || $filter['product_variation'] === 'null'
        )
        {
            return $query;
        }

        $product_variation = $filter['product_variation'];

        $query->whereHas('productVariation', function ($query) use ($product_variation) {
            $query->where('slug', $product_variation);

        });

    }

    //-------------------------------------------------

    public function scopeAttributesFilter($query, $filter)
    {

        if(!isset($filter['attributes'])
            || is_null($filter['attributes'])
            || $filter['attributes'] === 'null'
        )
        {
            return $query;
        }

        $attributes = $filter['attributes'];

        $query->whereHas('attribute', function ($query) use ($attributes) {
            $query->where('slug', $attributes);

        });

    }

    //-------------------------------------------------

    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with(['productVariation', 'attribute']);
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->productVariationFilter($request->filter);
        $list->attributesFilter($request->filter);

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
                $list->update(['is_active' => null]);
                break;
            case 'trash-all':
                $user_id = auth()->user()->id;
                $list->update(['deleted_by' => $user_id]);
                $list->delete();
                break;
            case 'restore-all':
                $list->update(['deleted_by' => null]);
                $list->restore();
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }

        $item->vh_st_product_variation_id = $item->productVariation->id;
        $item->vh_st_attribute_id = $item->attribute->id;

        $item1 = ProductAttributeValue::where('vh_st_product_attribute_id', $item->id)->with(['attributeValues'])->get();
        $attribute_values = [];
        $item->attribute_values = [];
        if ($item1){
            foreach ($item1->toArray() as $key=>$value){
                $attribute_values[$key]['id'] = $value['attribute_values'][0]['id'];
                $attribute_values[$key]['default_value'] = $value['attribute_values'][0]['value'];
                $attribute_values[$key]['new_value'] = $value['value'];
            }
            $item->attribute_values = $attribute_values;
        }

        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }
    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $validation_result = self::productAttributeInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }

        $inputs = $validation_result['data'];

        // check if product variation and attribute value already  exist in the table

        $product_variation_id = $inputs['vh_st_product_variation_id'];
        $attribute_id = $inputs['vh_st_attribute_id'];

        $item = self::where('id','!=', $id)->where('vh_st_product_variation_id',$product_variation_id)
            ->where('vh_st_attribute_id',$attribute_id)->withTrashed()->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This record is already exist.";
            return $response;
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->save();

        $all_active_attribute_values_ids = [];
        foreach ($inputs['attribute_values'] as $key=>$value){

            $item1 = ProductAttributeValue::where(['vh_st_product_attribute_id' =>
                $item->id, 'vh_st_attribute_value_id' => $value['id']])->first();
            if (!$item1){
                $item1 = new ProductAttributeValue();
                $item1->vh_st_product_attribute_id = $item->id;
                $item1->vh_st_attribute_value_id = $value['id'];
                $item1->value = $value['new_value'] ?? $value['default_value'];
                $item1->save();
            }else{
                $item1->value = $value['new_value'] ?? $value['default_value'];
                $item1->save();
            }

            array_push($all_active_attribute_values_ids,$value['id']);
        }

        ProductAttributeValue::where(['vh_st_product_attribute_id' => $item->id])->
        whereNotIn('vh_st_attribute_value_id', $all_active_attribute_values_ids)->delete();

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
            foreach ($inputs['attribute_values'] as $key=>$value){
                $item1 = new ProductAttributeValue();
                $item1->vh_st_product_attribute_id = $item->id;
                $item1->vh_st_attribute_value_id = $value['id'];
                $item1->value = $value['new_value'] ?? $value['default_value'];
                $item1->save();
            }
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

        $product_variations = ProductVariation::where('is_active', 1)->get();
        $product_variation_ids = $product_variations->pluck('id')->toArray();
        $product_variation_id = $product_variation_ids[array_rand($product_variation_ids)];
        $product_variation = $product_variations->where('id',$product_variation_id)->first();
        $inputs['product_variation'] = $product_variation;
        $inputs['vh_st_product_variation_id'] = $product_variation_id;

        $attributes = Attribute::get();
        $attribute_ids = $attributes->pluck('id')->toArray();
        $attribute_id = $attribute_ids[array_rand($attribute_ids)];
        $attribute = $attributes->where('id',$attribute_id)->first();
        $inputs['attribute'] = $attribute;
        $inputs['vh_st_attribute_id'] = $attribute_id;

        $attribute_values = AttributeValue::where('vh_st_attribute_id', $attribute_id)->get(['id', 'value']);

        $attribute_value = [];
        foreach ($attribute_values as $key=>$value){
            $attribute_value[$key]['id'] = $value['id'];
            $attribute_value[$key]['default_value'] = $value['value'];
            $attribute_value[$key]['new_value'] = $value['value'];
        }

        $inputs['attribute_values'] = $attribute_value;
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

    public static function deleteProductVariations($items_id){

        if ($items_id) {
            $items_exist = self::whereIn('vh_st_product_variation_id', $items_id);

            if ($items_exist) {
                self::whereIn('vh_st_product_variation_id', $items_id)->forceDelete();
                $response['success'] = true;
                $response['data'] = true;
            } else {
                $response['success'] = true;
                $response['data'] = false;
            }
        } else {
            // If $items_id is not set, return an error
            $response['error'] = true;
            $response['data'] = false;
        }
    }

    //-------------------------------------------------

    //-------------------------------------------------
    public static function deleteProductVariation($items_id){
        if ($items_id) {
            $items_exist = self::whereIn('vh_st_product_variation_id', $items_id);

            if ($items_exist) {
                self::whereIn('vh_st_product_variation_id', $items_id)->forceDelete();
                $response['success'] = true;
                $response['data'] = true;
            } else {
                $response['success'] = true;
                $response['data'] = false;
            }
        } else {
            // If $items_id is not set, return an error
            $response['error'] = true;
            $response['data'] = false;
        }

    }

}
