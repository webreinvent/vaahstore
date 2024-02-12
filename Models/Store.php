<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Models\Taxonomy;
use WebReinvent\VaahCms\Models\TaxonomyType;
use Faker\Factory;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use VaahCms\Modules\Store\Models\Currency;

class Store extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_stores';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'name', 'slug', 'is_multi_currency',
        'is_multi_lingual', 'is_multi_vendor', 'allowed_ips',
        'is_default', 'is_active', 'taxonomy_id_store_status',
        'status_notes', 'meta',
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
            'is_multi_currency',
            'is_multi_lingual',
            'is_multi_vendor',
            'is_default',
            'is_active',

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
        $empty_item['is_multi_currency'] = 0;
        $empty_item['is_multi_lingual'] = 0;
        $empty_item['is_multi_vendor'] = 0;
        $empty_item['is_default'] = 0;
        $empty_item['is_active'] = 0;
        $empty_item['status'] = null;
        $empty_item['allowed_ips'] =[];
        return $empty_item;
    }

    //-------------------------------------------------


    public function status(){
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id_store_status')
            ->select(['id','name', 'slug']);
    }

    //-------------------------------------------------
    public function currenciesData(){
        return $this->hasMany(Currency::class, 'vh_st_store_id', 'id')
            ->where('is_active', 1)
            ->select(['vh_st_currencies.vh_st_store_id','vh_st_currencies.name',
                'vh_st_currencies.code','vh_st_currencies.symbol','vh_st_currencies.is_default']);
    }

    //-------------------------------------------------
    public function lingualData(){
        return $this->hasMany(Lingual::class, 'vh_st_store_id', 'id')
            ->where('is_active', 1)
            ->select(['vh_st_lingual.vh_st_store_id','vh_st_lingual.name','vh_st_lingual.is_default']);
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

        // check if name exist
        $item = self::where('name', $inputs['name'])->withTrashed()->first();

        // check if name starts with numbers and has at least one alphabet
        if (preg_match('/^\d/', $inputs['name']) && !preg_match('/[a-zA-Z]/', $inputs['name'])) {
            $response['errors'][] ="The Name Field should have atleast one letter if it starts with numbers.";
            return $response;
        }

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

        if ($inputs['is_default'] == 1){
            self::removePreviousDefaults();
        }


        $item = new self();
        $item->fill($inputs);

        if(isset($item->allowed_ips))
        {
            $item->allowed_ips = json_encode($inputs['allowed_ips']);

        }

        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        if(!empty($inputs['currencies']) && $item->is_multi_currency == 1) {
            foreach ($inputs['currencies'] as $key => $value) {

                $record = new Currency();
                $record->vh_st_store_id = $item->id;
                $record->name = $value['name'];

                if (!empty($inputs['default_currency'])) {
                    if ($inputs['default_currency']['name'] == $value['name']) {
                        $record->is_default = 1;
                    }
                } else {
                    $record->is_default = 0;
                }

                $record->is_active = 1;
                $record->save();
            }
        }

        if(!empty($inputs['languages']) && $item->is_multi_lingual == 1) {
            foreach ($inputs['languages'] as $key => $value) {

                $record = new Lingual();
                $record->vh_st_store_id = $item->id;
                $record->name = $value['name'];

                if (!empty($inputs['default_language'])) {
                    if ($inputs['default_language']['name'] == $value['name']) {
                        $record->is_default = 1;
                    }
                } else {
                    $record->is_default =0;
                }

                $record->is_active = 1;
                $record->save();
            }
        }

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }

    //-------------------------------------------------

    public static function searchCurrencies($request){

        $query = $request['filter']['q']['query'];

        if(empty($query)) {
            $currency = Taxonomy::getTaxonomyByType('Currency')->take(5);
        } else {
            $status = TaxonomyType::getFirstOrCreate('Currency');
            $item =array();

            if(!$status){
                return $item;
            }
            $currency = Taxonomy::whereNotNull('is_active')
                ->where('vh_taxonomy_type_id',$status->id)
                ->where('name', 'LIKE', '%' . $query . '%')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $currency;
        return $response;

    }

    //-------------------------------------------------

    public static function searchLanguages($request){

        $query = $request['filter']['q']['query'];

        if(empty($query)) {
            $language = Taxonomy::getTaxonomyByType('Language')->take(5);
        } else {
            $status = TaxonomyType::getFirstOrCreate('Language');
            $item =array();

            if(!$status){
                return $item;
            }
            $language = Taxonomy::whereNotNull('is_active')
                ->where('vh_taxonomy_type_id',$status->id)
                ->where('name', 'LIKE', '%' . $query . '%')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $language;
        return $response;

    }

    //-------------------------------------------------

    public static function removePreviousDefaults(){

        self::where('is_default', 1)
            ->update(['is_default' => 0]);

    }
    //-------------------------------------------------

    public static function validation($inputs){

        $validated_data = validator($inputs,[
            'name' => [
                'required',
                'regex:/^(?![-\/_+]+$)(?![0-9]+$)[a-zA-Z0-9\s\-_\.,-\/_+]+$/',
                'max:50'
            ],
            'slug' => [
                'required',
                'max:50',
            ],
            'taxonomy_id_store_status' => 'required',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:250'
            ],
            'currencies' => 'required_if:is_multi_currency,1',
            'currency_default' => '',
            'languages' => 'required_if:is_multi_lingual,1',
            'language_default' => '',
            'allowed_ips.*' => 'ip',
        ],
            [
                'name.max' => 'The Name field cannot be more than :max characters.',
                'name.required' => 'The Name field is required',
                'slug.required' => 'The Slug field is required',
                'slug.max' => 'The Slug field cannot be more than :max characters.',
                'name.regex' => 'The Name field format is not valid.',
                'taxonomy_id_store_status.required' => 'The Status field is required',
                'notes.required' => 'The Store Notes field is required',
                'currencies.required_if' => 'The Currencies field is required when Is Multi Currency is "Yes".',
                'languages.required_if' => 'The Languages field is required when Is Multi Lingual is "Yes".',
                'status_notes.required_if' => 'The Status notes field is required for "Rejected" Status',
                'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
                'allowed_ips.*.ip' => 'The Allowed IPs address field must contain valid ip address',
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

        if($status == 'all')
        {
            return $query;
        }

        $query->whereHas('status', function ($query) use ($status) {
            $query->where('name', $status)
                ->orWhere('slug',$status);
        });

    }

    //-------------------------------------------------

    public function scopeDefaultFilter($query, $filter)
    {

        if(!isset($filter['is_default'])
            || is_null($filter['is_default'])
            || $filter['is_default'] === 'null'
        )
        {
            return $query;
        }

        $default = $filter['is_default'][0];
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

    public function scopeMultiCurrencyFilter($query, $filter)
    {
        if(!isset($filter['is_multi_currency'])
            || is_null($filter['is_multi_currency'])
            || $filter['is_multi_currency'] === 'null'
        )
        {
            return $query;
        }

        $is_multi_currency = $filter['is_multi_currency'][0];
        if($is_multi_currency == 'true')
        {
            return $query->where(function ($q){
                $q->Where('is_multi_currency', 1);
            });
        }
        else{
            return $query->where(function ($q){
                $q->whereNull('is_multi_currency')
                    ->orWhere('is_multi_currency', 0);
            });
        }

    }

    //-------------------------------------------------

    public function scopeMultiVendorFilter($query, $filter)
    {
        if(!isset($filter['is_multi_vendor'])
            || is_null($filter['is_multi_vendor'])
            || $filter['is_multi_vendor'] === 'null'
        )
        {
            return $query;
        }

        $is_multi_vendor = $filter['is_multi_vendor'][0];
        if($is_multi_vendor == 'true')
        {
            return $query->where(function ($q){
                $q->Where('is_multi_vendor', 1);
            });
        }
        else{
            return $query->where(function ($q){
                $q->whereNull('is_multi_vendor')
                    ->orWhere('is_multi_vendor', 0);
            });
        }
    }

    //-------------------------------------------------

    public function scopeMultiLanguageFilter($query, $filter)
    {
        if(!isset($filter['is_multi_language'])
            || is_null($filter['is_multi_language'])
            || $filter['is_multi_language'] === 'null'
        )
        {
            return $query;
        }

        $is_multi_language = $filter['is_multi_language'][0];

        if($is_multi_language == 'true')
        {
            return $query->where(function ($q){
                $q->Where('is_multi_lingual', 1);
            });
        }
        else{
            return $query->where(function ($q){
                $q->whereNull('is_multi_lingual')
                    ->orWhere('is_multi_lingual', 0);
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
    public static function getList($request)
    {
//dd($request->filter);
        $list = self::getSorted($request->filter)->with('status');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);
        $list->defaultFilter($request->filter);
        $list->multiCurrencyFilter($request->filter);
        $list->multiLanguageFilter($request->filter);
        $list->multiVendorFilter($request->filter);
        $list->dateFilter($request->filter);
        $list->storeIdFilter($request->filter);

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
                $items->update(['deleted_by' => $user_id,'is_default' => null]);
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
            $response['failed'] = true;
            $response['messages'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        foreach($items_id as $item_id)
        {
            self::deleteRelatedRecords($item_id);
        }
        self::whereIn('id', $items_id)->forceDelete();
        StorePaymentMethod::deleteStores($items_id);
        Vendor::deleteStores($items_id);
        Product::deleteStores($items_id);

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
                    $items->update(['deleted_by' => auth()->user()->id,'is_default' => null]);
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
                    foreach($items_id as $item_id)
                    {
                        self::deleteRelatedRecords($item_id);
                    }
                    self::whereIn('id', $items_id)->forceDelete();
                    StorePaymentMethod::deleteStores($items_id);
                    Vendor::deleteStores($items_id);
                    Product::deleteStores($items_id);
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
                $list->update(['deleted_by' => $user_id,'is_default' => null]);
                $list->delete();
                break;
            case 'restore-all':
                $list->update(['deleted_by' => null]);
                $list->restore();
                break;
            case 'delete-all':
                $items_id = self::all()->pluck('id')->toArray();
                foreach ($items_id as $item_id)
                {
                    self::deleteRelatedRecords($item_id);
                }
                $list->forceDelete();
                StorePaymentMethod::deleteStores($items_id);
                Vendor::deleteStores($items_id);
                Product::deleteStores($items_id);
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser', 'status', 'currenciesData', 'lingualData'])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }

        $item->default_currency = null;
        $item->currencies = [];
        if ($item->currenciesData->isNotEmpty()){

            $currency_default_record = $item->currenciesData()->where('is_default',1)
                ->select('name',)->get();
            if($currency_default_record->isNotEmpty()){

                $item->default_currency = $currency_default_record[0];
            }

            $currencies = [];
            foreach ($item->currenciesData as $key => $value) {
                $currencies[$key]['name'] = $value['name'];
            }
            $item->currencies = $currencies;

        }

        $item->default_language = null;
        $item->languages = [];
        if ($item->lingualData->isNotEmpty()){

            $language_default_record = $item->lingualData()->where('is_default',1)->select('name')->get();

            if($language_default_record->isNotEmpty()){

                $item->default_language = $language_default_record[0];
            }

            $languages = [];
            foreach ($item->lingualData as $key => $value) {
                $languages[$key]['name'] = $value['name'];
            }
            $item->languages = $languages;
        }

        $item->allowed_ips = json_decode($item->allowed_ips);

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

        if ($inputs['is_default'] == 1){
            self::removePreviousDefaults();
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

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);

        $item->allowed_ips = json_encode($inputs['allowed_ips']);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        if(!empty($inputs['currencies'])) {
            Currency::where('vh_st_store_id', $item->id)->update(['is_active' => 0, 'is_default' => 0]);
            foreach ($inputs['currencies'] as $key => $v) {

                Currency::updateOrInsert(
                    ['vh_st_store_id' => $item->id, 'name' => $v['name']],
                    ['is_active' => 1]
                );

            }

            if (!empty($inputs['default_currency'])){
                Currency::where(['vh_st_store_id' => $item->id, 'name' => $inputs['default_currency']['name'],
                    'is_active' => 1])->update(['is_default' => 1]);
            }
        }else{
            Currency::where('vh_st_store_id', $item->id)->update(['is_active' => 0, 'is_default' => 0]);
        }

        if(!empty($inputs['languages'])) {
            Lingual::where('vh_st_store_id', $item->id)->update(['is_active' => 0, 'is_default' => 0]);

            foreach ($inputs['languages'] as $key => $v) {

                Lingual::updateOrInsert(
                    ['vh_st_store_id' => $item->id, 'name' => $v['name']],
                    ['is_active' => 1]
                );

            }

            if (!empty($inputs['default_language'])){
                Lingual::where(['vh_st_store_id' => $item->id, 'name' => $inputs['default_language']['name'],
                    'is_active' => 1])->update(['is_default' => 1]);
            }
        }else{
            Lingual::where('vh_st_store_id', $item->id)->update(['is_active' => 0, 'is_default' => 0]);
        }

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request, $id)
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['messages'][] = 'Record does not exist.';
            return $response;
        }
        self::deleteRelatedRecords($item->id);
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
                $item->is_default = null;
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
            $item->allowed_ips =json_encode($inputs['allowed_ips']);
            $item->slug = Str::slug($inputs['slug']);
            $item->save();

            if(!empty($inputs['currencies']) && $item->is_multi_currency == 1) {
                foreach ($inputs['currencies'] as $key => $value) {

                    $record = new Currency();
                    $record->vh_st_store_id = $item->id;
                    $record->name = $value['name'];

                    if (!empty($inputs['default_currency'])) {
                        if ($inputs['default_currency']['name'] == $value['name']) {
                            $record->is_default = 1;
                        }
                    } else {
                        $record->is_default = $key == 0 ? 1 : 0;
                    }

                    $record->is_active = 1;
                    $record->save();
                }
            }

            if(!empty($inputs['languages']) && $item->is_multi_lingual == 1) {
                foreach ($inputs['languages'] as $key => $value) {

                    $record = new Lingual();
                    $record->vh_st_store_id = $item->id;
                    $record->name = $value['name'];

                    if (!empty($inputs['default_language'])) {
                        if ($inputs['default_language']['name'] == $value['name']) {
                            $record->is_default = 1;
                        }
                    } else {
                        $record->is_default = $key == 0 ? 1 : 0;
                    }

                    $record->is_active = 1;
                    $record->save();
                }
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
        $faker = Factory::create();
        $taxonomy_status = Taxonomy::getTaxonomyByType('store-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        $status_id = $status_ids[array_rand($status_ids)];

        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['taxonomy_id_store_status'] = $status_id;
        $inputs['status']=$status;

        $inputs['is_multi_currency'] = rand(0,1);
        $inputs['is_multi_lingual'] =  rand(0,1);

        $currency_list = Taxonomy::getTaxonomyByType('Currency')->toArray();
        $random_currencies = array_rand($currency_list, 3);
        $selected_currencies = [];
        $inputs['default_currency'] = null;

        foreach ($random_currencies as $index) {
            $selected_currencies[] = $currency_list[$index];
        }

        if($inputs['is_multi_currency'] == 1)
        {
            $inputs['default_currency'] = $selected_currencies[0];
            foreach ($selected_currencies as $currency)
            {

                $inputs['currencies'][] = $currency;
            }
        }

        $language_list = Taxonomy::getTaxonomyByType('Language')->toArray();
        $random_languages = array_rand($language_list, 3);
        $selected_languages = [];
        $inputs['default_language'] = null;
        foreach ($random_languages as $index) {
            $selected_languages[] = $language_list[$index];
        }
        if($inputs['is_multi_lingual'] == 1)
        {
            $inputs['default_language'] = $selected_languages[0];
            foreach ($selected_languages as $language)
            {

                $inputs['languages'][] = $language;
            }

        }

        $inputs['allowed_ips'][] = $faker->ipv4;
        $inputs['is_multi_vendor'] = 1;
        $inputs['is_active'] = 1;
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


    public function scopestoreIdFilter($query, $filter)
    {

        if(!isset($filter['store_ids']))
        {
            return $query;
        }
        $search = $filter['store_ids'];
        $query->where(function ($q) use ($search) {
            $q->where(function ($q) use ($search) {
                $q->whereIn('id', $search );

            });
        });
    }

    //------------------------------------------------
    public static function deleteRelatedRecords($id)
    {
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }
        self::deletePaymentMethod($item->id);
        self::deleteVendor($item->id);
        self::deleteProduct($item->id);
    }

    //------------------------------------------------

    public static function deletePaymentMethod($id)
    {
        $response=[];
        $is_exist = StorePaymentMethod::where('vh_st_store_id',$id)
            ->withTrashed()
            ->get();

        if($is_exist){
            StorePaymentMethod::where('vh_st_store_id',$id)->withTrashed()->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;
    }

    //------------------------------------------------

    public static function deleteVendor($id)
    {

        $response=[];
        $is_exist = Vendor::where('vh_st_store_id',$id)
            ->withTrashed()
            ->get();

        if($is_exist){
            $vendor_ids = Vendor::where('vh_st_store_id',$id)->withTrashed()->pluck('id')->toArray();
            foreach($vendor_ids as $vendor_id)
            {
                self::deleteVendorRelatedRecords($vendor_id);
            }
            Vendor::where('vh_st_store_id',$id)->withTrashed()->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }

    //------------------------------------------------

    public static function deleteProduct($id)
    {

        $response=[];
        $is_exist = Product::where('vh_st_store_id',$id)
            ->withTrashed()
            ->get();

        if($is_exist){
            $product_ids = Product::where('vh_st_store_id',$id)->withTrashed()->pluck('id')->toArray();
            foreach($product_ids as $product_id)
            {
                Product::deleteRelatedRecords($product_id);
            }
            Product::where('vh_st_store_id',$id)->withTrashed()->forcedelete();
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }
        return $response;

    }

    //------------------------------------------------

    public static function deleteVendorRelatedRecords($id)
    {

        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['messages'][] = 'Record does not exist.';
            return $response;
        }
        $is_product_exist = ProductVendor::where('vh_st_vendor_id',$item->id)->withTrashed()->get();
        if($is_product_exist)
        {
            ProductVendor::where('vh_st_vendor_id',$id)->withTrashed()->forcedelete();
        }
        $is_warehouse_exist = Warehouse::where('vh_st_vendor_id',$item->id)->withTrashed()->get();
        if($is_warehouse_exist)
        {
            Warehouse::where('vh_st_vendor_id',$id)->withTrashed()->forcedelete();
        }

        $is_stock_exist = ProductStock::where('vh_st_vendor_id',$item->id)->withTrashed()->get();
        if($is_stock_exist)
        {
            ProductStock::where('vh_st_vendor_id',$id)->withTrashed()->forcedelete();

        }

    }


}
