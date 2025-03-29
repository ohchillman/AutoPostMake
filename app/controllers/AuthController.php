<?php
// Контроллер для авторизации
class AuthController extends Controller {
    private $user;
    
    public function __construct() {
        parent::__construct();
        $this->user = new User();
    }
    
    // Метод для отображения страницы входа
    public function showLoginPage() {
        return $this->render('auth/login');
    }
    
    // Метод для обработки входа
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                return $this->render('auth/login', [
                    'error' => 'Пожалуйста, введите имя пользователя и пароль'
                ]);
            }
            
            if ($this->user->login($username, $password)) {
                $this->redirect('/admin/index.php');
            } else {
                return $this->render('auth/login', [
                    'error' => 'Неверное имя пользователя или пароль'
                ]);
            }
        }
        
        return $this->render('auth/login');
    }
    
    // Метод для выхода из системы
    public function logout() {
        $this->user->logout();
        $this->redirect('/login.php');
    }
    
    // Метод для создания первого пользователя (админа)
    public function createInitialAdmin() {
        // Проверяем, есть ли уже пользователи в системе
        $users = $this->db->select("SELECT COUNT(*) as count FROM users");
        if ($users[0]['count'] == 0) {
            // Если пользователей нет, создаем админа
            $this->user->createUser('admin', 'admin123');
            return true;
        }
        return false;
    }
}
?>
