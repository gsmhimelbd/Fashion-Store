<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation #{{ $order->id }}</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        
        <!-- Header -->
        <div style="background: #0f172a; padding: 30px 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 0.5px;">{{ strtoupper($storeName) }}</h1>
            <p style="margin: 6px 0 0; font-size: 13px; color: #94a3b8;">Thank you for your order! Your invoice receipt is below.</p>
        </div>

        <!-- Order Summary Card -->
        <div style="padding: 24px;">
            <div style="background: #f1f5f9; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                    <strong style="color: #475569;">Order ID:</strong>
                    <span style="font-weight: 800; color: #4f46e5; font-family: monospace;">#{{ $order->id }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                    <strong style="color: #475569;">Customer:</strong>
                    <span>{{ $order->customer_name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                    <strong style="color: #475569;">Phone:</strong>
                    <span>{{ $order->phone }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                    <strong style="color: #475569;">Delivery Address:</strong>
                    <span>{{ $order->address }}, {{ $order->district }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 13px;">
                    <strong style="color: #475569;">Payment Method:</strong>
                    <span style="text-transform: uppercase; font-weight: 700; color: #10b981;">{{ $order->payment_method }}</span>
                </div>
            </div>

            <!-- Items Table -->
            <h3 style="font-size: 14px; text-transform: uppercase; color: #475569; margin: 0 0 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">Purchased Items</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left; color: #64748b;">
                        <th style="padding: 8px;">Product</th>
                        <th style="padding: 8px; text-align: center;">Qty</th>
                        <th style="padding: 8px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 8px; font-weight: 600;">{{ $item->product_name }}</td>
                        <td style="padding: 10px 8px; text-align: center;">{{ $item->quantity }}</td>
                        <td style="padding: 10px 8px; text-align: right; font-weight: 700;">৳{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Calculation Totals -->
            <div style="border-top: 2px solid #e2e8f0; padding-top: 10px; font-size: 13px; line-height: 1.8;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">Subtotal:</span>
                    <span style="font-weight: 600;">৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">Delivery Fee:</span>
                    <span style="font-weight: 600;">৳{{ number_format($order->delivery_charge, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div style="display: flex; justify-content: space-between; color: #10b981; font-weight: 600;">
                    <span>Discount:</span>
                    <span>-৳{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; color: #0f172a; margin-top: 6px; border-top: 1px solid #cbd5e1; padding-top: 6px;">
                    <span>Grand Total:</span>
                    <span style="color: #4f46e5;">৳{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="margin-top: 30px; text-align: center;">
                <a href="{{ url('/track-order?order_id=' . $order->id . '&phone=' . $order->phone) }}" style="display: inline-block; padding: 12px 28px; background: #4f46e5; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 13px; border-radius: 10px; margin-right: 10px;">Track Order Live 📦</a>
                <a href="{{ url('/order/' . $order->id . '/invoice') }}" style="display: inline-block; padding: 12px 28px; background: #0f172a; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 13px; border-radius: 10px;">View Printable Receipt 📄</a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0 0 4px;">Need help with your order? Contact our WhatsApp hotline: <strong>{{ $storePhone }}</strong></p>
            <p style="margin: 0;">&copy; {{ date('Y') }} {{ $storeName }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
