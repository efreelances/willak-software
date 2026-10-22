<?php
class ReportsController {
    private $db;
    
    public function __construct() {
        Auth::requireLogin();
        $this->db = Database::getInstance()->getConnection();
    }

    public function actionIndex() {
        $currentPage = 'reports';
        $pageTitle = 'Reportes';
        include BASE_PATH . 'views/reports/index.php';
    }

    public function actionTasks() {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'client_id' => $_GET['client_id'] ?? null,
            'task_type_id' => $_GET['task_type_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        $filters = array_filter($filters);

        $tasks = $this->getTasksReport($filters);
        $clients = $this->getClients();
        $types = $this->getTaskTypes();

        $currentPage = 'reports';
        $pageTitle = 'Reporte de Tareas';
        include BASE_PATH . 'views/reports/tasks.php';
    }

    public function actionClients() {
        $clients = $this->getClientsReport();
        $currentPage = 'reports';
        $pageTitle = 'Reporte de Clientes';
        include BASE_PATH . 'views/reports/clients.php';
    }

    private function getTasksReport($filters) {
        $sql = "SELECT t.*, c.name AS client_name, tt.name AS type_name, u.full_name AS owner_name
                FROM tasks t
                LEFT JOIN clients c ON c.id = t.client_id
                LEFT JOIN task_types tt ON tt.id = t.task_type_id
                LEFT JOIN users u ON u.id = t.user_id
                WHERE 1=1";
        
        if (!Auth::isAdmin()) {
            $sql .= " AND t.user_id = " . (int)Auth::id();
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = '" . $filters['status'] . "'";
        }
        if (!empty($filters['client_id'])) {
            $sql .= " AND t.client_id = " . (int)$filters['client_id'];
        }
        if (!empty($filters['task_type_id'])) {
            $sql .= " AND t.task_type_id = " . (int)$filters['task_type_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.end_date >= '" . $filters['date_from'] . "'";
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND t.end_date <= '" . $filters['date_to'] . "'";
        }

        $sql .= " ORDER BY t.end_date ASC";
        return $this->db->query($sql)->fetchAll();
    }

    private function getClients() {
        $sql = "SELECT id, name FROM clients WHERE status = 'activo' ORDER BY name ASC";
        if (!Auth::isAdmin()) {
            $sql .= " AND user_id = " . (int)Auth::id();
        }
        return $this->db->query($sql)->fetchAll();
    }

    private function getTaskTypes() {
        return $this->db->query("SELECT id, name FROM task_types WHERE status = 'activo' ORDER BY name ASC")->fetchAll();
    }

    private function getClientsReport() {
        $sql = "SELECT c.*, u.full_name AS owner_name,
                (SELECT COUNT(*) FROM tasks WHERE client_id = c.id) AS total_tasks
                FROM clients c
                LEFT JOIN users u ON u.id = c.user_id
                WHERE 1=1";
        
        if (!Auth::isAdmin()) {
            $sql .= " AND c.user_id = " . (int)Auth::id();
        }
        
        $sql .= " ORDER BY c.name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /* exportar */
        public function actionExportTasks() {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'client_id' => $_GET['client_id'] ?? null,
            'task_type_id' => $_GET['task_type_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
        ];
        $filters = array_filter($filters);

        $tasks = $this->getTasksReport($filters);
        $format = $_GET['format'] ?? 'csv';

        if ($format === 'csv' || $format === 'excel') {
            $this->exportToCSV($tasks, 'reporte_tareas', $format);
        }
    }

    public function actionExportClients() {
        $clients = $this->getClientsReport();
        $format = $_GET['format'] ?? 'csv';

        if ($format === 'csv' || $format === 'excel') {
            $this->exportClientsToCSV($clients, 'reporte_clientes', $format);
        }
    }

    private function exportToCSV($data, $filename, $format = 'csv') {
        $extension = $format === 'excel' ? 'xls' : 'csv';
        $mimeType = $format === 'excel' 
            ? 'application/vnd.ms-excel' 
            : 'text/csv';

        header('Content-Type: ' . $mimeType . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.' . $extension . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $output = fopen('php://output', 'w');
        
        // BOM para Excel (soporta UTF-8)
        if ($format === 'excel') {
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        }

        // Encabezados
        fputcsv($output, [
            'ID', 'Título', 'Cliente', 'Tipo', 
            'Fecha Inicio', 'Fecha Fin', 'Estado', 'Responsable'
        ], ';');

        // Datos
        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['title'],
                $row['client_name'] ?? '-',
                $row['type_name'] ?? 'Sin tipo',
                date('d/m/Y', strtotime($row['start_date'])),
                date('d/m/Y', strtotime($row['end_date'])),
                ucfirst(str_replace('_', ' ', $row['status'])),
                $row['owner_name'] ?? '-'
            ], ';');
        }

        fclose($output);
        exit;
    }

    private function exportClientsToCSV($data, $filename, $format = 'csv') {
        $extension = $format === 'excel' ? 'xls' : 'csv';
        $mimeType = $format === 'excel' 
            ? 'application/vnd.ms-excel' 
            : 'text/csv';

        header('Content-Type: ' . $mimeType . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.' . $extension . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $output = fopen('php://output', 'w');
        
        if ($format === 'excel') {
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        }

        fputcsv($output, [
            'ID', 'Nombre', 'Tipo Documento', 'Documento',
            'Email', 'Teléfono', 'Total Tareas', 'Propietario', 'Estado'
        ], ';');

        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['document_type'] ?? '-',
                $row['document'] ?? '-',
                $row['email'] ?? '-',
                $row['phone'] ?? '-',
                (int)$row['total_tasks'],
                $row['owner_name'] ?? '-',
                ucfirst($row['status'])
            ], ';');
        }

        fclose($output);
        exit;
    }
}