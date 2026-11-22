<?php
/*
 * Messages from the previous action ($_SESSION success / error / info),
 * shown as styled notices by js/app.js. Each message is shown once.
 */
$flashSuccess = $_SESSION['success'] ?? '';
$flashError = $_SESSION['error'] ?? '';
$flashInfo = $_SESSION['info'] ?? '';
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['info']);
?>
<?php if ($flashSuccess !== '' || $flashError !== '' || $flashInfo !== ''): ?>
    <div
        id="cc-flash"
        hidden
        data-success="<?php echo e($flashSuccess); ?>"
        data-error="<?php echo e($flashError); ?>"
        data-info="<?php echo e($flashInfo); ?>"
    ></div>
    <noscript>
        <div class="cc-flash-fallback">
            <?php echo e(trim($flashError . ' ' . $flashSuccess . ' ' . $flashInfo)); ?>
        </div>
    </noscript>
<?php endif; ?>
