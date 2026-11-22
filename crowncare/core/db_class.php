<?php

class Database
{
    protected $conn;

    /* One connection per page, shared by every class. */
    private static $shared = null;

    public function __construct()
    {
        if (self::$shared === null) {
            // Use local credentials when available, otherwise fall back to the example file.
            $credFile = __DIR__ . '/db_cred.php';
            if (!is_file($credFile)) {
                $credFile = __DIR__ . '/db_cred_example.php';
            }
            require_once $credFile;

            self::$shared = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            self::$shared->set_charset('utf8mb4');
        }

        $this->conn = self::$shared;
    }
}
