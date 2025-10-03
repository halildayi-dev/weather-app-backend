<?php
require_once __DIR__ . '/../Models/WeatherModel.php';

class WeatherController {

    private $model;

    public function __construct() {
        $this->model = new WeatherModel();
    }

    public function today($date) {
        $currentHour = $this->model->getCurrentHourData($date);
        $hourlyForecast = $this->model->getHourlyForecast($date);
        $weeklyAvgTemp = $this->model->getWeeklyAvgTemp();
        $dailyRainChance = $this->model->getDailyRainChance($date);
        $sunriseSunset = $this->model->getSunriseSunset($date);

        $response = [
            'currentHour' => $currentHour,
            'hourlyForecast' => $hourlyForecast,
            'weeklyAvgTemp' => $weeklyAvgTemp,
            'dailyRainChance' => $dailyRainChance,
            'sunriseSunset' => $sunriseSunset
        ];

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    public function tomorrow($date) {
        $currentHour = $this->model->getCurrentHourData($date);
        $hourlyForecast = $this->model->getHourlyForecast($date);
        $weeklyAvgTemp = $this->model->getWeeklyAvgTemp();
        $dailyRainChance = $this->model->getDailyRainChance($date);
        $sunriseSunset = $this->model->getSunriseSunset($date);

        $response = [
            'currentHour' => $currentHour,
            'hourlyForecast' => $hourlyForecast,
            'weeklyAvgTemp' => $weeklyAvgTemp,
            'dailyRainChance' => $dailyRainChance,
            'sunriseSunset' => $sunriseSunset
        ];

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    public function tenDays() {
        $tenDaysData = $this->model->getTenDays();

        header('Content-Type: application/json');
        echo json_encode($tenDaysData, JSON_PRETTY_PRINT);
    }
}
