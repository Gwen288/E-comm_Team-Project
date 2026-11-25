<?php

require_once __DIR__ . '/../core/db_class.php';

/*
 * Hair consultation bookings.
 *
 * Services, prices, opening hours and specific dates are kept in the database
 * (migration 008) and changed by consultants in views/consultant/.
 * Each booking takes one hourly slot; a slot can only be booked once
 * while the booking is active (the database enforces this too).
 */
class ConsultationClass extends Database
{
    /* Used only until migration 008 has been run. */
    public const DEFAULT_SERVICES = [
        'assessment' => [
            'name' => 'Hair and Scalp Assessment',
            'minutes' => 45,
            'price' => 100,
            'description' => 'A full look at your hair and scalp: hair type, porosity, density and condition, followed by a written care plan.',
        ],
        'routine' => [
            'name' => 'Routine Planning',
            'minutes' => 30,
            'price' => 60,
            'description' => 'Build a wash-day and moisture routine around your hair, your schedule and your budget, with product recommendations.',
        ],
        'protective' => [
            'name' => 'Protective Style Consultation',
            'minutes' => 30,
            'price' => 60,
            'description' => 'Choose protective styles that suit your hair, and learn how to care for your edges and scalp while you wear them.',
        ],
    ];

    public const FORMATS = [
        'in_person' => [
            'name' => 'In person',
            'detail' => 'At the CrownCare studio, Greater Accra',
        ],
        'video' => [
            'name' => 'Video call',
            'detail' => 'We email you the video call link before your consultation',
        ],
    ];

    /* Used only until migration 008 has been run (1 = Monday ... 7 = Sunday). */
    public const DEFAULT_HOURS = [
        1 => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'],
        2 => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'],
        3 => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'],
        4 => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'],
        5 => ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'],
        6 => ['10:00', '11:00', '12:00', '13:00'],
        7 => [],
    ];

    /* Start times a consultant can choose from on the schedule page. */
    public const TIME_OPTIONS = [
        '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00',
        '14:00', '15:00', '16:00', '17:00', '18:00', '19:00',
    ];

    public const WEEKDAYS = [
        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
        5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
    ];

    public const DEFAULT_DAYS_AHEAD = 60;

    /* Durations a consultant can choose. Each appointment fits in one hourly slot. */
    public const DURATION_OPTIONS = [15, 30, 45, 60];

    private $serviceCache = null;
    private $hoursCache = null;

    /* ---------- Small query helpers ---------- */

