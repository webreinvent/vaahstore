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
    protected $appends = ['ordered_product'];
    protected $hidden = ['product','productVariation'];


    public function getOrderedProductAttribute()
    {

        $product = $this->getRelationValue('product');
        $vendor = $this->getRelationValue('vendor');
        if ($product && $this->productVariation) {
            $variation = $this->productVariation;

            $resolved_variation = Product::getResolvedVariationWithVendor($product->id,$vendor,$variation);
            if ($resolved_variation) {
                $product_variation = $resolved_variation;
            }


            return [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => $this->quantity,
                'slug' => $product->slug,
                'media' => $product->media,
                'grouped_attributes' => $product->grouped_attributes,
                'product_variation' => $product_variation,
                // it can be remove
                'vendor' => $vendor ? [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'slug' => $vendor->slug,
                    'email' => $vendor->email,
                    'phone_number' => $vendor->phone_number,
                    'business_type' => $vendor->business_type,
                ] : null,
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                    'media' => $product->brand->media,
                ] : null,
            ];
        }

        return null;
    }

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
        return $this->hasOne(Product::class,'id','vh_st_product_id');
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
        return $this->hasOne(ProductVariation::class,'id','vh_st_product_variation_id');
    }
    //-------------------------------------------------

    public function shipmentItems()
    {
        return $this->hasMany(ShipmentItem::class, 'vh_st_order_item_id');
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


}
