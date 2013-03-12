<?php

class SEMOCMemcache extends EMOCBaseCache
{
	private $prefix;
	private $memcache;

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

	public function delete($key, $group = 'default')
	{
		parent::delete($key, $group);
		return $this->memcache->delete($this->getKey($group, $key));
	}

	public function flush()
	{
		$this->memcache->flush();
		sleep(1);
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
			$result = $this->memcache->get($this->getKey($group, $key));

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
		return $this->memcache->set($this->getKey($group, $key), $data, 0, $ttl);
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
