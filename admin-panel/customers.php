<?php
$adminTitle = 'Customer Directory & Accounts';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_customer') {
            $userId = (int)$_POST['user_id'];
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $district = trim($_POST['district'] ?? 'Dhaka');
            $newPassword = trim($_POST['new_password'] ?? '');

            if ($newPassword) {
                $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, email = ?, address = ?, district = ?, password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $phone, $email, $address, $district, $hashed, $userId]);
                $msg = "Customer updated and password reset successfully!";
            } else {
                $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, email = ?, address = ?, district = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $phone, $email, $address, $district, $userId]);
                $msg = "Customer details updated successfully!";
            }
        } elseif ($action === 'create_customer') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '123456');
            $address = trim($_POST['address'] ?? '');
            $district = trim($_POST['district'] ?? 'Dhaka');

            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (name, phone, email, password, address, district, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$name, $phone, $email, $hashed, $address, $district]);
            $msg = "New customer account created!";
        } elseif ($action === 'delete_customer') {
            $userId = (int)$_POST['user_id'];
            $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            $msg = "Customer account deleted!";
        }
    }

    $users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE customer_phone = u.phone OR customer_email = u.email) as total_orders, (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE customer_phone = u.phone OR customer_email = u.email) as total_spent FROM users u ORDER BY u.id DESC")->fetchAll();
    
    // Fallback if users table is empty: populate from orders
    if (empty($users)) {
        $fromOrders = $db->query("SELECT customer_name as name, customer_phone as phone, customer_email as email, district_name as district, delivery_address as address, COUNT(*) as total_orders, SUM(total_amount) as total_spent, MAX(created_at) as created_at FROM orders GROUP BY customer_phone ORDER BY total_spent DESC")->fetchAll();
    } else {
        $fromOrders = [];
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    $users = [];
    $fromOrders = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1.5"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold">
    <i class="fas fa-circle-exclamation mr-1.5"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-6 rounded-3xl shadow-sm">
    <div>
        <span class="text-xs font-bold uppercase text-indigo-400">Buyer CRM & Profiles</span>
        <h2 class="text-lg font-black text-white mt-1">Customer Accounts (<?= count($users) + count($fromOrders) ?>)</h2>
        <p class="text-xs text-slate-400">Edit customer info, reset passwords from admin panel, and review lifetime purchase value.</p>
    </div>
    <button type="button" onclick="openAddCustomerModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl text-xs shadow-lg transition flex items-center gap-2">
        <i class="fas fa-user-plus"></i> <span>+ Add Customer</span>
    </button>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">Customer Name</th>
                    <th class="py-3">Phone</th>
                    <th class="py-3">Email</th>
                    <th class="py-3">District</th>
                    <th class="py-3 text-center">Orders</th>
                    <th class="py-3">Lifetime Spent</th>
                    <th class="py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php 
                $list = !empty($users) ? $users : $fromOrders;
                foreach ($list as $c): 
                    $uJson = htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8');
                    $wa = preg_replace('/[^0-9]/', '', $c['phone'] ?? '');
                    if (str_starts_with($wa, '0')) $wa = '88' . $wa;
                ?>
                <tr class="hover:bg-slate-800/40 transition">
                    <td class="py-3.5 font-bold text-white"><?= htmlspecialchars($c['name']) ?></td>
                    <td class="py-3.5 font-mono text-slate-300"><?= htmlspecialchars($c['phone']) ?></td>
                    <td class="py-3.5 text-slate-400"><?= htmlspecialchars($c['email'] ?: 'N/A') ?></td>
                    <td class="py-3.5 text-slate-300"><?= htmlspecialchars($c['district'] ?? 'Dhaka') ?></td>
                    <td class="py-3.5 text-center font-bold text-indigo-400"><?= $c['total_orders'] ?></td>
                    <td class="py-3.5 font-black text-emerald-400">৳<?= number_format($c['total_spent'], 2) ?></td>
                    <td class="py-3.5 text-right space-x-2">
                        <?php if ($wa): ?>
                        <a href="https://wa.me/<?= $wa ?>" target="_blank" class="p-2 bg-emerald-600/20 text-emerald-400 hover:bg-emerald-600 hover:text-white rounded-xl text-xs" title="WhatsApp Customer">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (isset($c['id'])): ?>
                        <button type="button" onclick='openEditCustomerModal(<?= $uJson ?>)' class="p-2 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white rounded-xl text-xs" title="Edit Customer & Reset Password">
                            <i class="fas fa-pen-to-square"></i>
                        </button>
                        <form method="POST" action="customers.php" onsubmit="return confirm('Delete customer?');" class="inline">
                            <input type="hidden" name="action" value="delete_customer">
                            <input type="hidden" name="user_id" value="<?= $c['id'] ?>">
                            <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500 hover:text-white rounded-xl text-xs" title="Delete Customer">
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

<!-- CUSTOMER ADD / EDIT MODAL -->
<div id="customerModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeCustomerModal()"></div>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden z-10">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-950">
                <h3 class="text-base font-extrabold text-white" id="custModalTitle">Edit Customer Account</h3>
                <button type="button" onclick="closeCustomerModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form method="POST" action="customers.php" class="p-6 sm:p-8 space-y-4 text-xs">
                <input type="hidden" name="action" id="custAction" value="update_customer">
                <input type="hidden" name="user_id" id="custUserId" value="">

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Full Name *</label>
                    <input type="text" name="name" id="cName" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Phone Number *</label>
                        <input type="tel" name="phone" id="cPhone" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Email Address</label>
                        <input type="email" name="email" id="cEmail" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">District</label>
                        <input type="text" name="district" id="cDistrict" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-amber-400 font-bold mb-1"><i class="fas fa-key mr-1"></i> Admin Password Reset</label>
                        <input type="password" name="new_password" id="cNewPassword" placeholder="Enter new password to reset" class="w-full px-3.5 py-2.5 bg-slate-950 border border-amber-500/30 rounded-xl text-white outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Street Address</label>
                    <textarea name="address" id="cAddress" rows="2" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" onclick="closeCustomerModal()" class="px-5 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddCustomerModal() {
    document.getElementById('custModalTitle').textContent = 'Add New Customer Account';
    document.getElementById('custAction').value = 'create_customer';
    document.getElementById('custUserId').value = '';
    document.getElementById('cName').value = '';
    document.getElementById('cPhone').value = '';
    document.getElementById('cEmail').value = '';
    document.getElementById('cDistrict').value = 'Dhaka';
    document.getElementById('cAddress').value = '';
    document.getElementById('cNewPassword').placeholder = 'Set Account Password';
    document.getElementById('customerModal').classList.remove('hidden');
}

function openEditCustomerModal(u) {
    document.getElementById('custModalTitle').textContent = 'Edit Customer: ' + u.name;
    document.getElementById('custAction').value = 'update_customer';
    document.getElementById('custUserId').value = u.id;
    document.getElementById('cName').value = u.name || '';
    document.getElementById('cPhone').value = u.phone || '';
    document.getElementById('cEmail').value = u.email || '';
    document.getElementById('cDistrict').value = u.district || 'Dhaka';
    document.getElementById('cAddress').value = u.address || '';
    document.getElementById('cNewPassword').value = '';
    document.getElementById('cNewPassword').placeholder = 'Leave blank to keep current';
    document.getElementById('customerModal').classList.remove('hidden');
}

function closeCustomerModal() {
    document.getElementById('customerModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
