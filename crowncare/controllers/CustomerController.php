<?php

require_once __DIR__ . '/../classes/CustomerClass.php';

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

    public function createPasswordReset($customerId)
    {
        return $this->customer->createPasswordReset($customerId);
    }

    public function findPasswordReset($token)
    {
        return $this->customer->findPasswordReset($token);
    }

    public function usePasswordReset($resetId, $customerId, $newPassword)
    {
        return $this->customer->usePasswordReset($resetId, $customerId, $newPassword);
    }

    public function countOrdersInProgress($customerId)
    {
        return $this->customer->countOrdersInProgress($customerId);
    }

    public function closeAccount($customerId)
    {
        return $this->customer->closeAccount($customerId);
    }
}
