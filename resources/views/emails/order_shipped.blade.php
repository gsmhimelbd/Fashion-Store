<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Order #{{ $order->id }} is On The Way!</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        
        <div style="background: #1e1b4b; padding: 30px 24px; text-align: center; color: #ffffff;">
            <div style="font-size: 36px; margin-bottom: 8px;">🚚</div>
            <h1 style="margin: 0; font-size: 22px; font-weight: 800;">Your Order is On The Way!</h1>
            <p style="margin: 6px 0 0; font-size: 13px; color: #a5b4fc;">Package for Order #{{ $order->id }} has been handed over to courier.</p>
        </div>

        <div style="padding: 24px; text-align: center;">
            <p style="font-size: 14px; color: #475569; margin-bottom: 20px;">
                Hello <strong>{{ $order->customer_name }}</strong>, our courier rider is en route to deliver your package to <strong>{{ $order->district }}</strong>. Please keep <strong>৳{{ number_format($order->grand_total, 2) }}</strong> ready if you chose Cash on Delivery.
            </p>

            <a href="{{ url('/track-order?order_id=' . $order->id . '&phone=' . $order->phone) }}" style="display: inline-block; padding: 12px 28px; background: #10b981; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 13px; border-radius: 10px;">Track Live Delivery Status &rarr;</a>
        </div>

        <div style="background: #f8fafc; padding: 16px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0;">{{ $storeName }} Customer Support Team</p>
        </div>
    </div>
</body>
</html>
