<?php

final class EMOCZendShmCache extends EMOCBaseCache
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

	protected function do_delete($key, $group)
	{
		return zend_shm_cache_delete($this->getKey($group, $key));
	}

	protected function do_flush()
	{
		zend_shm_cache_clear($this->prefix);
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		$result = zend_shm_cache_fetch($this->getKey($group, $key));
		$found  = (false !== $result);
		return $result;
	}

	protected function do_set($key, $data, $group, $ttl)
	{
		return zend_shm_cache_store($this->getKey($group, $key), $data, $ttl);
	}

	protected function getKey($group, $key)
	{
		global $blog_id;

		if (!isset($this->global_groups[$group])) {
			$prefix = $this->prefix . '::' . $this->blog_id . '/';
		}
		else {
			$prefix = $this->prefix . '::';
		}

		return $prefix . $group . '/' . $key;
	}
}
