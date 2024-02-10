<?php namespace VaahCms\Modules\Store\Models;

use Illuminate\Support\Str;
use WebReinvent\VaahCms\Models\Role;
use WebReinvent\VaahCms\Models\User as UserBase;

class User extends UserBase
{
    public function user()
    {
        return $this->belongsToMany(UserBase::class,'id','vh_st_user_id','vh_st_role_id');
    }

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

    public static function getList($request,$excluded_columns = [])
    {
        if (isset($request['recount']) && $request['recount'] == true) {
            Role::syncRolesWithUsers();
        }

        $list = self::getSorted($request->filter);
        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);

        if (isset($request['from']) && isset($request['to'])) {
            $list->betweenDates($request['from'],$request['to']);
        }

        $rows = config('vaahcms.per_page');

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


}
