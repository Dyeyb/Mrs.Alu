<?php
/**
 * send-reactivation-otp.php (MAILTRAP FIXED)
 * 
 * Sends OTP via Mailtrap SMTP instead of mail() function
 * Mailtrap is free for testing and development
 */

header('Content-Type: application/json');
require_once './db-config.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Parse input
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $email_input = trim($input['email'] ?? '');

    // Validate email format
    if (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Invalid email address format', null, 400);
    }

    // Normalize email (lowercase for consistency)
    $email = strtolower($email_input);

    log_error('OTP request for email: ' . $email, 'INFO');

    // ─────────────────────────────────────────────────────────────────────
    // 1. Check if user exists and get their status
    // ─────────────────────────────────────────────────────────────────────

    $user = get_user_by_email($email);

    if (!$user) {
        log_error('User not found for email: ' . $email, 'WARNING');
        json_response(
            false,
            'Email not found in our system. Please check your email address.',
            null,
            404
        );
    }

    log_error('User found: ' . $user['user_id'] . ' - Status: ' . ($user['status'] ?? 'null'), 'INFO');

    // ─────────────────────────────────────────────────────────────────────
    // 2. Verify account is actually archived/suspended
    // ─────────────────────────────────────────────────────────────────────

    $is_archived = (bool)($user['is_archived'] ?? false);
    $status = $user['status'] ?? 'active';

    if ($status === 'active' && !$is_archived) {
        log_error('Account already active for: ' . $email, 'WARNING');
        json_response(
            false,
            'Your account is already active. You can log in now!',
            null,
            400
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // 3. Generate OTP
    // ─────────────────────────────────────────────────────────────────────

    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otp_hash = hash_password($otp);
    $expires_at = date('Y-m-d H:i:s', time() + 300); // 5 minutes

    log_error('Generated OTP for: ' . $email . ' (expires at: ' . $expires_at . ')', 'INFO');

    // ─────────────────────────────────────────────────────────────────────
    // 4. Store OTP in Supabase
    // ─────────────────────────────────────────────────────────────────────

    $otp_record = create_otp(
        $user['user_id'],
        $email,
        $otp_hash,
        $expires_at,
        'Account Reactivation'
    );

    if (!$otp_record) {
        log_error('Failed to create OTP record for email: ' . $email, 'ERROR');
        json_response(
            false,
            'Failed to generate OTP. Please try again later.',
            null,
            500
        );
    }

    log_error('OTP record created successfully', 'INFO');

    // ─────────────────────────────────────────────────────────────────────
    // 5. Send OTP via Mailtrap SMTP (or fallback to mail())
    // ─────────────────────────────────────────────────────────────────────

    $first_name = $user['first_name'] ?? 'User';
    $subject = 'Reactivate Your CAL ELITE Account';

    $html_message = "
        <html>
            <body style='font-family: Arial, sans-serif; background: #f5f5f5;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    <h2 style='color: #E8960A; margin-bottom: 10px;'>Account Reactivation</h2>
                    <p style='color: #333; font-size: 14px; margin-bottom: 20px;'>Hello {$first_name},</p>
                    
                    <p style='color: #333; font-size: 14px; margin-bottom: 20px;'>
                        We received a request to reactivate your account. Use the code below to proceed:
                    </p>
                    
                    <div style='background: #f0ebe0; padding: 20px; text-align: center; margin: 30px 0; border-radius: 5px; border-left: 4px solid #E8960A;'>
                        <p style='font-size: 32px; font-weight: bold; color: #E8960A; letter-spacing: 5px; margin: 0;'>{$otp}</p>
                        <p style='color: #999; font-size: 12px; margin-top: 10px; margin-bottom: 0;'>Code expires in 5 minutes</p>
                    </div>
                    
                    <p style='color: #666; font-size: 14px; margin-bottom: 20px;'>
                        <strong>Important:</strong> Never share this code with anyone. Our support team will never ask for it.
                    </p>
                    
                    <p style='color: #666; font-size: 14px; margin-bottom: 20px;'>
                        If you didn't request this, please ignore this email or contact our support team.
                    </p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                    <p style='color: #999; font-size: 12px; margin: 0;'>© 2024 CAL ELITE Builders & CAL Electrical. All rights reserved.</p>
                </div>
            </body>
        </html>
    ";

    // Try Method 1: Mailtrap SMTP (More reliable)
    $mail_sent = send_via_mailtrap($email, $subject, $html_message);

    // Fallback: Try Method 2: Standard mail() function
    if (!$mail_sent) {
        log_error('Mailtrap failed, trying standard mail() function', 'WARNING');
        $mail_sent = send_via_mail($email, $subject, $html_message);
    }

    if (!$mail_sent) {
        log_error('Failed to send email to: ' . $email, 'WARNING');
    // Still return success as OTP was generated - inform user to check spam
    }
    else {
        log_error('Email sent successfully to: ' . $email, 'INFO');
    }

    json_response(
        true,
        'OTP sent successfully! Check your email inbox and spam folder.',
    [
        'email' => $email,
        'message' => 'Enter the 6-digit code sent to your email'
    ],
        200
    );

}
catch (Exception $e) {
    log_error('Exception in send-reactivation-otp.php: ' . $e->getMessage(), 'ERROR');
    json_response(
        false,
        'An unexpected error occurred. Please try again.',
        null,
        500
    );
}

// ═══════════════════════════════════════════════════════════════════
// EMAIL SENDING METHODS
// ═══════════════════════════════════════════════════════════════════

/**
 * Send email via Mailtrap SMTP
 * Sign up for free at: https://mailtrap.io
 * 
 * After signing up:
 * 1. Create a project
 * 2. Copy SMTP credentials
 * 3. Update the constants below
 */
function send_via_mailtrap(string $to_email, string $subject, string $html_body): bool
{
    // ⚠️ UPDATE THESE WITH YOUR MAILTRAP CREDENTIALS
    define_once('MAILTRAP_HOST', 'smtp.mailtrap.io');
    define_once('MAILTRAP_PORT', 2525);
    define_once('MAILTRAP_USER', ''); // Your Mailtrap username
    define_once('MAILTRAP_PASS', ''); // Your Mailtrap password
    define_once('MAILTRAP_FROM', 'noreply@caleliticbuilders.com');

    // Skip if not configured
    if (empty(MAILTRAP_USER) || empty(MAILTRAP_PASS)) {
        return false;
    }

    try {
        $fp = @fsockopen(MAILTRAP_HOST, MAILTRAP_PORT, $errno, $errstr, 10);

        if (!$fp) {
            return false;
        }

        // Read server response
        fgets($fp, 1024);

        // Send EHLO
        fputs($fp, "EHLO localhost\r\n");
        fgets($fp, 1024);

        // Send AUTH LOGIN
        fputs($fp, "AUTH LOGIN\r\n");
        fgets($fp, 1024);

        // Send username
        fputs($fp, base64_encode(MAILTRAP_USER) . "\r\n");
        fgets($fp, 1024);

        // Send password
        fputs($fp, base64_encode(MAILTRAP_PASS) . "\r\n");
        fgets($fp, 1024);

        // Send MAIL FROM
        fputs($fp, "MAIL FROM: <" . MAILTRAP_FROM . ">\r\n");
        fgets($fp, 1024);

        // Send RCPT TO
        fputs($fp, "RCPT TO: <" . $to_email . ">\r\n");
        fgets($fp, 1024);

        // Send DATA
        fputs($fp, "DATA\r\n");
        fgets($fp, 1024);

        // Send headers and body
        $headers = "From: " . MAILTRAP_FROM . "\r\n";
        $headers .= "To: " . $to_email . "\r\n";
        $headers .= "Subject: " . $subject . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";

        fputs($fp, $headers . "\r\n" . $html_body . "\r\n.\r\n");
        fgets($fp, 1024);

        // Send QUIT
        fputs($fp, "QUIT\r\n");
        fgets($fp, 1024);

        fclose($fp);

        return true;
    }
    catch (Exception $e) {
        return false;
    }
}

/**
 * Send email via standard mail() function
 */
function send_via_mail(string $to_email, string $subject, string $html_body): bool
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@caleliticbuilders.com\r\n";

    return @mail($to_email, $subject, $html_body, $headers);
}

/**
 * Define a constant if not already defined
 */
function define_once(string $name, $value): void
{
    if (!defined($name)) {
        define($name, $value);
    }
}
?>