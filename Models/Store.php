<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Entities\User;

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
    public function status(){
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id_store_status')
            ->select(['id','name', 'slug']);
    }

    //-------------------------------------------------
    public function currenciesRecord(){
        return $this->hasMany(currencie::class, 'vh_st_store_id', 'id')
            ->where('is_active', 1)
            ->select(['vh_st_currencies.vh_st_store_id','vh_st_currencies.name',
                'vh_st_currencies.code','vh_st_currencies.symbol','vh_st_currencies.is_default']);
    }

    //-------------------------------------------------
    public function lingualRecord(){
        return $this->hasMany(Lingual::class, 'vh_st_store_id', 'id')
            ->where('is_active', 1)
            ->select(['vh_st_lingual.vh_st_store_id','vh_st_lingual.name','vh_st_lingual.is_default']);
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
        $validation_result = self::storeInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }

        $inputs = $validation_result['data'];

        if ($inputs['is_default'] == 1 || $inputs['is_default'] == true){
            self::removePreviousDefaults();
        }

        $item = new self();
        $item->fill($inputs);
        $item->allowed_ips =json_encode($inputs['allowed_ips']);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        if(!empty($inputs['currencies']) && $item->is_multi_currency == 1) {
            foreach ($inputs['currencies'] as $key => $value) {

                $record = new currencie();
                $record->vh_st_store_id = $item->id;
                $record->fill($value);

                if (!empty($inputs['currency_default'])) {
                    if ($inputs['currency_default']['code'] == $value['code']) {
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
                $record->fill($value);

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
    public static function removePreviousDefaults(){
        self::where('is_default', 1)
            ->update(['is_default' => 0]);
    }
    //-------------------------------------------------
    public static function storeInputValidator($requestData){

        $validated_data = validator($requestData, [
            'name' => 'required',
            'slug' => 'required',
            'is_multi_currency' => 'required',
            'is_multi_lingual' => 'required',
            'is_multi_vendor' => 'required',
            'allowed_ips' => 'required',
            'is_default' => 'required',
            'taxonomy_id_store_status' => 'required',
            'status_notes' => 'required_if:taxonomy_id_vendor_status.slug,==,rejected',
            'is_active' => 'required',
            'currencies' => 'required_if:is_multi_currency,1',
            'currency_default' => '',
            'languages' => 'required_if:is_multi_lingual,1',
            'language_default' => ''
        ],
        [
            'taxonomy_id_store_status.required' => 'The Status field is required',
            'notes.required' => 'The Store Notes field is required',
            'currencies.required_if' => 'The currencies field is required when is multi currency is "Yes".',
            'languages.required_if' => 'The languages field is required when is multi lingual is "Yes".',
             'status_notes.*' => 'The Status notes field is required for "Rejected" Status',
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
        $list = self::getSorted($request->filter)->with('status');
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
                    StorePaymentMethod::deleteStores($items_id);
                    Vendor::deleteStores($items_id);
                    Product::deleteStores($items_id);
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
                StorePaymentMethod::deleteStores($items_id);
                Vendor::deleteStores($items_id);
                Product::deleteStores($items_id);
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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser', 'status', 'currenciesRecord', 'lingualRecord'])
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
        if ($item->currenciesRecord->isNotEmpty()){

            $currency_default_record = $item->currenciesRecord()->where('is_default',1)
                ->select('name','code','symbol')->get();
            if($currency_default_record->isNotEmpty()){
                $item->currency_default = $currency_default_record[0];
            }

            $currencies = [];
            foreach ($item->currenciesRecord as $key => $value) {
                $currencies[$key]['name'] = $value['name'];
                $currencies[$key]['code'] = $value['code'];
                $currencies[$key]['symbol'] = $value['symbol'];
            }
            $item->currencies = $currencies;

        }

        $item->language_default = [];
        $item->languages = [];
        if ($item->lingualRecord->isNotEmpty()){

            $language_default_record = $item->lingualRecord()->where('is_default',1)->select('name')->get();
            if($language_default_record->isNotEmpty()){
                $item->language_default = $language_default_record[0];
            }

            $languages = [];
            foreach ($item->lingualRecord as $key => $value) {
                $languages[$key]['name'] = $value['name'];
            }
            $item->languages = $languages;
        }

        $item->is_default = $item->is_default == 1 ? true :false;
        $item->is_active = $item->is_active == 1 ? true :false;
        $item->allowed_ips = json_decode($item->allowed_ips);

        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }
    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $validation_result = self::storeInputValidator($request->all());

        if ($validation_result['success'] != true){
            return $validation_result;
        }

        $inputs = $validation_result['data'];

        if ($inputs['is_default'] == 1 || $inputs['is_default'] == true){
            self::removePreviousDefaults();
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->allowed_ips = json_encode($inputs['allowed_ips']);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        if(!empty($inputs['currencies'])) {
            currencie::where('vh_st_store_id', $item->id)->update(['is_active' => 0, 'is_default' => 0]);

            foreach ($inputs['currencies'] as $key => $v) {

                currencie::updateOrInsert(
                    ['vh_st_store_id' => $item->id, 'name' => $v['name'], 'code' => $v['code'], 'symbol' => $v['symbol']],
                    ['is_active' => 1]
                );

            }

            if (!empty($inputs['currency_default'])){
                currencie::where(['vh_st_store_id' => $item->id, 'code' => $inputs['currency_default']['code'],
                    'is_active' => 1])->update(['is_default' => 1]);
            }else{
                $first_active_currencies = currencie::where(['vh_st_store_id' => $item->id, 'is_active' => 1])->first();
                $first_active_currencies->is_default = 1;
                $first_active_currencies->save();
            }
        }else{
            currencie::where('vh_st_store_id', $item->id)->update(['is_active' => 0, 'is_default' => 0]);
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
    public static function deleteItem($request, $id): array
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

        $rules = array(
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
        );

        $validator = \Validator::make($inputs, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $response['success'] = false;
            $response['messages'] = $messages->all();
            return $response;
        }

        $response['success'] = true;
        return $response;

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
