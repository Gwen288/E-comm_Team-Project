<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../core/consultant_helpers.php';
require_once __DIR__ . '/../../classes/ConsultantClass.php';

require_consultant();

$consultant = new ConsultantClass();
$weeklyHours = $consultant->weeklyHours();

$today = date('Y-m-d');
$thisMonth = date('Y-m');
$lastMonth = date('Y-m', strtotime('+12 months'));

$validDate = function ($value) {
    $d = DateTime::createFromFormat('Y-m-d', (string) $value);
    return $d && $d->format('Y-m-d') === $value;
};

/* ---------- Calendar month (?month=YYYY-MM) ---------- */

$month = (string) ($_GET['month'] ?? $thisMonth);
if (!preg_match('/^\d{4}-\d{2}$/', $month) || $month < $thisMonth || $month > $lastMonth) {
    $month = $thisMonth;
}

$monthStart = $month . '-01';
$monthEnd = date('Y-m-t', strtotime($monthStart));
$prevMonth = date('Y-m', strtotime($monthStart . ' -1 month'));
$nextMonth = date('Y-m', strtotime($monthStart . ' +1 month'));

// Dates to select when the page opens (?select=Y-m-d,Y-m-d), for example after saving.
$preselect = array_values(array_filter(explode(',', (string) ($_GET['select'] ?? '')), function ($d) use ($validDate, $today) {
    return $validDate($d) && $d >= $today;
}));

/* ---------- Data for the calendar ---------- */

$specialDates = $consultant->specialDates($monthStart, $monthEnd);
$bookedTimes = $consultant->bookedSlots($monthStart, $monthEnd);

/** The hours that apply on a date: times set on the calendar first, otherwise the standard weekly times. */
$hoursOn = function ($date) use ($specialDates, $weeklyHours) {
    if (isset($specialDates[$date])) {
        $s = $specialDates[$date];
        return ['times' => $s['closed'] ? [] : $s['times'], 'custom' => true, 'note' => $s['note']];
    }
    return ['times' => $weeklyHours[(int) date('N', strtotime($date))] ?? [], 'custom' => false, 'note' => ''];
};

// Calendar cells, starting on the Monday on or before the 1st.
$gridStart = date('Y-m-d', strtotime($monthStart . ' -' . (date('N', strtotime($monthStart)) - 1) . ' days'));
$gridEnd = date('Y-m-d', strtotime($monthEnd . ' +' . (7 - date('N', strtotime($monthEnd))) . ' days'));

$calendarData = [];
for ($d = $monthStart; $d <= $monthEnd; $d = date('Y-m-d', strtotime($d . ' +1 day'))) {
    $h = $hoursOn($d);
    $calendarData[$d] = [
        'times' => $h['times'],
        'custom' => $h['custom'],
        'note' => $h['note'],
        'booked' => $bookedTimes[$d] ?? [],
    ];
}

$pageTitle = 'Date and time';
require_once __DIR__ . '/../layout/consultant_header.php';
?>

<div class="admin-page-header">
    <div>
        <h1>Date and time</h1>
        <p>Choose dates on the calendar and set the times clients can book on them.</p>
    </div>
    <a class="admin-btn admin-btn-secondary" href="<?php echo BASE_URL; ?>views/consultant/schedule_view.php">View schedule</a>
</div>

