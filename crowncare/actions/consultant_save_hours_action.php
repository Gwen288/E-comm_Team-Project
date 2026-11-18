<?php
/*
 * The usual weekly times form was removed from the Schedule page.
 * This file only redirects old requests. It can be deleted.
 */
require_once __DIR__ . '/../core/core.php';
redirect(BASE_URL . 'views/consultant/schedule_dates.php');
