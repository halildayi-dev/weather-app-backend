<?php
require_once __DIR__ . '/app/Controllers/WeatherController.php';

$controller = new WeatherController();

$endpoint = $_GET['endpoint'] ?? '';

switch($endpoint) {
    case 'weather/today':
        $controller->today(date("Y-m-d")); // bugünün tarihi
        break;
    case 'weather/tomorrow':
        $controller->tomorrow(date("Y-m-d", strtotime("+1 day"))); // yarının tarihi
        break;
    case 'weather/ten-days':
        $controller->tenDays();
        break;
    default:
        echo json_encode(["error" => "Endpoint not found"]);
        break;
}