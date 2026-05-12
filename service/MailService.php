<?php
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailService {

    private array $cfg;

    public function __construct() {
        $this->cfg = require __DIR__ . '/../config/mail.php';
    }

    // Initialise et configure PHPMailer avec les paramètres SMTP
    private function mailer(): PHPMailer {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $this->cfg['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->cfg['username'];
        $mail->Password   = $this->cfg['password'];
        $mail->SMTPSecure = $this->cfg['encryption'];
        $mail->Port       = $this->cfg['port'];
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($this->cfg['from'], $this->cfg['from_name']);
        // Désactive la vérification SSL (utile sur XAMPP local)
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]
        ];
        return $mail;
    }

    // Envoie un accusé de réception au client après soumission d'une réclamation
    public function sendConfirmation(string $toEmail, string $toName, string $sujet, string $description, int $id): bool {
        try {
            $mail = $this->mailer();
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = "✅ Réclamation reçue — TinyTrack #$id";
            $mail->Body    = $this->templateConfirmation($toName, $sujet, $description, $id);
            $mail->AltBody = "Bonjour $toName, votre réclamation #$id a bien été reçue. Sujet : $sujet.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("[MailService] Confirmation failed: " . $e->getMessage());
            return false;
        }
    }

    // Notifie le client par email quand l'admin répond à sa réclamation
    public function sendReponseNotification(string $toEmail, string $toName, string $sujet, string $message, int $idRec): bool {
        try {
            $mail = $this->mailer();
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = "💬 Réponse à votre réclamation — TinyTrack #$idRec";
            $mail->Body    = $this->templateReponse($toName, $sujet, $message, $idRec);
            $mail->AltBody = "Bonjour $toName, votre réclamation #$idRec a reçu une réponse : $message";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("[MailService] Réponse notification failed: " . $e->getMessage());
            return false;
        }
    }

    // Template HTML — accusé de réception
    private function templateConfirmation(string $name, string $sujet, string $description, int $id): string {
        $date = date('d/m/Y à H:i');
        return <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
        <body style="margin:0;padding:0;background:#f4f6f9;font-family:'Segoe UI',Arial,sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 0;">
            <tr><td align="center">
              <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                <!-- En-tête -->
                <tr>
                  <td style="background:linear-gradient(135deg,#4CAF50,#81C784);padding:36px 40px;text-align:center;">
                    <div style="font-size:2.2rem;font-weight:900;color:#ffffff;letter-spacing:-0.5px;">🌱 TinyTrack</div>
                    <div style="color:rgba(255,255,255,0.85);font-size:0.95rem;margin-top:6px;">Gestion des réclamations</div>
                  </td>
                </tr>

                <!-- Corps -->
                <tr>
                  <td style="padding:40px;">

                    <!-- Icône succès -->
                    <div style="text-align:center;margin-bottom:24px;">
                      <div style="display:inline-block;background:#e8f5e9;border-radius:50%;width:72px;height:72px;line-height:72px;font-size:2rem;">✅</div>
                    </div>

                    <h2 style="margin:0 0 8px;color:#2D3436;font-size:1.4rem;text-align:center;">Réclamation bien reçue !</h2>
                    <p style="color:#636e72;text-align:center;margin:0 0 32px;font-size:0.95rem;">Bonjour <strong>$name</strong>, votre demande a été enregistrée avec succès.</p>

                    <!-- Badge numéro ticket -->
                    <div style="text-align:center;margin-bottom:28px;">
                      <span style="background:#4CAF50;color:#fff;padding:8px 24px;border-radius:100px;font-weight:700;font-size:1rem;letter-spacing:0.5px;">Ticket #$id</span>
                    </div>

                    <!-- Détails réclamation -->
                    <div style="background:#f8fdf8;border:1px solid #c8e6c9;border-radius:14px;padding:24px;margin-bottom:28px;">
                      <div style="margin-bottom:14px;">
                        <span style="font-size:0.75rem;text-transform:uppercase;color:#81C784;font-weight:700;letter-spacing:0.08em;">Sujet</span>
                        <div style="font-size:1rem;color:#2D3436;font-weight:600;margin-top:4px;">$sujet</div>
                      </div>
                      <div style="margin-bottom:14px;">
                        <span style="font-size:0.75rem;text-transform:uppercase;color:#81C784;font-weight:700;letter-spacing:0.08em;">Description</span>
                        <div style="font-size:0.9rem;color:#555;margin-top:4px;line-height:1.6;">$description</div>
                      </div>
                      <div>
                        <span style="font-size:0.75rem;text-transform:uppercase;color:#81C784;font-weight:700;letter-spacing:0.08em;">Date</span>
                        <div style="font-size:0.9rem;color:#555;margin-top:4px;">$date</div>
                      </div>
                    </div>

                    <!-- Statut -->
                    <div style="background:#fff3e0;border-radius:12px;padding:16px 20px;display:flex;align-items:center;margin-bottom:28px;">
                      <span style="font-size:1.3rem;margin-right:12px;">⏳</span>
                      <div>
                        <div style="font-weight:700;color:#E65100;font-size:0.9rem;">Statut actuel : En attente</div>
                        <div style="color:#795548;font-size:0.82rem;">Notre équipe vous répondra dans les plus brefs délais.</div>
                      </div>
                    </div>

                    <p style="color:#636e72;font-size:0.88rem;line-height:1.7;margin:0;">
                      Vous recevrez un email dès qu'une réponse sera apportée à votre réclamation.<br>
                      Conservez la référence <strong>#$id</strong> pour tout suivi.
                    </p>
                  </td>
                </tr>

                <!-- Pied de page -->
                <tr>
                  <td style="background:#f8fdf8;padding:24px 40px;text-align:center;border-top:1px solid #e8f5e9;">
                    <div style="color:#4CAF50;font-weight:800;font-size:1rem;margin-bottom:4px;">🌱 TinyTrack</div>
                    <div style="color:#aaa;font-size:0.78rem;">© 2026 ESPRIT 2A19 — Chaque petit pas compte</div>
                  </td>
                </tr>

              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;
    }

    // Template HTML — notification de réponse admin
    private function templateReponse(string $name, string $sujet, string $message, int $idRec): string {
        $date    = date('d/m/Y à H:i');
        $msgHtml = nl2br(htmlspecialchars($message));
        return <<<HTML
        <!DOCTYPE html>
        <html lang="fr">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
        <body style="margin:0;padding:0;background:#f4f6f9;font-family:'Segoe UI',Arial,sans-serif;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 0;">
            <tr><td align="center">
              <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                <!-- En-tête -->
                <tr>
                  <td style="background:linear-gradient(135deg,#5B9BD5,#90CAF9);padding:36px 40px;text-align:center;">
                    <div style="font-size:2.2rem;font-weight:900;color:#ffffff;letter-spacing:-0.5px;">🌱 TinyTrack</div>
                    <div style="color:rgba(255,255,255,0.85);font-size:0.95rem;margin-top:6px;">Gestion des réclamations</div>
                  </td>
                </tr>

                <!-- Corps -->
                <tr>
                  <td style="padding:40px;">

                    <div style="text-align:center;margin-bottom:24px;">
                      <div style="display:inline-block;background:#e3f2fd;border-radius:50%;width:72px;height:72px;line-height:72px;font-size:2rem;">💬</div>
                    </div>

                    <h2 style="margin:0 0 8px;color:#2D3436;font-size:1.4rem;text-align:center;">Votre réclamation a été traitée !</h2>
                    <p style="color:#636e72;text-align:center;margin:0 0 28px;font-size:0.95rem;">Bonjour <strong>$name</strong>, l'équipe TinyTrack a répondu à votre demande.</p>

                    <div style="text-align:center;margin-bottom:28px;">
                      <span style="background:#5B9BD5;color:#fff;padding:8px 24px;border-radius:100px;font-weight:700;font-size:1rem;">Ticket #$idRec</span>
                    </div>

                    <!-- Réclamation d'origine -->
                    <div style="margin-bottom:20px;">
                      <div style="font-size:0.75rem;text-transform:uppercase;color:#90CAF9;font-weight:700;letter-spacing:0.08em;margin-bottom:6px;">Votre réclamation</div>
                      <div style="background:#f8f9fa;border-radius:10px;padding:14px 18px;color:#555;font-size:0.9rem;border-left:4px solid #90CAF9;">$sujet</div>
                    </div>

                    <!-- Réponse admin -->
                    <div style="margin-bottom:28px;">
                      <div style="font-size:0.75rem;text-transform:uppercase;color:#5B9BD5;font-weight:700;letter-spacing:0.08em;margin-bottom:6px;">Réponse de notre équipe</div>
                      <div style="background:#e3f2fd;border-radius:12px;padding:20px;color:#1a237e;font-size:0.95rem;line-height:1.7;border-left:4px solid #5B9BD5;">$msgHtml</div>
                    </div>

                    <!-- Statut traité -->
                    <div style="background:#e8f5e9;border-radius:12px;padding:16px 20px;margin-bottom:28px;">
                      <span style="font-size:1.2rem;margin-right:10px;">✅</span>
                      <span style="font-weight:700;color:#2E7D32;font-size:0.9rem;">Statut mis à jour : Traité — $date</span>
                    </div>

                    <p style="color:#636e72;font-size:0.88rem;line-height:1.7;margin:0;">
                      Si cette réponse ne vous satisfait pas, vous pouvez soumettre une nouvelle réclamation sur notre portail.
                    </p>
                  </td>
                </tr>

                <!-- Pied de page -->
                <tr>
                  <td style="background:#f8fdf8;padding:24px 40px;text-align:center;border-top:1px solid #e8f5e9;">
                    <div style="color:#4CAF50;font-weight:800;font-size:1rem;margin-bottom:4px;">🌱 TinyTrack</div>
                    <div style="color:#aaa;font-size:0.78rem;">© 2026 ESPRIT 2A19 — Chaque petit pas compte</div>
                  </td>
                </tr>

              </table>
            </td></tr>
          </table>
        </body>
        </html>
        HTML;
    }
}
