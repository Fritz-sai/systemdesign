<?php
/**
 * Product Reviews Page
 * 
 * Displays all reviews for products with filtering and sorting options
 */

require_once __DIR__ . '/php/db_connect.php';
require_once __DIR__ . '/php/helpers.php';

// Get filter parameters
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$sortBy = $_GET['sort'] ?? 'recent'; // 'recent', 'rating_high', 'rating_low'
$minRating = isset($_GET['min_rating']) ? (int)$_GET['min_rating'] : 0;

// Build query
$where = "1=1";
$params = [];
$types = '';

if ($productId > 0) {
    $where .= " AND r.product_id = ?";
    $params[] = $productId;
    $types .= 'i';
}

if ($minRating > 0) {
    $where .= " AND r.rating >= ?";
    $params[] = $minRating;
    $types .= 'i';
}

$orderBy = "r.created_at DESC";
switch ($sortBy) {
    case 'rating_high':
        $orderBy = "r.rating DESC, r.created_at DESC";
        break;
    case 'rating_low':
        $orderBy = "r.rating ASC, r.created_at DESC";
        break;
    case 'recent':
    default:
        $orderBy = "r.created_at DESC";
        break;
}

// Get reviews
$sql = "SELECT r.*, u.name as user_name, p.name as product_name, p.image as product_image
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        JOIN products p ON p.id = r.product_id
        WHERE $where
        ORDER BY $orderBy";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
} else {
    $result = $conn->query($sql);
    $reviews = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    if ($result) $result->close();
}

// Get all products for filter dropdown
$productsResult = $conn->query("SELECT id, name FROM products ORDER BY name");
$allProducts = $productsResult ? $productsResult->fetch_all(MYSQLI_ASSOC) : [];
if ($productsResult) $productsResult->close();

// Get statistics
$statsSql = "SELECT 
    COUNT(*) as total_reviews,
    AVG(rating) as avg_rating,
    COUNT(DISTINCT product_id) as products_reviewed,
    COUNT(DISTINCT user_id) as reviewers
    FROM reviews";
if ($productId > 0) {
    $statsSql .= " WHERE product_id = ?";
    $statsStmt = $conn->prepare($statsSql);
    $statsStmt->bind_param('i', $productId);
    $statsStmt->execute();
    $statsResult = $statsStmt->get_result();
    $stats = $statsResult->fetch_assoc();
    $statsStmt->close();
} else {
    $statsResult = $conn->query($statsSql);
    $stats = $statsResult ? $statsResult->fetch_assoc() : ['total_reviews' => 0, 'avg_rating' => 0, 'products_reviewed' => 0, 'reviewers' => 0];
    if ($statsResult) $statsResult->close();
}

// Get rating distribution
$ratingDistSql = "SELECT rating, COUNT(*) as count FROM reviews";
if ($productId > 0) {
    $ratingDistSql .= " WHERE product_id = ?";
}
$ratingDistSql .= " GROUP BY rating ORDER BY rating DESC";

if ($productId > 0) {
    $distStmt = $conn->prepare($ratingDistSql);
    $distStmt->bind_param('i', $productId);
    $distStmt->execute();
    $distResult = $distStmt->get_result();
    $ratingDistribution = $distResult ? $distResult->fetch_all(MYSQLI_ASSOC) : [];
    $distStmt->close();
} else {
    $distResult = $conn->query($ratingDistSql);
    $ratingDistribution = $distResult ? $distResult->fetch_all(MYSQLI_ASSOC) : [];
    if ($distResult) $distResult->close();
}

renderHead('Product Reviews | Reboot');
renderNav();
renderFlashMessages([
    'review_success' => 'success',
    'review_errors' => 'error'
]);
?>

<link rel="stylesheet" href="css/orders.css">
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

body {
    background: #101313;
    color: #eafbe6;
    font-family: 'Inter', 'Segoe UI', 'Arial', sans-serif;
    margin: 0;
}
.reviews-page {
    padding: 2rem 0;
}

.reviews-header {
    background: linear-gradient(135deg, #101313 0%, #00ff6a 120%);
    color: #00ff6a;
    padding: 3rem 0 2rem 0;
    margin-bottom: 2rem;
    border-radius: 0 0 2.3rem 2.3rem;
    box-shadow: 0 3px 32px #00ff6a13;
}

.reviews-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2.6rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    color: #00ff6a;
    text-shadow: 0 0 10px #00ff6a60;
}
.reviews-header p {
    margin: 0;
    opacity: 0.96;
    font-size: 1.18rem;
    color: #b2ffcb;
}

.container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 1.2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.3rem;
}

