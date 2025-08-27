<?php

namespace VaahCms\Modules\Store\Libraries;

use VaahCms\Modules\Store\Models\Product;
use VaahCms\Modules\Store\Models\ProductVariation;
use VaahCms\Modules\Store\Models\Vendor;
use WebReinvent\VaahCms\Entities\Taxonomy;

class TransformerPrimevue
{

    public static function transformProductGetItem($data)
    {
        $filter_fields = function (&$items, $allowed_fields) {
            foreach ($items as &$item) {
                $item = array_intersect_key($item, array_flip($allowed_fields));
            }
        };

        if (!empty($data['product_variations'])) {
            foreach ($data['product_variations'] as &$variation) {
                unset($variation['product']);
            }
            $filter_fields($data['product_variations'], [
                'id', 'uuid', 'vh_st_product_id', 'sku', 'name',
                'is_default', 'slug', 'quantity', 'price', 'currency',
            ]);
        }

        if (!empty($data['product_categories'])) {
            $filter_fields($data['product_categories'], ['id', 'name', 'slug', 'parent_id']);
        }

        foreach ([
                     'created_by_user', 'updated_by_user', 'deleted_by_user',
                     'status', 'vendors', 'product_medias',
                 ] as $field) {
            unset($data[$field]);
        }

        return [
            'success' => true,
            'data' => $data,
        ];
    }
    //----------------------------------------------------------


    public static function addressGetList($response)
    {
        $grouped = [];

        $address_types = Taxonomy::whereHas('type', function ($query) {
            $query->where('slug', 'address-types');
        })
            ->pluck('name')
            ->toArray();

        // Initialize each address type with an empty array
        foreach ($address_types as $type) {
            $grouped[$type] = [];
        }

        if (!empty($response['success']) && !empty($response['data'])) {
            $data = $response['data'];

            if (method_exists($data, 'getCollection')) {
                $addresses = $data->getCollection();
            } else {
                $addresses = $data;
            }

            // Group addresses by their type
            foreach ($addresses as $address) {
                if (isset($address->addressType->name)) {
                    $name = $address->addressType->name;

                    if (in_array($name, $address_types)) {
                        $grouped[$name][] = $address;
                    }
                }
            }

            $preferred_address_types = ['Shipping', 'Billing'];

            $ordered_grouped = [];

            foreach ($preferred_address_types as $type) {
                if (isset($grouped[$type])) {
                    $ordered_grouped[$type] = $grouped[$type];
                } else {
                    $ordered_grouped[$type] = [];
                }

                unset($grouped[$type]);
            }

            foreach ($grouped as $key => $value) {
                $ordered_grouped[$key] = $value;
            }

            return [
                'success' => true,
                'data' => $ordered_grouped
            ];
        }

        return [
            'success' => false,
            'data' => [],
        ];
    }

    //----------------------------------------------------------

    public static function getCartDetails($cart)
    {
        $transformed_data = collect([
            'id' => $cart->id,
            'uuid' => $cart->uuid,
            'user_id' => $cart->vh_user_id,
            'cart_products_count' => $cart->cart_products_count,
            'products' => [],
            'total_amount' => $cart->total_amount ?? 0,
        ]);

        // Preload all variations to prevent N+1
        $variation_ids = collect($cart->products)->pluck('pivot.vh_st_product_variation_id')->filter()->unique();
        $vendor_ids = collect($cart->products)->pluck('pivot.vh_st_vendor_id')->filter()->unique();
        $variations = ProductVariation::with('medias.images')
            ->whereIn('id', $variation_ids)
            ->get()
            ->keyBy('id');
        $vendors = Vendor::whereIn('id', $vendor_ids)
            ->get()
            ->keyBy('id');

        $products = collect($cart->products)
            ->sortByDesc(fn($product) => $product->pivot->updated_at)
            ->map(function ($product) use ($variations,$vendors) {
                $pivot = $product->pivot;
                $product_variation = $variations->get($pivot->vh_st_product_variation_id);
                $vendor = $vendors->get($pivot->vh_st_vendor_id);

                return [
                    'id' => $product->id,
                    'uuid' => $product->uuid,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'currency' => $product->price['currency'],
                    'brand' => $product->brand ? [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name,
                        'slug' => $product->brand->slug,
                        'media' => $product->brand->media,
                    ] : null,
                    'summary' => $product->summary,
                    'quantity' => $pivot->quantity,
                    'available_stock_quantity' => $pivot->available_stock_quantity ?? $product->available_stock_quantity ?? 0,
                    'wishlist_ids' => $product->wishlist_ids,
                    'is_stock_available' => $pivot->is_stock_available,
                    'product_variation' => Product::buildResolvedVariationResponse($product, $product_variation,'fallback',$vendor),
                    'total_price' => round($pivot->quantity * $pivot->price, 2),
                    'media' => $product->media,
                    'pivot' => [
                        'id' => $pivot->id,
                        'vh_st_cart_id' => $pivot->vh_st_cart_id,
                        'vh_st_product_id' => $pivot->vh_st_product_id,
                        'vh_st_product_variation_id' => $pivot->vh_st_product_variation_id,
                        'vh_st_vendor_id' => $pivot->vh_st_vendor_id,
                        'quantity' => $pivot->quantity,
                        'price' => $pivot->price,
                        'is_stock_available' => $pivot->is_stock_available,
                        'is_wishlisted' => $pivot->is_wishlisted,
                    ],
                ];
            })
            ->values();

        // Attach products to transformed data
        $transformed_data->put('products', $products);

        // Return wrapped response
        return [
            'success' => true,
            'data' => $transformed_data->toArray(),
        ];
    }

