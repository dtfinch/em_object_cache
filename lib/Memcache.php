<?php

class EMOCMemcache extends EMOCBaseCache
{
	private $prefix;
	private $memcache;

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

		$this->memcache = new Memcache();
		$result = false;
		if (!empty($data['server'])) {
			foreach ($data['server'] as $x) {
				$result |= $this->memcache->addServer($x['h'], $x['p'], true, $x['w']);
			}
		}

		if (!$result) {
			$persist = false;
		}

		parent::__construct($data, $enabled, $persist, $maxttl);
	}

	protected function do_delete($key, $group)
	{
		return $this->memcache->delete($this->getKey($group, $key));
	}

	protected function do_flush()
	{
		$this->memcache->flush();
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		$result = $this->memcache->get($this->getKey($group, $key));
		$found  = (false !== $result);
		return $result;
	}

	protected function do_set($key, $data, $group, $ttl)
	{
		return $this->memcache->set($this->getKey($group, $key), $data, 0, $ttl);
	}

	protected function getKey($group, $key)
	{
		return $this->prefix . '/' . $group . '/' . $key;
	}
}