.stat-card {
    background: #181b1a;
    padding: 1.5rem;
    border-radius: 1.2rem;
    box-shadow: 0 2px 12px #00ff6a14;
    text-align: center;
    border: 1.5px solid #00ff6a30;
    transition: box-shadow 0.17s, border-color 0.16s;
}
.stat-card:hover {
    box-shadow: 0 3px 24px #00ff6a36;
    border-color: #00ff6a66;
}
.stat-card h3 {
    margin: 0 0 0.5rem 0;
    font-size: 2.2rem;
    color: #00ff6a;
    font-weight: 700;
    text-shadow: 0 0 6px #00ff6a33;
}
.stat-card p {
    margin: 0;
    color: #b2ffcb;
    font-size: 0.89rem;
}

.filters-section {
    background: #181b1a;
    padding: 1.6rem 1.3rem;
    border-radius: 1.2rem;
    box-shadow: 0 2px 15px #00ff6a19;
    margin-bottom: 2.3rem;
    border: 1.5px solid #00ff6a30;
}
.filters-row {
    display: flex;
    gap: 1.2rem;
    flex-wrap: wrap;
    align-items: flex-end;
}
.filter-group {
    flex: 1;
    min-width: 170px;
}
.filter-group label {
    display: block;
    margin-bottom: 0.45rem;
    font-weight: 600;
    color: #00ff6a;
    font-size: 1.01rem;
    letter-spacing: 0.01em;
}

.filter-group select,
.filter-group input {
    width: 100%;
    padding: 0.7rem;
    border: 1.5px solid #00ff6a60;
    border-radius: 0.7rem;
    font-size: 1rem;
    background: #101313;
    color: #eafbe6;
    transition: border-color 0.15s, box-shadow 0.18s;
    box-shadow: 0px 2px 6px #00ff6a13 inset;
}
.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: #00ff6a;
    box-shadow: 0 0 0 1.5px #00ff6a45;
}

.btn-primary {
    background: #00ff6a;
    color: #111;
    font-weight: 700;
    border-radius: 0.7rem;
    padding: 0.67rem 1.1rem;
    border: none;
    transition: background 0.17s, color 0.15s, box-shadow 0.18s;
    box-shadow: 0 3px 10px #00ff6a2a;
    font-size: 1.09rem;
    cursor: pointer;
    letter-spacing: 0.02em;
}
.btn-primary:hover {
    background: #09b95b;
    color: #fff;
}

.rating-distribution {
    background: #181b1a;
    padding: 1.6rem 1.3rem;
    border-radius: 1.2rem;
    box-shadow: 0 2px 13px #00ff6a15;
    margin-bottom: 2.3rem;
    border: 1.5px solid #00ff6a30;
}
.rating-distribution h3 {
    margin: 0 0 1rem 0;
    font-size: 1.27rem;
    color: #00ff6a;
    font-weight: 700;
}
.rating-bar {
    display: flex;
    align-items: center;
    gap: 1.1rem;
    margin-bottom: 1.0rem;
}
.rating-label {
    min-width: 80px;
    color: #eafbe6;
    font-weight: 600;
}
.rating-bar-fill {
    flex: 1;
    height: 22px;
    background: #161816;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    border: 1.2px solid #00ff6a40;
    box-shadow: 0 1px 7px #09b95b19 inset;
}
.rating-bar-progress {
    height: 100%;
    background: linear-gradient(90deg, #00ff6a, #09b95b 80%);
    transition: width 0.3s ease;
}
.rating-count {
    min-width: 60px;
    text-align: right;
    color: #b2ffcb;
    font-size: 1.01rem;
    font-weight: 700;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 1.7rem;
}

.review-card {
    background: #181b1a;
    padding: 1.5rem 1.3rem;
    border-radius: 1.2rem;
    box-shadow: 0 2px 16px #00ff6a18;
    border: 1.5px solid #00ff6a26;
    transition: box-shadow 0.18s, border-color 0.16s;
}
.review-card:hover {
    box-shadow: 0 6px 28px #00ff6a25;
    border-color: #00ff6a55;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}
.review-user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.product-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.52rem 1rem;
    background: #101313;
    border-radius: 20px;
    font-size: 0.98rem;
    color: #00ff6a;
    border: 1px solid #00ff6a45;
    box-shadow: 0 2px 10px #00ff6a15;
}
.product-badge img {
    width: 25px;
    height: 25px;
    border-radius: 6px;
    object-fit: cover;
    background: #141914;
    box-shadow: 0 0 6px #09b95b17;
    border: 1px solid #00ff6a27;
}

.review-rating {
    display: flex;
    gap: 0.28rem;
}
.review-rating .star {
    color: #00ff6a;
    font-size: 1.34rem;
    text-shadow: 0 0 6px #00ff6a44;
}
.review-date {
    color: #b2ffcb;
    font-size: 0.99rem;
    margin-top: 0.18rem;
}

.review-comment {
    color: #eafbe6;
    line-height: 1.68;
    margin-top: 0.77rem;
    font-size: 1.11rem;
    letter-spacing: 0.01em;
}

.empty-reviews {
    text-align: center;
    padding: 3.3rem 1rem;
    background: #181b1a;
    border-radius: 1.2rem;
    box-shadow: 0 2px 14px #00ff6a14;
    border: 1.5px solid #00ff6a28;
}
.empty-reviews-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #00ff6a;
    text-shadow: 0 0 13px #00ff6a29;
}
.empty-reviews h2 {
    margin: 0 0 0.5rem 0;
    color: #00ff6a;
    font-weight: 700;
}
.empty-reviews p {
    color: #b2ffcb;
    margin: 0;
}
@media (max-width: 950px) {
    .container { padding: 0 8px;}
    .reviews-header { border-radius: 0 0 1.2rem 1.2rem;}
    .stat-card, .filters-section, .rating-distribution, .review-card, .empty-reviews { border-radius: 0.8rem;}
    .stats-grid { gap: 1rem;}
}
@media (max-width: 700px) {
    .stats-grid, .reviews-list { grid-template-columns: 1fr;}
    .reviews-header h1 { font-size: 2rem;}
    .reviews-header { padding: 2.4rem 0;}
    .reviews-page { padding: 1.2rem 0;}
}
</style>

