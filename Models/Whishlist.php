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
use WebReinvent\VaahCms\Models\Taxonomy;
use WebReinvent\VaahCms\Models\TaxonomyType;

class Whishlist extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_whishlists';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'vh_user_id',
        'name',
        'slug',
        'type',
        'taxonomy_id_whishlists_status',
        'is_default',
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
    public function status(){
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id_whishlists_status')->select(['id','name','slug']);
    }

    //-------------------------------------------------
    public function whishlistType(){
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id_whishlists_types')->select(['id','name','slug']);
    }

    //-------------------------------------------------
    public function user(){
        return $this->hasOne(User::class, 'id', 'vh_user_id')->select(['id','first_name']);
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // Check if current record is default
        if($inputs['is_default']){
            self::where('is_default',1)->update(['is_default' => 0]);
        }


        $item = new self();
        $item->fill($inputs);
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

        if (!isset($filter['q'])) {
            return $query;
        }

        $search_terms = explode(' ', $filter['q']);

        $query->where(function ($query) use ($search_terms) {

            $query->orWhereHas('user', function ($userQuery) use ($search_terms) {
                foreach ($search_terms as $term) {
                    $userQuery->where('first_name', 'LIKE', '%' . $term . '%')
                        ->orWhere('middle_name', 'LIKE', '%' . $term . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $term . '%');
                }
            });
        });

        return $query;


    }
    //-------------------------------------------------
    public function scopeWishlistStatusFilter($query, $filter)
    {

        if(!isset($filter['wishlist_status']))
        {
            return $query;
        }
        $search = $filter['wishlist_status'];
        $query->whereHas('status' , function ($q) use ($search){
                      $q->whereIn('name' ,$search);
        });
    }
    //-------------------------------------------------
    public function scopeWishlistTypeFilter($query, $filter)
    {

        if(!isset($filter['wishlist_type']))
        {
            return $query;
        }
        $search = $filter['wishlist_type'];
        $query->whereHas('whishlistType',function ($q) use ($search) {
                $q->whereIn('name',$search);
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
    //------------------------------------------------------

    public static function getList($request)
    {
        $list = self::getSorted($request->filter)->with('user','status','whishlistType');
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->wishlistStatusFilter($request->filter);
        $list->wishlistTypeFilter($request->filter);
        $list->dateRangeFilter($request->filter);

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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }

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

        $taxonomy_status = Taxonomy::getTaxonomyByType('whishlists-status');
        $approved_id = $taxonomy_status->where('slug','approved')->pluck('id')->first();
        $pending_id = $taxonomy_status->where('slug','pending')->pluck('id')->first();
        $rejected_id = $taxonomy_status->where('slug','rejected')->pluck('id')->first();

        switch ($inputs['type']) {
            case 'approve':
                $items->update(['taxonomy_id_whishlists_status' => $approved_id]);
                break;
            case 'pending':
                $items->update(['taxonomy_id_whishlists_status' => $pending_id]);
                break;
            case 'reject':
                $items->update(['taxonomy_id_whishlists_status' => $rejected_id]);
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }

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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
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

        $taxonomy_status = Taxonomy::getTaxonomyByType('whishlists-status');
        $approved_id = $taxonomy_status->where('slug','approved')->pluck('id')->first();
        $pending_id = $taxonomy_status->where('slug','pending')->pluck('id')->first();
        $rejected_id = $taxonomy_status->where('slug','rejected')->pluck('id')->first();

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
            case 'approved-all':
                $list->update(['taxonomy_id_whishlists_status' => $approved_id]);
                break;
            case 'pending-all':
                $list->update(['taxonomy_id_whishlists_status' => $pending_id]);
                break;
            case 'reject-all':
                $list->update(['taxonomy_id_whishlists_status' => $rejected_id]);
                break;
            case 'approved':
                if($items->count() > 0) {
                    $items->update(['taxonomy_id_whishlists_status' => $approved_id]);
                }
                break;
            case 'pending':
                if($items->count() > 0) {
                    $items->update(['taxonomy_id_whishlists_status' => $pending_id]);
                }
                break;
            case 'reject':
                if($items->count() > 0) {
                    $items->update(['taxonomy_id_whishlists_status' => $rejected_id]);
                }
                break;
            case 'trash-all':
                $list->update(['deleted_by' => auth()->user()->id]);
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }


        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','user','status','whishlistType'])
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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }


        $inputs = $request->all();

        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // Check default
        if($inputs['is_default'] == 1 || $inputs['is_default'] == true){
            self::where('is_default',1)->update(['is_default' => 0]);
        }

        $item = self::where('id', $id)->withTrashed()->first();
        $item->fill($inputs);
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
        }


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
        if (!\Auth::user()->hasPermission('can-update-module')) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms::messages.permission_denied");

            return $response;
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
                $item = self::where('id', $id)->withTrashed()->first();
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
            'vh_user_id'=> 'required',
            'name' => 'required|max:100',
            'slug' => 'required|max:100',
            'type' => '',
            'taxonomy_id_whishlists_status'=> 'required',
            'status_notes' => 'max:250',
        );

        $customMessages = array(
            'vh_user_id.required' => 'The User field is required.',
            'name.required' => 'The Name field is required.',
            'name.max' => 'The Name field may not be greater than :max characters.',
            'slug.required' => 'The Slug field is required.',
            'slug.max' => 'The Slug field may not be greater than :max characters.',
            'vh_user_id.required' => 'The User field is required.',
            'taxonomy_id_whishlists_status.required' => 'The Status field is required.',
            'status_notes.max' => 'The Status notes field may not be greater than :max characters.',
        );

        $validator = \Validator::make($inputs, $rules, $customMessages);
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

        // fill the user field here

        $users_ids = User::where('is_active',1)->pluck('id')->toArray();
        $users_id = $users_ids[array_rand($users_ids)];
        $users_id_data = User::where('is_active',1)->where('id',$users_id)->first();
        $inputs['vh_user_id'] =$users_id;
        $inputs['user'] = $users_id_data;

        // fill the taxonomy status field here

        $taxonomy_status = Taxonomy::getTaxonomyByType('whishlists-status');
        $status_ids = $taxonomy_status->pluck('id')->toArray();
        if($taxonomy_status->isEmpty())
        {
            $response['success'] = false;
            $response['errors'][] = 'No Wishlist Status Found , Create Wishlist Status From Taxonomies ';
            return $response;

        }
        $status_id = $status_ids[array_rand($status_ids)];
        $status = $taxonomy_status->where('id',$status_id)->first();
        $inputs['taxonomy_id_whishlists_status'] = $status_id;
        $inputs['status']=$status;

        // fill the name field here
        $max_chars = rand(2,100);
        $inputs['name']=$faker->text($max_chars);

        // fill the slug field here
        $inputs['slug']=Str::slug($inputs['name']);

        // fill the is default field here

        $inputs['type'] = 0;
        $inputs['is_default'] = 0;


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
    public static function searchVaahUsers($request)
    {
        $query = $request->input('query');
        $search_approved = User::select('id', 'first_name')->where('is_active', '1');
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
    public static function searchType($request)
    {
        $query = $request->input('query');
        if(empty($query)) {
            $item = Taxonomy::getTaxonomyByType('wishlists-types');
        } else {
            $tax_type = TaxonomyType::getFirstOrCreate('wishlists-types');

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

    public static function searchStatus($request)
    {
        $query = $request->input('query');
        if(empty($query)) {
            $item = Taxonomy::getTaxonomyByType('whishlists-status');
        } else {
            $tax_type = TaxonomyType::getFirstOrCreate('whishlists-status');

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


}
