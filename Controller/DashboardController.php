<?php
require_once __DIR__ . '/../config/db.php';

class DashboardController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
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
        try { $stats['totalEvents'] = $this->db->query("SELECT COUNT(*) FROM evenement")->fetchColumn(); } catch (Exception $e) {}
        try { $stats['totalRapports'] = $this->db->query("SELECT COUNT(*) FROM rapport")->fetchColumn(); } catch (Exception $e) {}

        return $stats;
    }
}
