<?php
$adminTitle = 'Customer Messages';
require_once __DIR__ . '/header.php';

$msg = '';
try {
    $db = getDB();

    if (isset($_GET['mark_read'])) {
        $id = (int)$_GET['mark_read'];
        $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$id]);
        header('Location: messages.php');
        exit;
    }

    $messages = $db->query("SELECT * FROM messages ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    $messages = [];
}
?>

<div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-black text-white">Customer Inquiries Inbox (<?= count($messages) ?>)</h2>
    </div>

    <div class="space-y-4">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $m): ?>
            <div class="p-5 rounded-2xl bg-slate-950 border <?= $m['is_read'] ? 'border-slate-800' : 'border-indigo-500/40 bg-indigo-950/10' ?> space-y-3 text-xs">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-800 pb-2">
                    <div>
                        <span class="font-bold text-white"><?= htmlspecialchars($m['name']) ?></span>
                        <span class="text-slate-400 text-[11px] ml-2"><?= htmlspecialchars($m['phone'] ?? '') ?> • <?= htmlspecialchars($m['email'] ?? '') ?></span>
                    </div>
                    <span class="text-slate-500 text-[11px]"><?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></span>
                </div>
                <p class="text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
                <div class="flex items-center gap-3 pt-2">
                    <?php if ($m['phone']): 
                        $wa = preg_replace('/[^0-9]/', '', $m['phone']);
                        if (str_starts_with($wa, '0')) $wa = '88' . $wa;
                    ?>
                    <a href="https://wa.me/<?= $wa ?>" target="_blank" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 rounded-lg text-white font-bold text-[11px] inline-flex items-center gap-1">
                        <i class="fab fa-whatsapp"></i> Reply via WhatsApp
                    </a>
                    <?php endif; ?>
                    <?php if (!$m['is_read']): ?>
                    <a href="messages.php?mark_read=<?= $m['id'] ?>" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 rounded-lg text-slate-300 font-bold text-[11px]">Mark as Read</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-xs text-slate-500 py-6 text-center">No messages in inbox.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
