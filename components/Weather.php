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
		if ($location === null) {
			$location = $this->defaultLocation;
		}

		try {
			$weather = $this->getClient()->getCurrentWeather($location);

			$result = $weather['current_observation'];
			$result['saytoday_icon'] = isset($this->icons[$result['icon']])? $this->icons[$result['icon']]: $result['icon'];

			return $result;
		} catch (WeatherException $e) {
			return [];
		}

	}
}