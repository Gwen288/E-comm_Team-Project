<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../core/consultant_helpers.php';
require_once __DIR__ . '/../../classes/ConsultantClass.php';

require_consultant();

$consultant = new ConsultantClass();
$services = $consultant->services();

$view = in_array($_GET['view'] ?? '', ['upcoming', 'history', 'all'], true) ? $_GET['view'] : 'upcoming';

$cleanDate = function ($value) {
    $d = DateTime::createFromFormat('Y-m-d', (string) $value);
    return ($d && $d->format('Y-m-d') === $value) ? $value : '';
};

$serviceNames = array_column($services, 'name');

$filters = [
    'view' => $view,
    'status' => isset(CC_APPOINTMENT_STATUSES[$_GET['status'] ?? '']) ? $_GET['status'] : '',
    'service' => in_array($_GET['service'] ?? '', $serviceNames, true) ? $_GET['service'] : '',
    'search' => mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 60),
    'from' => $cleanDate($_GET['from'] ?? ''),
    'to' => $cleanDate($_GET['to'] ?? ''),
];

// Upcoming only lists booked appointments, so the status filter is not offered there.
if ($view === 'upcoming') {
    $filters['status'] = '';
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$total = $consultant->countAppointments($filters);
$pages = max(1, (int) ceil($total / ConsultantClass::PAGE_SIZE));
$page = min($page, $pages);
$appointments = $consultant->appointments($filters, $page);

$queryFor = function (array $changes) use ($filters) {
    return '?' . http_build_query(array_filter(array_merge($filters, $changes), 'strlen'));
};

$headings = [
    'upcoming' => ['Upcoming appointments', 'Booked appointments from now on, soonest first.'],
    'history' => ['Appointment history', 'Past appointments and every cancelled, completed or no-show appointment, newest first.'],
    'all' => ['All appointments', 'Every appointment, newest first.'],
];

$pageTitle = $headings[$view][0];
require_once __DIR__ . '/../layout/consultant_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1><?php echo $headings[$view][0]; ?></h1>
        <p><?php echo $headings[$view][1]; ?></p>
    </div>
</div>

<nav class="consultant-tabs" aria-label="Appointment lists">
    <?php foreach (['upcoming' => 'Upcoming', 'history' => 'History', 'all' => 'All'] as $key => $label): ?>
        <a class="<?php echo $view === $key ? 'is-active' : ''; ?>" href="?view=<?php echo $key; ?>"><?php echo $label; ?></a>
    <?php endforeach; ?>
</nav>

<form class="analytics-filter consultant-filter" method="GET">
    <input type="hidden" name="view" value="<?php echo $view; ?>">

    <div class="consultant-filter-search">
        <label for="search">Search</label>
        <input type="search" id="search" name="search" placeholder="Name, email, phone or reference" value="<?php echo htmlspecialchars($filters['search']); ?>">
    </div>

    <div>
        <label for="service">Consultation</label>
        <select id="service" name="service">
            <option value="">All consultations</option>
            <?php foreach ($serviceNames as $name): ?>
                <option value="<?php echo htmlspecialchars($name); ?>" <?php echo $filters['service'] === $name ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($view !== 'upcoming'): ?>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">All statuses</option>
                <?php foreach (CC_APPOINTMENT_STATUSES as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $filters['status'] === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <div>
        <label for="from">From</label>
        <input type="date" id="from" name="from" value="<?php echo htmlspecialchars($filters['from']); ?>">
    </div>
    <div>
        <label for="to">To</label>
        <input type="date" id="to" name="to" value="<?php echo htmlspecialchars($filters['to']); ?>">
    </div>

    <button type="submit">Apply</button>
    <a href="?view=<?php echo $view; ?>">Reset</a>
</form>

<section class="admin-card">
    <div class="admin-card-header">
        <h2><?php echo number_format($total) . ($total === 1 ? ' appointment' : ' appointments'); ?></h2>
    </div>

    <?php if (!$appointments): ?>
        <p class="admin-empty">No appointments match.</p>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Consultation</th>
                        <th>Format</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Reference</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $row): ?>
                        <tr>
                            <td>
                                <strong><?php echo cc_date_label($row['consultation_date']); ?></strong><br>
                                <span class="admin-muted"><?php echo cc_time_label($row['consultation_time']); ?>, <?php echo (int) $row['duration_minutes']; ?> minutes</span>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                                <span class="admin-muted"><?php echo htmlspecialchars($row['email']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($row['service']); ?></td>
                            <td><?php echo cc_format_label($row['consultation_format']); ?></td>
                            <td>GH₵<?php echo number_format((float) $row['price'], 2); ?></td>
                            <td><span class="<?php echo cc_status_class($row['status']); ?>"><?php echo cc_status_label($row['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['reference']); ?></td>
                            <td><div class="admin-actions-links"><a href="<?php echo BASE_URL; ?>views/consultant/appointment.php?id=<?php echo (int) $row['consultation_id']; ?>">Open</a></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
            <nav class="consultant-pager" aria-label="Pages">
                <?php if ($page > 1): ?>
                    <a href="<?php echo htmlspecialchars($queryFor(['page' => $page - 1])); ?>">Previous</a>
                <?php endif; ?>
                <span>Page <?php echo $page; ?> of <?php echo $pages; ?></span>
                <?php if ($page < $pages): ?>
                    <a href="<?php echo htmlspecialchars($queryFor(['page' => $page + 1])); ?>">Next</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layout/consultant_footer.php'; ?>
