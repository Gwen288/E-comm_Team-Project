<?php 

class Database { 

  protected $conn; 

  public function __construct() { 

    // Use local credentials when available, otherwise fall back to the example file.
    $credFile = __DIR__ . '/db_cred.php';
    if (!is_file($credFile)) {
        $credFile = __DIR__ . '/db_cred_example.php';
    }
    require_once $credFile;

    $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME); 

    if ($this->conn->connect_error) { 

      error_log($this->conn->connect_error); 

      die('Connection failed.'); 

    } 

  } 

}