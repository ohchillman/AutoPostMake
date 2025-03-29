<?php
// Класс для работы с источниками новостей
class NewsSource {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllSources() {
        return $this->db->select("SELECT * FROM news_sources ORDER BY name");
    }

    public function getActiveSourcesCount() {
        $result = $this->db->selectOne("SELECT COUNT(*) as count FROM news_sources WHERE active = 1");
        return $result['count'];
    }

    public function getSourceById($id) {
        return $this->db->selectOne("SELECT * FROM news_sources WHERE id = :id", ['id' => $id]);
    }

    public function addSource($name, $url, $parser_type, $selector = null) {
        return $this->db->insert('news_sources', [
            'name' => $name,
            'url' => $url,
            'parser_type' => $parser_type,
            'selector' => $selector,
            'active' => 1
        ]);
    }

    public function updateSource($id, $data) {
        $this->db->update('news_sources', $data, 'id = :id', ['id' => $id]);
    }

    public function deleteSource($id) {
        $this->db->delete('news_sources', 'id = :id', ['id' => $id]);
    }

    public function toggleSourceStatus($id, $active) {
        $this->db->update('news_sources', ['active' => $active], 'id = :id', ['id' => $id]);
    }

    public function getActiveSources() {
        return $this->db->select("SELECT * FROM news_sources WHERE active = 1");
    }
}
?>
