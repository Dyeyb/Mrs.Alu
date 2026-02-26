<?php
/**
 * resend-otp.php
 * Checks if email exists → generates OTP → saves to DB → sends email
 * Returns JSON: { success: bool, message: string, data: { user_id: int } }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// ── Config ────────────────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');     // ← change this
define('DB_USER', 'your_db_user');      // ← change this
define('DB_PASS', 'your_db_password');  // ← change this

define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USERNAME', 'your@gmail.com');       // ← change this
define('MAIL_PASSWORD', 'your_app_password');    // ← Gmail App Password
define('MAIL_FROM',     'your@gmail.com');       // ← change this
define('MAIL_NAME',     'Aluminum Lady');

// ── DB Connection ─────────────────────────────────────────────────────────────
function getDB() {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        respond(false, 'Database connection failed.');
    }
}

// ── Helper ────────────────────────────────────────────────────────────────────
function respond($success, $message = '', $data = null) {
    $payload = ['success' => $success, 'message' => $message];
    if ($data !== null) $payload['data'] = $data;
    echo json_encode($payload);
    exit;
}

// ── Only allow POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed.');
}

// ── Read JSON body ────────────────────────────────────────────────────────────
$body  = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($body['email'] ?? ''));

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Invalid email address.');
}

// ── Connect ───────────────────────────────────────────────────────────────────
$pdo = getDB();

// ── Check if email exists in users table ─────────────────────────────────────
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    // Do NOT reveal whether email exists for security (optional)
    // Change message below if you prefer explicit "not found" feedback
    respond(false, 'No account found with that email address.');
}

$user_id = $user['user_id'];

// ── Generate 6-digit OTP ──────────────────────────────────────────────────────
$otp        = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires_at = date('Y-m-d H:i:s', time() + 600); // 10 minutes

// ── Save OTP to DB ────────────────────────────────────────────────────────────
// Table: otp_codes (user_id INT UNIQUE, otp VARCHAR(6), expires_at DATETIME, created_at DATETIME)
$stmt = $pdo->prepare("
    INSERT INTO otp_codes (user_id, otp, expires_at, created_at)
    VALUES (:user_id, :otp, :expires_at, NOW())
    ON DUPLICATE KEY UPDATE
        otp        = VALUES(otp),
        expires_at = VALUES(expires_at),
        created_at = NOW()
");
$stmt->execute([
    ':user_id'    => $user_id,
    ':otp'        => $otp,
    ':expires_at' => $expires_at,
]);

// ── Send Email via PHPMailer ──────────────────────────────────────────────────
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;

    // Recipients
    $mail->setFrom(MAIL_FROM, MAIL_NAME);
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Password Reset Code – Aluminum Lady';
    $mail->Body    = buildEmailHTML($otp);
    $mail->AltBody = "Your Aluminum Lady password reset code is: $otp\nThis code expires in 10 minutes.";

    $mail->send();

    respond(true, 'OTP sent successfully.', ['user_id' => $user_id]);

} catch (Exception $e) {
    // OTP was saved but email failed — still respond with error
    respond(false, 'Failed to send verification email. Please try again.');
}

// ── Email Template ────────────────────────────────────────────────────────────
function buildEmailHTML($otp) {
    // Split OTP into individual digit boxes for styling
    $digits = '';
    foreach (str_split($otp) as $d) {
        $digits .= "<span style='
            display:inline-block;
            width:44px; height:52px;
            line-height:52px;
            text-align:center;
            font-size:1.6rem;
            font-weight:700;
            background:#f5f0e8;
            border-radius:6px;
            margin:0 3px;
            color:#1a1a18;
            font-family: Georgia, serif;
        '>$d</span>";
    }

    return "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#f4f1eb;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f1eb;padding:40px 20px;'>
            <tr>
                <td align='center'>
                    <table width='520' cellpadding='0' cellspacing='0'
                           style='background:#1a1a18;border-radius:12px;overflow:hidden;'>

                        <!-- Header -->
                        <tr>
                            <td style='background:linear-gradient(135deg,#4a2e18,#b8935a);
                                        padding:32px 40px;text-align:center;'>
                                <div style='font-family:Georgia,serif;font-size:1.6rem;
                                            font-weight:700;color:#fff;letter-spacing:2px;'>
                                    ALUMINUM LADY
                                </div>
                                <div style='color:rgba(255,255,255,0.6);font-size:0.7rem;
                                            letter-spacing:4px;text-transform:uppercase;margin-top:4px;'>
                                    Premium Building Materials
                                </div>
                            </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                            <td style='padding:40px;text-align:center;color:#e8e4dc;'>
                                <p style='font-size:0.85rem;color:#888880;
                                           text-transform:uppercase;letter-spacing:2px;margin:0 0 8px;'>
                                    Password Recovery
                                </p>
                                <h2 style='font-family:Georgia,serif;font-size:1.8rem;
                                            font-weight:400;color:#e8e4dc;margin:0 0 20px;'>
                                    Your Verification Code
                                </h2>
                                <p style='font-size:0.9rem;color:#888880;margin:0 0 28px;line-height:1.6;'>
                                    Use the code below to reset your password.<br>
                                    It expires in <strong style='color:#b8935a;'>10 minutes</strong>.
                                </p>

                                <!-- OTP Digits -->
                                <div style='margin:0 auto 32px;'>$digits</div>

                                <div style='background:#222220;border-radius:8px;
                                             padding:16px 24px;margin-bottom:28px;'>
                                    <p style='font-size:0.78rem;color:#888880;margin:0;line-height:1.6;'>
                                        If you did not request a password reset, you can safely ignore this email.
                                        Your account remains secure.
                                    </p>
                                </div>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style='border-top:1px solid rgba(184,147,90,0.2);
                                        padding:20px 40px;text-align:center;'>
                                <p style='font-size:0.72rem;color:#555552;margin:0;'>
                                    &copy; " . date('Y') . " Aluminum Lady. All rights reserved.
                                </p>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ";
}