<main class="page reviews-page">
    <section class="reviews-header">
        <div class="container">
            <h1>Product Reviews</h1>
            <p>See what our customers are saying about our products</p>
        </div>
    </section>

    <section class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo (int)$stats['total_reviews']; ?></h3>
                <p>Total Reviews</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format((float)$stats['avg_rating'], 1); ?></h3>
                <p>Average Rating</p>
            </div>
            <div class="stat-card">
                <h3><?php echo (int)$stats['products_reviewed']; ?></h3>
                <p>Products Reviewed</p>
            </div>
            <div class="stat-card">
                <h3><?php echo (int)$stats['reviewers']; ?></h3>
                <p>Reviewers</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" class="filters-row">
                <div class="filter-group">
                    <label for="product_id">Filter by Product:</label>
                    <select name="product_id" id="product_id">
                        <option value="0">All Products</option>
                        <?php foreach ($allProducts as $product): ?>
                            <option value="<?php echo (int)$product['id']; ?>" <?php echo $productId === (int)$product['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="min_rating">Minimum Rating:</label>
                    <select name="min_rating" id="min_rating">
                        <option value="0">All Ratings</option>
                        <option value="5" <?php echo $minRating === 5 ? 'selected' : ''; ?>>5 Stars</option>
                        <option value="4" <?php echo $minRating === 4 ? 'selected' : ''; ?>>4+ Stars</option>
                        <option value="3" <?php echo $minRating === 3 ? 'selected' : ''; ?>>3+ Stars</option>
                        <option value="2" <?php echo $minRating === 2 ? 'selected' : ''; ?>>2+ Stars</option>
                        <option value="1" <?php echo $minRating === 1 ? 'selected' : ''; ?>>1+ Stars</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="sort">Sort By:</label>
                    <select name="sort" id="sort">
                        <option value="recent" <?php echo $sortBy === 'recent' ? 'selected' : ''; ?>>Most Recent</option>
                        <option value="rating_high" <?php echo $sortBy === 'rating_high' ? 'selected' : ''; ?>>Highest Rating</option>
                        <option value="rating_low" <?php echo $sortBy === 'rating_low' ? 'selected' : ''; ?>>Lowest Rating</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn-primary" style="width: 100%;">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Rating Distribution -->
        <?php if (!empty($ratingDistribution)): ?>
            <div class="rating-distribution">
                <h3>Rating Distribution</h3>
                <?php 
                $totalForDist = array_sum(array_column($ratingDistribution, 'count'));
                foreach ($ratingDistribution as $dist): 
                    $percentage = $totalForDist > 0 ? ($dist['count'] / $totalForDist) * 100 : 0;
                ?>
                    <div class="rating-bar">
                        <div class="rating-label"><?php echo (int)$dist['rating']; ?> Stars</div>
                        <div class="rating-bar-fill">
                            <div class="rating-bar-progress" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                        <div class="rating-count"><?php echo (int)$dist['count']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Reviews List -->
        <?php if (empty($reviews)): ?>
            <div class="empty-reviews">
                <div class="empty-reviews-icon">⭐</div>
                <h2>No Reviews Found</h2>
                <p>There are no reviews matching your criteria. Try adjusting your filters.</p>
            </div>
        <?php else: ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-user-info">
                                <div>
                                    <strong style="font-size: 1.1rem; display: block; margin-bottom: 0.25rem; color: #eafbe6;">
                                        <?php echo htmlspecialchars($review['user_name']); ?>
                                    </strong>
                                    <div class="product-badge">
                                        <img src="/systemFinals/<?php echo htmlspecialchars($review['product_image']); ?>" alt="<?php echo htmlspecialchars($review['product_name']); ?>">
                                        <span><?php echo htmlspecialchars($review['product_name']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= (int)$review['rating']; $i++): ?>
                                        <span class="star">⭐</span>
                                    <?php endfor; ?>
                                </div>
                                <div class="review-date">
                                    <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($review['comment'])): ?>
                            <div class="review-comment">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php
renderFooter();
?>