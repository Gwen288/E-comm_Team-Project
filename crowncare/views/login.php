<?php

require_once __DIR__ . '/../core/core.php';

?>

<?php require_once __DIR__ . '/layout/header.php'; ?>

<main class="auth-page">

    <h1>Sign in</h1>
    <p class="auth-lead">
        Access your CrownCare account to checkout and track orders.
    </p>


    <?php if (isset($_SESSION['error'])): ?>

        <div class="error-message">
            <?php echo htmlspecialchars($_SESSION['error']); ?>
        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <form
        action="../actions/login_action.php"
        method="POST"
    >

        <div>
            <label for="email">Email</label>

            <input
                type="email"
                id="email"
                name="email"
                required
            >
        </div>


        <div>
            <label for="pass">Password</label>

            <input
                type="password"
                id="pass"
                name="pass"
                required
            >
        </div>


        <button type="submit">
            Login
        </button>

    </form>


    <p>
        Don't have an account?
        <a href="register.php">Register</a>
    </p>

</main>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

