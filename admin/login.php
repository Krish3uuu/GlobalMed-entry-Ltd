<?php
/**
 * GlobalMed Entry - Secure Admin Login
 */
require_once __DIR__ . '/auth.php';

// Redirect if already logged in
if (isset($_SESSION['admin_user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$ip = get_client_ip();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Security session expired. Please refresh and try again.';
    } elseif (is_ip_locked($ip)) {
        $error = 'Too many failed login attempts. For security, access is temporarily locked for 15 minutes.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both your username and password.';
        } else {
            $db = get_db_connection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Success - regenerate session
                session_regenerate_id(true);
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                clear_failed_logins($ip);

                $redirect = $_GET['redirect'] ?? 'index.php';
                // Prevent open redirect
                if (strpos($redirect, 'http') === 0 || strpos($redirect, '//') === 0) {
                    $redirect = 'index.php';
                }
                header("Location: " . $redirect);
                exit;
            } else {
                record_failed_login($ip);
                $error = 'Invalid credentials. Please verify your username and password.';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admissions Directorate Portal Login | GlobalMed Entry Ltd</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700;800&family=Rubik:wght@500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#02326f',
                        'primary-dark': '#01214a',
                        accent: '#12bda9',
                        'accent-dark': '#0d9686'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#0F172A] min-h-screen flex items-center justify-center p-4 font-['Karla',sans-serif] text-slate-800 relative overflow-hidden">
    <!-- Ambient glow shapes -->
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-[#12bda9]/10 blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Brand Header Card -->
        <div class="text-center mb-8">
            <a href="../index.html" class="inline-block mb-4">
                <img src="../images/logo.webp" alt="GlobalMed Entry Ltd" class="h-16 w-auto mx-auto object-contain drop-shadow-md" />
            </a>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-400/20 text-blue-200 text-xs font-semibold uppercase tracking-wider">
                <span class="material-symbols-outlined text-[15px] text-[#12bda9]">lock</span>
                Protected Editorial Directorate
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight mt-3">Content &amp; Article Manager</h1>
            <p class="text-slate-400 text-sm mt-1">Sign in to publish, edit articles, and manage homepage items.</p>
        </div>

        <!-- Login Box -->
        <div class="bg-white rounded-[5px] border border-slate-200/90 shadow-2xl p-7 sm:p-8">
            <?php if (!empty($error)): ?>
                <div class="mb-5 p-3.5 rounded-[5px] bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-[18px] text-rose-600 shrink-0">error</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" />

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5" for="username">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <span class="material-symbols-outlined text-[18px]">account_circle</span>
                        </span>
                        <input type="text" id="username" name="username" required autofocus autocomplete="username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            class="w-full pl-9 pr-3.5 py-2.5 rounded-[5px] border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary text-sm placeholder-slate-400 transition-colors"
                            placeholder="admin" />
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700" for="password">Password</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <span class="material-symbols-outlined text-[18px]">key</span>
                        </span>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                            class="w-full pl-9 pr-10 py-2.5 rounded-[5px] border border-slate-200 focus:border-primary focus:ring-1 focus:ring-primary text-sm placeholder-slate-400 transition-colors"
                            placeholder="••••••••••••" />
                        <button type="button" onclick="togglePassVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-[18px]" id="eyeIcon">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-[5px] bg-primary hover:bg-primary-dark text-white font-bold text-xs uppercase tracking-wider transition-all shadow-md flex items-center justify-center gap-2">
                        <span>Sign In to Dashboard</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 bg-slate-50/80 -mx-7 -mb-7 sm:-mx-8 sm:-mb-8 p-4 rounded-b-[5px] text-center">
                <p class="text-xs text-slate-500">
                    <span class="font-bold text-slate-700">Client Demo Access:</span> Username: <code class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-bold text-primary">admin</code> · Password: <code class="px-1.5 py-0.5 bg-white border border-slate-200 rounded font-bold text-primary">GlobalMed2026!</code>
                </p>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="../index.html" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[15px]">arrow_back</span>
                <span>Return to GlobalMed Entry Homepage</span>
            </a>
        </div>
    </div>

    <script>
        function togglePassVisibility() {
            const passInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.innerText = 'visibility_off';
            } else {
                passInput.type = 'password';
                eyeIcon.innerText = 'visibility';
            }
        }
    </script>
</body>
</html>
