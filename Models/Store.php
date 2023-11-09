<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\TaxonomyType;
use Faker\Factory;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use VaahCms\Modules\Store\Models\Currency;

class Store extends Model
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
        if($item->allowed_ips)
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

                if (!empty($inputs['currency_default'])) {
                    if ($inputs['currency_default']['name'] == $value['name']) {
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

                if (!empty($inputs['language_default'])) {
                    if ($inputs['language_default']['name'] == $value['name']) {
                        $record->is_default = 1;
                    }
                } else {
                    $record->is_default = $key == 0 ? 1 : 0;
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
            'name' => 'required|max:100|regex:/^[a-zA-Z\.\s]+$/',
            'slug' => 'required|regex:/^[a-zA-Z\-\.]+$/',
            'taxonomy_id_store_status' => 'required',
            'status_notes' => [
                'required_if:status.slug,==,rejected',
                'max:100'
            ],
            'currencies' => 'required_if:is_multi_currency,1',
            'currency_default' => '',
            'languages' => 'required_if:is_multi_lingual,1',
            'language_default' => '',
            'allowed_ips.*' => 'ip',
        ],
            [
                'name.regex' => 'The Name field should only contain alphabets',
                'slug.regex' => 'The Slug field should only contain alphabets',
                'taxonomy_id_store_status.required' => 'The Status field is required',
                'notes.required' => 'The Store Notes field is required',
                'currencies.required_if' => 'The currencies field is required when is multi currency is "Yes".',
                'languages.required_if' => 'The languages field is required when is multi lingual is "Yes".',
                'status_notes.required_if' => 'The Status notes field is required for "Rejected" Status',
                'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
                'allowed_ips.*.ip' => 'The Allowed IP address field must contain valid ip address'
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

        $query->whereHas('status', function ($query) use ($status) {
            $query->where('name', $status)
                ->orWhere('slug',$status);
        });

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
                ->orWhere('slug', 'LIKE', '%' . $search . '%')
                ->orWhere('id', 'LIKE', '%' . $search . '%');
        });

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('status');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->statusFilter($request->filter);

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

        $taxonomy_status = Taxonomy::getTaxonomyByType('store-status');
        $approved_status = $taxonomy_status->filter(function ($taxonomy) {
            return $taxonomy['name'] === 'Approved';
        });
        $approved_status_id = $approved_status->pluck('id')->first();
        $rejected_status = $taxonomy_status->filter(function ($taxonomy) {
            return $taxonomy['name'] === 'Rejected';
        });
        $rejected_status_id = $rejected_status->pluck('id')->first();
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
                $items->update(['is_active' => null,'taxonomy_id_store_status' => $rejected_status_id]);
                break;
            case 'activate':
                $items->update(['is_active' => 1,'taxonomy_id_store_status' => $approved_status_id]);
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
            $response['failed'] = true;
            $response['messages'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
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
        $taxonomy_status = Taxonomy::getTaxonomyByType('store-status');
        $approved_status = $taxonomy_status->filter(function ($taxonomy) {
            return $taxonomy['name'] === 'Approved';
        });
        $approved_status_id = $approved_status->pluck('id')->first();
        $rejected_status = $taxonomy_status->filter(function ($taxonomy) {
            return $taxonomy['name'] === 'Rejected';
        });
        $rejected_status_id = $rejected_status->pluck('id')->first();

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
                    $items->update(['is_active' => null,'taxonomy_id_store_status'=>$rejected_status_id]);
                }
                break;
            case 'activate':
                if($items->count() > 0) {
                    $items->update(['is_active' => 1,'taxonomy_id_store_status'=>$approved_status_id]);
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
                    StorePaymentMethod::deleteStores($items_id);
                    Vendor::deleteStores($items_id);
                    Product::deleteStores($items_id);
                }
                break;
            case 'activate-all':
                $list->update(['is_active' => 1,'taxonomy_id_store_status'=>$approved_status_id]);
                break;
            case 'deactivate-all':
                $list->update(['is_active' => null,'taxonomy_id_store_status'=>$rejected_status_id]);
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

        $item->currency_default = [];
        $item->currencies = [];
        if ($item->currenciesData->isNotEmpty()){

            $currency_default_record = $item->currenciesData()->where('is_default',1)
                ->select('name',)->get();
            if($currency_default_record->isNotEmpty()){
                $item->currency_default = $currency_default_record[0];
            }

            $currencies = [];
            foreach ($item->currenciesData as $key => $value) {
                $currencies[$key]['name'] = $value['name'];
            }
            $item->currencies = $currencies;

        }

        $item->language_default = [];
        $item->languages = [];
        if ($item->lingualData->isNotEmpty()){

            $language_default_record = $item->lingualData()->where('is_default',1)->select('name')->get();
            if($language_default_record->isNotEmpty()){
                $item->language_default = $language_default_record[0];
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

            if (!empty($inputs['currency_default'])){
                Currency::where(['vh_st_store_id' => $item->id, 'name' => $inputs['currency_default']['name'],
                    'is_active' => 1])->update(['is_default' => 1]);
            }else{
                $first_active_currencies = Currency::where(['vh_st_store_id' => $item->id, 'is_active' => 1])->first();
                $first_active_currencies->is_default = 1;
                $first_active_currencies->save();
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

            if (!empty($inputs['language_default'])){
                Lingual::where(['vh_st_store_id' => $item->id, 'name' => $inputs['language_default']['name'],
                    'is_active' => 1])->update(['is_default' => 1]);
            }else{
                $first_active_lingual = Lingual::where(['vh_st_store_id' => $item->id, 'is_active' => 1])->first();
                $first_active_lingual->is_default = 1;
                $first_active_lingual->save();
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
        $item->forceDelete();
        StorePaymentMethod::deleteStores($item->id);
        Vendor::deleteStores($item->id);
        Product::deleteStores($item->id);

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Record has been deleted';

        return $response;
    }

    //-------------------------------------------------
    public static function itemAction($request, $id, $type): array
    {
        $taxonomy_status = Taxonomy::getTaxonomyByType('store-status');
        $approved_status = $taxonomy_status->filter(function ($taxonomy) {
            return $taxonomy['name'] === 'Approved';
        });
        $approved_status_id = $approved_status->pluck('id')->first();
        $rejected_status = $taxonomy_status->filter(function ($taxonomy) {
            return $taxonomy['name'] === 'Rejected';
        });
        $rejected_status_id = $rejected_status->pluck('id')->first();

        switch($type)
        {
            case 'activate':

                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => 1,'taxonomy_id_store_status' => $approved_status_id]);
                break;
            case 'deactivate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => null,'taxonomy_id_store_status' => $rejected_status_id]);
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

                    if (!empty($inputs['currency_default'])) {
                        if ($inputs['currency_default']['name'] == $value['name']) {
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

                    if (!empty($inputs['language_default'])) {
                        if ($inputs['language_default']['name'] == $value['name']) {
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
        $inputs['is_active'] = 0;

        if($status['name'] == 'Approved')
        {
            $inputs['is_active'] = 1;
        }

        $inputs['is_multi_currency'] = rand(0,1);
        $inputs['is_multi_lingual'] = rand(0,1);

        $currency_list = Taxonomy::getTaxonomyByType('Currency')->toArray();
        $random_currencies = array_rand($currency_list, 3);
        $selected_currencies = [];
        foreach ($random_currencies as $index) {
            $selected_currencies[] = $currency_list[$index];
        }
        if($inputs['is_multi_currency'] == 1)
        {

            foreach ($selected_currencies as $currency)
            {

                $inputs['currencies'][] = $currency;
            }

        }



        $language_list = Taxonomy::getTaxonomyByType('Language')->toArray();
        $random_languages = array_rand($language_list, 3);
        $selected_languages = [];
        foreach ($random_languages as $index) {
            $selected_languages[] = $language_list[$index];
        }
        if($inputs['is_multi_lingual'] == 1)
        {

            foreach ($selected_languages as $language)
            {

                $inputs['languages'][] = $language;
            }

        }
        $inputs['is_multi_vendor'] = 1;

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

}
