<?php

final class EMOCXCache extends EMOCBaseCache
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

		if ('cli' == PHP_SAPI || !xcache_set($this->prefix . '/xcache-test', $data, 1)) {
			$persist = false;
		}

		parent::__construct($data, $enabled, $persist, $maxttl);
	}

	protected function do_delete($key, $group)
	{
		return xcache_unset($this->getKey($group, $key));
	}

	protected function do_flush()
	{
		if (function_exists('xcache_unset_by_prefix')) {
			xcache_unset_by_prefix($this->prefix . '/');
		}
		else {
			xcache_clear_cache(XC_TYPE_VAR, 0);
		}
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		$result = xcache_get($this->getKey($group, $key));
		$found  = (null !== $result);
		return $result;
	}

	protected function do_set($key, $data, $group, $ttl)
	{
		return xcache_set($this->getKey($group, $key), $data, $ttl);
	}

	protected function getKey($group, $key)
	{
		return $this->prefix . '/' . $group . '/' . $key;
	}
}
