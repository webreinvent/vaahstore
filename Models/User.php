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
        $registered_role = Role::where('slug', 'customer')->first();
        $registered_role?->users()->updateExistingPivot($reg, ['is_active' => 1]);

        $response['success'] = true;
        $response['data']['item'] = $reg;
        $response['messages'][] = trans('vaahcms-general.saved_successfully');
        return $response;

    }


    //----------------------------------------------------------


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
    public static function deleteList($request): array
    {
        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
            'items' => 'required',
        );

        $messages = array(
            'type.required' => trans('vaahcms-general.action_type_is_required'),
            'items.required' => trans('vaahcms-general.select_items'),
        );

        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {
            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        $response['errors'] = [];

        foreach($inputs['items'] as $item) {
            $is_restricted = self::restrictedActions('delete', $item['id']);

            if(isset($is_restricted['success']) && !$is_restricted['success'])
            {
                $response['errors'][] = '<b>'.$item['email'].'</b>: '.$is_restricted['errors'][0];
                continue;
            }

            $item = self::query()->where('id', $item['id'])->withTrashed()->first();

            if ($item) {
                $item->roles()->detach();
                $item->forceDelete();
            }
        }

        $response['success'] = true;
        $response['data'] = true;

        if(count($inputs['items']) !== count($response['errors'])){
            $response['messages'][] = trans('vaahcms-general.action_successful');
        }

        return $response;
    }
    //----------------------------------------------------------



}
