<?php

require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../classes/StockClass.php';

/*
 * Product detail page for shoppers: gallery, size choices, stock note and the
 * Add to Cart / Buy Now form. The form posts to actions/add_to_cart_action.php.
 */

$controller = new ProductController();

// A bad or missing pro_id is treated as "not found" below, not as an error page.
$productId = filter_input(INPUT_GET, 'pro_id', FILTER_VALIDATE_INT);
$product = false;

if ($productId !== false && $productId !== null && $productId > 0) {
    $product = $controller->getProductById($productId);
}

$sizes = [];
$selectedSize = null;
$media = ['all' => [], 'sizes' => []];

if ($product) {
    $sizes = $controller->getProductSizes($productId);
    $media = cc_product_media($product);

    // ?size= from a cart or shop link selects a size on page load.
    $requestedSize = filter_input(INPUT_GET, 'size', FILTER_VALIDATE_INT);
    foreach ($sizes as $size) {
        if ($requestedSize && (int) $size['size_id'] === $requestedSize) {
            $selectedSize = $size;
        }
    }
}

// Before a size is chosen the page shows the lowest price, as on the CMS.
$displayPrice = $selectedSize
    ? (float) $selectedSize['price']
    : ($sizes ? (float) $sizes[0]['price'] : (float) ($product['product_price'] ?? 0));

// Some sizes have their own photos; otherwise use the product's usual gallery.
$photos = $media['all'];
if ($selectedSize && isset($media['sizes'][cc_size_key($selectedSize['size_name'])])) {
    $photos = $media['sizes'][cc_size_key($selectedSize['size_name'])];
}

// Stock: per size, or for the whole product when it has no sizes.
$stockClass = new StockClass();
$sizeStock = $product ? $stockClass->getSizeStock($productId) : [];
$productStock = $product && !$sizes ? $stockClass->getAvailable($productId, 0) : 0;
$selectedStock = $selectedSize ? ($sizeStock[(int) $selectedSize['size_id']] ?? 0) : ($sizes ? null : $productStock);
$allSoldOut = $product && ($sizes ? array_sum($sizeStock) <= 0 : $productStock <= 0);

if (!$product) {
    http_response_code(404);
}

$pageTitle = $product ? $product['product_title'] : 'Product not found';
$pageDescription = $product && !empty($product['product_desc']) ? mb_substr($product['product_desc'], 0, 155) : null;

// The cart action sets this when a sized product is added without picking a size.
$sizeError = !empty($_SESSION['size_error']);
unset($_SESSION['size_error']);

// Hide the buying buttons for staff accounts; the cart also blocks them.
$canBuy = !(is_logged_in() && is_admin());

require_once __DIR__ . '/layout/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/cms-shop.css?v=20261111">

