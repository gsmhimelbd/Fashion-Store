<?php
$adminTitle = 'Customer Product Reviews & Moderation';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

try {
    $db = getDB();

    // Auto-heal reviews schema
    try {
        @$db->exec("ALTER TABLE `reviews` ADD COLUMN `product_id` int(11) DEFAULT NULL");
        @$db->exec("ALTER TABLE `reviews` ADD COLUMN `author_name` varchar(191) NOT NULL DEFAULT 'Customer'");
        @$db->exec("ALTER TABLE `reviews` ADD COLUMN `rating` int(11) NOT NULL DEFAULT 5");
        @$db->exec("ALTER TABLE `reviews` ADD COLUMN `review_text` text DEFAULT NULL");
        @$db->exec("ALTER TABLE `reviews` ADD COLUMN `district_name` varchar(100) DEFAULT 'Dhaka'");
        @$db->exec("ALTER TABLE `reviews` ADD COLUMN `phone` varchar(100) DEFAULT NULL");
        @$db->exec("ALTER TABLE `reviews` ADD COLUMN `is_approved` tinyint(1) DEFAULT 0");
    } catch (Exception $ex) {}

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create_admin') {
            $name = trim($_POST['author_name'] ?? 'Verified Customer');
            $prodId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
            $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
            $review = trim($_POST['review_text'] ?? '');
            $district = trim($_POST['district_name'] ?? 'Dhaka');
            $isApproved = isset($_POST['is_approved']) ? 1 : 1;

            $stmt = $db->prepare("INSERT INTO reviews (`product_id`, `author_name`, `rating`, `review_text`, `district_name`, `is_approved`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            $stmt->execute([$prodId, $name, $rating, $review, $district, $isApproved]);
            $msg = '✓ Verified review added successfully!';
        } elseif ($action === 'approve') {
            $id = (int)$_POST['review_id'];
            $db->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?")->execute([$id]);
            $msg = '✓ Review approved and published on product page!';
        } elseif ($action === 'unapprove') {
            $id = (int)$_POST['review_id'];
            $db->prepare("UPDATE reviews SET is_approved = 0 WHERE id = ?")->execute([$id]);
            $msg = '✓ Review unapproved and hidden from storefront.';
        } elseif ($action === 'delete') {
            $id = (int)$_POST['review_id'];
            $db->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
            $msg = '✓ Review deleted permanently!';
        }
    }

    $allProducts = $db->query("SELECT id, name, slug, image_path FROM products WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
    $reviews = $db->query("SELECT r.*, p.name as product_name, p.slug as product_slug, p.image_path as product_image FROM reviews r LEFT JOIN products p ON r.product_id = p.id ORDER BY r.id DESC")->fetchAll();

    $pendingCount = 0;
    $approvedCount = 0;
    foreach ($reviews as $r) {
        if (!empty($r['is_approved'])) $approvedCount++;
        else $pendingCount++;
    }

} catch (Exception $e) {
    $error = $e->getMessage();
    $reviews = [];
    $allProducts = [];
    $pendingCount = 0;
    $approvedCount = 0;
}
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

