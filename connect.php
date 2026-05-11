<?php
// В Docker имя хоста — это название сервиса из docker-compose.yml
define('DB_HOST', 'db'); 
define('DB_USER', 'root');
define('DB_PASS', 'admin'); 
define('DB_NAME', 'ex_db'); // Убедись, что имя базы совпадает с тем, что в docker-compose

function getDBConnection() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Ошибка подключения: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4");
    }
    return $conn;
}

// Безопасная обертка для кэша
class CacheWrapper {
    private $redis = null;
    private $enabled = false;

    public function __construct() {
        // ПРОВЕРКА: Существует ли класс Redis в системе?
        if (class_exists('Redis')) {
            $this->redis = new Redis();
            try {
                if (@$this->redis->connect('127.0.0.1', 6379)) {
                    $this->enabled = true;
                }
            } catch (Exception $e) {
                $this->enabled = false;
            }
        }
    }

    public function get($key) {
        if (!$this->enabled) return null;
        $data = $this->redis->get($key);
        return $data ? json_decode($data, true) : null;
    }

    public function set($key, $value, $ttl = 3600) {
        if (!$this->enabled) return false;
        return $this->redis->setex($key, $ttl, json_encode($value));
    }
}

$conn = getDBConnection();
$cache = new CacheWrapper();
?>