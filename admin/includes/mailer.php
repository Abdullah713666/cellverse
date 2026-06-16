<?php
/**
 * CellVerse - Email Mailer Helper
 * Uses PHPMailer with SMTP (Gmail compatible)
 */
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function getSmtpSettings() {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $settings = [];
    foreach (['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from', 'smtp_from_name'] as $key) {
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $settings[$key] = $row ? $row['setting_value'] : '';
    }
    return $settings;
}

function sendPasswordResetEmail($toEmail, $toName, $resetUrl) {
    $smtp = getSmtpSettings();

    if (empty($smtp['smtp_host']) || empty($smtp['smtp_user'])) {
        return ['success' => false, 'message' => 'SMTP not configured. Contact administrator.'];
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $smtp['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['smtp_user'];
        $mail->Password   = $smtp['smtp_pass'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($smtp['smtp_port'] ?: 587);
        $mail->CharSet    = 'UTF-8';

        $from = $smtp['smtp_from'] ?: $smtp['smtp_user'];
        $fromName = $smtp['smtp_from_name'] ?: 'CellVerse Admin';
        $mail->setFrom($from, $fromName);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'CellVerse - Password Reset Request';

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="margin:0;padding:0;background:#0f172a;font-family:Arial,sans-serif;">
            <div style="max-width:500px;margin:40px auto;background:#1e293b;border-radius:12px;overflow:hidden;border:1px solid #334155;">
                <div style="background:#6366f1;padding:24px;text-align:center;">
                    <h1 style="color:#fff;margin:0;font-size:1.3rem;">CellVerse Admin</h1>
                </div>
                <div style="padding:32px;color:#e2e8f0;">
                    <h2 style="margin:0 0 16px;color:#f8fafc;">Password Reset Request</h2>
                    <p style="color:#94a3b8;line-height:1.6;">We received a request to reset your admin password. Click the button below to set a new password:</p>
                    <div style="text-align:center;margin:28px 0;">
                        <a href="' . htmlspecialchars($resetUrl) . '" style="display:inline-block;padding:14px 32px;background:#6366f1;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Reset Password</a>
                    </div>
                    <p style="color:#64748b;font-size:0.85rem;line-height:1.6;">This link expires in <strong style="color:#f59e0b;">1 hour</strong>. If you did not request this, ignore this email.</p>
                </div>
                <div style="padding:16px 32px;border-top:1px solid #334155;text-align:center;">
                    <p style="color:#475569;font-size:0.8rem;margin:0;">CellVerse &copy; ' . date('Y') . ' &mdash; Wholesale Mobile Accessories</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = "CellVerse Admin - Password Reset\n\n"
            . "Click the link below to reset your password:\n"
            . $resetUrl . "\n\n"
            . "This link expires in 1 hour. If you did not request this, ignore this email.\n";

        $mail->send();
        return ['success' => true, 'message' => 'Reset link sent to your email.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to send email. Please try again later.'];
    }
}
