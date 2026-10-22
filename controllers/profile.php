<?php
class ProfileController {
    private $model;
    
    public function __construct() {
        Auth::requireLogin();
        $this->model = new User();
    }

    public function actionIndex() {
        $user = $this->model->find(Auth::id());
        
        if (!$user) {
            Session::flash('error', 'Usuario no encontrado.');
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token inválido.');
                header('Location: ' . APP_URL . '/profile');
                exit;
            }
            
            $data = [
                'username'  => Security::clean($_POST['username'] ?? ''),
                'email'     => Security::clean($_POST['email'] ?? ''),
                'full_name' => Security::clean($_POST['full_name'] ?? ''),
                'role'      => $user['role'], // No se puede cambiar el rol desde perfil
                'status'    => $user['status'], // No se puede cambiar el estado
                'password'  => $_POST['password'] ?? ''
            ];
            
            $result = $this->model->update(Auth::id(), $data);
            
            if ($result['success']) {
                // Actualizar sesión con los nuevos datos
                Session::set('user_name', $data['full_name']);
                Session::set('user_email', $data['email']);
                Session::set('user_username', $data['username']);
                
                Session::flash('success', 'Perfil actualizado correctamente.');
                header('Location: ' . APP_URL . '/profile');
                exit;
            } else {
                Session::flash('errors', $result['errors']);
                header('Location: ' . APP_URL . '/profile');
                exit;
            }
        }
        
        $currentPage = 'profile';
        $pageTitle = 'Mi Perfil';
        include BASE_PATH . 'views/profile/index.php';
    }
}