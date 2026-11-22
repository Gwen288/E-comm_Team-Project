<?php
require_once __DIR__ . '/../core/core.php';

// Old payment page, kept as a redirect: it renders nothing for anyone.
// Payment now starts on checkout.php; this is just for old links/bookmarks.
redirect(BASE_URL . 'views/checkout.php');
