<?php
// Контроллер для управления парсингом и рерайтингом новостей
class ParserController extends Controller {
    private $newsSource;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->newsSource = new NewsSource();
    }
    
    // Метод для отображения страницы управления парсингом
    public function index() {
        // Получаем активные источники
        $sources = $this->newsSource->getActiveSources();
        
        // Получаем последние логи парсинга
        $logs = $this->db->select("
            SELECT l.*, s.name as source_name 
            FROM parsing_logs l 
            JOIN news_sources s ON l.source_id = s.id 
            ORDER BY l.created_at DESC 
            LIMIT 10
        ");
        
        return $this->render('admin/parser/index', [
            'sources' => $sources,
            'logs' => $logs
        ]);
    }
    
    // Метод для запуска парсинга
    public function runParser($sourceId = null) {
        $pythonPath = PYTHON_PATH;
        $scriptPath = SCRIPTS_DIR . '/news_parser.py';
        
        $command = $pythonPath . ' ' . $scriptPath;
        
        if ($sourceId) {
            $command .= ' ' . $sourceId;
        }
        
        // Запускаем скрипт в фоновом режиме
        $this->runCommandInBackground($command);
        
        // Перенаправляем на страницу управления парсингом
        $this->redirect('/admin/parse.php?status=started');
    }
    
    // Метод для запуска рерайтинга
    public function runRewriter() {
        $pythonPath = PYTHON_PATH;
        $scriptPath = SCRIPTS_DIR . '/news_rewriter.py';
        
        $command = $pythonPath . ' ' . $scriptPath;
        
        // Запускаем скрипт в фоновом режиме
        $this->runCommandInBackground($command);
        
        // Перенаправляем на страницу управления парсингом
        $this->redirect('/admin/parse.php?status=rewrite_started');
    }
    
    // Метод для запуска команды в фоновом режиме
    private function runCommandInBackground($command) {
        if (substr(php_uname(), 0, 7) == "Windows") {
            pclose(popen("start /B " . $command, "r"));
        } else {
            exec($command . " > /dev/null 2>&1 &");
        }
    }
    
    // Метод для настройки cron-задания для периодического парсинга
    public function setupCron() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $interval = $_POST['interval'] ?? '60';
            
            // Путь к скрипту для cron
            $cronScriptPath = APP_ROOT . '/scripts/cron_setup.php';
            
            // Создаем файл для cron
            $cronContent = "<?php\n";
            $cronContent .= "// Скрипт для настройки cron-задания\n";
            $cronContent .= "// Интервал: каждые {$interval} минут\n\n";
            $cronContent .= "// Путь к PHP\n";
            $cronContent .= "\$phpPath = '/usr/bin/php';\n\n";
            $cronContent .= "// Путь к скрипту запуска парсера\n";
            $cronContent .= "\$parserScriptPath = '" . APP_ROOT . "/scripts/run_parser.php';\n\n";
            $cronContent .= "// Путь к скрипту запуска рерайтера\n";
            $cronContent .= "\$rewriterScriptPath = '" . APP_ROOT . "/scripts/run_rewriter.php';\n\n";
            $cronContent .= "// Команда для cron (запуск парсера)\n";
            $cronContent .= "\$cronCommand = \"*/{$interval} * * * * \$phpPath \$parserScriptPath > /dev/null 2>&1\";\n\n";
            $cronContent .= "// Команда для cron (запуск рерайтера, через 5 минут после парсера)\n";
            $cronContent .= "\$cronCommandRewriter = \"*/{$interval} * * * * sleep 300 && \$phpPath \$rewriterScriptPath > /dev/null 2>&1\";\n\n";
            $cronContent .= "// Получаем текущие cron-задания\n";
            $cronContent .= "\$currentCron = shell_exec('crontab -l') ?: '';\n\n";
            $cronContent .= "// Удаляем старые задания для наших скриптов\n";
            $cronContent .= "\$currentCron = preg_replace('/.*run_parser\.php.*\\n?/', '', \$currentCron);\n";
            $cronContent .= "\$currentCron = preg_replace('/.*run_rewriter\.php.*\\n?/', '', \$currentCron);\n\n";
            $cronContent .= "// Добавляем новые задания\n";
            $cronContent .= "\$newCron = \$currentCron . \"\\n\" . \$cronCommand . \"\\n\" . \$cronCommandRewriter . \"\\n\";\n\n";
            $cronContent .= "// Применяем новый crontab\n";
            $cronContent .= "\$tempFile = tempnam(sys_get_temp_dir(), 'cron');\n";
            $cronContent .= "file_put_contents(\$tempFile, \$newCron);\n";
            $cronContent .= "exec('crontab ' . \$tempFile);\n";
            $cronContent .= "unlink(\$tempFile);\n\n";
            $cronContent .= "echo \"Cron-задание успешно настроено. Парсинг будет выполняться каждые {$interval} минут.\";\n";
            
            file_put_contents($cronScriptPath, $cronContent);
            
            // Создаем скрипт для запуска парсера
            $parserRunnerPath = APP_ROOT . '/scripts/run_parser.php';
            $parserRunnerContent = "<?php\n";
            $parserRunnerContent .= "// Скрипт для запуска парсера из cron\n\n";
            $parserRunnerContent .= "// Путь к Python\n";
            $parserRunnerContent .= "\$pythonPath = '" . PYTHON_PATH . "';\n\n";
            $parserRunnerContent .= "// Путь к скрипту парсера\n";
            $parserRunnerContent .= "\$scriptPath = '" . SCRIPTS_DIR . "/news_parser.py';\n\n";
            $parserRunnerContent .= "// Запускаем парсер\n";
            $parserRunnerContent .= "exec(\$pythonPath . ' ' . \$scriptPath);\n";
            
            file_put_contents($parserRunnerPath, $parserRunnerContent);
            
            // Создаем скрипт для запуска рерайтера
            $rewriterRunnerPath = APP_ROOT . '/scripts/run_rewriter.php';
            $rewriterRunnerContent = "<?php\n";
            $rewriterRunnerContent .= "// Скрипт для запуска рерайтера из cron\n\n";
            $rewriterRunnerContent .= "// Путь к Python\n";
            $rewriterRunnerContent .= "\$pythonPath = '" . PYTHON_PATH . "';\n\n";
            $rewriterRunnerContent .= "// Путь к скрипту рерайтера\n";
            $rewriterRunnerContent .= "\$scriptPath = '" . SCRIPTS_DIR . "/news_rewriter.py';\n\n";
            $rewriterRunnerContent .= "// Запускаем рерайтер\n";
            $rewriterRunnerContent .= "exec(\$pythonPath . ' ' . \$scriptPath);\n";
            
            file_put_contents($rewriterRunnerPath, $rewriterRunnerContent);
            
            // Делаем скрипты исполняемыми
            chmod($cronScriptPath, 0755);
            chmod($parserRunnerPath, 0755);
            chmod($rewriterRunnerPath, 0755);
            
            // Запускаем скрипт настройки cron
            $output = shell_exec('php ' . $cronScriptPath . ' 2>&1');
            
            return $this->render('admin/parser/index', [
                'sources' => $this->newsSource->getActiveSources(),
                'logs' => $this->db->select("SELECT l.*, s.name as source_name FROM parsing_logs l JOIN news_sources s ON l.source_id = s.id ORDER BY l.created_at DESC LIMIT 10"),
                'success' => 'Периодический парсинг настроен. ' . $output
            ]);
        }
        
        return $this->render('admin/parser/cron', [
            'interval' => 60 // Значение по умолчанию - каждый час
        ]);
    }
}
?>