<div class="consultant-calendar-layout">

    <!-- CALENDAR -->
    <section class="admin-card consultant-calendar-card">
        <div class="consultant-calendar-head">
            <?php if ($month > $thisMonth): ?>
                <a class="consultant-month-btn" href="?month=<?php echo $prevMonth; ?>" aria-label="Previous month">
                    <span class="consultant-arrow consultant-arrow-left" aria-hidden="true"></span>
                </a>
            <?php else: ?>
                <span class="consultant-month-btn is-disabled" aria-hidden="true"><span class="consultant-arrow consultant-arrow-left"></span></span>
            <?php endif; ?>

            <h2><?php echo date('F Y', strtotime($monthStart)); ?></h2>

            <?php if ($month < $lastMonth): ?>
                <a class="consultant-month-btn" href="?month=<?php echo $nextMonth; ?>" aria-label="Next month">
                    <span class="consultant-arrow consultant-arrow-right" aria-hidden="true"></span>
                </a>
            <?php else: ?>
                <span class="consultant-month-btn is-disabled" aria-hidden="true"><span class="consultant-arrow consultant-arrow-right"></span></span>
            <?php endif; ?>
        </div>

        <div class="consultant-calendar" role="grid" aria-label="<?php echo date('F Y', strtotime($monthStart)); ?>">
            <?php foreach (ConsultationClass::WEEKDAYS as $dayName): ?>
                <div class="consultant-calendar-weekday" role="columnheader"><?php echo substr($dayName, 0, 3); ?></div>
            <?php endforeach; ?>

            <?php for ($d = $gridStart; $d <= $gridEnd; $d = date('Y-m-d', strtotime($d . ' +1 day'))): ?>
                <?php
                $inMonth = $d >= $monthStart && $d <= $monthEnd;
                if (!$inMonth) {
                    echo '<div class="consultant-day is-outside" aria-hidden="true"></div>';
                    continue;
                }
                $info = $calendarData[$d];
                $isPast = $d < $today;
                $classes = ['consultant-day'];
                if ($isPast) $classes[] = 'is-past';
                if ($d === $today) $classes[] = 'is-today';
                if ($info['custom']) $classes[] = 'is-custom';
                if (!$info['times']) $classes[] = 'is-closed';
                $count = count($info['times']);
                ?>
                <button
                    type="button"
                    class="<?php echo implode(' ', $classes); ?>"
                    data-date="<?php echo $d; ?>"
                    <?php echo $isPast ? 'disabled' : ''; ?>
                    aria-pressed="false"
                    aria-label="<?php echo date('l j F', strtotime($d)) . ', ' . ($count ? $count . ' times' : 'closed'); ?>"
                >
                    <span class="consultant-day-number"><?php echo (int) date('j', strtotime($d)); ?></span>
                    <span class="consultant-day-hours">
                        <?php if ($count): ?>
                            <?php echo $count; ?><span class="consultant-day-word"><?php echo $count === 1 ? ' time' : ' times'; ?></span>
                        <?php else: ?>
                            Closed
                        <?php endif; ?>
                    </span>
                    <?php if ($info['booked']): ?>
                        <span class="consultant-day-booked"><?php echo count($info['booked']); ?><span class="consultant-day-word"> booked</span></span>
                    <?php endif; ?>
                </button>
            <?php endfor; ?>
        </div>

        <div class="consultant-calendar-key">
            <span><i class="consultant-key consultant-key-custom"></i>Set for that date</span>
            <span><i class="consultant-key consultant-key-closed"></i>Closed</span>
            <span><i class="consultant-key consultant-key-selected"></i>Selected</span>
        </div>
    </section>

    <!-- TIMES FOR THE SELECTED DATES -->
    <section class="admin-card consultant-day-panel">
        <form class="admin-form consultant-form" method="POST" action="<?php echo BASE_URL; ?>actions/consultant_save_date_action.php" data-date-form>
            <?php echo cc_consultant_token_field(); ?>
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <div data-date-inputs></div>

            <div class="admin-card-header">
                <h2 data-panel-title>Choose dates</h2>
                <p data-panel-text>Tap one or more dates on the calendar to set their times.</p>
            </div>

            <div data-panel-body hidden>
                <fieldset class="consultant-fieldset">
                    <legend>Hours</legend>
                    <label class="consultant-radio">
                        <input type="radio" name="mode" value="open" checked>
                        <span>Open at these times</span>
                    </label>
                    <label class="consultant-radio">
                        <input type="radio" name="mode" value="closed">
                        <span>Closed all day</span>
                    </label>
                </fieldset>

                <div class="consultant-chips consultant-panel-chips" data-times>
                    <?php foreach (ConsultationClass::TIME_OPTIONS as $time): ?>
                        <label class="consultant-chip">
                            <input type="checkbox" name="times[]" value="<?php echo $time; ?>">
                            <span><?php echo cc_time_label($time); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <p class="consultant-panel-booked" data-panel-booked hidden></p>

                <div class="form-group">
                    <label for="note">Note <span class="admin-muted">(optional, only consultants see it)</span></label>
                    <input type="text" id="note" name="note" maxlength="150" placeholder="For example: Public holiday">
                </div>

                <div class="consultant-panel-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Save times</button>
                    <button
                        type="submit"
                        class="admin-btn admin-btn-secondary"
                        formaction="<?php echo BASE_URL; ?>actions/consultant_delete_date_action.php"
                        formnovalidate
                        data-reset-button
                    >Use standard times</button>
                    <button type="button" class="consultant-text-btn" data-clear-selection>Clear selection</button>
                </div>
            </div>
        </form>
    </section>

</div>


