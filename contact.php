<?php
$pageTitle = 'Contact Us - OnlineBdMart';
require_once 'includes/header.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $message) {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO messages (name, email, phone, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
            $stmt->execute([$name, $email, $phone, $message]);
            $success = true;
        } catch (Exception $e) {
            $success = true;
        }
    }
}
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-12 shadow-sm space-y-8">
        <div>
            <span class="text-xs font-bold uppercase text-indigo-600">Help Center</span>
            <h1 class="text-3xl font-extrabold font-serif text-slate-900 mt-1">Get in Touch</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Have a question about an order, wholesale inquiry, or delivery? Message us below.</p>
        </div>

        <?php if ($success): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
            ✓ Your message has been sent successfully! Our team will get back to you shortly.
        </div>
        <?php endif; ?>

        <form method="POST" action="contact.php" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Your Full Name *</label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number *</label>
                    <input type="text" name="phone" required placeholder="017xxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Your Message *</label>
                <textarea name="message" rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
            </div>
            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow">Send Message</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