<!-- Top Metrics -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
        <span class="text-[10px] font-black uppercase text-amber-400 tracking-wider flex items-center gap-1.5"><i class="fas fa-clock"></i> Pending Approval</span>
        <div class="text-2xl font-black text-white"><?= $pendingCount ?></div>
        <p class="text-[10px] text-slate-500">Awaiting your approval</p>
    </div>
    <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
        <span class="text-[10px] font-black uppercase text-emerald-400 tracking-wider flex items-center gap-1.5"><i class="fas fa-circle-check"></i> Live & Approved</span>
        <div class="text-2xl font-black text-white"><?= $approvedCount ?></div>
        <p class="text-[10px] text-slate-500">Visible on product pages</p>
    </div>
    <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
        <span class="text-[10px] font-black uppercase text-indigo-400 tracking-wider flex items-center gap-1.5"><i class="fas fa-comments"></i> Total Reviews</span>
        <div class="text-2xl font-black text-white"><?= count($reviews) ?></div>
        <p class="text-[10px] text-slate-500">All submitted feedback</p>
    </div>
    <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-1">
        <span class="text-[10px] font-black uppercase text-rose-400 tracking-wider flex items-center gap-1.5"><i class="fas fa-star"></i> Store Rating</span>
        <div class="text-2xl font-black text-amber-400">4.9 ★</div>
        <p class="text-[10px] text-slate-500">Customer satisfaction</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left: Add Admin Verified Review Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4 h-fit">
        <div class="border-b border-slate-800 pb-3">
            <span class="text-xs font-bold uppercase text-indigo-400">Feedback Manager</span>
            <h3 class="text-base font-black text-white mt-0.5">Add Verified Review</h3>
            <p class="text-xs text-slate-400">Add reviews for specific products or general store testimonials.</p>
        </div>

        <form method="POST" action="reviews.php" class="space-y-3.5 text-xs">
            <input type="hidden" name="action" value="create_admin">

            <div>
                <label class="block text-slate-300 font-bold mb-1">Target Product (প্রোডাক্ট নির্বাচন করুন)</label>
                <select name="product_id" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
                    <option value="">General Store Review (সাধারণ রিভিউ)</option>
                    <?php foreach ($allProducts as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Customer Name (কাস্টমারের নাম) *</label>
                <input type="text" name="author_name" required placeholder="e.g. Mahfuzur Rahman" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">District / City</label>
                    <input type="text" name="district_name" value="Dhaka" placeholder="e.g. Chittagong" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Star Rating</label>
                    <select name="rating" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-amber-400 font-bold outline-none">
                        <option value="5">★★★★★ (5.0)</option>
                        <option value="4">★★★★☆ (4.0)</option>
                        <option value="3">★★★☆☆ (3.0)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-slate-300 font-bold mb-1">Review Feedback (রিভিউ মন্তব্য) *</label>
                <textarea name="review_text" rows="3" required placeholder="100% original product and got fast delivery in 2 days..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none focus:border-indigo-500"></textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow-lg transition">
                + Publish Verified Review
            </button>
        </form>
    </div>

    <!-- Right: Customer Submitted Reviews Moderation List -->
    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div>
                <h3 class="text-base font-extrabold text-white flex items-center gap-2">
                    <i class="fas fa-star text-amber-400"></i> Customer Reviews & Moderation (<?= count($reviews) ?>)
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Reviews submitted by customers will remain <b>Pending</b> until you click <b>Approve</b>.</p>
            </div>
        </div>

        <?php if (!empty($reviews)): ?>
        <div class="space-y-3.5">
            <?php foreach ($reviews as $r): 
                $isApproved = !empty($r['is_approved']);
                $stars = max(1, min(5, (int)($r['rating'] ?? 5)));
            ?>
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-950 border <?= $isApproved ? 'border-slate-800 hover:border-slate-700' : 'border-amber-500/40 bg-amber-500/[0.03]' ?> space-y-3 text-xs transition">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-sm shrink-0">
                            <?= strtoupper(substr($r['author_name'] ?? 'C', 0, 1)) ?>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-extrabold text-white text-sm"><?= htmlspecialchars($r['author_name']) ?></h4>
                                <span class="text-amber-400 font-bold"><?= str_repeat('★', $stars) ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                <span><i class="fas fa-location-dot text-rose-400 mr-1"></i><?= htmlspecialchars($r['district_name'] ?? 'Bangladesh') ?></span>
                                <span>•</span>
                                <span><?= date('d M Y, h:i A', strtotime($r['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Status Badge -->
                    <div class="flex items-center gap-2">
                        <?php if ($isApproved): ?>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                            <i class="fas fa-circle-check"></i> <span>Live on Product</span>
                        </span>
                        <?php else: ?>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/20 text-amber-300 border border-amber-500/40 flex items-center gap-1 animate-pulse">
                            <i class="fas fa-clock"></i> <span>Pending Approval</span>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Link (if attached to specific product) -->
                <?php if (!empty($r['product_name'])): ?>
                <div class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-[10px] uppercase font-bold text-indigo-400">Product:</span>
                        <a href="../product.php?slug=<?= htmlspecialchars($r['product_slug'] ?? '') ?>" target="_blank" class="font-bold text-white hover:text-indigo-400 truncate">
                            <?= htmlspecialchars($r['product_name']) ?>
                        </a>
                    </div>
                    <a href="../product.php?slug=<?= htmlspecialchars($r['product_slug'] ?? '') ?>" target="_blank" class="text-indigo-400 hover:underline text-[10px] shrink-0">View Page &rarr;</a>
                </div>
                <?php endif; ?>

                <!-- Review Content -->
                <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-800/60 text-slate-300 italic leading-relaxed">
                    "<?= nl2br(htmlspecialchars($r['review_text'])) ?>"
                </div>

                <!-- Admin Actions: Approve, Unapprove, Delete -->
                <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <?php if (!$isApproved): ?>
                        <form method="POST" action="reviews.php" class="inline">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black rounded-xl text-xs flex items-center gap-1.5 shadow transition">
                                <i class="fas fa-check"></i> <span>✓ Approve & Publish</span>
                            </button>
                        </form>
                        <?php else: ?>
                        <form method="POST" action="reviews.php" class="inline">
                            <input type="hidden" name="action" value="unapprove">
                            <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white font-bold rounded-xl text-[11px] flex items-center gap-1 transition">
                                <i class="fas fa-pause"></i> <span>Hide / Unapprove</span>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="reviews.php" onsubmit="return confirm('Delete this review permanently?');" class="inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="p-2 bg-rose-500/20 hover:bg-rose-500 text-rose-400 hover:text-white rounded-xl transition" title="Delete Review">
                            <i class="fas fa-trash-can"></i>
                        </button>
                    </form>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="p-10 text-center bg-slate-950 rounded-2xl border border-slate-800 text-slate-400 space-y-2">
            <i class="fas fa-comments text-3xl text-slate-600"></i>
            <p class="font-bold text-slate-300">No Customer Reviews Yet</p>
            <p class="text-xs">When customers submit reviews on product pages, they will show up here for moderation.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
