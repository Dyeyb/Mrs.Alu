<?php
/**
 * verify-reactivation-otp.php (FIXED)
 * 
 * Verifies OTP and reactivates user account
 * Uses Supabase REST API instead of PDO
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
    $email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $otp = trim($input['otp'] ?? '');
    $type = trim($input['type'] ?? 'Account Reactivation');

    // Validate input
    if (!$email || !$otp) {
        json_response(false, 'Missing required fields: email and otp', null, 400);
    }

    if (strlen($otp) !== 6 || !ctype_digit($otp)) {
        json_response(false, 'Invalid OTP format', null, 400);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 1. Get user by email
    // ─────────────────────────────────────────────────────────────────────

    $user = get_user_by_email($email);

    if (!$user) {
        json_response(false, 'Account not found', null, 404);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 2. Get latest valid OTP for this email
    // ─────────────────────────────────────────────────────────────────────

    $otp_record = get_latest_otp($email, $type);

    if (!$otp_record) {
        json_response(false, 'No valid OTP found. Please request a new one.', null, 400);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 3. Check OTP expiration
    // ─────────────────────────────────────────────────────────────────────

    $expires_at = strtotime($otp_record['expires_at']);
    $now = time();

    if ($expires_at < $now) {
        json_response(false, 'OTP has expired. Please request a new one.', null, 400);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 4. Verify OTP hash
    // ─────────────────────────────────────────────────────────────────────

    if (!verify_password($otp, $otp_record['otp_hash'] ?? '')) {
        log_error('Invalid OTP attempt for email: ' . $email, 'WARNING');
        json_response(false, 'Invalid OTP. Please try again.', null, 400);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 5. Mark OTP as used
    // ─────────────────────────────────────────────────────────────────────

    $otp_id = $otp_record['id'] ?? null;
    if (!$otp_id || !mark_otp_as_used((int)$otp_id)) {
        log_error('Failed to mark OTP as used for email: ' . $email, 'ERROR');
        json_response(false, 'Error processing verification. Please try again.', null, 500);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 6. Update user status to active
    // ─────────────────────────────────────────────────────────────────────

    $user_id = (int)($user['user_id'] ?? 0);
    if (!$user_id || !update_user_status($user_id, 'active', false)) {
        log_error('Failed to update user status for email: ' . $email, 'ERROR');
        json_response(false, 'Error activating account. Please contact support.', null, 500);
    }

    // ─────────────────────────────────────────────────────────────────────
    // 7. Send confirmation email
    // ─────────────────────────────────────────────────────────────────────

    $first_name = $user['first_name'] ?? 'User';
    $last_name = $user['last_name'] ?? '';
    $full_name = trim($first_name . ' ' . $last_name);

    $subject = 'Your Account Has Been Reactivated';

    $message = "
        <html>
            <body style='font-family: Arial, sans-serif; background: #f5f5f5;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    <h2 style='color: #E8960A; margin-bottom: 10px;'>Account Reactivated Successfully ✓</h2>
                    <p style='color: #333; font-size: 14px; margin-bottom: 20px;'>Hello {$full_name},</p>
                    
                    <p style='color: #333; font-size: 14px; margin-bottom: 20px;'>
                        Great news! Your CAL ELITE account has been successfully reactivated and is now active.
                    </p>
                    
                    <div style='background: #f0ebe0; padding: 15px; margin: 30px 0; border-left: 4px solid #E8960A; border-radius: 3px;'>
                        <p style='margin: 0; color: #333; font-size: 14px;'><strong>Account Status:</strong> Active ✓</p>
                        <p style='margin: 10px 0 0 0; color: #333; font-size: 14px;'><strong>Email:</strong> {$email}</p>
                    </div>
                    
                    <p style='color: #333; font-size: 14px; margin-bottom: 20px;'>
                        You can now log in to your account and access all features.
                    </p>
                    
                    <a href='https://yoursite.com/login' style='display: inline-block; background: #E8960A; color: white; padding: 12px 24px; text-decoration: none; border-radius: 3px; font-weight: bold; margin: 20px 0;'>Go to Login</a>
                    
                    <p style='color: #999; font-size: 12px; margin-top: 30px; margin-bottom: 20px;'>
                        If you didn't request this reactivation, please contact our support team immediately.
                    </p>
                    
                    <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                    <p style='color: #999; font-size: 12px; margin: 0;'>© 2024 CAL ELITE Builders & CAL Electrical. All rights reserved.</p>
                </div>
            </body>
        </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@caleliticbuilders.com\r\n";

    // Send confirmation email (non-blocking)
    mail($email, $subject, $message, $headers);

    // ─────────────────────────────────────────────────────────────────────
    // 8. Return success with user data
    // ─────────────────────────────────────────────────────────────────────

    json_response(
        true,
        'Account reactivated successfully',
    [
        'user_id' => $user['user_id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'email' => $email,
        'status' => 'active',
        'is_archived' => false
    ],
        200
    );

}
catch (Exception $e) {
    log_error('Exception in verify-reactivation-otp.php: ' . $e->getMessage(), 'ERROR');
    json_response(false, 'Server error: ' . $e->getMessage(), null, 500);
}