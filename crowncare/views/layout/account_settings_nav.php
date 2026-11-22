<?php
/*
 * Heading and menu shared by the Account settings pages.
 * The page sets $settingsPage to 'details', 'password' or 'close'.
 */
$settingsLinks = [
    'details' => ['Your details', 'edit_account.php', ''],
    'password' => ['Password', 'change_pass.php', ''],
    'close' => ['Close account', 'delete_account.php', 'is-danger'],
];
?>
<a class="cc-back" href="<?php echo BASE_URL; ?>views/account/my_account.php">Back to my account</a>

<div class="cc-account-head">
    <div>
        <h1>Account settings</h1>
    </div>
</div>

<div class="cc-settings-layout">
    <nav class="cc-settings-nav" aria-label="Account settings">
        <?php foreach ($settingsLinks as $key => [$label, $file, $class]): ?>
            <a
                class="<?php echo trim($class . ($settingsPage === $key ? ' is-active' : '')); ?>"
                href="<?php echo BASE_URL; ?>views/account/<?php echo $file; ?>"
                <?php echo $settingsPage === $key ? 'aria-current="page"' : ''; ?>
            ><?php echo e($label); ?></a>
        <?php endforeach; ?>
    </nav>
    <div>
