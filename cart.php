<?php
require_once __DIR__ . '/php/helpers.php';

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += (float) $item['price'] * (int) $item['quantity'];
}

// Get delivery option from session
$deliveryOption = $_SESSION['delivery_option'] ?? 'pickup';
$shippingFee = 0;
if ($deliveryOption === 'delivery') {
    $shippingFee = $subtotal < 1000 ? 100 : 0;
}
$total = $subtotal + $shippingFee;

renderHead('Your Cart | Reboot');
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

/* --- Cart Modern Styles --- */
body {
    font-family: 'Inter', Arial, sans-serif;
    margin: 0;
    background: #101213;
    color: #eafbe6;
    padding-top: 64px;
}
.cart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 2.1rem 0 1.3rem 0;
}
.cart-title {
    font-size: 2.1rem;
    font-weight: 800;
    color: #00ff6a;
    letter-spacing: -0.015em;
    margin: 0;
    text-shadow: 0 0 6px #00ff6a55;
}
.continue-shopping {
    color: #b2ffcb;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    border-radius: 0.5rem;
    padding: 6px 1.1rem;
    transition: background 0.16s, color 0.14s;
    background: #181a1b;
    border: 1px solid #09b95b;
}
.continue-shopping:hover {
    background: #00ff6a20;
    color: #00ff6a;
}
.cart-layout {
    display: flex;
    gap: 2.4rem;
    align-items: flex-start;
}
.cart-main {
    flex: 2 2 0;
    min-width: 0;
}
.cart-summary-sidebar {
    flex: 1 1 300px;
    max-width: 340px;
}
.card, .summary-card {
    background: #171a1b;
    border-radius: 1.1rem;
    box-shadow: 0 5px 24px #00ff6a13;
    padding: 2rem 1.4rem 1.5rem 1.4rem;
    border: 1.5px solid #00ff6a21;
    margin-bottom: 1.3rem;
    transition: box-shadow 0.17s, border-color 0.15s;
}
.card:hover, .summary-card:hover {
    box-shadow: 0 7px 30px #00ff6a27;
    border-color: #00ff6a66;
}
.cart-table-wrapper {
    background: #181a1b;
    border-radius: 0.8rem;
    box-shadow: 0 2px 14px #00ff6a14;
    overflow-x: auto;
}
.cart-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.cart-table th, .cart-table td {
    padding: 0.85rem 1rem;
    text-align: left;
    font-size: 1.03rem;
    border-bottom: 1.5px solid #181a1b;
    background: transparent;
}
.cart-table th {
    font-size: 0.97rem;
    font-weight: 700;
    color: #00ff6a;
    background: #161916;
    text-shadow: 0 0 6px #00ff6a40;
    border-radius: 0.5rem 0.5rem 0 0;
}
.cart-product {
    display: flex;
    align-items: center;
    gap: 1.09rem;
}
.cart-product-image {
    width: 54px;
    height: 54px;
    object-fit: cover;
    border-radius: 0.7rem;
    box-shadow: 0 4px 14px #09b95b23;
    border: 1.4px solid #00ff6a30;
    background: #161c16;
}
.cart-product-details {
    display: flex;
    flex-direction: column;
}
.cart-product-name {
    color: #00ff6a;
    font-weight: 700;
    font-size: 1.08rem;
}
.cart-product-id {
    color: #b2ffcb;
    font-size: 0.97rem;
    margin-top: 1px;
}
.cart-price .price-amount,
.cart-total-cell .item-total {
    color: #00ff6a;
    font-weight: 700;
    font-size: 1.08rem;
    background: #00ff6a1a;
    border-radius: 0.4rem;
    padding: 0.09em 0.59em;
    display: inline-block;
}
.cart-quantity .quantity-input {
    width: 60px;
    border-radius: 0.7rem;
    border: 1.3px solid #00ff6a60;
    background: #101313;
    color: #eafbe6;
    font-size: 1.05rem;
    padding: 0.42rem;
    font-family: inherit;
    text-align: center;
}
.cart-quantity .quantity-input:focus {
    outline: none;
    border-color: #00ff6a;
    box-shadow: 0 0 0 1.2px #00ff6a42;
}
.remove-form {
    display: inline-block;
    margin-left: 1.1rem;
}
.remove-btn {
    background: #101213;
    color: #b2ffcb;
    border: none;
    font-size: 1.5rem;
    border-radius: 0.5rem;
    padding: 0 9px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.remove-btn:hover {
    background: #00ff6a31;
    color: #09b95b;
}
.summary-card {
    box-shadow: 0 7px 30px #00ff6a14;
    padding: 2rem 1.3rem 1.5rem 1.3rem;
}
.summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 1.07rem;
    margin-bottom: 0.95rem;
    color: #b2ffcb;
}
.summary-row.summary-total {
    font-weight: 800;
    font-size: 1.13rem;
    color: #00ff6a;
    padding-top: 0.5rem;
    border-top: 1.5px solid #00ff6a33;
}
.checkout-btn {
    width: 100%;
    background: linear-gradient(93deg,#09b95b 5%,#00ff6a 94%);
    color: #181818;
    font-weight: 700;
    border: none;
    font-size: 1.1rem;
    border-radius: 0.8rem;
    margin-top: 1.1rem;
    padding: 0.88rem 0;
    cursor: pointer;
    transition: background 0.18s, color 0.14s, box-shadow 0.13s;
    box-shadow: 0 3px 14px #00ff6a15;
    letter-spacing: 0.018em;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8em;
}
.checkout-btn:hover {
    background: linear-gradient(90deg,#00ff6a 0%,#09b95b 100%);
    color: #fff;
    box-shadow: 0 7px 20px #09b95b31;
}
.delivery-options-section {
    background: #161c1a;
    border-radius: 0.8rem;
    box-shadow: 0 2px 11px #09b95b14;
    padding: 1.35rem;
    margin-top: 2.1rem;
}
.delivery-title {
    color: #00ff6a;
    font-size: 1.08rem;
    font-weight: 700;
    margin-bottom: 1.1rem;
}
.delivery-form {
    display: flex;
    flex-direction: column;
    gap: 1.05rem;
}
.delivery-option {
    margin-bottom: 0.6rem;
}
.delivery-label {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    cursor: pointer;
    font-size: 1rem;
    color: #eafbe6;
}
.delivery-label input[type="radio"] {
    accent-color: #00ff6a;
    margin-right: 0.55rem;
    width: 1.2em;
    height: 1.2em;
}
.delivery-text {
    display: flex;
    gap: 0.5rem;
}
.delivery-cost {
    color: #00ff6a;
    font-weight: 700;
}
.delivery-address {
    margin-top: 0.5rem;
    color: #b2ffcb;
    font-size: 0.97rem;
    background: #181a1b;
    padding: 0.45em 1em;
    border-radius: 0.6em;
    display: inline-block;
}
.checkout-form input[type=hidden] {
    display: none;
}
.empty-cart {
    text-align: center;
    background: #181a1b;
    border-radius: 1.1rem;
    box-shadow: 0 4px 18px #09b95b1b;
    padding: 2.5rem 1.2rem;
    color: #b2ffcb;
}
.empty-cart a {
    color: #00ff6a;
    text-decoration: underline wavy;
    font-weight: 700;
}

@media (max-width: 950px) {
    .cart-layout { flex-direction: column; gap: 1.1rem;}
    .cart-summary-sidebar { max-width: 100%; }
    .cart-header { flex-direction: column; gap: 0.8rem; align-items: flex-start;}
    .delivery-options-section { margin-top: 1.2rem; }
}
@media (max-width: 700px) {
    .container { padding: 0 7px;}
    .cart-table-wrapper { border-radius: 0.7rem;}
    .card, .summary-card { border-radius: 0.7rem; padding: 1rem 0.7rem;}
    .cart-header { margin: 1.2rem 0 1rem 0;}
    .cart-title { font-size: 1.5rem;}
}
</style>

<main class="page cart-page">
    <section class="container">
        <div class="cart-header">
            <h1 class="cart-title">My Cart</h1>
            <a href="shop.php" class="continue-shopping">← Continue shopping</a>
        </div>

        <?php if ($cart): ?>
        <div class="cart-layout">
            <div class="cart-main">
                <div class="cart-table-wrapper">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>PRODUCT</th>
                                <th>PRICE</th>
                                <th>QTY</th>
                                <th>TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $item): 
                                $itemTotal = (float) $item['price'] * (int) $item['quantity'];
                            ?>
                            <tr class="cart-table-row">
                                <td class="cart-product">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-product-image">
                                    <div class="cart-product-details">
                                        <div class="cart-product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="cart-product-id">ID: <?php echo (int) $item['product_id']; ?></div>
                                    </div>
                                </td>
                                <td class="cart-price">
                                    <div class="price-amount" data-price="<?php echo (float) $item['price']; ?>">₱<?php echo number_format((float) $item['price'], 2); ?></div>
                                </td>
                                <td class="cart-quantity">
                                    <form action="php/handle_cart.php" method="POST" class="quantity-form">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $item['product_id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo (int) $item['quantity']; ?>" min="1" class="quantity-input" data-product-id="<?php echo (int) $item['product_id']; ?>">
                                    </form>
                                </td>
                                <td class="cart-total-cell">
                                    <span class="item-total" data-product-id="<?php echo (int) $item['product_id']; ?>">₱<?php echo number_format($itemTotal, 2); ?></span>
                                    <form action="php/handle_cart.php" method="POST" class="remove-form">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $item['product_id']; ?>">
                                        <button type="submit" class="remove-btn" title="Remove item">×</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="delivery-options-section">
                    <h3 class="delivery-title">Choose shipping mode:</h3>
                    <form id="delivery-form" action="php/handle_cart.php" method="POST" class="delivery-form">
                        <input type="hidden" name="action" value="update_delivery">
                        <div class="delivery-option">
                            <label class="delivery-label">
                                <input type="radio" name="delivery_option" value="pickup" <?php echo $deliveryOption === 'pickup' ? 'checked' : ''; ?>>
                                <span class="delivery-text">
                                    <strong>Store pickup (In 20 min)</strong> • <span class="delivery-cost">FREE</span>
                                </span>
                            </label>
                        </div>
                        <div class="delivery-option">
                            <label class="delivery-label">
                                <input type="radio" name="delivery_option" value="delivery" <?php echo $deliveryOption === 'delivery' ? 'checked' : ''; ?>>
                                <span class="delivery-text">
                                    <strong>Delivery at home (Under 2 - 4 day)</strong> • <span class="delivery-cost" id="shipping-fee-text"><?php echo $shippingFee > 0 ? '₱' . number_format($shippingFee, 2) : 'FREE'; ?></span>
                                </span>
                            </label>
                            <?php if ($deliveryOption === 'delivery'): ?>
                            <div class="delivery-address">
                                Delivery address will be collected during checkout
                            </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="cart-summary-sidebar">
                <div class="summary-card">
                    <div class="summary-row">
                        <span>SUBTOTAL TTC</span>
                        <strong id="cart-subtotal">₱<?php echo number_format($subtotal, 2); ?></strong>
                    </div>
                    <div class="summary-row" id="shipping-row" style="<?php echo $deliveryOption === 'pickup' ? 'display: none;' : ''; ?>">
                        <span>SHIPPING</span>
                        <strong id="shipping-fee"><?php echo $shippingFee > 0 ? '₱' . number_format($shippingFee, 2) : 'Free'; ?></strong>
                    </div>
                    <div class="summary-row summary-total">
                        <span>TOTAL</span>
                        <strong id="cart-total">₱<?php echo number_format($total, 2); ?></strong>
                    </div>
                    <form action="php/handle_cart.php" method="POST" id="checkout-form" class="checkout-form">
                        <input type="hidden" name="action" value="checkout">
                        <input type="hidden" name="delivery_option" id="checkout-delivery-option" value="<?php echo htmlspecialchars($deliveryOption); ?>">
                        <input type="hidden" name="shipping_fee" id="checkout-shipping-fee" value="<?php echo $shippingFee; ?>">
                        <input type="hidden" name="total" id="checkout-total" value="<?php echo $total; ?>">
                        <button type="submit" class="checkout-btn">
                            Checkout
                            <span class="checkout-total">₱<?php echo number_format($total, 2); ?></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="card empty-cart">
                <p>Your cart is empty. Explore our <a href="shop.php">accessories</a> or <a href="booking.php">book a repair</a>.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const deliveryForm = document.getElementById('delivery-form');
    const deliveryRadios = document.querySelectorAll('input[name="delivery_option"]');
    const shippingRow = document.getElementById('shipping-row');
    const shippingFeeEl = document.getElementById('shipping-fee');
    const shippingFeeText = document.getElementById('shipping-fee-text');
    const subtotalEl = document.getElementById('cart-subtotal');
    const totalEl = document.getElementById('cart-total');
    const checkoutForm = document.getElementById('checkout-form');
    const checkoutDeliveryOption = document.getElementById('checkout-delivery-option');
    const checkoutShippingFee = document.getElementById('checkout-shipping-fee');
    const checkoutTotal = document.getElementById('checkout-total');

    // Get subtotal by calculating from all items
    function calculateSubtotal() {
        let subtotal = 0;
        const quantityInputs = document.querySelectorAll('.quantity-input');
        
        quantityInputs.forEach(input => {
            const productId = input.dataset.productId;
            const quantity = parseInt(input.value) || 1;
            
            // Find the price for this item
            const row = input.closest('tr');
            const priceEl = row.querySelector('.price-amount');
            const price = parseFloat(priceEl.dataset.price) || 0;
            
            subtotal += price * quantity;
        });
        
        return subtotal;
    }

    // Get subtotal from the displayed value (fallback)
    function getSubtotal() {
        const calculated = calculateSubtotal();
        if (calculated > 0) return calculated;
        
        const subtotalText = subtotalEl.textContent.replace('₱', '').replace(/,/g, '');
        return parseFloat(subtotalText) || 0;
    }

    // Update item total when quantity changes
    function updateItemTotal(inputElement) {
        const row = inputElement.closest('tr');
        const priceEl = row.querySelector('.price-amount');
        const itemTotalEl = row.querySelector('.item-total');
        const productId = inputElement.dataset.productId;
        const quantity = parseInt(inputElement.value) || 1;
        
        const price = parseFloat(priceEl.dataset.price) || 0;
        const itemTotal = price * quantity;
        
        if (itemTotalEl) {
            itemTotalEl.textContent = '₱' + itemTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        
        // Update subtotal and grand total
        updateSubtotalAndTotal();
    }

    // Update subtotal and grand total
    function updateSubtotalAndTotal() {
        const subtotal = calculateSubtotal();
        subtotalEl.textContent = '₱' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        // Update shipping and total
        updateShippingAndTotal();
    }

    function updateShippingAndTotal() {
        const subtotal = getSubtotal();
        const selectedOption = document.querySelector('input[name="delivery_option"]:checked');
        if (!selectedOption) return;
        
        const optionValue = selectedOption.value;
        let shippingFee = 0;
        const deliveryOption = selectedOption.closest('.delivery-option');
        let deliveryAddress = deliveryOption ? deliveryOption.querySelector('.delivery-address') : null;

        if (optionValue === 'delivery') {
            shippingFee = subtotal < 1000 ? 100 : 0;
            shippingRow.style.display = 'flex';
            shippingFeeEl.textContent = shippingFee > 0 ? '₱' + shippingFee.toFixed(2) : 'Free';
            shippingFeeText.textContent = shippingFee > 0 ? '₱' + shippingFee.toFixed(2) : 'FREE';
            
            // Show delivery address if it exists
            if (deliveryAddress) {
                deliveryAddress.style.display = 'block';
            } else {
                // Create delivery address if it doesn't exist
                const deliveryOptionDiv = selectedOption.closest('.delivery-option');
                if (deliveryOptionDiv && !deliveryOptionDiv.querySelector('.delivery-address')) {
                    const addressDiv = document.createElement('div');
                    addressDiv.className = 'delivery-address';
                    addressDiv.textContent = 'Delivery address will be collected during checkout';
                    deliveryOptionDiv.appendChild(addressDiv);
                }
            }
        } else {
            shippingRow.style.display = 'none';
            shippingFeeText.textContent = 'FREE';
            
            // Hide delivery address
            if (deliveryAddress) {
                deliveryAddress.style.display = 'none';
            }
        }

        const total = subtotal + shippingFee;
        totalEl.textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        // Update checkout button total
        const checkoutTotalSpan = document.querySelector('.checkout-total');
        if (checkoutTotalSpan) {
            checkoutTotalSpan.textContent = '₱' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Update hidden fields for checkout
        checkoutDeliveryOption.value = optionValue;
        checkoutShippingFee.value = shippingFee;
        checkoutTotal.value = total;
    }

    // Handle delivery option change
    let isUpdating = false;
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (isUpdating) return;
            updateShippingAndTotal();
            // Auto-submit to save selection in session (with small delay to show update)
            isUpdating = true;
            setTimeout(() => {
                deliveryForm.submit();
            }, 100);
        });
    });

    // Handle quantity changes
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            const quantity = parseInt(this.value) || 1;
            
            // Ensure minimum quantity
            if (quantity < 1) {
                this.value = 1;
            }
            
            // Update item total immediately
            updateItemTotal(this);
            
            // Debounce form submission
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.closest('form').submit();
            }, 800); // Submit after 800ms of no changes
        });
        
        // Also handle onchange (when user clicks away or uses arrow keys)
        input.addEventListener('change', function() {
            let quantity = parseInt(this.value) || 1;
            
            // Ensure minimum quantity
            if (quantity < 1) {
                this.value = 1;
                quantity = 1;
            }
            
            // Update item total
            updateItemTotal(this);
            
            // Submit form to update session
            clearTimeout(timeout);
            this.closest('form').submit();
        });
    });

    // Initial calculation on page load
    updateShippingAndTotal();
});
</script>

<?php
renderFooter();
?>