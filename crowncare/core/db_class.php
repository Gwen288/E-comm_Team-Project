<?php

/*
 * Opens the MySQL connection shared by the CrownCare classes.
 * Every model extends Database, and core/core.php loads this file first.
 */

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

            // core/core.php makes mysqli throw on errors, so a bad connection stops here.
            self::$shared = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            // utf8mb4 keeps names with non-ASCII characters intact.
            self::$shared->set_charset('utf8mb4');
        }

        $this->conn = self::$shared;
    }
}
