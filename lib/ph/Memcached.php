<?php

final class PH_EMOCMemcached
{
	public static function load_options()
	{
		global $params;
		if (empty($params['options']['server'])) {
			$params['options']['server'][] = array('h' => '', 'p' => 11211, 'w' => 1);
		}
	}

	public static function save_options()
	{
		$data    = $_POST['options'];
		$servers = array();

		if (!empty($_POST['options']['server'])) {
			foreach ($_POST['options']['server'] as &$x) {
				if (!empty($x['host'])) {
					$w = (int)$x['weight'];
					if ($w < 1) {
						$w = 1;
					}

					$servers[] = array('h' => $x['host'], 'p' => (int)$x['port'], 'w' => $w);
				}
			}

			unset($x);
		}

		$_POST['options']['server'] = $servers;
	}
}
