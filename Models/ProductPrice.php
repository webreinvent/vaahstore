<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Entities\User;

class ProductPrice extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_product_prices';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'vh_st_vendor_id',
        'vh_st_product_id',
        'vh_st_vendor_product_id',
        'vh_st_product_variation_id',
        'amount',
        'meta',
        'is_active',
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
    public function vendor()
    {
        return $this->hasOne(Vendor::class,'id','vh_st_vendor_id')->select('id','name','slug');
    }
    public function productVendor()
    {
        return $this->hasMany(ProductVendor::class,'vh_st_vendor_product_id','id');
    }
    //-------------------------------------------------
    public function product()
    {
        return $this->hasOne(Product::class,'id','vh_st_product_id')->select('id','name','slug');
    }
    //-------------------------------------------------
    public function productVariation()
    {
        return $this->hasOne(ProductVariation::class,'id','vh_st_product_variation_id')->select('id','name','slug');
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
    public static function createItem($request)
    {

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }


        // check if exist
        $item = self::where('vh_st_vendor_id', $inputs['vh_st_vendor_id']['id'])
            ->where('vh_st_product_variation_id', $inputs['vh_st_product_variation_id']['id'])
            ->where('vh_st_product_id', $inputs['vh_st_product_id']['id'])->withTrashed()->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This Vendor, Product and Product Variation is already exist.";
            return $response;
        }

        $item = new self();
        $item->fill($inputs);
        $item->vh_st_vendor_id = $inputs['vh_st_vendor_id']['id'];
        $item->vh_st_product_id = $inputs['vh_st_product_id']['id'];
        $item->vh_st_product_variation_id = $inputs['vh_st_product_variation_id']['id'];
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
        $list = self::getSorted($request->filter)->with('vendor','product','productVariation');
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
                self::withTrashed()->forceDelete();
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','vendor','product','productVariation'])
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
        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // check if exist
        $item = self::where('id', '!=', $inputs['id'])
            ->withTrashed()
            ->where('vh_st_vendor_id', $inputs['vh_st_vendor_id']['id'])
            ->where('vh_st_product_variation_id', $inputs['vh_st_product_variation_id']['id'])
            ->where('vh_st_product_id', $inputs['vh_st_product_id']['id'])->first();

        if ($item) {
            $response['success'] = false;
            $response['messages'][] = "This Vendor, Product and Product Variation is already exist.";
            return $response;
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->vh_st_vendor_id = $inputs['vh_st_vendor_id']['id'];
        $item->vh_st_product_id = $inputs['vh_st_product_id']['id'];
        $item->vh_st_product_variation_id = $inputs['vh_st_product_variation_id']['id'];
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
            'vh_st_vendor_id' => 'required|max:150',
            'vh_st_product_id' => 'required|max:150',
            'vh_st_product_variation_id' => 'required|max:150',
            'amount' => 'required|min:1|numeric',
                ],
        [
            'vh_st_product_id.required' => 'The Product field is required',
            'vh_st_vendor_id.required' => 'The Vendor field is required',
            'vh_st_product_variation_id.required' => 'The Product variation field is required',

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

    public static function deleteProductVariations($items_id){

        $response=[];

        if ($items_id) {
            $items_exist = self::whereIn('vh_st_product_variation_id', $items_id)->get();

            if ($items_exist) {
                self::whereIn('vh_st_product_variation_id', $items_id)->forceDelete();
                $response['success'] = true;
            }
        }

        $response['success'] = false;

        return $response;

    }

    //-------------------------------------------------

    public static function deleteProductVariation($item_id){

        $response = [];

        if ($item_id) {
            $item_exist = self::where('vh_st_product_variation_id', $item_id)->first();

            if ($item_exist) {
                self::where('vh_st_product_variation_id', $item_id)->forceDelete();

                $response['success'] = true;
            }
        } else {
            $response['success'] = false;
        }

        return $response;

    }



}
