<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->uuid ?? $order->id }}</title> {{-- Use uuid for consistency --}}
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.3;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }

        .invoice-header {
            background: #2c3e50;
            color: white;
            padding: 12px 20px;
            text-align: center;
            margin-bottom: 15px;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .invoice-number {
            font-size: 12px;
            opacity: 0.9;
        }

        .invoice-meta {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .meta-row {
            display: table-row;
        }

        .meta-cell {
            display: table-cell;
            padding: 6px 10px;
            border-right: 1px solid #ddd;
            vertical-align: top;
        }

        .meta-cell:last-child {
            border-right: none;
        }

        .meta-label {
            font-weight: bold;
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }

        .meta-value {
            font-size: 11px;
            margin-top: 2px;
        }

        .section {
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 6px;
            padding: 4px 0;
            border-bottom: 1px solid #ddd;
        }

        .addresses-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .address-cell {
            display: table-cell;
            width: 50%;
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .address-cell:first-child {
            border-right: none;
        }

        .address-title {
            font-weight: bold;
            font-size: 10px;
            color: #666;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .address-content {
            font-size: 10px;
            line-height: 1.3;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .items-table th {
            background-color: #f8f9fa;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .items-table td {
            padding: 5px 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            float: right;
            width: 300px;
            margin-bottom: 15px;
        }

        .totals-table {
            width: 100%;
            font-size: 10px;
        }

        .totals-table td {
            padding: 3px 8px;
            border: none;
        }

        .total-label {
            font-weight: bold;
            text-align: right;
        }

        .total-value {
            text-align: right;
            width: 80px;
        }

        .grand-total {
            font-size: 12px;
            font-weight: bold;
            border-top: 2px solid #2c3e50;
            padding-top: 4px;
        }

        .invoice-statuses {
            clear: both;
            margin-top: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            font-size: 10px;
        }

        .footer-row {
            display: table;
            width: 100%;
        }

        .footer-cell {
            display: table-cell;
            width: 33.33%;
            padding: 2px 8px;
        }

        .footer-label {
            font-weight: bold;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media print {
            .invoice-container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <!-- Compact Header -->
    <div class="invoice-header">
        <div class="invoice-title">INVOICE</div>
        <div class="invoice-number">#{{ $order->uuid }}</div>
    </div>

    <!-- Compact Meta Information -->
    <div class="invoice-meta">
        <div class="meta-row">
            <div class="meta-cell">
                <div class="meta-label">Date</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y H:i') }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Status</div>
                <div class="meta-value">
                        <span class="status-badge status-{{ strtolower($order->order_status ?? 'pending') }}">
                            {{ $order->order_status ?? 'Pending' }}
                        </span>
                </div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Customer</div>
                <div class="meta-value">{{ $order->user->name ?? 'N/A' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Email</div>
                <div class="meta-value">{{ $order->user->email ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
    <!-- Compact Statuses -->
    <div class="invoice-statuses">
        <div class="footer-row">
            <div class="footer-cell">
                <span class="footer-label">Currency:</span> {{ $order->currency->code ?? '' }}
            </div>
            <div class="footer-cell">
                <span class="footer-label">Payment:</span> {{ $order->order_payment_status->name ?? 'N/A' }}
            </div>
            <div class="footer-cell">
                <span class="footer-label">Shipment:</span> {{ $order->order_shipment_status ?? 'N/A' }}
            </div>
        </div>
    </div>
    <!-- Compact Addresses -->
    <div class="section">
        <div class="section-title">Addresses</div>
        <div class="addresses-row">
            <div class="address-cell">
                <div class="address-title">Billing Address</div>
                <div class="address-content">
                    @if($order->billing_address)
                        {{ $order->billing_address->address ?? 'Not provided' }}<br>
                        {{ $order->billing_address->city ?? '' }}{{ ($order->billing_address->city && $order->billing_address->state) ? ', ' : '' }}{{ $order->billing_address->state ?? '' }}<br>
                        {{ $order->billing_address->country ?? '' }}
                    @else
                        Not provided
                    @endif
                </div>
            </div>

            <div class="address-cell">
                <div class="address-title">Shipping Address</div>
                <div class="address-content">
                    @if($order->shipping_address)
                        {{ $order->shipping_address->address ?? 'Not provided' }}<br>
                        {{ $order->shipping_address->city ?? '' }}{{ ($order->shipping_address->city && $order->shipping_address->state) ? ', ' : '' }}{{ $order->shipping_address->state ?? '' }}<br>
                        {{ $order->shipping_address->country ?? '' }}
                    @else
                        Not provided
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Compact Items Table -->
    <div class="section">
        <div class="section-title">Order Details</div>
        <table class="items-table">
            <thead>
            <tr>
                <th>Product</th>
                <th>Brand</th>
                <th>Vendor</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            @php
                $sub_total = 0;
               $symbol = html_entity_decode($order->currency->symbol ?? '&#x20B9;', ENT_QUOTES | ENT_HTML5, 'UTF-8');

            @endphp
            @foreach($order->items as $item)
                @php
                    $price = $item->price ?? 0;
                    $quantity = $item->quantity ?? 0;
                    $line_total = $price * $quantity;
                    $sub_total += $line_total;
                    $product = $item->ordered_product ?? null;
                    $variation_name = $product->product_variation->name ?? 'N/A';
                    $vendor = $product->product_variation->selected_vendor->name
                            ?? $product->vendor->name
                            ?? 'N/A';
                    $brand_name = $product->brand->name ?? 'N/A';
                @endphp
                <tr>
                    <td>{{ $variation_name }}</td>
                    <td>{{ $brand_name }}</td>
                    <td>{{ $vendor }}</td>
                    <td class="text-center">{{ $quantity }}</td>
                    <td class="text-right">{{ $symbol }}{{ number_format($price, 2) }}</td>
                    <td class="text-right">{{ $symbol }}{{ number_format($line_total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Compact Totals -->
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="total-label">Subtotal:</td>
                <td class="total-value">{{ $symbol }}{{ number_format($sub_total, 2) }}</td>
            </tr>
            <tr>
                <td class="total-label">Delivery Fee:</td>
                <td class="total-value">{{ $symbol }}{{ number_format($order->delivery_fee ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="total-label">Discount:</td>
                <td class="total-value">-{{ $symbol }}{{ number_format($order->discount ?? 0, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td class="total-label">Total:</td>
                <td class="total-value">{{ $symbol }}{{ number_format($order->payable ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="total-label">Paid:</td>
                <td class="total-value">{{ $symbol }}{{ number_format($order->paid ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>


</div>
</body>
</html>
