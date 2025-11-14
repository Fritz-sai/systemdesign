<?php
require_once __DIR__ . '/php/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message) {
        $_SESSION['contact_success'] = 'Thanks for reaching out! Our team will respond shortly.';
    } else {
        $_SESSION['contact_errors'] = ['Please fill in all required fields.'];
    }

    header('Location: contact.php');
    exit;
}

renderHead('Contact Us | PhoneFix+');
renderNav();
renderFlashMessages([
    'contact_success' => 'success',
    'contact_errors' => 'error'
]);
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');

/* --- Modern Black & Green Navbar --- */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 64px;
  background: #000; /* Solid black */
  backdrop-filter: blur(7px);
  z-index: 100;
  box-shadow: 0 2px 14px rgba(16,32,16,0.14);
  display: flex;
  align-items: center;
  padding: 0 1.5rem;
  transition: background 0.2s;
  flex-wrap: nowrap;
}
.navbar .nav-logo {
  font-size: 1.4rem;
  font-weight: 800;
  color: #00ff6a;
  letter-spacing: -0.02em;
  text-decoration: none;
  margin-right: 1.2rem;
  white-space: nowrap;
  text-shadow: 0 0 8px #00ff6a99;
}
.navbar .nav-links {
  display: flex;
  gap: 0.6rem;
  align-items: center;
  flex: 1 1 auto;
  min-width: 0;
}
.navbar .nav-links a {
  color: #eafbe6;
  font-size: .97rem;
  font-weight: 600;
  text-decoration: none;
  padding: 6px 0.75rem;
  border-radius: 0.3rem;
  transition: background 0.17s, color 0.17s;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 9em;
}
.navbar .nav-links a:hover,
.navbar .nav-links a.active {
  background: #00ff6a22;
  color: #09b95b;
}
.navbar .nav-actions {
  display: flex;
  gap: 0.55rem;
  align-items: center;
  flex-shrink: 0;
}
.navbar .nav-actions a.btn-primary {
  background: #00ff6a;
  color: #111;
  border: none;
  font-weight: 600;
  padding: 6px 0.75rem;
  border-radius: 0.3rem;
  transition: background 0.17s, color 0.15s;
  white-space: nowrap;
  max-width: 9em;
  min-width: 0;
  font-size: .97rem;
  text-align: center;
  display: inline-block;
  margin: 0;
  vertical-align: middle;
}
.navbar .nav-actions a.btn-primary:hover {
  background: #09b95b;
  color: #fff;
}

/* --- Modern Contact Styles --- */
body {
    font-family: 'Inter', Arial, sans-serif;
    margin: 0;
    background: #101213;
    color: #eafbe6;
    padding-top: 64px;
}
.page-header {
    padding: 2rem 0 1rem;
    background: linear-gradient(90deg,#101313 0%, #00ff6a 100%);
    color: #00ff6a;
    text-align: center;
    border-radius: 0 0 1.2rem 1.2rem;
    margin-bottom: 2rem;
    box-shadow: 0px 8px 30px 0px #09b95b22;
}
.page-header h1 {
    font-size: 2.2rem;
    font-weight: 800;
    color: #00ff6a;
    margin-bottom: 0.32rem;
    text-shadow: 0 0 8px #00ff6a40;
}
.page-header p {
    font-size: 1.07rem;
    color: #b2ffcb;
}
.container {
    max-width: 1050px;
    margin: 0 auto;
    padding: 0 1rem;
}
.contact-grid {
    display: grid;
    gap: 2.6rem;
    grid-template-columns: 1fr 2fr;
    margin-bottom: 2.9rem;
}
.card {
    background: #181a1b;
    border-radius: 1.2rem;
    box-shadow: 0 4px 24px #00ff6a19;
    padding: 2rem 1.5rem 1.7rem 1.5rem;
    border: 1.5px solid #00ff6a24;
    transition: box-shadow 0.16s, border-color 0.14s;
}
.card:hover {
    box-shadow: 0 10px 28px #09b95b27;
    border-color: #00ff6a55;
}
.contact-info h3 {
    font-size: 1.18rem;
    font-weight: 700;
    color: #00ff6a;
    margin: 1.1rem 0 0.55rem 0;
}
.contact-info p {
    color: #c2f8e5;
    font-size: 1.03rem;
    margin-bottom: 0.5rem;
}
.contact-form label {
    display: block;
    margin-bottom: 1.1rem;
}
.contact-form span {
    display: block;
    margin-bottom: 0.45rem;
    font-weight: 600;
    color: #00ff6a;
    font-size: 1.01rem;
    letter-spacing: 0.01em;
}
.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 0.78rem 1rem;
    border: 1.5px solid #00ff6a46;
    border-radius: 0.69rem;
    font-size: 1rem;
    background: #101313;
    color: #eafbe6;
    box-shadow: 0px 2px 6px #00ff6a13 inset;
    transition: border-color 0.14s, box-shadow 0.13s;
}
.contact-form input:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: #00ff6a;
    box-shadow: 0 0 0 1.2px #00ff6a40;
}
.contact-form textarea {
    resize: vertical;
}
.contact-form .btn-primary {
    background: #00ff6a;
    color: #0e1212;
    font-weight: 700;
    border-radius: 0.8rem;
    font-size: 1.05rem;
    padding: 0.85rem 1.2rem;
    transition: background 0.18s, color 0.13s, box-shadow 0.19s;
    box-shadow: 0 3px 14px #00ff6a21;
    cursor: pointer;
    letter-spacing: 0.02em;
    margin-top: 0.7rem;
    border: none;
}
.contact-form .btn-primary:hover {
    background: #09b95b;
    color: #fff;
    box-shadow: 0 6px 20px #09b95b50;
}
@media (max-width: 900px) {
    .container { padding: 0 8px; }
    .page-header { border-radius: 0 0 0.8rem 0.8rem;}
    .card { border-radius: 0.7rem; }
    .contact-grid { grid-template-columns: 1fr;}
}
@media (max-width: 600px) {
    .container { padding: 0 3px; }
    .page-header { border-radius: 0 0 0.5rem 0.5rem;}
    .card { padding: 1.15rem 0.6rem 1rem 0.6rem; }
}
</style>
<main class="page">
    <section class="page-header">
        <div class="container">
            <h1>Get in Touch</h1>
            <p>Questions about a device or accessory? We&apos;re here to help.</p>
        </div>
    </section>

    <section class="container contact-grid">
        <div class="card contact-info">
            <h3>Visit Us</h3>
            <p>123 Mobile Ave, Suite 200<br>San Francisco, CA 94107</p>
            <h3>Support</h3>
            <p>Email: support@phonefixplus.com<br>Phone: +1 (555) 987-6543</p>
            <h3>Hours</h3>
            <p>Mon - Sat: 9:00 AM - 7:00 PM<br>Sun: 10:00 AM - 5:00 PM</p>
        </div>
        <form class="card contact-form" method="POST" action="contact.php">
            <label>
                <span>Name</span>
                <input type="text" name="name" required>
            </label>
            <label>
                <span>Email</span>
                <input type="email" name="email" required>
            </label>
            <label>
                <span>Message</span>
                <textarea name="message" rows="5" required></textarea>
            </label>
            <button type="submit" class="btn-primary">Send Message</button>
        </form>
    </section>
</main>

<?php
renderFooter();
?>