<?php
require_once __DIR__ . '/../../core/core.php';

$defaultName = is_logged_in() ? ($_SESSION['customer_name'] ?? '') : '';
$defaultEmail = is_logged_in() ? ($_SESSION['customer_email'] ?? '') : '';

$pageTitle = 'Contact us';
$pageDescription = 'Contact CrownCare about a product, an order or your hair routine.';
require_once __DIR__ . '/../layout/header.php';
?>

<main class="cc-info">
    <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>

    <h1>Contact us</h1>
    <p class="cc-info-lead">Questions about a product, your order or your routine? Send us a message and we will reply within one working day.</p>

    <div class="cc-contact-grid">
        <div class="cc-panel">
            <h2>Email</h2>
            <p><a href="mailto:<?php echo e(CC_CONTACT_EMAIL); ?>"><?php echo e(CC_CONTACT_EMAIL); ?></a></p>
        </div>
        <div class="cc-panel">
            <h2>Phone and WhatsApp</h2>
            <p><?php echo e(CC_CONTACT_PHONE); ?></p>
        </div>
        <div class="cc-panel">
            <h2>Hours</h2>
            <p><?php echo e(CC_CONTACT_HOURS); ?></p>
        </div>
    </div>

    <section class="cc-panel">
        <h2>Send a message</h2>
        <form
            class="cc-form"
            action="<?php echo BASE_URL; ?>actions/contact_action.php"
            method="POST"
            data-ajax
            data-reset-on-success
            novalidate
        >
            <?php echo cc_csrf_field(); ?>
            <div class="cc-form-alert" role="alert" hidden></div>

            <div class="cc-form-row">
                <div class="cc-field">
                    <label for="name">Your name</label>
                    <input type="text" id="name" name="name" value="<?php echo e(cc_old('name', $defaultName)); ?>" maxlength="100" autocomplete="name" required>
                    <span class="cc-field-error" data-error-for="name"></span>
                </div>
                <div class="cc-field">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="<?php echo e(cc_old('email', $defaultEmail)); ?>" maxlength="100" autocomplete="email" required>
                    <span class="cc-field-error" data-error-for="email"></span>
                </div>
            </div>

            <div class="cc-field">
                <label for="subject">Subject</label>
                <select id="subject" name="subject" required>
                    <?php foreach (['A product', 'My order', 'A consultation', 'Something else'] as $subject): ?>
                        <option value="<?php echo e($subject); ?>" <?php echo cc_old('subject') === $subject ? 'selected' : ''; ?>><?php echo e($subject); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="cc-field">
                <label for="message">Message</label>
                <textarea id="message" name="message" maxlength="2000" required><?php echo e(cc_old('message')); ?></textarea>
                <span class="cc-field-error" data-error-for="message"></span>
            </div>

            <button type="submit" class="cc-button" data-busy="Sending...">Send message</button>
        </form>
    </section>
</main>

<?php
cc_forget_old();
require_once __DIR__ . '/../layout/footer.php';
