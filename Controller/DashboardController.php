<?php
require_once __DIR__ . '/../config/db.php';

class DashboardController extends Controller {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // GET /dashboard or GET /dashboard/admin
    public function admin(): void {
        $this->requireAuth();
        $this->render('FrontOffice/dashboard', [
            'stats' => $this->getStats(),
        ]);
    }

    // GET /dashboard/educateur
    public function educateur(): void {
        $this->requireAuth();
        require_once __DIR__ . '/EducateurController.php';
        $eduCtrl = new EducateurController();
        $userId = $_SESSION['user_id'];

        $groupe = $eduCtrl->getGroupe($userId);
        $enfants = $groupe ? $eduCtrl->getEnfantsGroupe($groupe['id']) : [];

        $this->render('FrontOffice/dashboard_educateur', [
            'groupe'   => $groupe,
            'enfants'  => $enfants,
            'profil'   => $eduCtrl->getProfil($userId),
            'parents'  => $eduCtrl->getParentsContacts($userId),
            'rapports' => $eduCtrl->getRapportsEducateur($userId),
        ]);
    }

    public function getStats() {
        $stats = [];

        $stats['totalEnfants'] = $this->db->query("SELECT COUNT(*) FROM enfant WHERE statut = 'actif'")->fetchColumn();
        $stats['totalEducateurs'] = $this->db->query("SELECT COUNT(*) FROM user WHERE role = 'educateur' AND statut = 'actif'")->fetchColumn();
        $stats['totalParents'] = $this->db->query("SELECT COUNT(*) FROM user WHERE role = 'parent' AND statut = 'actif'")->fetchColumn();
        $stats['pendingAccounts'] = $this->db->query("SELECT COUNT(*) FROM user WHERE statut = 'en_attente'")->fetchColumn();
        $stats['totalGroupes'] = $this->db->query("SELECT COUNT(*) FROM groupe")->fetchColumn();
        $stats['unreadMessages'] = $this->db->query("SELECT COUNT(*) FROM message WHERE lu = 0")->fetchColumn();

        $stats['totalEvents'] = 0;
        $stats['totalRapports'] = 0;
        $stats['totalActivites'] = 0;
        try { $stats['totalEvents']    = $this->db->query("SELECT COUNT(*) FROM evenement")->fetchColumn(); } catch (Exception $e) {}
        try { $stats['totalRapports']  = $this->db->query("SELECT COUNT(*) FROM rapport")->fetchColumn();  } catch (Exception $e) {}
        try { $stats['totalActivites']    = $this->db->query("SELECT COUNT(*) FROM activite")->fetchColumn();     } catch (Exception $e) {}
        try { $stats['totalReclamations'] = $this->db->query("SELECT COUNT(*) FROM reclamations")->fetchColumn(); } catch (Exception $e) { $stats['totalReclamations'] = 0; }

        return $stats;
    }
}
