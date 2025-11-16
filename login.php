<?php
require_once __DIR__ . '/php/helpers.php';


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    session_start();
    $_SESSION['auth_success'] = 'You have been logged out successfully.';
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$loginErrors = $_SESSION['login_errors'] ?? [];
$registerSuccess = $_SESSION['auth_success'] ?? null;

renderHead('Shop Accessories | Reboot');
renderNav();
// REMOVE renderNav();
// Instead add custom nav below for full-width navbar with bg
?>
<!-- Modern NEON LOGIN STYLE + Custom NAVBAR -->
<style>
body, html {
    background: #10161d !important;
    color: #d9fff8;
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
    min-height: 100vh;
    margin: 0;
}
.top-navbar {
    width: 100vw;
    background: #10161d;
    box-shadow: 0 2px 8px #00000022;
    position: sticky;
    top: 0; left: 0;
    z-index: 300;
    border-bottom: 2px solid #121a23;
}
.top-navbar .navbar-container {
    max-width: 1160px;
    margin: 0 auto;
    padding: 0 2vw;
    height: 58px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.3rem;
}
.top-navbar .logo {
    color: #1976d2;
    font-weight: 800;
    font-size: 1.16rem;
    letter-spacing: 1.45px;
    margin-right: 18px;
    font-family:'Inter',sans-serif;
    text-shadow: 0 0 8px #134ad0a3;
}
.top-navbar .logo span { color: #1677ff; font-weight: 500; }
.top-navbar .nav-links {
    display: flex;
    gap: 22px;
    align-items: center;
    margin-right: 22px;
    font-size: 0.99rem;
}
.top-navbar .nav-links a {
    color: #e6ebff;
    text-decoration: none;
    font-weight: 400;
    padding: 4px 10px;
    border-radius: 7px;
    transition: background 0.19s, color 0.13s;
    font-family:'Inter',sans-serif;
}
.top-navbar .nav-links a:hover,
.top-navbar .nav-links a.active {
    background: #153052;
    color: #2081d6;
}
.top-navbar .btn-signup {
    border: none;
    background: #1677ff;
    color: #fff;
    font-weight: 600;
    border-radius: 8px;
    padding: 7px 22px;
    font-size: 1rem;
    margin-left: 10px;
    box-shadow: 0 1px 9px #258fff33;
    transition: background .12s, color .12s, box-shadow .13s;
}
.top-navbar .btn-signup:hover {
    background: #0c2f66;
    color: #86d6ff;
    box-shadow: 0 0 20px #1677ff55;
}
@media (max-width: 900px){
    .top-navbar .navbar-container{max-width:98vw;}
    .top-navbar .nav-links { gap: 13px; margin-right:8px;}
    .top-navbar .btn-signup {margin-left:8px;}
}
.page.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(180deg,rgb(7, 7, 7) 30%, #00ff6a 100%);
}
.container.auth-grid {
    display: grid;
    grid-template-columns: 1.25fr 1fr;
    gap: 40px;
    max-width: 900px;
    margin: 3vh auto;
    width: 95vw;
    align-items: center;
}
.card.auth-card,
.auth-side.card {
    background: rgba(16, 22, 29, 0.95);
    border-radius: 18px;
    box-shadow: 0 0 28px #21fc8933, 0 2px 18px #011b1640;
    border: 2px solid #21fc89;
    padding: 38px 28px;
    max-width: 420px;
    width: 100%;
    margin: 0;
    backdrop-filter: blur(1.5px);
}
.card.auth-card h1, .auth-side.card h2 {
    margin: 0 0 13px 0;
    font-size: 2.1rem;
    color: #21fc89;
    font-weight: 800;
    letter-spacing: .5px;
    text-shadow: 0 0 8px #21fc8977;
}
.card.auth-card p {
    color: #aaf3ce;
    margin: 0 0 30px 0;
    font-size:1.08rem;
    letter-spacing:.1px;
}
.auth-form label {
    display: block;
    margin-bottom: 18px;
    color: #50fca5;
    font-size: 1.08rem;
    font-weight: 600;
}
.auth-form label span {
    display: block;
    margin-bottom: 7px;
    font-size:0.97rem;
    color: #30e688;
}
.auth-form input[type="email"],
.auth-form input[type="password"] {
    width: 100%;
    background: #16202B;
    border: 1.6px solid #21fc89;
    color: #21fc89;
    padding: 11px;
    border-radius: 8px;
    font-size: 1.04rem;
    outline: none;
    margin-bottom: 4px;
    box-shadow: 0 0 9px #21fc8952;
    transition: border-color 0.15s, background 0.15s;
}
.auth-form input:focus {
    border-color: #1dc99b;
    background: #212a3a;
}
.auth-form .btn-primary {
    width: 100%;
    margin: 10px 0 0 0;
    background: linear-gradient(90deg,#21fc89 10%,#13c37a 100%);
    color: #011;
    border: none;
    box-shadow: 0 1px 10px #21fc8922;
    border-radius: 8px;
    padding: 14px 0;
    font-size: 1.17rem;
    font-weight: bold;
    cursor: pointer;
    transition: box-shadow 0.16s, background 0.13s, color 0.13s;
}
.auth-form .btn-primary:hover {
    background: linear-gradient(90deg,#19e088 0%,#00a769 100%);
    color:#fff;
    box-shadow: 0 0 16px #21fc89cc;
}
.auth-switch {
    text-align: center;
    font-size: 1.01rem;
    margin-top: 1.6em;
    color: #9afeed;
}
.auth-switch a {
    color: #21fc89;
    font-weight: 600;
    text-shadow:0 0 5px #21fc8999;
    text-decoration: underline dashed #30e688 1.5px;
}
.auth-side.card {
    text-align: center;
    border: 2px solid #21fc89;
    background: linear-gradient(135deg, #10161d 70%, #053e38 100%);
}
.auth-side.card h2 {
    margin-bottom: 16px;
    font-size: 2rem;
}
.auth-side.card p {
    color: #66f9c3;
    font-size: 1.16rem;
    letter-spacing:.08px;
    margin: 0;
}
@media (max-width: 900px){
    .container.auth-grid{grid-template-columns:1fr;gap:18px;}
    .card.auth-card,.auth-side.card{max-width:97vw;}
    body,html{padding:0;}
}
</style>

<!-- Custom NAVBAR to match the background and the sample image 3 -->


<main class="page auth-page">
    <section class="container auth-grid">
        <div class="card auth-card">
            <h1>Welcome Back</h1>
            <p>Log in to track your repairs, orders, and manage bookings.</p>
            <form action="php/handle_login.php" method="POST" class="auth-form">
                <label>
                    <span>Email</span>
                    <input type="email" name="email" required>
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>
                <button type="submit" class="btn-primary">Log In</button>
            </form>
            <p class="auth-switch">New here? <a href="register.php">Create an account</a>.</p>
        </div>
        <div class="auth-side card">
            <h2>Reboot</h2>
            <p>Manage your repair bookings, checkout faster, and access exclusive accessories curated for your device.</p>
        </div>
    </section>
</main>

<?php
unset($_SESSION['login_errors'], $_SESSION['auth_success']);
renderFooter();
?>