    //----------------------------------------------------------

    public static function topSellingProducts($data)
    {
        $items = collect($data)
            ->flatten(1)
            ->filter(fn($item) => is_array($item) && isset($item['id']));

        $product_ids = $items->pluck('id')->unique();
        $products = Product::whereIn('id', $product_ids)->get()->keyBy('id');

        $transformed = $items->map(function ($item) use ($products) {
            $product = $products->get($item['id']);
            $product_variation=Product::getResolvedVariationWithVendor($item['id'], $product->vendor_product_data['selected_vendor']);
            if (!$product) {
                return $item;
            }

            unset($item['image_urls']);

            return array_merge($item, [
                'price' => $product->price,
                'product_variation' => $product_variation,
                'media' => $product->media ? $product->media->values()->toArray() : [],
                'vendor_product_data' => $product->vendor_product_data,
                'wishlist_ids' => $product->wishlist_ids,
            ]);
        });

        return [
            'success' => true,
            'data' => $transformed->values(),
        ];
    }
    //----------------------------------------------------------

    public static function getCartItemDetailsAtCheckout($data)
    {
        // Remove unwanted keys
        unset($data['user']);
        unset($data['user_addresses']);
        unset($data['user_billing_addresses']);
        $variation_ids = collect($data['products'])->pluck('pivot.vh_st_product_variation_id')->filter()->unique();
        $vendor_ids = collect($data['products'])->pluck('pivot.vh_st_vendor_id')->filter()->unique();
        $variations = ProductVariation::with('medias.images')
            ->whereIn('id', $variation_ids)
            ->get()
            ->keyBy('id');
        $vendors = Vendor::whereIn('id', $vendor_ids)
            ->get()
            ->keyBy('id');
        // Transform products
        $data['products'] = collect($data['products'])->map(function ($product) use ($variations,$vendors) {
            $pivot = $product->pivot;
            $product_variation = $variations->get($pivot->vh_st_product_variation_id);
            $vendor = $vendors->get($pivot->vh_st_vendor_id);

            return [
                'id' => $product->id,
                'uuid' => $product->uuid,
                'name' => $product->name,
                'slug' => $product->slug,
                'currency' => $product->price['currency'],
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                    'media' => $product->brand->media,
                ] : null,
                'summary' => $product->summary,
                'quantity' => $pivot->quantity,
                'available_stock_quantity' => $pivot->available_stock_quantity ?? $product->available_stock_quantity ?? 0,
                'wishlist_ids' => $product->wishlist_ids ?? [],
                'is_stock_available' => $pivot->is_stock_available,
                'product_variation' => Product::buildResolvedVariationResponse($product, $product_variation,'fallback',$vendor),
                'total_price' => round($pivot->quantity * $pivot->price, 2),
                'media' => $product->media,
                'pivot' => [
                    'id' => $pivot->id,
                    'vh_st_cart_id' => $pivot->vh_st_cart_id,
                    'vh_st_product_id' => $pivot->vh_st_product_id,
                    'vh_st_product_variation_id' => $pivot->vh_st_product_variation_id,
                    'vh_st_vendor_id' => $pivot->vh_st_vendor_id,
                    'quantity' => $pivot->quantity,
                    'price' => $pivot->price,
                    'is_stock_available' => $pivot->is_stock_available,
                    'is_wishlisted' => $pivot->is_wishlisted,
                ],
            ];
        });

        return [
            'success' => true,
            'data' => $data,
        ];
    }

    //----------------------------------------------------------
    public static function topSellingBrands($brands)
    {
        $brands = collect($brands);
        if ($brands->isEmpty()) {
            return [
                'success' => true,
                'data' => []
            ];
        }

        // Format response
        $primary = $brands->first();
        $secondary = $brands->slice(1)->values();

        return [
            'success' => true,
            'data' => [
                'primary' => $primary,
                'secondary' => $secondary,
            ]
        ];
    }

}
