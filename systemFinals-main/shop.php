<?php
require_once __DIR__ . '/php/helpers.php';
require_once __DIR__ . '/php/admin_functions.php';
require_once __DIR__ . '/php/db_connect.php';

$products = getProducts($conn);

// Get reviews for each product
foreach ($products as &$product) {
    $stmt = $conn->prepare('
        SELECT r.*, u.name as user_name 
        FROM reviews r 
        JOIN users u ON u.id = r.user_id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT 5
    ');
    $stmt->bind_param('i', $product['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $product['reviews'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    
    // Calculate average rating
    $avgStmt = $conn->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ?');
    $avgStmt->bind_param('i', $product['id']);
    $avgStmt->execute();
    $avgResult = $avgStmt->get_result();
    $avgData = $avgResult->fetch_assoc();
    $product['avg_rating'] = $avgData ? round((float)$avgData['avg_rating'], 1) : 0;
    $product['review_count'] = $avgData ? (int)$avgData['review_count'] : 0;
    $avgStmt->close();
}
unset($product);

renderHead('Shop Accessories | PhoneFix+');
renderNav();
renderFlashMessages([
    'cart_success' => 'success',
    'cart_errors' => 'error'
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


/* ---- MODERN SHOP STYLES ---- */
body {
    background: #101313;
    color: #eafbe6;
    font-family: 'Inter', Arial, sans-serif;
    margin: 0;
    padding-top: 64px;
}
.page-header {
    background: linear-gradient(90deg, #101313 0%, #00ff6a 100%);
    color: #00ff6a;
    padding: 2rem 0 1rem 0;
    text-align: center;
    border-radius: 0 0 1.2rem 1.2rem;
    margin-bottom: 2rem;
    box-shadow: 0 7px 30px 0 #00ff6a22;
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
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 1rem;
}
.products-grid {
    display: grid;
    gap: 2.1rem;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    padding-bottom: 2.5rem;
}
.product-card {
    background: #181A1B;
    border-radius: 1.1rem;
    box-shadow: 0 8px 32px #09b95b15;
    padding: 1.6rem 1.2rem 1.5rem 1.2rem;
    transition: box-shadow 0.16s, transform 0.14s, border 0.19s;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    border: 1.5px solid #00ff6a20;
}
.product-card:hover {
    transform: translateY(-2px) scale(1.025);
    box-shadow: 0 12px 40px #00ff6a22;
    border-color: #00ff6a55;
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
    margin: 0 0 0.40rem;
    font-size: 1.18rem;
    font-weight: 700;
    color: #00ff6a;
}
.product-info p {
    color: #c2f8e5;
    margin-bottom: 0.5rem;
    font-size: 1.02rem;
    line-height: 1.45;
}
.price {
    font-size: 1.13rem;
    color: #00ff6a;
    font-weight: 700;
    margin-bottom: 0.6rem;
    margin-top: 0.2rem;
    border-radius: 0.4rem;
    background: #00ff6a1a;
    padding: 0.07em 0.64em;
    display: inline-block;
    letter-spacing: 0.01em;
}
.cart-form {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    margin-top: 0.7rem;
    flex-wrap: wrap;
}
.cart-form label {
    color: #b2ffcb;
    font-weight: 500;
    font-size: 0.97rem;
}
.cart-form input[type="number"] {
    width: 70px;
    border-radius: 0.65rem;
    border: 1.3px solid #00ff6a60;
    background: #101313;
    color: #eafbe6;
    font-size: 1.04rem;
    padding: 0.43rem;
    font-family: inherit;
    transition: border-color 0.15s, box-shadow 0.18s;
    outline: none;
}
.cart-form input[type="number"]:focus {
    border-color: #00ff6a;
    box-shadow: 0 0 0 1.5px #00ff6a45;
}

.btn-primary {
    background: #00ff6a;
    color: #0e1212;
    border: none;
    font-weight: 700;
    border-radius: 0.8rem;
    font-size: 1.07rem;
    padding: 0.68rem 1rem;
    transition: background 0.18s, color 0.14s, box-shadow 0.18s;
    box-shadow: 0 3px 12px #00ff6a21;
    cursor: pointer;
    letter-spacing: 0.02em;
}
.btn-primary:hover {
    background: #09b95b;
    color: #fff;
    box-shadow: 0 6px 20px #09b95b40;
}

@media (max-width: 900px) {
    .container { padding: 0 8px;}
    .page-header { border-radius: 0 0 0.7rem 0.7rem;}
    .product-card, .products-grid { border-radius: 0.8rem;}
    .products-grid { gap: 1.2rem;}
}
@media (max-width: 600px) {
    .products-grid { grid-template-columns: 1fr; }
    .container { padding: 0 3px; }
}
</style>
<main class="page">
    <section class="page-header">
        <div class="container">
            <h1>Accessory Shop</h1>
            <p>Curated essentials to protect and power your devices.</p>
        </div>
    </section>

    <section class="container products-grid">
        <?php if ($products): ?>
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        
                        <!-- Rating Display -->
                        <?php if ($product['review_count'] > 0): ?>
                            <div style="margin: 0.5rem 0; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <div style="display: flex; gap: 0.125rem;">
                                    <?php 
                                    $avgRating = $product['avg_rating'];
                                    for ($i = 1; $i <= 5; $i++): 
                                        $starColor = $i <= $avgRating ? '#00ff6a' : '#333d35';
                                    ?>
                                        <span style="color: <?php echo $starColor; ?>; font-size: 1.05rem; text-shadow:0 0 6px #00ff6a44;">⭐</span>
                                    <?php endfor; ?>
                                </div>
                                <a href="reviews.php?product_id=<?php echo (int)$product['id']; ?>" style="font-size: 0.93rem; color: #00ff6a; font-weight:600; text-decoration: underline dotted; text-shadow:0px 0px 3px #00ff6a80;">
                                    <?php echo number_format($avgRating, 1); ?> (<?php echo $product['review_count']; ?> review<?php echo $product['review_count'] !== 1 ? 's' : ''; ?>)
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <span class="price">$<?php echo number_format((float) $product['price'], 2); ?></span>
                        <form action="php/handle_cart.php" method="POST" class="cart-form">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                            <label for="qty-<?php echo (int) $product['id']; ?>">Qty</label>
                            <input id="qty-<?php echo (int) $product['id']; ?>" type="number" name="quantity" value="1" min="1">
                            <button type="submit" class="btn-primary">Add to Cart</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products available yet.</p>
        <?php endif; ?>
    </section>
</main>

<?php
renderFooter();
?>