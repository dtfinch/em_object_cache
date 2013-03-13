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

	protected function do_delete($key, $group)
	{
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

	protected function do_flush()
	{
		$this->remove_dir($this->dir, false);
		$this->known_groups = array();
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		$fname = $this->getKey($group, $key);
		$dir   = $this->getKey($group);
		if (is_readable($fname) && filemtime($fname) > time() - $ttl) {
			settype($fname, 'string');
			$found  = true;
			$result = @file_get_contents($fname, LOCK_EX);
			$this->known_groups[$dir] = true;
			return $result;
		}

		@unlink($fname);
		$found = false;
		return false;
	}

	protected function do_set($key, $data, $group, $ttl)
	{
		$dir   = $this->getKey($group, false);
		$fname = $this->getKey($group, $key);

		if (!isset($this->known_groups[$dir])) {
			if (!file_exists($dir)) {
				@mkdir($dir);
			}

			$this->known_groups[$dir] = true;
		}

		return false !== @file_put_contents($fname, $data, LOCK_EX);
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
