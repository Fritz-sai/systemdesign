<?php
require_once __DIR__ . '/php/helpers.php';

$featuredProducts = [];
$result = $conn->query('SELECT * FROM products ORDER BY created_at DESC LIMIT 3');
if ($result) {
    $featuredProducts = $result->fetch_all(MYSQLI_ASSOC);
    $result->close();
}

renderHead('PhoneFix+ | Premium Repairs & Accessories');
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');

.navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 64px;
  background: rgba(10, 12, 13, 0.96);
  backdrop-filter: blur(7px);
  z-index: 100;
  box-shadow: 0 2px 14px rgba(16,32,16,0.14);
  display: flex;
  align-items: center;
  padding: 0 1rem;
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
}
.navbar .nav-links {
  display: flex;
  gap: 0.6rem;
  align-items: center;
  flex: 1 1 auto;
  min-width: 0;
}
.navbar .nav-links a {
  color: #e6e6e6;
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
  padding: 0.32rem 0.83rem;
  font-size: 0.94rem;
  font-weight: 600;
  border-radius: 0.34rem;
  box-shadow: none;
  background: #00ff6a;
  color: #111;
  border: none;
  letter-spacing: 0.01em;
  white-space: nowrap;
  max-width: 8em;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
}
.navbar .nav-actions a.btn-primary:hover {
  background: #09b95b;
  color: #fff;
}
.navbar .nav-actions a,
.navbar .nav-actions a.btn-primary {
  font-family: inherit;
  font-weight: 600;
  text-align: center;
}

