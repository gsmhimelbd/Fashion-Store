<?php
$adminTitle = 'Customer Reviews Management';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $name = trim($_POST['author_name']);
            $rating = (int)$_POST['rating'];
            $review = trim($_POST['review_text']);
            $district = trim($_POST['district_name'] ?? 'Dhaka');

            $stmt = $db->prepare("INSERT INTO reviews (author_name, rating, review_text, district_name, is_approved, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
            $stmt->execute([$name, $rating, $review, $district]);
            $msg = 'Review published!';
        } elseif ($action === 'delete') {
            $id = (int)$_POST['review_id'];
            $db->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
            $msg = 'Review deleted!';
        }
    }

    $reviews = $db->query("SELECT * FROM reviews ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $reviews = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">Add Verified Review</h3>
        <form method="POST" action="reviews.php" class="space-y-3 text-xs">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Customer Name</label>
                <input type="text" name="author_name" required placeholder="e.g. Arif Hossain" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Customer District</label>
                <input type="text" name="district_name" value="Dhaka" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Star Rating (1-5)</label>
                <select name="rating" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    <option value="5">★★★★★ (5 Stars)</option>
                    <option value="4">★★★★☆ (4 Stars)</option>
                    <option value="3">★★★☆☆ (3 Stars)</option>
                </select>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Review Feedback</label>
                <textarea name="review_text" rows="3" required placeholder="Very satisfied with delivery and build quality..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
            </div>
            <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl shadow">Save Review</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">Customer Reviews (<?= count($reviews) ?>)</h3>
        <div class="space-y-3">
            <?php foreach ($reviews as $r): ?>
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-start justify-between gap-4 text-xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-white"><?= htmlspecialchars($r['author_name']) ?></span>
                        <span class="text-amber-400 font-mono">★ <?= $r['rating'] ?>.0</span>
                        <span class="text-[10px] text-slate-400">(<?= htmlspecialchars($r['district_name'] ?? 'Dhaka') ?>)</span>
                    </div>
                    <p class="text-slate-300 italic">"<?= htmlspecialchars($r['review_text']) ?>"</p>
                </div>
                <form method="POST" action="reviews.php" onsubmit="return confirm('Delete review?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                    <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-xl"><i class="fas fa-trash-can"></i></button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
