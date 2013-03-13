<?php

if (!class_exists('EMOCCacheFactory', false)) :

final class EMOCCacheFactory
{
	private static $engines = array();
	private static $path = false;

	public static function registerEngine($id, $classname, $file, $checker, $has_options, $pretty, $force = false)
	{
		if (!self::$path) {
			self::$path = dirname(__FILE__);
		}

		if (function_exists($checker) || class_exists($checker, true)) {
			if (file_exists(self::$path . DIRECTORY_SEPARATOR . $file . '.php')) {
				if ($force) {
					include_once(self::$path . DIRECTORY_SEPARATOR . $file . '.php');
				}

				self::$engines[$id] = array($classname, $file, $has_options, $pretty);
				return true;
			}
		}

		return false;
	}

	public static function get(array $options)
	{
		if (empty($options['engine'])) {
			return null;
		}

		$enabled = (isset($options['enabled'])) ? $options['enabled'] : true;
		$persist = (isset($options['persist'])) ? $options['persist'] : false;
		$maxttl  = (isset($options['maxttl']))  ? $options['maxttl']  : 3600;

		$engine = strtolower($options['engine']);

		if (!isset(self::$engines[$engine])) {
			$item = reset(self::$engines);
			$name = key(self::$engines);
			trigger_error('Caching engine "' . $engine . '" is not available, falling back to ' . $name . '.', E_USER_WARNING);
		}
		else {
			$item = self::$engines[$engine];
		}

		include(self::$path . DIRECTORY_SEPARATOR . $item[1] . '.php');
		$params = isset($options['options'][$engine]) ? $options['options'][$engine] : array();
		$params['engine'] = $engine;

		return call_user_func(array($item[0], 'instance'), $params, $enabled, $persist, $maxttl);
	}

	public static function getEngines()
	{
		return self::$engines;
	}
}

endif;
