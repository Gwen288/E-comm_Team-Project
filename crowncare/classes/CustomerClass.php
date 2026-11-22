<?php

require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends Database
{
    /* Failed sign ins allowed in the window below before a wait is required. */
    const MAX_FAILED_LOGINS = 5;
    const LOGIN_WINDOW_MINUTES = 15;

    public function emailExists($email, $exceptCustomerId = 0)
    {
        $stmt = $this->conn->prepare(
            "SELECT customer_id FROM cc_customer WHERE customer_email = ? AND customer_id <> ?"
        );
        $stmt->bind_param("si", $email, $exceptCustomerId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $exists;
    }


    /** New customer account. Country is always Ghana; city holds the Accra area. */
    public function addCustomer($name, $email, $pass, $city, $contact)
    {
        $hashedPassword = password_hash($pass, PASSWORD_BCRYPT);
        $country = 'Ghana';
        $userRole = 2;

        $stmt = $this->conn->prepare(
            "INSERT INTO cc_customer
                (customer_name, customer_email, customer_pass, customer_country,
                 customer_city, customer_contact, customer_image, user_role)
             VALUES (?, ?, ?, ?, ?, ?, NULL, ?)"
        );
        $stmt->bind_param("ssssssi", $name, $email, $hashedPassword, $country, $city, $contact, $userRole);
        $stmt->execute();
        $id = (int) $this->conn->insert_id;
        $stmt->close();

        return $id ?: false;
    }


    public function getCustomerById($customerId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM cc_customer WHERE customer_id = ?");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $customer ?: false;
    }


    public function getCustomerByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM cc_customer WHERE customer_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $customer ?: false;
    }


    /** Returns the customer when the email and password match an open account. */
    public function login($email, $pass)
    {
        $customer = $this->getCustomerByEmail($email);

        // Check a password even when the email is unknown, so the reply takes
        // the same time either way.
        $hash = $customer ? $customer['customer_pass'] : '$2y$10$tKejGTT0nokJy8nKrRxSz.j2qbZypI3chsuMpPnYvzxSmWpLD4T62';
        $matches = password_verify($pass, $hash);

        if (!$customer || !$matches || ($customer['customer_status'] ?? 'active') !== 'active') {
            return false;
        }

        // Keep the stored hash up to date if PHP's default changes.
        if (password_needs_rehash($customer['customer_pass'], PASSWORD_BCRYPT)) {
            $this->setPassword((int) $customer['customer_id'], $pass);
        }

        return $customer;
    }


    /* ---------- Sign in attempts ---------- */

    public function tooManyFailedLogins($email, $ip)
    {
        $since = date('Y-m-d H:i:s', time() - self::LOGIN_WINDOW_MINUTES * 60);

        $stmt = $this->conn->prepare(
            "SELECT
                SUM(email = ?) AS by_email,
                SUM(ip_address = ?) AS by_ip
             FROM cc_login_attempts
             WHERE attempted_at >= ? AND (email = ? OR ip_address = ?)"
        );
        $stmt->bind_param("sssss", $email, $ip, $since, $email, $ip);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return (int) ($row['by_email'] ?? 0) >= self::MAX_FAILED_LOGINS
            || (int) ($row['by_ip'] ?? 0) >= self::MAX_FAILED_LOGINS * 4;
    }

    public function recordFailedLogin($email, $ip)
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare(
            "INSERT INTO cc_login_attempts (email, ip_address, attempted_at) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $email, $ip, $now);
        $stmt->execute();
        $stmt->close();

        // Old attempts are no longer needed.
        $this->conn->query("DELETE FROM cc_login_attempts WHERE attempted_at < NOW() - INTERVAL 1 DAY");
    }

    public function clearFailedLogins($email)
    {
        $stmt = $this->conn->prepare("DELETE FROM cc_login_attempts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    }


    /* ---------- Account details and password ---------- */

    public function updateCustomer($customerId, $name, $email, $city, $contact)
    {
        $stmt = $this->conn->prepare(
            "UPDATE cc_customer
             SET customer_name = ?, customer_email = ?, customer_country = 'Ghana',
                 customer_city = ?, customer_contact = ?
             WHERE customer_id = ?"
        );
        $stmt->bind_param("ssssi", $name, $email, $city, $contact, $customerId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }


    public function passwordMatches($customerId, $password)
    {
        $customer = $this->getCustomerById($customerId);

        return $customer && password_verify((string) $password, $customer['customer_pass']);
    }


    public function setPassword($customerId, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE cc_customer SET customer_pass = ? WHERE customer_id = ?");
        $stmt->bind_param("si", $hash, $customerId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }


    /* ---------- Password reset links ---------- */

    /** Creates a one-hour reset link token. Only its hash is stored. */
    public function createPasswordReset($customerId)
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + 3600);

        // Only the newest link works.
        $stmt = $this->conn->prepare("DELETE FROM cc_password_resets WHERE customer_id = ?");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->conn->prepare(
            "INSERT INTO cc_password_resets (customer_id, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("isss", $customerId, $hash, $expires, $now);
        $stmt->execute();
        $stmt->close();

        return $token;
    }

    /** The customer for a reset token that is unused and not expired, or false. */
    public function findPasswordReset($token)
    {
        if (!preg_match('/^[a-f0-9]{64}$/', (string) $token)) {
            return false;
        }

        $hash = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');

        $stmt = $this->conn->prepare(
            "SELECT r.reset_id, c.customer_id, c.customer_name, c.customer_email
             FROM cc_password_resets r
             INNER JOIN cc_customer c ON c.customer_id = r.customer_id
             WHERE r.token_hash = ? AND r.used_at IS NULL AND r.expires_at > ?
               AND c.customer_status = 'active'"
        );
        $stmt->bind_param("ss", $hash, $now);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: false;
    }

    public function usePasswordReset($resetId, $customerId, $newPassword)
    {
        $now = date('Y-m-d H:i:s');

        $this->conn->begin_transaction();
        try {
            $this->setPassword($customerId, $newPassword);

            $stmt = $this->conn->prepare("UPDATE cc_password_resets SET used_at = ? WHERE reset_id = ?");
            $stmt->bind_param("si", $now, $resetId);
            $stmt->execute();
            $stmt->close();

            $this->clearFailedLoginsFor($customerId);
            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Password reset failed: ' . $e->getMessage());
            return false;
        }
    }

    private function clearFailedLoginsFor($customerId)
    {
        $customer = $this->getCustomerById($customerId);
        if ($customer) {
            $this->clearFailedLogins($customer['customer_email']);
        }
    }


    /* ---------- Closing an account ---------- */

    /** Orders that are paid but not yet delivered or cancelled. */
    public function countOrdersInProgress($customerId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total FROM cc_orders
             WHERE customer_id = ?
               AND order_status IN ('paid', 'processing', 'out_for_delivery')"
        );
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $total = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
        $stmt->close();

        return $total;
    }

    /**
     * Closes an account. Personal details are removed and the person can no
     * longer sign in, but orders and payments stay for the shop's records
     * (with the delivery details removed). Upcoming consultations are cancelled.
     */
    public function closeAccount($customerId)
    {
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $closedEmail = 'closed-' . (int) $customerId . '-' . bin2hex(random_bytes(4)) . '@closed.invalid';
        $unusablePassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);

        $this->conn->begin_transaction();

        try {
            $statements = [
                ["DELETE FROM cc_cart WHERE c_id = ?", "i", [$customerId]],
                ["DELETE FROM cc_hair_profiles WHERE customer_id = ?", "i", [$customerId]],
                ["DELETE FROM cc_password_resets WHERE customer_id = ?", "i", [$customerId]],
                [
                    "UPDATE cc_consultations
                     SET status = 'cancelled', cancelled_by = 'client', status_updated_at = ?
                     WHERE customer_id = ? AND status = 'booked' AND consultation_date >= ?",
                    "sis",
                    [$now, $customerId, $today],
                ],
                [
                    "UPDATE cc_consultations
                     SET full_name = 'Closed account', email = '', phone = '', customer_id = NULL
                     WHERE customer_id = ?",
                    "i",
                    [$customerId],
                ],
                [
                    "UPDATE cc_orders
                     SET delivery_name = 'Closed account', delivery_phone = '',
                         delivery_address = '', delivery_notes = NULL
                     WHERE customer_id = ?",
                    "i",
                    [$customerId],
                ],
                [
                    "UPDATE cc_customer
                     SET customer_name = 'Closed account', customer_email = ?, customer_pass = ?,
                         customer_city = '', customer_contact = '', customer_image = NULL,
                         customer_status = 'closed', closed_at = ?
                     WHERE customer_id = ?",
                    "sssi",
                    [$closedEmail, $unusablePassword, $now, $customerId],
                ],
            ];

            foreach ($statements as [$sql, $types, $values]) {
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
                $stmt->close();
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            error_log('Closing account failed: ' . $e->getMessage());
            return false;
        }
    }


    /* ---------- Admin ---------- */

    /** Customer accounts for the admin list (consultants and admins are not customers). */
    public function getAllCustomers()
    {
        $stmt = $this->conn->prepare(
            "SELECT
                c.customer_id, c.customer_name, c.customer_email, c.customer_country,
                c.customer_city, c.customer_contact, c.user_role, c.customer_status,
                (SELECT COUNT(*) FROM cc_orders o WHERE o.customer_id = c.customer_id) AS order_count,
                (SELECT COUNT(*) FROM cc_orders o
                 WHERE o.customer_id = c.customer_id AND o.payment_status = 'paid') AS paid_order_count
             FROM cc_customer c
             WHERE c.user_role = 2
             ORDER BY c.customer_status = 'closed', c.customer_id DESC"
        );
        $stmt->execute();
        $customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $customers;
    }


    /** Open customer accounts only. */
    public function getCustomerCount()
    {
        $result = $this->conn->query(
            "SELECT COUNT(*) AS total FROM cc_customer WHERE user_role = 2 AND customer_status = 'active'"
        );

        return (int) ($result->fetch_assoc()['total'] ?? 0);
    }
}
