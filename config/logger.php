<?php
/**
 * Logger sencillo: registra cambios en logs/changes.log
 * Uso: require_once __DIR__ . '/logger.php'; log_change($userId, $action, $detailsArray);
 */
function ensure_logs_dir() {
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir;
}

function log_change($userId, $action, $details = []) {
    try {
        $dir = ensure_logs_dir();
        $file = $dir . '/changes.log';
        $entry = [
            'ts' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'action' => $action,
            'details' => $details
        ];
        file_put_contents($file, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
        return true;
    } catch (Exception $e) {
        error_log('Logger error: ' . $e->getMessage());
        return false;
    }
}

?>