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
use WebReinvent\VaahExtend\Facades\VaahCountry;
use VaahCms\Modules\Store\Models\Vendor;
use Illuminate\Support\Facades\Auth;
class Warehouse extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_warehouses';
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
        'slug', 'vh_st_vendor_id', 'country', 'state',
        'city', 'taxonomy_id_warehouse_status', 'status_notes',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
        'address_1',
        'address_2',
        'postal_code',
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
        foreach ($fillable as $column) {
            $empty_item[$column] = null;
        }

        return $empty_item;
    }


    //-------------------------------------------------
    public function status()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id_warehouse_status')
            ->select(['id', 'name', 'slug']);
    }

    //-------------------------------------------------

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'id', 'vh_st_vendor_id')->withTrashed()->select(['id', 'name','deleted_at']);
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

        $item = self::where('name', $inputs['name'])
            ->where('vh_st_vendor_id', $inputs['vh_st_vendor_id'])
            ->withTrashed()
            ->first();
        if ($item) {
            $error_message = "This Warehouse is already exist with this Vendor".($item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
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

        if (!isset($filter['sort'])) {
            return $query->orderBy('id', 'desc');
        }

        $sort = $filter['sort'];


        $direction = Str::contains($sort, ':');

        if (!$direction) {
            return $query->orderBy($sort, 'asc');
        }

        $sort = explode(':', $sort);

        return $query->orderBy($sort[0], $sort[1]);
    }

    //-------------------------------------------------
    public function scopeIsActiveFilter($query, $filter)
    {

        if (!isset($filter['is_active'])
            || is_null($filter['is_active'])
            || $filter['is_active'] === 'null'
        ) {
            return $query;
        }
        $is_active = $filter['is_active'];

        if ($is_active === 'true' || $is_active === true) {
            return $query->where('is_active', 1);
        } else {
            return $query->where(function ($q) {
                $q->whereNull('is_active')
                    ->orWhere('is_active', 0);
            });
        }

    }

    //-------------------------------------------------
    public function scopeTrashedFilter($query, $filter)
    {

        if (!isset($filter['trashed'])) {
            return $query;
        }
        $trashed = $filter['trashed'];

        if ($trashed === 'include') {
            return $query->withTrashed();
        } else if ($trashed === 'only') {
            return $query->onlyTrashed();
        }

    }

    //-------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if (!isset($filter['q'])) {
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
    public function scopeCountryStateFilter($query, $filter)
    {

        if (!isset($filter['state_city'])) {
            return $query;
        }

            $search = $filter['state_city'];
            $query->where(function ($country_state) use ($search) {
                $country_state->where('state', 'LIKE', '%' . $search . '%')
                    ->orWhere('city', 'LIKE', '%' . $search . '%')
                    ->orWhere('postal_code', 'LIKE', '%' . $search . '%');
            });


    }

    public function scopeCountryFilter($query, $filter)
    {
        if (!isset($filter['country'])) {
            return $query;
        }
        $keywords = $filter['country'];
        $query->whereIn('country', $keywords);
        return $query;
    }

    //-------------------------------------------------

    public function scopeStatusFilter($query, $filter)
    {

        if (!isset($filter['status'])) {
            return $query;
        }
        $status = $filter['status'];
        $query->whereHas('status', function ($q) use ($status) {
            $q->whereIn('slug', $status);
        });

    }


    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('status');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->dateFilter($request->filter);
        $list->countryStateFilter($request->filter);
        $list->countryFilter($request->filter);
        $rows = config('vaahcms.per_page');

        if ($request->has('rows')) {
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

        if (isset($inputs['items'])) {
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

        if (isset($inputs['items'])) {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();

            $items = self::whereIn('id', $items_id)
                ->withTrashed();
        }

        $list = self::query();

        if ($request->has('filter')) {
            $list->getSorted($request->filter);
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
            $list->countryStateFilter($request->filter);
        }

        switch ($type) {
            case 'deactivate':
                if ($items->count() > 0) {
                    $items->update(['is_active' => null]);
                }
                break;
            case 'activate':
                if ($items->count() > 0) {
                    $items->update(['is_active' => 1]);
                }
                break;
            case 'trash':
                if (isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->delete();
                    $items->update(['deleted_by' => auth()->user()->id]);
                }
                break;
            case 'restore':
                if (isset($items_id) && count($items_id) > 0) {
                    self::whereIn('id', $items_id)->restore();
                    $items->update(['deleted_by' => null]);
                }
                break;
            case 'delete':
                if (isset($items_id) && count($items_id) > 0) {
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

                if (!config('store.is_dev')) {
                    $response['success'] = false;
                    $response['errors'][] = 'User is not in the development environment.';

                    return $response;
                }

                preg_match('/-(.*?)-/', $type, $matches);

                if (count($matches) !== 2) {
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser', 'status', 'vendor'])
            ->withTrashed()
            ->first();

        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: ' . $id;
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


        $item = self::where('id', $id)->withTrashed()->first();

        // check if name exist
        $existing_item = self::where('id', '!=', $id)
            ->where('name', $inputs['name'])
            ->where('vh_st_vendor_id', $inputs['vh_st_vendor_id'])
            ->withTrashed()
            ->first();

        if ($existing_item) {
            $error_message = "This Warehouse is already exist with this Vendor".($existing_item->deleted_at?' in trash.':'.');
            $response['errors'][] = $error_message;
            return $response;
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
        switch ($type) {
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

        $rules = array(
                        'name' => 'required|max:100',
            'slug' => 'required|max:100',
            'vendor' => 'required',
            'country' => 'required',
            'state' => 'nullable|max:100',
            'city' => 'nullable|max:100',
            'address_1' => 'required|max:150',
            'address_2' => 'nullable|max:150',
            'postal_code' => 'nullable|max:10',
            'status' => 'required',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:100'
            ],
        );
        $messages = array(
            'name.required' => 'The Name field is required.',
            'name.max' => 'The Name field may not be greater than :max characters.',
            'slug.required' => 'The Slug field is required.',
            'slug.max' => 'The slug field may not be greater than :max characters.',
            'vendor.required' => 'The Vendor field is required.',
            'country.required' => 'The Country field is required.',
            'state.max' => 'The State field may not be greater than :max characters.',
            'address_1.required' => 'The Address 1 field is required.',
            'address_1.max' => 'The Address 1 field may not be greater than :max characters.',
            'city.max' => 'The City field may not be greater than :max characters.',
            'postal_code.max' => 'The Postal Code field may not be greater than :max digits.',
            'status.required' => 'The Status field is required.',
            'status_notes.required_if' => 'The Status notes field is required for "Rejected" Status',
            'status_notes.max' => 'The Status notes field may not be greater than :max characters.',

        );

        $validator = \Validator::make($inputs, $rules,$messages);
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
        }$faker = Factory::create();
        $countries = array_column(VaahCountry::getList(), 'name');
        $inputs = $fillable['data']['fill'];
        $inputs['name'] = $faker->country;
        $inputs['country'] = $countries[array_rand($countries)];
        $inputs['is_active'] = 1;

        $inputs['slug'] = $faker->slug;
        $inputs['postal_code'] = $faker->randomNumber(6);
        $inputs['address_1'] = $faker->address;
        $inputs['address_2'] = $faker->secondaryAddress;
        $taxonomy_status = Taxonomy::getTaxonomyByType('warehouse-status');

        $status_ids = $taxonomy_status->pluck('id')->toArray();

        $status_id = $status_ids[array_rand($status_ids)];

        $status = $taxonomy_status->where('id',$status_id)->first();

        $inputs['taxonomy_id_warehouse_status'] = $status_id;
        $inputs['status']=$status;

        $vendor_ids = Vendor::where('is_active',1)->pluck('id')->toArray();
        $inputs['vh_st_vendor_id'] = null;
        $inputs['vendor'] = null;

        if (!empty($vendor_ids)) {
            $vendor_id = $vendor_ids[array_rand($vendor_ids)];
            $vendor_data = Vendor::where('is_active', 1)->where('id', $vendor_id)->first();
            $inputs['vh_st_vendor_id'] = $vendor_id;
            $inputs['vendor'] = $vendor_data;
        }


        /*
         * You can override the filled variables below this line.
         * You should also return relationship from here
         */
//        $inputs['postal_code'] = $faker->randomNumber(6);
        if(!$is_response_return){
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }

    //-------------------------------------------------
    public static function searchActiveVendor($request){
        $addedBy = Vendor::select('id', 'name','slug')->where('is_active',1);
        if ($request->has('query') && $request->input('query')) {
            $addedBy->where('name', 'LIKE', '%' . $request->input('query') . '%');
        }
        $addedBy = $addedBy->limit(10)->get();

        $response['success'] = true;
        $response['data'] = $addedBy;
        return $response;

    }
    //-------------------------------------------------
    public static function defaultVendor($request)
    {
        $default_vendor = Vendor::where(['is_active'=>1,'deleted_at'=>null,'is_default'=>1])->get()->first();


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

    //----------------------------------------------------------

    public static function warehouseStockInBarChart($request)
    {
        $inputs = $request->all();

        $start_date = isset($request->start_date) ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfDay();
        $end_date = isset($request->end_date) ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();


        $stock_data = ProductStock::select('vh_st_warehouse_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->groupBy('vh_st_warehouse_id')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->orderBy('total_quantity', 'asc') // Sort in descending order to get the top
            ->take(7)
            ->get();

        $chart_series = [];
        $chart_categories = [];

        foreach ($stock_data as $stock) {
            $warehouse = self::find($stock->vh_st_warehouse_id);

            if ($warehouse) {
                $chart_series[] = [
                    'name' => $warehouse->name,
                    'data' => [(int)$stock->total_quantity]
                ];
                $chart_categories[] = $warehouse->name;
            }
        }

        return [
            'data' => [
                'chart_series' => [
                    'quantity_data' => $stock_data->pluck('total_quantity'),
                ],
                'chart_options' => [
                    'xaxis' => [
                        'categories' => $chart_categories
                    ],
                ]
            ]
        ];
    }


}
