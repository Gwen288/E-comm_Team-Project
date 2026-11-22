<?php

require_once __DIR__ . '/../classes/CustomerClass.php';

/*
 * Thin wrapper around CustomerClass for the account pages and the
 * register/login/password-reset actions. Results are passed through as they
 * come back from the model.
 */
class CustomerController
{
    private $customer;

    public function __construct()
    {
        $this->customer = new CustomerClass();
    }

    /**
     * New account. Returns ['success' => true, 'customer' => row] or
     * ['success' => false, 'error' => message]. The message for an email that
     * is already used does not say so, so nobody can check who has an account.
     */
    public function register($data)
    {
        if ($this->customer->emailExists($data['email'])) {
            return [
                'success' => false,
                'error' => 'We could not create an account with these details. If you already have an account, sign in or reset your password.',
            ];
        }

        $id = $this->customer->addCustomer(
            $data['name'],
            $data['email'],
            $data['pass'],
            $data['city'],
            $data['contact']
        );

        if (!$id) {
            return ['success' => false, 'error' => 'We could not create your account. Please try again.'];
        }

        return ['success' => true, 'customer' => $this->customer->getCustomerById($id)];
    }

    /** The customer row, or false. Wrong email and wrong password give the same result. */
    public function login($email, $pass)
    {
        return $this->customer->login($email, $pass);
    }

    /* ---------- Sign-in attempts ---------- */

    /** Checked before the password; login_action.php passes the visitor's IP. */
    public function tooManyFailedLogins($email, $ip)
    {
        return $this->customer->tooManyFailedLogins($email, $ip);
    }

    public function recordFailedLogin($email, $ip)
    {
        $this->customer->recordFailedLogin($email, $ip);
    }

    public function clearFailedLogins($email)
    {
        $this->customer->clearFailedLogins($email);
    }

    public function getCustomer($customerId)
    {
        return $this->customer->getCustomerById($customerId);
    }

    public function getCustomerByEmail($email)
    {
        return $this->customer->getCustomerByEmail($email);
    }

    public function getAllCustomers()
    {
        return $this->customer->getAllCustomers();
    }

    public function getCustomerCount()
    {
        return $this->customer->getCustomerCount();
    }

    /** Email check for the edit-account form; the customer's own ID is ignored. */
    public function emailTaken($email, $exceptCustomerId)
    {
        return $this->customer->emailExists($email, $exceptCustomerId);
    }

    public function updateAccount($customerId, $data)
    {
        return $this->customer->updateCustomer(
            $customerId,
            $data['name'],
            $data['email'],
            $data['city'],
            $data['contact']
        );
    }

    public function passwordMatches($customerId, $password)
    {
        return $this->customer->passwordMatches($customerId, $password);
    }

    public function setPassword($customerId, $newPassword)
    {
        return $this->customer->setPassword($customerId, $newPassword);
    }

    /* ---------- Password reset ---------- */

    /** Returns the raw token to email; the model stores only its hash. */
    public function createPasswordReset($customerId)
    {
        return $this->customer->createPasswordReset($customerId);
    }

    /** Returns a customer only if the token is unused and not expired. */
    public function findPasswordReset($token)
    {
        return $this->customer->findPasswordReset($token);
    }

    /** Sets the new password and marks the reset token as used. */
    public function usePasswordReset($resetId, $customerId, $newPassword)
    {
        return $this->customer->usePasswordReset($resetId, $customerId, $newPassword);
    }

    /* ---------- Closing an account ---------- */

    /** Checked by delete_account_action.php before the account can be closed. */
    public function countOrdersInProgress($customerId)
    {
        return $this->customer->countOrdersInProgress($customerId);
    }

    /** Only called when no orders are still on their way. */
    public function closeAccount($customerId)
    {
        return $this->customer->closeAccount($customerId);
    }
}
