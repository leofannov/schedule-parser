<?php
// rasp.php - Страница с расписанием звонков
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расписание звонков</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f8f8;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .schedule-image {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            display: block;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 4px;
            background: white;
        }
        .description {
            text-align: center;
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .back-btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Расписание звонков</h1>
        
        <div class="description">
            Актуальное расписание звонков на текущий семестр
        </div>

        <img src="images/rasp_mon.jpg" alt="Расписание звонков на ПОНЕДЕЛЬНИК" class="schedule-image">
        <img src="images/rasp.jpg" alt="Расписание звонков" class="schedule-image">

        <div style="text-align: center;">
            <a href="index.php" class="back-btn">Вернуться к расписанию</a>
        </div>
    </div>
</body>
</html>