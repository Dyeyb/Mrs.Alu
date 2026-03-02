<?php
/**
 * resend-otp.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Used by the Forgot-Password flow.
 * Looks up email in Supabase → generates OTP → stores hash in OTP_Verifications
 * → sends email via PHPMailer + Gmail SMTP.
 *
 * Expects POST JSON: { "email": "..." }
 * Returns JSON:      { success: bool, message: string, data: { user_id } }
 */

// ── CONFIG ────────────────────────────────────────────────────────────────────
define('SB_URL',            'https://pdqhbxtxvxrwtkvymjlm.supabase.co');
define('SB_KEY',            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBkcWhieHR4dnhyd3RrdnltamxtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzE1NTEyMzIsImV4cCI6MjA4NzEyNzIzMn0.jKq6Zw1XWDYXkxdrkW6HscOpsOuUm0gcyBCwFsAwN9U');
define('USERS_TABLE',       'Users');
define('OTP_TABLE',         'OTP_Verifications');

// ── GMAIL SMTP CREDENTIALS ────────────────────────────────────────────────────
define('GMAIL_USER',         'cuarteljohncarlosl@gmail.com');
define('GMAIL_APP_PASSWORD', 'mcnz lqqc mzfp npan');
define('MAIL_FROM_NAME',     'CalElite Agent');

// ─────────────────────────────────────────────────────────────────────────────

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── Load PHPMailer ────────────────────────────────────────────────────────────
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    out(false, 'PHPMailer not found. Run: composer require phpmailer/phpmailer', null, 500);
}

use PHPMailer\PHPMailer\PHPMailer;
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
    $cerr   = curl_error($ch);
    curl_close($ch);
    return ['status' => $status, 'body' => json_decode($raw, true), 'cerr' => $cerr];
}

function out(bool $ok, string $msg, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $ok, 'message' => $msg, 'data' => $data]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Method not allowed.', null, 405);

// ── Parse & validate input ────────────────────────────────────────────────────
$in    = json_decode(file_get_contents('php://input'), true) ?? [];
$email = strtolower(trim($in['email'] ?? ''));

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(false, 'Please enter a valid email address.', null, 422);
}

// ── Look up user in Supabase ──────────────────────────────────────────────────
$r = sb('GET', USERS_TABLE
    . '?select=user_id,first_name,status,is_archived'
    . '&email=eq.' . urlencode($email)
    . '&limit=1');

if ($r['cerr'])           out(false, 'Connection error. Please try again.',   null, 500);
if ($r['status'] !== 200) out(false, 'Database error. Please try again.',     null, 500);

if (empty($r['body']) || !is_array($r['body'])) {
    out(false, 'No account found with that email address.', null, 404);
}

$user    = $r['body'][0];
$userId  = $user['user_id'];

// ── Account status checks ─────────────────────────────────────────────────────
if ((bool)($user['is_archived'] ?? false)) {
    out(false, 'This account has been archived. Please contact support.', null, 403);
}
if (($user['status'] ?? '') === 'inactive') {
    out(false, 'Your account is inactive. Please contact support.', null, 403);
}
if (($user['status'] ?? '') === 'suspended') {
    out(false, 'Your account has been suspended. Please contact support.', null, 403);
}

// ── Rate-limit: max 3 OTPs per 10 minutes ────────────────────────────────────
$since = date('Y-m-d\TH:i:sP', strtotime('-10 minutes'));
$chk   = sb('GET', OTP_TABLE
    . '?user_id=eq.' . urlencode($userId)
    . '&created_at=gte.' . urlencode($since)
    . '&select=id');

if (!empty($chk['body']) && is_array($chk['body']) && count($chk['body']) >= 3) {
    out(false, 'Too many OTP requests. Please wait a few minutes before trying again.', null, 429);
}

// ── Delete all previous unused OTPs for this user ────────────────────────────
sb('DELETE', OTP_TABLE . '?user_id=eq.' . urlencode($userId) . '&used=eq.false');

// ── Generate 6-digit OTP ──────────────────────────────────────────────────────
$otp       = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otpHash   = password_hash($otp, PASSWORD_BCRYPT);
$expiresAt = date('Y-m-d\TH:i:sP', strtotime('+10 minutes'));

// ── Store OTP in Supabase ─────────────────────────────────────────────────────
$insert = sb('POST', OTP_TABLE, [
    'user_id'    => (string) $userId,
    'email'      => $email,
    'otp_hash'   => $otpHash,
    'expires_at' => $expiresAt,
    'used'       => false,
]);

if (!in_array($insert['status'], [200, 201])) {
    out(false, 'Failed to store verification code. Please try again.', null, 500);
}

// ── Build email body ──────────────────────────────────────────────────────────
$year     = date('Y');
$htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Password Reset Code</title>
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
              We received a request to reset your password for your <strong style="color:#e8e4dc;">CalElite Agent</strong> account.
              Use the code below to proceed. It is valid for <strong style="color:#b8935a;">10 minutes</strong>.
            </p>

            <!-- OTP Block -->
            <div style="background:rgba(184,147,90,0.06);border:1px solid rgba(184,147,90,0.2);border-radius:10px;padding:28px 24px;text-align:center;margin-bottom:28px;">
              <div style="font-size:10px;letter-spacing:3px;text-transform:uppercase;color:rgba(184,147,90,0.6);margin-bottom:14px;">
                Your Password Reset Code
              </div>
              <div style="font-size:48px;font-weight:700;letter-spacing:16px;color:#b8935a;font-family:Georgia,serif;padding-left:16px;">
                {OTP}
              </div>
              <div style="font-size:12px;color:rgba(136,136,128,0.6);margin-top:14px;">
                ⏱ Expires in <strong style="color:#b8935a;">10 minutes</strong>
              </div>
            </div>

            <p style="font-size:12px;color:rgba(136,136,128,0.55);line-height:1.7;border-top:1px solid rgba(255,255,255,0.05);padding-top:20px;margin:0;">
              If you did not request a password reset, please ignore this email. Your account remains secure.
              <strong style="color:rgba(224,84,84,0.8);">Never share this code with anyone.</strong>
            </p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#141412;padding:18px 40px;text-align:center;">
            <p style="font-size:11px;color:rgba(136,136,128,0.4);margin:0;letter-spacing:0.5px;">
              &copy; {YEAR} Aluminum Lady Building Materials &nbsp;·&nbsp; Est. 2024
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
$textBody = "Your Aluminum Lady password reset code is: {$otp}\n\nThis code expires in 10 minutes.\n\nIf you did not request this, please ignore this email.";

// ── Send via PHPMailer ────────────────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = GMAIL_USER;
    $mail->Password   = GMAIL_APP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom(GMAIL_USER, MAIL_FROM_NAME);
    $mail->addAddress($email);
    $mail->addReplyTo(GMAIL_USER, MAIL_FROM_NAME);

    $mail->isHTML(true);
    $mail->Subject = 'Your Password Reset Code – Aluminum Lady';
    $mail->Body    = $htmlBody;
    $mail->AltBody = $textBody;

    $mail->send();

} catch (Exception $e) {
    error_log('PHPMailer Error (resend-otp): ' . $mail->ErrorInfo);
    out(false, 'OTP generated but email delivery failed. Please try again.', null, 500);
}

out(true, 'Verification code sent to ' . $email . '. Please check your inbox.', [
    'user_id' => $userId,
]);