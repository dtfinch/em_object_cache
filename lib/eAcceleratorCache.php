<?php

final class EMOCEAcceleratorCache extends EMOCBaseCache
{
	private $prefix;

	public static function instance(array $data, $enabled = true, $persist = true, $maxttl = 3600)
	{
		static $self = false;

		if (!$self) {
			$self = new self($data, $enabled, $persist, $maxttl);
		}

		return $self;
	}

	protected function __construct(array $data, $enabled = true, $persist = true, $maxttl = 3600)
	{
		$this->prefix = (empty($data['prefix'])) ? md5($_SERVER['HTTP_HOST']) : $data['prefix'];

		if (function_exists('eaccelerator_info')) {
			$info = eaccelerator_info();
			if (is_array($info)) {
				/**
				 * @todo Probably make this value configurable
				 */
				if ($info['memoryAvailable'] < 1048576) {
					$persist = false;
				}
			}
		}

		parent::__construct($data, $enabled, $persist, $maxttl);
	}

	protected function do_delete($key, $group)
	{
		return eaccelerator_rm($this->getKey($group, $key));
	}

	protected function do_flush()
	{
		$prefix = $this->prefix;
		$len    = strlen($prefix);
		$data   = eaccelerator_list_keys();

		if ($data) {
			foreach ($data as &$x) {
				if (!strncmp($x['name'], $prefix, $len)) {
					eaccelerator_rm($x['name']);
				}
			}

			unset($x);
		}
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		$result = eaccelerator_get($this->getKey($group, $key));
		$found  = (null !== $result);
		return $result;
	}

	protected function do_set($key, $data, $group, $ttl)
	{
		return eaccelerator_put($this->getKey($group, $key), $data, $ttl);
	}

	protected function getKey($group, $key)
	{
		return $this->prefix . '/' . $group . '/' . $key;
	}
}
