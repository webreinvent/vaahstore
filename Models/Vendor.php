<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use VaahCms\Modules\Store\Models\ProductVendor;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\TaxonomyType;
use Faker\Factory;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use VaahCms\Modules\Store\Models\Store;
use Illuminate\Support\Facades\DB;

class Vendor extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_vendors';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'vh_st_store_id', 'name', 'slug',
        'phone_number','email','address','country_code',
        'owned_by', 'registered_at',
        'years_in_business',
        'business_document_type',
        'country_code',
        'business_document_detail',
        'business_document_file',
        'services_offered',
        'taxonomy_id_vendor_business_type',
        'auto_approve_products', 'approved_by',
        'approved_at', 'is_default', 'is_active',
        'taxonomy_id_vendor_status', 'status_notes', 'meta',

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

    public function users()
    {
        return $this->belongsToMany(User::class,
            'vh_st_vendor_users','vh_st_vendor_id', 'vh_user_id',
        )->withPivot('vh_st_vendor_id','vh_user_id','vh_role_id','id');
    }
    //-------------------------------------------------

    public function productVendors()
    {

        return $this->hasMany(ProductVendor::class, 'vh_st_vendor_id', 'id');
    }
    //-------------------------------------------------

    public function roles()
    {
        return $this->belongsToMany(Role::class,
            'vh_user_roles', 'vh_user_id', 'vh_role_id'
        )->withPivot('is_active');
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
    public function store(){

        return $this->belongsTo(Store::class, 'vh_st_store_id','id')
            ->withTrashed()
            ->select(['id','name', 'is_default','slug','is_multi_vendor','deleted_at']);
    }

    //-------------------------------------------------
    public function approvedByUser(){
        return $this->hasOne(User::class, 'id', 'approved_by')->select(['id','first_name','email']);
    }

    //-------------------------------------------------
    public function ownedByUser(){
        return $this->hasOne(User::class, 'id', 'owned_by')->select(['id','first_name', 'email']);
    }

    //-------------------------------------------------
    public function status(){
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id_vendor_status', 'id');
    }

    //-------------------------------------------------

    public function business_type(){
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id_vendor_business_type', 'id');
    }
    public function productStocks()
    {
        return $this->hasMany(ProductStock::class, 'vh_st_vendor_id', 'id');
    }
    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class, 'vh_st_vendor_id', 'id');
    }
    //-------------------------------------------------

    public function vendorProducts()
    {
        return $this->belongsToMany(Product::class,'vh_st_product_vendors','vh_st_vendor_id','vh_st_product_id')->withTrashed()
            ->select('vh_st_products.id','vh_st_products.name','vh_st_products.slug','vh_st_products.status_notes','vh_st_products.taxonomy_id_product_status')
            ->withPivot([]);
    }
    //---------------------------------------------------


    //-------------------------------------------------
    public static function validatedProduct($data) {
        if (!isset($data) || empty($data)) {
            return [
                'success' => false,
                'errors' => ['Products are empty.']
            ];
        }

        $error_message = [];

        foreach ($data as $key => $product) {

            if (!isset($product['taxonomy_id_product_status']) || empty($product['taxonomy_id_product_status'])) {
                $error_message[] = 'Status required';
            }
            if (!isset($product['id']) || empty($product['id'])) {
                $error_message[] = 'Product ID is required';
            }
            if (!isset($product['can_update'])) {
                $error_message[] = 'Can Update required';
            }
        }

        if (empty($error_message)) {
            return [
                'success' => true
            ];
        }

        return [
            'success' => false,
            'errors' => $error_message
        ];
    }


    //-------------------------------------------------
    public static function attachProducts($request,$id){

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $vendor_id = $id;
        $product_data = $request->input('products');
        $validation = self::validatedProduct($product_data);
        if (!$validation['success']) {
            return $validation;
        }

        $active_user = auth()->user();
        ProductVendor::where('vh_st_vendor_id', $vendor_id)->update(['is_active'=>0]);

        foreach ($product_data as $key=>$product){

            $vendor_product = ProductVendor::where(['vh_st_vendor_id'=> $vendor_id, 'vh_st_product_id' => $product['id']])->first();
           if($vendor_product){
                $response['errors'][] = "'{$product['name']}' already exists for this vendor.";
                return $response;
            }

           $item = new ProductVendor();

           $item->vh_st_vendor_id = $vendor_id;

           $item->vh_st_product_id = $product['id'];

           $item->added_by = $active_user->id;

           $item->can_update = $product['can_update'];

           $item->taxonomy_id_product_vendor_status = $product['taxonomy_id_product_status'];
            if(isset($product['status_notes']))
            {
                $item->status_notes = $product['status_notes'];
            }

           $item->is_active = 1;
           $item->save();
           $vendor_product = ProductVendor::find($item->id);
           $vendor_product->storeVendorProduct()->attach($product['vh_st_store_id']);
        }

        $response = self::getItem($vendor_id);
        $response['messages'][] = 'Product Added successfully.';
        return $response;

    }
    //-------------------------------------------------
    public static function bulkProductRemove($request ,$id){

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }

            ProductVendor::where('vh_st_vendor_id', $id)->update(['is_active'=>0]);
            $response['messages'][] = 'Removed all product successfully.';
            return $response;


    }

    //-------------------------------------------------
    public static function singleProductRemove($request ,$id){

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }

        ProductVendor::where('id', $id)->update(['is_active'=>0]);
        $vendor = ProductVendor::select('vh_st_vendor_id')->where('id', $id)->first();
        $response = self::getItem($vendor->vh_st_vendor_id);
        $response['messages'][] = 'Removed successfully.';
        return $response;

    }
    //-------------------------------------------------
    public static function createItem($request)
    {

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission($permission_slug)) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $inputs = $request->all();
        $validation_result = self::vendorInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }

            if($inputs['store']['is_multi_vendor'] === 0)
            {

                $vendor_count = Store::where('id', $inputs['vh_st_store_id'])
                    ->withTrashed()
                    ->firstOrFail()
                    ->vendors()
                    ->count();

                if ($vendor_count > 0) {
                    $response['errors'][] = "A vendor is already associated with this non-multi-vendor store.";
                    return $response;
                }
            }
            // check if name exist
        $item = self::where('name', $inputs['name'])->withTrashed()->first();

        if ($item) {
            $error_message = "This Name already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if slug exist
        $item = self::where('slug', $inputs['slug'])->withTrashed()->first();

        if ($item) {
            $error_message = "This Slug already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        // Check if current record is default
        if($inputs['is_default']){
            self::where('is_default',1)->update(['is_default' => 0]);
        }
        $inputs = $validation_result['data'];
        $item = new self();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->registered_at = \Carbon\Carbon::now()->toDateTimeString();
        $item->approved_at = \Carbon\Carbon::now()->toDateTimeString();
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;

    }

    //-------------------------------------------------
    public static function vendorInputValidator($requestData){


        $rules = [
            'name' => [
                'required',
                'max:150',

            ],
            'slug' => 'required|max:150',
            'vh_st_store_id' => 'required',
            'years_in_business' => 'nullable|integer|min:1|max:100',
            'services_offered' => 'max:250',
            'approved_by' => 'required',
            'owned_by' => 'required',
            'taxonomy_id_vendor_status' => 'required',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:250'
            ],
            'store' => 'nullable',
            'email' => 'nullable|email|max:100',
            'address' => 'max:250',
            'business_document_type' => 'nullable|max:50',
            'business_document_detail'=>'nullable|max:50',
            'business_document_file' => 'nullable',
            'is_default' => 'nullable',
            'auto_approve_products' => 'nullable',
            'vendor_products' =>'nullable',
            'is_active'=>'nullable',
        ];

        if (!empty($requestData['phone_number']) || !empty($requestData['country_code'])) {
            $rules['phone_number'] = [
                'max:10',
                'min:10',
                'regex:/^[0-9]+$/', // Only allow numbers
            ];

            $rules['country_code'] = [
                'max:4',
            ];
        }

        $validated_data = validator($requestData, $rules, [
            'name.regex' => 'The Name field only supports uppercase letters (A-Z), lowercase letters (a-z),
             numbers (0-9), period (.), apostrophe (\'), hyphen/dash (-), ampersand (&), slash (/), and spaces',
            'name.required' => 'The Name field is required',
            'name.max' => 'The Name field cannot be greater than :max characters',
            'slug.required' => 'The Slug field is required',
            'slug.max' => 'The Slug field cannot be greater than :max characters',
            'email.email' => 'The Email is not valid',
            'email.max' => 'The Email field cannot be more than :max characters',
            'address.required' => 'The Address field cannot be more than :max characters',
            'years_in_business.min' => 'The Years in business must be greater than 0',
            'years_in_business.max' => 'The Years in Business field Should not be greater than 100 ',
            'services_offered.required' => 'The Services offered field is required',
            'services_offered.max' => 'The Services offered field cannot be more than :max characters',
            'approved_by.required' => 'The Approved by field is required',
            'owned_by.required' => 'The Owned by field is required',
            'vh_st_store_id.required' => 'The Store field is required',
            'taxonomy_id_vendor_status.required' => 'The Status field is required',
            'status_notes.required_if' => 'The Status notes field is required for "Rejected" Status',
            'status_notes.max' => 'The Status notes field cannot not be greater than :max characters.',
            'phone_number.regex' => 'The Phone Number is required if the country code is provided and should contain only numbers.',
            'phone_number.max' => 'The Phone Number field should not be more than :max characters',
            'phone_number.min' => 'The Phone Number field should  be  :min characters',
            'country_code.regex' => 'The Country Code is required if the Phone Number is provided should contain only numbers',
            'country_code.max' => 'The Country Code field should not be more than :max characters',
            'business_document_detail.max'=>'The Business Document Details  field should not be more than :max characters',
            'business_document_type.max' =>'The Business Document Type field should not be more than :max characters',
        ]);

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
    //---------------------------------------------------

    public function scopeProductFilter($query, $filter)
    {

        if(!isset($filter['products']))
        {
            return $query;
        }
        $products = $filter['products'];

        $query->whereHas('vendorProducts' , function ($q) use ($products){
            $q->whereIn('slug' ,$products);
        });


    }


    //-------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if(!isset($filter['q']))
        {
            return $query;
        }

        $keys = explode(' ',$filter['q']);
        foreach($keys as $search)
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
    public function scopeSearchStore($query, $filter)
    {

        if(!isset($filter['vendor_status']))
        {
            return $query;
        }
        $search = $filter['vendor_status'];
        $query->whereHas('status' , function ($q) use ($search){
            $q->whereIn('name' ,$search);
        });

    }
    //-------------------------------------------------
    public function scopeVendorStatus($query, $filter)
    {
        if(!isset($filter['store']))
        {
            return $query;
        }
        $search = $filter['store'];
        $query->whereHas('store',function ($q) use ($search) {
            $q->whereIn('name',$search);
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
        $default_vendor = self::where('is_default', 1)->first();
        $is_exist = $default_vendor ? true : false;
        $default_vendor_product_exists = $is_exist ? ProductVendor::where('vh_st_vendor_id', $default_vendor->id)->exists() : false;

        $list = self::getSorted($request->filter)->with(['store', 'approvedByUser',
            'ownedByUser', 'status','vendorProducts','users']);

        if ($request->has('filter')) {
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
            $list->searchStore($request->filter);
            $list->vendorStatus($request->filter);
            $list->dateFilter($request->filter);
            $list->productFilter($request->filter);
        }

        $rows = config('vaahcms.per_page');
        if ($request->has('rows')) {
            $rows = $request->rows;
        }
        $list = $list->paginate($rows);

        $response = [
            'success' => true,
            'data' => $list,
        ];

        if (!$is_exist) {
            $response['message'] = ' There is no default vendor. Mark a vendor as default.';
        } elseif (!$default_vendor_product_exists) {
            $response['message'] = 'The default vendor is not associated with any product in vendor products table.';
        }

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
                $user_id = auth()->user()->id;
                $items->update(['deleted_by' => $user_id]);
                $items->update(['is_default' => 0]);
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
        foreach ($items_id as $item_id)
        {
            self::deleteRelatedItem($item_id, ProductVendor::class);
            self::deleteRelatedItem($item_id, Warehouse::class);
            self::deleteRelatedItem($item_id, ProductStock::class);

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
                    $items->update(['is_default' => 0]);
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
                        self::deleteRelatedItem($item_id, ProductVendor::class);
                        self::deleteRelatedItem($item_id, Warehouse::class);
                        self::deleteRelatedItem($item_id, ProductStock::class);


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
                $items_id = self::withTrashed()->get()->pluck('id')->toArray();

                foreach ($items_id as $item_id)
                {
                    self::deleteRelatedItem($item_id, ProductVendor::class);
                    self::deleteRelatedItem($item_id, Warehouse::class);
                    self::deleteRelatedItem($item_id, ProductStock::class);

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
            ->with(['createdByUser','updatedByUser', 'deletedByUser', 'store', 'approvedByUser','ownedByUser',
                'status','business_type','users' => function ($query) {
                    $query->select('vh_users.id','vh_users.first_name')
                        ->withPivot('is_active');
                }])
            ->withTrashed()
            ->first();
        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }

        $vendor_product = $item->vendorProducts->map(function ($vendor_product) {
            return [
                'is_selected' => false,
                'product' => [
                    'id' => $vendor_product->id,
                    'name' => $vendor_product->name,
                    'status_notes'=>$vendor_product->status_notes
                ]
            ];
        })->toArray();






        $response['data'] = $item->toArray();
        $response['data']['products'] = $vendor_product;


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
        $inputs = $request->all();

        $validation_result = self::vendorInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }


        if ($inputs['is_default']) {
            self::where('is_default', 1)->update(['is_default' => 0]);
        }

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

        $item = self::where('id', $id)->withTrashed()->first();

        if(isset($inputs['store']['is_multi_vendor']) &&
            $inputs['store']['is_multi_vendor'] !== $item->store->is_multi_vendor) {

            if($inputs['store']['is_multi_vendor'] === 0) {


                $vendor_count = Store::where('id', $inputs['vh_st_store_id'])
                    ->withTrashed()
                    ->firstOrFail()
                    ->vendors()
                    ->count();

                if ($vendor_count >= 1) {
                    $response['errors'][] = "A vendor is already associated with this non-multi-vendor store.";
                    return $response;
                }
            }
        }


        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->registered_at = \Carbon\Carbon::now()->toDateTimeString();
        $item->approved_at = \Carbon\Carbon::now()->toDateTimeString();
        $item->save();



        if (isset($inputs['products']) && is_array($inputs['products'])) {
            $productsData = collect($inputs['products'])->map(function ($product) {

                return [
                    'id' => $product['product']['id'],
                    'status_notes' => $product['product']['status_notes'],
                    'taxonomy_id_product_vendor_status' => $product['product']['taxonomy_id_product_status'],
                ];
            });



            $item->vendorProducts()->sync($productsData);
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
            self::deleteRelatedItem($item->id, ProductVendor::class);
            self::deleteRelatedItem($item->id, Warehouse::class);
            self::deleteRelatedItem($item->id, ProductStock::class);
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
                $item->is_default = 0;
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

        $inputs['name'] = $faker->name;
        $inputs['slug'] = Str::slug($inputs['name']);
        $store = Store::where('is_active', 1)->inRandomOrder()->first();
        if ($store) {
            $inputs['vh_st_store_id'] = $store->id;
            $inputs['store'] = $store;
        }

        $inputs['years_in_business'] = rand(1,100);

        $number_of_characters = rand(5,250);
        $inputs['status_notes']=$faker->text($number_of_characters);

        $approved_by = User::where('is_active',1)->inRandomOrder()->first();
        $inputs['approved_by'] =$approved_by->id;
        $inputs['approved_by_user'] = $approved_by;

        $inputs['phone_number'] = rand(1000000000,9999999999);

        $inputs['email'] = $faker->email;
        $inputs['address'] = $faker->address;
        $inputs['years_in_business'] = rand(1,25);;

        $owned_by = User::where('is_active',1)->inRandomOrder()->first();
        $inputs['owned_by'] =$owned_by->id;
        $inputs['owned_by_user'] = $owned_by;

        $taxonomy_status = Taxonomy::getTaxonomyByType('vendor-status');
        $count = count($taxonomy_status);
        if ($count <= 0) {
            $response['success'] = false;
            $response['errors'][] = 'No Status types found. Please create or activate existing ones.';
            return $response;
        }
        $status_id = $taxonomy_status->pluck('id')->random();
        $status = $taxonomy_status->where('id', $status_id)->first();
        $inputs['taxonomy_id_vendor_status'] = $status_id;
        $inputs['status'] = $status;

        $inputs['is_active'] = 1;
        $inputs['is_default'] = 0;
        $inputs['business_document_file'] = null;

        // set contact info field

        $inputs['services_offered'] =  $faker->text($number_of_characters);

        $inputs['country_code'] = $faker->countryCode;

        $document_details = [
            'invoice' => 'Invoice for services rendered on project XYZ.',
            'contract' => 'A legally binding agreement between two parties.',
            'license' => 'Government-issued permit for business operations.',
            'permit' => 'Approval document for construction or business activity.',
            'agreement' => 'Mutual understanding document between companies.',
            'receipt' => 'Proof of payment for a completed transaction.',
            'quotation' => 'Estimated cost for requested services or goods.',
            'purchase order' => 'Official order request for goods or services.',
            'business proposal' => 'Detailed plan for a new business venture.',
            'non-disclosure agreement' => 'Confidentiality agreement between parties.',
            'memorandum of understanding' => 'Preliminary agreement outlining terms.',
            'letter of intent' => 'Indicates the intention to proceed with a deal.',
            'tax certificate' => 'Official proof of tax compliance.',
            'certificate of incorporation' => 'Legal document establishing a company.',
            'employment contract' => 'Agreement between employer and employee.',
            'service agreement' => 'Contract for service-based work.',
            'vendor agreement' => 'Agreement between business and suppliers.',
            'lease agreement' => 'Rental contract for property or equipment.',
            'business plan' => 'Detailed strategic plan for a company.',
            'audit report' => 'Financial examination report for a company.'
        ];

        $inputs['business_document_type'] = array_rand($document_details);
        $inputs['business_document_detail'] = $document_details[$inputs['business_document_type']];



        if(!$is_response_return){
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }

    //-------------------------------------------------
    public static function deleteStores($items_id){
        if($items_id){
            self::where('vh_st_store_id',$items_id)->forcedelete();
            $response['success'] = true;
            $response['data'] = true;
        }else{
            $response['error'] = true;
            $response['data'] = false;
        }

    }
    //-------------------------------------------------
    public static function searchStore($request)
    {

        $search_store = Store::select('id', 'name','slug','is_default','is_multi_vendor')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_store->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%');
                $q->orwhere('slug', 'LIKE', '%' . $query . '%');
            });
        }
        $search_store = $search_store->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_store;
        return $response;
    }
    //-------------------------------------------------
    public static function searchApprovedBy($request)
    {
        $search_approved = User::select('id', 'first_name','last_name','email')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_approved->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', '%' . $query . '%');
                $q->orwhere('email', 'LIKE', '%' . $query . '%');
            });
        }
        $search_approved = $search_approved->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_approved;
        return $response;
    }

    //-------------------------------------------------
    public static function searchOwnedBy($request)
    {
        $search_owned_by = User::select('id', 'first_name','last_name','email')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_owned_by->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', '%' . $query . '%');
                $q->orwhere('email', 'LIKE', '%' . $query . '%');
            });
        }
        $search_owned_by = $search_owned_by->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_owned_by;
        return $response;
    }
    //-------------------------------------------------
    public static function searchStatus($request)
    {
        $query = $request->input('query');
        if(empty($query)) {
            $item = Taxonomy::getTaxonomyByType('vendor-status');
        } else {
            $tax_type = TaxonomyType::getFirstOrCreate('vendor-status');
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

    public static function searchProduct($request)
    {

        $search_product = Product::with('status')
            ->select('id','name','slug','is_default','taxonomy_id_product_status','vh_st_store_id')
            ->where('is_active', '1');

        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_product->where(function($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%');
                $q->orwhere('slug', 'LIKE', '%' . $query . '%');
            });
        }
        $search_product = $search_product->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_product;
        return $response;
    }

    //-----------------------------------------------------------------

    public static function deleteRelatedItem($item_id, $related_model)
    {
        $response = [];

        if ($item_id) {
            $item_exist = $related_model::where('vh_st_vendor_id', $item_id)->withTrashed()->first();
            if ($item_exist) {
                $related_model::where('vh_st_vendor_id', $item_id)->forceDelete();
                $response['success'] = true;
            }
        } else {
            $response['success'] = false;
        }

        return $response;
    }

    //-------------------------------------------------------------------------------

    public static function VendorRole()
    {
        $allowed_slugs = ['vendor-staff', 'vendor-admin', 'vendor-manager'];

      $roles = Role::whereIn('slug', $allowed_slugs)->get();
        if ($roles){
            return [
                'roles' =>$roles
            ];
        }else{
            return [
                'roles' => null
            ];
        }
    }

    //----------------------------------------------------------------------------------------


    public static function searchUser($request)
    {
        $search_user = User::select('id', 'first_name','last_name','email')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_user->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', '%' . $query . '%');
            });
        }
        $search_user = $search_user->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_user;
        return $response;
    }

    //-----------------------------------------------------------------------------

    public static function attachUsersRoles($request,$id)
    {
        $item = self::where('id', $id)
            ->withTrashed()
            ->first();

        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_not_found_with_id") . $id;
            return $response;
        }
        $user_roles = [];
        foreach ($request->input('users') as $user_detail) {
            $user_id = $user_detail['pivot']['vh_user_id'];
            $role_id = $user_detail['pivot']['vh_role_id'];

            $key = $user_id . '-' . $role_id;
            if (isset($user_roles[$key])) {
                $error_message = "This Record already present in the list.";
                $response['success'] = false;
                $response['messages'][] = $error_message;
                return $response;
            } else {
                $user_roles[$key] = true;
            }
        }

        VendorUser::where('vh_st_vendor_id', $id)->forceDelete();

        $data = [];
        foreach ($request->input('users') as $user_detail) {
            $user_id = $user_detail['pivot']['vh_user_id'];
            $role_id = $user_detail['pivot']['vh_role_id'];

            $data[] = [
                'vh_user_id' => $user_id,
                'vh_role_id' => $role_id,
            ];
        }

        $item->users()->attach($data);
        $response = self::getItem($item->id);
        $response['success'] = true;
        $response['data'] = $item;
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;
    }

    //------------------------------------------------------------------


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


    //-----------------------------------------------------------------

    public static function defaultStore($request)
    {
        $default_store = Store::where(['is_active' => 1, 'is_default' => 1])->select('id', 'name', 'slug', 'is_default', 'is_multi_vendor')->first();


        if($default_store)
        {
            $response['success'] = true;
            $response['data'] = $default_store;
        }
        else {
            $response['success'] = false;
            $response['data'] = null;
        }
        return $response;
    }


    //----------------------------------------------------------


    public static function topSellingVendorsData($request)
    {
        $limit = 5;

        $start_date = isset($request->start_date) ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
        $end_date = isset($request->end_date) ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        $store_id = isset($request->store['id']) ? (int)$request->store['id'] : null;
        $apply_date_range = !isset($request->filter_all) || !$request->filter_all;

        $query = OrderItem::query();

        if ($apply_date_range) {
            $query->whereBetween('created_at', [$start_date, $end_date]);
        }
        if ($store_id) {
            $query->whereHas('vendor', function ($q) use ($store_id) {
                $q->where('vh_st_store_id', $store_id);
            });
        }
        $top_selling_vendors = $query
            ->select('vh_st_vendor_id')
            ->groupBy('vh_st_vendor_id')
            ->with(['vendor'])
            ->get()
            ->map(function ($item) use ($apply_date_range, $start_date, $end_date) {
                $total_sales_query = OrderItem::where('vh_st_vendor_id', $item->vh_st_vendor_id);
                if ($apply_date_range) {
                    $total_sales_query->whereBetween('created_at', [$start_date, $end_date]);
                }
                $total_sales = $total_sales_query->sum('quantity');

                return [
                    'id' => $item->vendor->id,
                    'name' => $item->vendor->name,
                    'slug' => $item->vendor->slug,
                    'total_sales' => $total_sales,
                ];
            })
            ->sortByDesc('total_sales');

        if ($apply_date_range) {
            $top_selling_vendors = $top_selling_vendors->take($limit);
        }

        return [
            'data' => $top_selling_vendors->values(),
        ];
    }

    //----------------------------------------------------------


    public static function vendorSalesByRange($request)
    {
        $inputs = $request->all();

        $start_date = isset($inputs['start_date']) ? Carbon::parse($inputs['start_date'])->startOfDay() : Carbon::now()->startOfDay();
        $end_date = isset($inputs['end_date']) ? Carbon::parse($inputs['end_date'])->endOfDay() : Carbon::now()->endOfDay();
        $store_id = isset($inputs['store']['id']) ? (int)$inputs['store']['id'] : null;
        $apply_date_range = !isset($inputs['filter_all']) || !$inputs['filter_all'];

        $top_vendors = [];
        if ($apply_date_range) {
            $request->merge(['store' => $store_id ? ['id' => $store_id] : null]);
            $top_vendors = self::topSellingVendorsData($request)['data']->pluck('id')->toArray();
        }



        $period = new \DatePeriod($start_date, new \DateInterval('P1D'), $end_date->addDay());
        $labels = [];
        foreach ($period as $date) {
            $labels[] = $date->format('Y-m-d');
        }

        $query = OrderItem::select('vh_st_vendor_id')->groupBy('vh_st_vendor_id')->with('vendor');

        if ($apply_date_range) {
            $query->whereIn('vh_st_vendor_id', $top_vendors);
        }

        $vendor_sales = $query
            ->get()
            ->map(function ($item) use ($start_date, $end_date, $labels, $apply_date_range) {
                $sales_query = OrderItem::where('vh_st_vendor_id', $item->vh_st_vendor_id);

                if ($apply_date_range) {
                    $sales_query->whereBetween('created_at', [$start_date, $end_date]);
                }

                $sales_data = $sales_query
                    ->selectRaw('DATE(created_at) as sales_date, SUM(quantity) as total_sales')
                    ->groupBy('sales_date')
                    ->orderBy('sales_date')
                    ->get()
                    ->keyBy('sales_date');

                $formatted_data = [];
                $has_sales = false;
                foreach ($labels as $date_string) {
                    $sales = isset($sales_data[$date_string]) ? (int) $sales_data[$date_string]->total_sales : 0;
                    $formatted_data[] = ['x' => $date_string, 'y' => $sales];

                    if ($sales > 0) {
                        $has_sales = true;
                    }
                }
                if (!$has_sales) {
                    return null;
                }

                return [
                    'name' => $item->vendor->name,
                    'data' => $formatted_data,
                ];
            })
            ->filter();

        return [
            'data' => [
                'chart_series' => $vendor_sales->values(),
                'chart_options' => [
                    'xaxis' => [
                        'type' => 'datetime',
                        'categories' => $labels,
                    ],
                ],
            ],
        ];
    }
    //----------------------------------------------------------

}
