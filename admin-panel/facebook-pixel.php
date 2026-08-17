<?php
$adminTitle = 'Meta Pixel, Conversions API & GTM Server-Side Tracking';
require_once __DIR__ . '/header.php';

$msg = '';
$testResult = null;

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'save_settings';

        if ($action === 'save_settings') {
            $keys = [
                // GTM Web Container ID
                'gtm_container_id' => strtoupper(trim($_POST['gtm_container_id'] ?? '')),
                'gtm_enabled' => isset($_POST['gtm_enabled']) ? '1' : '0',

                // Stape GTM Server Container URL
                'stape_server_container_url' => rtrim(trim($_POST['stape_server_container_url'] ?? ''), '/'),
                'stape_server_enabled' => isset($_POST['stape_server_enabled']) ? '1' : '0',

                // Meta Pixel / Dataset ID
                'facebook_pixel_id' => trim($_POST['facebook_pixel_id'] ?? ''),
                'facebook_pixel_enabled' => isset($_POST['facebook_pixel_enabled']) ? '1' : '0',

                // Meta Conversions API (CAPI)
                'meta_capi_enabled' => isset($_POST['meta_capi_enabled']) ? '1' : '0',
                'meta_capi_access_token' => trim($_POST['meta_capi_access_token'] ?? ''),
                'meta_capi_test_code' => strtoupper(trim($_POST['meta_capi_test_code'] ?? '')),

                // Events Toggle
                'track_view_content' => isset($_POST['track_view_content']) ? '1' : '0',
                'track_add_to_cart' => isset($_POST['track_add_to_cart']) ? '1' : '0',
                'track_initiate_checkout' => isset($_POST['track_initiate_checkout']) ? '1' : '0',
                'track_purchase' => isset($_POST['track_purchase']) ? '1' : '0',

                // Advanced Matching
                'meta_advanced_matching' => isset($_POST['meta_advanced_matching']) ? '1' : '0',
            ];

            foreach ($keys as $k => $v) {
                saveSetting($k, $v);
            }
            $msg = '✓ Meta Pixel, Conversions API & GTM Server-Side settings saved successfully!';
        } elseif ($action === 'test_event') {
            require_once __DIR__ . '/../includes/meta_capi.php';

            $testEventName = trim($_POST['test_event_name'] ?? 'Purchase');
            $testEventId = 'test_' . strtolower($testEventName) . '_' . time();
            
            $customData = [
                'currency' => 'BDT',
                'value' => 1800.00,
                'content_name' => 'Prestige Electric Multi Cooker 8L Stainless Steel',
                'content_type' => 'product',
                'content_ids' => ['101'],
                'num_items' => 1,
                'order_id' => 'TEST-' . rand(1000, 9999),
            ];

            $customerData = [
                'email' => 'customer@onlinebdmart.com',
                'phone' => '01775153740',
                'name' => 'Arif Hossain',
                'first_name' => 'Arif',
                'last_name' => 'Hossain',
                'city' => 'Dhaka',
                'district' => 'Dhaka',
                'country' => 'bd'
            ];

            $testResult = MetaConversionsAPI::trackServerEvent(
                $testEventName,
                $testEventId,
                $customData,
                $customerData,
                'https://onlinebdmart.com/product.php?slug=prestige-electric-multi-cooker-8l-stainless-steel'
            );

            $msg = '🚀 Test Event [' . htmlspecialchars($testEventName) . '] dispatched to Meta CAPI & Stape Server!';
        }
    }

    $settings = getAllSettings();
} catch (Exception $e) {
    $settings = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center gap-2 mb-6">
    <i class="fas fa-circle-check text-base"></i> <span><?= htmlspecialchars($msg) ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left Column: Settings Configuration Form -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
            <div>
                <span class="text-xs font-bold uppercase text-blue-400">Tracking Architecture</span>
                <h2 class="text-lg font-black text-white mt-1">Meta Pixel, CAPI & GTM Server-Side Hub</h2>
                <p class="text-xs text-slate-400">Complete dual tracking: Browser Pixel + Meta Conversions API (CAPI) + GTM Web & Server Container (Stape) with 100% stable Event Deduplication.</p>
            </div>

            <form method="POST" action="facebook-pixel.php" class="space-y-6 text-xs">
                <input type="hidden" name="action" value="save_settings">

                <!-- 1. Meta Pixel & Conversions API Credentials -->
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center text-lg"><i class="fab fa-facebook-f"></i></div>
                            <div>
                                <h3 class="font-extrabold text-white text-sm">1. Meta Pixel & Conversions API (CAPI)</h3>
                                <p class="text-slate-400 text-[11px]">Primary Facebook Dataset / Pixel and Server-side Access Token.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="meta_capi_enabled" <?= ($settings['meta_capi_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-blue-500">
                            <span class="text-blue-400 font-bold">CAPI Active</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Meta Pixel / Dataset ID *</label>
                            <input type="text" name="facebook_pixel_id" value="<?= htmlspecialchars($settings['facebook_pixel_id'] ?? '') ?>" placeholder="e.g. 123456789012345" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-slate-300 font-bold mb-1">Test Event Code (Meta Test Events Tab)</label>
                            <input type="text" name="meta_capi_test_code" value="<?= htmlspecialchars($settings['meta_capi_test_code'] ?? '') ?>" placeholder="e.g. TEST12345 (Optional)" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-amber-300 font-mono outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Meta Conversions API (CAPI) Access Token *</label>
                        <textarea name="meta_capi_access_token" rows="3" placeholder="EAAG... (Generated from Meta Events Manager > Settings > Generate Access Token)" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-200 font-mono text-[11px] outline-none focus:border-blue-500"><?= htmlspecialchars($settings['meta_capi_access_token'] ?? '') ?></textarea>
                        <p class="text-[10px] text-slate-500 mt-1">Events Manager ➔ Settings ➔ Conversions API ➔ "Generate access token" থেকে তৈরি করা টোকেনটি এখানে পেস্ট করুন।</p>
                    </div>
                </div>

                <!-- 2. Google Tag Manager (GTM Web Container) -->
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-cyan-600/20 text-cyan-400 flex items-center justify-center text-lg"><i class="fas fa-tag"></i></div>
                            <div>
                                <h3 class="font-extrabold text-white text-sm">2. Google Tag Manager (GTM Web Container)</h3>
                                <p class="text-slate-400 text-[11px]">Injects standard GTM Web snippet & pushes e-commerce DataLayer events.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="gtm_enabled" <?= ($settings['gtm_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-cyan-500">
                            <span class="text-cyan-400 font-bold">GTM Active</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">GTM Web Container ID</label>
                        <input type="text" name="gtm_container_id" value="<?= htmlspecialchars($settings['gtm_container_id'] ?? 'GTM-') ?>" placeholder="e.g. GTM-XXXXXXX" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-cyan-400 font-mono font-black outline-none focus:border-cyan-500 uppercase">
                    </div>
                </div>

                <!-- 3. Stape GTM Server Container Configuration -->
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-purple-600/20 text-purple-400 flex items-center justify-center text-lg"><i class="fas fa-server"></i></div>
                            <div>
                                <h3 class="font-extrabold text-white text-sm">3. Stape GTM Server-Side Endpoint</h3>
                                <p class="text-slate-400 text-[11px]">Stape hosted GTM Server URL / Custom Tagging Domain.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="stape_server_enabled" <?= ($settings['stape_server_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-purple-500">
                            <span class="text-purple-400 font-bold">Stape Active</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Stape Tagging Server URL / Custom Subdomain</label>
                        <input type="text" name="stape_server_container_url" value="<?= htmlspecialchars($settings['stape_server_container_url'] ?? '') ?>" placeholder="e.g. https://ss.onlinebdmart.com or https://xxxx.stape.io" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-purple-300 font-mono outline-none focus:border-purple-500">
                        <p class="text-[10px] text-slate-500 mt-1">Stape.io ড্যাশবোর্ড থেকে আপনার Server Container-এর Tagging Server URL এখানে দিন।</p>
                    </div>
                </div>

                <!-- 4. Required Events & Deduplication Controls -->
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                    <h3 class="font-extrabold text-white text-sm flex items-center gap-2">
                        <i class="fas fa-list-check text-emerald-400"></i> 4. Tracked Meta Standard Events
                    </h3>
                    <p class="text-slate-400 text-[11px]">All events send synchronized <code>event_id</code> on both Browser & Server for 100% Deduplication.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-900 border border-slate-800 cursor-pointer">
                            <input type="checkbox" name="track_view_content" <?= ($settings['track_view_content'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-500">
                            <div>
                                <strong class="text-white block">1. ViewContent (view_item)</strong>
                                <span class="text-[10px] text-slate-400">Product Page View with price & ID</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-900 border border-slate-800 cursor-pointer">
                            <input type="checkbox" name="track_add_to_cart" <?= ($settings['track_add_to_cart'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-500">
                            <div>
                                <strong class="text-white block">2. AddToCart (add_to_cart)</strong>
                                <span class="text-[10px] text-slate-400">Bag Add with selected Color & Size</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-900 border border-slate-800 cursor-pointer">
                            <input type="checkbox" name="track_initiate_checkout" <?= ($settings['track_initiate_checkout'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-indigo-500">
                            <div>
                                <strong class="text-white block">3. InitiateCheckout (begin_checkout)</strong>
                                <span class="text-[10px] text-slate-400">Checkout Step with subtotal</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-900 border border-slate-800 cursor-pointer">
                            <input type="checkbox" name="track_purchase" <?= ($settings['track_purchase'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-emerald-500">
                            <div>
                                <strong class="text-white block">4. Purchase (purchase)</strong>
                                <span class="text-[10px] text-slate-400">Stable Order ID deduplicated</span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="meta_advanced_matching" <?= ($settings['meta_advanced_matching'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-emerald-500">
                            <span class="text-emerald-400 font-bold">Enable Event Match Quality Parameters (SHA-256 Hashed Email, Phone, City, Country, FBP, FBC, IP, User-Agent)</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg transition">
                    Save Meta & GTM Server Settings
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Diagnostics & Live Test Event Dispatcher -->
    <div class="space-y-6">
        
        <!-- Live Test Event Dispatcher -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4 text-xs">
            <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-bolt text-amber-400"></i> Test Meta CAPI & Stape Server
            </h3>
            <p class="text-slate-400">Send an instant test event from server to verify Event Match Quality and Test Events Tab in Meta Events Manager.</p>

            <form method="POST" action="facebook-pixel.php" class="space-y-3">
                <input type="hidden" name="action" value="test_event">

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Select Event Type to Test</label>
                    <select name="test_event_name" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold outline-none">
                        <option value="Purchase">Purchase (অর্ডার ক্রয় - ৳1800)</option>
                        <option value="InitiateCheckout">InitiateCheckout (চেকআউট শুরু)</option>
                        <option value="AddToCart">AddToCart (কার্টে যুক্ত)</option>
                        <option value="ViewContent">ViewContent (পণ্য ভিউ)</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black rounded-xl shadow transition flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i> <span>Send Test Event to Meta</span>
                </button>
            </form>

            <?php if ($testResult): ?>
            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 space-y-2 text-[11px] font-mono">
                <div class="font-bold <?= ($testResult['meta']['success'] ?? false) ? 'text-emerald-400' : 'text-rose-400' ?>">
                    Meta Graph API: <?= ($testResult['meta']['success'] ?? false) ? '✓ SUCCESS (200 OK)' : '✕ FAILED' ?>
                </div>
                <?php if (!empty($testResult['meta']['error'])): ?>
                <div class="text-rose-400 text-[10px] break-all"><?= htmlspecialchars(json_encode($testResult['meta']['error'])) ?></div>
                <?php endif; ?>
                <div class="text-slate-400 text-[10px]">
                    Event ID: <code><?= htmlspecialchars($testResult['event_id']) ?></code>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Architecture Flow Reference Box -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-3 text-xs">
            <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-network-wired text-indigo-400"></i> Tracking Dataflow
            </h3>
            
            <div class="space-y-2 text-[11px] text-slate-300 leading-relaxed font-mono bg-slate-950 p-3.5 rounded-2xl border border-slate-800">
                <div class="text-emerald-400 font-bold">1. Website (PHP DataLayer)</div>
                <div class="pl-3">↓ pushes standard e-commerce event</div>
                <div class="text-cyan-400 font-bold">2. GTM Web Container</div>
                <div class="pl-3">├─&gt; Meta Pixel (Browser) [event_id]</div>
                <div class="pl-3">└─&gt; GTM Server Container (Stape)</div>
                <div class="text-purple-400 font-bold">3. Stape GTM Server Container</div>
                <div class="pl-3">↓ Meta Conversions API Tag</div>
                <div class="text-blue-400 font-bold">4. Meta CAPI (Server) [event_id]</div>
                <div class="pt-1 text-amber-300 font-bold">✓ Meta Deduplicates Browser + Server</div>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
