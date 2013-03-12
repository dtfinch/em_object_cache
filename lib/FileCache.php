<?php

final class EMOCFileCache extends EMOCBaseCache
{
	protected $dir;
	protected $known_groups = array();

	public static function instance($params, $enabled = true, $persist = true, $maxttl = 3600)
	{
		static $self = false;

		if (!$self) {
			$self = new self($params, $enabled, $persist, $maxttl);
		}

		return $self;
	}

	protected function __construct($params, $enabled = true, $persist = true, $maxttl = 3600)
	{
		if (empty($params['path'])) {
			$path = dirname(dirname(__FILE__)) . '/cache';
		}
		else {
			$path = $params['path'];
		}

		$this->dir = $path;
		parent::__construct($params, $enabled, $persist, $maxttl);
	}

	public function close()
	{
		$this->known_groups = array();
		parent::close();
	}

	public function delete($key, $group = 'default')
	{
		parent::delete($key, $group);

		if ($this->persist && !isset($this->np_groups[$group])) {
			$fname = $this->getKey($group, $key);
			return @unlink($fname);
		}

		return true;
	}

	private function remove_dir($dir, $self)
	{
		$dh = @opendir($dir);
		if (false === $dh) {
			return;
		}

		while (false !== ($obj = readdir($dh))) {
			if ('.' == $obj || '..' == $obj) {
				continue;
			}

			if (false == @unlink($dir . '/' . $obj)) {
				$this->remove_dir($dir . '/' . $obj, true);
			}
		}

		closedir($dh);
		if ($self) {
			@rmdir($dir);
		}
	}

	public function flush()
	{
		$this->remove_dir($this->dir, false);
		$this->known_groups = array();
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
			$fname = $this->getKey($group, $key);
			$dir   = $this->getKey($group);
			if (is_readable($fname)) {
				if (filemtime($fname) > time() - $ttl) {
					settype($fname, 'string');
					$found  = true;
					$result = unserialize(@file_get_contents($fname, LOCK_EX));
					parent::fast_set($key, $result, $group, 0);
					$this->known_groups[$dir] = true;
					return $result;
				}
			}

			@unlink($fname);
		}

		return false;
	}

	protected function fast_set($key, $data, $group, $ttl)
	{
		parent::fast_set($key, $data, $group, $ttl);
		$dir   = $this->getKey($group, false);
		$fname = $this->getKey($group, $key);

		if (!isset($this->known_groups[$dir])) {
			if (!file_exists($dir)) {
				@mkdir($dir);
			}

			$this->known_groups[$dir] = true;
		}

		return false !== @file_put_contents($fname, serialize($data), LOCK_EX);
	}

	protected function getKey($group, $key = false)
	{
		$path = $this->dir . '/';

		if (!isset($this->global_groups[$group])) {
			$path .= $this->blog_id . '_';
		}

		$path = $path . urlencode($group);
		if ($key) {
			 $path .= '/' . urlencode($key) . '.cache';
		}

		return $path;
	}
}
