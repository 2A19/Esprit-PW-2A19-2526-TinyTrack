<?php
/**
 * TinyTrack — Email sender via Gmail SMTP
 *
 * Credentials are loaded from config/secrets.php (gitignored).
 * Copy config/secrets.example.php -> config/secrets.php and fill in your values.
 */
require_once __DIR__ . '/secrets.php';

function envoyerMotDePasse($to, $prenom, $mot_de_passe, $role) {
    $smtp_user = SMTP_USER;
    $smtp_pass = SMTP_PASS;

    $roleLabel = ($role === 'educateur') ? 'Éducateur' : 'Parent';
    $subject = '=?UTF-8?B?' . base64_encode('TinyTrack - Votre mot de passe') . '?=';

    $body = '<html><body style="font-family:Arial,sans-serif;margin:0;padding:0;">';
    $body .= '<div style="max-width:500px;margin:20px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">';
    $body .= '<div style="background:linear-gradient(135deg,#4CAF50,#81C784);padding:24px;text-align:center;">';
    $body .= '<h1 style="color:#fff;font-size:24px;margin:0;">TinyTrack</h1>';
    $body .= '<p style="color:rgba(255,255,255,0.85);margin:4px 0 0;font-size:14px;">Chaque petit pas compte</p>';
    $body .= '</div>';
    $body .= '<div style="padding:24px;">';
    $body .= '<h2 style="color:#2D3436;font-size:18px;">Bonjour ' . htmlspecialchars($prenom) . ' !</h2>';
    $body .= '<p style="color:#555;font-size:15px;">Votre compte <strong>' . $roleLabel . '</strong> a ete active sur TinyTrack.</p>';
    $body .= '<p style="color:#555;font-size:15px;">Voici votre mot de passe :</p>';
    $body .= '<div style="background:#E8F5E9;border-radius:12px;padding:16px;text-align:center;margin:16px 0;">';
    $body .= '<div style="font-family:Courier New,monospace;font-size:28px;font-weight:bold;letter-spacing:3px;color:#2E7D32;">' . htmlspecialchars($mot_de_passe) . '</div>';
    $body .= '</div>';
    $body .= '<p style="color:#EF5350;font-size:13px;font-weight:bold;">Notez ce mot de passe, il ne vous sera plus communique.</p>';
    $body .= '</div>';
    $body .= '<div style="background:#f8f9fa;padding:12px;text-align:center;font-size:12px;color:#888;">TinyTrack 2026 - ESPRIT 2A19</div>';
    $body .= '</div></body></html>';

    // Connect via SSL
    $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
    $socket = @stream_socket_client('ssl://smtp.gmail.com:465', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
    if (!$socket) return false;

    fgets($socket, 515); // 220 greeting

    fputs($socket, "EHLO localhost\r\n");
    while ($line = fgets($socket, 515)) { if (substr($line, 3, 1) == ' ') break; }

    fputs($socket, "AUTH LOGIN\r\n");
    fgets($socket, 515);

    fputs($socket, base64_encode($smtp_user) . "\r\n");
    fgets($socket, 515);

    fputs($socket, base64_encode($smtp_pass) . "\r\n");
    $auth = fgets($socket, 515);
    if (substr($auth, 0, 3) != '235') { fclose($socket); return false; }

    fputs($socket, "MAIL FROM:<{$smtp_user}>\r\n");
    fgets($socket, 515);

    fputs($socket, "RCPT TO:<{$to}>\r\n");
    fgets($socket, 515);

    fputs($socket, "DATA\r\n");
    fgets($socket, 515);

    $msg  = "From: TinyTrack <{$smtp_user}>\r\n";
    $msg .= "To: {$to}\r\n";
    $msg .= "Subject: {$subject}\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=utf-8\r\n";
    $msg .= "\r\n";
    $msg .= $body . "\r\n.\r\n";

    fputs($socket, $msg);
    $result = fgets($socket, 515);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return (substr($result, 0, 3) == '250');
}

/**
 * Send a password-reset link by email.
 * Returns true on successful SMTP delivery, false otherwise.
 */
function envoyerLienReset($to, $prenom, $resetUrl) {
    $smtp_user = SMTP_USER;
    $smtp_pass = SMTP_PASS;

    $subject = '=?UTF-8?B?' . base64_encode('TinyTrack - Reinitialisation de votre mot de passe') . '?=';

    $body  = '<html><body style="font-family:Arial,sans-serif;margin:0;padding:0;background:#FFF9F0;">';
    $body .= '<div style="max-width:520px;margin:20px auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">';
    $body .= '<div style="background:linear-gradient(135deg,#FFE8EC 0%,#FFF4D6 55%,#E0F4F1 100%);padding:28px 24px;text-align:center;">';
    $body .= '<div style="font-size:42px;margin-bottom:4px;">&#x1F511;</div>';
    $body .= '<h1 style="color:#2D3436;font-size:24px;margin:0;font-family:Arial,sans-serif;">TinyTrack</h1>';
    $body .= '<p style="color:#555;margin:4px 0 0;font-size:14px;font-weight:600;">Chaque petit pas compte</p>';
    $body .= '</div>';
    $body .= '<div style="padding:24px;">';
    $body .= '<h2 style="color:#2D3436;font-size:18px;margin-top:0;">Bonjour ' . htmlspecialchars($prenom) . ',</h2>';
    $body .= '<p style="color:#555;font-size:15px;line-height:1.5;">Vous avez demande a reinitialiser votre mot de passe sur <strong>TinyTrack</strong>.</p>';
    $body .= '<p style="color:#555;font-size:15px;line-height:1.5;">Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>';
    $body .= '<div style="text-align:center;margin:24px 0;">';
    $body .= '<a href="' . htmlspecialchars($resetUrl) . '" style="display:inline-block;background:#26A69A;color:#fff;text-decoration:none;padding:14px 32px;border-radius:50px;font-weight:bold;font-size:15px;box-shadow:0 4px 0 #00796B;">Reinitialiser mon mot de passe</a>';
    $body .= '</div>';
    $body .= '<p style="color:#888;font-size:12px;line-height:1.5;">Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :<br>';
    $body .= '<span style="color:#26A69A;word-break:break-all;">' . htmlspecialchars($resetUrl) . '</span></p>';
    $body .= '<div style="background:#FFF3E0;border-radius:12px;padding:12px 16px;margin:20px 0 0;font-size:13px;color:#E65100;border:1px solid #FFE082;">';
    $body .= '<strong>&#x23F0; Ce lien est valable 30 minutes</strong> et ne peut etre utilise qu\'une seule fois.';
    $body .= '</div>';
    $body .= '<p style="color:#888;font-size:12px;margin-top:20px;line-height:1.5;">Si vous n\'etes pas a l\'origine de cette demande, ignorez simplement cet email — votre mot de passe restera inchange.</p>';
    $body .= '</div>';
    $body .= '<div style="background:#f8f9fa;padding:12px;text-align:center;font-size:12px;color:#888;">TinyTrack 2026 - ESPRIT 2A19</div>';
    $body .= '</div></body></html>';

    $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
    $socket = @stream_socket_client('ssl://smtp.gmail.com:465', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
    if (!$socket) return false;

    fgets($socket, 515);
    fputs($socket, "EHLO localhost\r\n");
    while ($line = fgets($socket, 515)) { if (substr($line, 3, 1) == ' ') break; }

    fputs($socket, "AUTH LOGIN\r\n"); fgets($socket, 515);
    fputs($socket, base64_encode($smtp_user) . "\r\n"); fgets($socket, 515);
    fputs($socket, base64_encode($smtp_pass) . "\r\n");
    $auth = fgets($socket, 515);
    if (substr($auth, 0, 3) != '235') { fclose($socket); return false; }

    fputs($socket, "MAIL FROM:<{$smtp_user}>\r\n"); fgets($socket, 515);
    fputs($socket, "RCPT TO:<{$to}>\r\n"); fgets($socket, 515);
    fputs($socket, "DATA\r\n"); fgets($socket, 515);

    $msg  = "From: TinyTrack <{$smtp_user}>\r\n";
    $msg .= "To: {$to}\r\n";
    $msg .= "Subject: {$subject}\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
    $msg .= $body . "\r\n.\r\n";

    fputs($socket, $msg);
    $result = fgets($socket, 515);
    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return (substr($result, 0, 3) == '250');
}
