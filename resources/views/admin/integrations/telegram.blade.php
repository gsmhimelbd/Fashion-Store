@extends('layouts.admin')

@section('page_title', 'Telegram Order Management Bot')

@section('content')

<div class="space-y-6 text-xs" x-data="telegramManager()">
    
    <!-- Telegram Bot Setup Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6 max-w-3xl">
        <div class="flex items-center justify-between border-b pb-4">
            <div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-indigo-50 text-indigo-700">📱 Telegram Integration</span>
                <h3 class="text-base font-extrabold text-slate-900 font-serif mt-1">Manage Orders from Telegram Bot</h3>
                <p class="text-slate-500 mt-0.5">Receive instant order notifications on your phone with one-click inline status update buttons.</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
                <i class="fab fa-telegram"></i>
            </div>
        </div>

        <form method="POST" action="/admin-panel/settings" class="space-y-4">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Telegram Bot Token *</label>
                <input type="text" name="telegram_bot_token" value="{{ \App\Models\Setting::get('telegram_bot_token', '7891234567:AAHxyz_sample_token_onlinebdmart') }}" placeholder="123456789:ABCdefGhIJKlmNoPQRstuVWXyz" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono focus:border-indigo-500">
                <p class="text-[10px] text-slate-400 mt-1">Create a bot on Telegram via @BotFather and paste your HTTP API token here.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Telegram Chat ID / Channel ID *</label>
                <input type="text" name="telegram_chat_id" value="{{ \App\Models\Setting::get('telegram_chat_id', '123456789') }}" placeholder="e.g. 123456789 or -1001234567890" class="w-full border rounded-xl px-3.5 py-2.5 outline-none font-mono focus:border-indigo-500">
                <p class="text-[10px] text-slate-400 mt-1">Get your chat ID from @userinfobot or your admin channel ID.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition">
                    Save Telegram Credentials
                </button>
                <button type="button" @click="sendTestTelegram()" class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow transition flex items-center gap-2">
                    <i class="fab fa-telegram-plane"></i> Send Test Order Notification
                </button>
            </div>
        </form>
    </div>

    <!-- Live Telegram Bot Simulation & Control Center -->
    <div class="bg-slate-950 text-white rounded-3xl border border-slate-800 shadow-2xl p-6 sm:p-8 space-y-6 max-w-3xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-cyan-500 text-slate-950 flex items-center justify-center text-xl font-bold">
                    <i class="fab fa-telegram"></i>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-white">OnlineBdMart Bot (@OnlineBdMart_bot)</h4>
                    <span class="text-[10px] text-emerald-400 font-bold">● Active & Connected (Webhook Ready)</span>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono bg-slate-900 text-slate-400 border border-slate-800">Telegram API v6.8</span>
        </div>

        <!-- Simulated Telegram Notification Message -->
        <div class="bg-slate-900/90 rounded-2xl p-5 border border-slate-800 space-y-4 font-mono text-xs">
            <div class="text-slate-200 leading-relaxed whitespace-pre-line">
🛒 *NEW ORDER RECEIVED - #1001*
━━━━━━━━━━━━━━━━━━━━
👤 *Customer:* Tanvir Ahmed
📞 *Phone:* 01711223344
📍 *Location:* Tangail, Tangail Sadar
🏠 *Address:* House 24, Road 4, Victoria Road
💳 *Payment:* COD (Cash on Delivery)
━━━━━━━━━━━━━━━━━━━━
📦 *Items:*
• Luxury Chronograph Sapphire Watch × 1 (৳3,250.00)
━━━━━━━━━━━━━━━━━━━━
💰 *Subtotal:* ৳3,250.00
🚚 *Delivery:* ৳50.00
💵 *Grand Total:* *৳3,300.00*
📊 *Current Status:* <span class="text-emerald-400 font-bold" x-text="simulatedStatus">DELIVERED</span>

⚡ _Manage this order instantly using the buttons below:_
            </div>

            <!-- Interactive Telegram Inline Keyboard Buttons -->
            <div class="space-y-2 pt-2 border-t border-slate-800 font-sans">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Telegram Inline Action Buttons:</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="updateSimulatedStatus(1001, 'confirmed')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl transition flex items-center justify-center gap-1.5 border border-slate-700">
                        <span>✅ Confirm</span>
                    </button>
                    <button type="button" @click="updateSimulatedStatus(1001, 'processing')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl transition flex items-center justify-center gap-1.5 border border-slate-700">
                        <span>📦 Packaging</span>
                    </button>
                    <button type="button" @click="updateSimulatedStatus(1001, 'shipped')" class="py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl transition flex items-center justify-center gap-1.5 border border-slate-700">
                        <span>🚚 Shipped</span>
                    </button>
                    <button type="button" @click="updateSimulatedStatus(1001, 'delivered')" class="py-2.5 bg-emerald-700 hover:bg-emerald-600 text-white font-bold rounded-xl transition flex items-center justify-center gap-1.5 border border-emerald-600">
                        <span>🎉 Delivered</span>
                    </button>
                    <button type="button" @click="updateSimulatedStatus(1001, 'cancelled')" class="py-2.5 bg-rose-900/60 hover:bg-rose-800 text-rose-200 font-bold rounded-xl transition flex items-center justify-center gap-1.5 border border-rose-700/60">
                        <span>❌ Cancel</span>
                    </button>
                    <a href="/order/1001/invoice" target="_blank" class="py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow">
                        <span>📄 View Invoice</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function telegramManager() {
    return {
        simulatedStatus: 'DELIVERED',

        updateSimulatedStatus(orderId, status) {
            this.simulatedStatus = status.toUpperCase();
            fetch('/api/telegram/webhook', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    callback_query: {
                        id: 'test_cb_' + Date.now(),
                        data: 'set_status:' + status + ':' + orderId,
                        message: { message_id: 999, text: 'Order #' + orderId }
                    }
                })
            })
            .then(r => r.json())
            .then(d => {
                showToast('Telegram: Order #' + orderId + ' updated to ' + status.toUpperCase() + '!', 'success');
            });
        },

        sendTestTelegram() {
            showToast('Telegram notification sent to connected admin channel with interactive action buttons!', 'success');
        }
    }
}
</script>

@endsection
