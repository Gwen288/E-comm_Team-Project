<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/ConsultationClass.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

/*
 * Book a hair consultation with a CrownCare hair specialist.
 * Saves through actions/book_consultation_action.php.
 */

$consultations = new ConsultationClass();

$firstDate = $consultations->firstBookableDate();
$lastDate = $consultations->lastBookableDate();

// Booked times for the whole booking window, so the page can grey them out.
$booked = $consultations->bookedSlots($firstDate, $lastDate);

if (empty($_SESSION['booking_token'])) {
    $_SESSION['booking_token'] = bin2hex(random_bytes(16));
}

$errors = $_SESSION['booking_errors'] ?? [];
$old = $_SESSION['booking_old'] ?? [];
unset($_SESSION['booking_errors'], $_SESSION['booking_old']);

// Pre-fill the details of a logged-in customer.
if (!$old && is_logged_in() && !is_admin()) {
    $customer = (new CustomerController())->getCustomer(get_customer_id());
    if ($customer) {
        $old = [
            'full_name' => $customer['customer_name'] ?? '',
            'email' => $customer['customer_email'] ?? '',
            'phone' => $customer['customer_contact'] ?? '',
        ];
    }
}

$value = function ($key, $default = '') use ($old) {
    return htmlspecialchars((string) ($old[$key] ?? $default), ENT_QUOTES, 'UTF-8');
};

$selectedService = $old['service'] ?? '';
$selectedFormat = $old['format'] ?? 'in_person';
$selectedDate = $old['date'] ?? '';
$selectedTime = $old['time'] ?? '';
$services = $consultations->services();
$chosen = $services[$selectedService] ?? null;

$concerns = ['Dryness', 'Breakage', 'Frizz', 'Tangles and knots', 'Itchy or flaky scalp', 'Thinning or weak edges', 'Product buildup', 'Something else'];

// Line icons for the three consultation cards.
$serviceIcons = [
    'assessment' => '<svg viewBox="0 0 48 48" width="44" height="44" aria-hidden="true"><circle cx="21" cy="21" r="12" fill="none" stroke="currentColor" stroke-width="2.6"/><path d="M30 30l10 10" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/></svg>',
    'routine' => '<svg viewBox="0 0 48 48" width="44" height="44" aria-hidden="true"><rect x="9" y="10" width="30" height="30" rx="4" fill="none" stroke="currentColor" stroke-width="2.6"/><path d="M9 19h30M17 6v8M31 6v8M16 27l4 4 8-8" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    'protective' => '<svg viewBox="0 0 48 48" width="44" height="44" aria-hidden="true"><path d="M24 6l14 5v11c0 9.5-6 16.5-14 20-8-3.5-14-10.5-14-20V11z" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linejoin="round"/><path d="M18 24l4 4 8-8" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
];

$photoFile = 'images/consultation/specialist.jpg';
$photo = is_file(__DIR__ . '/../' . $photoFile) ? BASE_URL . $photoFile : '';

$availability = [
    'first' => $firstDate,
    'last' => $lastDate,
    'hours' => (object) $consultations->weeklyHours(),
    'special' => (object) $consultations->specialDates($firstDate, $lastDate),
    'booked' => (object) $booked,
];

$pageTitle = 'Book a hair consultation';
$pageDescription = 'Book a hair and scalp assessment, routine planning or protective style consultation with a CrownCare specialist, in person in Accra or by video call.';
require_once __DIR__ . '/layout/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/consultation.css?v=20261118b">

