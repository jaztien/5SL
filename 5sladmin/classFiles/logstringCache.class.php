<?php
class LogTemplateCache {
    // 快取檔案存放路徑（請確保該目錄有寫入權限）
    private static $cache_file = __DIR__ . '/log_templates_cache.php';
    private static $templates = null;

    // 取得所有樣板陣列（優先從記憶體或檔案讀取）
    private static function load($pdo) {
        // 1. 如果在同一次請求中已經載入過記憶體，直接回傳
        if (self::$templates !== null) {
            return self::$templates;
        }

        // 2. 如果快取檔案存在，直接 require 進來（Opcache 會讓這裡變成記憶體級速度）
        if (file_exists(self::$cache_file)) {
            self::$templates = require(self::$cache_file);
            return self::$templates;
        }

        // 3. 如果連快取檔案都不存在（第一次執行），觸發重新整理
        return self::refresh($pdo);
    }

    // 重新從 MsSQL 撈取全部樣板，並寫入 PHP 快取檔案
    public static function refresh($pdo) {
        // 使用 PDO::FETCH_KEY_PAIR 直接轉成 [id => text] 格式
        $sql = "SELECT template_id, template_text FROM log_templates";
        self::$templates = $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);

        // 將陣列轉化為標準的 PHP 原始碼字串
        $file_content = "<?php\n// 自動生成的樣板快取檔案 - " . date('Y-m-d H:i:s') . "\nreturn " . var_export(self::$templates, true) . ";";
        
        // 寫入檔案（使用 LOCK_EX 確保多執行緒安全）
        file_put_contents(self::$cache_file, $file_content, LOCK_EX);

        return self::$templates;
    }

    // 💡 外部呼叫的主方法：精準、快速取得樣板文字
    public static function get($pdo, $template_id) {
        $templates = self::load($pdo);

        // 如果快取裡有，直接回傳
        if (isset($templates[$template_id])) {
            return $templates[$template_id];
        }

        // 關鍵防錯：因為樣板只會增加，如果快取沒有，代表資料庫增加了新樣板！
        // 馬上重整快取檔案
        $templates = self::refresh($pdo);

        return isset($templates[$template_id]) ? $templates[$template_id] : null;
    }
}
?>
