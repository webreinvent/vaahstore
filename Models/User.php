<?php namespace VaahCms\Modules\Store\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\User as UserBase;

class User extends UserBase
{
    public function user()
    {
        return $this->belongsToMany(UserBase::class,'id','vh_st_user_id','vh_st_role_id');
    }

    //----------------------------------------------------------

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class,
            'vh_st_user_customer_groups', 'vh_st_user_id','vh_st_customer_group_id'
        );
    }

    //----------------------------------------------------------

    public static function getPivotData($pivot)
    {
        $data = array();

        if($pivot->created_by && self::find($pivot->created_by)){
            $data['Created by'] = self::find($pivot->created_by)->name;
        }

        if($pivot->updated_by && self::find($pivot->updated_by)){
            $data['Updated by'] = self::find($pivot->updated_by)->name;
        }

        if($pivot->created_at){
            $data['Created at'] = date('d-m-Y H:i:s', strtotime($pivot->created_at));
        }

        if($pivot->updated_at){
            $data['Updated at'] = date('d-m-Y H:i:s', strtotime($pivot->updated_at));
        }

        return $data;

    }
    //----------------------------------------------------------


    public function scopeCustomerGroupFilter($query, $filter)
    {

        if(!isset($filter['customer_group']))
        {
            return $query;
        }
        $search = $filter['customer_group'];
        $query->whereHas('customerGroups',function ($q) use ($search) {
            $q->whereIn('slug',$search);
        });

    }
    //----------------------------------------------------------


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

    //----------------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if(!isset($filter['q']))
        {
            return $query;
        }
        $keywords = explode(' ',$filter['q']);
        foreach($keywords as $search) {
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('id', 'LIKE', '%' . $search . '%');
            });
        }

    }
    //----------------------------------------------------------

    public static function getList($request,$excluded_columns = [])
    {
        if (isset($request['recount']) && $request['recount'] == true) {
            Role::syncRolesWithUsers();
        }

        $list = self::getSorted($request->filter);
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);
        $list->customerGroupFilter($request->filter);
        $list->dateRangeFilter($request->filter);

        if (isset($request['from']) && isset($request['to'])) {
            $list->betweenDates($request['from'],$request['to']);
        }

        $rows = config('vaahcms.per_page');
        $list->with('customerGroups');

        if ($request->has('rows')) {
            $rows = $request->rows;
        }

        $list->withCount(['activeRoles']);

        $list->whereHas('activeRoles', function ($query) {
            $query->where('slug', 'customer');
        });

        $list = $list->paginate($rows);
        $countRole = Role::all()->count();

        $response['success'] = true;
        $response['data']['totalRole'] = $countRole;
        $response['data'] = $list;

        return $response;
    }

    //----------------------------------------------------------

    public static function createItem($request)
    {
        if (!\Auth::user()->hasPermission('can-create-users')) {
            $response['success'] = false;
            $response['errors'][] = trans('vaahcms-general.permission_denied');

            return $response;
        }

        $inputs = $request->all();

        $validate = self::validation($inputs);

        if (isset($validate['success']) && !$validate['success']) {
            return $validate;
        }

        $rules = array(
            'password' => 'required',
        );

        $messages = array(
            'password.required' => trans('vaahcms-validation.the').' :attribute '.trans('vaahcms-validation.field_is_required'),
        );

        $validator = \Validator::make( $inputs, $rules, $messages);

        if ( $validator->fails() ) {

            $errors             = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        // check if already exist
        $user = self::withTrashed()->where('email',$inputs['email'])->first();

        if ($user) {
            $response['success'] = false;
            $response['errors'][] = trans('vaahcms-user.email_already_registered');
            return $response;
        }

        // check if username already exist
        $user = self::withTrashed()->where('username',$inputs['username'])->first();

        if ($user) {
            $response['success'] = false;
            $response['errors'][] = trans('vaahcms-user.username_already_registered');
            return $response;
        }

        if (!isset($inputs['username'])) {
            $inputs['username'] = Str::slug($inputs['email']);
        }

        if ($inputs['is_active'] === '1' || $inputs['is_active'] === 1 ) {
            $inputs['is_active'] = 1;
        } else {
            $inputs['is_active'] = 0;
        }

        $inputs['created_ip'] = request()->ip();

        $reg = new static();
        $reg->fill($inputs);
        $reg->save();

        Role::syncRolesWithUsers();
//        $customerRole = Role::where('slug', 'customer')->first();
////        dd($customerRole->id);
//        $reg->roles()->attach($customerRole->id);

        $response['success'] = true;
        $response['data']['item'] = $reg;
        $response['messages'][] = trans('vaahcms-general.saved_successfully');
        return $response;

    }


    //----------------------------------------------------------

    public static function bulkChangeRoleStatus($request)
    {

        $inputs = $request->all();

        $role = Role::find($inputs['inputs']['role_id']);

        if($role && $inputs['inputs']['id'] == 1 && $role->slug == 'super-administrator'
            && $inputs['data']['is_active'] == 0)
        {
            $response['success'] = false;
            $response['errors'][] = trans('vaahcms-user.first_user_super_administrator');
            return $response;
        }

        $item = self::find($inputs['inputs']['id']);


        $data = [
            'is_active' => $inputs['data']['is_active'],
            'updated_at' => Carbon::now()
        ];


        if($inputs['inputs']['role_id']){
            $pivot = $item->roles->find($inputs['inputs']['role_id'])->pivot;

            if($pivot->is_active === null && !$pivot->created_by){
                //$data['created_by'] = Auth::user()->id;
                $data['created_at'] = Carbon::now();
            }

            $item->roles()->updateExistingPivot(
                $inputs['inputs']['role_id'],
                $data
            );

        }else{
            $role_ids = [];
            if(isset($inputs['inputs']['query']) && isset($inputs['inputs']['query']['q'])){
                $role_ids = Role::where(function ($q) use($inputs){
                    $q->where('name', 'LIKE', '%'.$inputs['inputs']['query']['q'].'%')
                        ->orWhere('slug', 'LIKE', '%'.$inputs['inputs']['query']['q'].'%');
                })->pluck('id');
            }

            $item_roles = $item->roles()
                ->newPivotStatement()
                ->where('vh_user_id', '=', $item->id);

            if(count($role_ids) > 0){
                $item_roles->whereIn('vh_role_id',$role_ids);
            }

            $item_roles->update($data);
        }

        Role::recountRelations();

        $response['success'] = true;
        $response['data'] = [];

        return $response;


    }

    //----------------------------------------------------------


    public static function searchCustomerGroups($request)
    {
        $query = $request->input('query');
        if($query === null)
        {
            $attribute_name = CustomerGroup::select('id','name','slug')
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        else{

            $attribute_name = CustomerGroup::where('name', 'like', "%$query%")
                ->orWhere('slug','like',"%$query%")
                ->select('id','name','slug')
                ->get();
        }

        $response['success'] = true;
        $response['data'] = $attribute_name;
        return $response;

    }

    //----------------------------------------------------------

    public static function getCustomerGroupsBySlug($request)
    {
        $query = $request['filter']['customer_group'];

        $customer_group = CustomerGroup::whereIn('name',$query)
            ->orWhereIn('slug',$query)
            ->select('id','name','slug')->get();

        $response['success'] = true;
        $response['data'] = $customer_group;
        return $response;
    }

    //----------------------------------------------------------
    //----------------------------------------------------------



}
