<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';

class ApprobationController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Approve a pending user account and send credentials by email.
     */
    public function approuver($id) {
        $id = (int)$id;

        $this->db->prepare("UPDATE user SET statut = 'actif' WHERE id = :id AND statut = 'en_attente'")->execute([':id' => $id]);

        $stmt = $this->db->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        if ($user) {
            // For parents, get their enfant codes for the email
            $enfantCodes = [];
            if ($user['role'] === 'parent') {
                $stmt = $this->db->prepare("SELECT code_unique, prenom FROM enfant WHERE parent_id = :pid");
                $stmt->execute([':pid' => $id]);
                $enfantCodes = $stmt->fetchAll();
            }
            $this->envoyerEmailApprobation($user, $enfantCodes);
            $this->db->prepare("UPDATE user SET mdp_temp = NULL WHERE id = :id")->execute([':id' => $id]);
        }
    }

    /**
     * Reject (delete) a pending user account.
     */
    public function rejeter($id) {
        $id = (int)$id;
        $this->db->prepare("DELETE FROM user WHERE id = :id AND statut = 'en_attente'")->execute([':id' => $id]);
    }

    /**
     * List all pending user accounts.
     */
    public function listerEnAttente() {
        $stmt = $this->db->query("SELECT * FROM user WHERE statut = 'en_attente' ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * List recently approved active accounts (non-admin).
     */
    public function listerActifs() {
        $stmt = $this->db->query("SELECT * FROM user WHERE statut = 'actif' AND role != 'admin' ORDER BY created_at DESC LIMIT 10");
        return $stmt->fetchAll();
    }

    /**
     * Send approval email with credentials via SMTP.
     */
    private function envoyerEmailApprobation($user, $enfantCodes = []) {
        $roleLabel = ($user['role'] === 'educateur') ? 'Éducateur' : 'Parent';
        $code = $user['code_unique'];
        $subject = '=?UTF-8?B?' . base64_encode('TinyTrack - Compte approuvé !') . '?=';

        $body = '<html><body style="font-family:Arial,sans-serif;margin:0;padding:0;">';
        $body .= '<div style="max-width:500px;margin:20px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">';
        $body .= '<div style="background:linear-gradient(135deg,#4CAF50,#81C784);padding:24px;text-align:center;">';
        $body .= '<h1 style="color:#fff;font-size:24px;margin:0;">TinyTrack</h1>';
        $body .= '<p style="color:rgba(255,255,255,0.85);margin:4px 0 0;font-size:14px;">Chaque petit pas compte</p>';
        $body .= '</div>';
        $body .= '<div style="padding:24px;">';
        $body .= '<h2 style="color:#2D3436;font-size:18px;">Bonjour ' . htmlspecialchars($user['prenom']) . ' !</h2>';
        $body .= '<p style="color:#555;font-size:15px;">Votre compte <strong>' . $roleLabel . '</strong> a ete approuve.</p>';

        // For educateur: show their code
        if ($user['role'] === 'educateur') {
            $body .= '<div style="background:#E8F5E9;border-radius:12px;padding:16px;margin:16px 0;">';
            $body .= '<p style="margin:0 0 8px;font-size:14px;color:#555;"><strong>Votre identifiant :</strong></p>';
            $body .= '<div style="font-family:Courier New,monospace;font-size:22px;font-weight:bold;color:#2E7D32;letter-spacing:2px;">' . htmlspecialchars($code) . '</div>';
            $body .= '</div>';
        }

        // For parent: show enfant codes
        if ($user['role'] === 'parent' && !empty($enfantCodes)) {
            $body .= '<div style="background:#E3F2FD;border-radius:12px;padding:16px;margin:16px 0;">';
            $body .= '<p style="margin:0 0 8px;font-size:14px;color:#555;"><strong>Code(s) de connexion (enfant) :</strong></p>';
            foreach ($enfantCodes as $ec) {
                $body .= '<div style="font-family:Courier New,monospace;font-size:20px;font-weight:bold;color:#1565C0;letter-spacing:2px;margin:4px 0;">' . htmlspecialchars($ec['code_unique']) . ' <span style="font-size:12px;color:#888;">(' . htmlspecialchars($ec['prenom']) . ')</span></div>';
            }
            $body .= '<p style="margin:8px 0 0;font-size:12px;color:#888;">Utilisez un de ces codes pour vous connecter en tant que parent.</p>';
            $body .= '</div>';
        }

        $body .= '<div style="background:#FFF3E0;border-radius:12px;padding:16px;margin:12px 0;">';
        $body .= '<p style="margin:0 0 8px;font-size:14px;color:#555;"><strong>Mot de passe :</strong></p>';
        $body .= '<div style="font-family:Courier New,monospace;font-size:20px;font-weight:bold;color:#E65100;letter-spacing:2px;">' . htmlspecialchars($user['mdp_temp'] ?? '(choisi a l\'inscription)') . '</div>';
        $body .= '</div>';
        $body .= '<p style="color:#555;font-size:14px;">Connectez-vous sur TinyTrack avec ces coordonnees.</p>';
        $body .= '</div>';
        $body .= '<div style="background:#f8f9fa;padding:12px;text-align:center;font-size:12px;color:#888;">TinyTrack 2026 - ESPRIT 2A19</div>';
        $body .= '</div></body></html>';

        $smtp_user = SMTP_USER;
        $smtp_pass = SMTP_PASS;

        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $socket = @stream_socket_client('ssl://smtp.gmail.com:465', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
        if ($socket) {
            fgets($socket, 515);
            fputs($socket, "EHLO localhost\r\n");
            while ($line = fgets($socket, 515)) { if (substr($line, 3, 1) == ' ') break; }
            fputs($socket, "AUTH LOGIN\r\n"); fgets($socket, 515);
            fputs($socket, base64_encode($smtp_user) . "\r\n"); fgets($socket, 515);
            fputs($socket, base64_encode($smtp_pass) . "\r\n"); fgets($socket, 515);
            fputs($socket, "MAIL FROM:<{$smtp_user}>\r\n"); fgets($socket, 515);
            fputs($socket, "RCPT TO:<{$user['email']}>\r\n"); fgets($socket, 515);
            fputs($socket, "DATA\r\n"); fgets($socket, 515);
            $msg = "From: TinyTrack <{$smtp_user}>\r\nTo: {$user['email']}\r\nSubject: {$subject}\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=utf-8\r\n\r\n{$body}\r\n.\r\n";
            fputs($socket, $msg); fgets($socket, 515);
            fputs($socket, "QUIT\r\n"); fclose($socket);
        }
    }
}
