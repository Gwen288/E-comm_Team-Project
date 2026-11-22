<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/db_class.php';

/*
 * Saves the answers from views/hair_quiz.php and opens views/recommendations.php.
 * Everyone can take the quiz; guests get the answers in the session only.
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'views/hair_quiz.php');
}

// These lists mirror the options on the form; anything else is rejected.
$allowedHairTypes = ['3C', '4A', '4B', '4C', 'Not sure'];

$allowedConcerns = [
    'Dryness',
    'Breakage',
    'Frizz',
    'Tangles and knots',
    'Itchy or flaky scalp',
    'Thinning or weak edges',
    'Product buildup'
];

$allowedGoals = [
    'Lasting moisture',
    'Length retention',
    'Growth',
    'Curl definition',
    'Repair',
    'Healthy scalp'
];

$allowedScalpTypes = ['Dry', 'Oily', 'Balanced', 'Sensitive', 'Not sure'];

$allowedWashFrequencies = [
    '2–3 times a week or more',
    'Once a week',
    'Every 2 weeks or less'
];

$allowedPreferences = [
    'Fragrance-free',
    'Vegan',
    'Sulphate-free',
    'Silicone-free'
];

$allowedBudgets = [
    'essentials',
    '250-500',
    '500-750',
    '750-plus'
];

$hairType = $_POST['hair_type'] ?? '';
$concerns = $_POST['concerns'] ?? [];
// Up to two goals (older forms sent one hair_goal).
$goals = $_POST['hair_goals'] ?? (isset($_POST['hair_goal']) ? [$_POST['hair_goal']] : []);
$scalpType = $_POST['scalp_type'] ?? '';
$washFrequency = $_POST['wash_frequency'] ?? '';
$preferences = $_POST['preferences'] ?? [];
$budget = $_POST['budget'] ?? '';

if (
    !in_array($hairType, $allowedHairTypes, true) ||
    !is_array($goals) ||
    count($goals) < 1 ||
    count($goals) > 2 ||
    array_diff($goals, $allowedGoals) ||
    !in_array($scalpType, $allowedScalpTypes, true) ||
    !in_array($washFrequency, $allowedWashFrequencies, true) ||
    !in_array($budget, $allowedBudgets, true) ||
    !is_array($concerns) ||
    !is_array($preferences) ||
    count($concerns) < 1 ||
    count($concerns) > 3
) {
    $_SESSION['error'] = 'Please check your answers and try again.';
    redirect(BASE_URL . 'views/hair_quiz.php');
}

foreach ($concerns as $concern) {
    if (!in_array($concern, $allowedConcerns, true)) {
        $_SESSION['error'] = 'One or more selected concerns are invalid.';
        redirect(BASE_URL . 'views/hair_quiz.php');
    }
}

foreach ($preferences as $preference) {
    if (!in_array($preference, $allowedPreferences, true)) {
        $_SESSION['error'] = 'One or more product preferences are invalid.';
        redirect(BASE_URL . 'views/hair_quiz.php');
    }
}

/*
 * Keep the complete quiz answers in the session.
 * This lets guests receive recommendations too.
 */
$_SESSION['hair_quiz'] = [
    'hair_type' => $hairType,
    'concerns' => array_values($concerns),
    'hair_goals' => array_values(array_unique($goals)),
    'hair_goal' => $goals[0],
    'scalp_type' => $scalpType,
    'wash_frequency' => $washFrequency,
    'preferences' => array_values($preferences),
    'budget' => $budget,
    'completed_at' => date('Y-m-d H:i:s')
];

/*
 * Save reusable hair-profile information for logged-in customers.
 * The current shopping goal and budget are intentionally not saved.
 */
if (is_logged_in() && !is_admin()) {

    // Exposes the connection so the statements below can use it directly.
    class HairQuizDatabase extends Database
    {
        public function getConnection()
        {
            return $this->conn;
        }
    }

    $database = new HairQuizDatabase();
    $conn = $database->getConnection();

    $customerId = get_customer_id();

    try {

        // Either the whole profile is replaced or none of it is.
        $conn->begin_transaction();

        // Reuse the profile row if this customer took the quiz before.
        $profileStmt = $conn->prepare(
            "INSERT INTO cc_hair_profiles (customer_id)
             VALUES (?)
             ON DUPLICATE KEY UPDATE
                profile_id = LAST_INSERT_ID(profile_id)"
        );

        if (!$profileStmt) {
            throw new Exception('Could not prepare hair profile.');
        }

        $profileStmt->bind_param('i', $customerId);

        if (!$profileStmt->execute()) {
            throw new Exception('Could not save hair profile.');
        }

        $profileId = $conn->insert_id;
        $profileStmt->close();

        $profileAttributes = [];

        if ($hairType !== 'Not sure') {
            $profileAttributes[] = [
                'name' => $hairType,
                'type' => 'texture'
            ];
        }

        foreach ($concerns as $concern) {
            $profileAttributes[] = [
                'name' => $concern,
                'type' => 'concern'
            ];
        }

        foreach ($preferences as $preference) {
            $profileAttributes[] = [
                'name' => $preference,
                'type' => 'preference'
            ];
        }

        $findAttribute = $conn->prepare(
            "SELECT attribute_id
             FROM cc_hair_attributes
             WHERE attribute_name = ?
               AND attribute_type = ?"
        );

        $insertProfileAttribute = $conn->prepare(
            "INSERT IGNORE INTO cc_hair_profile_attributes
                (profile_id, attribute_id)
             VALUES (?, ?)"
        );

        $deleteProfileAttributes = $conn->prepare(
            "DELETE FROM cc_hair_profile_attributes
             WHERE profile_id = ?"
        );

        // Replace the old traits instead of leaving stale ones behind.
        $deleteProfileAttributes->bind_param('i', $profileId);
        $deleteProfileAttributes->execute();
        $deleteProfileAttributes->close();

        foreach ($profileAttributes as $attribute) {

            $attributeName = $attribute['name'];
            $attributeType = $attribute['type'];

            $findAttribute->bind_param(
                'ss',
                $attributeName,
                $attributeType
            );

            $findAttribute->execute();

            $result = $findAttribute->get_result();
            $attributeRow = $result->fetch_assoc();

            if (!$attributeRow) {

                $insertAttribute = $conn->prepare(
                    "INSERT INTO cc_hair_attributes
                        (attribute_name, attribute_type)
                     VALUES (?, ?)"
                );

                $insertAttribute->bind_param(
                    'ss',
                    $attributeName,
                    $attributeType
                );

                if (!$insertAttribute->execute()) {
                    throw new Exception('Could not save hair attribute.');
                }

                $attributeId = $conn->insert_id;
                $insertAttribute->close();

            } else {
                $attributeId = (int) $attributeRow['attribute_id'];
            }

            $insertProfileAttribute->bind_param(
                'ii',
                $profileId,
                $attributeId
            );

            if (!$insertProfileAttribute->execute()) {
                throw new Exception('Could not update hair profile.');
            }
        }

        $findAttribute->close();
        $insertProfileAttribute->close();

        $conn->commit();

    } catch (Throwable $error) {

        $conn->rollback();

        error_log('Hair quiz profile error: ' . $error->getMessage());

        $_SESSION['error'] =
            'Your recommendations are ready, but we could not save your hair profile.';
    }
}

redirect(BASE_URL . 'views/recommendations.php');