<?php
/**
 * TinyTrack — Email sender via Gmail SMTP
 */

function envoyerMotDePasse($to, $prenom, $mot_de_passe, $role) {
    $smtp_user = 'eya.belhajmabrrouk@gmail.com';
    $smtp_pass = 'owclpimeecveksjf';

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
