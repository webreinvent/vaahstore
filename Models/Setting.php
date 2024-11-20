<?php namespace VaahCms\Modules\Store\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;

class Setting extends VaahModel
{
    use SoftDeletes;
    use CrudWithUuidObservantTrait;


    protected $table = 'vh_st_settings';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    //-------------------------------------------------
    protected $fillable = [
        'label', 'excerpt',
        'type', 'key', 'value', 'meta',
        'vh_user_id',"created_by",
        "updated_by","deleted_by"
    ];
    //-------------------------------------------------
    protected $appends = [
    ];
    //-------------------------------------------------
    protected $encrypt = ['meta'];

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

    public function setMetaAttribute($value)
    {
        $encryptedValues = collect($value)->map(function ($item) {
            return $item !== null ? encrypt($item) : null;
        })->toArray();

        $this->attributes['meta'] = json_encode($encryptedValues);
    }

    public function getMetaAttribute($value)
    {
        try {
            $decodedValue = json_decode($value, true);

            if (is_array($decodedValue)) {
                return collect($decodedValue)->map(function ($item) {
                    return $item !== null ? decrypt($item) : null;
                })->all();
            }

            return [];
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return [];
        }
    }

    //-------------------------------------------------



    public function createdByUser()
    {
        return $this->belongsTo(U::class,
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


    //-------------------------------------------------
    public static function getSettingByKey($key)
    {
        $setting_key = self::where('key', $key)
            ->first();

        if ($setting_key) {
            return $setting_key->value;
        }

        return null;
    }
}
