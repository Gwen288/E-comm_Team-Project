<?php

require_once __DIR__ . '/../../core/core.php';

require_login();

?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>

<main>

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

    <h1>My Account</h1>

    <div class="account-card">

        <h2>Account Information</h2>

        <div class="account-info">

            <p>
                <strong>Name:</strong>
                <?php echo htmlspecialchars($_SESSION['customer_name']); ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?php echo htmlspecialchars($_SESSION['customer_email']); ?>
            </p>


        </div>

        <div class="account-actions">

            <a href="<?php echo BASE_URL; ?>views/account/edit_account.php">
                Edit Account
            </a>

            <a href="<?php echo BASE_URL; ?>views/account/change_pass.php">
                Change Password
            </a>

            <a href="<?php echo BASE_URL; ?>index.php">
                Continue Shopping
            </a>

        </div>

    </div>

    <form
    action="<?php echo BASE_URL; ?>actions/delete_account_action.php"
    method="POST"
    onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');"
>
    <button type="submit">
        Delete Account
    </button>
</form>

</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

