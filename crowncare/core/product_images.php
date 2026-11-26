<?php
/*
 * Image helpers for CrownCare.
 *
 * Product photos live in images/products/ and are named after the product
 * slug (the product title in lowercase, with dashes), matching the CMS site:
 *   crowncare-hibiscus-conditioner.jpg        main photo
 *   crowncare-hibiscus-conditioner-250ml.jpg  photo for one size
 *   crowncare-hibiscus-conditioner-2.jpg      extra gallery photos
 *
 * Category banners live in images/categories/<category-slug>.jpg.
 */

require_once __DIR__ . '/image_store.php';

function cc_slug($text)
{
    $slug = strtolower(trim((string) $text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Returns every photo for a product as a list of
 * ['src' => url, 'label' => text], with the main photo first.
 */
function cc_product_gallery($product)
{
    $dir = __DIR__ . '/../images/products/';
    $photos = [];
    $seen = [];

    $add = function ($file, $label) use (&$photos, &$seen, $dir) {
        $file = basename($file);
        if ($file === '' || isset($seen[$file]) || !is_file($dir . $file)) {
            return;
        }
        $seen[$file] = true;
        $photos[] = [
            'src' => BASE_URL . 'images/products/' . rawurlencode($file),
            'label' => $label,
        ];
    };

    $title = $product['product_title'] ?? '';
    $add($product['product_image'] ?? '', $title);

    // A photo kept in the database (when the folder was not writable).
    $mainFile = basename(trim($product['product_image'] ?? ''));
    if ($mainFile !== '' && !isset($seen[$mainFile]) && ($url = cc_product_image_url($mainFile))) {
        $seen[$mainFile] = true;
        array_unshift($photos, ['src' => $url, 'label' => $title]);
    }

    $slug = cc_slug($title);
    if ($slug === '') {
        return $photos;
    }

    $matches = array_filter(
        glob($dir . $slug . '-*') ?: [],
        function ($path) {
            return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
        }
    );
    natsort($matches);

    $sizePhotos = [];
    $extraPhotos = [];
    foreach ($matches as $path) {
        $file = basename($path);
        $suffix = pathinfo(substr($file, strlen($slug) + 1), PATHINFO_FILENAME);

        if (ctype_digit($suffix)) {
            $extraPhotos[] = $file;
        } elseif (preg_match('/^(\d+)(ml|g|l|kg)$/', $suffix, $m)) {
            $sizePhotos[] = [$file, $title . ', ' . $m[1] . ' ' . $m[2]];
        }
    }

    foreach ($sizePhotos as $size) {
        $add($size[0], $size[1]);
    }
    foreach ($extraPhotos as $file) {
        $add($file, $title);
    }

    return $photos;
}

/**
 * Returns the banner URL for a category, or null when there is none.
 * Subcategories fall back to their parent's banner.
 */
function cc_category_banner($categoryName, $parentName = null)
{
    $dir = __DIR__ . '/../images/categories/';

    foreach ([$categoryName, $parentName] as $name) {
        if ($name === null || $name === '') {
            continue;
        }
        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
            $file = cc_slug($name) . '.' . $ext;
            if (is_file($dir . $file)) {
                return BASE_URL . 'images/categories/' . $file;
            }
        }
    }

    return null;
}

/** "250ml" becomes "250 ml", as the CMS shows sizes. */
function cc_size_label($sizeName)
{
    $label = trim((string) $sizeName);
    return preg_replace('/^(\d+(?:\.\d+)?)\s*(ml|g|kg|l)$/i', '$1 $2', $label);
}

/** "250 ml" or "250ml" becomes "250ml", the suffix used in photo file names. */
function cc_size_key($sizeName)
{
    return strtolower(preg_replace('/\s+/', '', (string) $sizeName));
}

/**
 * Photos for the product page, as on the CMS:
 *  - 'all'   every photo, shown before a size is chosen;
 *  - 'sizes' for each size key, that size's photo followed by the shared extra photos.
 * Sizes without their own photo are left out, so the page keeps the full gallery.
 */
function cc_product_media($product)
{
    $all = cc_product_gallery($product);
    $slug = cc_slug($product['product_title'] ?? '');
    $sizes = [];
    $extras = [];

    foreach ($all as $photo) {
        $file = rawurldecode(basename($photo['src']));
        $suffix = pathinfo(substr($file, strlen($slug) + 1), PATHINFO_FILENAME);

        if (strpos($file, $slug . '-') === 0 && ctype_digit($suffix)) {
            $extras[] = $photo;
        } elseif (strpos($file, $slug . '-') === 0 && preg_match('/^\d+(ml|g|l|kg)$/', $suffix)) {
            $sizes[$suffix] = $photo;
        }
    }

    $bySize = [];
    foreach ($sizes as $key => $photo) {
        $bySize[$key] = array_merge([$photo], $extras);
    }

    return ['all' => $all, 'sizes' => $bySize];
}

/** The photo for a cart line: the chosen size's photo when there is one, else the main photo. */
function cc_cart_image($product, $sizeName = '')
{
    $dir = __DIR__ . '/../images/products/';
    $slug = cc_slug($product['product_title'] ?? '');

    if ($sizeName !== '' && $slug !== '') {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $file = $slug . '-' . cc_size_key($sizeName) . '.' . $ext;
            if (is_file($dir . $file)) {
                return BASE_URL . 'images/products/' . rawurlencode($file);
            }
        }
    }

    return cc_product_image_url($product['product_image'] ?? '');
}

/** Price as the CMS shows it, e.g. GH₵65.00 */
function cc_money($amount)
{
    return 'GH₵' . number_format((float) $amount, 2);
}

/** Escapes text for HTML. */
function cc_e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}


