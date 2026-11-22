<?php
/*
 * Friendly error page for anyone who hits a bad URL or an unexpected error.
 * Shown by cc_render_error_page() in core.php for "not found" (404) and
 * unexpected errors (500). It doesn't use the database or the normal layout,
 * so it still works when the database is the problem.
 */

// cc_render_error_page() passes the HTTP status; anything else falls back to 500.
$errorCode = (int) ($errorCode ?? 500);
// core.php may have failed before BASE_URL was defined.
$errorBase = defined('BASE_URL') ? BASE_URL : '/';

$errorTitles = [
    403 => 'This page is not available',
    404 => 'Page not found',
    500 => 'Something went wrong',
];
$errorTexts = [
    403 => 'You do not have access to this page.',
    404 => 'The page you are looking for does not exist or has been moved.',
    500 => 'We could not complete your request. Please try again in a moment.',
];

$errorTitle = $errorTitles[$errorCode] ?? $errorTitles[500];
$errorText = $errorTexts[$errorCode] ?? $errorTexts[500];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?php echo htmlspecialchars($errorTitle); ?> | CrownCare</title>
    <link rel="icon" href="<?php echo $errorBase; ?>images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?php echo $errorBase; ?>css/app.css?v=20261122">
</head>
<body class="cc-error-body">
    <main class="cc-error-page">
        <a class="cc-error-logo" href="<?php echo $errorBase; ?>index.php">CrownCare</a>
        <p class="cc-error-code"><?php echo $errorCode; ?></p>
        <h1><?php echo htmlspecialchars($errorTitle); ?></h1>
        <p><?php echo htmlspecialchars($errorText); ?></p>
        <div class="cc-error-actions">
            <a class="cc-button cc-button--primary" href="<?php echo $errorBase; ?>index.php">Back to home</a>
            <a class="cc-button cc-button--outline" href="<?php echo $errorBase; ?>views/shop.php">Go to the shop</a>
        </div>
    </main>
</body>
</html>
