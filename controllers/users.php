<?php
class UsersController {
    private $model;
    
    public function __construct() {
        Auth::requireAdmin();
        $this->model = new User();
    }

    public function actionIndex() {
        $users = $this->model->getAll(true);
        $currentPage = 'users';
        $pageTitle = 'Usuarios';
        include BASE_PATH . 'views/users/index.php';
    }

    public function actionCreate() {
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token inválido.');
                header('Location: ' . APP_URL . '/users');
                exit;
            }
            
            $data = [
                'username'  => Security::clean($_POST['username'] ?? ''),
                'email'     => Security::clean($_POST['email'] ?? ''),
                'full_name' => Security::clean($_POST['full_name'] ?? ''),
                'role'      => $_POST['role'] ?? 'user',
                'password'  => $_POST['password'] ?? ''
            ];
            
            $result = $this->model->create($data);
            
            if ($result['success']) {
                Session::flash('success', 'Usuario creado correctamente.');
                header('Location: ' . APP_URL . '/users');
                exit;
            } else {
                Session::flash('errors', $result['errors']);
                Session::flash('form_data', $data);
                header('Location: ' . APP_URL . '/users/create');
                exit;
            }
        }
        
        // IMPORTANTE: $user debe ser NULL para creación
        $user = null;
        $currentPage = 'users';
        $pageTitle = 'Nuevo usuario';
        include BASE_PATH . 'views/users/form.php';
    }

    public function actionEdit($id) {
        $user = $this->model->find($id);
        
        if (!$user) {
            Session::flash('error', 'Usuario no encontrado.');
            header('Location: ' . APP_URL . '/users');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token inválido.');
                header('Location: ' . APP_URL . '/users/edit/' . $id);
                exit;
            }
            
            $data = [
                'username'  => Security::clean($_POST['username'] ?? ''),
                'email'     => Security::clean($_POST['email'] ?? ''),
                'full_name' => Security::clean($_POST['full_name'] ?? ''),
                'role'      => $_POST['role'] ?? 'user',
                'status'    => $_POST['status'] ?? 'activo',
                'password'  => $_POST['password'] ?? ''
            ];
            
            $result = $this->model->update($id, $data);
            
            if ($result['success']) {
                Session::flash('success', 'Usuario actualizado.');
                header('Location: ' . APP_URL . '/users');
                exit;
            } else {
                Session::flash('errors', $result['errors']);
                header('Location: ' . APP_URL . '/users/edit/' . $id);
                exit;
            }
        }
        
        $currentPage = 'users';
        $pageTitle = 'Editar usuario';
        include BASE_PATH . 'views/users/form.php';
    }

    public function actionView($id) {
        $user = $this->model->find($id);
        
        if (!$user) {
            Session::flash('error', 'Usuario no encontrado.');
            header('Location: ' . APP_URL . '/users');
            exit;
        }
        
        $currentPage = 'users';
        $pageTitle = 'Detalle de usuario';
        include BASE_PATH . 'views/users/view.php';
    }

    public function actionDeactivate($id) {
        if ($id == Auth::id()) {
            Session::flash('error', 'No puedes desactivar tu propio usuario.');
        } else {
            $this->model->deactivate($id);
            Session::flash('success', 'Usuario desactivado.');
        }
        header('Location: ' . APP_URL . '/users');
        exit;
    }

    public function actionReactivate($id) {
        $this->model->reactivate($id);
        Session::flash('success', 'Usuario reactivado.');
        header('Location: ' . APP_URL . '/users');
        exit;
    }

    public function actionResetPassword($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPwd = $_POST['new_password'] ?? '';
            $result = $this->model->resetPassword($id, $newPwd);
            
            if ($result['success']) {
                Session::flash('success', 'Contraseña restablecida.');
            } else {
                Session::flash('errors', $result['errors']);
            }
        }
        header('Location: ' . APP_URL . '/users/edit/' . $id);
        exit;
    }
}