/* ---------- Product image uploads (admin) ---------- */

/** Bytes from a php.ini size such as "2M" or "8M". */
function cc_ini_bytes($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return 0;
    }
    $number = (float) $value;
    switch (strtolower(substr($value, -1))) {
        case 'g': $number *= 1024;
        // no break
        case 'm': $number *= 1024;
        // no break
        case 'k': $number *= 1024;
    }
    return (int) $number;
}

/**
 * Largest product image this server accepts: 5 MB, or less when the server's
 * upload_max_filesize or post_max_size is lower (the school server allows 2 MB).
 */
function cc_upload_limit()
{
    $limit = 5 * 1024 * 1024;
    $upload = cc_ini_bytes(ini_get('upload_max_filesize'));
    $post = cc_ini_bytes(ini_get('post_max_size'));
    if ($upload > 0) {
        $limit = min($limit, $upload);
    }
    if ($post > 0) {
        $limit = min($limit, $post - 64 * 1024); // room for the other form fields
    }
    return max(256 * 1024, $limit);
}

/** "2 MB" style label for the limit. */
function cc_upload_limit_label()
{
    $mb = cc_upload_limit() / (1024 * 1024);
    return (floor($mb) == $mb ? (int) $mb : round($mb, 1)) . ' MB';
}

/** A clear message for a PHP upload error code. */
function cc_upload_error_message($code)
{
    switch ((int) $code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'The image is too large. The largest image this server accepts is ' . cc_upload_limit_label() . '.';
        case UPLOAD_ERR_PARTIAL:
            return 'The image only partly uploaded. Please try again.';
        case UPLOAD_ERR_NO_FILE:
            return 'Please select a product image.';
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
            return 'The server could not store the upload. Please try again later.';
        case UPLOAD_ERR_EXTENSION:
            return 'The server blocked this upload. Please try a different image.';
        default:
            return 'The image could not be uploaded. Please try again.';
    }
}

/** Message when images/products cannot be written to (folder permissions). */
function cc_upload_folder_message()
{
    return 'The image could not be saved. The images/products folder is not writable and '
        . 'the photo could not be stored in the database either. Please make sure migration 014 has been run.';
}

