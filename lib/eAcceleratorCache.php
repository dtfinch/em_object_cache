<?php

final class EMOCEAcceleratorCache extends EMOCBaseCache
{
	private $prefix;

	public static function instance($data, $enabled = true, $persist = true, $maxttl = 3600)
	{
		static $self = false;

		if (!$self) {
			$self = new self($data, $enabled, $persist, $maxttl);
		}

		return $self;
	}

	protected function __construct($data, $enabled = true, $persist = true, $maxttl = 3600)
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

	public function delete($key, $group = 'default')
	{
		parent::delete($key, $group);
		return eaccelerator_rm($this->getKey($group, $key));
	}

	public function flush()
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

		parent::flush();
	}

	public function get($key, $group = 'default', $force = false, &$found = null, $ttl = 3600)
	{
		$found = false;
		if (!$this->enabled) {
			return false;
		}

		if (!$force) {
			$result = $this->fast_get($key, $group, $found);
			if ($found) {
				return $result;
			}
		}

		if ($this->persist && !isset($this->np_groups[$group])) {
			$result = eaccelerator_get($this->getKey($group, $key));

			if (null !== $result) {
				$found  = true;
				$result = unserialize($result);
				parent::fast_set($key, $result, $group, 0);
				return $result;
			}
		}

		return false;
	}

	protected function fast_set($key, $data, $group, $ttl)
	{
		parent::fast_set($key, $data, $group, $ttl);
		return eaccelerator_put($this->getKey($group, $key), serialize($data), $ttl);
	}

	protected function getKey($group, $key)
	{
		if (!isset($this->global_groups[$group])) {
			$prefix = $this->prefix . ':' . $this->blog_id;
		}
		else {
			$prefix = $this->prefix;
		}

		return $prefix . '/' . $group . '/' . $key;
	}
}
