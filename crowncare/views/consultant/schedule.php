<?php
/*
 * The Weekly hours page was merged into the Schedule page (schedule_dates.php),
 * and its weekly times were removed. This file only redirects old links. It can be deleted.
 */
require_once __DIR__ . '/../../core/core.php';
redirect(BASE_URL . 'views/consultant/schedule_dates.php');
