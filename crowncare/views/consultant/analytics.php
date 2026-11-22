<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../core/consultant_helpers.php';
require_once __DIR__ . '/../../classes/ConsultantClass.php';

require_consultant();

$consultant = new ConsultantClass();

// Default period: the last 30 days and the next 30 days, so upcoming bookings show too.
$defaultFrom = date('Y-m-d', strtotime('-30 days'));
$defaultTo = date('Y-m-d', strtotime('+30 days'));

$from = $_GET['from'] ?? $defaultFrom;
$to = $_GET['to'] ?? $defaultTo;

$validDate = function ($value) {
    $d = DateTime::createFromFormat('Y-m-d', (string) $value);
    return $d && $d->format('Y-m-d') === $value;
};

if (!$validDate($from) || !$validDate($to) || $from > $to) {
    $from = $defaultFrom;
    $to = $defaultTo;
}

$data = $consultant->analytics($from, $to);

$active = $data['bookings'] - $data['cancelled'];
$settled = $data['completed'] + $data['no_show'];
$completionRate = $settled > 0 ? round($data['completed'] / $settled * 100) : 0;
$cancelRate = $data['bookings'] > 0 ? round($data['cancelled'] / $data['bookings'] * 100) : 0;

// Chart data.
$dayLabels = [];
$dayCounts = [];
foreach ($data['by_day'] as $row) {
    $dayLabels[] = date('j M', strtotime($row['day']));
    $dayCounts[] = (int) $row['bookings'];
}

$weekdayCounts = array_fill(1, 7, 0);
foreach ($data['by_weekday'] as $row) {
    $weekdayCounts[(int) $row['weekday']] = (int) $row['bookings'];
}

$formatCounts = ['in_person' => 0, 'video' => 0];
foreach ($data['by_format'] as $row) {
    $formatCounts[$row['consultation_format']] = (int) $row['bookings'];
}

$json = function ($value) {
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
};

$pageTitle = 'Analytics';
require_once __DIR__ . '/../layout/consultant_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Analytics</h1>
        <p><?php echo e(cc_date_label($from, 'j M Y') . ' to ' . cc_date_label($to, 'j M Y')); ?></p>
    </div>
</div>

<form class="cc-date-filter" method="GET">
    <label>
        <span>From</span>
        <input type="date" name="from" value="<?php echo e($from); ?>">
    </label>
    <label>
        <span>To</span>
        <input type="date" name="to" value="<?php echo e($to); ?>">
    </label>
    <button type="submit" class="admin-btn admin-btn-primary">Apply</button>
    <a class="admin-btn" href="<?php echo BASE_URL; ?>views/consultant/analytics.php">Reset</a>
</form>

<div class="admin-stat-grid cc-stat-grid">
    <div class="admin-stat-card"><span>Appointments</span><strong><?php echo number_format($active); ?></strong></div>
    <div class="admin-stat-card"><span>Still to take place</span><strong><?php echo number_format($data['booked']); ?></strong></div>
    <div class="admin-stat-card"><span>Income received</span><strong><?php echo e(cc_money($data['revenue'])); ?></strong></div>
    <div class="admin-stat-card"><span>Expected income</span><strong><?php echo e(cc_money($data['expected'])); ?></strong></div>
    <div class="admin-stat-card"><span>Clients</span><strong><?php echo number_format($data['clients']); ?></strong></div>
    <div class="admin-stat-card"><span>Repeat clients</span><strong><?php echo number_format($data['returning']); ?></strong></div>
    <div class="admin-stat-card"><span>Completed</span><strong><?php echo number_format($data['completed']); ?></strong></div>
    <div class="admin-stat-card"><span>No-shows</span><strong><?php echo number_format($data['no_show']); ?></strong></div>
    <div class="admin-stat-card"><span>Attendance rate</span><strong><?php echo $completionRate; ?>%</strong></div>
    <div class="admin-stat-card"><span>Cancelled</span><strong><?php echo number_format($data['cancelled']); ?></strong></div>
</div>

<section class="admin-card">
    <div class="admin-card-header"><h2>Appointments per day</h2></div>
    <?php if (!$dayCounts): ?>
        <p class="admin-empty">No appointments in this period.</p>
    <?php else: ?>
        <div class="cc-chart cc-chart--wide"><canvas id="dayChart"></canvas></div>
    <?php endif; ?>
</section>

<div class="cc-chart-grid">
    <section class="admin-card">
        <div class="admin-card-header"><h2>Appointment status</h2></div>
        <div class="cc-chart"><canvas id="statusChart"></canvas></div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Bookings by consultation</h2></div>
        <?php if (!$data['by_service']): ?>
            <p class="admin-empty">No bookings in this period.</p>
        <?php else: ?>
            <div class="cc-chart"><canvas id="serviceChart"></canvas></div>
        <?php endif; ?>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>In person and video call</h2></div>
        <div class="cc-chart"><canvas id="formatChart"></canvas></div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Income by consultation</h2></div>
        <div class="cc-chart"><canvas id="incomeChart"></canvas></div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Busiest days</h2></div>
        <div class="cc-chart"><canvas id="weekdayChart"></canvas></div>
    </section>

    <section class="admin-card">
        <div class="admin-card-header"><h2>Most booked times</h2></div>
        <?php if (!$data['by_hour']): ?>
            <p class="admin-empty">No bookings in this period.</p>
        <?php else: ?>
            <div class="cc-chart"><canvas id="hourChart"></canvas></div>
        <?php endif; ?>
    </section>
