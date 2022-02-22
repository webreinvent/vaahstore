<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Entities\User;

class Store extends Model {

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
        'name',
        'slug',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    //-------------------------------------------------
    protected $appends  = [
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
    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()
            ->getColumnListing($this->getTable());
    }
    //-------------------------------------------------
    public function scopeExclude($query, $columns)
    {
        return $query->select( array_diff( $this->getTableColumns(),$columns) );
    }

    //-------------------------------------------------
    public function scopeBetweenDates($query, $from, $to)
    {

        if($from)
        {
            $from = \Carbon::parse($from)
                ->startOfDay()
                ->toDateTimeString();
        }

        if($to)
        {
            $to = \Carbon::parse($to)
                ->endOfDay()
                ->toDateTimeString();
        }

        $query->whereBetween('updated_at',[$from,$to]);
    }
    //-------------------------------------------------
    public static function createItem($request)
    {

        $inputs = $request->new_item;

        $validation = self::validation($inputs);
        if( isset($validation['status'])
            && $validation['status'] == 'failed'
            )
        {
            return $validation;
        }


        // check if name exist
        $item = self::where('name',$inputs['name'])->first();

        if($item)
        {
            $response['failed'] = true;
            $response['errors'][] = "This name is already exist.";
            return $response;
        }


        // check if slug exist
        $item = self::where('slug',$inputs['slug'])->first();

        if($item)
        {
            $response['failed'] = true;
            $response['errors'][] = "This slug is already exist.";
            return $response;
        }

        $item = new self();
        $item->fill($inputs);
        $item->slug = Str::slug($inputs['slug']);
        $item->save();

        $response['success'] = true;
        $response['data']['item'] = $item;
        $response['messages'][] = 'Saved successfully.';
        return $response;

    }
    //-------------------------------------------------
    public static function getList($request)
    {


        $list = self::orderBy('id', 'desc');

        if($request['trashed'] == 'true')
        {

            $list->withTrashed();
        }

        if(isset($request->from) && isset($request->to))
        {
            $list->betweenDates($request['from'],$request['to']);
        }

        if($request['filter'] && $request['filter'] == '1')
        {

            $list->where('is_active',$request['filter']);
        }elseif($request['filter'] == '10'){

            $list->whereNull('is_active')->orWhere('is_active',0);
        }

        if(isset($request->q))
        {
            $list->where(function ($q) use ($request){
                $q->where('name', 'LIKE', '%'.$request->q.'%')
                    ->orWhere('slug', 'LIKE', '%'.$request->q.'%');
            });
        }

        $list = $list->paginate(config('vaahcms.per_page'));

        $response['success'] = true;
        $response['data'] = $list;

        return $response;


    }
    //-------------------------------------------------
    public static function getItem($id)
    {

        $item = self::where('id', $id)
        ->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
        ->withTrashed()
        ->first();

        $response['success'] = true;
        $response['data'] = $item;

        return $response;

    }
    //-------------------------------------------------
    public static function updateItem($request,$id)
    {
        $inputs = $request->item;

        $validation = self::validation($inputs);
        if(isset($validation['status']) && $validation['status'] == 'failed')
        {
            return $validation;
        }

        // check if name exist
        $user = self::where('id','!=',$inputs['id'])
        ->where('name',$inputs['name'])->first();

        if($user)
        {
            $response['failed'] = true;
            $response['errors'][] = "This name is already exist.";
            return $response;
        }

        // check if slug exist
        $user = self::where('id','!=',$inputs['id'])
        ->where('slug',$inputs['slug'])->first();

        if($user)
        {
            $response['failed'] = true;
            $response['errors'][] = "This slug is already exist.";
            return $response;
        }

        $update = self::where('id',$id)->withTrashed()->first();

        $update->name = $inputs['name'];
        $update->slug = Str::slug($inputs['slug']);
        $update->save();


        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Data updated.';

        return $response;

    }
    //-------------------------------------------------
    public static function bulkStatusChange($request)
    {

        if(!$request->has('inputs'))
        {
            $response['failed'] = true;
            $response['messages'][] = 'Select IDs';
            return $response;
        }

        if(!$request->has('data'))
        {
            $response['failed'] = true;
            $response['errors'][] = 'Select Status';
            return $response;
        }

        foreach($request->inputs as $id)
        {
            $item = self::where('id',$id)->withTrashed()->first();

            if($item->deleted_at){
                continue ;
            }

            if($request['data']){
                $item->is_active = $request['data']['status'];
            }else{
                if($item->is_active == 1){
                    $item->is_active = 0;
                }else{
                    $item->is_active = 1;
                }
            }
            $item->save();
        }

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Action was successful';

        return $response;

    }
    //-------------------------------------------------
    public static function bulkTrash($request)
    {


        if(!$request->has('inputs'))
        {
            $response['failed'] = true;
            $response['messages'][] = 'Select IDs';
            return $response;
        }


        foreach($request->inputs as $id)
        {
            $item = self::withTrashed()->where('id', $id)->first();
            if($item)
            {
                $item->delete();
            }
        }

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Action was successful';

        return $response;


    }
    //-------------------------------------------------
    public static function bulkRestore($request)
    {

        if(!$request->has('inputs'))
        {
            $response['failed'] = true;
            $response['messages'][] = 'Select IDs';
            return $response;
        }

        if(!$request->has('data'))
        {
            $response['failed'] = true;
            $response['errors'][] = 'Select Status';
            return $response;
        }

        foreach($request->inputs as $id)
        {
            $item = self::withTrashed()->where('id', $id)->first();
            if(isset($item) && isset($item->deleted_at))
            {
                $item->restore();
            }
        }

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Action was successful';

        return $response;

    }
    //-------------------------------------------------
    public static function bulkDelete($request)
    {

        if(!$request->has('inputs'))
        {
            $response['failed'] = true;
            $response['messages'][] = 'Select IDs';
            return $response;
        }

        if(!$request->has('data'))
        {
            $response['failed'] = true;
            $response['errors'][] = 'Select Status';
            return $response;
        }

        foreach($request->inputs as $id)
        {
            $item = self::where('id', $id)->withTrashed()->first();
            if($item)
            {
                $item->forceDelete();
            }
        }

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Action was successful';

        return $response;


    }
    //-------------------------------------------------

    public static function validation($inputs)
    {

        $rules = array(
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
        );

        $validator = \Validator::make( $inputs, $rules);
        if ( $validator->fails() ) {
            $errors             = errorsToArray($validator->errors());
            $response['failed'] = true;
            $response['messages'] = $errors;
            return $response;
        }

    }
    //-------------------------------------------------
    public static function getActiveItems()
    {
        $item = self::where('is_active', 1)->get();
        return $item;
    }
    //-------------------------------------------------
    //-------------------------------------------------
    //-------------------------------------------------


}
