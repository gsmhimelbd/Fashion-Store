<?php
$adminTitle = '64 Districts Delivery Management';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

try {
    $db = getDB();

    // Handle updating individual or bulk district
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_single') {
            $id = (int)$_POST['district_id'];
            $fee = (float)$_POST['delivery_fee'];
            $days = trim($_POST['estimated_days']);
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $stmt = $db->prepare("UPDATE districts SET delivery_fee = ?, estimated_days = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$fee, $days, $isActive, $id]);
            $msg = 'District delivery rate updated successfully!';
        } elseif ($action === 'bulk_division_rate') {
            $division = trim($_POST['division_name']);
            $fee = (float)$_POST['bulk_fee'];
            $days = trim($_POST['bulk_days']);

            $stmt = $db->prepare("UPDATE districts SET delivery_fee = ?, estimated_days = ? WHERE division_name = ?");
            $stmt->execute([$fee, $days, $division]);
            $msg = "All districts in {$division} Division updated to ৳{$fee} ({$days})!";
        } elseif ($action === 'add_custom') {
            $name = trim($_POST['district_name']);
            $division = trim($_POST['division_name']);
            $fee = (float)$_POST['delivery_fee'];
            $days = trim($_POST['estimated_days']);

            $stmt = $db->prepare("INSERT INTO districts (name, division_name, delivery_fee, estimated_days, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$name, $division, $fee, $days]);
            $msg = "New district {$name} added successfully!";
        }
    }

    $districts = $db->query("SELECT * FROM districts ORDER BY division_name ASC, name ASC")->fetchAll();
    $divisions = $db->query("SELECT DISTINCT division_name FROM districts ORDER BY division_name ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $error = $e->getMessage();
    $districts = [];
    $divisions = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="p-4 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 text-xs font-bold">
    <i class="fas fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Division-wide Bulk Update Box -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white"><i class="fas fa-bolt text-amber-400 mr-1.5"></i> Quick Bulk Division Rate</h3>
        <p class="text-xs text-slate-400">Update shipping fee and transit days for all districts in a selected division at once.</p>

        <form method="POST" action="delivery.php" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="bulk_division_rate">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Select Division</label>
                <select name="division_name" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    <?php foreach ($divisions as $div): ?>
                    <option value="<?= htmlspecialchars($div) ?>"><?= htmlspecialchars($div) ?> Division</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">New Delivery Fee (৳)</label>
                <input type="number" step="1" name="bulk_fee" value="120" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Estimated Delivery Days</label>
                <input type="text" name="bulk_days" value="2-4 days" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl text-xs shadow">Apply to Division</button>
        </form>
    </div>

    <!-- Add Custom District Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white"><i class="fas fa-plus-circle text-emerald-400 mr-1.5"></i> Add Custom District / Zone</h3>
        <p class="text-xs text-slate-400">Add a new delivery district, sub-district, or special economic zone.</p>

        <form method="POST" action="delivery.php" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="add_custom">
            <div>
                <label class="block text-slate-300 font-bold mb-1">District / Zone Name</label>
                <input type="text" name="district_name" placeholder="e.g. Uttara / Ashulia" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Division</label>
                <input type="text" name="division_name" placeholder="e.g. Dhaka" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Fee (৳)</label>
                    <input type="number" step="1" name="delivery_fee" value="80" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Est. Days</label>
                    <input type="text" name="estimated_days" value="1-2 days" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
            </div>
            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs shadow">Save Zone</button>
        </form>
    </div>

    <!-- Overview Stats -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4 flex flex-col justify-between">
        <div>
            <h3 class="text-sm font-extrabold text-white">Logistics Overview</h3>
            <p class="text-xs text-slate-400 mt-1">Full nationwide coverage configured with automatic checkout shipping charge recalculation.</p>
            <div class="mt-4 space-y-2 text-xs">
                <div class="flex justify-between p-2.5 bg-slate-950 rounded-xl border border-slate-800">
                    <span class="text-slate-400">Total Configured Districts:</span>
                    <span class="font-bold text-white"><?= count($districts) ?></span>
                </div>
                <div class="flex justify-between p-2.5 bg-slate-950 rounded-xl border border-slate-800">
                    <span class="text-slate-400">Total Divisions:</span>
                    <span class="font-bold text-white"><?= count($divisions) ?></span>
                </div>
                <div class="flex justify-between p-2.5 bg-slate-950 rounded-xl border border-slate-800">
                    <span class="text-slate-400">Free Shipping Threshold:</span>
                    <span class="font-bold text-emerald-400">Orders over ৳2,000</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 64-District Editable Table -->
<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h3 class="text-sm font-extrabold text-white">64 Bangladesh Districts Delivery Rates</h3>
        <input type="text" id="districtSearchInput" onkeyup="filterDistrictRows()" placeholder="Search district name..." class="px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white outline-none w-64">
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs" id="districtTable">
            <thead>
                <tr class="border-b border-slate-800 text-slate-400 font-bold uppercase text-[10px]">
                    <th class="py-3">District Name</th>
                    <th class="py-3">Division</th>
                    <th class="py-3">Delivery Fee (৳)</th>
                    <th class="py-3">Estimated Transit</th>
                    <th class="py-3">Status</th>
                    <th class="py-3 text-right">Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
                <?php foreach ($districts as $d): ?>
                <tr class="hover:bg-slate-800/40 transition district-row">
                    <form method="POST" action="delivery.php">
                        <input type="hidden" name="action" value="update_single">
                        <input type="hidden" name="district_id" value="<?= $d['id'] ?>">
                        <td class="py-3 font-bold text-white district-name-cell"><?= htmlspecialchars($d['name']) ?></td>
                        <td class="py-3 text-slate-400"><?= htmlspecialchars($d['division_name']) ?></td>
                        <td class="py-3">
                            <input type="number" step="1" name="delivery_fee" value="<?= (int)$d['delivery_fee'] ?>" class="w-20 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-white font-mono outline-none">
                        </td>
                        <td class="py-3">
                            <input type="text" name="estimated_days" value="<?= htmlspecialchars($d['estimated_days'] ?? '2-4 days') ?>" class="w-28 px-2.5 py-1 bg-slate-950 border border-slate-800 rounded-lg text-white outline-none">
                        </td>
                        <td class="py-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" <?= $d['is_active'] ? 'checked' : '' ?> class="rounded text-indigo-600 focus:ring-0">
                                <span class="text-[11px] <?= $d['is_active'] ? 'text-emerald-400' : 'text-slate-500' ?>"><?= $d['is_active'] ? 'Active' : 'Disabled' ?></span>
                            </label>
                        </td>
                        <td class="py-3 text-right">
                            <button type="submit" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg text-[11px] shadow">Save</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterDistrictRows() {
    const q = document.getElementById('districtSearchInput').value.toLowerCase();
    document.querySelectorAll('.district-row').forEach(row => {
        const name = row.querySelector('.district-name-cell').textContent.toLowerCase();
        row.style.display = name.includes(q) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
