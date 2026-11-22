<?php
/* Sign out is a small form (POST with the form token), styled like a link. */
$signoutClass = $signoutClass ?? 'cc-text-link';
?>
<form class="cc-signout-form" action="<?php echo BASE_URL; ?>logout.php" method="POST">
    <?php echo cc_csrf_field(); ?>
    <button type="submit" class="<?php echo e($signoutClass); ?>">Sign out</button>
</form>
