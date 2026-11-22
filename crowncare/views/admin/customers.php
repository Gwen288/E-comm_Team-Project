<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';

require_admin();

/* Customer accounts for admins, with order and paid-order counts from the controller. */

$controller = new CustomerController();
$customers = $controller->getAllCustomers();

$pageTitle = 'Customers';
require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Customers</h1>
        <p>Customer accounts. Staff accounts are not listed.</p>
    </div>
</div>

<section class="admin-card">
    <?php if (empty($customers)): ?>
        <p class="admin-empty">No accounts yet.</p>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Account</th>
                        <th>Orders</th>
                        <th>Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <!-- A closed account keeps its orders, but cannot sign in. -->
                        <?php $isClosed = ($customer['customer_status'] ?? 'active') === 'closed'; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['customer_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['customer_email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['customer_contact'] ?? ''); ?></td>
                            <td><span class="<?php echo $isClosed ? 'cc-status cc-status--bad' : 'cc-status cc-status--good'; ?>"><?php echo $isClosed ? 'Closed' : 'Active'; ?></span></td>
                            <td><?php echo (int) ($customer['order_count'] ?? 0); ?></td>
                            <td><?php echo (int) ($customer['paid_order_count'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layout/admin_footer.php'; ?>
