<?php
class DashboardController {
    public function __construct() { Auth::requireLogin(); }

    public function actionIndex() {
        $db = Database::getInstance()->getConnection();
        $userId = Auth::id();
        $isAdmin = Auth::isAdmin();

        $userFilter = $isAdmin ? '' : "AND t.user_id = $userId";

        // KPIs
        $stats = [];
        $stats['total_tasks'] = $db->query("SELECT COUNT(*) FROM tasks t WHERE 1=1 $userFilter")->fetchColumn();
        $stats['pending'] = $db->query("SELECT COUNT(*) FROM tasks t WHERE t.status='pendiente' $userFilter")->fetchColumn();
        $stats['in_progress'] = $db->query("SELECT COUNT(*) FROM tasks t WHERE t.status='en_proceso' $userFilter")->fetchColumn();
        $stats['due_soon'] = $db->query("SELECT COUNT(*) FROM tasks t WHERE t.status IN ('activo','pendiente','en_proceso') AND t.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) $userFilter")->fetchColumn();
        $stats['overdue'] = $db->query("SELECT COUNT(*) FROM tasks t WHERE t.status IN ('activo','pendiente','en_proceso') AND t.end_date < CURDATE() $userFilter")->fetchColumn();
        $stats['total_clients'] = $db->query("SELECT COUNT(*) FROM clients c WHERE c.status='activo' " . ($isAdmin?'':"AND c.user_id=$userId"))->fetchColumn();

        // Próximos vencimientos
        $upcoming = $db->query("SELECT t.*, c.name AS client_name 
                                FROM tasks t 
                                INNER JOIN clients c ON c.id = t.client_id 
                                WHERE t.status IN ('activo','pendiente','en_proceso') 
                                AND t.end_date >= CURDATE() 
                                $userFilter
                                ORDER BY t.end_date ASC LIMIT 5")->fetchAll();

        $currentPage = 'dashboard';
        $pageTitle = 'Dashboard';
        include BASE_PATH . 'views/dashboard/index.php';
    }
}