<script type="application/json" id="calendar-data"><?php echo json_encode((object) $calendarData, JSON_HEX_TAG); ?></script>
<script>
(function () {
    var data = JSON.parse(document.getElementById('calendar-data').textContent);
    var preselect = <?php echo json_encode($preselect); ?>;
    var form = document.querySelector('[data-date-form]');
    var body = form.querySelector('[data-panel-body]');
    var title = form.querySelector('[data-panel-title]');
    var text = form.querySelector('[data-panel-text]');
    var bookedNote = form.querySelector('[data-panel-booked]');
    var dateInputs = form.querySelector('[data-date-inputs]');
    var timesBox = form.querySelector('[data-times]');
    var resetButton = form.querySelector('[data-reset-button]');
    var selected = [];

    function niceTime(t) {
        var h = parseInt(t, 10);
        return (h % 12 || 12) + ':' + t.slice(3) + (h < 12 ? ' am' : ' pm');
    }

    function niceDate(d, long) {
        return new Date(d + 'T12:00:00').toLocaleDateString('en-GB', long
            ? { weekday: 'long', day: 'numeric', month: 'long' }
            : { day: 'numeric', month: 'short' });
    }

    function setMode(mode) {
        form.querySelector('input[name="mode"][value="' + mode + '"]').checked = true;
        timesBox.hidden = mode !== 'open';
    }

    /* Fill the panel with the hours of the first selected date. */
    function render() {
        document.querySelectorAll('.consultant-day[data-date]').forEach(function (cell) {
            var on = selected.indexOf(cell.dataset.date) !== -1;
            cell.classList.toggle('is-selected', on);
            cell.setAttribute('aria-pressed', on ? 'true' : 'false');
        });

        dateInputs.innerHTML = '';
        selected.forEach(function (d) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'dates[]';
            input.value = d;
            dateInputs.appendChild(input);
        });

        if (!selected.length) {
            body.hidden = true;
            title.textContent = 'Choose dates';
            text.textContent = 'Tap one or more dates on the calendar to set their times.';
            return;
        }

        body.hidden = false;
        var sorted = selected.slice().sort();
        var first = data[sorted[0]];

        if (sorted.length === 1) {
            title.textContent = niceDate(sorted[0], true);
            text.textContent = first.custom ? 'These times were set for this date.' : 'This date uses the standard opening times.';
        } else {
            title.textContent = sorted.length + ' dates selected';
            text.textContent = sorted.map(function (d) { return niceDate(d); }).join(', ') + '. The times you save apply to all of them.';
        }

        // Start from the first date's current hours.
        timesBox.querySelectorAll('input').forEach(function (input) {
            input.checked = first.times.indexOf(input.value) !== -1;
        });
        setMode(first.times.length ? 'open' : 'closed');
        form.querySelector('#note').value = sorted.length === 1 ? first.note : '';

        resetButton.hidden = !sorted.some(function (d) { return data[d].custom; });

        var booked = [];
        sorted.forEach(function (d) {
            data[d].booked.forEach(function (t) {
                booked.push((sorted.length > 1 ? niceDate(d) + ' ' : '') + niceTime(t));
            });
        });
        bookedNote.hidden = !booked.length;
        bookedNote.textContent = booked.length ? 'Already booked: ' + booked.join(', ') + '. These appointments stay booked.' : '';
    }

    document.querySelectorAll('.consultant-day[data-date]').forEach(function (cell) {
        cell.addEventListener('click', function () {
            var d = cell.dataset.date;
            var i = selected.indexOf(d);
            if (i === -1) selected.push(d); else selected.splice(i, 1);
            render();
        });
    });

    form.querySelectorAll('input[name="mode"]').forEach(function (radio) {
        radio.addEventListener('change', function () { setMode(radio.value); });
    });

    form.querySelector('[data-clear-selection]').addEventListener('click', function () {
        selected = [];
        render();
    });

    form.addEventListener('submit', function (e) {
        var open = form.querySelector('input[name="mode"]:checked').value === 'open';
        var isReset = e.submitter && e.submitter.hasAttribute('data-reset-button');
        if (!isReset && open && !timesBox.querySelector('input:checked')) {
            e.preventDefault();
            timesBox.classList.add('is-invalid');
            bookedNote.hidden = false;
            bookedNote.textContent = 'Choose at least one time, or choose Closed all day.';
        }
    });

    timesBox.addEventListener('change', function () { timesBox.classList.remove('is-invalid'); });

    preselect.forEach(function (d) { if (data[d]) selected.push(d); });
    render();
})();
</script>


<?php require_once __DIR__ . '/../layout/consultant_footer.php'; ?>
