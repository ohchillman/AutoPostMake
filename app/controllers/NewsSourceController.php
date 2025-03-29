<?php
// Контроллер для управления источниками новостей
class NewsSourceController extends Controller {
    private $newsSource;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->newsSource = new NewsSource();
    }
    
    // Метод для отображения списка источников
    public function index() {
        $sources = $this->newsSource->getAllSources();
        return $this->render('admin/sources/index', [
            'sources' => $sources
        ]);
    }
    
    // Метод для отображения формы добавления источника
    public function create() {
        return $this->render('admin/sources/create');
    }
    
    // Метод для сохранения нового источника
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $url = $_POST['url'] ?? '';
            $parser_type = $_POST['parser_type'] ?? '';
            $selector = $_POST['selector'] ?? null;
            
            if (empty($name) || empty($url) || empty($parser_type)) {
                return $this->render('admin/sources/create', [
                    'error' => 'Пожалуйста, заполните все обязательные поля',
                    'data' => $_POST
                ]);
            }
            
            $this->newsSource->addSource($name, $url, $parser_type, $selector);
            $this->redirect('/admin/sources.php');
        }
    }
    
    // Метод для отображения формы редактирования источника
    public function edit($id) {
        $source = $this->newsSource->getSourceById($id);
        if (!$source) {
            $this->redirect('/admin/sources.php');
        }
        
        return $this->render('admin/sources/edit', [
            'source' => $source
        ]);
    }
    
    // Метод для обновления источника
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $url = $_POST['url'] ?? '';
            $parser_type = $_POST['parser_type'] ?? '';
            $selector = $_POST['selector'] ?? null;
            $active = isset($_POST['active']) ? 1 : 0;
            
            if (empty($name) || empty($url) || empty($parser_type)) {
                $source = $this->newsSource->getSourceById($id);
                return $this->render('admin/sources/edit', [
                    'error' => 'Пожалуйста, заполните все обязательные поля',
                    'source' => $source
                ]);
            }
            
            $this->newsSource->updateSource($id, [
                'name' => $name,
                'url' => $url,
                'parser_type' => $parser_type,
                'selector' => $selector,
                'active' => $active
            ]);
            
            $this->redirect('/admin/sources.php');
        }
    }
    
    // Метод для удаления источника
    public function delete($id) {
        $this->newsSource->deleteSource($id);
        $this->redirect('/admin/sources.php');
    }
    
    // Метод для изменения статуса источника (активен/неактивен)
    public function toggleStatus($id) {
        $source = $this->newsSource->getSourceById($id);
        if ($source) {
            $newStatus = $source['active'] ? 0 : 1;
            $this->newsSource->toggleSourceStatus($id, $newStatus);
        }
        $this->redirect('/admin/sources.php');
    }
}
?>
