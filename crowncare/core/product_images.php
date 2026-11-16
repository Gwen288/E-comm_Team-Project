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

    $main = basename(trim($product['product_image'] ?? ''));
    if ($main !== '' && is_file($dir . $main)) {
        return BASE_URL . 'images/products/' . rawurlencode($main);
    }

    return null;
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
