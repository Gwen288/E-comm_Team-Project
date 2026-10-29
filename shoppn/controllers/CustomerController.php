<?php

require_once __DIR__ . "/../classes/CustomerClass.php";

class CustomerController{

    private $customer;

    public function __construct(){

        $this->customer=new CustomerClass();

    }

    public function register($data){

        if ($this->customer->emailExists($data["email"]))

        return[
            "success"=> false,
            "error"=> "Email already registered"
        ];

    


    $added= $this->customer->addCustomer(
        $data["name"],
        $data["email"],
        $data["pass"],
        $data["country"],
        $data["city"],
        $data["contact"]
    );

    if ($added !==false){
        return[
            "success"=> true,
            'customer_id'=>$added,
            "user_role"=> 2
        ];
    }

    //Registeration Failed
    return [
        "success"=> false,
        "error"=> "Registeration failed"
    ];


    }


    public function login($email,$pass){

        $customer = $this->customer->login($email,$pass);

        if($customer !== false){
            return $customer;
        }

        return[
            "success"=> false,
            "error"=> "Invalid email or password"
        ];
    }

    
public function getCustomer($customerId)
{
    return $this->customer->getCustomerById($customerId);
}



public function updateAccount($customerId,$data){

    $updated = $this->customer->updateCustomer(
        $customerId,
        $data["name"],
        $data["email"],
        $data["country"],
        $data["city"],
        $data["contact"]
    );

    return $updated;
}

public function changePassword($customerId,$currentPassword,$newPasssword){
      return $this->customer->changePassword(
        $customerId,$currentPassword,$newPasssword
      );
}

public function deleteAccount($customerId){
    return $this->customer->deleteCustomer($customerId);
}

}