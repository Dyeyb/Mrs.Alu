<?php
/**
 * send-otp.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Generates a 6-digit OTP, stores it (hashed) in Supabase OTP_Verifications,
 * and sends it to the user's Gmail via PHPMailer + Gmail SMTP.
 *
 * SETUP STEPS (do these once):
 *  1. Run: composer require phpmailer/phpmailer
 *  2. Configure your Gmail below (GMAIL_USER + GMAIL_APP_PASSWORD)
 *  3. Run supabase_setup.sql in your Supabase SQL Editor
 *
 * HOW TO GET A GMAIL APP PASSWORD:
 *  1. Go to myaccount.google.com → Security → 2-Step Verification (enable it)
 *  2. Then go to myaccount.google.com/apppasswords
 *  3. Create a new app password → name it "Aluminum Lady"
 *  4. Copy the 16-character password → paste it below as GMAIL_APP_PASSWORD
 *
 * Expects POST JSON: { "user_id": "...", "email": "..." }
 */

// ── CONFIG — edit these ───────────────────────────────────────────────────────
define('SB_URL',            'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SB_KEY',            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');
define('OTP_TABLE',         'OTP_Verifications');
define('USERS_TABLE',       'Users');

// ── YOUR GMAIL CREDENTIALS ─────────────────────────────────────────────────
define('GMAIL_USER',         'cuarteljohncarlosl@gmail.com'); // ✅ fixed
define('GMAIL_APP_PASSWORD', 'mcnz lqqc mzfp npan');          // ✅ fixed
define('MAIL_FROM_NAME',     'Napakapogi ko');

// ─────────────────────────────────────────────────────────────────────────────

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── Load PHPMailer ────────────────────────────────────────────────────────────
// Make sure you ran: composer require phpmailer/phpmailer
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    // Fallback: try a relative path one level up
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoload)) {
        out(false, 'PHPMailer not found. Run: composer require phpmailer/phpmailer', null, 500);
    }
    require $autoload;
} else {
    require __DIR__ . '/vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ── Helpers ───────────────────────────────────────────────────────────────────
function sb(string $method, string $endpoint, ?array $body = null): array {
    $ch = curl_init(SB_URL . '/rest/v1/' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: '               . SB_KEY,
            'Authorization: Bearer ' . SB_KEY,
            'Prefer: return=representation',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $raw    = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => json_decode($raw, true)];
}

function out(bool $ok, string $msg, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Method not allowed.', null, 405);

// ── Parse & validate input ────────────────────────────────────────────────────
$in     = json_decode(file_get_contents('php://input'), true) ?? [];
$userId = trim($in['user_id'] ?? '');
$email  = strtolower(trim($in['email'] ?? ''));

if (!$userId || !$email) out(false, 'user_id and email are required.', null, 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) out(false, 'Invalid email address.', null, 422);

// ── Rate-limit: max 3 OTPs per 10 minutes ────────────────────────────────────
$since = date('Y-m-d\TH:i:sP', strtotime('-10 minutes'));
$check = sb('GET', OTP_TABLE
    . '?user_id=eq.' . urlencode($userId)
    . '&created_at=gte.' . urlencode($since)
    . '&select=id'
);
if (!empty($check['body']) && is_array($check['body']) && count($check['body']) >= 3) {
    out(false, 'Too many OTP requests. Please wait a few minutes.', null, 429);
}

// ── Delete all previous unused OTPs for this user ────────────────────────────
sb('DELETE', OTP_TABLE . '?user_id=eq.' . urlencode($userId) . '&used=eq.false');

// ── Generate 6-digit OTP ──────────────────────────────────────────────────────
$otp       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otpHash   = password_hash($otp, PASSWORD_BCRYPT);
$expiresAt = date('Y-m-d\TH:i:sP', strtotime('+10 minutes'));

// ── Store OTP in Supabase ─────────────────────────────────────────────────────
$insert = sb('POST', OTP_TABLE, [
    'user_id'    => $userId,
    'email'      => $email,
    'otp_hash'   => $otpHash,
    'expires_at' => $expiresAt,
    'used'       => false,
]);

if (!in_array($insert['status'], [200, 201])) {
    out(false, 'Failed to store OTP. Please try again.', null, 500);
}

// ── Build HTML email body ─────────────────────────────────────────────────────
$year     = date('Y');
$htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Verify Your Email</title>
</head>
<body style="margin:0;padding:0;background:#f4f3f0;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f3f0;padding:40px 20px;">
    <tr><td align="center">
      <table width="500" cellpadding="0" cellspacing="0" style="max-width:500px;width:100%;background:#1a1a18;border-radius:12px;overflow:hidden;border-top:3px solid #b8935a;box-shadow:0 8px 40px rgba(0,0,0,0.25);">

        <!-- Header -->
        <tr>
          <td style="padding:32px 40px 24px;text-align:center;">
            <div style="font-size:24px;font-weight:700;color:#b8935a;letter-spacing:1px;font-family:Georgia,serif;">
              Mrs.<span style="font-style:italic;">Alu</span>
            </div>
            <div style="font-size:9px;color:rgba(184,147,90,0.5);letter-spacing:3px;text-transform:uppercase;margin-top:4px;">
              Aluminum Lady Building Materials
            </div>
          </td>
        </tr>

        <!-- Divider -->
        <tr><td style="padding:0 40px;"><div style="height:1px;background:rgba(184,147,90,0.2);"></div></td></tr>

        <!-- Body -->
        <tr>
          <td style="padding:32px 40px;">
            <p style="font-size:15px;color:rgba(232,228,220,0.85);margin:0 0 10px;">Hello,</p>
            <p style="font-size:14px;color:rgba(136,136,128,0.9);line-height:1.75;margin:0 0 28px;">
              Thank you for registering with <strong style="color:#e8e4dc;">Aluminum Lady</strong>.
              Enter the verification code below to confirm your email and activate your account.
            </p>

            <!-- OTP Block -->
            <div style="background:rgba(184,147,90,0.06);border:1px solid rgba(184,147,90,0.2);border-radius:10px;padding:28px 24px;text-align:center;margin-bottom:28px;">
              <div style="font-size:10px;letter-spacing:3px;text-transform:uppercase;color:rgba(184,147,90,0.6);margin-bottom:14px;">
                Your Verification Code
              </div>
              <div style="font-size:48px;font-weight:700;letter-spacing:16px;color:#b8935a;font-family:Georgia,serif;padding-left:16px;">
                {OTP}
              </div>
              <div style="font-size:12px;color:rgba(136,136,128,0.6);margin-top:14px;">
                ⏱ Expires in <strong style="color:#b8935a;">10 minutes</strong>
              </div>
            </div>

            <p style="font-size:12px;color:rgba(136,136,128,0.55);line-height:1.7;border-top:1px solid rgba(255,255,255,0.05);padding-top:20px;margin:0;">
              If you did not create an account with Aluminum Lady, you can safely ignore this email.
              <strong style="color:rgba(224,84,84,0.8);">Never share this code with anyone.</strong>
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#141412;padding:18px 40px;text-align:center;">
            <p style="font-size:11px;color:rgba(136,136,128,0.4);margin:0;letter-spacing:0.5px;">
              © {YEAR} Aluminum Lady Building Materials &nbsp;·&nbsp; Est. 2024
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

$htmlBody = str_replace(['{OTP}', '{YEAR}'], [$otp, $year], $htmlBody);
$textBody = "Your Aluminum Lady verification code is: {$otp}\n\nThis code expires in 10 minutes.\n\nDo not share this code with anyone.";

// ── Send via PHPMailer (Gmail SMTP) ───────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    // SMTP config
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = GMAIL_USER;         // ✅ uses the constant defined above
    $mail->Password   = GMAIL_APP_PASSWORD; // ✅ uses the constant defined above
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender & recipient
    $mail->setFrom(GMAIL_USER, MAIL_FROM_NAME);
    $mail->addAddress($email);
    $mail->addReplyTo(GMAIL_USER, MAIL_FROM_NAME);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Aluminum Lady Verification Code – ' . $otp;
    $mail->Body    = $htmlBody;
    $mail->AltBody = $textBody;

    $mail->send();

} catch (Exception $e) {
    error_log('PHPMailer Error: ' . $mail->ErrorInfo);
    out(false, 'OTP saved but email delivery failed: ' . $mail->ErrorInfo . '. Use "Resend code" to try again.', null, 500);
}

out(true, 'OTP sent successfully to ' . $email . '. Please check your inbox (and spam folder).');
