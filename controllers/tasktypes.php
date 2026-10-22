<?php
class TasktypesController {
    private $model;
    public function __construct() {
        Auth::requireLogin();
        $this->model = new TaskType();
    }

    public function actionIndex() {
        $types = $this->model->getAll(true);
        $currentPage = 'tasktypes';
        $pageTitle = 'Tipos de Tarea';
        include BASE_PATH . 'views/tasktypes/index.php';
    }

    public function actionCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token inválido.');
                header('Location: ' . APP_URL . '/tasktypes'); exit;
            }
            $data = [
                'name' => Security::clean($_POST['name'] ?? ''),
                'description' => Security::clean($_POST['description'] ?? ''),
            ];
            $result = $this->model->create($data);
            if ($result['success']) {
                Session::flash('success', 'Tipo de tarea creado.');
                header('Location: ' . APP_URL . '/tasktypes'); exit;
            } else {
                Session::flash('errors', $result['errors']);
                header('Location: ' . APP_URL . '/tasktypes/create'); exit;
            }
        }
        $type = null;
        $currentPage = 'tasks';
        $pageTitle = 'Nuevo tipo de tarea';
        include BASE_PATH . 'views/tasktypes/form.php';
    }

    public function actionEdit($id) {
        $type = $this->model->find($id);
        if (!$type) {
            Session::flash('error', 'Tipo no encontrado.');
            header('Location: ' . APP_URL . '/tasktypes'); exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token inválido.');
                header('Location: ' . APP_URL . '/tasktypes/edit/' . $id); exit;
            }
            $data = [
                'name' => Security::clean($_POST['name'] ?? ''),
                'description' => Security::clean($_POST['description'] ?? ''),
                'status' => $_POST['status'] ?? 'activo',
            ];
            $result = $this->model->update($id, $data);
            if ($result['success']) {
                Session::flash('success', 'Tipo actualizado.');
                header('Location: ' . APP_URL . '/tasktypes'); exit;
            } else {
                Session::flash('errors', $result['errors']);
                header('Location: ' . APP_URL . '/tasktypes/edit/' . $id); exit;
            }
        }
        $currentPage = 'tasks';
        $pageTitle = 'Editar tipo de tarea';
        include BASE_PATH . 'views/tasktypes/form.php';
    }

    public function actionDeactivate($id) {
        $this->model->deactivate($id);
        Session::flash('success', 'Tipo desactivado.');
        header('Location: ' . APP_URL . '/tasktypes'); exit;
    }

    public function actionReactivate($id) {
        $this->model->reactivate($id);
        Session::flash('success', 'Tipo reactivado.');
        header('Location: ' . APP_URL . '/tasktypes'); exit;
    }
}