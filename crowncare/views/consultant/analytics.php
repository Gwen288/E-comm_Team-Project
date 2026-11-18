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

<div class="admin-page-header analytics-heading">
    <div>
        <p class="analytics-eyebrow">CONSULTATION PERFORMANCE</p>
        <h1>Analytics</h1>
        <p>Bookings, income and client trends for hair consultations.</p>
    </div>

    <div class="analytics-period-label">
        <span>APPOINTMENT DATES</span>
        <strong><?php echo cc_date_label($from, 'M j, Y'); ?> to <?php echo cc_date_label($to, 'M j, Y'); ?></strong>
    </div>
</div>

<form class="analytics-filter" method="GET">
    <div>
        <label for="from">From</label>
        <input type="date" id="from" name="from" value="<?php echo htmlspecialchars($from); ?>">
    </div>
    <div>
        <label for="to">To</label>
        <input type="date" id="to" name="to" value="<?php echo htmlspecialchars($to); ?>">
    </div>
    <button type="submit">Apply dates</button>
    <a href="<?php echo BASE_URL; ?>views/consultant/analytics.php">Reset</a>
</form>

<div class="analytics-kpi-grid">
    <article class="analytics-kpi kpi-orders">
        <div class="analytics-kpi-top"><span>Appointments</span></div>
        <strong><?php echo number_format($active); ?></strong>
        <small><?php echo number_format($data['booked']); ?> still to take place</small>
    </article>

    <article class="analytics-kpi kpi-revenue">
        <div class="analytics-kpi-top"><span>Income received</span></div>
        <strong>GH₵<?php echo number_format($data['revenue'], 2); ?></strong>
        <small>From completed consultations</small>
    </article>

    <article class="analytics-kpi kpi-average">
        <div class="analytics-kpi-top"><span>Expected income</span></div>
        <strong>GH₵<?php echo number_format($data['expected'], 2); ?></strong>
        <small>From appointments still booked</small>
    </article>

    <article class="analytics-kpi kpi-customers">
        <div class="analytics-kpi-top"><span>Clients</span></div>
        <strong><?php echo number_format($data['clients']); ?></strong>
        <small><?php echo number_format($data['returning']); ?> booked more than once</small>
    </article>
</div>

<div class="analytics-secondary-stats">
    <div><span>Completed</span><strong><?php echo number_format($data['completed']); ?></strong></div>
    <div><span>No-shows</span><strong><?php echo number_format($data['no_show']); ?></strong></div>
    <div><span>Attendance rate</span><strong><?php echo $completionRate; ?>%</strong></div>
    <div><span>Cancelled</span><strong><?php echo number_format($data['cancelled']); ?> (<?php echo $cancelRate; ?>%)</strong></div>
</div>

<div class="analytics-chart-grid">

    <section class="analytics-panel analytics-panel-wide">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">BOOKINGS</span>
                <h2>Appointments over time</h2>
            </div>
            <span class="analytics-chart-note">Appointments per day, cancellations excluded</span>
        </div>
        <div class="analytics-chart-wrap"><canvas id="dayChart"></canvas></div>
        <?php if (!$dayCounts): ?><p class="analytics-empty">No appointments in this period.</p><?php endif; ?>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">OUTCOMES</span>
                <h2>Appointment status</h2>
            </div>
        </div>
        <div class="analytics-chart-wrap analytics-doughnut-wrap"><canvas id="statusChart"></canvas></div>
        <div class="analytics-legend">
            <span><i class="legend-dot consultant-dot-booked"></i>Booked</span>
            <span><i class="legend-dot dot-paid"></i>Completed</span>
            <span><i class="legend-dot dot-pending"></i>No-show</span>
            <span><i class="legend-dot dot-failed"></i>Cancelled</span>
        </div>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">SERVICES</span>
                <h2>Bookings by consultation</h2>
            </div>
        </div>
        <div class="analytics-chart-wrap"><canvas id="serviceChart"></canvas></div>
        <?php if (!$data['by_service']): ?><p class="analytics-empty">No bookings in this period.</p><?php endif; ?>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">FORMAT</span>
                <h2>In person and video call</h2>
            </div>
        </div>
        <div class="analytics-chart-wrap analytics-doughnut-wrap"><canvas id="formatChart"></canvas></div>
        <div class="analytics-legend">
            <span><i class="legend-dot consultant-dot-person"></i>In person</span>
            <span><i class="legend-dot consultant-dot-video"></i>Video call</span>
        </div>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">INCOME</span>
                <h2>Income by consultation</h2>
            </div>
            <span class="analytics-chart-note">Completed only, GH₵</span>
        </div>
        <div class="analytics-chart-wrap"><canvas id="incomeChart"></canvas></div>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">DEMAND</span>
                <h2>Busiest days</h2>
            </div>
        </div>
        <div class="analytics-chart-wrap"><canvas id="weekdayChart"></canvas></div>
    </section>

    <section class="analytics-panel">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">DEMAND</span>
                <h2>Most booked times</h2>
            </div>
        </div>
        <div class="analytics-chart-wrap"><canvas id="hourChart"></canvas></div>
        <?php if (!$data['by_hour']): ?><p class="analytics-empty">No bookings in this period.</p><?php endif; ?>
    </section>

    <section class="analytics-panel analytics-panel-wide">
        <div class="analytics-panel-heading">
            <div>
                <span class="analytics-eyebrow">CLIENTS</span>
                <h2>Main hair concerns</h2>
            </div>
            <span class="analytics-chart-note">As chosen by clients when booking</span>
        </div>
        <div class="analytics-chart-wrap"><canvas id="concernChart"></canvas></div>
        <?php if (!$data['by_concern']): ?><p class="analytics-empty">No bookings in this period.</p><?php endif; ?>
    </section>

</div>

<div class="analytics-data-note">
    <strong>How to read these figures</strong>
    <p>
        All figures use the appointment date, not the date the booking was made.
        Income received counts completed consultations at the price the client booked at.
        The attendance rate is completed appointments out of completed and no-show appointments.
        Cancelled appointments are left out of the charts.
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    var palette = ['#A84E2B', '#7A8B69', '#D9A441', '#6D7FA8', '#B87991', '#6B9C91', '#9A7BB5', '#C98B5E'];
    var gridColor = 'rgba(74, 44, 33, 0.08)';

    Chart.defaults.font.family = "'Nunito Sans', sans-serif";
    Chart.defaults.color = '#6F625A';

    var money = function (v) {
        return 'GH₵' + Number(v).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    var noLegend = { legend: { display: false } };
    var countAxis = { beginAtZero: true, grid: { color: gridColor }, ticks: { precision: 0 } };

    function bar(id, labels, values, options) {
        options = options || {};
        new Chart(document.getElementById(id), {
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
        new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0, hoverOffset: 7 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: noLegend }
        });
    }

    /* Appointments over time */
    new Chart(document.getElementById('dayChart'), {
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
