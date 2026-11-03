<footer class="site-footer">
    <div class="footer-shell">
        <div class="footer-brand">
            <a href="<?php echo BASE_URL; ?>index.php" class="footer-logo">CrownCare</a>
            <p>Natural hair care for textured hair — simple routines, thoughtful products.</p>
        </div>

        <div class="footer-col">
            <h3>Shop</h3>
            <a href="<?php echo BASE_URL; ?>index.php">All products</a>
            <a href="<?php echo BASE_URL; ?>views/cart.php">Cart</a>
        </div>

        <div class="footer-col">
            <h3>Account</h3>
            <?php if (is_logged_in()): ?>
                <a href="<?php echo BASE_URL; ?>views/account/my_account.php">My account</a>
                <a href="<?php echo BASE_URL; ?>logout.php">Sign out</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>views/login.php">Sign in</a>
                <a href="<?php echo BASE_URL; ?>views/register.php">Register</a>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <h3>Checkout</h3>
            <p>Paystack · Mobile money &amp; card</p>
            <p>Free delivery from GH₵300</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> CrownCare</p>
    </div>
</footer>

<script src="<?php echo BASE_URL; ?>js/validate.js"></script>
</body>
</html>
