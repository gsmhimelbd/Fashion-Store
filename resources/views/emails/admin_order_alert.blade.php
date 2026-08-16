<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Order Alert #{{ $order->id }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 20px;">
    <div style="max-width: 550px; margin: 0 auto; background: #1e293b; border-radius: 14px; padding: 24px; border: 1px solid #334155;">
        <h2 style="color: #38bdf8; margin-top: 0;">🚨 New Order #{{ $order->id }} Received!</h2>
        <p><strong>Customer:</strong> {{ $order->customer_name }} ({{ $order->phone }})</p>
        <p><strong>Location:</strong> {{ $order->district }}, {{ $order->upazila }}</p>
        <p><strong>Address:</strong> {{ $order->address }}</p>
        <p><strong>Payment:</strong> {{ strtoupper($order->payment_method) }}</p>
        <p style="font-size: 16px; font-weight: bold; color: #4ade80;">Grand Total: ৳{{ number_format($order->grand_total, 2) }}</p>
        <div style="margin-top: 20px;">
            <a href="{{ url('/admin-panel/orders') }}" style="background: #4f46e5; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 12px;">Manage in Admin Panel &rarr;</a>
        </div>
    </div>
</body>
</html>