<main class="booking-page">
    <div class="cc-booking-nav">
        <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>
    </div>

    <!-- INTRODUCTION -->
    <section class="landing-shell booking-intro">
        <div class="booking-intro-copy">
            <p class="eyebrow">Hair consultation</p>
            <h1>Book a consultation with a hair specialist</h1>
            <p class="booking-lead">
                Get one-to-one advice on your hair and scalp. A CrownCare hair specialist
                looks at your hair, talks through your goals and gives you a routine you can follow at home.
            </p>
        </div>

        <div class="booking-intro-photo">
            <?php if ($photo): ?>
                <img src="<?php echo $photo; ?>" alt="CrownCare hair specialist">
            <?php else: ?>
                <div class="booking-photo-placeholder" aria-hidden="true">
                    <svg viewBox="0 0 64 64" width="64" height="64"><circle cx="32" cy="22" r="11" fill="none" stroke="currentColor" stroke-width="2.5"/><path d="M12 56c3-11 10.5-17 20-17s17 6 20 17" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                </div>
            <?php endif; ?>
            <div class="hair-image-label">Advice. Routine. Results.</div>
        </div>
    </section>

    <!-- CONSULTATION CARDS AND BOOKING FORM -->
    <section class="landing-shell booking-layout" id="booking-form">

        <form
            class="booking-form"
            id="consultation-form"
            method="POST"
            action="<?php echo BASE_URL; ?>actions/book_consultation_action.php"
            novalidate
        >
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['booking_token']); ?>">

            <?php if ($errors): ?>
                <div class="booking-errors" role="alert">
                    <strong>Please check the following:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <fieldset class="booking-services" data-services>
                <legend>Choose your consultation</legend>
                <div class="booking-cards">
                    <?php foreach ($services as $key => $service): ?>
                        <label class="booking-card">
                            <input
                                type="radio"
                                name="service"
                                value="<?php echo $key; ?>"
                                data-name="<?php echo htmlspecialchars($service['name']); ?>"
                                data-minutes="<?php echo (int) $service['minutes']; ?>"
                                data-price="<?php echo (float) $service['price']; ?>"
                                <?php echo $selectedService === $key ? 'checked' : ''; ?>
                                required
                            >
                            <span class="booking-card-body">
                                <span class="booking-card-icon"><?php echo $serviceIcons[$key] ?? ''; ?></span>
                                <strong class="booking-card-title"><?php echo htmlspecialchars($service['name']); ?></strong>
                                <span class="booking-card-text"><?php echo htmlspecialchars($service['description']); ?></span>
                                <span class="booking-card-meta">
                                    <?php echo (int) $service['minutes']; ?> minutes
                                    <span class="booking-card-price">GH₵<?php echo number_format($service['price'], 2); ?></span>
                                </span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <!-- Opens when a consultation card is chosen. -->
            <div class="booking-details" data-details>

                <div class="booking-details-head">
                    <h2 data-show="title"><?php echo $chosen ? 'Book ' . htmlspecialchars($chosen['name']) : 'Your booking'; ?></h2>
                    <p data-show="meta"><?php echo $chosen ? (int) $chosen['minutes'] . ' minutes, paid online when you book' : ''; ?></p>
                </div>

                <div class="booking-panel">

                <fieldset class="booking-group">
                    <legend class="booking-label">How would you like to meet?</legend>
                    <div class="booking-options booking-options--two">
                        <?php foreach (ConsultationClass::FORMATS as $key => $format): ?>
                            <label class="booking-option">
                                <input
                                    type="radio"
                                    name="format"
                                    value="<?php echo $key; ?>"
                                    <?php echo $selectedFormat === $key ? 'checked' : ''; ?>
                                    required
                                >
                                <span class="booking-option-body">
                                    <strong><?php echo htmlspecialchars($format['name']); ?></strong>
                                    <span class="booking-option-text"><?php echo htmlspecialchars($format['detail']); ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div class="booking-grid">
                    <div class="booking-field booking-field--wide booking-date-field">
                        <label for="booking-date">Date</label>
                        <input
                            type="date"
                            id="booking-date"
                            name="date"
                            min="<?php echo $firstDate; ?>"
                            max="<?php echo $lastDate; ?>"
                            value="<?php echo htmlspecialchars($selectedDate); ?>"
                            required
                        >
                        <small>Times are in Ghana time (GMT).</small>
                    </div>

                    <div class="booking-field booking-field--wide">
                        <span class="booking-label" id="booking-time-label">Available times</span>
                        <div class="booking-slots" role="radiogroup" aria-labelledby="booking-time-label" data-slots data-selected="<?php echo htmlspecialchars($selectedTime); ?>">
                            <p class="booking-slots-note">Choose a date to see the available times.</p>
                        </div>
                        <noscript><p class="booking-slots-note">Please turn on JavaScript in your browser to see the available times.</p></noscript>
                    </div>

                    <div class="booking-field booking-field--wide">
                        <label for="booking-name">Full name</label>
                        <input type="text" id="booking-name" name="full_name" value="<?php echo $value('full_name'); ?>" autocomplete="name" maxlength="100" required>
                    </div>
                    <div class="booking-field">
                        <label for="booking-email">Email</label>
                        <input type="email" id="booking-email" name="email" value="<?php echo $value('email'); ?>" autocomplete="email" maxlength="100" required>
                    </div>
                    <div class="booking-field">
                        <label for="booking-phone">Phone number</label>
                        <input type="tel" id="booking-phone" name="phone" value="<?php echo $value('phone'); ?>" autocomplete="tel" placeholder="024 123 4567" maxlength="20" required>
                    </div>
                    <div class="booking-field booking-field--wide">
                        <label for="booking-concern">Main concern <span class="booking-optional">(optional)</span></label>
                        <select id="booking-concern" name="main_concern">
                            <option value="">Choose your main concern</option>
                            <?php foreach ($concerns as $concern): ?>
                                <option value="<?php echo $concern; ?>" <?php echo ($old['main_concern'] ?? '') === $concern ? 'selected' : ''; ?>><?php echo $concern; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="booking-field booking-field--wide">
                        <label for="booking-notes">Anything we should know? <span class="booking-optional">(optional)</span></label>
                        <textarea id="booking-notes" name="notes" rows="4" maxlength="500" placeholder="For example: recent treatments, products you use, or what you would like to achieve."><?php echo $value('notes'); ?></textarea>
                    </div>
                </div>

                <div class="booking-confirm-row">
                    <div class="booking-total">
                        <span>To pay now</span>
                        <strong data-show="price"><?php echo $chosen ? 'GH₵' . number_format($chosen['price'], 2) : ''; ?></strong>
                    </div>
                    <button type="submit" class="booking-submit">Continue to payment</button>
                    <p class="booking-note">
                        You pay securely with mobile money or card through Paystack, and your time is held for
                        <?php echo (int) ConsultationClass::HOLD_MINUTES; ?> minutes while you pay.
                    </p>
                    <p class="booking-note">
                        <strong>Cancelling:</strong> use the link in your confirmation email, or My consultations if you have an account.
                        Cancel <?php echo (int) ConsultationClass::FULL_REFUND_HOURS; ?> hours or more before for a full refund.
                        With less notice we refund <?php echo 100 - ConsultationClass::LATE_CANCEL_FEE_PERCENT; ?>%.
                        If you do not come, the payment is not refunded. Refunds take 3 to 5 working days.
                    </p>
                </div>

                </div>
            </div>
        </form>

    </section>
