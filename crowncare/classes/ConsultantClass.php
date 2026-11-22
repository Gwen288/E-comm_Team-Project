<?php

require_once __DIR__ . '/ConsultationClass.php';

/*
 * Queries for the consultant area: appointments, dashboard figures and analytics.
 * Services, prices and hours are handled by ConsultationClass.
 * The views and actions under consultant/ call this class.
 */
class ConsultantClass extends ConsultationClass
{
    public const PAGE_SIZE = 25;

    /* ---------- Appointments ---------- */

    /**
     * Builds the WHERE clause for the appointment list.
     * $filters: view (upcoming|history|all), status, service, search, from, to.
     * It also fills $types and $params, so the count and the list query filter
     * on exactly the same things.
     */
    private function appointmentWhere(array $filters, &$types, array &$params)
    {
        $where = [];
        $now = date('Y-m-d H:i:s');

        $view = $filters['view'] ?? 'all';
        if ($view === 'upcoming') {
            $where[] = "TIMESTAMP(consultation_date, consultation_time) >= ? AND status = 'booked'";
            $types .= 's';
            $params[] = $now;
        } elseif ($view === 'history') {
            $where[] = "(TIMESTAMP(consultation_date, consultation_time) < ? OR status <> 'booked')";
            $types .= 's';
            $params[] = $now;
        }

        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $types .= 's';
            $params[] = $filters['status'];
        }
        if (!empty($filters['service'])) {
            $where[] = 'service = ?';
            $types .= 's';
            $params[] = $filters['service'];
        }
        if (!empty($filters['from'])) {
            $where[] = 'consultation_date >= ?';
            $types .= 's';
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = 'consultation_date <= ?';
            $types .= 's';
            $params[] = $filters['to'];
        }
        if (!empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $where[] = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR reference LIKE ?)';
            $types .= 'ssss';
            array_push($params, $like, $like, $like, $like);
        }

        return $where ? 'WHERE ' . implode(' AND ', $where) : '';
    }

    /** How many appointments match the filters, ignoring pagination. */
    public function countAppointments(array $filters)
    {
        $types = '';
        $params = [];
        $where = $this->appointmentWhere($filters, $types, $params);
        $rows = $this->rows("SELECT COUNT(*) AS total FROM cc_consultations $where", $types, $params);

        return $rows ? (int) $rows[0]['total'] : 0;
    }

    /** One page of the appointment list; uses the same filters as countAppointments(). */
    public function appointments(array $filters, $page = 1)
    {
        $types = '';
        $params = [];
        $where = $this->appointmentWhere($filters, $types, $params);

        // Upcoming: soonest first. History: most recent first.
        $order = ($filters['view'] ?? '') === 'upcoming' ? 'ASC' : 'DESC';
        $offset = max(0, ((int) $page - 1) * self::PAGE_SIZE);

        return $this->rows(
            "SELECT consultation_id, reference, service, consultation_format, consultation_date,
                    consultation_time, duration_minutes, price, full_name, email, phone, status
             FROM cc_consultations
             $where
             ORDER BY consultation_date $order, consultation_time $order
             LIMIT " . self::PAGE_SIZE . " OFFSET $offset",
            $types,
            $params
        ) ?: [];
    }

    /** One appointment by ID, or false. */
    public function getAppointment($id)
    {
        $rows = $this->rows("SELECT * FROM cc_consultations WHERE consultation_id = ? LIMIT 1", 'i', [(int) $id]);
        return $rows ? $rows[0] : false;
    }

    /** Earlier appointments for the same email address, newest first. */
    public function clientHistory($email, $excludeId)
    {
        return $this->rows(
            "SELECT consultation_id, reference, service, consultation_date, consultation_time, status
             FROM cc_consultations
             WHERE email = ? AND consultation_id <> ?
             ORDER BY consultation_date DESC, consultation_time DESC
             LIMIT 10",
            'si',
            [$email, (int) $excludeId]
        ) ?: [];
    }

    /**
     * Changes the status and private notes of an appointment.
     * Returns true, or an error message.
     */
    public function updateAppointment($id, $status, $notes)
    {
        try {
            // Only move status_updated_at when the status actually changed.
            $stmt = $this->conn->prepare(
                "UPDATE cc_consultations
                 SET status_updated_at = IF(status = ?, status_updated_at, NOW()),
                     status = ?, consultant_notes = ?
                 WHERE consultation_id = ?"
            );
            if (!$stmt) {
                error_log('Appointment update failed: ' . $this->conn->error);
                return 'The appointment could not be saved. Please try again.';
            }
            $stmt->bind_param('sssi', $status, $status, $notes, $id);
            $ok = $stmt->execute();
            $errno = $stmt->errno;
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $ok = false;
            $errno = (int) $e->getCode();
        }

        if ($ok) {
            return true;
        }
        if ($errno === 1062) {
            return 'This time has been booked by another client since the appointment was cancelled, so it cannot be booked again.';
        }
        return 'The appointment could not be saved. Please try again.';
    }

    /** Active bookings per date between two dates: ['Y-m-d' => count]. */
    public function bookingCounts($from, $to)
    {
        $rows = $this->rows(
            "SELECT consultation_date, COUNT(*) AS total
             FROM cc_consultations
             WHERE consultation_date BETWEEN ? AND ? AND status = 'booked'
             GROUP BY consultation_date",
            'ss',
            [$from, $to]
        );

        $counts = [];
        foreach ($rows ?: [] as $row) {
            $counts[$row['consultation_date']] = (int) $row['total'];
        }
        return $counts;
    }

    /* ---------- Dashboard ---------- */

    /** Appointments on one date (cancellations left out), in time order. */
    public function appointmentsOn($date)
    {
        return $this->rows(
            "SELECT consultation_id, reference, service, consultation_format, consultation_time,
                    duration_minutes, full_name, phone, main_concern, status
             FROM cc_consultations
             WHERE consultation_date = ? AND status <> 'cancelled'
             ORDER BY consultation_time",
            's',
            [$date]
        ) ?: [];
    }

    /** Booked appointments still to come, soonest first. */
    public function nextAppointments($limit = 8)
    {
        return $this->rows(
            "SELECT consultation_id, reference, service, consultation_format, consultation_date,
                    consultation_time, full_name, price
             FROM cc_consultations
             WHERE status = 'booked' AND consultation_date > ?
             ORDER BY consultation_date, consultation_time
             LIMIT " . (int) $limit,
            's',
            [date('Y-m-d')]
        ) ?: [];
    }

    /** Past appointments still marked as booked: the consultant should record what happened. */
    public function needingUpdate($limit = 8)
    {
        return $this->rows(
            "SELECT consultation_id, reference, service, consultation_date, consultation_time, full_name
             FROM cc_consultations
             WHERE status = 'booked' AND TIMESTAMP(consultation_date, consultation_time) < ?
             ORDER BY consultation_date DESC, consultation_time DESC
             LIMIT " . (int) $limit,
            's',
            [date('Y-m-d H:i:s')]
        ) ?: [];
    }

    /** Counts for the consultant dashboard, including overdue bookings to update. */
    public function dashboardFigures()
    {
        $today = date('Y-m-d');
        $weekEnd = date('Y-m-d', strtotime('+7 days'));
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $rows = $this->rows(
            "SELECT
                SUM(consultation_date = ? AND status <> 'cancelled') AS today,
                SUM(consultation_date > ? AND consultation_date <= ? AND status = 'booked') AS next_week,
                SUM(consultation_date BETWEEN ? AND ? AND status = 'completed') AS month_completed,
                SUM(IF(consultation_date BETWEEN ? AND ? AND status = 'completed', price, 0)) AS month_revenue,
                SUM(status = 'booked' AND TIMESTAMP(consultation_date, consultation_time) < ?) AS needs_update
             FROM cc_consultations",
            'ssssssss',
            [$today, $today, $weekEnd, $monthStart, $monthEnd, $monthStart, $monthEnd, date('Y-m-d H:i:s')]
        );

        $row = $rows ? $rows[0] : [];
        return [
            'today' => (int) ($row['today'] ?? 0),
            'next_week' => (int) ($row['next_week'] ?? 0),
            'month_completed' => (int) ($row['month_completed'] ?? 0),
            'month_revenue' => (float) ($row['month_revenue'] ?? 0),
            'needs_update' => (int) ($row['needs_update'] ?? 0),
        ];
    }

    /* ---------- Analytics ---------- */

    /** Figures for the analytics page, for appointments dated between $from and $to. */
    public function analytics($from, $to)
    {
        $range = [$from, $to];

        $totals = $this->rows(
            "SELECT
                COUNT(*) AS bookings,
                SUM(status = 'completed') AS completed,
                SUM(status = 'cancelled') AS cancelled,
                SUM(status = 'no_show') AS no_show,
                SUM(status = 'booked') AS booked,
                SUM(IF(status = 'completed', price, 0)) AS revenue,
                SUM(IF(status = 'booked', price, 0)) AS expected,
                COUNT(DISTINCT email) AS clients
             FROM cc_consultations
             WHERE consultation_date BETWEEN ? AND ?",
            'ss',
            $range
        );

        // The totals above count every booking; the breakdowns leave out
        // cancellations, so they are not meant to add up to those totals.
        $byDay = $this->rows(
            "SELECT consultation_date AS day, COUNT(*) AS bookings, SUM(status = 'completed') AS completed
             FROM cc_consultations
             WHERE consultation_date BETWEEN ? AND ? AND status <> 'cancelled'
             GROUP BY consultation_date
             ORDER BY consultation_date",
            'ss',
            $range
        );

        $byService = $this->rows(
            "SELECT service, COUNT(*) AS bookings, SUM(IF(status = 'completed', price, 0)) AS revenue
             FROM cc_consultations
             WHERE consultation_date BETWEEN ? AND ? AND status <> 'cancelled'
             GROUP BY service
             ORDER BY bookings DESC",
            'ss',
            $range
        );

        $byFormat = $this->rows(
            "SELECT consultation_format, COUNT(*) AS bookings
             FROM cc_consultations
             WHERE consultation_date BETWEEN ? AND ? AND status <> 'cancelled'
             GROUP BY consultation_format",
            'ss',
            $range
        );

        $byWeekday = $this->rows(
            "SELECT WEEKDAY(consultation_date) + 1 AS weekday, COUNT(*) AS bookings
             FROM cc_consultations
             WHERE consultation_date BETWEEN ? AND ? AND status <> 'cancelled'
             GROUP BY WEEKDAY(consultation_date)",
            'ss',
            $range
        );

        $byHour = $this->rows(
            "SELECT TIME_FORMAT(consultation_time, '%H:%i') AS start_time, COUNT(*) AS bookings
             FROM cc_consultations
             WHERE consultation_date BETWEEN ? AND ? AND status <> 'cancelled'
             GROUP BY consultation_time
             ORDER BY consultation_time",
            'ss',
            $range
        );

        $byConcern = $this->rows(
            "SELECT COALESCE(NULLIF(main_concern, ''), 'Not given') AS concern, COUNT(*) AS bookings
             FROM cc_consultations
             WHERE consultation_date BETWEEN ? AND ? AND status <> 'cancelled'
             GROUP BY concern
             ORDER BY bookings DESC
             LIMIT 8",
            'ss',
            $range
        );

        $returning = $this->rows(
            "SELECT COUNT(*) AS clients FROM (
                SELECT email FROM cc_consultations
                WHERE status <> 'cancelled'
                GROUP BY email
                HAVING COUNT(*) > 1 AND SUM(consultation_date BETWEEN ? AND ?) > 0
             ) repeat_clients",
            'ss',
            $range
        );

        $t = $totals ? $totals[0] : [];
        return [
            'bookings' => (int) ($t['bookings'] ?? 0),
            'completed' => (int) ($t['completed'] ?? 0),
            'cancelled' => (int) ($t['cancelled'] ?? 0),
            'no_show' => (int) ($t['no_show'] ?? 0),
            'booked' => (int) ($t['booked'] ?? 0),
            'revenue' => (float) ($t['revenue'] ?? 0),
            'expected' => (float) ($t['expected'] ?? 0),
            'clients' => (int) ($t['clients'] ?? 0),
            'returning' => $returning ? (int) $returning[0]['clients'] : 0,
            'by_day' => $byDay ?: [],
            'by_service' => $byService ?: [],
            'by_format' => $byFormat ?: [],
            'by_weekday' => $byWeekday ?: [],
            'by_hour' => $byHour ?: [],
            'by_concern' => $byConcern ?: [],
        ];
    }

    /** Records that the consultant (not the client) cancelled. */
    public function markCancelledByConsultant($id)
    {
        return $this->run(
            "UPDATE cc_consultations SET cancelled_by = 'consultant' WHERE consultation_id = ? AND status = 'cancelled'",
            'i',
            [(int) $id]
        );
    }

    /** Tomorrow's booked appointments that have not had a reminder yet. */
    public function remindersDueTomorrow()
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        return $this->rows(
            "SELECT * FROM cc_consultations
             WHERE consultation_date = ? AND status = 'booked' AND reminder_sent_at IS NULL AND email <> ''
             ORDER BY consultation_time",
            's',
            [$tomorrow]
        ) ?: [];
    }

    /** Stamps the booking so the reminder job won't email it twice. */
    public function markReminded($id)
    {
        return $this->run(
            "UPDATE cc_consultations SET reminder_sent_at = NOW() WHERE consultation_id = ?",
            'i',
            [(int) $id]
        );
    }
}
