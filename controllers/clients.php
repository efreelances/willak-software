<?php
class ClientsController {
    private $model;
    public function __construct() {
        Auth::requireLogin();
        $this->model = new Client();
    }

    public function actionIndex() {
        $clients = $this->model->getAll();
        $currentPage = 'clients';
        $pageTitle = 'Clientes';
        include BASE_PATH . 'views/clients/index.php';
    }

    public function actionCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token inválido.');
                header('Location: ' . APP_URL . '/clients'); exit;
            }
            $data = [
                'name' => Security::clean($_POST['name'] ?? ''),
                'document_type' => $_POST['document_type'] ?? 'DNI',
                'document' => Security::clean($_POST['document'] ?? ''),
                'email' => Security::clean($_POST['email'] ?? ''),
                'phone' => Security::clean($_POST['phone'] ?? ''),
                'address' => Security::clean($_POST['address'] ?? ''),
                'notes' => Security::clean($_POST['notes'] ?? ''),
                'user_id' => $_POST['user_id'] ?? Auth::id()
            ];
            $result = $this->model->create($data);
            if ($result['success']) {
                Session::flash('success', 'Cliente creado.');
                header('Location: ' . APP_URL . '/clients'); exit;
            } else {
                Session::flash('errors', $result['errors']);
                header('Location: ' . APP_URL . '/clients/create'); exit;
            }
        }
        $client = null;
        $users = Auth::isAdmin() ? (new User())->getAll() : [];
        $currentPage = 'clients';
        $pageTitle = 'Nuevo cliente';
        include BASE_PATH . 'views/clients/form.php';
    }

    public function actionEdit($id) {
        $client = $this->model->find($id);
        if (!$client) {
            Session::flash('error', 'Cliente no encontrado.');
            header('Location: ' . APP_URL . '/clients'); exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token inválido.');
                header('Location: ' . APP_URL . '/clients/edit/' . $id); exit;
            }
            $data = [
                'name' => Security::clean($_POST['name'] ?? ''),
                'document_type' => $_POST['document_type'] ?? 'DNI',
                'document' => Security::clean($_POST['document'] ?? ''),
                'email' => Security::clean($_POST['email'] ?? ''),
                'phone' => Security::clean($_POST['phone'] ?? ''),
                'address' => Security::clean($_POST['address'] ?? ''),
                'notes' => Security::clean($_POST['notes'] ?? ''),
                'status' => $_POST['status'] ?? 'activo',
                'user_id' => $_POST['user_id'] ?? $client['user_id']
            ];
            $result = $this->model->update($id, $data);
            if ($result['success']) {
                Session::flash('success', 'Cliente actualizado.');
                header('Location: ' . APP_URL . '/clients'); exit;
            } else {
                Session::flash('errors', $result['errors']);
                header('Location: ' . APP_URL . '/clients/edit/' . $id); exit;
            }
        }
        $users = Auth::isAdmin() ? (new User())->getAll() : [];
        $currentPage = 'clients';
        $pageTitle = 'Editar cliente';
        include BASE_PATH . 'views/clients/form.php';
    }

    public function actionView($id) {
        $client = $this->model->find($id);
        if (!$client) {
            Session::flash('error', 'Cliente no encontrado.');
            header('Location: ' . APP_URL . '/clients'); exit;
        }
        // Contar tareas del cliente
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE client_id = :c");
        $stmt->execute([':c' => $id]);
        $taskCount = $stmt->fetchColumn();

        $currentPage = 'clients';
        $pageTitle = 'Detalle de cliente';
        include BASE_PATH . 'views/clients/view.php';
    }

    public function actionDeactivate($id) {
        $this->model->deactivate($id);
        Session::flash('success', 'Cliente archivado.');
        header('Location: ' . APP_URL . '/clients'); exit;
    }

    public function actionReactivate($id) {
        $this->model->reactivate($id);
        Session::flash('success', 'Cliente reactivado.');
        header('Location: ' . APP_URL . '/clients'); exit;
    }
}