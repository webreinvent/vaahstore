<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use Faker\Factory;
use WebReinvent\VaahCms\Models\TaxonomyType;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use Illuminate\Support\Facades\DB;

class Brand extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_brands';
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
        'registered_by',
        'registered_at',
        'approved_by',
        'approved_at',
        'is_active',
        'is_default',
        'taxonomy_id_brand_status',
        'status_notes',
        'meta',
        'created_by',
        'updated_by',
        'deleted_by',
        'image',
        'meta_description',
        'meta_title',
        'meta_keywords'
    ];

    protected $casts =[
        'meta_keywords'=>'array',
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
    protected function registeredAt(): Attribute
    {
        return Attribute::make(
            get: function (string $value = null) {
                return self::getUserTimezoneDate($value);
            },
            set: function (?string $value = null) {
                if ($value === null || $value === '') {
                    return null;
                }

                return \Carbon::parse(strtotime($value))
                    ->setTimezone(config('app.timezone'))
                    ->format(config('settings.global.datetime_format', 'Y-m-d H:i:s'));
            },
        );
    }


    //-------------------------------------------------
    protected function approvedAt(): Attribute
    {

        return Attribute::make(
            get: function (string $value = null) {
                return self::getUserTimezoneDate($value);
            },
            set: function (?string $value = null) {
                if ($value === null || $value === '') {
                    return null;
                }

                return \Carbon::parse(strtotime($value))
                    ->setTimezone(config('app.timezone'))
                    ->format(config('settings.global.datetime_format', 'Y-m-d H:i:s'));
            },
        );
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

    //-------------------------------------------------
    public function ownedByUser()
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
    public function registeredByUser()
    {
        return $this->hasOne(User::class,'id','registered_by');
    }
    public function approvedByUser()
    {
        return $this->hasOne(User::class,'id','approved_by');
    }
    //-------------------------------------------------
    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_brand_status')
            ->select('id','name','slug');
    }

    //-------------------------------------------------
    public function products()
    {
        return $this->hasMany(Product::class, 'vh_st_brand_id','id' )->select(['id', 'name', 'slug', 'vh_st_brand_id','vh_st_store_id']);
    }
    //-------------------------------------------------
    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
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

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    //-------------------------------------------------
    public function scopeIsDefault($query, Store $store)
    {
        return $query->where('is_active', 1)->where('is_default', 1)
            ->where('vh_st_store_id', $store->id);
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $inputs = $request->all();
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // check if name exist
        $item = self::where('name', $inputs['name'])->withTrashed()->first();

        if ($item) {
            $error_message = "This name already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if slug exist
        $item = self::where('slug', $inputs['slug'])->withTrashed()->first();

        if ($item) {
            $error_message = "This slug already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if meta title exist


        $item = new self();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->taxonomy_id_brand_status = $inputs['status']['id'];

        $item->save();

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
        foreach($keywords as $search)
        {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('slug', 'LIKE', '%' . $search . '%')
                    ->orWhere('id', 'LIKE', '%' . $search . '%');
            });
        }

    }
    //-------------------------------------------------
    public function scopeStatusFilter($query, $filter)
    {
        if(!isset($filter['brand_status']))
        {
            return $query;
        }
        $search = $filter['brand_status'];
        $query->whereHas('status', function ($q) use ($search) {
            $q->whereIn('name', $search);
        });

    }
    //-------------------------------------------------
    public static function getList($request)
    {

        $list = self::getSorted($request->filter);
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->dateFilter($request->filter);

        $rows = config('vaahcms.per_page');

        if($request->has('rows'))
        {
            $rows = $request->rows;
        }

        $list = $list->with(['registeredByUser','status',
            'approvedByUser','products.store'])
            ->paginate($rows);

        $response['success'] = true;
        $response['data'] = $list;

        return $response;


    }

    //-------------------------------------------------
    public static function updateList($request)
    {

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
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
                $items->update(['deleted_by'=> auth()->user()->id]);
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
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
                    $items->update(['deleted_by' => null ]);
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
                $list->update(['deleted_by' => auth()->user()->id]);
                $list->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->update(['deleted_by' => null]);
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
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }
    //-------------------------------------------------
    public static function getItem($id)
    {

        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','registeredByUser','status','approvedByUser'])
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

        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
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
            $error_message = "This name already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if slug exist
        $item = self::where('id', '!=', $id)
            ->withTrashed()
            ->where('slug', $inputs['slug'])->first();

        if ($item) {
            $error_message = "This slug already exists".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
        }

        // check if meta title exist


        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->taxonomy_id_brand_status = $inputs['status']['id'];
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        $permission_slug = 'can-update-module';
        if (!\Auth::user()->hasPermission('can-update-module')) {
            return vh_get_permission_denied_response($permission_slug);
        }

        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
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
                $item = self::where('id' , $id)->withTrashed()->first();
                if($item->delete()){
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


        $rules = array(
            'name' => 'required|min:1|max:100',
            'slug' => 'required|min:1|max:100',
            'meta_title' => 'nullable|max:100',
            'meta_description' => 'nullable|max:100',
            'meta_keywords' => 'nullable|array|max:15',
            'meta_keywords.*' => 'max:100',
            'registered_by'=> 'nullable',
            'registered_at'=> 'nullable',
            'approved_by'=> 'nullable',
            'approved_at'=> 'nullable',
            'status'=> 'required',
            'status_notes' => 'nullable|min:1|max:250',


        );



        $custom_messages = array(
            'name.required' => 'The Name field is required.',
            'name.min' => 'The Name field must be at least :min characters.',
            'name.max' => 'The Name field must not exceed :max characters.',
            'slug.required' => 'The Slug field is required.',
            'slug.min' => 'The Slug field must be at least :min characters.',
            'slug.max' => 'The Slug field must not exceed :max characters.',
            'meta_title.max' => 'The Meta Title field must not exceed :max characters.',
            'meta_description.max' => 'The Meta Description field must not exceed :max characters.',
            'meta_keywords.max' => 'The Meta Keywords field must not have more than :max items.',
            'meta_keywords.*' => 'The Meta Keyword field may not have greater than :max characters',
            'registered_by.required_with' => 'The Registered By field is required when Registered At is present.',
            'registered_at.required_with' => 'The Registered At field is required when Registered By is present.',
            'approved_by.required_with' => 'The Approved By field is required when Approved At is present.',
            'approved_at.required_with' => ' The Approved At field is required when Approved By is present.',
            'status_notes.max' => 'The Status Notes may not be greater than :max characters.',
            'status' => 'The Status field is required.',
        );

        $rules['registered_by'] = 'required_with:registered_at';
        $rules['registered_at'] = 'required_with:registered_by';
        $rules['approved_by'] = 'required_with:approved_at';
        $rules['approved_at'] = 'required_with:approved_by';

        $validator = \Validator::make($inputs, $rules,$custom_messages);
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


        $start_date = Carbon::create(2022, 1, 1);
        $end_date = Carbon::create(2023, 1, 1);
        $random_timestamp = mt_rand($start_date->timestamp, $end_date->timestamp);
        $random_date = Carbon::createFromTimestamp($random_timestamp);
        $registered_date = $random_date->toDateTimeString();
        $inputs['registered_at'] = $registered_date;

        $random_approved = mt_rand($start_date->timestamp, $end_date->timestamp);
        $approved_date = Carbon::createFromTimestamp($random_approved);
        $approved_date = $approved_date->toDateTimeString();
        $inputs['approved_at'] = $approved_date;

        $inputs['is_active'] = rand(0,1);

        $taxonomy_status = Taxonomy::getTaxonomyByType('brand-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        $status_id = $status_ids[array_rand($status_ids)];
        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['taxonomy_id_brand_status'] = $status_id;
        $inputs['status']=$status;

        $registered_ids = User::where('is_active',1)->pluck('id')->toArray();
        $registered_id = $registered_ids[array_rand($registered_ids)];
        $registered_by_data = User::where('is_active',1)->where('id',$registered_id)->first();
        $inputs['registered_by'] = $registered_id;
        $inputs['registered_by_user'] = $registered_by_data;
        $image = UploadedFile::fake()->image('file1.png', 600, 600);

        if($image){
            $file_name = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('image/uploads/brands'), $file_name);
            $image_default = $file_name;
            $inputs['image'] = $image_default;
        }


        $approved_ids = User::where('is_active',1)->pluck('id')->toArray();
        $approved_id = $approved_ids[array_rand($approved_ids)];
        $approved_by_data = User::where('is_active',1)->where('id',$approved_id)->first();
        $inputs['approved_by'] =$approved_id;
        $inputs['approved_by_user'] = $approved_by_data;

        $faker = Factory::create();


        $random_words = [];
        $max_words = 10;

        for ($i = 0; $i < $faker->numberBetween(2, $max_words); $i++) {
            $random_words[] = $faker->word;
        }

        $inputs['meta_keywords'] = $random_words;

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
    public static function searchApprovedBy($request)
    {
        $query = $request->input('query');
        $search_approved = User::select('id', 'first_name','last_name')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_approved->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', '%' . $query . '%');
            });
        }
        $search_approved = $search_approved->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_approved;
        return $response;
    }
    //-------------------------------------------------

    public static function searchRegisteredBy($request)
    {
        $query = $request->input('query');
        $search_register = User::select('id', 'first_name','last_name')->where('is_active', '1');
        if($request->has('query') && $request->input('query')){
            $query = $request->input('query');
            $search_register->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', '%' . $query . '%');
            });
        }
        $search_register = $search_register->limit(10)->get();
        $response['success'] = true;
        $response['data'] = $search_register;
        return $response;
    }
    //-------------------------------------------------

    public static function searchBrandStatus($request)
    {
        $query = $request->input('query');
        if(empty($query)) {
            $item = Taxonomy::getTaxonomyByType('brand-status');
        } else {
            $tax_type = TaxonomyType::getFirstOrCreate('brand-status');
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


    public static function uploadImage($request){

        if($request->hasFile('image')){
            $file = $request->file('image');
            $file_name = time().'.' .$file->getClientOriginalExtension();
            $file->move(public_path('image/uploads/brands'), $file_name);
            $response['image_name'] = $file_name;
            return $response;
        }
    }

    //-------------------------------------------------


}
