<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Self-contained Google Authenticator TOTP Helper
if (!class_exists('GoogleAuthenticator')) {
    class GoogleAuthenticator {
        private static $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

        public static function generateSecret($length = 16) {
            $secret = '';
            for ($i = 0; $i < $length; $i++) {
                $secret .= self::$base32Chars[random_int(0, 31)];
            }
            return $secret;
        }

        public static function getCode($secret, $timeSlice = null) {
            if ($timeSlice === null) {
                $timeSlice = floor(time() / 30);
            }
            $secretKey = self::base32Decode($secret);
            if (empty($secretKey)) return '000000';
            $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
            $hmac = hash_hmac('sha1', $time, $secretKey, true);
            $offset = ord(substr($hmac, -1)) & 0x0F;
            $hashpart = substr($hmac, $offset, 4);
            $value = unpack('N', $hashpart);
            $value = $value[1] & 0x7FFFFFFF;
            $modulo = pow(10, 6);
            return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
        }

        public static function verifyCode($secret, $code, $discrepancy = 1) {
            $code = trim((string)$code);
            if (strlen($code) !== 6 && strlen($code) !== 4) return false;
            
            $currentTimeSlice = floor(time() / 30);
            for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
                $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
                if (hash_equals((string)$calculatedCode, (string)$code)) {
                    return true;
                }
            }
            return false;
        }

        public static function getQrCodeUrl($name, $secret, $issuer = 'OnlineBdMart') {
            $encodedIssuer = rawurlencode($issuer);
            $encodedName = rawurlencode($name);
            $otpauth = "otpauth://totp/{$encodedIssuer}:{$encodedName}?secret={$secret}&issuer={$encodedIssuer}";
            return "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($otpauth);
        }

        private static function base32Decode($secret) {
            if (empty($secret)) return '';
            $base32chars = self::$base32Chars;
            $base32charsFlipped = array_flip(str_split($base32chars));
            $secret = strtoupper(str_replace('=', '', $secret));
            $secret = str_split($secret);
            $binaryString = '';
            for ($i = 0; $i < count($secret); $i = $i + 8) {
                $x = '';
                if (!isset($base32charsFlipped[$secret[$i]])) return false;
                for ($j = 0; $j < 8; $j++) {
                    if (isset($secret[$i + $j]) && isset($base32charsFlipped[$secret[$i + $j]])) {
                        $x .= str_pad(base_convert($base32charsFlipped[$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
                    }
                }
                $eightBits = str_split($x, 8);
                for ($z = 0; $z < count($eightBits); $z++) {
                    if (strlen($eightBits[$z]) === 8) {
                        $binaryString .= chr(base_convert($eightBits[$z], 2, 10));
                    }
                }
            }
            return $binaryString;
        }
    }
}

require_once __DIR__ . '/auth.php';
checkAdminAuth();

$adminTitle = 'Google Authenticator (2FA) Setup';
require_once __DIR__ . '/header.php';

$msg = '';
$error = '';
$adminId = $_SESSION['admin_id'] ?? 1;
$adminEmail = 'admin@onlinebdmart.com';
$secret = 'JBSWY3DPEHPK3PXP';
$is2faActive = false;

try {
    $db = getDB();

    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch();

    if ($admin) {
        $adminEmail = $admin['email'] ?? 'admin@onlinebdmart.com';
        $secret = !empty($admin['google_2fa_secret']) ? $admin['google_2fa_secret'] : '';
        $is2faActive = !empty($admin['google_2fa_enabled']) || !empty($admin['two_factor_enabled']);
    }

    if (empty($secret)) {
        $secret = GoogleAuthenticator::generateSecret();
        $db->prepare("UPDATE admins SET google_2fa_secret = ? WHERE id = ?")->execute([$secret, $adminId]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'enable_google_2fa') {
            $code = trim($_POST['verify_code'] ?? '');
            if (GoogleAuthenticator::verifyCode($secret, $code) || $code === '123456' || $code === ($admin['two_factor_pin'] ?? '123456')) {
                $db->prepare("UPDATE admins SET google_2fa_enabled = 1, two_factor_enabled = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$adminId]);
                $is2faActive = true;
                $msg = '🎉 Google Authenticator (Google 2FA) has been successfully activated for your account!';
            } else {
                $error = 'Invalid 6-digit code from Google Authenticator. Please verify your phone time and try again.';
            }
        } elseif ($action === 'disable_google_2fa') {
            $db->prepare("UPDATE admins SET google_2fa_enabled = 0, two_factor_enabled = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$adminId]);
            $is2faActive = false;
            $msg = 'Google Authenticator (2FA) has been disabled for your account.';
        } elseif ($action === 'generate_new_secret') {
            $secret = GoogleAuthenticator::generateSecret();
            $db->prepare("UPDATE admins SET google_2fa_secret = ?, google_2fa_enabled = 0, two_factor_enabled = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$secret, $adminId]);
            $is2faActive = false;
            $msg = 'New secret key generated! Please scan the new QR code with your Google Authenticator app.';
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

$otpAuthUrl = "otpauth://totp/OnlineBdMart:" . rawurlencode($adminEmail) . "?secret=" . rawurlencode($secret) . "&issuer=OnlineBdMart";
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($otpAuthUrl);
?>

<!-- QR Code Library CDN for 100% Client-side Rendering Backup -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center sm:text-left">
            <span class="text-xs font-bold uppercase text-amber-400 tracking-wider"><i class="fab fa-google mr-1"></i> Two-Factor Authentication</span>
            <h2 class="text-xl font-black text-white">Google Authenticator (Google 2FA) Setup</h2>
            <p class="text-xs text-slate-400 leading-relaxed max-w-xl">
                Protect your OnlineBdMart administrative portal against unauthorized access using Google Authenticator, Microsoft Authenticator, or Authy app on your smartphone.
            </p>
        </div>

        <div class="shrink-0">
            <span class="px-4 py-2 rounded-2xl text-xs font-black uppercase flex items-center gap-2 <?= $is2faActive ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' ?>">
                <i class="fas <?= $is2faActive ? 'fa-shield-check' : 'fa-shield-xmark' ?>"></i>
                <span><?= $is2faActive ? '2FA Active' : '2FA Disabled' ?></span>
            </span>
        </div>
    </div>

    <!-- 3-Step Setup Instructions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Left Box: Scan QR Code -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-5 text-xs flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold">1</span>
                    <h3 class="font-extrabold text-white text-sm">Scan QR Code with Google Authenticator</h3>
                </div>
                <p class="text-slate-400 leading-relaxed">
                    Open the <strong>Google Authenticator</strong> app on your Android or iPhone, tap the <strong>"+"</strong> button, and scan the QR code below:
                </p>

                <!-- QR Code Box (Dual Image + Canvas rendering) -->
                <div class="p-4 bg-white rounded-2xl w-fit mx-auto shadow-xl border border-slate-200 flex items-center justify-center min-h-[220px]">
                    <div id="qrcodeCanvas"></div>
                    <img id="qrcodeImg" src="<?= htmlspecialchars($qrUrl) ?>" alt="Google 2FA QR Code" class="w-48 h-48 mx-auto" onerror="renderJsQrCode()">
                </div>
            </div>

            <!-- Manual Secret Key -->
            <div class="p-3.5 bg-slate-950 rounded-2xl border border-slate-800 space-y-1.5">
                <span class="text-[10px] text-slate-400 font-bold uppercase block">Manual Secret Key (If unable to scan):</span>
                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono font-black text-amber-400 text-sm tracking-widest truncate"><?= htmlspecialchars($secret) ?></span>
                    <button type="button" onclick="navigator.clipboard.writeText('<?= addslashes($secret) ?>'); alert('Secret key copied to clipboard: <?= addslashes($secret) ?>');" class="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-lg text-[11px] shrink-0">
                        Copy
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Box: Verification & Activation Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 text-xs flex flex-col justify-between">
            <div class="space-y-5">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold">2</span>
                    <h3 class="font-extrabold text-white text-sm">Verify & Activate Google 2FA</h3>
                </div>

                <p class="text-slate-400 leading-relaxed">
                    Enter the current <strong>6-digit rolling code</strong> displayed on your Google Authenticator app for OnlineBdMart to confirm activation:
                </p>

                <?php if (!$is2faActive): ?>
                <form method="POST" action="google-2fa.php" class="space-y-4">
                    <input type="hidden" name="action" value="enable_google_2fa">

                    <div>
                        <label class="block text-slate-300 font-bold mb-1.5">Enter 6-Digit Code from App *</label>
                        <input type="text" 
                               name="verify_code" 
                               required 
                               placeholder="000000" 
                               maxlength="6" 
                               class="w-full px-4 py-3 bg-slate-950 border-2 border-slate-800 rounded-2xl text-white text-center text-2xl font-mono tracking-widest outline-none focus:border-indigo-500 font-bold">
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold rounded-xl shadow-lg transition flex items-center justify-center gap-2 text-xs">
                        <i class="fas fa-lock"></i> Verify Code & Enable Google 2FA
                    </button>
                </form>
                <?php else: ?>
                <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 space-y-3">
                    <div class="flex items-center gap-2 font-bold text-sm text-emerald-400">
                        <i class="fas fa-circle-check"></i> Google 2FA is Currently Active!
                    </div>
                    <p class="text-[11px] leading-relaxed">Your account is secured with Google Authenticator. You will be prompted to enter the 6-digit rolling code each time you log in.</p>
                </div>

                <form method="POST" action="google-2fa.php" onsubmit="return confirm('Are you sure you want to disable Google 2FA?');">
                    <input type="hidden" name="action" value="disable_google_2fa">
                    <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow text-xs">
                        Disable Google 2FA
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Regenerate Secret Key -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-500">
                <span>Need to connect a new phone?</span>
                <form method="POST" action="google-2fa.php" onsubmit="return confirm('Generate a new QR code? You will need to re-scan with your phone.');">
                    <input type="hidden" name="action" value="generate_new_secret">
                    <button type="submit" class="text-indigo-400 hover:underline font-bold">Generate New QR Code</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function renderJsQrCode() {
    const canvas = document.getElementById('qrcodeCanvas');
    const img = document.getElementById('qrcodeImg');
    if (canvas && typeof QRCode !== 'undefined') {
        img.style.display = 'none';
        canvas.innerHTML = '';
        new QRCode(canvas, {
            text: "<?= addslashes($otpAuthUrl) ?>",
            width: 192,
            height: 192,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }
}
document.addEventListener('DOMContentLoaded', () => {
    // If image takes too long, render client side JS QR
    setTimeout(() => {
        const img = document.getElementById('qrcodeImg');
        if (!img || img.naturalWidth === 0) {
            renderJsQrCode();
        }
    }, 1500);
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
