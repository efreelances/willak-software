<?php
class StatsController {
    private $db;
    
    public function __construct() {
        Auth::requireLogin();
        $this->db = Database::getInstance()->getConnection();
    }

    public function actionIndex() {
        $userId = Auth::id();
        $isAdmin = Auth::isAdmin();
        
        // Filtros de usuario
        $userFilter = $isAdmin ? '' : "AND user_id = $userId";
        
        // 1. Total de tareas por estado
        $tasksByStatus = $this->db->query("
            SELECT status, COUNT(*) as total 
            FROM tasks 
            WHERE 1=1 $userFilter
            GROUP BY status
        ")->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // 2. Total de tareas por tipo
        $tasksByType = $this->db->query("
            SELECT tt.name, COUNT(t.id) as total
            FROM task_types tt
            LEFT JOIN tasks t ON tt.id = t.task_type_id
            WHERE tt.status = 'activo'
            GROUP BY tt.id, tt.name
            ORDER BY total DESC
        ")->fetchAll();
        
        // 3. Total de tareas por cliente (top 10)
        $tasksByClient = $this->db->query("
            SELECT c.name, COUNT(t.id) as total
            FROM clients c
            LEFT JOIN tasks t ON c.id = t.client_id
            WHERE c.status = 'activo'
            GROUP BY c.id, c.name
            ORDER BY total DESC
            LIMIT 10
        ")->fetchAll();
        
        // 4. Tareas vencidas vs por vencer
        $overdue = $this->db->query("
            SELECT COUNT(*) FROM tasks 
            WHERE status IN ('activo','pendiente','en_proceso') 
            AND end_date < CURDATE() $userFilter
        ")->fetchColumn();
        
        $dueSoon = $this->db->query("
            SELECT COUNT(*) FROM tasks 
            WHERE status IN ('activo','pendiente','en_proceso') 
            AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) $userFilter
        ")->fetchColumn();
        
        $onTime = $this->db->query("
            SELECT COUNT(*) FROM tasks 
            WHERE status IN ('activo','pendiente','en_proceso') 
            AND end_date > DATE_ADD(CURDATE(), INTERVAL 7 DAY) $userFilter
        ")->fetchColumn();
        
        // 5. Total de clientes
        $totalClients = $this->db->query("
            SELECT COUNT(*) FROM clients 
            WHERE status = 'activo' $userFilter
        ")->fetchColumn();
        
        // 6. Total de usuarios (solo admin)
        $totalUsers = $isAdmin ? $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'activo'")->fetchColumn() : 0;
        
        // 7. Tareas creadas por mes (últimos 6 meses)
        $tasksByMonth = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total
            FROM tasks
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ")->fetchAll();
        
        // Preparar datos para gráficos
        $chartData = [
            'tasksByStatus' => $tasksByStatus,
            'tasksByType' => $tasksByType,
            'tasksByClient' => $tasksByClient,
            'timeline' => [
                'overdue' => (int)$overdue,
                'dueSoon' => (int)$dueSoon,
                'onTime' => (int)$onTime
            ],
            'tasksByMonth' => $tasksByMonth
        ];
        
        $currentPage = 'stats';
        $pageTitle = 'Estadísticas';
        include BASE_PATH . 'views/stats/index.php';
    }
}