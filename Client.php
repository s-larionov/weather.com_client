<?php

namespace weathercom;

use yii\helpers\ArrayHelper;

class Client {
	protected $key;
	protected $address;
	protected $cacheId = 'cache';
	protected $cacheTimeout;

	public function __construct(array $config) {
		$this->key          = ArrayHelper::getValue($config, 'key');
		$this->address      = ArrayHelper::getValue($config, 'address');
		$this->cacheId      = ArrayHelper::getValue($config, 'cacheId', 'cache');
		$this->cacheTimeout = ArrayHelper::getValue($config, 'cacheTimeout', 300);
	}

	protected function compileSettings(array $settings) {
		if (empty($settings)) {
			return '';
		}
		$result = '/';
		foreach($settings as $key => $value) {
			$result .= urlencode($key) . ':' . urlencode($value);
		}
		return $result;
	}

	protected function generateUrl($features, $query, array $settings = []) {
		return str_replace(
			['{$key}', '{$features}', '{$query}', '{$settings}'],
			[$this->key, $features, $query, $this->compileSettings($settings)],
			$this->address
		);
	}

	protected function doRequest($features, $query, array $settings = []) {
		$cache = $this->cacheId? \Yii::$app->get($this->cacheId): null;
		$cacheKey = __CLASS__ . ':' . $features . ':' . $query . ':' . md5(json_encode($settings));

		if ($cache !== null && ($result = $cache->get($cacheKey)) !== false) {
			return $result;
		}

		$response = @file_get_contents($this->generateUrl($features, $query, $settings));

		if (($result = json_decode($response, true)) === null) {
			throw new Exception('Unknown response');
		}

		if (!is_array($result) || !isset($result['response']) || !is_array($result['response'])) {
			throw new Exception('Wrong response format');
		}

		if (isset($result['response']['error'])) {
			if (is_array(isset($result['response']['error'])) && isset($result['response']['error']['type'])) {
				throw new Exception($result['response']['error']['type']);
			}
			throw new Exception('Unknown error: ' . json_encode($result['response']['error']));
		}

		if ($cache !== null) {
			$cache->add($cacheKey, $result, $this->cacheTimeout);
		}

		return $result;
	}

	public function getCurrentWeather($city, array $settings = []) {
		return $this->doRequest('conditions', $city, $settings);
	}

	public function getHourlyForecast($city, array $settings = []) {
		return $this->doRequest('hourly', $city, $settings);
	}

	public function getHourly10DaysForecast($city, array $settings = []) {
		return $this->doRequest('hourly10day', $city, $settings);
	}
}