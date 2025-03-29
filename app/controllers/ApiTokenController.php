<?php
// Контроллер для управления API токенами
class ApiTokenController extends Controller {
    private $apiToken;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->apiToken = new ApiToken();
    }
    
    // Метод для отображения страницы настроек токенов
    public function index() {
        $makeComToken = $this->apiToken->getTokenByService('make.com');
        return $this->render('admin/tokens/index', [
            'makeComToken' => $makeComToken
        ]);
    }
    
    // Метод для сохранения токена make.com
    public function saveMakeComToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            
            if (empty($token)) {
                return $this->render('admin/tokens/index', [
                    'error' => 'Пожалуйста, введите токен'
                ]);
            }
            
            $this->apiToken->addToken('make.com', $token);
            return $this->render('admin/tokens/index', [
                'success' => 'Токен успешно сохранен',
                'makeComToken' => $this->apiToken->getTokenByService('make.com')
            ]);
        }
    }
    
    // Метод для удаления токена
    public function deleteToken($id) {
        $this->apiToken->deleteToken($id);
        $this->redirect('/admin/tokens.php');
    }
}
?>
