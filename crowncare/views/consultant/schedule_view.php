<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../core/consultant_helpers.php';
require_once __DIR__ . '/../../classes/ConsultantClass.php';

require_consultant();

/*
 * View schedule: every day in a period with its times, where the times come from
 * and how many are booked. Times are set on the Date and time page (schedule_dates.php).
 */

$consultant = new ConsultantClass();
$weeklyHours = $consultant->weeklyHours();
$today = date('Y-m-d');

$validDate = function ($value) {
    $d = DateTime::createFromFormat('Y-m-d', (string) $value);
    return $d && $d->format('Y-m-d') === $value;
};

/* ---------- Schedule overview period (?period=...) ---------- */

$periods = [
    'this_week' => 'This week',
    'next_week' => 'Next week',
    'this_month' => 'This month',
    'next_month' => 'Next month',
    'custom' => 'Choose dates',
];
$period = isset($periods[$_GET['period'] ?? '']) ? $_GET['period'] : 'this_week';
$changedOnly = !empty($_GET['changed']);

switch ($period) {
    case 'next_week':
        $from = date('Y-m-d', strtotime('monday next week'));
        $to = date('Y-m-d', strtotime('sunday next week'));
        break;
    case 'this_month':
        $from = date('Y-m-01');
        $to = date('Y-m-t');
        break;
    case 'next_month':
        $from = date('Y-m-01', strtotime('first day of next month'));
        $to = date('Y-m-t', strtotime('first day of next month'));
        break;
    case 'custom':
        $from = $validDate($_GET['from'] ?? '') ? $_GET['from'] : $today;
        $to = $validDate($_GET['to'] ?? '') ? $_GET['to'] : date('Y-m-d', strtotime('+13 days'));
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        // Keep the list a sensible length.
        if (strtotime($to) - strtotime($from) > 92 * 86400) {
            $to = date('Y-m-d', strtotime($from . ' +92 days'));
        }
        break;
    default:
        $from = date('Y-m-d', strtotime('monday this week'));
        $to = date('Y-m-d', strtotime('sunday this week'));
}

/* ---------- Data ---------- */

$specialDates = $consultant->specialDates($from, $to);
$bookedTimes = $consultant->bookedSlots($from, $to);

/** The hours that apply on a date: times set on the calendar first, otherwise the standard weekly times. */
$hoursOn = function ($date) use ($specialDates, $weeklyHours) {
    if (isset($specialDates[$date])) {
        $s = $specialDates[$date];
        return ['times' => $s['closed'] ? [] : $s['times'], 'custom' => true, 'note' => $s['note']];
    }
    return ['times' => $weeklyHours[(int) date('N', strtotime($date))] ?? [], 'custom' => false, 'note' => ''];
};

// Overview rows.
$overview = [];
for ($d = $from; $d <= $to; $d = date('Y-m-d', strtotime($d . ' +1 day'))) {
    $h = $hoursOn($d);
    if ($changedOnly && !$h['custom']) {
        continue;
    }
    $overview[$d] = $h + ['booked' => $bookedTimes[$d] ?? []];
}

$openDays = count(array_filter($overview, function ($row) { return $row['times']; }));
$totalSlots = array_sum(array_map(function ($row) { return count($row['times']); }, $overview));
$bookedSlots = array_sum(array_map(function ($row) { return count($row['booked']); }, $overview));

$calendarPage = BASE_URL . 'views/consultant/schedule_dates.php';
$query = function (array $changes) use ($period, $from, $to, $changedOnly) {
    $q = array_merge(['period' => $period, 'changed' => $changedOnly ? '1' : ''], $changes);
    if (($q['period'] ?? '') === 'custom') {
        $q += ['from' => $from, 'to' => $to];
    }
    return '?' . http_build_query(array_filter($q, 'strlen'));
};

$pageTitle = 'Your schedule';
require_once __DIR__ . '/../layout/consultant_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Your schedule</h1>
        <p>The times clients can book on each day, and how many are booked.</p>
    </div>
    <a class="admin-btn admin-btn-secondary" href="<?php echo $calendarPage; ?>">Set times</a>
