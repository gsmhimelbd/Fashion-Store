<?php
$adminTitle = 'Blog & Buying Guides';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $title = trim($_POST['title']);
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            $excerpt = trim($_POST['excerpt']);
            $content = trim($_POST['content']);

            $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, is_published, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
            $stmt->execute([$title, $slug, $excerpt, $content]);
            $msg = 'Article published!';
        } elseif ($action === 'delete') {
            $id = (int)$_POST['post_id'];
            $db->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
            $msg = 'Article removed!';
        }
    }

    $posts = $db->query("SELECT * FROM blog_posts ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $posts = [];
}
?>

<?php if ($msg): ?>
<div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-bold">
    <i class="fas fa-circle-check mr-1"></i> <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">Write Article</h3>
        <form method="POST" action="blog.php" class="space-y-3 text-xs">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="block text-slate-300 font-bold mb-1">Article Title</label>
                <input type="text" name="title" required placeholder="e.g. Best 5 Leather Bags in 2026" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Short Excerpt</label>
                <textarea name="excerpt" rows="2" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
            </div>
            <div>
                <label class="block text-slate-300 font-bold mb-1">Full Content (HTML / Text)</label>
                <textarea name="content" rows="6" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
            </div>
            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow">Publish Post</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-white">Published Articles (<?= count($posts) ?>)</h3>
        <div class="space-y-3">
            <?php foreach ($posts as $p): ?>
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                <div>
                    <h4 class="font-bold text-white"><?= htmlspecialchars($p['title']) ?></h4>
                    <p class="text-slate-400 text-[11px] line-clamp-1 mt-0.5"><?= htmlspecialchars($p['excerpt'] ?? '') ?></p>
                </div>
                <form method="POST" action="blog.php" onsubmit="return confirm('Delete article?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 rounded-xl"><i class="fas fa-trash-can"></i></button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
