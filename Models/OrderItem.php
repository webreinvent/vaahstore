<?php namespace VaahCms\Modules\Store\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use WebReinvent\VaahCms\Entities\Taxonomy;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Entities\User;

class OrderItem extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_st_order_items';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
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
    public function status()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_order_items_status')->select('id','name','slug');
    }
    //-------------------------------------------------
    public function type()
    {
        return $this->hasOne(Taxonomy::class,'id','taxonomy_id_order_items_types')->select('id','name','slug');
    }
    //-------------------------------------------------
    public function user()
    {
        return $this->hasOne(User::class,'id','vh_user_id')->select('id','first_name', 'email');
    }
    //-------------------------------------------------
    public function product()
    {
        return $this->hasOne(Product::class,'id','vh_st_product_id')->select('id','name', 'slug');
    }
    //-------------------------------------------------
    public function vendor()
    {
        return $this->hasOne(Vendor::class,'id','vh_st_vendor_id')->select('id','name', 'slug');
    }
    //-------------------------------------------------

    //-------------------------------------------------
    public function order()
    {
        return $this->belongsTo(Order::class,'vh_st_order_id','id');
    }
    //-------------------------------------------------
    public function ProductVariation()
    {
        return $this->hasOne(ProductVariation::class,'id','vh_st_product_variation_id')->select('id','name','slug');
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

    public function scopeQuickFilter($query, $filter)
    {
        // Return unmodified query if 'time' filter is not set or is null
        if (!isset($filter['time']) || is_null($filter['time']) || $filter['time'] === 'null') {
            return $query;
        }

        // Define the end date as the end of today
        $end_date = Carbon::now()->endOfDay();
        $start_date = null;

        // Set start date based on filter values
        switch ($filter['time']) {
            case 'today':
                $start_date = Carbon::now()->startOfDay();
                break;
            case 'last-7-days':
                $start_date = Carbon::now()->subDays(7)->startOfDay();
                break;
            case 'last-1-month':
                $start_date = Carbon::now()->subMonth()->startOfDay();
                break;
            case 'last-1-year':
                $start_date = Carbon::now()->subYear()->startOfDay();
                break;
            default:
                // Optional: handle unknown filter values
                return $query;
        }

        // Apply the date range filter

        return $query->whereBetween('created_at', [$start_date, $end_date]);
    }
}
