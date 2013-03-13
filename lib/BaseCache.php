<?php

if (!class_exists('EMOCBaseCache', false)) :

class EMOCBaseCache
{
	protected $cache = array();

	protected $enabled = true;
	protected $persist = true;
	protected $maxttl  = 3600;

	protected $np_groups = array();
	protected $global_groups = array();

	protected $blog_id;

	private static $serialize   = 'serialize';
	private static $unserialize = 'unserialize';

	/**
	 * @desc To stay compatible with SimpleTags
	 */
	protected $cache_enabled = true;

	public static function instance($data, $enabled = true, $persist = true, $maxttl = 3600)
	{
		static $self = false;

		if (!$self) {
			$self = new EMOCBaseCache($data, $enabled, $persist, $maxttl);
		}

		return $self;
	}

	public function __get($key)
	{
		static $keys = array('global_groups' => 'global_groups', 'cache_enabled' => 'cache_enabled', 'enabled' => 'enabled');
		return isset($keys[$key]) ? $this->$key : null;
	}

	public function __set($key, $val)
	{
		if ('enabled' == $key) {
			if (!$val) {
				$this->close();
				$this->persist = false;
			}

			$this->enabled = $val;
		}
	}

	protected function __construct($data, $enabled = true, $persist = true, $maxttl = 3600)
	{
		$this->enabled = $enabled;
		$this->persist = $persist && $enabled;
		$this->maxttl  = $maxttl;
		$this->blog_id = $GLOBALS['blog_id'];

		if (function_exists('igbinary_serialize')) {
			self::$serialize   = 'igbinary_serialize';
			self::$unserialize = 'igbinary_unserialize';
		}

		if (!$this->persist) {
			global $_wp_using_ext_object_cache;
			$_wp_using_ext_object_cache = false;
			$this->cache_enabled = false;
		}
	}

	public function add($key, $data, $group = 'default', $ttl = 0)
	{
		if ($this->enabled && false === $this->get($key, $group, $ttl)) {
			return $this->set($key, $data, $group, $ttl);
		}

		return false;
	}

	public function close()
	{
	}

	public function decr($key, $offset = 1, $group = 'default')
	{
		$found = null;
		$val   = $this->get($key, $group, false, $found);

		if ($found) {
			if (!is_numeric($val)) {
				$val = 0;
			}

			$val -= $offset;
			if ($val < 0) {
				$val = 0;
			}

			$this->fast_set($key, $val, $group, 0);
			return $val;
		}

		return false;
	}

	public function delete($key, $group = 'default')
	{
		unset($this->cache[$group][$key]);
		return $this->do_delete($key, $group);
	}

	public function flush()
	{
		$this->cache = array();
		$this->do_flush();
	}

	public function get($key, $group = 'default', $force = false, &$found = null, $ttl = 3600)
	{
		if (!$this->enabled) {
			$found = false;
			return false;
		}

		return $this->fast_get($key, $group, $found);


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
			$result = $this->do_get($group, $key, $found, $ttl);

			if ($found) {
				$func   = self::$unserialize;
				$result = $func($result);
				$this->cache[$group][$key] = $result;
				return $result;
			}
		}

		return false;
	}

	public function incr($key, $offset = 1, $group = 'default')
	{
		$found = null;
		$val   = $this->get($key, $group, false, $found);

		if ($found) {
			if (!is_numeric($val)) {
				$val = $offset;
			}
			else {
				$val += $offset;
			}

			if ($val < 0) {
				$val = 0;
			}

			$this->fast_set($key, $val, $group, 0);
			return $val;
		}

		return false;
	}

	public function replace($key, $data, $group, $ttl = 0)
	{
		if ($this->enabled && false !== $this->get($key, $group, $ttl)) {
			return $this->set($key, $data, $group, $ttl);
		}

		return false;
	}

	public function reset()
	{
		$this->close();
		if ($this->cache) {
			foreach ($this->cache as $group => &$x) {
				if (!in_array($group, $this->global_groups)) {
					unset($this->cache[$group]);
				}
			}

			unset($x);
		}

		$this->blog_id = $GLOBALS['blog_id'];
	}

	public function set($key, $data, $group = 'default', $ttl = 0)
	{
		if (!$this->enabled) {
			return false;
		}

		if (!$ttl && $this->maxttl) {
			$ttl = $this->maxttl;
		}

		if (is_object($data)) {
			$data = clone($data);
		}

		if (!$this->persist) {
			$this->cache[$group][$key] = $data;
			return true;
		}

		return $this->fast_set($key, $data, $group, $ttl);
	}

	protected function do_delete($key, $group)
	{
		return true;
	}

	protected function do_get($group, $key, &$found, $ttl)
	{
		$found = false;
		return false;
	}

	protected function do_flush()
	{
	}

	protected function do_set($key, $data, $group, $ttl)
	{
		return true;
	}

	private function fast_get($key, $group, &$found = null)
	{
		if (isset($this->cache[$group][$key])) {
			$found  = true;
			$result = $this->cache[$group][$key];
			return is_object($result) ? clone($result) : $result;
		}

		$found = false;
		return false;
	}

	private function fast_set($key, $data, $group, $ttl)
	{
		$this->cache[$group][$key] = $data;
		$func = self::$serialize;
		$data = $func($data);
		return $this->do_set($key, $data, $group, $ttl);
	}

	protected function has_group($group)
	{
		return isset($this->cache[$group]);
	}

	public function addNonPersistentGroups(array $groups)
	{
		$this->np_groups = array_merge(
			array_values($this->np_groups),
			$groups
		);

		$this->np_groups = array_unique($this->np_groups);
		$this->np_groups = array_combine($this->np_groups, $this->np_groups);
	}

	public function addGlobalGroups(array $groups)
	{
		if (!is_array($this->global_groups)) {
			$this->global_groups = array();
		}

		$this->global_groups = array_merge(
			array_values($this->global_groups),
			$groups
		);

		$this->global_groups = array_unique($this->global_groups);
		$this->global_groups = array_combine($this->global_groups, $this->global_groups);
	}

	public function clearGlobalGroups()
	{
		$this->global_groups = array();
	}

	public function clearNonPersistentGroups()
	{
		$this->np_groups = array();
	}
}

endif;