body { padding-top: 64px; }
/* --- PAGE STYLES --- */
body {
    font-family: 'Inter', Arial, sans-serif;
    margin: 0;
    background: #101213;
    color: #f2f6f3;
}
main { width: 100%; overflow: hidden; }
.hero {
    background: linear-gradient(108deg, #131715 0%, #223b25 100%);
    color: #eafbe6;
    min-height: 400px;
    box-shadow: 0 6px 32px 0 rgba(16,32,16,0.18);
    display: flex;
    align-items: center;
    border-radius: 0 0 2.5rem 2.5rem;
}
.hero-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 3rem;
    padding: 2.8rem 0;
}
.hero-content > div { flex: 1; }
.hero-content h1 {
    font-size: 2.6rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    color: #ccffe5;
}
.hero-content p {
    font-size: 1.15rem;
    max-width: 37rem;
    margin-bottom: 1.2rem;
    color: #abffcd;
}
.hero-actions {
    display: flex;
    gap: 1.2rem;
    justify-content: center;
}
.btn-primary, .btn-secondary, .btn-outline {
    padding: 0.7rem 1.8rem;
    border-radius: 0.6rem;
    font-size: 1.08rem;
    font-weight: 700;
    outline: none;
    cursor: pointer;
    transition: background 0.18s, color 0.15s, box-shadow 0.18s;
    box-shadow: 0 3px 12px rgba(24,48,24,0.07);
    border: none;
    text-decoration: none;
    display: inline-block;
}
.btn-primary {
    background: #00ff6a;
    color: #0f140f;
}
.btn-primary:hover {
    background: #09b95b;
    color: #fff;
    box-shadow: 0 5px 19px rgba(10,255,106,0.13);
}
.btn-outline {
    background: transparent;
    border: 2px solid #00ff6a;
    color: #00ff6a;
}
.btn-outline:hover {
    background: #00ff6a22;
    color: #fff;
    border-color: #09b95b;
}
.btn-secondary {
    background: linear-gradient(93deg,#09b95b 5%,#00ff6a 94%);
    color: #fff;
    border: none;
}
.btn-secondary:hover {
    background: linear-gradient(90deg,#00ff6a 0,#09b95b 100%);
    color: #181818;
}
.hero-visual img {
    width: 340px;
    max-width: 95vw;
    filter: drop-shadow(0 8px 32px #09b95b40);
    border-radius: 1.1rem;
    background: rgba(8,32,8,0.10);
}
.features {
    margin: 3.2rem 0 2.7rem 0;
}
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 2.3rem;
}
.features-grid article {
    background: #181A1B;
    border-radius: 1.1rem;
    box-shadow: 0 4px 24px #00ff6a26;
    padding: 1.5rem 1.2rem;
    text-align: center;
    transition: transform 0.15s, box-shadow 0.13s;
}
.features-grid article:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 8px 32px #09b95b33;
}
.features-grid h3 {
    font-size: 1.23rem;
    font-weight: 700;
    color: #00ff6a;
    margin-bottom: 0.2rem;
}
.features-grid p {
    font-size: 0.99rem;
    color: #d8fbe8;
    margin-top: 0.35rem;
}
.section-light {
    background: #191d1e;
    border-radius: 1.3rem;
    box-shadow: 0 4px 24px #00ff6a12;
    margin: 3rem auto 2.9rem;
    padding: 2.3rem 0;
    max-width: 1100px;
}
.section-header {
    text-align: center;
    margin-bottom: 1.6rem;
}
.section-header h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #00ff6a;
}
.section-header p {
    color: #c3ffdd;
    margin-top: 0.35rem;
    font-size: 1.05rem;
}
.products-grid {
    display: grid;
    gap: 2.1rem;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    padding: 0.2rem 0.4rem;
}
.product-card {
    background: #181A1B;
    border-radius: 1.1rem;
    box-shadow: 0 4px 20px rgba(0,255,106,0.15);
    padding: 1.4rem;
    transition: box-shadow 0.19s, transform 0.14s;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
.product-card:hover {
    box-shadow: 0 10px 32px #09b95b30;
    transform: translateY(-2px) scale(1.03);
}
.product-card img {
    max-width: 100%;
    border-radius: 0.75rem;
    margin-bottom: 1rem;
    background: #161c16;
    box-shadow: 0 8px 24px #09b95b60;
    aspect-ratio: 1/1;
    object-fit: cover;
}
.product-info h3 {
    margin: 0 0 0.48rem;
    font-size: 1.14rem;
    font-weight: 600;
    color: #00ff6a;
}
.product-info p {
    color: #d8fbe8;
    margin-bottom: 0.48rem;
    font-size: 0.99rem;
    line-height: 1.45;
}
.price {
    font-size: 1.13rem;
    color: #00ff6a;
    font-weight: 700;
    margin-bottom: 0.6rem;
    margin-top: 0.4rem;
    border-radius: 0.4rem;
    background: #00ff6a1a;
    padding: 0.09em 0.7em;
    display: inline-block;
}
.product-card form {
    margin-top: 0.7rem;
    width: 100%;
}
.product-card .btn-primary {
    width: 100%;
    font-size: 1.01rem;
}

/* FIX CTA WHITE BOX */
.cta {
    background: linear-gradient(93deg,#00ff6a 5%,#09b95b 94%);
    color: #00ff6a;
    border-radius: 1.3rem;
    padding: 2.1rem 0;
    text-align: center;
    box-shadow: 0 4px 24px #00ff6a20;
    margin: 2.7rem auto 0.2rem;
    max-width: 950px;
    /* Modern dark look */
    background: linear-gradient(120deg,#00ff6a 14%,#191d1e 70%,#191d1e 100%);
}
.cta-content {
    background: transparent;
}

.cta-content h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.4rem;
    color: #00ff6a;
    text-shadow: 0 0 6px #00ff6a60;
}
.cta-content p {
    font-size: 1.09rem;
    margin-bottom: 1.1rem;
    color: #b2ffcb;
}
.cta-content .btn-secondary {
    background: #191d1e;
    color: #00ff6a;
    border: 2px solid #00ff6a;
}
.cta-content .btn-secondary:hover {
    background: #00ff6a;
    color: #191d1e;
}
@media (max-width: 950px) {
    .hero-content { flex-direction: column; gap: 2rem; padding: 2rem 0; }
    .hero-visual img { width: 220px; }
    .section-light, .cta { border-radius: 1.1rem; }
}
@media (max-width: 700px) {
    .features-grid, .products-grid { grid-template-columns: 1fr; }
    .features { margin: 2.1rem 0 1.6rem; }
    .hero-content h1 { font-size: 2rem;}
}
</style>
<?php
renderNav();
renderFlashMessages([
    'auth_success' => 'success',
    'cart_success' => 'success',
    'cart_errors' => 'error'
]);
?>

<main>
    <section class="hero">
        <div class="container hero-content">
            <div>
                <h1>Fast, Reliable Phone Repairs &amp; Stylish Accessories</h1>
                <p>PhoneFix+ brings expert technicians and curated accessories together. Book your repair in minutes and shop essentials that keep your device protected.</p>
                <div class="hero-actions">
                    <a class="btn-primary" href="booking.php" style="width: 100%; text-align: center;">Book a Repair</a>
                </div>
            </div>
            <div class="hero-visual">
                <img src="images/placeholder.png" alt="Phone repair" />
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container features-grid">
            <article>
                <h3>Certified Technicians</h3>
                <p>Our experts diagnose and repair with precision using high-quality parts and modern tools.</p>
            </article>
            <article>
                <h3>Same-Day Repairs</h3>
                <p>Book a time that works for you. Most jobs completed in under two hours.</p>
            </article>
            <article>
                <h3>Premium Accessories</h3>
                <p>Protective cases, chargers, audio gear, and more—all vetted for durability and style.</p>
            </article>
        </div>
    </section>

    <section class="section-light">
        <div class="container">
            <div class="section-header">
                <h2>Featured Accessories</h2>
                <p>Hand-picked gear to keep your phone looking and working like new.</p>
            </div>
            <div class="products-grid">
                <?php if (count($featuredProducts) > 0): ?>
                    <?php foreach ($featuredProducts as $product): ?>
                        <article class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p><?php echo htmlspecialchars($product['description']); ?></p>
                                <span class="price">$<?php echo number_format((float) $product['price'], 2); ?></span>
                                <form action="php/handle_cart.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                    <button type="submit" class="btn-primary">Add to Cart</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No featured products available yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container cta-content">
            <h2>Need help now?</h2>
            <p>Our technicians are ready. Book a repair and get back to what matters.</p>
            <a class="btn-secondary" href="booking.php">Schedule Service</a>
        </div>
    </section>
</main>

<?php
renderFooter();
?>