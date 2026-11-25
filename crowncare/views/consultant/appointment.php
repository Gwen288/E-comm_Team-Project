<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../core/consultant_helpers.php';
require_once __DIR__ . '/../../classes/ConsultantClass.php';

require_consultant();

$consultant = new ConsultantClass();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$appointment = $id ? $consultant->getAppointment($id) : false;
$history = $appointment ? $consultant->clientHistory($appointment['email'], $appointment['consultation_id']) : [];

$isPast = $appointment && strtotime($appointment['consultation_date'] . ' ' . $appointment['consultation_time']) < time();
$format = $appointment ? (ConsultationClass::FORMATS[$appointment['consultation_format']] ?? null) : null;

$pageTitle = $appointment ? 'Appointment ' . $appointment['reference'] : 'Appointment not found';
require_once __DIR__ . '/../layout/consultant_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p>
            <?php if ($appointment): ?>
                <?php echo htmlspecialchars($appointment['service']); ?> with <?php echo htmlspecialchars($appointment['full_name']); ?>
            <?php else: ?>
                This appointment does not exist.
            <?php endif; ?>
        </p>
    </div>
    <a class="admin-btn admin-btn-secondary" href="<?php echo BASE_URL; ?>views/consultant/appointments.php?view=<?php echo $isPast ? 'history' : 'upcoming'; ?>">Back to appointments</a>
</div>

<?php if ($appointment): ?>

    <div class="admin-detail-grid">
        <section class="admin-card">
            <div class="admin-card-header">
                <h2>Appointment</h2>
            </div>
            <dl class="admin-dl">
                <div><dt>Status</dt><dd><span class="<?php echo cc_status_class($appointment['status']); ?>"><?php echo cc_status_label($appointment['status']); ?></span></dd></div>
                <div><dt>Date</dt><dd><?php echo cc_date_label($appointment['consultation_date'], 'l j F Y'); ?></dd></div>
                <div><dt>Time</dt><dd><?php echo cc_time_label($appointment['consultation_time']); ?>, <?php echo (int) $appointment['duration_minutes']; ?> minutes</dd></div>
                <div><dt>Consultation</dt><dd><?php echo htmlspecialchars($appointment['service']); ?></dd></div>
                <div><dt>Format</dt><dd><?php echo htmlspecialchars($format['name'] ?? $appointment['consultation_format']); ?></dd></div>
                <div><dt>Price</dt><dd><?php echo e(cc_money($appointment['price'])); ?></dd></div>
                <div>
                    <dt>Payment</dt>
                    <dd>
                        <?php if ($appointment['payment_status'] === 'unpaid'): ?>
                            To be paid at the consultation
                        <?php else: ?>
                            <?php echo e(cc_money($appointment['amount_paid'])); ?> paid online<?php echo $appointment['paid_at'] ? ' on ' . cc_date_label($appointment['paid_at'], 'j M Y') : ''; ?>
                        <?php endif; ?>
                    </dd>
                </div>
                <?php if ((float) $appointment['refund_amount'] > 0): ?>
                    <div>
                        <dt>Refund</dt>
                        <dd>
                            <?php echo e(cc_money($appointment['refund_amount'])); ?>,
                            <?php echo e(['pending' => 'sent to Paystack', 'processed' => 'paid out', 'failed' => 'not sent: refund it in the Paystack dashboard'][$appointment['refund_status']] ?? (string) $appointment['refund_status']); ?>
                            <?php if (!empty($appointment['refund_note'])): ?>
                                <br><small><?php echo e($appointment['refund_note']); ?></small>
                            <?php endif; ?>
                        </dd>
                    </div>
                <?php endif; ?>
                <div><dt>Reference</dt><dd><?php echo htmlspecialchars($appointment['reference']); ?></dd></div>
                <div><dt>Booked on</dt><dd><?php echo cc_date_label($appointment['created_at'], 'j M Y, g:i a'); ?></dd></div>
            </dl>
        </section>

        <section class="admin-card">
            <div class="admin-card-header">
                <h2>Client</h2>
            </div>
            <dl class="admin-dl">
                <div><dt>Name</dt><dd><?php echo htmlspecialchars($appointment['full_name']); ?></dd></div>
                <div><dt>Email</dt><dd><a href="mailto:<?php echo htmlspecialchars($appointment['email']); ?>"><?php echo htmlspecialchars($appointment['email']); ?></a></dd></div>
                <div><dt>Phone</dt><dd><a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $appointment['phone'])); ?>"><?php echo htmlspecialchars($appointment['phone']); ?></a></dd></div>
                <div><dt>Account</dt><dd><?php echo $appointment['customer_id'] ? 'Registered customer' : 'Booked as a guest'; ?></dd></div>
                <div><dt>Main concern</dt><dd><?php echo htmlspecialchars($appointment['main_concern'] ?: 'Not given'); ?></dd></div>
                <div><dt>Client's notes</dt><dd><?php echo $appointment['notes'] !== null && $appointment['notes'] !== '' ? nl2br(htmlspecialchars($appointment['notes'])) : 'None'; ?></dd></div>
            </dl>
        </section>
    </div>

    <section class="admin-card">
        <div class="admin-card-header">
            <h2>Update appointment</h2>
            <p>Record what happened and keep private notes. Clients do not see these notes.</p>
        </div>

        <form class="admin-form consultant-form" method="POST" action="<?php echo BASE_URL; ?>actions/consultant_update_appointment_action.php">
            <?php echo cc_consultant_token_field(); ?>
            <input type="hidden" name="id" value="<?php echo (int) $appointment['consultation_id']; ?>">

            <fieldset class="consultant-fieldset">
                <legend>Status</legend>
                <div class="consultant-status-options">
                    <?php foreach (CC_APPOINTMENT_STATUSES as $key => $label): ?>
                        <label class="consultant-radio">
                            <input type="radio" name="status" value="<?php echo $key; ?>" <?php echo $appointment['status'] === $key ? 'checked' : ''; ?>>
                            <span><?php echo $label; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if (!$isPast && $appointment['status'] === 'booked'): ?>
                    <small class="admin-muted">Cancelling frees this time for other clients, refunds the client in full and emails them. Marking a no-show keeps the payment.</small>
                <?php endif; ?>
            </fieldset>

            <div class="form-group">
                <label for="consultant_notes">Consultant notes</label>
                <textarea id="consultant_notes" name="consultant_notes" rows="5" maxlength="2000" placeholder="For example: hair and scalp findings, products recommended, follow-up advice."><?php echo htmlspecialchars((string) $appointment['consultant_notes']); ?></textarea>
            </div>

            <button type="submit" class="admin-btn admin-btn-primary">Save changes</button>
        </form>
    </section>

    <section class="admin-card">
        <div class="admin-card-header">
            <h2>Other appointments with this client</h2>
        </div>

        <?php if (!$history): ?>
            <p class="admin-empty">This is the client's only appointment.</p>
        <?php else: ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Consultation</th>
                            <th>Status</th>
                            <th>Reference</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td><?php echo cc_date_label($row['consultation_date']); ?>, <?php echo cc_time_label($row['consultation_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['service']); ?></td>
                                <td><span class="<?php echo cc_status_class($row['status']); ?>"><?php echo cc_status_label($row['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['reference']); ?></td>
                                <td><div class="admin-actions-links"><a href="?id=<?php echo (int) $row['consultation_id']; ?>">Open</a></div></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

<?php else: ?>
    <section class="admin-card">
        <p class="admin-empty">This appointment does not exist.</p>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/consultant_footer.php'; ?>
