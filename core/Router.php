<?php
class Router {
    public static function dispatch() {
        // 1. Obtener la URI limpia
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        // 2. Quitar el prefijo de la carpeta (ej: "willak")
        $basePath = trim(BASE_URL, '/');
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = trim($uri, '/');

        // 3. Fallback por query string (?r=login)
        if (empty($uri) && isset($_GET['r'])) {
            $uri = trim($_GET['r'], '/');
        }

        // 4. Valor por defecto
        if ($uri === '' || $uri === 'index.php') {
            $uri = 'dashboard';
        }

        $parts = explode('/', $uri);
        $rawModule = $parts[0] ?? 'dashboard';
        $action = $parts[1] ?? 'index';
        // /logout debe ejecutar actionLogout(); si cae en actionIndex(),
        // AuthController redirige a usuarios autenticados al dashboard.
        if ($rawModule === 'logout' && !isset($parts[1])) {
            $action = 'logout';
        }
        $param  = $parts[2] ?? null;

        // 5. MAPEO DE RUTAS A ARCHIVOS DE CONTROLADOR (¡LA CLAVE!)
        $routeMap = [
            'login'     => 'auth',
            'logout'    => 'auth',
            'dashboard' => 'dashboard',
            'users'     => 'users',
            'clients'   => 'clients',
            'tasks'     => 'tasks',
            'tasktypes' => 'tasktypes',
            'reports'   => 'reports',
            'stats'     => 'stats',
            'settings'  => 'settings',   // ← Cambiado de 'config' a 'settings'
            'config'    => 'settings',    // ← Redirección por compatibilidad
            'profile'   => 'profile'   // ← Agregar esta línea si no existe
        ];

        $module = $routeMap[$rawModule] ?? $rawModule;

        // 6. Rutas públicas (no requieren login)
        $publicRoutes = ['login', 'logout'];
        if (!in_array($rawModule, $publicRoutes)) {
            Auth::requireLogin();
        }

        // 7. Cargar controlador
        $controllerFile = BASE_PATH . "controllers/{$module}.php";
        
        if (!file_exists($controllerFile)) {
            http_response_code(404);
            echo "<div style='font-family: monospace; padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'>";
            echo "<h3>❌ Error 404: Archivo de controlador no encontrado</h3>";
            echo "<p><strong>Ruta buscada:</strong> <code>" . htmlspecialchars($controllerFile) . "</code></p>";
            echo "<p><strong>Módulo URL:</strong> <code>" . htmlspecialchars($rawModule) . "</code></p>";
            echo "<p><strong>Módulo mapeado a:</strong> <code>" . htmlspecialchars($module) . "</code></p>";
            echo "<p><strong>BASE_PATH:</strong> <code>" . htmlspecialchars(BASE_PATH) . "</code></p>";
            echo "</div>";
            return;
        }

        require_once $controllerFile;
        $controllerClass = ucfirst($module) . 'Controller';

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            die("<h3>❌ Error 500:</h3><p>La clase <code>$controllerClass</code> no existe en el archivo.</p>");
        }

        $controller = new $controllerClass();

        // Convertir "export-tasks" en "ExportTasks" (camelCase)
        $actionCamel = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
        $method = 'action' . $actionCamel;

        if (!method_exists($controller, $method)) {
            http_response_code(404);
            die("<h3>❌ Error 404:</h3><p>El método <code>$method</code> no existe en <code>$controllerClass</code>.</p>");
        }

        // Ejecutar la acción
        $controller->$method($param);
    }
}