</div>

<section class="admin-card">
    <div class="admin-card-header"><h2>Main hair concerns</h2></div>
    <?php if (!$data['by_concern']): ?>
        <p class="admin-empty">No bookings in this period.</p>
    <?php else: ?>
        <div class="cc-chart cc-chart--wide"><canvas id="concernChart"></canvas></div>
    <?php endif; ?>
</section>

<script src="<?php echo BASE_URL; ?>js/vendor/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    // Creates a chart only when its canvas is on the page (empty charts are left out).
    function makeChart(id, config) {
        var canvas = document.getElementById(id);
        return canvas ? new Chart(canvas, config) : null;
    }

    if (typeof Chart === 'undefined') return;

    var palette = ['#A84E2B', '#7A8B69', '#D9A441', '#6D7FA8', '#B87991', '#6B9C91', '#9A7BB5', '#C98B5E'];
    var gridColor = 'rgba(74, 44, 33, 0.08)';

    Chart.defaults.font.family = "'Instrument Sans', 'Nunito Sans', sans-serif";
    Chart.defaults.color = '#6F625A';

    var money = function (v) {
        return 'GH₵' + Number(v).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    var noLegend = { legend: { display: false } };
    var countAxis = { beginAtZero: true, grid: { color: gridColor }, ticks: { precision: 0 } };

    function bar(id, labels, values, options) {
        options = options || {};
        makeChart(id, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: options.colors || palette[0],
                    borderRadius: 8,
                    maxBarThickness: 44
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: options.horizontal ? 'y' : 'x',
                plugins: {
                    legend: { display: false },
                    tooltip: options.money ? { callbacks: { label: function (c) { return money(c.raw); } } } : {}
                },
                scales: options.horizontal
                    ? { x: options.money ? { beginAtZero: true, grid: { color: gridColor } } : countAxis, y: { grid: { display: false } } }
                    : { y: options.money ? { beginAtZero: true, grid: { color: gridColor }, ticks: { callback: function (v) { return 'GH₵' + v; } } } : countAxis, x: { grid: { display: false } } }
            }
        });
    }

    function doughnut(id, labels, values, colors) {
        makeChart(id, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0, hoverOffset: 7 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: noLegend }
        });
    }

    /* Appointments over time */
    makeChart('dayChart', {
        type: 'line',
        data: {
            labels: <?php echo $json($dayLabels); ?>,
            datasets: [{
                label: 'Appointments',
                data: <?php echo $json($dayCounts); ?>,
                borderColor: '#A84E2B',
                backgroundColor: 'rgba(168, 78, 43, 0.13)',
                fill: true,
                cubicInterpolationMode: 'monotone',
                pointRadius: 4,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: noLegend,
            scales: { y: countAxis, x: { grid: { display: false } } }
        }
    });

    doughnut('statusChart', ['Booked', 'Completed', 'No-show', 'Cancelled'],
        <?php echo $json([$data['booked'], $data['completed'], $data['no_show'], $data['cancelled']]); ?>,
        ['#6D7FA8', '#6F9475', '#E0B65D', '#C97870']);

    var services = <?php echo $json($data['by_service']); ?>;
    bar('serviceChart', services.map(function (r) { return r.service; }), services.map(function (r) { return Number(r.bookings); }), { colors: palette });
    bar('incomeChart', services.map(function (r) { return r.service; }), services.map(function (r) { return Number(r.revenue); }), { colors: palette, money: true, horizontal: true });

    doughnut('formatChart', ['In person', 'Video call'],
        <?php echo $json([$formatCounts['in_person'], $formatCounts['video']]); ?>,
        ['#A84E2B', '#7A8B69']);

    bar('weekdayChart', <?php echo $json(array_values(ConsultationClass::WEEKDAYS)); ?>, <?php echo $json(array_values($weekdayCounts)); ?>, { colors: '#7A8B69' });

    var hours = <?php echo $json($data['by_hour']); ?>;
    bar('hourChart', hours.map(function (r) {
        var h = parseInt(r.start_time, 10);
        return (h % 12 || 12) + ':' + r.start_time.slice(3) + (h < 12 ? ' am' : ' pm');
    }), hours.map(function (r) { return Number(r.bookings); }), { colors: '#D9A441' });

    var concerns = <?php echo $json($data['by_concern']); ?>;
    bar('concernChart', concerns.map(function (r) { return r.concern; }), concerns.map(function (r) { return Number(r.bookings); }), { colors: '#6D7FA8', horizontal: true });
})();
</script>

<?php require_once __DIR__ . '/../layout/consultant_footer.php'; ?>
