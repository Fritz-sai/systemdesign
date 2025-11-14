<?php
require_once __DIR__ . '/php/helpers.php';

$services = [
    ['title' => 'Screen Replacement', 'description' => 'Cracked or shattered screen? We use premium glass replacements with original feel.', 'price' => 129.99],
    ['title' => 'Battery Replacement', 'description' => 'Restore battery life and performance with genuine replacements.', 'price' => 89.99],
    ['title' => 'Water Damage Treatment', 'description' => 'Complete device diagnostics and ultrasonic cleaning to revive water-damaged phones.', 'price' => 149.99],
    ['title' => 'Charging Port Repair', 'description' => 'Fix loose or unresponsive charging ports and restore fast charging.', 'price' => 79.99],
    ['title' => 'Speaker & Mic Repair', 'description' => 'Crystal clear audio for calls, music, and voice assistants.', 'price' => 69.99],
    ['title' => 'Software Optimization', 'description' => 'Speed tune-ups, data backup, and malware removal.', 'price' => 49.99]
];

renderHead('Services | PhoneFix+');
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

/* --- Modern Page Styles --- */
body {
    font-family: 'Inter', Arial, sans-serif;
    margin: 0;
    background: #101213;
    color: #f2f6f3;
    padding-top: 64px;
}
.page-header {
    padding: 2rem 0 1rem;
    background: linear-gradient(90deg, #101313 0%, #00ff6a 100%);
    color: #00ff6a;
    text-align: center;
    border-radius: 0 0 1.3rem 1.3rem;
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
    font-size: 1.09rem;
    color: #aefad4;
}
.services-grid {
    display: grid;
    gap: 2.2rem;
    grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
    padding-bottom: 2.5rem;
}
.service-card {
    background: #181a1b;
    border-radius: 1.2rem;
    box-shadow: 0 6px 34px #09b95b17;
    padding: 2.2rem 1.6rem 2rem 1.6rem;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    transition: box-shadow 0.18s, transform 0.14s, border-color 0.18s;
    border: 1.5px solid #00ff6a25;
    position: relative;
}
.service-card:hover {
    box-shadow: 0 10px 36px #00ff6a44;
    transform: translateY(-2px) scale(1.02);
    border-color: #00ff6a70;
}
.service-card h3 {
    font-size: 1.28rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: #00ff6a;
    margin: 0 0 0.55rem 0;
}
.service-card p {
    color: #c5f8e5;
    font-size: 1.03rem;
    margin-bottom: 1.15rem;
}
.price {
    font-size: 1.13rem;
    color: #00ff6a;
    font-weight: 700;
    margin-bottom: 0.6rem;
    border-radius: 0.4rem;
    background: #00ff6a1a;
    padding: 0.09em 0.75em;
    display: inline-block;
    letter-spacing: 0.015em;
}
.btn-outline {
    background: transparent;
    border: 2px solid #00ff6a;
    color: #00ff6a;
    padding: 0.54rem 1.45rem;
    border-radius: 0.7rem;
    font-size: 1.04rem;
    font-weight: 700;
    text-decoration: none;
    letter-spacing: 0.012em;
    margin-top: 1.25rem;
    transition: background 0.16s, color 0.16s, border-color 0.16s;
    cursor: pointer;
    outline: none;
    box-shadow: 0 2px 8px #00984612;
    white-space: nowrap;
    min-width: 0;
    max-width: 8.4em;
    overflow: hidden;
    text-overflow: ellipsis;
}
.btn-outline:hover {
    background: #00ff6a25;
    color: #181a1b;
    border-color: #09b95b;
}
@media (max-width: 900px) {
    .navbar { flex-wrap: wrap; height: auto; padding: 0.5rem 0.5rem; }
    .navbar .nav-logo { font-size: 1.18rem; margin-right: 0.7rem;}
    .navbar .nav-links { gap: 0.35rem; }
    .navbar .nav-links a { font-size: .93rem; padding: 6px 0.5rem; max-width: 7.7em; }
    .navbar .nav-actions { gap: 0.32rem; }
    .navbar .nav-actions a.btn-primary { font-size: .93rem; padding: 0.28rem 0.6rem; max-width: 6.2em;}
    .services-grid { gap: 1.2rem;}
    .service-card { border-radius: 0.7rem; }
    .page-header { border-radius: 0 0 0.8rem 0.8rem;}
}
@media (max-width: 600px) {
    .navbar { flex-direction: column; height: auto; padding: 0.3rem 0.3rem;}
    .navbar .nav-logo { margin-bottom: 5px; }
    .navbar .nav-links, .navbar .nav-actions { justify-content: flex-start; }
    .navbar .nav-links a, .navbar .nav-actions a { font-size: .92rem; padding: 4px 0.42rem; }
    .services-grid { grid-template-columns: 1fr;}
    .page-header { border-radius: 0 0 0.5rem 0.5rem;}
}
</style>
<?php renderNav(); ?>
<?php
renderFlashMessages([
    'auth_success' => 'success'
]);
?>

<main class="page">
    <section class="page-header">
        <div class="container">
            <h1>Repair Services</h1>
            <p>Transparent pricing, premium parts, and fast turnaround for every device.</p>
        </div>
    </section>

    <section class="container services-grid">
        <?php foreach ($services as $service): ?>
            <article class="service-card">
                <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
                <span class="price">Starting at $<?php echo number_format($service['price'], 2); ?></span>
                <a class="btn-outline" href="booking.php">Book Now</a>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<?php
renderFooter();
?>