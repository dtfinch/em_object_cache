<?php

final class EMOCZendDiskCache extends EMOCBaseCache
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
		parent::__construct($data, $enabled, $persist, $maxttl);
	}

	protected function do_delete($key, $group)
	{
		return zend_disk_cache_delete($this->getKey($group, $key));
	}

	protected function do_flush()
	{
		zend_disk_cache_clear($this->prefix);
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		$result = zend_disk_cache_fetch($this->getKey($group, $key));
		$found  = (false !== $result);
		return $result;
	}

	protected function do_set($key, $data, $group, $ttl)
	{
		return zend_disk_cache_store($this->getKey($group, $key), $data, $ttl);
	}

	protected function getKey($group, $key)
	{
		return $this->prefix . '::' . $group . '/' . $key;
	}
}
