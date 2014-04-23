<?php

namespace weathercom;

class Client {
	protected $key;
	protected $address;

	public function __construct(array $config) {
		$this->key = isset($config['key'])? $config['key']: null;
		$this->address = isset($config['address'])? $config['address']: null;
	}

	protected function generateUrl($features, $query, array $settings = []) {
		return str_replace(
			['{$key}', '{$features}', '{$query}'],
			[$this->key, $features, $query],
			$this->address
		);
	}

	protected function doRequest($features, $query, array $settings = []) {
		$responseBody = @file_get_contents($this->generateUrl($features, $query, $settings));

		if (($response = json_decode($responseBody, true)) === null) {
			throw new Exception('Unknown response');
		}

		if (!is_array($response) || !isset($response['response']) || !is_array($response['response'])) {
			throw new Exception('Wrong response format');
		}

		if (isset($response['response']['error'])) {
			if (is_array(isset($response['response']['error'])) && isset($response['response']['error']['type'])) {
				throw new Exception($response['response']['error']['type']);
			}
			throw new Exception('Unknown error: ' . json_encode($response['response']['error']));
		}

		return $response;
	}

	public function getCurrecntWeather($city, array $settings = []) {
		return $this->doRequest('conditions', $city, $settings);
	}
}