<?php
/*
** Zabbix
** Copyright (C) 2001-2017 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * Converter for converting import data from 3.2 to 3.4
 */
class C32ImportConverter extends CConverter {

	public function convert($data) {
		$data['zabbix_export']['version'] = '3.4';

		if (array_key_exists('hosts', $data['zabbix_export'])) {
			$data['zabbix_export']['hosts'] = $this->convertHosts($data['zabbix_export']['hosts']);
		}
		if (array_key_exists('templates', $data['zabbix_export'])) {
			$data['zabbix_export']['templates'] = $this->convertHosts($data['zabbix_export']['templates']);
		}

		return $data;
	}

	/**
	 * Convert hosts.
	 *
	 * @param array $hosts
	 *
	 * @return array
	 */
	protected function convertHosts(array $hosts) {
		foreach ($hosts as &$host) {
			if (array_key_exists('discovery_rules', $host)) {
				$host['discovery_rules'] = $this->convertDiscoveryRules($host['discovery_rules']);
			}
			if (array_key_exists('items', $host)) {
				$host['items'] = $this->convertItems($host['items']);
			}
			if (array_key_exists('httptests', $host)) {
				$host['httptests'] = $this->convertHttpTests($host['httptests']);
			}
		}
		unset($host);

		return $hosts;
	}

	/**
	 * Convert item elements.
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	protected function convertItems(array $items) {
		foreach ($items as &$item) {
			$item['preprocessing'] = [];

			if ($item['data_type'] != ITEM_DATA_TYPE_DECIMAL) {
				switch ($item['data_type']) {
					case ITEM_DATA_TYPE_OCTAL:
						$type = ZBX_PREPROC_OCT2DEC;
						break;
					case ITEM_DATA_TYPE_HEXADECIMAL:
						$type = ZBX_PREPROC_HEX2DEC;
						break;
					case ITEM_DATA_TYPE_BOOLEAN:
						$type = ZBX_PREPROC_BOOL2DEC;
						break;
				}

				$item['preprocessing'][] = ['type' => $type, 'params' => ''];
			}

			if ($item['delta'] == 1) {
				$item['preprocessing'][] = ['type' => ZBX_PREPROC_DELTA_SPEED, 'params' => ''];
			}
			elseif ($item['delta'] == 2) {
				$item['preprocessing'][] = ['type' => ZBX_PREPROC_DELTA_VALUE, 'params' => ''];
			}

			if ($item['multiplier'] == 1) {
				$item['preprocessing'][] = ['type' => ZBX_PREPROC_MULTIPLIER, 'params' => $item['formula']];
			}

			unset($item['data_type'], $item['delta'], $item['multiplier'], $item['formula']);

			if (!$item['preprocessing']) {
				unset($item['preprocessing']);
			}
		}
		unset($item);

		return $items;
	}

	/**
	 * Convert discovery rule elements.
	 *
	 * @param array $discovery_rules
	 *
	 * @return array
	 */
	protected function convertDiscoveryRules(array $discovery_rules) {
		foreach ($discovery_rules as &$discovery_rule) {
			$discovery_rule['item_prototypes'] =
				$this->convertItems($discovery_rule['item_prototypes']);
		}
		unset($discovery_rule);

		return $discovery_rules;
	}

	/**
	 * Convert HTTP fields (headers / variables) into http pair array
	 *
	 * @param string $value
	 * @param string $delimiter
	 *
	 * @return array
	 */
	protected function convertHttpFields($value, $delimiter) {
		if (is_string($value)) {
			$pairs = array_values(array_filter(explode("\n", str_replace("\r", "\n", $value))));
			foreach ($pairs as &$pair) {
				$pair = explode($delimiter, $pair, 2);
				$pair = [
					'name' => $pair[0],
					'value' => array_key_exists(1, $pair) ? $pair[1] : ''
				];
			}
			unset($pair);

			return $pairs;
		}

		return $value;
	}

	/**
	 * Convert httptest step elements.
	 *
	 * @param array $http_test_steps
	 *
	 * @return array
	 */
	protected function convertHttpTestSteps(array $http_test_steps) {
		foreach ($http_test_steps as &$http_test_step) {
			$http_test_step['headers'] = $this->convertHttpFields($http_test_step['headers'], ':');
			$http_test_step['variables'] = $this->convertHttpFields($http_test_step['variables'], '=');
		}
		unset($http_test_step);

		return $http_test_steps;
	}

	/**
	 * Convert httptest elements.
	 *
	 * @param array $http_tests
	 *
	 * @return array
	 */
	protected function convertHttpTests(array $http_tests) {
		foreach ($http_tests as &$http_test) {
			$http_test['headers'] = $this->convertHttpFields($http_test['headers'], ':');
			$http_test['variables'] = $this->convertHttpFields($http_test['variables'], '=');

			if (array_key_exists('steps', $http_test)) {
				$http_test['steps'] = $this->convertHttpTestSteps($http_test['steps']);
			}
		}
		unset($http_test);

		return $http_tests;
	}
}
