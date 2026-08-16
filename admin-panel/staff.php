<?php
$adminTitle = 'Staff & Salesman Permissions Manager';
require_once __DIR__ . '/header.php';
requirePermission('staff');

$msg = '';
$error = '';

$availableSections = [
    'products' => ['title' => 'Products Management', 'icon' => 'fa-boxes-stacked', 'desc' => 'Add, edit, and delete products & pricing'],
    'categories' => ['title' => 'Categories & Subcategories', 'icon' => 'fa-folder-tree', 'desc' => 'Manage parent categories and subcategories with emojis'],
    'wholesale' => ['title' => 'Wholesale & B2B Portal', 'icon' => 'fa-boxes-packing', 'desc' => 'Control bulk factory pricing and MOQs'],
    'orders' => ['title' => 'Orders & Invoices', 'icon' => 'fa-receipt', 'desc' => 'View orders, update status, and print tax invoices'],
    'delivery' => ['title' => '64 Districts Delivery', 'icon' => 'fa-truck-fast', 'desc' => 'Manage nationwide shipping rates and delivery times'],
    'customers' => ['title' => 'Customer Accounts & CRM', 'icon' => 'fa-users', 'desc' => 'View buyer directory, order history, and reset password'],
    'suppliers' => ['title' => 'Suppliers & Vendors', 'icon' => 'fa-truck-ramp-box', 'desc' => 'Manage manufacturer contacts and product supplies'],
    'messages' => ['title' => 'Customer Messages', 'icon' => 'fa-envelope', 'desc' => 'Customer support inbox & inquiries'],
    'analytics' => ['title' => 'Analytics & Cart Recovery', 'icon' => 'fa-chart-line', 'desc' => 'Live visitor timeline & 1-Click WhatsApp cart recovery'],
    'banners' => ['title' => 'Hero Slider & Banners', 'icon' => 'fa-images', 'desc' => 'Upload slider graphics and CTA links'],
    'deals' => ['title' => 'Flash Deals & Timer', 'icon' => 'fa-fire', 'desc' => 'Manage flash sales and countdown clock'],
    'blog' => ['title' => 'Blog & Buying Guides', 'icon' => 'fa-newspaper', 'desc' => 'Write buying guides and Google SEO metadata'],
    'reviews' => ['title' => 'Customer Reviews', 'icon' => 'fa-star', 'desc' => 'Moderate verified buyer ratings and testimonials'],
    'whatsapp' => ['title' => 'WhatsApp Settings', 'icon' => 'fa-whatsapp', 'desc' => 'Configure floating chat numbers and bulk hotline'],
    'payments' => ['title' => 'Payment Accounts', 'icon' => 'fa-credit-card', 'desc' => 'bKash, Nagad, Rocket, Bank deposit configuration'],
    'settings' => ['title' => 'Store Settings & Logo', 'icon' => 'fa-gear', 'desc' => 'Global logo, store details, and footer customization'],
    'staff' => ['title' => 'Staff & Role Manager', 'icon' => 'fa-user-shield', 'desc' => 'Create salesman accounts and assign custom permissions'],
];

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create_staff' || $action === 'update_staff') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            if (!$username) $username = strtolower(explode('@', $email)[0]);
            $role = trim($_POST['role'] ?? 'salesman');
            $password = trim($_POST['password'] ?? '');
            $selectedPerms = $_POST['perms'] ?? [];
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $twoFactorEnabled = isset($_POST['two_factor_enabled']) ? 1 : 0;
            $twoFactorPin = trim($_POST['two_factor_pin'] ?? '123456');

            $permsJson = ($role === 'superadmin') ? 'all' : json_encode(array_values($selectedPerms));

            if ($action === 'create_staff') {
                if (empty($name) || empty($email) || empty($password)) {
                    $error = 'Please fill out all required fields (Name, Email, and Password).';
                } else {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("INSERT INTO admins (name, username, email, password, role, permissions, is_active, two_factor_enabled, two_factor_pin, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
                    $stmt->execute([$name, $username, $email, $hashed, $role, $permsJson, $isActive, $twoFactorEnabled, $twoFactorPin]);
                    $msg = "New staff account ({$name}) created with assigned permissions & 2FA PIN!";
                }
            } else {
                $id = (int)$_POST['admin_id'];
                if ($password) {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE admins SET name = ?, username = ?, email = ?, password = ?, role = ?, permissions = ?, is_active = ?, two_factor_enabled = ?, two_factor_pin = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$name, $username, $email, $hashed, $role, $permsJson, $isActive, $twoFactorEnabled, $twoFactorPin, $id]);
                } else {
                    $stmt = $db->prepare("UPDATE admins SET name = ?, username = ?, email = ?, role = ?, permissions = ?, is_active = ?, two_factor_enabled = ?, two_factor_pin = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$name, $username, $email, $role, $permsJson, $isActive, $twoFactorEnabled, $twoFactorPin, $id]);
                }
                $msg = "Staff account ({$name}) updated successfully!";
            }
        } elseif ($action === 'delete_staff') {
            $id = (int)$_POST['admin_id'];
            if ($id === 1 || $id === (int)($_SESSION['admin_id'] ?? 0)) {
                $error = 'You cannot delete the primary Super Administrator account!';
            } else {
                $db->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
                $msg = 'Staff account deleted successfully!';
            }
        }
    }

    $staffList = $db->query("SELECT * FROM admins ORDER BY id ASC")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $staffList = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold flex items-center gap-2">
    <i class="fas fa-circle-check text-base"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold flex items-center gap-2">
    <i class="fas fa-circle-exclamation text-base"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- Header & Add Button -->
