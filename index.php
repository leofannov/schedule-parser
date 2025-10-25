<?php
require 'vendor/autoload.php';
require 'schedule_parser.php';

try {
    // Создаем и запускаем парсер
    $parser = new ScheduleParser();
    $parser->displayFullSchedule();
    
} catch (Exception $e) {
    // Выводим полноценную HTML-страницу с ошибкой
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка - Расписание занятий</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #f8f8f8;
                color: #333;
            }
            .error-container {
                max-width: 800px;
                margin: 50px auto;
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .error-message {
                color: #f44336;
                padding: 20px;
                border: 1px solid #f44336;
                border-radius: 4px;
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .error-title {
                color: #f44336;
                margin-bottom: 20px;
            }
            .error-actions {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #eee;
            }
            .btn {
                padding: 10px 20px;
                background: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin-right: 10px;
            }
            .btn:hover {
                background: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1 class="error-title">Ошибка загрузки расписания</h1>
            
            <div class="error-message">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32">
                    <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <div>
                    <strong>'.htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8').'</strong>
                    <p style="margin-top: 10px; color: #666; font-size: 14px;">
                        Проверьте наличие файла расписания в папке cache/ и права доступа.
                    </p>
                </div>
            </div>
            
            <div class="error-actions">
                <a href="'.htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8').'" class="btn">Обновить страницу</a>
                <a href="/" class="btn">На главную</a>
            </div>
            
            <div style="margin-top: 20px; font-size: 12px; color: #888;">
                <strong>Техническая информация:</strong><br>
                Ошибка в файле: '.htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8').'<br>
                Строка: '.$e->getLine().'<br>
                Время: '.date('d.m.Y H:i:s').'
            </div>
        </div>
    </body>
    </html>';
    
    // Логируем ошибку для администратора
    error_log('Schedule Parser Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}
?>

<!-- Добавляем гиперссылку "Расписание звонков" внизу страницы -->
<div style="position: fixed; bottom: 20px; right: 20px;">
    <a href="rasp.php" style="display: inline-block; padding: 12px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
        Расписание звонков
    </a>
</div>