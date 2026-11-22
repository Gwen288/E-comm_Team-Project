<?php

require_once __DIR__ . '/../core/db_class.php';

/*
 * Delivery areas in Greater Accra and their delivery fees (cc_delivery_areas).
 * Orders from FREE_DELIVERY_THRESHOLD (core/paystack_config.php) are delivered free.
 */
class DeliveryClass extends Database
{
    /** Areas open for delivery, in menu order. */
    public function getActiveAreas()
    {
        $result = $this->conn->query(
            "SELECT area_id, area_name, delivery_fee FROM cc_delivery_areas
             WHERE is_active = 1 ORDER BY sort_order, area_name"
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllAreas()
    {
        $result = $this->conn->query(
            "SELECT area_id, area_name, delivery_fee, is_active, sort_order FROM cc_delivery_areas
             ORDER BY sort_order, area_name"
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getActiveAreaByName($name)
    {
        $stmt = $this->conn->prepare(
            "SELECT area_id, area_name, delivery_fee FROM cc_delivery_areas WHERE area_name = ? AND is_active = 1"
        );
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: false;
    }

    public function getActiveAreaById($areaId)
    {
        $stmt = $this->conn->prepare(
            "SELECT area_id, area_name, delivery_fee FROM cc_delivery_areas WHERE area_id = ? AND is_active = 1"
        );
        $stmt->bind_param("i", $areaId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ?: false;
    }

    /** Delivery fee for an area and order subtotal. */
    public static function feeFor($area, $subtotal)
    {
        if (!$area || $subtotal <= 0) {
            return 0.0;
        }
        if (defined('FREE_DELIVERY_THRESHOLD') && $subtotal >= FREE_DELIVERY_THRESHOLD) {
            return 0.0;
        }
        return (float) $area['delivery_fee'];
    }

    public function saveArea($areaId, $name, $fee, $isActive)
    {
        if ($areaId > 0) {
            $stmt = $this->conn->prepare(
                "UPDATE cc_delivery_areas SET area_name = ?, delivery_fee = ?, is_active = ? WHERE area_id = ?"
            );
            $stmt->bind_param("sdii", $name, $fee, $isActive, $areaId);
        } else {
            $stmt = $this->conn->prepare(
                "INSERT INTO cc_delivery_areas (area_name, delivery_fee, is_active, sort_order)
                 SELECT ?, ?, ?, COALESCE(MAX(sort_order), 0) + 10 FROM cc_delivery_areas"
            );
            $stmt->bind_param("sdi", $name, $fee, $isActive);
        }
        $stmt->execute();
        $stmt->close();

        return true;
    }

    public function areaNameTaken($name, $exceptId)
    {
        $stmt = $this->conn->prepare("SELECT area_id FROM cc_delivery_areas WHERE area_name = ? AND area_id <> ?");
        $stmt->bind_param("si", $name, $exceptId);
        $stmt->execute();
        $taken = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $taken;
    }
}
