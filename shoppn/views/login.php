<?php

require_once __DIR__ . '/../core/core.php';

?>

<?php require_once __DIR__ . '/layout/header.php'; ?>

<main>

    <h1>Login</h1>


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

