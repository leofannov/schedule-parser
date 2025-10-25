<?php
require 'vendor/autoload.php';
require 'schedule_parser.php';

// Конфигурация
$excelUrl = 'http://spospk.ru/document/rasp/rasp.xlsx';
$cacheFile = 'cache/schedule.xlsx';
$scheduleCacheFile = 'cache/schedule_data.cache';

try {
    if (!file_exists('cache')) {
        mkdir('cache', 0755, true);
    }

    // Удаляем старый кэш
    if (file_exists($scheduleCacheFile) && !unlink($scheduleCacheFile)) {
        $backupName = $scheduleCacheFile . '.old_' . time();
        rename($scheduleCacheFile, $backupName);
    }

    // Скачиваем файл
    $context = stream_context_create([
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
        'http' => ['timeout' => 30, 'ignore_errors' => true]
    ]);

    $fileContent = file_get_contents($excelUrl, false, $context);
    if ($fileContent === false) {
        throw new Exception("Не удалось скачать файл.");
    }

    if (file_put_contents($cacheFile, $fileContent) === false) {
        throw new Exception("Не удалось сохранить файл.");
    }
    
    // Создаем кэш вручную
    $parser = new ScheduleParser();
    
    // Захватываем вывод метода displayFullSchedule
    ob_start();
    $parser->displayFullSchedule();
    $htmlContent = ob_get_clean();
    
    // Сохраняем в файл кэша
    if (file_put_contents($scheduleCacheFile, $htmlContent) === false) {
        throw new Exception("Не удалось сохранить кэш расписания.");
    }
    
    echo "Файл и кэш успешно обновлены: " . date('Y-m-d H:i:s');

} catch (Exception $e) {
    file_put_contents('cache/update_error.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
    die("Ошибка: " . $e->getMessage());
}
?>