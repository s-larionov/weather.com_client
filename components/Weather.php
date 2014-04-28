<?php

namespace weathercom\components;

use weathercom\Client;
use yii\base\Component;
use weathercom\Exception as WeatherException;

class Weather extends Component {
	public $config;
	public $defaultLocation;

	protected $client;
	protected $icons = [
		'chanceflurries' => 'rain',
		'chancerain'     => 'rain',
		'chancesleet'    => 'snow',
		'chancesnow'     => 'snow',
		'chancetstorms'  => 'storm',
		'clear'          => 'sun',
		'cloudy'         => 'cloud',
		'flurries'       => 'rain',
		'fog'            => 'fog',
		'hazy'           => 'fog',
		'mostlycloudy'   => 'sun-cloud',
		'mostlysunny'    => 'sun-cloud',
		'partlycloudy'   => 'sun-cloud',
		'partlysunny'    => 'sun-cloud',
		'sleet'          => 'snow',
		'rain'           => 'rain',
		'snow'           => 'snow',
		'sunny'          => 'sun',
		'tstorms'        => 'storm',
	];


	protected function getClient() {
		if ($this->client === null) {
			$this->client = new Client($this->config);
		}

		return $this->client;
	}

	public function getCurrentWeather($location = null) {
		$location = $location === null? $this->defaultLocation: $location;

		try {
			$weather = $this->getClient()->getCurrentWeather($location);

			if (!isset($weather['current_observation']) || !is_array($weather['current_observation'])) {
				\Yii::error('Unable to getting current weather conditions', 'weathercom');
				return [];
			}

			$result = $weather['current_observation'];
			$result['saytoday_icon'] = $this->getInternalIconName($result['icon']);

			return $result;
		} catch (WeatherException $e) {
			\Yii::error("[{$e->getCode()}] {$e->getMessage()}", 'weathercom');
			return [];
		}
	}

	public function getForecastHourly($location = null) {
		$location = $location === null? $this->defaultLocation: $location;
		$result = [];

		try {
			$weather = $this->getClient()->getHourly10DaysForecast($location);

			foreach($weather['hourly_forecast'] as $hourData) {
				$date = "{$hourData['FCTTIME']['year']}-{$hourData['FCTTIME']['mon_padded']}-{$hourData['FCTTIME']['mday_padded']}";
				if (!isset($result[$date])) {
					$result[$date] = [];
				}
				$hourData['saytoday_icon'] = $this->getInternalIconName($hourData['icon']);
				$result[$date][$hourData['FCTTIME']['hour']] = $hourData;
			}

			return $result;
		} catch (WeatherException $e) {
			return [];
		}
	}

	public function getForecast($location = null) {
		$location = $location === null? $this->defaultLocation: $location;
		$result = [];

		try {
			$weather = $this->getClient()->get10DaysForecast($location);
//var_dump($weather['forecast']['txt_forecast']);
			$result = $weather['forecast']['simpleforecast']['forecastday'];
			foreach($result as &$dayData) {
				$dayData['saytoday_icon'] = $this->getInternalIconName($dayData['icon']);
			}

			return $result;
		} catch (WeatherException $e) {
			return [];
		}
	}

	/**
	 * @param string $icon
	 * @return string
	 */
	public function getInternalIconName($icon) {
		return isset($this->icons[$icon])? $this->icons[$icon]: $icon;
	}
}