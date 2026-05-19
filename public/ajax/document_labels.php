<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * LOAD LABELS
 */
if ($action === "load") {
    try {
        $result = $mysqli->query("SELECT label FROM document_labels ORDER BY id ASC");

        $labels = [];
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['label'];
        }

        echo json_encode(['status' => true, 'labels' => $labels]);
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'msg' => 'Failed to load labels']);
    }
    exit;
}

/**
 * ADD LABEL
 */
if ($action === "add") {
    $label = trim($_POST['label'] ?? '');

    if ($label === "") {
        echo json_encode(['status' => false, 'msg' => 'Label cannot be empty']);
        exit;
    }

    try {
        $stmt = $mysqli->prepare("INSERT INTO document_labels (label) VALUES (?)");
        $stmt->bind_param("s", $label);

        if ($stmt->execute()) {
            echo json_encode(['status' => true, 'label' => $label]);
        } else {
            echo json_encode(['status' => false, 'msg' => 'Label already exists']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'msg' => 'Error adding label']);
    }
    exit;
}

/**
 * DELETE LABEL
 */
if ($action === "delete") {
    $label = trim($_POST['label'] ?? '');

    if ($label === "") {
        echo json_encode(['status' => false, 'msg' => 'Invalid label']);
        exit;
    }

    try {
        $stmt = $mysqli->prepare("DELETE FROM document_labels WHERE label = ?");
        $stmt->bind_param("s", $label);

        if ($stmt->execute()) {
            echo json_encode(['status' => true]);
        } else {
            echo json_encode(['status' => false, 'msg' => 'Unable to delete label']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'msg' => 'Error deleting label']);
    }
    exit;
}

/**
 * RENAME LABEL
 */
if ($action === "rename") {
    $old = trim($_POST['old_label'] ?? '');
    $new = trim($_POST['new_label'] ?? '');

    if ($old === "" || $new === "") {
        echo json_encode(['status' => false, 'msg' => 'Invalid label data']);
        exit;
    }

    try {
        $stmt = $mysqli->prepare("UPDATE document_labels SET label = ? WHERE label = ?");
        $stmt->bind_param("ss", $new, $old);

        if ($stmt->execute()) {
            echo json_encode(['status' => true, 'label' => $new]);
        } else {
            echo json_encode(['status' => false, 'msg' => 'Unable to rename label']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'msg' => 'Error renaming label']);
    }
    exit;
}

// Invalid action
echo json_encode(['status' => false, 'msg' => 'Invalid action']);
exit;
?>