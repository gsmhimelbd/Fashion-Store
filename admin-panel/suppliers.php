<?php
$adminTitle = 'Suppliers & Vendors';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $name = trim($_POST['name']);
            $contact = trim($_POST['contact_person']);
            $phone = trim($_POST['phone']);
            $category = trim($_POST['category']);

            $stmt = $db->prepare("INSERT INTO suppliers (name, contact_person, phone, category, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $contact, $phone, $category]);
            $msg = 'Supplier added!';
        } elseif ($action === 'delete') {
            $id = (int)$_POST['supplier_id'];
            $db->prepare("DELETE FROM suppliers WHERE id = ?")->execute([$id]);
            $msg = 'Supplier deleted!';
        }
    }

    $suppliers = $db->query("SELECT * FROM suppliers ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $suppliers = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">Add New Supplier</h3>
        <form method="POST" action="suppliers.php" class="space-y-3 text-xs">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Company / Supplier Name</label>
                <input type="text" name="name" required placeholder="e.g. BD Watch Importers Ltd" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Contact Person</label>
                <input type="text" name="contact_person" placeholder="e.g. Kamal Hossain" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Phone Number</label>
                <input type="text" name="phone" placeholder="017xxxxxxxx" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Supply Category</label>
                <input type="text" name="category" placeholder="e.g. Chronograph Watches & Belts" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow">Save Supplier</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">Active Suppliers (<?= count($suppliers) ?>)</h3>
        <div class="space-y-3">
            <?php foreach ($suppliers as $s): ?>
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                <div>
                    <h4 class="font-bold text-white"><?= htmlspecialchars($s['name']) ?></h4>
                    <p class="text-slate-400 text-[11px]"><?= htmlspecialchars($s['contact_person'] ?? '') ?> • <?= htmlspecialchars($s['phone'] ?? '') ?></p>
                    <span class="inline-block mt-1 text-[10px] bg-slate-800 px-2 py-0.5 rounded text-indigo-300"><?= htmlspecialchars($s['category'] ?? 'General') ?></span>
                </div>
                <form method="POST" action="suppliers.php" onsubmit="return confirm('Delete supplier?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="supplier_id" value="<?= $s['id'] ?>">
                    <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-xl"><i class="fas fa-trash-can"></i></button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
