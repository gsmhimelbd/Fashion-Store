<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Order #{{ $order->id }} is Delivered!</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        
        <div style="background: #022c22; padding: 30px 24px; text-align: center; color: #ffffff;">
            <div style="font-size: 36px; margin-bottom: 8px;">🎉</div>
            <h1 style="margin: 0; font-size: 22px; font-weight: 800;">Package Delivered Successfully!</h1>
            <p style="margin: 6px 0 0; font-size: 13px; color: #6ee7b7;">Order #{{ $order->id }} has been delivered.</p>
        </div>

        <div style="padding: 24px; text-align: center;">
            <p style="font-size: 14px; color: #475569; margin-bottom: 20px;">
                Thank you for shopping with <strong>{{ $storeName }}</strong>! We hope you love your new items.
            </p>

            <a href="{{ url('/shop') }}" style="display: inline-block; padding: 12px 28px; background: #4f46e5; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 13px; border-radius: 10px;">Explore More Products &rarr;</a>
        </div>
    </div>
</body>
</html>
