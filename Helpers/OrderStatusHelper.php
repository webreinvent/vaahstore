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

    /**
     * Get the order status and shipment status based on payment and shipment status.
     *
     * @param  string $payment_status_slug
     * @param  string $shipment_status_name
     * @param  int    $shipped_order_quantity
     * @param  int    $total_order_quantity
     * @param  bool   $all_delivered
     * @return array
     */
    public static function getOrderStatusWithShipment($payment_status_slug, $shipment_status_name, $shipped_order_quantity, $total_order_quantity, $all_delivered)
    {
        $order_status = $shipment_status_name;
        $order_shipment_status = $shipment_status_name;

        switch ($payment_status_slug) {
            case 'pending':
                switch ($shipment_status_name) {
                    case 'Pending':
                        $order_status = 'Placed';
                        break;
                    case 'Delivered':
                        $order_status = 'Payment Pending';
                        $order_shipment_status = $all_delivered
                            ? ($shipped_order_quantity != $total_order_quantity ? 'Partially Delivered' : $shipment_status_name)
                            : 'Partially Delivered';
                        break;
                }
                break;

            case 'partially-paid':
                switch ($shipment_status_name) {
                    case 'Pending':
                        $order_status = 'Placed';
                        break;
                    case 'Delivered':
                        $order_status = 'Partially-Paid';
                        $order_shipment_status = ($shipped_order_quantity != $total_order_quantity)
                            ? 'Partially Delivered'
                            : $shipment_status_name;
                        break;
                }
                break;

            case 'paid':
                switch ($shipment_status_name) {
                    case 'Pending':
                        $order_status = 'Placed';
                        break;
                    case 'Delivered':
                        if ($all_delivered) {
                            $order_status = ($shipped_order_quantity != $total_order_quantity)
                                ? 'Partially Delivered'
                                : 'Completed';
                            $order_shipment_status = ($shipped_order_quantity != $total_order_quantity)
                                ? 'Partially Delivered'
                                : $shipment_status_name;
                        } else {
                            $order_shipment_status = 'Partially Delivered';
                            $order_status = 'Partially Delivered';
                        }
                        break;
                    default:
                        $order_status = $shipment_status_name;
                        $order_shipment_status = $shipment_status_name;
                        break;
                }
                break;

            default:
                $order_status = $shipment_status_name;
                $order_shipment_status = $shipment_status_name;
                break;
        }
        return [
            'order_status' => $order_status,
            'order_shipment_status' => $order_shipment_status,
        ];
    }

}
