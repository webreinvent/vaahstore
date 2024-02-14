<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Faker\Factory;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class CustomerGroup extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_customer_groups';
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
        'taxonomy_id_customer_groups_status',
        'status_notes',
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
        $data['empty_item'] = [
            'status' => null,
        ];
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
    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_customer_groups_status')
            ->select('id','name','slug');
    }

    //-------------------------------------------------
    public function customers()
    {
        return $this->belongsToMany(User::class, 'vh_st_user_customer_groups','vh_st_customer_group_id','vh_st_user_id')
            ->select('vh_users.id','vh_users.first_name','vh_users.last_name','vh_users.display_name');
    }
    //-------------------------------------------------
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'vh_st_customer_group_id');
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

        $item = new self();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();
        if (isset($inputs['customers']) && is_array($inputs['customers'])) {
            $customer_ids = collect($inputs['customers'])->pluck('id')->toArray();
            $item->customers()->sync($customer_ids, function ($pivot) use ($item) {
                $pivot->group_id = $item->id;
            });
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
//    public function scopeIsActiveFilter($query, $filter)
//    {
//
//        if(!isset($filter['is_active'])
//            || is_null($filter['is_active'])
//            || $filter['is_active'] === 'null'
//        )
//        {
//            return $query;
//        }
//        $is_active = $filter['is_active'];
//
//        if($is_active === 'true' || $is_active === true)
//        {
//            return $query->where('is_active', 1);
//        } else{
//            return $query->where(function ($q){
//                $q->whereNull('is_active')
//                    ->orWhere('is_active', 0);
//            });
//        }
//
//    }
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
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('slug', 'LIKE', '%' . $search . '%')
                    ->orWhere('id', 'LIKE', '%' . $search . '%');
            });
        }

    }
    //-------------------------------------------------

    public function scopeStatusFilter($query, $filter)
    {

        if(!isset($filter['status']))
        {
            return $query;
        }
        $status = $filter['status'];
        $query->whereHas('status' , function ($q) use ($status){
            $q->whereIn('name' ,$status);
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
    public function scopeCustomerFilter($query, $filter)
    {
        if (!isset($filter['customers']) || is_null($filter['customers']) || $filter['customers'] === 'null') {
            return $query;
        }

        $display_names = $filter['customers'];

        return $query->whereHas('customers', function ($q) use ($display_names) {
            $q->whereIn('display_name', $display_names);
        });
    }
    //-------------------------------------------------

    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('status','customers','orderItems');
//        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->dateRangeFilter($request->filter);
        $list->customerFilter($request->filter);
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

        $status = Taxonomy::getTaxonomyByType('customer-groups-status');
        $approve_id = $status->where('name','Approved')->pluck('id')->first();
        $reject_id = $status->where('name','Rejected')->pluck('id')->first();
        $pending_id =$status->where('name','Pending')->pluck('id')->first();

        $items = self::whereIn('id', $items_id)
            ->withTrashed();

        switch ($inputs['type']) {
            case 'approve':
                $items->update(['taxonomy_id_customer_groups_status' => $approve_id]);
                break;
            case 'reject':
                $items->update(['taxonomy_id_customer_groups_status' => $reject_id]);
                break;
            case 'pending':
                $items->update(['taxonomy_id_customer_groups_status' => $pending_id]);
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

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        foreach ($items_id as $item_id)
        {
            $item = self::where('id', $item_id)->withTrashed()->first();
            $item->customers()->detach();
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
        $inputs = $request->all();
        $status = Taxonomy::getTaxonomyByType('customer-groups-status');
        $approve_id = $status->where('name','Approved')->pluck('id')->first();
        $reject_id = $status->where('name','Rejected')->pluck('id')->first();
        $pending_id =$status->where('name','Pending')->pluck('id')->first();

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
//            $list->isActiveFilter($request->filter);
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
                if (isset($items_id) && count($items_id) > 0) {
                    $user_id = auth()->user()->id;
                    $items->update(['deleted_by' => $user_id]);
                    self::whereIn('id', $items_id)->delete();

                }
                break;
            case 'restore':
                if (isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->restore();
                    $items->update(['deleted_by' => null]);
                }
                break;
            case 'delete':
                if(isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->forceDelete();
                }
                break;
            case 'pending-all':
                $list->update(['taxonomy_id_customer_groups_status' => $pending_id]);
                break;
            case 'reject-all':
                $list->update(['taxonomy_id_customer_groups_status' => $reject_id]);
                break;
            case 'approve-all':
                $list->update(['taxonomy_id_customer_groups_status' => $approve_id]);
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
                $items_id = self::withTrashed()->pluck('id')->toArray();
                foreach ($items_id as $item_id) {

                    $item = self::where('id', $item_id)->withTrashed()->first();
                    $item->customers()->detach();
                }
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
    public static function getItem($id)
    {

        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','status','customers','orderItems'])
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

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();
        if (isset($inputs['customers']) && is_array($inputs['customers'])) {
            $product_ids = collect($inputs['customers'])->pluck('id')->toArray();
            $item->customers()->sync($product_ids, function ($pivot) use ($item) {
                $pivot->group_id = $item->id;
            });
        }

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

        $item->customers()->detach();
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
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
            'customers'=>'required',
            'taxonomy_id_customer_groups_status'=> 'required',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:150'
            ],
        ],
            [
                'name.required'=>'The Name field is required.',
                'name.max'=>'The Name  field must not exceed :max characters.',
                'slug.required'=>'The Slug field is required.',
                'slug.max'=>'The Slug  field must not exceed :max characters.',
                'customers.required'=>'The Customers field is required.',
                'taxonomy_id_customer_groups_status.required' => 'The Status field is required.',
                'status_notes.required_if' => 'The Status notes field is required for "Rejected" Status.',
                'status_notes.max' => 'The Status notes field must not exceed :max characters.',
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
        $inputs['customer_count'] = rand(20,50);
        $inputs['order_count'] = rand(20,60);
        $taxonomy_status = Taxonomy::getTaxonomyByType('customer-groups-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        $status_id = $status_ids[array_rand($status_ids)];

        $status = $taxonomy_status->where('id',$status_id)->first();

        $inputs['taxonomy_id_customer_groups_status'] = $status_id;
        $inputs['status']=$status;
        $customer_group_data = \VaahCms\Modules\Store\Models\User::whereHas('activeRoles', function ($query) {
            $query->where('slug', 'customer');
        })->where('is_active', 1)->get();

        if ($customer_group_data->isEmpty()) {
            $error_message = "No customer exists.";
            $response['errors'][] = $error_message;
            return $response;

        }

        $randomCustomerGroup = $customer_group_data->random();

        $inputs['customers'] = [
            'id' => $randomCustomerGroup->id,
            'first_name' => $randomCustomerGroup->first_name,
            'last_name' => $randomCustomerGroup->last_name,
            'display_name' => $randomCustomerGroup->display_name,
        ];
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
    public static function searchCustomers($request){
        $active_customers = User::select('id', 'first_name', 'last_name','display_name')
            ->whereHas('activeRoles', function ($query) {
                $query->where('slug', 'customer');
            })
            ->where('is_active', 1);

        if ($request->has('query') && $request->input('query')) {
            $query = $request->input('query');

            $active_customers->where(function ($q) use ($query) {
                $q->where('display_name', 'LIKE', '%' . $query . '%')
                    ->orWhere('first_name', 'LIKE', '%' . $query . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $query . '%');
            });
        }

        $customers = $active_customers->limit(10)->get()->map(function ($customer) {
            $customer['name'] = $customer['display_name'] ;
            return $customer;
        });

        $response['success'] = true;
        $response['data'] = $customers;
        return $response;

    }
    //-------------------------------------------------
    public static function getCustomers($request): array {
        $customer = User::select('id', 'first_name', 'last_name','display_name')
            ->whereHas('activeRoles', function ($query) {
                $query->where('slug', 'customer');
            });

        if ($request->has('query') && $request->input('query')) {
            $query = $request->input('query');

            $customer->where(function ($q) use ($query) {
                $q->where('display_name', 'LIKE', '%' . $query . '%')
                    ->orWhere('first_name', 'LIKE', '%' . $query . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $query . '%');
            });
        }

        $customers = $customer->limit(10)->get();

        $response['success'] = true;
        $response['data'] = $customers;
        return $response;
    }
    //-------------------------------------------------
    //-------------------------------------------------

    public static function getCustomersBySlug($request)
    {
        $queries = $request->input('filter.customers');

        $customers = User::where(function ($q) use ($queries) {
            $q->where(function ($query) use ($q, $queries) {
                foreach ($queries as $query) {
                    $q->orWhere('display_name', 'LIKE', '%' . $query . '%');
                }
            });
        })->select('id', 'first_name', 'last_name','display_name')->get();

        $response['success'] = true;
        $response['data'] = $customers;
        return $response;
    }


}
