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

        $inputs = $request->all();

        $validation = self::validation($inputs);
        if(isset($validation['failed']))
        {
            return $validation;
        }


        // check if name exist
        $item = self::where('name',$inputs['name'])->first();

        if($item)
        {
            $response['failed'] = true;
            $response['messages'][] = "This name is already exist.";
            return $response;
        }

        // check if slug exist
        $item = self::where('slug',$inputs['slug'])->first();

        if($item)
        {
            $response['failed'] = true;
            $response['messages'][] = "This slug is already exist.";
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


        if(isset($request->sort))
        {

            $sort = $request->sort;
            if(Str::contains($request->sort, ':'))
            {
                $sort = explode(":", $request->sort);
                $list= self::orderBy($sort[0], $sort[1]);
            } else
            {
                $list = self::orderBy($sort, 'asc');
            }
        } else{
            $list = self::orderBy('id', 'desc');
        }

        if(isset($request['filter']['is_active']))
        {
            if($request['filter']['is_active'] == "true")
            {
                $list->where('is_active',1);
            } else
            {
                $list->where(function ($q){
                    $q->where('is_active',0);
                    $q->orWhereNull('is_active');
                });
            }
        }

        if(isset($request['filter']['trashed']))
        {
            if($request['filter']['trashed'] === 'include')
            {
                $list->withTrashed();
            }

            if($request['filter']['trashed'] === 'only')
            {
                $list->onlyTrashed();
            }

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
    public static function updateList($request)
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


        $validator = \Validator::make( $inputs, $rules, $messages);
        if ( $validator->fails() ) {

            $errors             = errorsToArray($validator->errors());
            $response['failed'] = true;
            $response['errors'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();

        switch ($inputs['type'])
        {
            case 'inactive':
                self::whereIn('id', $items_id)->update(['is_active' => null]);
                break;
            case 'active':
                self::whereIn('id', $items_id)->update(['is_active' => 1]);
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
    public static function deleteList($request)
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

        $validator = \Validator::make( $inputs, $rules, $messages);
        if ( $validator->fails() ) {

            $errors             = errorsToArray($validator->errors());
            $response['failed'] = true;
            $response['messages'] = $errors;
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
        $inputs = $request->all();

        $validation = self::validation($inputs);
        if(isset($validation['failed']))
        {
            return $validation;
        }

        // check if name exist
        $user = self::where('id','!=',$inputs['id'])
        ->where('name',$inputs['name'])->first();

        if($user)
        {
            $response['failed'] = true;
            $response['messages'][] = "This name is already exist.";
            return $response;
        }

        // check if slug exist
        $user = self::where('id','!=',$inputs['id'])
        ->where('slug',$inputs['slug'])->first();

        if($user)
        {
            $response['failed'] = true;
            $response['messages'][] = "This slug is already exist.";
            return $response;
        }

        $update = self::where('id',$id)->withTrashed()->first();
        $update->fill($inputs);
        $update->slug = Str::slug($inputs['slug']);
        $update->save();

        //check specific actions

        if(isset($inputs['action'])){
            switch($inputs['action'])
            {
                case 'trash':
                    $update->delete();
                    break;
                case 'restore':
                    $update->restore();
                    break;
            }
        }


        $response['success'] = true;
        $response['data'] = $update;
        $response['messages'][] = 'Record has been updated';

        return $response;

    }
    //-------------------------------------------------
    public static function deleteItem($request,$id): array
    {
        $update = self::where('id',$id)->withTrashed()->first();
        if(!$update)
        {
            $response['failed'] = true;
            $response['messages'][] = 'Record does not exist.';
            return $response;
        }

        $update->forceDelete();

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = 'Record has been deleted';

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
            $messages = $validator->errors();
            $response['failed'] = true;
            $response['messages'] = $messages;
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