<main class="cc-page cc-product">


    <?php if ($product === false): ?>

        <div class="cc-shell cc-empty">
            <h1 class="cc-category-title">Product not found</h1>
            <p>This product does not exist or is no longer available.</p>
            <a class="cc-btn" style="max-width: 280px; margin: 24px auto 0;" href="<?php echo BASE_URL; ?>views/shop.php">Back to Shop</a>
        </div>

    <?php else: ?>

        <a class="cc-back cc-product-back" href="<?php echo BASE_URL; ?>views/shop.php" data-back>Back to shop</a>

        <div class="cc-product-layout">

            <!-- GALLERY: thumbnails on the left, large photo on the right -->
            <section
                class="cc-gallery<?php echo count($photos) > 1 ? '' : ' cc-gallery--single'; ?>"
                aria-label="Product photos"
                data-gallery
            >
                <div class="cc-gallery-thumbs" data-gallery-thumbs <?php echo count($photos) > 1 ? '' : 'hidden'; ?>>
                    <?php foreach ($photos as $i => $photo): ?>
                        <button
                            type="button"
                            class="cc-gallery-thumb<?php echo $i === 0 ? ' is-active' : ''; ?>"
                            data-src="<?php echo cc_e($photo['src']); ?>"
                            data-alt="<?php echo cc_e($photo['label']); ?>"
                            aria-label="Show photo <?php echo $i + 1; ?>"
                        >
                            <img src="<?php echo cc_e($photo['src']); ?>" alt="" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="cc-gallery-main">
                    <?php if ($photos): ?>
                        <img data-gallery-main src="<?php echo cc_e($photos[0]['src']); ?>" alt="<?php echo cc_e($photos[0]['label']); ?>">
                    <?php else: ?>
                        <div class="cc-card-noimage">Photo coming soon</div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- DETAILS -->
            <section class="cc-product-info">

                <h1 class="cc-product-title"><?php echo cc_e($product['product_title']); ?></h1>
                <p class="cc-product-price" data-price><?php echo cc_money($displayPrice); ?></p>
                <p
                    class="cc-stock-note<?php echo ($selectedStock !== null && $selectedStock <= 0) || $allSoldOut ? ' is-out' : ''; ?>"
                    data-stock-note
                    aria-live="polite"
                ><?php
                    if ($allSoldOut) {
                        echo 'Out of stock';
                    } elseif ($selectedStock !== null && $selectedStock <= 0) {
                        echo 'This size is out of stock';
                    } elseif ($selectedStock !== null && $selectedStock <= CC_LOW_STOCK) {
                        echo 'Only ' . (int) $selectedStock . ' left';
                    }
                ?></p>

                <form
                    method="POST"
                    action="<?php echo BASE_URL; ?>actions/add_to_cart_action.php"
                    data-product-form
                    data-has-sizes="<?php echo $sizes ? '1' : '0'; ?>"
                    data-stock="<?php echo $selectedStock === null ? '' : (int) $selectedStock; ?>"
                    data-low-stock="<?php echo (int) CC_LOW_STOCK; ?>"
                    novalidate
                >
                    <input type="hidden" name="product_id" value="<?php echo (int) $product['product_id']; ?>">
                    <input type="hidden" name="size_id" value="<?php echo $selectedSize ? (int) $selectedSize['size_id'] : 0; ?>" data-size-input>

                    <?php if ($sizes): ?>
                        <fieldset class="cc-field<?php echo $sizeError ? ' cc-size-error' : ''; ?>" data-size-field>
                            <legend>Size<span aria-hidden="true">*</span></legend>
                            <div class="cc-field-row">
                                <?php foreach ($sizes as $size): ?>
                                    <?php
                                    $isSelected = $selectedSize && (int) $selectedSize['size_id'] === (int) $size['size_id'];
                                    $thisStock = $sizeStock[(int) $size['size_id']] ?? 0;
                                    ?>
                                    <button
                                        type="button"
                                        data-stock="<?php echo (int) $thisStock; ?>"
                                        class="cc-size-option<?php echo $isSelected ? ' is-selected' : ''; ?><?php echo $thisStock <= 0 ? ' is-soldout' : ''; ?>"
                                        aria-pressed="<?php echo $isSelected ? 'true' : 'false'; ?>"
                                        data-size-id="<?php echo (int) $size['size_id']; ?>"
                                        data-size-key="<?php echo cc_e(cc_size_key($size['size_name'])); ?>"
                                        data-price="<?php echo cc_e(cc_money($size['price'])); ?>"
                                    ><?php echo cc_e(cc_size_label($size['size_name'])); ?></button>
                                <?php endforeach; ?>
                                <span class="cc-field-error" data-size-error <?php echo $sizeError ? '' : 'hidden'; ?>>
                                    <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M12 7v6M12 16v1" stroke="currentColor" stroke-width="1.5"/></svg>
                                    Select Size
                                </span>
                            </div>
                        </fieldset>
                    <?php endif; ?>

                    <fieldset class="cc-field">
                        <legend>Quantity<span aria-hidden="true">*</span></legend>
                        <div class="cc-counter" data-counter>
                            <button type="button" data-step="-1" aria-label="Decrease quantity" disabled>&minus;</button>
                            <input type="number" name="qty" value="1" min="1" max="99" inputmode="numeric" aria-label="Quantity">
                            <button type="button" data-step="1" aria-label="Increase quantity">+</button>
                        </div>
                    </fieldset>

                    <?php if ($canBuy): ?>
                        <div class="cc-product-actions">
                            <button type="submit" class="cc-btn" data-buy-button <?php echo $allSoldOut || ($selectedStock !== null && $selectedStock <= 0) ? 'disabled' : ''; ?>>Add to Cart</button>
                            <button type="submit" class="cc-btn cc-btn--tall" name="buy_now" value="1" data-buy-button <?php echo $allSoldOut || ($selectedStock !== null && $selectedStock <= 0) ? 'disabled' : ''; ?>>Buy Now</button>
                        </div>
                    <?php endif; ?>
                </form>

                <?php if (!empty($product['product_desc'])): ?>
                    <p class="cc-description" data-description><?php echo cc_e($product['product_desc']); ?></p>
                    <button type="button" class="cc-read-more" data-read-more hidden>Read more</button>
                <?php endif; ?>

            </section>
        </div>

        <!-- All gallery sets go to the script below so switching sizes needs no reload. -->
        <script type="application/json" id="cc-product-media"><?php
            echo json_encode([
                'all' => $media['all'],
                'sizes' => (object) $media['sizes'],
            ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
        ?></script>

    <?php endif; ?>

</main>

<script>
(function () {
    var form = document.querySelector('[data-product-form]');
    if (!form) return;

    var media = JSON.parse(document.getElementById('cc-product-media').textContent);
    var gallery = document.querySelector('[data-gallery]');
    var thumbsBox = gallery.querySelector('[data-gallery-thumbs]');
    var mainImg = gallery.querySelector('[data-gallery-main]');
    var priceEl = document.querySelector('[data-price]');
    var sizeInput = form.querySelector('[data-size-input]');
    var sizeField = form.querySelector('[data-size-field]');
    var sizeError = form.querySelector('[data-size-error]');

    /* Gallery: clicking a thumbnail shows it in the large frame. */
    function showPhoto(thumb) {
        if (!mainImg) return;
        mainImg.src = thumb.dataset.src;
        mainImg.alt = thumb.dataset.alt;
        thumbsBox.querySelectorAll('.cc-gallery-thumb').forEach(function (t) {
            t.classList.toggle('is-active', t === thumb);
        });
    }

    thumbsBox.addEventListener('click', function (e) {
        var thumb = e.target.closest('.cc-gallery-thumb');
        if (thumb) showPhoto(thumb);
    });

    /* Replaces the gallery photos, e.g. with the photos of the chosen size. */
    function setPhotos(photos) {
        if (!mainImg || !photos || !photos.length) return;
        thumbsBox.innerHTML = '';
        photos.forEach(function (photo, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'cc-gallery-thumb' + (i === 0 ? ' is-active' : '');
            b.dataset.src = photo.src;
            b.dataset.alt = photo.label;
            b.setAttribute('aria-label', 'Show photo ' + (i + 1));
            var img = document.createElement('img');
            img.src = photo.src;
            img.alt = '';
            b.appendChild(img);
            thumbsBox.appendChild(b);
        });
        thumbsBox.hidden = photos.length < 2;
        gallery.classList.toggle('cc-gallery--single', photos.length < 2);
        mainImg.src = photos[0].src;
        mainImg.alt = photos[0].label;
    }

    /* Stock message and buttons for the chosen size. */
    var stockNote = document.querySelector('[data-stock-note]');
    var buyButtons = form.querySelectorAll('[data-buy-button]');
    var lowStock = parseInt(form.dataset.lowStock, 10) || 5;

    function showStock(stock) {
        form.dataset.stock = stock;
        var out = stock <= 0;
        stockNote.textContent = out ? 'This size is out of stock' : (stock <= lowStock ? 'Only ' + stock + ' left' : '');
        stockNote.classList.toggle('is-out', out);
        buyButtons.forEach(function (b) { b.disabled = out; });
        setQty(qty.value);
    }

    /* Size boxes: choosing a size updates the price, the photos and the address. */
    form.querySelectorAll('.cc-size-option').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.querySelectorAll('.cc-size-option').forEach(function (b) {
                var on = b === btn;
                b.classList.toggle('is-selected', on);
                b.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            sizeInput.value = btn.dataset.sizeId;
            priceEl.textContent = btn.dataset.price;
            showStock(parseInt(btn.dataset.stock, 10));
            if (sizeError) sizeError.hidden = true;
            if (sizeField) sizeField.classList.remove('cc-size-error');
            setPhotos(media.sizes[btn.dataset.sizeKey] || media.all);

            var url = new URL(window.location.href);
            url.searchParams.set('size', btn.dataset.sizeId);
            history.replaceState(null, '', url);
        });
    });

    /* Quantity counter. */
    var counter = form.querySelector('[data-counter]');
    var qty = counter.querySelector('input');
    var minus = counter.querySelector('[data-step="-1"]');

    function setQty(value) {
        var max = form.dataset.stock !== '' ? Math.max(1, Math.min(99, parseInt(form.dataset.stock, 10) || 1)) : 99;
        var n = Math.max(1, Math.min(max, parseInt(value, 10) || 1));
        qty.value = n;
        minus.disabled = n <= 1;
    }

    counter.addEventListener('click', function (e) {
        var step = e.target.closest('[data-step]');
        if (step) setQty((parseInt(qty.value, 10) || 1) + parseInt(step.dataset.step, 10));
    });
    qty.addEventListener('change', function () { setQty(qty.value); });

    /* A size must be chosen before adding to the cart. */
    form.addEventListener('submit', function (e) {
        if (form.dataset.hasSizes === '1' && sizeInput.value === '0') {
            e.preventDefault();
            if (sizeError) sizeError.hidden = false;
            if (sizeField) sizeField.classList.add('cc-size-error');
        }
    });

    /* Description: three lines with "Read more", as on the CMS. */
    var desc = document.querySelector('[data-description]');
    var more = document.querySelector('[data-read-more]');
    if (desc && more && desc.scrollHeight > desc.clientHeight + 2) {
        more.hidden = false;
        more.addEventListener('click', function () {
            var open = desc.classList.toggle('is-expanded');
            more.textContent = open ? 'Read less' : 'Read more';
        });
    }
})();
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
