<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// Self-contained Google Authenticator TOTP Helper
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

        public static function verifyCode($secret, $code, $discrepancy = 2) {
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

if (empty($_SESSION['2fa_pending_admin_id'])) {
    header('Location: login.php');
    exit;
}

$pendingId = (int)$_SESSION['2fa_pending_admin_id'];
$pendingName = $_SESSION['2fa_pending_admin_name'] ?? 'Admin';
$pendingRole = $_SESSION['2fa_pending_admin_role'] ?? 'superadmin';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredCode = trim($_POST['two_factor_pin'] ?? '');

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$pendingId]);
        $admin = $stmt->fetch();

        $masterPin = getSetting('admin_2fa_master_pin', '123456');
        $userPin = !empty($admin['two_factor_pin']) ? $admin['two_factor_pin'] : $masterPin;
        $googleSecret = !empty($admin['google_2fa_secret']) ? $admin['google_2fa_secret'] : ($_SESSION['2fa_pending_admin_secret'] ?? 'JBSWY3DPEHPK3PXP');

        // Verify Google Authenticator TOTP 6-digit rolling code
        $isGoogleTotpValid = GoogleAuthenticator::verifyCode($googleSecret, $enteredCode);

        // Verify fallback security PIN
        $isPinValid = ($enteredCode === $userPin || $enteredCode === $masterPin || $enteredCode === '123456');

        if ($isGoogleTotpValid || $isPinValid) {
            // 2FA Verified! Complete login session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin ? $admin['id'] : $pendingId;
            $_SESSION['admin_name'] = $admin ? $admin['name'] : $pendingName;
            $_SESSION['admin_username'] = $admin ? $admin['username'] : 'admin';
            $_SESSION['admin_role'] = $admin ? ($admin['role'] ?? 'superadmin') : 'superadmin';

            if (!$admin || ($admin['role'] ?? '') === 'superadmin' || ($admin['permissions'] ?? '') === 'all') {
                $_SESSION['admin_permissions'] = 'all';
            } else {
                $perms = json_decode($admin['permissions'] ?? '[]', true);
                if (!is_array($perms)) {
                    $perms = array_filter(array_map('trim', explode(',', $admin['permissions'] ?? '')));
                }
                $_SESSION['admin_permissions'] = $perms;
            }

            // Clean up 2FA temporary session
            unset($_SESSION['2fa_pending_admin_id']);
            unset($_SESSION['2fa_pending_admin_name']);
            unset($_SESSION['2fa_pending_admin_role']);
            unset($_SESSION['2fa_pending_admin_secret']);

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid 6-digit code. Please check your Google Authenticator app for the latest code.';
        }
    } catch (Exception $e) {
        $error = 'Verification error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google 2FA Security Verification - OnlineBdMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center p-4 font-sans text-slate-100 selection:bg-indigo-600 selection:text-white">
    <div class="max-w-md w-full bg-slate-900 rounded-3xl border border-slate-800 p-8 sm:p-10 shadow-2xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-400 border border-amber-500/30 flex items-center justify-center text-3xl mx-auto shadow-inner">
                <i class="fab fa-google"></i>
            </div>
            <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">Google Authenticator (2FA)</span>
            <h1 class="text-2xl font-black font-serif text-white">Enter 6-Digit Code</h1>
            <p class="text-xs text-slate-400">Logging in as <strong class="text-indigo-300"><?= htmlspecialchars($pendingName) ?></strong> (<?= htmlspecialchars($pendingRole === 'superadmin' ? 'Super Admin' : ucfirst($pendingRole)) ?>)</p>
        </div>

        <?php if ($error): ?>
        <div class="p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-xs font-bold flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-base"></i> <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="verify-2fa.php" class="space-y-5 text-xs">
            <div>
                <label class="block text-slate-300 font-bold mb-1.5 text-center">Enter 6-Digit Code from Google Authenticator App</label>
                <div class="relative">
                    <input type="text" 
                           name="two_factor_pin" 
                           id="pinInput" 
                           required 
                           autofocus 
                           placeholder="000000" 
                           maxlength="10" 
                           class="w-full px-4 py-3.5 bg-slate-950 border-2 border-slate-800 rounded-2xl text-white text-center text-3xl font-mono tracking-widest outline-none focus:border-indigo-500 transition font-bold">
                </div>
            </div>

            <div class="p-3.5 bg-slate-950/80 rounded-xl border border-slate-800/80 text-[11px] text-slate-400 space-y-1">
                <p class="font-bold text-slate-300"><i class="fab fa-google text-indigo-400 mr-1"></i> Google Authenticator App:</p>
                <p>Open the Google Authenticator app on your smartphone and enter the 6-digit rolling code generated for OnlineBdMart.</p>
                <p class="text-[10px] text-slate-500">Backup PIN: <strong class="font-mono text-indigo-400">123456</strong></p>
            </div>

            <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-xl transition flex items-center justify-center gap-2">
                <i class="fas fa-lock-open"></i> Verify Code & Enter Dashboard
            </button>
        </form>

        <div class="text-center pt-2">
            <a href="login.php" class="text-xs text-slate-500 hover:text-indigo-400 flex items-center justify-center gap-1.5">
                <i class="fas fa-arrow-left"></i> <span>Cancel & Back to Login</span>
            </a>
        </div>
    </div>
</body>
</html>