<div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-3xl shadow-sm">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Team & Role-Based Access Control</span>
        <h2 class="text-lg font-black text-white mt-1">Staff, Salesmen & Operators (<?= count($staffList) ?>)</h2>
        <p class="text-xs text-slate-400">Create staff accounts with custom section access. Staff will only be able to view and manage sections you select for them.</p>
    </div>
    <button type="button" onclick="openAddStaffModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl text-xs shadow-lg transition flex items-center gap-2">
        <i class="fas fa-user-plus"></i> <span>+ Add New Salesman / Staff</span>
    </button>
</div>

<!-- Staff Accounts Table -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">Staff Name</th>
                    <th class="py-3">Email (Login ID)</th>
                    <th class="py-3">Role</th>
                    <th class="py-3">Allowed Permissions</th>
                    <th class="py-3 text-center">Status</th>
                    <th class="py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php foreach ($staffList as $st): 
                    $role = $st['role'] ?? 'salesman';
                    $perms = ($role === 'superadmin' || ($st['permissions'] ?? '') === 'all') ? 'all' : json_decode($st['permissions'] ?? '[]', true);
                    if (!is_array($perms) && $perms !== 'all') {
                        $perms = array_filter(explode(',', $st['permissions'] ?? ''));
                    }
                    $stJson = htmlspecialchars(json_encode($st), ENT_QUOTES, 'UTF-8');
                ?>
                <tr class="hover:bg-slate-800/40 transition">
                    <td class="py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr <?= $role === 'superadmin' ? 'from-indigo-600 to-cyan-500' : 'from-amber-600 to-orange-500' ?> text-white font-bold flex items-center justify-center text-sm shadow">
                                <?= strtoupper(substr($st['name'] ?? 'S', 0, 1)) ?>
                            </div>
                            <div>
                                <span class="font-bold text-white block"><?= htmlspecialchars($st['name']) ?></span>
                                <span class="text-[10px] text-slate-400 font-mono">@<?= htmlspecialchars($st['username']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 font-mono text-slate-300"><?= htmlspecialchars($st['email']) ?></td>
                    <td class="py-3.5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase <?= $role === 'superadmin' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' ?>">
                            <?= $role === 'superadmin' ? '👑 Super Admin' : '💼 ' . ucfirst($role) ?>
                        </span>
                    </td>
                    <td class="py-3.5 max-w-xs">
                        <?php if ($perms === 'all' || $role === 'superadmin'): ?>
                            <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold text-[10px]">✓ Full Unrestricted Access</span>
                        <?php elseif (empty($perms)): ?>
                            <span class="text-slate-500 text-[10px]">No sections assigned</span>
                        <?php else: ?>
                            <div class="flex flex-wrap gap-1">
                                <?php foreach ($perms as $pKey): ?>
                                <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-[10px] font-bold text-indigo-300">
                                    <?= htmlspecialchars($availableSections[$pKey]['title'] ?? $pKey) ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="py-3.5 text-center">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $st['is_active'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' ?>">
                            <?= $st['is_active'] ? 'Active' : 'Disabled' ?>
                        </span>
                    </td>
                    <td class="py-3.5 text-right space-x-2">
                        <button type="button" onclick='openEditStaffModal(<?= $stJson ?>)' class="p-2 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white rounded-xl text-xs transition" title="Edit Permissions & Password">
                            <i class="fas fa-user-gear"></i>
                        </button>
                        <?php if ($st['id'] != 1 && $st['id'] != ($_SESSION['admin_id'] ?? 0)): ?>
                        <form method="POST" action="staff.php" onsubmit="return confirm('Are you sure you want to delete this staff member?');" class="inline">
                            <input type="hidden" name="action" value="delete_staff">
                            <input type="hidden" name="admin_id" value="<?= $st['id'] ?>">
                            <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500 hover:text-white rounded-xl text-xs transition" title="Delete Staff Account">
                                <i class="fas fa-trash-can"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD / EDIT STAFF POPUP MODAL -->
<div id="staffModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeStaffModal()"></div>
    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-3xl bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden z-10">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-950">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-lg">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white" id="staffModalTitle">Add New Salesman / Staff</h3>
                        <p class="text-xs text-slate-400">Set staff login credentials and select allowed management sections</p>
                    </div>
                </div>
                <button type="button" onclick="closeStaffModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form method="POST" action="staff.php" class="p-6 sm:p-8 space-y-5 text-xs max-h-[80vh] overflow-y-auto">
                <input type="hidden" name="action" id="staffAction" value="create_staff">
                <input type="hidden" name="admin_id" id="staffAdminId" value="">

                <!-- Basic Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Staff Member Name *</label>
                        <input type="text" name="name" id="sName" required placeholder="e.g. Tanvir (Salesman)" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Email Address (Staff Login ID) *</label>
                        <input type="email" name="email" id="sEmail" required placeholder="salesman@onlinebdmart.com" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Username</label>
                        <input type="text" name="username" id="sUsername" placeholder="tanvir_sales" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Role Type</label>
                        <select name="role" id="sRole" onchange="toggleRolePerms(this.value)" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none font-bold">
                            <option value="salesman">Salesman / Order Staff</option>
                            <option value="manager">Store Manager</option>
                            <option value="product_manager">Product Operator</option>
                            <option value="custom">Custom Permissions</option>
                            <option value="superadmin">Super Admin (Full Access)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1" id="passLabel">Login Password *</label>
                        <input type="password" name="password" id="sPassword" placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                </div>

                <!-- Custom Permission Checkboxes Grid -->
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4" id="permsContainer">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <span class="font-extrabold text-white text-xs"><i class="fas fa-key text-amber-400 mr-1.5"></i> Select Allowed Sections (অ্যাডমিন যা সিলেক্ট করবেন শুধু তার এক্সেস পাবে)</span>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="checkAllPerms(true)" class="text-[11px] text-indigo-400 hover:underline">Select All</button>
                            <span class="text-slate-600">|</span>
                            <button type="button" onclick="checkAllPerms(false)" class="text-[11px] text-slate-400 hover:underline">Clear</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php foreach ($availableSections as $secKey => $secInfo): ?>
                        <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-900 border border-slate-800/90 hover:border-indigo-500/50 cursor-pointer transition">
                            <input type="checkbox" name="perms[]" value="<?= $secKey ?>" class="perm-chk rounded text-indigo-600 mt-0.5 focus:ring-0">
                            <div>
                                <span class="font-bold text-white block text-xs"><i class="fas <?= $secInfo['icon'] ?> w-4 text-indigo-400 mr-1"></i> <?= $secInfo['title'] ?></span>
                                <span class="text-[10px] text-slate-400"><?= $secInfo['desc'] ?></span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 2FA Security PIN settings for this staff member -->
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-white text-xs"><i class="fas fa-shield-halved text-amber-400 mr-1.5"></i> 2-Factor Authentication (2FA) for this Staff</span>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="two_factor_enabled" id="s2faEnabled" value="1" class="rounded text-amber-500">
                            <span class="text-amber-400 font-bold">Require 2FA PIN on Login</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Staff 2FA Security PIN Code</label>
                        <input type="text" name="two_factor_pin" id="s2faPin" value="123456" placeholder="e.g. 123456" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="sIsActive" value="1" checked class="rounded text-emerald-600">
                        <span class="text-slate-300 font-bold">Account Active (Permit Login)</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" onclick="closeStaffModal()" class="px-5 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition" id="staffSubmitBtn">
                        Save Staff Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddStaffModal() {
    document.getElementById('staffModalTitle').textContent = 'Add New Salesman / Staff';
    document.getElementById('staffSubmitBtn').textContent = 'Create Staff Account';
    document.getElementById('staffAction').value = 'create_staff';
    document.getElementById('staffAdminId').value = '';
    document.getElementById('sName').value = '';
    document.getElementById('sEmail').value = '';
    document.getElementById('sUsername').value = '';
    document.getElementById('sRole').value = 'salesman';
    document.getElementById('sPassword').value = '';
    document.getElementById('sPassword').required = true;
    document.getElementById('passLabel').textContent = 'Login Password *';
    document.getElementById('sIsActive').checked = true;
    document.getElementById('s2faEnabled').checked = false;
    document.getElementById('s2faPin').value = '123456';

    // Default Salesman permissions: products, categories, wholesale, orders, messages
    const defaultSalesman = ['products', 'categories', 'wholesale', 'orders', 'messages'];
    document.querySelectorAll('.perm-chk').forEach(chk => {
        chk.checked = defaultSalesman.includes(chk.value);
    });

    document.getElementById('permsContainer').style.display = 'block';
    document.getElementById('staffModal').classList.remove('hidden');
}

function openEditStaffModal(st) {
    document.getElementById('staffModalTitle').textContent = 'Edit Staff Permissions: ' + st.name;
    document.getElementById('staffSubmitBtn').textContent = 'Update Staff Permissions';
    document.getElementById('staffAction').value = 'update_staff';
    document.getElementById('staffAdminId').value = st.id;
    document.getElementById('sName').value = st.name || '';
    document.getElementById('sEmail').value = st.email || '';
    document.getElementById('sUsername').value = st.username || '';
    document.getElementById('sRole').value = st.role || 'salesman';
    document.getElementById('sPassword').value = '';
    document.getElementById('sPassword').required = false;
    document.getElementById('passLabel').textContent = 'New Password (Leave blank to keep current)';
    document.getElementById('sIsActive').checked = Boolean(Number(st.is_active));
    document.getElementById('s2faEnabled').checked = Boolean(Number(st.two_factor_enabled));
    document.getElementById('s2faPin').value = st.two_factor_pin || '123456';

    // Parse permissions
    let perms = [];
    if (st.role === 'superadmin' || st.permissions === 'all') {
        perms = Object.keys(<?= json_encode($availableSections) ?>);
    } else {
        try { perms = JSON.parse(st.permissions || '[]'); } catch(e) {
            perms = (st.permissions || '').split(',').map(s => s.trim());
        }
    }

    document.querySelectorAll('.perm-chk').forEach(chk => {
        chk.checked = (st.role === 'superadmin' || perms.includes(chk.value));
    });

    toggleRolePerms(st.role || 'salesman');
    document.getElementById('staffModal').classList.remove('hidden');
}

function closeStaffModal() {
    document.getElementById('staffModal').classList.add('hidden');
}

function checkAllPerms(check) {
    document.querySelectorAll('.perm-chk').forEach(chk => chk.checked = check);
}

function toggleRolePerms(role) {
    const pContainer = document.getElementById('permsContainer');
    if (role === 'superadmin') {
        checkAllPerms(true);
        pContainer.style.opacity = '0.5';
    } else {
        pContainer.style.opacity = '1';
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
