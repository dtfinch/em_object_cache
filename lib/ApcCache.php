<?php

final class EMOCApcCache extends EMOCBaseCache
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

		if (function_exists('apc_sma_info')) {
			$info = apc_sma_info();
			if ($info['avail_mem'] < 1048576) {
				$persist = false;
			}
		}

		parent::__construct($data, $enabled, $persist, $maxttl);
	}

	protected function do_delete($key, $group)
	{
		return apc_delete($this->getKey($group, $key));
	}

	protected function do_flush()
	{
		$prefix = $this->prefix;
		$len    = strlen($this->prefix);
		$data   = @apc_cache_info('user');

		if ($data && !empty($data['cache_list'])) {
			foreach ($data['cache_list'] as &$x) {
				if (!strncmp($x['info'], $prefix, $len)) {
					apc_delete($x['info']);
				}
			}

			unset($x);
		}
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		$result = apc_fetch($this->getKey($group, $key), $found);
		return $result;
	}

	protected function do_set($key, $data, $group, $ttl)
	{
		return apc_store($this->getKey($group, $key), $data, $ttl);
	}

	protected function getKey($group, $key)
	{
		return $this->prefix . '/' . $group . '/' . $key;
	}
}
