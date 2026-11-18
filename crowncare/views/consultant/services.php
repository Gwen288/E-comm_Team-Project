<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../core/consultant_helpers.php';
require_once __DIR__ . '/../../classes/ConsultantClass.php';

require_consultant();

$consultant = new ConsultantClass();
$services = $consultant->services();
$updatedAt = $consultant->serviceUpdatedAt();

$pageTitle = 'Services and prices';
require_once __DIR__ . '/../layout/consultant_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Services and prices</h1>
        <p>Set the price and length of each consultation offered on the booking page.</p>
    </div>
    <a class="admin-btn admin-btn-secondary" href="<?php echo BASE_URL; ?>views/book_consultation.php">View booking page</a>
</div>

<form class="admin-form consultant-form" method="POST" action="<?php echo BASE_URL; ?>actions/consultant_save_services_action.php">
    <?php echo cc_consultant_token_field(); ?>

    <div class="consultant-service-grid">
        <?php foreach ($services as $key => $service): ?>
            <section class="admin-card consultant-service-card">
                <div class="admin-card-header">
                    <h2><?php echo htmlspecialchars($service['name']); ?></h2>
                </div>

                <div class="consultant-service-fields">
                    <div class="form-group">
                        <label for="price-<?php echo $key; ?>">Price (GH₵)</label>
                        <input
                            type="number"
                            id="price-<?php echo $key; ?>"
                            name="services[<?php echo $key; ?>][price]"
                            min="0"
                            max="10000"
                            step="0.01"
                            value="<?php echo number_format($service['price'], 2, '.', ''); ?>"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="minutes-<?php echo $key; ?>">Duration</label>
                        <select id="minutes-<?php echo $key; ?>" name="services[<?php echo $key; ?>][minutes]">
                            <?php foreach (ConsultationClass::DURATION_OPTIONS as $minutes): ?>
                                <option value="<?php echo $minutes; ?>" <?php echo (int) $service['minutes'] === $minutes ? 'selected' : ''; ?>><?php echo $minutes; ?> minutes</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if (!empty($updatedAt[$key])): ?>
                    <p class="admin-muted">Last changed <?php echo cc_date_label($updatedAt[$key], 'j M Y, g:i a'); ?></p>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <p class="admin-muted consultant-footnote">New prices apply to new bookings. Appointments already booked keep the price the client booked at.</p>

    <div class="consultant-save-bar">
        <button type="submit" class="admin-btn admin-btn-primary">Save prices</button>
    </div>
</form>

<?php require_once __DIR__ . '/../layout/consultant_footer.php'; ?>
