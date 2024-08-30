<?php

namespace VaahCms\Modules\Store\Helpers;


use VaahCms\Modules\Store\Models\Shipment;
use VaahCms\Modules\Store\Models\ShipmentItem;
use WebReinvent\VaahCms\Entities\Taxonomy;

class OrderStatusHelper
{
    /**
     * Check if all shipments for a given order are delivered.
     *
     * @param  int $order_id
     * @return bool
     */
    public static function areAllShipmentsDelivered($order_id)
    {
        // Retrieve all shipment IDs for the given order
        $all_shipment_ids = ShipmentItem::where('vh_st_order_id', $order_id)
            ->pluck('vh_st_shipment_id');

        // Get the 'delivered' status ID from taxonomy
        $taxonomy_id_shipment_status_delivered = Taxonomy::getTaxonomyByType('shipment-status')
            ->where('slug', 'delivered')
            ->value('id');

        // Retrieve all shipment statuses for the given shipment IDs
        $shipment_statuses = Shipment::whereIn('id', $all_shipment_ids)
            ->pluck('taxonomy_id_shipment_status');

        // Check if all statuses are 'delivered'
        return $shipment_statuses->every(function ($status_id) use ($taxonomy_id_shipment_status_delivered) {
            return $status_id == $taxonomy_id_shipment_status_delivered;
        });
    }
}
