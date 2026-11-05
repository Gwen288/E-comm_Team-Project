<?php

require_once __DIR__ . '/../../core/core.php';

require_login();

$accountName = (string) ($_SESSION['customer_name'] ?? '');
$firstName = trim(explode(' ', $accountName)[0] ?? '');
if ($firstName === '') {
    $firstName = 'there';
}

?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>

<main class="account-page">

    <div class="account-heading">
        <div>
            <p class="eyebrow">My account</p>
            <h1>Welcome back, <?php echo htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>

        <a class="account-signout" href="<?php echo BASE_URL; ?>logout.php">
            Sign out
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <section class="account-profile surface-panel">
        <h2>Personal information</h2>

        <div class="account-info">
            <p>
                <span>Name</span>
                <?php echo htmlspecialchars($_SESSION['customer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <p>
                <span>Email</span>
                <?php echo htmlspecialchars($_SESSION['customer_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>

        <div class="account-actions">
            <a href="<?php echo BASE_URL; ?>views/account/edit_account.php">
                Edit account
            </a>

            <a href="<?php echo BASE_URL; ?>views/account/change_pass.php">
                Change password
            </a>

            <a href="<?php echo BASE_URL; ?>views/shop.php">
                Continue shopping
            </a>
        </div>
    </section>

    <form
        class="account-delete"
        action="<?php echo BASE_URL; ?>actions/delete_account_action.php"
        method="POST"
        onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');"
    >
        <button type="submit">
            Delete account
        </button>
    </form>

</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
