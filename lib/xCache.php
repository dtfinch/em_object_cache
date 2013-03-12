<?php

final class EMOCXCache extends EMOCBaseCache
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

		if ('cli' == PHP_SAPI || !xcache_set($this->prefix . '/xcache-test', $data, 1)) {
			// We are really out of memory here
			$persist = false;
		}

		parent::__construct($data, $enabled, $persist, $maxttl);
	}

	public function delete($key, $group = 'default')
	{
		parent::delete($key, $group);
		return xcache_unset($this->getKey($group, $key));
	}

	public function flush()
	{
		if (function_exists('xcache_unset_by_prefix')) {
			xcache_unset_by_prefix($this->prefix . '/');
			xcache_unset_by_prefix($this->prefix . ':');
		}
		else {
			xcache_clear_cache(XC_TYPE_VAR, 0);
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
			$result = xcache_get($this->getKey($group, $key));

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
		return xcache_set($this->getKey($group, $key), serialize($data), $ttl);
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
