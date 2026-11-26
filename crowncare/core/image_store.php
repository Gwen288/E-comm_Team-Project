<?php
/*
 * Product photos stored in the database (table cc_image_store, migration 014).
 *
 * Uploaded photos are saved as files in images/products/ whenever the server
 * allows it. When PHP is not allowed to write to that folder, the photo is kept
 * in the database instead and shown through image.php?f=<file name>.
 */

require_once __DIR__ . '/db_class.php';

class ImageStore extends Database
{
    /** File names kept in the database, loaded once per page. */
    private static $names = null;

    public function names()
    {
        if (self::$names === null) {
            self::$names = [];
            try {
                $result = $this->conn->query('SELECT file_name FROM cc_image_store');
                foreach ($result ? $result->fetch_all(MYSQLI_NUM) : [] as $row) {
                    self::$names[$row[0]] = true;
                }
            } catch (Throwable $e) {
                // Table missing (migration 014 not run): no stored photos.
            }
        }
        return self::$names;
    }

    public function has($fileName)
    {
        return isset($this->names()[basename((string) $fileName)]);
    }

    public function save($fileName, $mimeType, $path)
    {
        $data = @file_get_contents($path);
        if ($data === false || $data === '') {
            return false;
        }
        $fileName = basename($fileName);
        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO cc_image_store (file_name, mime_type, image_data) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE mime_type = VALUES(mime_type), image_data = VALUES(image_data)'
            );
            $null = null;
            $stmt->bind_param('ssb', $fileName, $mimeType, $null);
            $stmt->send_long_data(2, $data);
            $ok = $stmt->execute();
            $stmt->close();
        } catch (Throwable $e) {
            error_log('Image store save failed (has migration 014 been run?): ' . $e->getMessage());
            return false;
        }
        if ($ok && self::$names !== null) {
            self::$names[$fileName] = true;
        }
        return $ok;
    }

    public function get($fileName)
    {
        $fileName = basename((string) $fileName);
        try {
            $stmt = $this->conn->prepare('SELECT mime_type, image_data, created_at FROM cc_image_store WHERE file_name = ?');
            $stmt->bind_param('s', $fileName);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function delete($fileName)
    {
        $fileName = basename((string) $fileName);
        try {
            $stmt = $this->conn->prepare('DELETE FROM cc_image_store WHERE file_name = ?');
            $stmt->bind_param('s', $fileName);
            $stmt->execute();
            $stmt->close();
        } catch (Throwable $e) {
            // Nothing to delete.
        }
        if (self::$names !== null) {
            unset(self::$names[$fileName]);
        }
    }
}

/**
 * Address of a product photo: the file in images/products/ when it exists,
 * otherwise image.php when the photo is kept in the database, otherwise null.
 */
function cc_product_image_url($fileName)
{
    $fileName = basename(trim((string) $fileName));
    if ($fileName === '') {
        return null;
    }
    if (is_file(__DIR__ . '/../images/products/' . $fileName)) {
        return BASE_URL . 'images/products/' . rawurlencode($fileName);
    }
    if ((new ImageStore())->has($fileName)) {
        return BASE_URL . 'image.php?f=' . rawurlencode($fileName);
    }
    return null;
}

/**
 * Saves an uploaded product photo: as a file when images/products is writable,
 * otherwise in the database. Returns 'file', 'database' or false.
 */
function cc_save_product_image($tmpPath, $fileName, $mimeType)
{
    $dir = __DIR__ . '/../images/products/';
    if ((is_dir($dir) || @mkdir($dir, 0755, true)) && is_writable($dir)
        && @move_uploaded_file($tmpPath, $dir . $fileName)) {
        return 'file';
    }
    return (new ImageStore())->save($fileName, $mimeType, $tmpPath) ? 'database' : false;
}

/** Removes a product photo, wherever it is kept. */
function cc_delete_product_image($fileName)
{
    $fileName = basename(trim((string) $fileName));
    if ($fileName === '') {
        return;
    }
    $path = __DIR__ . '/../images/products/' . $fileName;
    if (is_file($path)) {
        @unlink($path);
    }
    (new ImageStore())->delete($fileName);
}
