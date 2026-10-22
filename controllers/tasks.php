<?php
class TasksController {
    private $model;
    private $clientModel;
    private $typeModel;

    public function __construct() {
        Auth::requireLogin();
        $this->model = new Task();
        $this->clientModel = new Client();
        $this->typeModel = new TaskType();
    }

    public function actionIndex() {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'client_id' => $_GET['client_id'] ?? null,
            'task_type_id' => $_GET['task_type_id'] ?? null,
            'search' => $_GET['search'] ?? null,
        ];
        $filters = array_filter($filters);

        $tasks = $this->model->getAll($filters);
        $clients = $this->clientModel->getAll(null, true);
        $types = $this->typeModel->getAll(true);

        $currentPage = 'tasks';
        $pageTitle = 'Tareas';
        include BASE_PATH . 'views/tasks/index.php';
    }

    public function actionCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token inválido.');
                header('Location: ' . APP_URL . '/tasks'); exit;
            }
            $data = [
                'client_id' => $_POST['client_id'] ?? null,
                'user_id' => $_POST['user_id'] ?? Auth::id(),
                'task_type_id' => $_POST['task_type_id'] ?? null,
                'title' => Security::clean($_POST['title'] ?? ''),
                'description' => Security::clean($_POST['description'] ?? ''),
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'reminder_days' => $_POST['reminder_days'] ?? [],
            ];
            $result = $this->model->create($data);
            if ($result['success']) {
                Session::flash('success', 'Tarea creada correctamente.');
                header('Location: ' . APP_URL . '/tasks'); exit;
            } else {
                Session::flash('errors', $result['errors']);
                header('Location: ' . APP_URL . '/tasks/create'); exit;
            }
        }
        $task = null;
        $clients = $this->clientModel->getAll(null, true);
        $types = $this->typeModel->getAll(true);
        $users = Auth::isAdmin() ? (new User())->getAll() : [];
        $currentPage = 'tasks';
        $pageTitle = 'Nueva tarea';
        include BASE_PATH . 'views/tasks/form.php';
    }

    public function actionEdit($id) {
        $task = $this->model->find($id);
        if (!$task) {
            Session::flash('error', 'Tarea no encontrada.');
            header('Location: ' . APP_URL . '/tasks'); exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token inválido.');
                header('Location: ' . APP_URL . '/tasks/edit/' . $id); exit;
            }
            $data = [
                'client_id' => $_POST['client_id'] ?? null,
                'user_id' => $_POST['user_id'] ?? $task['user_id'],
                'task_type_id' => $_POST['task_type_id'] ?? null,
                'title' => Security::clean($_POST['title'] ?? ''),
                'description' => Security::clean($_POST['description'] ?? ''),
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'reminder_days' => $_POST['reminder_days'] ?? [],
                'status' => $_POST['status'] ?? $task['status'],
            ];
            $result = $this->model->update($id, $data);
            if ($result['success']) {
                Session::flash('success', 'Tarea actualizada.');
                header('Location: ' . APP_URL . '/tasks'); exit;
            } else {
                Session::flash('errors', $result['errors']);
                header('Location: ' . APP_URL . '/tasks/edit/' . $id); exit;
            }
        }
        $clients = $this->clientModel->getAll(null, true);
        $types = $this->typeModel->getAll(true);
        $users = Auth::isAdmin() ? (new User())->getAll() : [];
        $currentPage = 'tasks';
        $pageTitle = 'Editar tarea';
        include BASE_PATH . 'views/tasks/form.php';
    }

    public function actionView($id) {
        $task = $this->model->find($id);
        if (!$task) {
            Session::flash('error', 'Tarea no encontrada.');
            header('Location: ' . APP_URL . '/tasks'); exit;
        }
        $currentPage = 'tasks';
        $pageTitle = 'Detalle de tarea';
        include BASE_PATH . 'views/tasks/view.php';
    }

    public function actionArchive($id) {
        $this->model->archive($id);
        Session::flash('success', 'Tarea archivada.');
        header('Location: ' . APP_URL . '/tasks'); exit;
    }

    public function actionReactivate($id) {
        $this->model->reactivate($id);
        Session::flash('success', 'Tarea reactivada.');
        header('Location: ' . APP_URL . '/tasks'); exit;
    }

    public function actionCancel($id) {
        $this->model->cancel($id);
        Session::flash('success', 'Tarea cancelada.');
        header('Location: ' . APP_URL . '/tasks'); exit;
    }
}