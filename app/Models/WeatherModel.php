<?php

class WeatherModel {

    private $cacheFile = __DIR__ . '/../../cache/data.json';
    private $data = [];
    private $hourlyData = [];
    private $dailyData = [];

    public function __construct($updateCacheOnStart = true) {
        if ($updateCacheOnStart) {
            $this->updateCacheFromAPI(); // Do it with cron job after uploading it to the server.
        }
        $this->loadData();
    }

    public function updateCacheFromAPI() {
        $url = "https://api.open-meteo.com/v1/forecast?latitude=41.0082&longitude=28.9784&hourly=temperature_2m,precipitation,precipitation_probability,pressure_msl,uv_index,wind_speed_10m,weathercode&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,uv_index_max,wind_speed_10m_max,sunrise,sunset,weathercode&forecast_days=10&timezone=auto";

        $jsonData = file_get_contents($url);
        if ($jsonData === false) {
            error_log("Weather API call failed");
            return false;
        }

        file_put_contents($this->cacheFile, $jsonData);
        return true;
    }

    private function loadData() {
        $jsonString = file_get_contents($this->cacheFile);
        $this->data = json_decode($jsonString, true);

        $this->hourlyData = [];
        foreach ($this->data['hourly']['time'] as $i => $time) {
            $this->hourlyData[] = [
                'time' => $time,
                'temperature' => $this->data['hourly']['temperature_2m'][$i],
                'wind_speed' => $this->data['hourly']['wind_speed_10m'][$i],
                'pressure' => $this->data['hourly']['pressure_msl'][$i],
                'uv_index' => $this->data['hourly']['uv_index'][$i],
                'precipitation_probability' => $this->data['hourly']['precipitation_probability'][$i],
                'weathercode' => $this->data['hourly']['weathercode'][$i]
            ];
        }

        $this->dailyData = [
            'time' => $this->data['daily']['time'],
            'temperature_min' => $this->data['daily']['temperature_2m_min'],
            'temperature_max' => $this->data['daily']['temperature_2m_max'],
            'sunrise' => $this->data['daily']['sunrise'],
            'sunset' => $this->data['daily']['sunset'],
            'rain_prob' => $this->data['daily']['precipitation_probability_max'],
            'uv_index' => $this->data['daily']['uv_index_max'],
            'wind_speed' => $this->data['daily']['wind_speed_10m_max'],
            'weathercode' => $this->data['daily']['weathercode']
        ];
    }

    public function getCurrentHourData($date) {
        date_default_timezone_set('Europe/Istanbul');

        $currentHour = ($date === date("Y-m-d")) ? date("Y-m-d\TH:00") : $date . "T00:00";

        foreach ($this->hourlyData as $hour) {
            if (strpos($hour['time'], $date) === 0 && $hour['time'] === $currentHour) {
                return [
                    'time' => $hour['time'],
                    'wind_speed' => $hour['wind_speed'],
                    'pressure' => $hour['pressure'],
                    'uv_index' => $hour['uv_index'],
                    'precipitation_probability' => $hour['precipitation_probability'],
                    'weathercode' => $hour['weathercode']
                ];
            }
        }

        return null;
    }

    public function getHourlyForecast($date) {
        $result = [];
        foreach ($this->hourlyData as $hour) {
            if (strpos($hour['time'], $date) === 0) {
                $result[] = [
                    'time' => substr($hour['time'], 11, 5),
                    'weathercode' => $hour['weathercode'],
                    'temperature' => $hour['temperature']
                ];
            }
        }
        return $result;
    }

    public function getWeeklyAvgTemp($days = 7) {
        $result = [];
        $dailyTime = $this->dailyData['time'];
        $dailyMin = $this->dailyData['temperature_min'];
        $dailyMax = $this->dailyData['temperature_max'];

        foreach ($dailyTime as $i => $date) {
            if ($i >= $days) break;
            $avgTemp = ($dailyMin[$i] + $dailyMax[$i]) / 2;
            $result[] = [
                'date' => $date,
                'avg_temperature' => round($avgTemp, 1)
            ];
        }
        return $result;
    }

    public function getDailyRainChance($date) {
        $result = [];
        foreach ($this->hourlyData as $hour) {
            if (strpos($hour['time'], $date) === 0) {
                $result[] = [
                    'time' => substr($hour['time'], 11, 5),
                    'precipitation_probability' => $hour['precipitation_probability']
                ];
            }
        }
        return $result;
    }

    public function getSunriseSunset($date) {
        $dailyTime = $this->dailyData['time'];
        $dailySunrise = $this->dailyData['sunrise'];
        $dailySunset = $this->dailyData['sunset'];

        foreach ($dailyTime as $i => $day) {
            if ($day === $date) {
                return [
                    'date' => $day,
                    'sunrise' => $dailySunrise[$i],
                    'sunset' => $dailySunset[$i]
                ];
            }
        }
        return null;
    }

    public function getTenDays() {
        $dailyTime = $this->dailyData['time'];
        $dailyMin = $this->dailyData['temperature_min'];
        $dailyMax = $this->dailyData['temperature_max'];
        $dailyWind = $this->dailyData['wind_speed'];
        //$dailyPressure = $this->dailyData['pressure'];
        $dailyUV = $this->dailyData['uv_index'];
        $dailyPrecip = $this->dailyData['rain_prob'];
        $dailyWeather = $this->dailyData['weathercode'];
        $dailySunrise = $this->dailyData['sunrise'];
        $dailySunset = $this->dailyData['sunset'];

        $result = [];

        foreach ($dailyTime as $i => $date) {
            $result[] = [
                'date' => $date,
                'temperature_min' => $dailyMin[$i],
                'temperature_max' => $dailyMax[$i],
                'wind_speed' => $dailyWind[$i] ?? null,
                'pressure' => $dailyPressure[$i] ?? null,
                'uv_index' => $dailyUV[$i] ?? null,
                'rain_prob' => $dailyPrecip[$i] ?? null,
                'weathercode' => $dailyWeather[$i] ?? null,
                'sunrise' => $dailySunrise[$i] ?? null,
                'sunset' => $dailySunset[$i] ?? null
            ];
        }
        return $result;
    }
}