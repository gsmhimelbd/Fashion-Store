<?php
$adminTitle = 'Blog Posts & Buying Guides';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';

function uploadBlogCoverFile($fileArray) {
    if (!empty($fileArray['name']) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
        if (in_array($ext, $allowed)) {
            $newName = uniqid('blog_', true) . '.' . $ext;
            $relPath = "uploads/blogs/" . $newName;
            $dest1 = __DIR__ . '/../' . $relPath;
            $dest2 = __DIR__ . '/../public/' . $relPath;
            @mkdir(dirname($dest1), 0777, true);
            @mkdir(dirname($dest2), 0777, true);
            if (@move_uploaded_file($fileArray['tmp_name'], $dest1)) {
                @copy($dest1, $dest2);
                return $relPath;
            }
        }
    }
    return null;
}

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $title = trim($_POST['title'] ?? '');
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            if (!$slug) $slug = 'guide-' . time();
            $category = trim($_POST['category'] ?? 'Buying Guide');
            $author = trim($_POST['author'] ?? 'OnlineBdMart Team');
            $summary = trim($_POST['summary'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $metaTitle = trim($_POST['meta_title'] ?? $title);
            $metaDesc = trim($_POST['meta_description'] ?? $summary);
            $metaKeywords = trim($_POST['meta_keywords'] ?? '');

            $imagePath = trim($_POST['existing_image'] ?? 'images/hero/hero-1.jpg');
            if (isset($_FILES['blog_image']) && $_FILES['blog_image']['error'] === UPLOAD_ERR_OK) {
                $up = uploadBlogCoverFile($_FILES['blog_image']);
                if ($up) $imagePath = $up;
            }

            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, category, author, summary, content, image_path, meta_title, meta_description, meta_keywords, is_published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP)");
                $stmt->execute([$title, $slug, $category, $author, $summary, $content, $imagePath, $metaTitle, $metaDesc, $metaKeywords]);
                $msg = 'Blog article published with cover picture & SEO metadata!';
            } else {
                $id = (int)$_POST['post_id'];
                $stmt = $db->prepare("UPDATE blog_posts SET title = ?, slug = ?, category = ?, author = ?, summary = ?, content = ?, image_path = ?, meta_title = ?, meta_description = ?, meta_keywords = ? WHERE id = ?");
                $stmt->execute([$title, $slug, $category, $author, $summary, $content, $imagePath, $metaTitle, $metaDesc, $metaKeywords, $id]);
                $msg = 'Blog article updated successfully!';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['post_id'];
            $db->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
            $msg = 'Article removed!';
        }
    }

    $posts = $db->query("SELECT * FROM blog_posts ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
    $posts = [];
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
        <span class="text-xs font-bold uppercase text-indigo-400">Content & SEO</span>
        <h2 class="text-lg font-black text-white mt-1">Articles & Buying Guides (<?= count($posts) ?>)</h2>
        <p class="text-xs text-slate-400">Publish guides with cover photos from your computer and custom Google SEO metadata.</p>
    </div>
    <button type="button" onclick="openAddBlogModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl text-xs shadow-lg transition flex items-center gap-2">
        <i class="fas fa-pen"></i> <span>+ Write New Article</span>
    </button>
</div>

<!-- Blog Articles Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($posts as $p): 
        $pJson = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');
        $cover = !empty($p['image_path']) ? $p['image_path'] : 'images/hero/hero-1.jpg';
    ?>
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-sm flex flex-col justify-between">
        <div class="space-y-4">
            <div class="relative aspect-video bg-slate-950">
                <img src="/<?= ltrim($cover, '/') ?>" class="w-full h-full object-cover">
                <span class="absolute top-3 left-3 px-2.5 py-1 bg-black/70 backdrop-blur-md text-indigo-400 rounded-full text-[10px] font-bold">
                    <?= htmlspecialchars($p['category'] ?? 'Buying Guide') ?>
                </span>
            </div>

            <div class="p-6 pt-0 space-y-2 text-xs">
                <h3 class="text-sm font-extrabold text-white line-clamp-2"><?= htmlspecialchars($p['title']) ?></h3>
                <p class="text-slate-400 line-clamp-2"><?= htmlspecialchars($p['summary'] ?? $p['excerpt'] ?? '') ?></p>
                <?php if (!empty($p['meta_title'])): ?>
                <div class="p-2 rounded-xl bg-slate-950 border border-slate-800/80 text-[10px] text-slate-400">
                    <span class="text-emerald-400 font-bold">SEO Title:</span> <?= htmlspecialchars($p['meta_title']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-6 pt-0 flex items-center justify-between border-t border-slate-800/80 mt-2">
            <span class="text-slate-500 text-[10px]"><?= date('d M Y', strtotime($p['created_at'])) ?></span>
            <div class="flex items-center gap-2">
                <button type="button" onclick='openEditBlogModal(<?= $pJson ?>)' class="p-2 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white rounded-xl text-xs" title="Edit Article & SEO">
                    <i class="fas fa-pen-to-square"></i>
                </button>
                <form method="POST" action="blog.php" onsubmit="return confirm('Delete article?');" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="p-2 bg-rose-500/20 text-rose-400 hover:bg-rose-500 hover:text-white rounded-xl text-xs" title="Delete">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- BLOG ADD / EDIT MODAL -->
<div id="blogModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeBlogModal()"></div>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden z-10">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-950">
                <h3 class="text-base font-extrabold text-white" id="blogModalTitle">Write Article</h3>
                <button type="button" onclick="closeBlogModal()" class="text-slate-400 hover:text-white"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form method="POST" action="blog.php" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-4 text-xs max-h-[80vh] overflow-y-auto">
                <input type="hidden" name="action" id="blogAction" value="create">
                <input type="hidden" name="post_id" id="blogPostId" value="">
                <input type="hidden" name="existing_image" id="blogExistingImage" value="images/hero/hero-1.jpg">

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Article Title *</label>
                    <input type="text" name="title" id="artTitle" required placeholder="e.g. Top 5 Luxury Watches in 2026" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Category</label>
                        <select name="category" id="artCategory" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                            <option value="Buying Guide">Buying Guide</option>
                            <option value="Tips & Tricks">Tips & Tricks</option>
                            <option value="Wholesale & B2B">Wholesale & B2B</option>
                            <option value="Fashion Trends">Fashion Trends</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Author Name</label>
                        <input type="text" name="author" id="artAuthor" value="OnlineBdMart Team" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
                    <label class="block text-white font-bold"><i class="fas fa-upload text-indigo-400 mr-1"></i> Upload Cover Image (from Local Computer)</label>
                    <input type="file" name="blog_image" accept="image/*" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-slate-300 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white">
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Short Summary / Excerpt</label>
                    <textarea name="summary" id="artSummary" rows="2" placeholder="Brief 2-line preview of the article..." class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Full Article Content</label>
                    <textarea name="content" id="artContent" rows="6" placeholder="Full guide content..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
                </div>

                <!-- SEO Section -->
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 space-y-3">
                    <h4 class="font-bold text-emerald-400 text-xs"><i class="fas fa-magnifying-glass mr-1"></i> SEO Metadata for Google Search</h4>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Meta Title</label>
                        <input type="text" name="meta_title" id="artMetaTitle" placeholder="Custom SEO Title for Google" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Meta Description</label>
                        <textarea name="meta_description" id="artMetaDesc" rows="2" placeholder="Google snippet description..." class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Meta Keywords (Comma separated)</label>
                        <input type="text" name="meta_keywords" id="artMetaKeywords" placeholder="watches bd, luxury wallets, best accessories 2026" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white outline-none">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" onclick="closeBlogModal()" class="px-5 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-xl shadow">Publish Article</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddBlogModal() {
    document.getElementById('blogModalTitle').textContent = 'Write New Article';
    document.getElementById('blogAction').value = 'create';
    document.getElementById('blogPostId').value = '';
    document.getElementById('blogExistingImage').value = 'images/hero/hero-1.jpg';
    document.getElementById('artTitle').value = '';
    document.getElementById('artCategory').value = 'Buying Guide';
    document.getElementById('artAuthor').value = 'OnlineBdMart Team';
    document.getElementById('artSummary').value = '';
    document.getElementById('artContent').value = '';
    document.getElementById('artMetaTitle').value = '';
    document.getElementById('artMetaDesc').value = '';
    document.getElementById('artMetaKeywords').value = '';
    document.getElementById('blogModal').classList.remove('hidden');
}

function openEditBlogModal(p) {
    document.getElementById('blogModalTitle').textContent = 'Edit Article: ' + p.title;
    document.getElementById('blogAction').value = 'update';
    document.getElementById('blogPostId').value = p.id;
    document.getElementById('blogExistingImage').value = p.image_path || '';
    document.getElementById('artTitle').value = p.title || '';
    document.getElementById('artCategory').value = p.category || 'Buying Guide';
    document.getElementById('artAuthor').value = p.author || 'OnlineBdMart Team';
    document.getElementById('artSummary').value = p.summary || p.excerpt || '';
    document.getElementById('artContent').value = p.content || '';
    document.getElementById('artMetaTitle').value = p.meta_title || '';
    document.getElementById('artMetaDesc').value = p.meta_description || '';
    document.getElementById('artMetaKeywords').value = p.meta_keywords || '';
    document.getElementById('blogModal').classList.remove('hidden');
}

function closeBlogModal() {
    document.getElementById('blogModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
