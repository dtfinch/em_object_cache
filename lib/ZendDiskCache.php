<?php

final class EMOCZendDiskCache extends EMOCBaseCache
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
		parent::__construct($data, $enabled, $persist, $maxttl);
	}

	public function delete($key, $group = 'delete')
	{
		parent::delete($key, $group);
		return zend_disk_cache_delete($this->getKey($group, $key));
	}

	public function flush()
	{
		zend_disk_cache_clear($this->prefix);
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
			$result = zend_disk_cache_fetch($this->getKey($group, $key));

			if (false !== $result) {
				$found = true;
				parent::fast_set($key, $result, $group, 0);
				return $result;
			}
		}

		return false;
	}

	protected function fast_set($key, $data, $group, $ttl)
	{
		parent::fast_set($key, $data, $group, $ttl);
		return zend_disk_cache_store($this->getKey($group, $key), $data, $ttl);
	}

	protected function getKey($group, $key)
	{
		if (!isset($this->global_groups[$group])) {
			$prefix = $this->prefix . '::' . $this->blog_id . '/';
		}
		else {
			$prefix = $this->prefix . '::';
		}

		return $prefix . $group . '/' . $key;
	}
}
