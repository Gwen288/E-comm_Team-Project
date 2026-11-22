<?php /* Site footer for the storefront and account pages (everything that includes layout/header.php). Links change with who is signed in. */ ?>
<footer class="site-footer">
    <div class="footer-shell">
        <div class="footer-brand">
            <a href="<?php echo BASE_URL; ?>index.php" class="footer-logo">CrownCare</a>
            <p>Natural hair care for African and textured hair, delivered across Accra.</p>
        </div>

        <div class="footer-col">
            <h3>Shop</h3>
            <a href="<?php echo BASE_URL; ?>views/shop.php">Shop all</a>
            <a href="<?php echo BASE_URL; ?>views/hair_quiz.php">Hair quiz</a>
            <a href="<?php echo BASE_URL; ?>views/book_consultation.php">Book a consultation</a>
            <?php /* Staff don't shop, so no cart link for them. */ ?>
            <?php if (!is_admin()): ?>
                <a href="<?php echo BASE_URL; ?>views/cart.php">Cart</a>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <h3>Help</h3>
            <a href="<?php echo BASE_URL; ?>views/pages/contact.php">Contact us</a>
            <a href="<?php echo BASE_URL; ?>views/pages/faq.php">FAQ</a>
            <a href="<?php echo BASE_URL; ?>views/pages/shipping.php">Delivery and returns</a>
            <a href="<?php echo BASE_URL; ?>views/pages/about.php">About CrownCare</a>
        </div>

        <div class="footer-col">
            <h3>Account</h3>
            <?php /* Admin gets a dashboard, customer their account links, guest sign in/register. */ ?>
            <?php if (is_logged_in() && is_admin()): ?>
                <a href="<?php echo BASE_URL; ?>views/admin/index.php">Dashboard</a>
                <?php $signoutClass = 'footer-signout'; require __DIR__ . '/signout_form.php'; ?>
            <?php elseif (is_logged_in()): ?>
                <a href="<?php echo BASE_URL; ?>views/account/my_account.php">My account</a>
                <a href="<?php echo BASE_URL; ?>views/account/orders.php">My orders</a>
                <a href="<?php echo BASE_URL; ?>views/account/bookings.php">My consultations</a>
                <?php $signoutClass = 'footer-signout'; require __DIR__ . '/signout_form.php'; ?>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>views/login.php">Sign in</a>
                <a href="<?php echo BASE_URL; ?>views/register.php">Create an account</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> CrownCare</p>
        <nav class="footer-legal" aria-label="Legal">
            <a href="<?php echo BASE_URL; ?>views/pages/privacy.php">Privacy policy</a>
            <a href="<?php echo BASE_URL; ?>views/pages/terms.php">Terms of use</a>
        </nav>
    </div>
</footer>

<?php /* Shared scripts (flash messages, form handling, etc.) live here. */ ?>
<?php require __DIR__ . '/foot_scripts.php'; ?>
</body>
</html>
