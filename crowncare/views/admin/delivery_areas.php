<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../classes/DeliveryClass.php';

require_admin();

$areas = (new DeliveryClass())->getAllAreas();

$pageTitle = 'Delivery areas';
require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Delivery areas</h1>
        <p>Areas in Accra that customers can choose, and the delivery fee for each. Orders from <?php echo e(cc_money(FREE_DELIVERY_THRESHOLD)); ?> are delivered free.</p>
    </div>
</div>

<section class="admin-card">
    <div class="admin-card-header">
        <h2>Add an area</h2>
    </div>
    <form class="admin-area-form" method="POST" action="<?php echo BASE_URL; ?>actions/save_delivery_area_action.php">
        <?php echo cc_csrf_field(); ?>
        <label>
            <span>Area name</span>
            <input type="text" name="area_name" maxlength="30" required>
        </label>
        <label>
            <span>Delivery fee (GH₵)</span>
            <input type="number" name="delivery_fee" min="0" max="1000" step="0.50" value="20.00" required>
        </label>
        <input type="hidden" name="is_active" value="1">
        <button type="submit" class="admin-btn admin-btn-primary">Add area</button>
    </form>
</section>

<section class="admin-card">
    <div class="admin-card-header">
        <h2>Areas</h2>
    </div>
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Area</th>
                    <th>Delivery fee (GH₵)</th>
                    <th>Shown to customers</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($areas as $area): ?>
                    <?php $formId = 'area-' . (int) $area['area_id']; ?>
                    <tr>
                        <td>
                            <input form="<?php echo $formId; ?>" type="text" name="area_name" value="<?php echo e($area['area_name']); ?>" maxlength="30" required aria-label="Area name">
                        </td>
                        <td>
                            <input form="<?php echo $formId; ?>" type="number" name="delivery_fee" value="<?php echo e(number_format((float) $area['delivery_fee'], 2, '.', '')); ?>" min="0" max="1000" step="0.50" required aria-label="Delivery fee for <?php echo e($area['area_name']); ?>">
                        </td>
                        <td>
                            <label class="admin-check">
                                <input form="<?php echo $formId; ?>" type="checkbox" name="is_active" value="1" <?php echo (int) $area['is_active'] === 1 ? 'checked' : ''; ?>>
                                <span><?php echo (int) $area['is_active'] === 1 ? 'Yes' : 'No'; ?></span>
                            </label>
                        </td>
                        <td>
                            <form id="<?php echo $formId; ?>" method="POST" action="<?php echo BASE_URL; ?>actions/save_delivery_area_action.php">
                                <?php echo cc_csrf_field(); ?>
                                <input type="hidden" name="area_id" value="<?php echo (int) $area['area_id']; ?>">
                                <button type="submit" class="admin-btn">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>
