<?php

namespace weathercom\components;

use weathercom\Client;
use yii\base\Component;
use weathercom\Exception as WeatherException;

class Weather extends Component {
	protected $client;

	public $key;
	public $address;
	public $defaultLocation;
	public $cacheTimeout = 60; // 60 seconds by default

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
			$this->client = new Client([
				'key' => $this->key,
				'address' => $this->address,
			]);
		}

		return $this->client;
	}

	public function getCurrentWeather($location = null) {
		if ($location === null) {
			$location = $this->defaultLocation;
		}

		$cacheKey = __CLASS__ . ':' . $location;
		$cache = \Yii::$app->cache;

		if ($cache !== null) {
			if ($result = $cache->get($cacheKey)) {
				return $result;
			}
		}

		try {
			$weather = $this->getClient()->getCurrecntWeather($location);

			$result = $weather['current_observation'];
			$result['saytoday_icon'] = isset($this->icons[$result['icon']])? $this->icons[$result['icon']]: $result['icon'];
		} catch (WeatherException $e) {
			return [];
		}

		if ($cache !== null) {
			$cache->add($cacheKey, $result, $this->cacheTimeout);
		}

		return $result;
	}
}