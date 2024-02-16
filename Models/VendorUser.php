<?php

namespace VaahCms\Modules\Store\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;

class vendorUser extends VaahModel
{
    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    protected $table = 'vh_st_vendor_users';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $fillable = [
        'vh_st_vendor_id',
        'vh_user_id',
        'vh_role_id',

    ];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')
            ->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id')
            ->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id')
            ->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vh_st_vendor_id', 'id');
    }

    public function getTableColumns()
    {
        return $this->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($this->getTable());
    }

    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }

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
}
