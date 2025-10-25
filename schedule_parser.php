<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ScheduleParser {
    private $excelFile = 'cache/schedule.xlsx';
    private $cacheFile = 'cache/schedule_data.cache';
    private $configFile = 'config/week_config.php';
    
    private $ranges = [
        'even' => [
            'Понедельник' => ['schedule' => 'BX97:CA110', 'pair_numbers' => 'B97:B110', 'time' => 'D97:D110'],
            'Вторник'     => ['schedule' => 'BX111:CA124', 'pair_numbers' => 'B111:B124', 'time' => 'D111:D124'],
            'Среда'       => ['schedule' => 'BX125:CA138', 'pair_numbers' => 'B125:B138', 'time' => 'D125:D138'],
            'Четверг'     => ['schedule' => 'BX139:CA152', 'pair_numbers' => 'B139:B152', 'time' => 'D139:D152'],
            'Пятница'     => ['schedule' => 'BX153:CA166', 'pair_numbers' => 'B153:B166', 'time' => 'D153:D166'],
            'Суббота'     => ['schedule' => 'BX167:CA180', 'pair_numbers' => 'B167:B180', 'time' => 'D167:D180']
        ],
        'odd' => [
            'Понедельник' => ['schedule' => 'BX10:CA23', 'pair_numbers' => 'B10:B23', 'time' => 'D10:D23'],
            'Вторник'     => ['schedule' => 'BX24:CA37', 'pair_numbers' => 'B24:B37', 'time' => 'D24:D37'],
            'Среда'       => ['schedule' => 'BX38:CA51', 'pair_numbers' => 'B38:B51', 'time' => 'D38:D51'],
            'Четверг'     => ['schedule' => 'BX52:CA65', 'pair_numbers' => 'B52:B65', 'time' => 'D52:D65'],
            'Пятница'     => ['schedule' => 'BX66:CA79', 'pair_numbers' => 'B66:B79', 'time' => 'D66:D79'],
            'Суббота'     => ['schedule' => 'BX80:CA93', 'pair_numbers' => 'B80:B93', 'time' => 'D80:D93']
        ]
    ];

    public function displayFullSchedule(): void {
        try {
            if (!file_exists('cache')) {
                mkdir('cache', 0755, true);
            }

            if (file_exists($this->cacheFile)) {
                readfile($this->cacheFile);
                return;
            }
            
            $currentWeekType = $this->getWeekType();
            $output = $this->generateSchedule($currentWeekType);
            file_put_contents($this->cacheFile, $output);
            echo $output;

        } catch (Exception $e) {
            $this->renderError('Ошибка загрузки расписания: ' . $e->getMessage());
        }
    }

    private function generateSchedule(string $currentWeekType): string {
        ob_start();
        
        $this->renderHeader();
        $spreadsheet = $this->loadSpreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $merges = $worksheet->getMergeCells();
        
        echo '<div class="week-switcher">
            <button class="week-btn '.($currentWeekType === 'even' ? 'active' : '').'" data-week="even">Чётная неделя</button>
            <button class="week-btn '.($currentWeekType === 'odd' ? 'active' : '').'" data-week="odd">Нечётная неделя</button>
            <div class="current-week-indicator">Текущая неделя: '.($currentWeekType === 'even' ? 'чётная' : 'нечётная').'</div>
        </div>';
        
        foreach (['even', 'odd'] as $weekType) {
            $display = $weekType === $currentWeekType ? 'block' : 'none';
            echo '<div class="week-schedule" id="week-'.$weekType.'" style="display:'.$display.'">';
            
            foreach ($this->ranges[$weekType] as $dayName => $dayRanges) {
                echo '<section class="day-schedule">';
                echo '<h3 class="day-header">'.$dayName.'</h3>';
                
                $this->renderDaySchedule($worksheet, $dayRanges, $merges);
                echo '</section>';
            }
            
            echo '</div>';
        }
        
        $this->renderFooter();
        $this->renderScripts();
        
        return ob_get_clean();
    }

    private function getWeekType(): string {
        // Загружаем конфигурацию
        $config = $this->loadConfig();
        
        if ($config && isset($config['base_week_type']) && isset($config['base_week_number'])) {
            // Вычисляем разницу в неделях от базовой
            $currentWeek = date('W');
            $weeksDifference = $currentWeek - $config['base_week_number'];
            
            // Определяем тип недели с учетом чередования
            if ($config['base_week_type'] === 'even') {
                return ($weeksDifference % 2 === 0) ? 'even' : 'odd';
            } else {
                return ($weeksDifference % 2 === 0) ? 'odd' : 'even';
            }
        }
        
        // Если конфига нет - используем календарную неделю
        return (date('W') % 2 === 0) ? 'even' : 'odd';
    }

    private function loadConfig(): ?array {
        if (!file_exists($this->configFile)) {
            return null;
        }
        
        return include $this->configFile;
    }

    private function renderDaySchedule($worksheet, $dayRanges, $merges): void {
        echo '<div class="schedule-table-container">';
        echo '<table class="exact-schedule">';
        echo '<thead><tr><th>№</th><th>Время</th><th>Дисциплина</th></tr></thead>';
        echo '<tbody>';
        
        // Получаем данные для всех колонок
        $pairNumbers = $worksheet->rangeToArray($dayRanges['pair_numbers'], null, true, true, false);
        $timeData = $worksheet->rangeToArray($dayRanges['time'], null, true, true, false);
        $scheduleData = $worksheet->rangeToArray($dayRanges['schedule'], null, true, true, false);
        
        foreach ($scheduleData as $rowNum => $row) {
            $pair = $pairNumbers[$rowNum][0] ?? '';
            $time = $timeData[$rowNum][0] ?? '';
            
            // Получаем все непустые ячейки в строке
            $content = [];
            foreach ($row as $cell) {
                $trimmed = trim($cell ?? '');
                if ($trimmed !== '') {
                    $content[] = $trimmed;
                }
            }
            
            // Если нет данных - пропускаем строку
            if (empty($content)) {
                continue;
            }
            
            // Объединяем все ячейки с переносами строк
            $discipline = implode("\n", $content);
            
            echo '<tr>
                <td class="pair-number">'.htmlspecialchars($pair, ENT_QUOTES, 'UTF-8').'</td>
                <td class="time-cell">'.htmlspecialchars($time, ENT_QUOTES, 'UTF-8').'</td>
                <td class="discipline-cell">'.nl2br(htmlspecialchars($discipline, ENT_QUOTES, 'UTF-8')).'</td>
            </tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    private function loadSpreadsheet(): \PhpOffice\PhpSpreadsheet\Spreadsheet {
        if (!file_exists($this->excelFile)) {
            throw new Exception('Файл расписания не найден');
        }
        return IOFactory::load($this->excelFile);
    }

    private function renderHeader(): void {
        echo '<!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Расписание занятий (Отключение сайта произойдет 01.11.2025 в 00:00 в связи с вводом бота в Телеграм)</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #ffffff;
                    color: #333333;
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .week-switcher {
                    display: flex;
                    gap: 10px;
                    margin: 20px 0;
                    align-items: center;
                    flex-wrap: wrap;
                }
                .week-btn {
                    padding: 8px 16px;
                    border: none;
                    border-radius: 4px;
                    background: #f0f0f0;
                    cursor: pointer;
                    font-weight: bold;
                }
                .week-btn.active {
                    background: #4CAF50;
                    color: white;
                }
                .current-week-indicator {
                    margin-left: auto;
                    font-weight: bold;
                    color: #4CAF50;
                }
                .day-schedule {
                    margin-bottom: 30px;
                }
                .day-header {
                    color: #4CAF50;
                    margin-bottom: 15px;
                    padding-bottom: 5px;
                    border-bottom: 2px solid #4CAF50;
                }
                .schedule-table-container {
                    overflow-x: auto;
                }
                .exact-schedule {
                    width: 100%;
                    border-collapse: collapse;
                }
                .exact-schedule th, .exact-schedule td {
                    border: 1px solid #dddddd;
                    padding: 8px;
                    text-align: left;
                }
                .exact-schedule th {
                    background-color: #f2f2f2;
                }
                .pair-number {
                    font-weight: bold;
                    background-color: #f8f8f8;
                    width: 30px;
                    text-align: center;
                }
                .time-cell {
                    width: 80px;
                    text-align: center;
                }
                .discipline-cell {
                    white-space: pre-wrap;
                    word-wrap: break-word;
                }
                .error-message {
                    color: #f44336;
                    margin: 20px;
                    padding: 15px;
                    border: 1px solid #f44336;
                    border-radius: 4px;
                    display: flex;
                    align-items: center;
                }
                .config-info {
                    margin: 10px 0;
                    padding: 10px;
                    background: #f8f8f8;
                    border-radius: 4px;
                    font-size: 14px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <header class="schedule-header">
                    <h1 style="color: #4CAF50;">Расписание занятий (Полное прекращение работы сайта произойдет 01.11.2025 в 00:00 в связи с вводом бота в Телеграм)</h1>
                    <div class="day-info">
                        <span id="current-date"></span>
                    </div>
                </header>
                <main class="schedule-container">';
    }

    private function renderFooter(): void {
        echo '</main>
            <footer class="schedule-footer" style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; color: #666;">
                Последнее обновление: '.date('d.m.Y H:i').'
            </footer>
        </div>';
    }

    private function renderScripts(): void {
        echo '<script>
            document.getElementById("current-date").textContent = new Date().toLocaleDateString("ru-RU", {
                day: "numeric", month: "long", year: "numeric"
            });

            // Переключение недель
            document.querySelectorAll(".week-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    document.querySelectorAll(".week-btn").forEach(b => b.classList.remove("active"));
                    this.classList.add("active");
                    
                    document.querySelectorAll(".week-schedule").forEach(schedule => {
                        schedule.style.display = "none";
                    });
                    
                    document.getElementById("week-" + this.dataset.week).style.display = "block";
                });
            });
        </script>
        </body>
        </html>';
    }

    private function renderError(string $message): void {
        echo '<div class="error-message">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" style="margin-right: 10px;">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
            <span>'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</span>
        </div>';
    }
}