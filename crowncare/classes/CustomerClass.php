
<?php

require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends Database
{
    public function emailExists($email)
    {
        $sql = "SELECT customer_email
                FROM customer
                WHERE customer_email = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }


    public function addCustomer($name, $email, $pass, $country, $city, $contact)
    {
        // Hash the password before storing it
        $hashedPassword = password_hash($pass, PASSWORD_BCRYPT);

        // Customer image is optional
        $customerImage = null;

        // 2 = customer
        $userRole = 2;

        $sql = "INSERT INTO customer
                (
                    customer_name,
                    customer_email,
                    customer_pass,
                    customer_country,
                    customer_city,
                    customer_contact,
                    customer_image,
                    user_role
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "sssssssi",
            $name,
            $email,
            $hashedPassword,
            $country,
            $city,
            $contact,
            $customerImage,
            $userRole
        );

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }

        return false;
    }


public function getCustomerById($customerId)
{
    $sql = "SELECT *
            FROM customer
            WHERE customer_id = ?";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param("i", $customerId);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }

    return false;
}


public function getCustomerByEmail($email){

    $sql="Select * from customer where customer_email= ?";

    $stmt= $this->conn->prepare($sql);

    $stmt->bind_param("s",$email);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1){
         return $result->fetch_assoc();
        
    }

    return false;
}



public function login($email,$pass){

    $customer= $this->getCustomerByEmail($email);

    if($customer === false){
        return false;
    }

    if(password_verify($pass,$customer["customer_pass"])){
        return $customer;
    }

    return false;


}


public function updateCustomer(
    $customerId,
    $name,
    $email,
    $country,
    $city,
    $contact
) {
    $sql = "UPDATE customer
            SET customer_name = ?,
                customer_email = ?,
                customer_country = ?,
                customer_city = ?,
                customer_contact = ?
            WHERE customer_id = ?";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
        "sssssi",
        $name,
        $email,
        $country,
        $city,
        $contact,
        $customerId
    );

    if ($stmt->execute()) {
        return true;
    }

    return false;
}


public function changePassword($customerId, $currentPassword, $newPassword)
{
    $sql = "SELECT customer_pass
            FROM customer
            WHERE customer_id = ?";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param("i", $customerId);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        return false;
    }

    $customer = $result->fetch_assoc();

    // Check the current password
    if (!password_verify($currentPassword, $customer['customer_pass'])) {
        return false;
    }

    // Hash the new password
    $hashedPassword = password_hash(
        $newPassword,
        PASSWORD_BCRYPT
    );

    $sql = "UPDATE customer
            SET customer_pass = ?
            WHERE customer_id = ?";

    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param(
        "si",
        $hashedPassword,
        $customerId
    );

    return $stmt->execute();
}


public function deleteCustomer($customerId){

    $sql= "Delete from customer where customer_id= ?";

    $stmt= $this->conn->prepare($sql);

    $stmt->bind_param("i",$customerId);

    return $stmt->execute();
}

}
