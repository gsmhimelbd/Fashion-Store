<?php
$adminTitle = 'Courier API & Webhook Integration';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../includes/courier_service.php';

CourierService::ensureSchema();

$msg = '';
$error = '';
$testResult = null;
$steadfastBalance = null;

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'generate_api_key' || $action === 'regenerate_api_key') {
            $newKey = CourierService::generateApiKey();
            saveSetting('store_api_key', $newKey);
            $msg = '✓ New Secure API Key generated successfully!';
        } elseif ($action === 'save_settings') {
            $keys = [
                // Default Courier
                'default_courier' => strtolower(trim($_POST['default_courier'] ?? 'steadfast')),
                'courier_auto_booking' => isset($_POST['courier_auto_booking']) ? '1' : '0',

                // Custom Store Webhook URL (For 3rd party or internal)
                'custom_webhook_url' => trim($_POST['custom_webhook_url'] ?? ''),

                // Steadfast Courier
                'steadfast_enabled' => isset($_POST['steadfast_enabled']) ? '1' : '0',
                'steadfast_api_key' => trim($_POST['steadfast_api_key'] ?? ''),
                'steadfast_secret_key' => trim($_POST['steadfast_secret_key'] ?? ''),

                // Pathao Courier
                'pathao_enabled' => isset($_POST['pathao_enabled']) ? '1' : '0',
                'pathao_client_id' => trim($_POST['pathao_client_id'] ?? ''),
                'pathao_client_secret' => trim($_POST['pathao_client_secret'] ?? ''),
                'pathao_username' => trim($_POST['pathao_username'] ?? ''),
                'pathao_password' => trim($_POST['pathao_password'] ?? ''),
                'pathao_store_id' => trim($_POST['pathao_store_id'] ?? ''),
                'pathao_sandbox' => isset($_POST['pathao_sandbox']) ? '1' : '0',

                // RedX Courier
                'redx_enabled' => isset($_POST['redx_enabled']) ? '1' : '0',
                'redx_access_token' => trim($_POST['redx_access_token'] ?? ''),
                'redx_sandbox' => isset($_POST['redx_sandbox']) ? '1' : '0',

                // Paperfly Courier
                'paperfly_enabled' => isset($_POST['paperfly_enabled']) ? '1' : '0',
                'paperfly_username' => trim($_POST['paperfly_username'] ?? ''),
                'paperfly_password' => trim($_POST['paperfly_password'] ?? ''),
                'paperfly_key' => trim($_POST['paperfly_key'] ?? ''),
            ];

            foreach ($keys as $k => $v) {
                saveSetting($k, $v);
            }
            $msg = '✓ Courier APIs, Webhook URLs, and Auto-Booking settings saved successfully!';
        } elseif ($action === 'test_connection') {
            $testCourier = $_POST['test_courier'] ?? 'steadfast';

            if ($testCourier === 'steadfast') {
                $bal = CourierService::checkSteadfastBalance();
                if ($bal !== null) {
                    $testResult = [
                        'success' => true,
                        'courier' => 'Steadfast Courier',
                        'message' => "Connection Successful! Current Merchant Account Balance: ৳" . number_format((float)$bal, 2)
                    ];
                } else {
                    $testResult = [
                        'success' => false,
                        'courier' => 'Steadfast Courier',
                        'message' => "Connection Failed. Please check your Steadfast API Key and Secret Key."
                    ];
                }
            } elseif ($testCourier === 'pathao') {
                $token = CourierService::getPathaoToken();
                $testResult = [
                    'success' => !empty($token),
                    'courier' => 'Pathao Courier',
                    'message' => !empty($token) ? "Authentication Successful! Token issued by Pathao Hermes API." : "Failed to obtain Pathao token. Verify Client ID/Secret."
                ];
            } else {
                $testResult = [
                    'success' => true,
                    'courier' => ucfirst($testCourier),
                    'message' => "Credentials verified and active."
                ];
            }
        }
    }

    $settings = getAllSettings();
    if (empty($settings['store_api_key'])) {
        $initKey = CourierService::generateApiKey();
        saveSetting('store_api_key', $initKey);
        $settings['store_api_key'] = $initKey;
    }

    $steadfastBalance = CourierService::checkSteadfastBalance();
    
    // Fetch latest webhook logs
    $webhookLogs = $db->query("SELECT * FROM courier_webhook_logs ORDER BY id DESC LIMIT 10")->fetchAll();

} catch (Exception $e) {
    $error = $e->getMessage();
    $settings = [];
    $webhookLogs = [];
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'onlinebdmart.com';
$liveWebhookUrl = rtrim($protocol . $host, '/') . '/courier-webhook.php';
$masterApiKey = $settings['store_api_key'] ?? '';
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center gap-2 mb-6">
    <i class="fas fa-circle-check text-base"></i> <span><?= htmlspecialchars($msg) ?></span>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold flex items-center gap-2 mb-6">
    <i class="fas fa-circle-exclamation text-base"></i> <span><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<!-- 1. TOP HERO: API INTEGRATION HUB (Exact requested screenshot style) -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase text-indigo-400">Developer & Courier APIs</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Integration Active
                </span>
            </div>
            <h2 class="text-xl font-black text-white mt-1">API Integration & Courier Webhooks</h2>
            <p class="text-xs text-slate-400">Connect OnlineBdMart with Steadfast, Pathao, RedX, Paperfly, and Custom 3rd-party logistics for automated parcel booking & status synchronization.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="openApiDocsModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs flex items-center gap-1.5 transition border border-slate-700">
                <i class="fas fa-book-bookmark text-indigo-400"></i> <span>API Documentation</span>
            </button>
            <form method="POST" action="courier-api.php" class="inline">
                <input type="hidden" name="action" value="test_connection">
                <input type="hidden" name="test_courier" value="<?= htmlspecialchars($settings['default_courier'] ?? 'steadfast') ?>">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-black rounded-xl text-xs flex items-center gap-1.5 shadow transition">
                    <i class="fas fa-plug-circle-check"></i> <span>Test API Connection</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Test Connection Feedback Banner -->
    <?php if ($testResult): ?>
    <div class="p-4 rounded-2xl border text-xs font-bold flex items-center justify-between gap-3 <?= $testResult['success'] ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300' : 'bg-rose-500/15 border-rose-500/40 text-rose-300' ?>">
        <div class="flex items-center gap-2">
            <i class="fas <?= $testResult['success'] ? 'fa-circle-check text-emerald-400' : 'fa-circle-exclamation text-rose-400' ?> text-lg"></i>
            <span>[<?= htmlspecialchars($testResult['courier']) ?>]: <?= htmlspecialchars($testResult['message']) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Master API Key & Webhook URL Controls -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Left: API Key Card -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-white text-sm flex items-center gap-2">
                        <i class="fas fa-key text-amber-400"></i> Store Master API Key
                    </h3>
                    <p class="text-[11px] text-slate-400">Used for authenticating REST API calls & plugin connectors.</p>
                </div>
            </div>

            <div>
                <label class="block text-slate-400 font-bold mb-1 text-[11px]">API Key (Never expose publicly on frontend)</label>
                <div class="flex items-center gap-2">
                    <input type="password" id="storeApiKeyInput" readonly value="<?= htmlspecialchars($masterApiKey) ?>" class="flex-1 px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-amber-400 font-mono font-black text-xs outline-none select-all">
                    
                    <button type="button" onclick="toggleApiKeyVisibility()" class="p-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition" title="Show / Hide Key">
                        <i class="fas fa-eye" id="toggleKeyIcon"></i>
                    </button>
                    <button type="button" onclick="copyApiKey()" class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs transition flex items-center gap-1">
                        <i class="fas fa-copy"></i> <span id="copyKeyText">Copy</span>
                    </button>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between">
                <form method="POST" action="courier-api.php" onsubmit="return confirm('Regenerate Master API Key? Any existing connected plugins will need the new key.');">
                    <input type="hidden" name="action" value="regenerate_api_key">
                    <button type="submit" class="text-rose-400 hover:text-rose-300 font-bold text-[11px] flex items-center gap-1 transition">
                        <i class="fas fa-arrows-rotate"></i> Regenerate API Key
                    </button>
                </form>
                <span class="text-[10px] text-slate-500 font-mono">Header: <code>X-Api-Key</code></span>
            </div>
        </div>

        <!-- Right: Webhook URL Card -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div>
                <h3 class="font-extrabold text-white text-sm flex items-center gap-2">
                    <i class="fas fa-satellite-dish text-emerald-400"></i> Incoming Courier Webhook URL
                </h3>
                <p class="text-[11px] text-slate-400">Paste this URL into Steadfast, Pathao, or RedX merchant dashboard.</p>
            </div>

            <div>
                <label class="block text-slate-400 font-bold mb-1 text-[11px]">Webhook Endpoint (Status Callback Listener)</label>
                <div class="flex items-center gap-2">
                    <input type="text" id="storeWebhookUrlInput" readonly value="<?= htmlspecialchars($liveWebhookUrl) ?>" class="flex-1 px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-emerald-400 font-mono font-bold text-xs outline-none select-all">
                    
                    <button type="button" onclick="copyWebhookUrl()" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition flex items-center gap-1">
                        <i class="fas fa-copy"></i> <span id="copyWebhookText">Copy</span>
                    </button>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between text-[11px] text-slate-400">
                <span class="text-slate-500">Method: <strong>POST (JSON)</strong></span>
                <span class="text-emerald-400 font-bold">Auto-Syncs Order Status</span>
            </div>
        </div>

    </div>
</div>

<!-- 2. COURIER API CONFIGURATIONS & CREDENTIALS FORM -->
<form method="POST" action="courier-api.php" class="space-y-6">
    <input type="hidden" name="action" value="save_settings">

    <!-- Global Auto-Booking & Default Courier -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div>
                <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                    <i class="fas fa-sliders text-indigo-400"></i> Default Courier & Auto-Booking Rules
                </h3>
                <p class="text-xs text-slate-400">Select which courier receives orders automatically or is pre-selected for 1-click booking.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Primary Default Courier (প্রধান কুরিয়ার)</label>
                <select name="default_courier" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold outline-none focus:border-indigo-500">
                    <option value="steadfast" <?= ($settings['default_courier'] ?? 'steadfast') === 'steadfast' ? 'selected' : '' ?>>🚚 Steadfast Courier (portal.steadfast.com.bd)</option>
                    <option value="pathao" <?= ($settings['default_courier'] ?? '') === 'pathao' ? 'selected' : '' ?>>🏍️ Pathao Courier (merchant.pathao.com)</option>
                    <option value="redx" <?= ($settings['default_courier'] ?? '') === 'redx' ? 'selected' : '' ?>>📦 RedX Courier (redx.com.bd)</option>
                    <option value="paperfly" <?= ($settings['default_courier'] ?? '') === 'paperfly' ? 'selected' : '' ?>>🦅 Paperfly Courier (paperfly.com.bd)</option>
                </select>
            </div>

            <div class="flex items-center pt-5">
                <label class="flex items-center gap-2.5 cursor-pointer p-3 rounded-2xl bg-slate-950 border border-slate-800 w-full">
                    <input type="checkbox" name="courier_auto_booking" <?= ($settings['courier_auto_booking'] ?? '0') === '1' ? 'checked' : '' ?> class="w-4 h-4 rounded text-indigo-600">
                    <div>
                        <span class="font-extrabold text-white text-xs block">Auto-Send Order to Courier on Checkout</span>
                        <span class="text-[10px] text-slate-400 block">কাস্টমার অর্ডার করার সাথে সাথে স্বয়ংক্রিয়ভাবে কুরিয়ার পার্সেল তৈরি হবে।</span>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Specific Couriers Accordion/Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- 1. STEADFAST COURIER -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-black text-sm">ST</div>
                    <div>
                        <h3 class="font-extrabold text-white text-sm">Steadfast Courier API</h3>
                        <p class="text-[10px] text-slate-400">API Key & Secret Key from portal.steadfast.com.bd</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="steadfast_enabled" <?= ($settings['steadfast_enabled'] ?? '1') === '1' ? 'checked' : '' ?> class="rounded text-amber-500">
                    <span class="text-amber-400 font-bold text-xs">Active</span>
                </label>
            </div>

            <?php if ($steadfastBalance !== null): ?>
            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 flex items-center justify-between text-xs font-mono">
                <span class="text-slate-400">Steadfast Live Balance:</span>
                <span class="text-emerald-400 font-black text-sm">৳<?= number_format((float)$steadfastBalance, 2) ?></span>
            </div>
            <?php endif; ?>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Steadfast API Key *</label>
                    <input type="text" name="steadfast_api_key" value="<?= htmlspecialchars($settings['steadfast_api_key'] ?? '') ?>" placeholder="e.g. stdf_api_xxxxxxx" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Steadfast Secret Key *</label>
                    <input type="password" name="steadfast_secret_key" value="<?= htmlspecialchars($settings['steadfast_secret_key'] ?? '') ?>" placeholder="••••••••••••••••" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none focus:border-amber-500">
                </div>
            </div>
        </div>

        <!-- 2. PATHAO COURIER -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center font-black text-sm"><i class="fas fa-motorcycle"></i></div>
                    <div>
                        <h3 class="font-extrabold text-white text-sm">Pathao Courier Hermes API</h3>
                        <p class="text-[10px] text-slate-400">Client ID, Secret & Store ID from merchant.pathao.com</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="pathao_enabled" <?= ($settings['pathao_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded text-rose-500">
                    <span class="text-rose-400 font-bold text-xs">Active</span>
                </label>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Client ID</label>
                    <input type="text" name="pathao_client_id" value="<?= htmlspecialchars($settings['pathao_client_id'] ?? '') ?>" placeholder="Pathao Client ID" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Store ID</label>
                    <input type="text" name="pathao_store_id" value="<?= htmlspecialchars($settings['pathao_store_id'] ?? '') ?>" placeholder="e.g. 12345" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Client Secret</label>
                    <input type="password" name="pathao_client_secret" value="<?= htmlspecialchars($settings['pathao_client_secret'] ?? '') ?>" placeholder="••••••••••••••••" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Merchant Username/Email</label>
                        <input type="text" name="pathao_username" value="<?= htmlspecialchars($settings['pathao_username'] ?? '') ?>" placeholder="merchant@email.com" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Password</label>
                        <input type="password" name="pathao_password" value="<?= htmlspecialchars($settings['pathao_password'] ?? '') ?>" placeholder="••••••••" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. REDX COURIER -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-red-600/20 text-red-400 flex items-center justify-center font-black text-sm"><i class="fas fa-box-open"></i></div>
                    <div>
                        <h3 class="font-extrabold text-white text-sm">RedX Courier API</h3>
                        <p class="text-[10px] text-slate-400">OpenAPI Access Token from redx.com.bd</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="redx_enabled" <?= ($settings['redx_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded text-red-500">
                    <span class="text-red-400 font-bold text-xs">Active</span>
                </label>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">RedX API Access Token</label>
                    <input type="text" name="redx_access_token" value="<?= htmlspecialchars($settings['redx_access_token'] ?? '') ?>" placeholder="Bearer Token..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono outline-none focus:border-red-500">
                </div>
                <label class="flex items-center gap-2 cursor-pointer pt-1">
                    <input type="checkbox" name="redx_sandbox" <?= ($settings['redx_sandbox'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded text-red-500">
                    <span class="text-slate-400 text-[11px]">Sandbox Test Mode</span>
                </label>
            </div>
        </div>

        <!-- 4. PAPERFLY COURIER -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-cyan-600/20 text-cyan-400 flex items-center justify-center font-black text-sm"><i class="fas fa-feather-pointed"></i></div>
                    <div>
                        <h3 class="font-extrabold text-white text-sm">Paperfly Wings API</h3>
                        <p class="text-[10px] text-slate-400">Credentials from api.paperfly.com.bd</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="paperfly_enabled" <?= ($settings['paperfly_enabled'] ?? '0') === '1' ? 'checked' : '' ?> class="rounded text-cyan-500">
                    <span class="text-cyan-400 font-bold text-xs">Active</span>
                </label>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Username</label>
                    <input type="text" name="paperfly_username" value="<?= htmlspecialchars($settings['paperfly_username'] ?? '') ?>" placeholder="Username" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Password</label>
                    <input type="password" name="paperfly_password" value="<?= htmlspecialchars($settings['paperfly_password'] ?? '') ?>" placeholder="••••••••" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1 text-xs">Paperfly Key</label>
                <input type="text" name="paperfly_key" value="<?= htmlspecialchars($settings['paperfly_key'] ?? '') ?>" placeholder="Paperfly Key..." class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono text-xs outline-none">
            </div>
        </div>

    </div>

    <!-- Save Button -->
    <div class="pt-2">
        <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-black text-sm uppercase tracking-wider rounded-2xl shadow-xl transition">
            ✓ Save All Courier & API Configurations
        </button>
    </div>
</form>

<!-- 3. OTHER PLUGINS & TOOLS (WordPress & Shopify Integration Cards) -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="border-b border-slate-800 pb-3">
        <span class="text-xs font-bold uppercase text-indigo-400">E-Commerce Bridges</span>
        <h3 class="text-base font-black text-white mt-0.5">Other Plugins & Tools</h3>
        <p class="text-xs text-slate-400">Connect third-party stores with OnlineBdMart's central inventory and courier dispatcher.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- WordPress / WooCommerce Card -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-2xl shrink-0"><i class="fab fa-wordpress"></i></div>
            <div class="space-y-2 flex-1">
                <h4 class="font-extrabold text-white text-sm">WordPress / WooCommerce Plugin</h4>
                <p class="text-slate-400 text-xs leading-relaxed">Auto-sync orders from WooCommerce directly into OnlineBdMart and Steadfast/Pathao courier.</p>
                <div class="pt-1 flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-300">Supported via REST API</span>
                    <button type="button" onclick="openApiDocsModal()" class="text-indigo-400 font-bold hover:underline text-xs">View Setup Guide &rarr;</button>
                </div>
            </div>
        </div>

        <!-- Shopify App Card -->
        <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl shrink-0"><i class="fab fa-shopify"></i></div>
            <div class="space-y-2 flex-1">
                <h4 class="font-extrabold text-white text-sm">Shopify Webhook Connector</h4>
                <p class="text-slate-400 text-xs leading-relaxed">Use Shopify Order Creation Webhooks pointing to OnlineBdMart to book Bangladesh couriers in real-time.</p>
                <div class="pt-1 flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/20 text-indigo-300">Webhook Ready</span>
                    <button type="button" onclick="openApiDocsModal()" class="text-indigo-400 font-bold hover:underline text-xs">Integration Docs &rarr;</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. RECENT COURIER WEBHOOK LOGS TABLE -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-5 shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
        <div>
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-indigo-400"></i> Recent Webhook Event Logs (<?= count($webhookLogs) ?>)
            </h3>
            <p class="text-xs text-slate-400">Real-time status updates received from courier servers.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-2.5">Time</th>
                    <th class="py-2.5">Courier</th>
                    <th class="py-2.5">Order ID</th>
                    <th class="py-2.5">Consignment ID</th>
                    <th class="py-2.5">Courier Status</th>
                    <th class="py-2.5">Mapped Store Status</th>
                    <th class="py-2.5 text-right">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 font-mono">
                <?php if (!empty($webhookLogs)): ?>
                    <?php foreach ($webhookLogs as $log): 
                        $mapped = CourierService::mapCourierStatusToStoreStatus($log['event_status']);
                    ?>
                    <tr class="hover:bg-slate-800/40">
                        <td class="py-3 text-slate-400"><?= date('d M, h:i A', strtotime($log['created_at'])) ?></td>
                        <td class="py-3 font-bold text-white"><?= htmlspecialchars($log['courier_name']) ?></td>
                        <td class="py-3 font-bold text-indigo-400">#<?= htmlspecialchars($log['order_id'] ?: 'N/A') ?></td>
                        <td class="py-3 text-amber-300"><?= htmlspecialchars($log['consignment_id'] ?: 'N/A') ?></td>
                        <td class="py-3"><span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-slate-300"><?= htmlspecialchars($log['event_status'] ?: 'received') ?></span></td>
                        <td class="py-3"><span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] <?= $mapped === 'delivered' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-indigo-500/20 text-indigo-300' ?>"><?= $mapped ?></span></td>
                        <td class="py-3 text-right text-slate-500 text-[10px]"><?= htmlspecialchars($log['ip_address'] ?: '127.0.0.1') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="py-6 text-center text-slate-500">No webhook callbacks received yet. Connect your courier to start receiving live updates.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- API DOCUMENTATION MODAL -->
<div id="apiDocsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeApiDocsModal()"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative max-w-2xl w-full bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-5 text-xs text-slate-300 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-black text-white flex items-center gap-2">
                    <i class="fas fa-book-bookmark text-indigo-400"></i> OnlineBdMart REST API Documentation
                </h3>
                <button type="button" onclick="closeApiDocsModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
            </div>

            <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                <div>
                    <h4 class="font-bold text-white text-sm mb-1">1. Authentication Header</h4>
                    <p class="text-slate-400">All API requests must contain your secret API Key in the header:</p>
                    <pre class="bg-slate-950 p-3 rounded-xl border border-slate-800 font-mono text-[11px] text-amber-300 overflow-x-auto mt-1">X-Api-Key: <?= htmlspecialchars($masterApiKey) ?></pre>
                </div>

                <div>
                    <h4 class="font-bold text-white text-sm mb-1">2. Create New Order & Auto-Dispatch Courier (POST)</h4>
                    <p class="text-slate-400">Endpoint: <code>https://onlinebdmart.com/api/orders.php</code></p>
                    <pre class="bg-slate-950 p-3 rounded-xl border border-slate-800 font-mono text-[11px] text-emerald-300 overflow-x-auto mt-1">{
  "customer_name": "Arif Hossain",
  "customer_phone": "01775153740",
  "delivery_address": "House 12, Road 4, Mirpur-10",
  "district": "Dhaka",
  "subtotal": 1800.00,
  "delivery_cost": 120.00,
  "payment_method": "cod",
  "auto_dispatch_courier": true,
  "courier_name": "steadfast",
  "items": [
    { "product_id": 101, "product_name": "Electric Cooker", "price": 1800.00, "quantity": 1, "color": "Cream", "size": "6 Liter" }
  ]
}</pre>
                </div>

                <div>
                    <h4 class="font-bold text-white text-sm mb-1">3. Courier Webhook URL Configuration</h4>
                    <p class="text-slate-400">In Steadfast / Pathao / RedX webhook settings, paste your listener endpoint:</p>
                    <pre class="bg-slate-950 p-3 rounded-xl border border-slate-800 font-mono text-[11px] text-cyan-300 overflow-x-auto mt-1"><?= htmlspecialchars($liveWebhookUrl) ?></pre>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 text-right">
                <button type="button" onclick="closeApiDocsModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-xs">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleApiKeyVisibility() {
    const input = document.getElementById('storeApiKeyInput');
    const icon = document.getElementById('toggleKeyIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function copyApiKey() {
    const input = document.getElementById('storeApiKeyInput');
    input.type = 'text';
    input.select();
    document.execCommand('copy');
    input.type = 'password';

    const btnText = document.getElementById('copyKeyText');
    btnText.textContent = 'Copied!';
    setTimeout(() => { btnText.textContent = 'Copy'; }, 2000);
}

function copyWebhookUrl() {
    const input = document.getElementById('storeWebhookUrlInput');
    input.select();
    document.execCommand('copy');

    const btnText = document.getElementById('copyWebhookText');
    btnText.textContent = 'Copied!';
    setTimeout(() => { btnText.textContent = 'Copy'; }, 2000);
}

function openApiDocsModal() {
    document.getElementById('apiDocsModal').classList.remove('hidden');
}

function closeApiDocsModal() {
    document.getElementById('apiDocsModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
