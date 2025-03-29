<?php
// Контроллер для публичной части сайта
class HomeController extends Controller {
    private $newsSource;
    
    public function __construct() {
        parent::__construct();
        $this->newsSource = new NewsSource();
    }
    
    // Метод для отображения главной страницы
    public function index() {
        // Получаем количество активных источников
        $activeSourcesCount = $this->newsSource->getActiveSourcesCount();
        
        // Получаем последние новости
        $news = $this->db->select("
            SELECT n.*, s.name as source_name 
            FROM news n 
            JOIN news_sources s ON n.source_id = s.id 
            ORDER BY n.published_at DESC 
            LIMIT 20
        ");
        
        // Получаем количество рерайтов для каждой новости
        foreach ($news as &$item) {
            $rewriteCount = $this->db->selectOne("
                SELECT COUNT(*) as count 
                FROM news_rewrites 
                WHERE news_id = :news_id
            ", ['news_id' => $item['id']]);
            
            $item['rewrite_count'] = $rewriteCount['count'];
        }
        
        // Получаем последние логи парсинга
        $lastParsing = $this->db->selectOne("
            SELECT created_at 
            FROM parsing_logs 
            WHERE status = 'success' 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        return $this->render('home/index', [
            'activeSourcesCount' => $activeSourcesCount,
            'news' => $news,
            'lastParsing' => $lastParsing ? $lastParsing['created_at'] : null
        ]);
    }
    
    // Метод для получения рерайтов новости
    public function getRewrites($newsId) {
        // Получаем информацию о новости
        $news = $this->db->selectOne("
            SELECT n.*, s.name as source_name 
            FROM news n 
            JOIN news_sources s ON n.source_id = s.id 
            WHERE n.id = :id
        ", ['id' => $newsId]);
        
        if (!$news) {
            return json_encode(['error' => 'Новость не найдена']);
        }
        
        // Получаем рерайты новости
        $rewrites = $this->db->select("
            SELECT * 
            FROM news_rewrites 
            WHERE news_id = :news_id 
            ORDER BY created_at
        ", ['news_id' => $newsId]);
        
        return json_encode([
            'news' => $news,
            'rewrites' => $rewrites
        ]);
    }
}
?>
