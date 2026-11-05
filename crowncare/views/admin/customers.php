<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/CustomerController.php';

require_admin();

$controller = new CustomerController();
$customers = $controller->getAllCustomers();

require_once __DIR__ . '/../layout/admin_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Customers</h1>
        <p>Everyone who has signed up on CrownCare.</p>
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
                        <th>Location</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <?php
                        $role = ((int) ($customer['user_role'] ?? 2) === 1)
                            ? 'Admin'
                            : 'Customer';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['customer_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['customer_email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['customer_contact'] ?? ''); ?></td>
                            <td>
                                <?php
                                echo htmlspecialchars(
                                    trim(
                                        ($customer['customer_city'] ?? '')
                                        . ', '
                                        . ($customer['customer_country'] ?? ''),
                                        ', '
                                    )
                                );
                                ?>
                            </td>
                            <td><?php echo $role; ?></td>
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