    /**
     * Runs a query and returns all rows, or false when it fails
     * (for example before migration 008 has been run).
     */
    protected function rows($sql, $types = '', array $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log('Consultation query failed: ' . $this->conn->error);
                return false;
            }
            if ($types !== '') {
                $stmt->bind_param($types, ...$params);
            }
            if (!$stmt->execute()) {
                error_log('Consultation query failed: ' . $stmt->error);
                $stmt->close();
                return false;
            }
            $result = $stmt->get_result();
            $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
            return $rows;
        } catch (mysqli_sql_exception $e) {
            error_log('Consultation query failed: ' . $e->getMessage());
            return false;
        }
    }

    /** Runs a query that changes data. Returns true on success. */
    protected function run($sql, $types = '', array $params = [])
    {
        try {
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log('Consultation update failed: ' . $this->conn->error);
                return false;
            }
            if ($types !== '') {
                $stmt->bind_param($types, ...$params);
            }
            $ok = $stmt->execute();
            if (!$ok) {
                error_log('Consultation update failed: ' . $stmt->error);
            }
            $stmt->close();
            return $ok;
        } catch (mysqli_sql_exception $e) {
            error_log('Consultation update failed: ' . $e->getMessage());
            return false;
        }
    }

    /* ---------- Services and prices ---------- */

    /** All services as ['key' => ['name', 'minutes', 'price', 'description']], in display order. */
    public function services()
    {
        if ($this->serviceCache !== null) {
            return $this->serviceCache;
        }

        $rows = $this->rows(
            "SELECT service_key, service_name, duration_minutes, price, description
             FROM cc_consultation_services
             ORDER BY sort_order, service_name"
        );

        if (!$rows) {
            return $this->serviceCache = self::DEFAULT_SERVICES;
        }

        $services = [];
        foreach ($rows as $row) {
            $services[$row['service_key']] = [
                'name' => $row['service_name'],
                'minutes' => (int) $row['duration_minutes'],
                'price' => (float) $row['price'],
                'description' => $row['description'],
            ];
        }

        return $this->serviceCache = $services;
    }

    /** When each service was last changed: ['key' => 'Y-m-d H:i:s']. */
    public function serviceUpdatedAt()
    {
        $rows = $this->rows("SELECT service_key, updated_at FROM cc_consultation_services");
        return $rows ? array_column($rows, 'updated_at', 'service_key') : [];
    }

    public function getService($key)
    {
        return $this->services()[(string) $key] ?? null;
    }

    public function updateService($key, $price, $minutes)
    {
        $this->serviceCache = null;
        return $this->run(
            "UPDATE cc_consultation_services SET price = ?, duration_minutes = ? WHERE service_key = ?",
            'dis',
            [(float) $price, (int) $minutes, (string) $key]
        );
    }

    /* ---------- Weekly hours ---------- */

    /** Start times by weekday: [1 => ['09:00', ...], ..., 7 => []]. */
    public function weeklyHours()
    {
        if ($this->hoursCache !== null) {
            return $this->hoursCache;
        }

        $rows = $this->rows(
            "SELECT weekday, TIME_FORMAT(start_time, '%H:%i') AS start_time
             FROM cc_consultation_hours
             ORDER BY weekday, start_time"
        );

        if ($rows === false) {
            return $this->hoursCache = self::DEFAULT_HOURS;
        }

        $hours = array_fill_keys(array_keys(self::WEEKDAYS), []);
        foreach ($rows as $row) {
            $hours[(int) $row['weekday']][] = $row['start_time'];
        }

        return $this->hoursCache = $hours;
    }

    /** Replaces the weekly hours. $hours is [weekday => ['09:00', ...]]. */
    public function saveWeeklyHours(array $hours)
    {
        $this->hoursCache = null;

        $this->conn->begin_transaction();
        $ok = $this->run("DELETE FROM cc_consultation_hours");

        foreach ($hours as $weekday => $times) {
            foreach ($times as $time) {
                $ok = $ok && $this->run(
                    "INSERT INTO cc_consultation_hours (weekday, start_time) VALUES (?, ?)",
                    'is',
                    [(int) $weekday, $time . ':00']
                );
            }
        }

        $ok ? $this->conn->commit() : $this->conn->rollback();
        return $ok;
    }

    /* ---------- Specific dates ---------- */

    /** Specific dates between two dates: ['Y-m-d' => ['closed' => bool, 'times' => [...], 'note' => '']]. */
    public function specialDates($from, $to)
    {
        $rows = $this->rows(
            "SELECT special_date, is_closed, start_times, note
             FROM cc_consultation_special_dates
             WHERE special_date BETWEEN ? AND ?
             ORDER BY special_date",
            'ss',
            [$from, $to]
        );

        $dates = [];
        foreach ($rows ?: [] as $row) {
            $dates[$row['special_date']] = [
                'closed' => (bool) $row['is_closed'],
                'times' => $row['start_times'] === '' ? [] : explode(',', $row['start_times']),
                'note' => (string) $row['note'],
            ];
        }

        return $dates;
    }

    public function saveSpecialDate($date, $closed, array $times, $note)
    {
        $closed = $closed ? 1 : 0;
        $list = $closed ? '' : implode(',', $times);

        return $this->run(
            "INSERT INTO cc_consultation_special_dates (special_date, is_closed, start_times, note)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE is_closed = VALUES(is_closed), start_times = VALUES(start_times), note = VALUES(note)",
            'siss',
            [$date, $closed, $list, $note]
        );
    }

    public function deleteSpecialDate($date)
    {
        return $this->run("DELETE FROM cc_consultation_special_dates WHERE special_date = ?", 's', [$date]);
    }

    /* ---------- Booking window ---------- */

    public function daysAhead()
    {
        $rows = $this->rows("SELECT setting_value FROM cc_consultation_settings WHERE setting_key = 'days_ahead'");
        $days = $rows ? (int) $rows[0]['setting_value'] : 0;

        return $days > 0 ? $days : self::DEFAULT_DAYS_AHEAD;
    }

    public function setDaysAhead($days)
    {
        return $this->run(
            "INSERT INTO cc_consultation_settings (setting_key, setting_value) VALUES ('days_ahead', ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            's',
            [(string) (int) $days]
        );
    }

    public function firstBookableDate()
    {
        return date('Y-m-d', strtotime('+1 day'));
    }

    public function lastBookableDate()
    {
        return date('Y-m-d', strtotime('+' . $this->daysAhead() . ' days'));
    }

    /* ---------- Availability ---------- */

    /** Start times offered on a date (ignores bookings), or [] when closed or out of range. */
    public function slotsForDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', (string) $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return [];
        }
        if ($date < $this->firstBookableDate() || $date > $this->lastBookableDate()) {
            return [];
        }

        $special = $this->specialDates($date, $date)[$date] ?? null;
        if ($special) {
            return $special['closed'] ? [] : $special['times'];
        }

        return $this->weeklyHours()[(int) $d->format('N')] ?? [];
    }

    /** Booked start times between two dates, as ['Y-m-d' => ['09:00', ...]]. */
    public function bookedSlots($from, $to)
    {
        $this->releaseExpiredHolds();

        $rows = $this->rows(
            "SELECT consultation_date, TIME_FORMAT(consultation_time, '%H:%i') AS start_time
             FROM cc_consultations
             WHERE consultation_date BETWEEN ? AND ?
               AND status <> 'cancelled'",
            'ss',
            [$from, $to]
        );

        $booked = [];
        foreach ($rows ?: [] as $row) {
            $booked[$row['consultation_date']][] = $row['start_time'];
        }

        return $booked;
    }

    /* ---------- Bookings ---------- */

    /**
     * Saves a booking. $data holds service, format, date, time, full_name, email,
     * phone, main_concern, notes and customer_id (or null).
     * Returns ['reference' => ...] on success or ['error' => message].
     */
    public function book(array $data)
    {
        $service = $this->getService($data['service']);
        if (!$service || !isset(self::FORMATS[$data['format']])) {
            return ['error' => 'Please choose a consultation type and format.'];
        }

        if (!in_array($data['time'], $this->slotsForDate($data['date']), true)) {
            return ['error' => 'Please choose an available date and time.'];
        }

        $this->releaseExpiredHolds();

        $reference = $this->newReference();
        $customerId = $data['customer_id'] ?: null;
        $time = $data['time'] . ':00';
        $minutes = (int) $service['minutes'];
        $price = (float) $service['price'];

        // A paid consultation holds the time while the client pays (see HOLD_MINUTES).
        $status = $price > 0 ? 'awaiting_payment' : 'booked';
        $cancelToken = bin2hex(random_bytes(32));

        $stmt = $this->conn->prepare(
            "INSERT INTO cc_consultations
                (reference, customer_id, service, consultation_format, consultation_date,
                 consultation_time, duration_minutes, price, full_name, email, phone,
                 main_concern, notes, status, cancel_token)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            error_log('Consultation booking error: ' . $this->conn->error);
            return ['error' => 'We could not save your booking. Please try again.'];
        }

        $stmt->bind_param(
            'sissssidsssssss',
            $reference,
            $customerId,
            $service['name'],
            $data['format'],
            $data['date'],
            $time,
            $minutes,
            $price,
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['main_concern'],
            $data['notes'],
            $status,
            $cancelToken
        );

        // Works whether mysqli reports errors as exceptions (PHP 8.1+) or return values (PHP 8.0).
        try {
            $ok = $stmt->execute() && $stmt->affected_rows === 1;
            $errno = $stmt->errno;
            $message = $stmt->error;
        } catch (mysqli_sql_exception $e) {
            $ok = false;
            $errno = (int) $e->getCode();
            $message = $e->getMessage();
        }
        $stmt->close();

        if ($ok) {
            return ['reference' => $reference];
        }

        // 1062 = duplicate key: someone else took this slot a moment ago.
        if ($errno === 1062) {
            return ['error' => 'Sorry, that time has just been booked. Please choose another time.'];
        }

        error_log('Consultation booking error: ' . $message);
        return ['error' => 'We could not save your booking. Please try again.'];
    }

    public function getByReference($reference)
    {
        $rows = $this->rows("SELECT * FROM cc_consultations WHERE reference = ? LIMIT 1", 's', [$reference]);
        return $rows ? $rows[0] : false;
    }

    /** A short, readable reference such as CC-7K4Q2M. */
    private function newReference()
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = 'CC-';
            for ($i = 0; $i < 6; $i++) {
                $code .= $letters[random_int(0, strlen($letters) - 1)];
            }
        } while ($this->getByReference($code));

        return $code;
    }

    /* ---------- Payment ---------- */

    /* Minutes a time is held while the client pays. */
    public const HOLD_MINUTES = 30;

    /**
     * Frees times held by clients who did not finish paying. The booking is
     * kept, marked as cancelled because it was not paid, in case Paystack
     * confirms a late payment.
     */
    public function releaseExpiredHolds()
    {
        return $this->run(
            "UPDATE cc_consultations
             SET status = 'cancelled', cancelled_by = 'unpaid', status_updated_at = NOW()
             WHERE status = 'awaiting_payment' AND created_at < (NOW() - INTERVAL " . (int) self::HOLD_MINUTES . " MINUTE)"
        );
    }

    /** Frees the time straight away when the payment did not go through. */
    public function releaseHold($consultationId)
    {
        return $this->run(
            "UPDATE cc_consultations
             SET status = 'cancelled', cancelled_by = 'unpaid', status_updated_at = NOW()
             WHERE consultation_id = ? AND status = 'awaiting_payment'",
            'i',
            [(int) $consultationId]
        );
    }

    public function setPaymentReference($consultationId, $paymentReference)
    {
        return $this->run(
            "UPDATE cc_consultations SET payment_reference = ? WHERE consultation_id = ?",
            'si',
            [$paymentReference, (int) $consultationId]
        );
    }

    public function getByPaymentReference($paymentReference)
    {
        $rows = $this->rows("SELECT * FROM cc_consultations WHERE payment_reference = ? LIMIT 1", 's', [$paymentReference]);
        return $rows ? $rows[0] : false;
    }

    public function getById($consultationId)
    {
        $rows = $this->rows("SELECT * FROM cc_consultations WHERE consultation_id = ? LIMIT 1", 'i', [(int) $consultationId]);
        return $rows ? $rows[0] : false;
    }

    /**
     * Records a successful payment and confirms the booking. Returns 'new' the
     * first time, 'already' if it was already recorded, 'taken' if the hold had
     * run out and someone else has since booked the time, or false.
     */
    public function markPaid($consultationId, $amount, $paidAt)
    {
        $consultationId = (int) $consultationId;

        try {
            $stmt = $this->conn->prepare(
                "UPDATE cc_consultations
                 SET payment_status = 'paid', amount_paid = ?, paid_at = ?,
                     status = 'booked', cancelled_by = NULL, status_updated_at = NOW()
                 WHERE consultation_id = ? AND payment_status = 'unpaid'
                   AND (status = 'awaiting_payment' OR (status = 'cancelled' AND cancelled_by = 'unpaid'))"
            );
            $stmt->bind_param('dsi', $amount, $paidAt, $consultationId);
            $stmt->execute();
            $changed = $stmt->affected_rows === 1;
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            // 1062: the hold ran out and the time was booked by someone else.
            if ((int) $e->getCode() === 1062) {
                $this->run(
                    "UPDATE cc_consultations SET payment_status = 'paid', amount_paid = ?, paid_at = ?
                     WHERE consultation_id = ? AND payment_status = 'unpaid'",
                    'dsi',
                    [$amount, $paidAt, $consultationId]
                );
                return 'taken';
            }
            error_log('Consultation payment update failed: ' . $e->getMessage());
            return false;
        }

        if ($changed) {
            return 'new';
        }

        $booking = $this->getById($consultationId);
        return $booking && $booking['payment_status'] !== 'unpaid' ? 'already' : false;
    }

    /** Money kept from consultations paid online, after refunds. */
    public function totalIncome()
    {
        $rows = $this->rows(
            "SELECT COALESCE(SUM(amount_paid - refund_amount), 0) AS total
             FROM cc_consultations WHERE payment_status <> 'unpaid'"
        );
        return $rows ? (float) $rows[0]['total'] : 0.0;
    }

    /* ---------- Cancelling and refunds ---------- */

    /* Cancelling at least this many hours before the start gets a full refund. */
    public const FULL_REFUND_HOURS = 24;

    /* Share kept when a client cancels with less notice. */
    public const LATE_CANCEL_FEE_PERCENT = 5;

    public static function startsAt(array $booking)
    {
        return strtotime($booking['consultation_date'] . ' ' . $booking['consultation_time']);
    }

    /** True when the client may still cancel: a confirmed booking that has not started. */
    public static function clientCanCancel(array $booking)
    {
        return ($booking['status'] ?? '') === 'booked' && self::startsAt($booking) > time();
    }

    /**
     * Share of the payment refunded when a booking is cancelled now.
     * Cancelled by the consultant: 100. By the client 24 hours or more
     * before: 100. By the client with less notice: 95.
     */
    public static function refundPercent(array $booking, $cancelledBy)
    {
        if ($cancelledBy !== 'client') {
            return 100;
        }
        $hoursLeft = (self::startsAt($booking) - time()) / 3600;
        return $hoursLeft >= self::FULL_REFUND_HOURS ? 100 : 100 - self::LATE_CANCEL_FEE_PERCENT;
    }

    /** Amount refunded for a cancellation now (0 when nothing was paid online). */
    public static function refundAmount(array $booking, $cancelledBy)
    {
        if (($booking['payment_status'] ?? '') !== 'paid') {
            return 0.0;
        }
        return round((float) $booking['amount_paid'] * self::refundPercent($booking, $cancelledBy) / 100, 2);
    }

    /** Marks a confirmed booking as cancelled. Returns true when it changed. */
    public function markCancelled($consultationId, $cancelledBy)
    {
        $stmt = $this->conn->prepare(
            "UPDATE cc_consultations
             SET status = 'cancelled', cancelled_by = ?, status_updated_at = NOW()
             WHERE consultation_id = ? AND status = 'booked'"
        );
        $id = (int) $consultationId;
        $stmt->bind_param('si', $cancelledBy, $id);
        $stmt->execute();
        $changed = $stmt->affected_rows === 1;
        $stmt->close();

        return $changed;
    }

    /** Records a refund sent to Paystack ($status 'pending' or 'failed'). */
    public function recordRefund($consultationId, $amount, $status, $note)
    {
        $booking = $this->getById($consultationId);
        $full = $booking && round((float) $amount, 2) >= round((float) $booking['amount_paid'], 2);

        return $this->run(
            "UPDATE cc_consultations
             SET refund_amount = ?, refund_status = ?, refund_note = ?, refunded_at = NOW(),
                 payment_status = ?
             WHERE consultation_id = ?",
            'dsssi',
            [$amount, $status, $note, $full ? 'refunded' : 'partly_refunded', (int) $consultationId]
        );
    }

    /** Called from Paystack's refund webhook once the money has been sent back. */
    public function markRefundProcessed($paymentReference)
    {
        return $this->run(
            "UPDATE cc_consultations SET refund_status = 'processed' WHERE payment_reference = ? AND refund_status = 'pending'",
            's',
            [$paymentReference]
        );
    }

    /**
     * The booking, if the person asking may manage it: the right cancel token
     * (from the email), a booking made in this browser session, or their own
     * booking when signed in.
     */
    public function getForManaging($reference, $token = '')
    {
        $booking = $this->getByReference($reference);
        if (!$booking) {
            return false;
        }
        if ($token !== '' && !empty($booking['cancel_token']) && hash_equals($booking['cancel_token'], $token)) {
            return $booking;
        }
        if (in_array($reference, $_SESSION['booking_references'] ?? [], true)) {
            return $booking;
        }
        if (is_logged_in() && (int) $booking['customer_id'] === (int) get_customer_id()) {
            return $booking;
        }
        return false;
    }

    /* ---------- Bookings in the customer's account ---------- */

    /** A customer's bookings: upcoming first (soonest first), then past ones (newest first). */
    public function forCustomer($customerId)
    {
        $rows = $this->rows(
            "SELECT * FROM cc_consultations
             WHERE customer_id = ?
               AND status <> 'awaiting_payment'
               AND NOT (status = 'cancelled' AND cancelled_by = 'unpaid')
             ORDER BY (CONCAT(consultation_date, ' ', consultation_time) >= NOW() AND status = 'booked') DESC,
                      CASE WHEN CONCAT(consultation_date, ' ', consultation_time) >= NOW() AND status = 'booked'
                           THEN CONCAT(consultation_date, ' ', consultation_time) END ASC,
                      consultation_date DESC, consultation_time DESC",
            'i',
            [(int) $customerId]
        );

        return $rows ?: [];
    }
}
