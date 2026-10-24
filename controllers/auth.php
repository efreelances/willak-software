<?php
class AuthController {
    private $auth;
    public function __construct() { $this->auth = new Auth(); }

    public function actionIndex() {
        if (Auth::check()) { header('Location: ' . APP_URL . '/dashboard'); exit; }
        $this->actionLogin();
    }

    public function actionLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
                Session::flash('error', 'Token de seguridad inválido.');
                header('Location: ' . APP_URL . '/login'); exit;
            }
            $username = Security::clean($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = !empty($_POST['remember']);

            if (empty($username) || empty($password)) {
                $errors = ['Complete todos los campos.'];
                $old = ['username' => $username];
                include BASE_PATH . 'views/auth/login.php';
                return;
            }

            $result = $this->auth->attempt($username, $password, $remember);
            if ($result['success']) {
                header('Location: ' . APP_URL . '/dashboard'); exit;
            } elseif (!empty($result['require_2fa'])) {
                header('Location: ' . APP_URL . '/login/2fa'); exit;
            } else {
                $errors = [$result['message']];
                $old = ['username' => $username];
                include BASE_PATH . 'views/auth/login.php';
                return;
            }
        }
        include BASE_PATH . 'views/auth/login.php';
    }

    public function actionLogout() {
        $this->auth->logout();
        
        // Forzar redirección
        header('Location: ' . APP_URL . '/login?logged_out=1');
        exit;
    }
}
  