</main>

<script type="application/json" id="booking-availability"><?php echo json_encode($availability, JSON_HEX_TAG); ?></script>

<script>
(function () {
    var form = document.getElementById('consultation-form');
    var data = JSON.parse(document.getElementById('booking-availability').textContent);
    var dateInput = document.getElementById('booking-date');
    var slotsBox = form.querySelector('[data-slots]');
    var servicesBox = form.querySelector('[data-services]');
    var details = form.querySelector('[data-details]');
    var show = {};
    form.querySelectorAll('[data-show]').forEach(function (el) { show[el.dataset.show] = el; });

    function checked(name) {
        return form.querySelector('input[name="' + name + '"]:checked');
    }

    function niceDate(value) {
        var d = new Date(value + 'T12:00:00');
        return d.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }

    function niceTime(t) {
        var h = parseInt(t.slice(0, 2), 10);
        return (h % 12 || 12) + ':' + t.slice(3) + (h < 12 ? ' am' : ' pm');
    }

    /* Open the form for the chosen consultation card. */
    function showService(scroll) {
        var service = checked('service');
        details.hidden = !service;
        if (!service) return;

        show.title.textContent = 'Book ' + service.dataset.name;
        show.meta.textContent = service.dataset.minutes + ' minutes, paid online when you book';
        show.price.textContent = 'GH₵' + Number(service.dataset.price).toFixed(2);
        servicesBox.classList.remove('is-invalid');

        if (scroll) {
            details.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /* Show the free times for the chosen date. */
    function renderSlots() {
        var date = dateInput.value;
        var selected = slotsBox.dataset.selected;
        slotsBox.innerHTML = '';

        if (!date) {
            slotsBox.innerHTML = '<p class="booking-slots-note">Choose a date to see the available times.</p>';
            return;
        }
        if (date < data.first || date > data.last) {
            slotsBox.innerHTML = '<p class="booking-slots-note">Please choose a date between tomorrow and ' + niceDate(data.last) + '.</p>';
            return;
        }

        // A specific date set by a consultant replaces the usual weekly hours.
        var special = data.special[date];
        var weekday = new Date(date + 'T12:00:00').getDay() || 7;
        var hours = special ? (special.closed ? [] : special.times) : (data.hours[weekday] || []);
        var taken = data.booked[date] || [];

        if (!hours.length) {
            slotsBox.innerHTML = '<p class="booking-slots-note">We are closed on this day. Please choose another day.</p>';
            return;
        }

        var free = 0;
        hours.forEach(function (t) {
            var isTaken = taken.indexOf(t) !== -1;
            var label = document.createElement('label');
            label.className = 'booking-slot' + (isTaken ? ' is-taken' : '');
            label.innerHTML = '<input type="radio" name="time" value="' + t + '"' + (isTaken ? ' disabled' : '') + (t === selected && !isTaken ? ' checked' : '') + '><span>' + niceTime(t) + '</span>';
            slotsBox.appendChild(label);
            if (!isTaken) free++;
        });

        if (!free) {
            slotsBox.insertAdjacentHTML('beforeend', '<p class="booking-slots-note">This day is fully booked. Please choose another day.</p>');
        }
    }

    form.querySelectorAll('input[name="service"]').forEach(function (input) {
        input.addEventListener('change', function () {
            // Scroll down only the first time the form opens.
            showService(details.hidden);
        });
    });

    dateInput.addEventListener('change', function () {
        slotsBox.dataset.selected = '';
        renderSlots();
    });

    /* Clear the red outline as soon as the customer fixes a field. */
    function clearProblem(e) {
        var el = e.target.name === 'time' ? slotsBox : e.target;
        el.classList.remove('is-invalid');
    }
    form.addEventListener('input', clearProblem);
    form.addEventListener('change', clearProblem);

    /* Check the form before sending, with a clear message at the first problem. */
    form.addEventListener('submit', function (e) {
        // Checked from the bottom up, so the first problem on the page wins.
        var problem = null;
        ['booking-phone', 'booking-email', 'booking-name'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el.checkValidity()) problem = el;
        });
        if (!checked('time')) problem = dateInput.value ? slotsBox : dateInput;
        if (!checked('service')) problem = servicesBox;

        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        if (problem) {
            e.preventDefault();
            problem.classList.add('is-invalid');
            problem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (problem.focus && problem !== slotsBox && problem !== servicesBox) problem.focus({ preventScroll: true });
        }
    });

    showService(false);
    renderSlots();
})();
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