</div>

<!-- SCHEDULE OVERVIEW -->
<section class="admin-card" id="schedule">
    <div class="admin-card-header consultant-card-header">
        <div>
            <h2><?php echo $periods[$period]; ?></h2>
            <p><?php echo cc_date_label($from, 'D j M Y'); ?> to <?php echo cc_date_label($to, 'D j M Y'); ?></p>
        </div>
        <div class="consultant-schedule-totals">
            <span><strong><?php echo $openDays; ?></strong> open <?php echo $openDays === 1 ? 'day' : 'days'; ?></span>
            <span><strong><?php echo $totalSlots; ?></strong> times</span>
            <span><strong><?php echo $bookedSlots; ?></strong> booked</span>
        </div>
    </div>

    <nav class="consultant-tabs" aria-label="Schedule period">
        <?php foreach ($periods as $key => $label): ?>
            <a class="<?php echo $period === $key ? 'is-active' : ''; ?>" href="<?php echo htmlspecialchars($query(['period' => $key])); ?>"><?php echo $label; ?></a>
        <?php endforeach; ?>
    </nav>

    <form class="analytics-filter consultant-filter" method="GET">
        <input type="hidden" name="period" value="<?php echo $period; ?>">
        <?php if ($period === 'custom'): ?>
            <div>
                <label for="from">From</label>
                <input type="date" id="from" name="from" value="<?php echo $from; ?>">
            </div>
            <div>
                <label for="to">To</label>
                <input type="date" id="to" name="to" value="<?php echo $to; ?>">
            </div>
        <?php endif; ?>
        <label class="consultant-radio consultant-check">
            <input type="checkbox" name="changed" value="1" <?php echo $changedOnly ? 'checked' : ''; ?>>
            <span>Only dates set on the calendar</span>
        </label>
        <button type="submit">Apply</button>
    </form>

    <?php if (!$overview): ?>
        <p class="admin-empty"><?php echo $changedOnly ? 'No dates in this period have been set on the calendar.' : 'No dates in this period.'; ?></p>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="admin-table consultant-schedule-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Times</th>
                        <th>Set by</th>
                        <th>Booked</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overview as $date => $row): ?>
                        <?php $past = $date < $today; ?>
                        <tr class="<?php echo $past ? 'is-past' : ''; ?><?php echo $date === $today ? ' is-today' : ''; ?>">
                            <td>
                                <strong><?php echo cc_date_label($date, 'D j M Y'); ?></strong>
                                <?php if ($row['note'] !== ''): ?>
                                    <br><span class="admin-muted"><?php echo htmlspecialchars($row['note']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$row['times']): ?>
                                    <span class="admin-status admin-status-failed">Closed</span>
                                <?php else: ?>
                                    <div class="consultant-time-list">
                                        <?php foreach ($row['times'] as $time): ?>
                                            <span class="<?php echo in_array($time, $row['booked'], true) ? 'is-booked' : ''; ?>"><?php echo cc_time_label($time); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['custom'] ? 'Calendar' : 'Standard times'; ?></td>
                            <td>
                                <?php if ($row['booked']): ?>
                                    <a href="<?php echo BASE_URL; ?>views/consultant/appointments.php?view=all&amp;from=<?php echo $date; ?>&amp;to=<?php echo $date; ?>">
                                        <?php echo count($row['booked']); ?> booked
                                    </a>
                                <?php else: ?>
                                    <span class="admin-muted">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$past): ?>
                                    <div class="admin-actions-links">
                                        <a href="<?php echo htmlspecialchars($calendarPage . '?month=' . substr($date, 0, 7) . '&select=' . $date); ?>">Change</a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="admin-muted consultant-footnote">Booked times are underlined. Appointments already booked on a date are kept if you change its times; contact those clients to rearrange.</p>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layout/consultant_footer.php'; ?>
