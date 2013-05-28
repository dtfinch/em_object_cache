<?php
/*
Plugin Name: EM Object Cache
Plugin URI: http://blog.sjinks.pro/wordpress/plugins/776-sj-object-cache-faster-object-cache-for-wordpress/
Description: Object Cache for WordPress - a replacement for the standard WP_Object_Cache
Author: Vladimir Kolesnikov
Version: 2.0
Author URI: http://www.extrememember.com/
*/

defined('ABSPATH') or die();

require_once(WP_PLUGIN_DIR . '/em_object_cache/lib/CacheFactory.php');

class EMObjectCache
{
	protected $options;

	public static function instance()
	{
		static $self = false;

		if (!$self) {
			$self = new self;
		}

		return $self;
	}

	private function __construct()
	{
		add_action('activate_em_object_cache/core.php',   array($this, 'activate'));
		add_action('deactivate_em_object_cache/core.php', array($this, 'deactivate'));
		add_action('init', array($this, 'init'));
	}

	public function init()
	{
		if (!isset($_SERVER['HTTP_HOST'])) {
			$_SERVER['HTTP_HOST'] = null;
		}

		if (is_admin()) {
			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('admin_init', array($this, 'admin_init'));

			load_plugin_textdomain('emobjectcache', false, 'em_object_cache/lang');
		}
	}

	public function admin_init()
	{
		require_once(WP_PLUGIN_DIR . "/em_object_cache/lib/PageHandlers.php");
	}

	public function loadOptions()
	{
		static $done = false;

		if (false === $done) {
			$done     = true;
			$defaults = array(
				'enabled'       => 1,
				'persist'       => 1,
				'nonpersistent' => '',
				'engine'        => 'basecache',
				'maxttl'        => 3600,
				'options'       => array(),
			);

			global $__emoc_options;

			$options = $__emoc_options;
			if (!is_array($options)) {
				$options = array();
			}

			$update = false;
			foreach ($defaults as $k => $v) {
				if (!isset($options[$k])) {
					$options[$k] = $v;
					$update = true;
				}
			}

			foreach ($options as $k => $v) {
				if (!isset($defaults[$k])) {
					unset($options[$k]);
					$update = true;
				}
			}

			if ($update) {
				$this->writeOptions($options);
			}

			$this->options = $options;
		}
	}

	public function writeOptions($options)
	{
		$data = '<?php $GLOBALS["__emoc_options"] = ' . var_export($options, true) . '; ?>';
		return file_put_contents(WP_PLUGIN_DIR . '/em_object_cache/options.php', $data, LOCK_EX);
	}

	public function activate()
	{
		$this->loadOptions();
		if (!copy(WP_PLUGIN_DIR . '/em_object_cache/object-cache.php', WP_CONTENT_DIR . '/object-cache.php')) {
			wp_die(__(sprintf("There was an error copying <code>%s</code> to <code>%s</code>", WP_PLUGIN_DIR . '/em_object_cache/object-cache.php', WP_CONTENT_DIR . '/object-cache.php')));
		}
	}

	public function deactivate()
	{
		if (file_exists(ABSPATH . 'wp-content/object-cache.php')) {
			if (!unlink(ABSPATH . 'wp-content/object-cache.php')) {
				die("WARNING: failed to delete <code>" . ABSPATH . "wp-content/object-cache.php</code><br/>Please delete this file ASAP.");
			}
		}
	}

	public function admin_menu()
	{
		add_menu_page(__('EM Object Cache', 'emobjectcache'), __('EM Object Cache', 'emobjectcache'), 'manage_options', 'em_object_cache/pages/options-object-cache.php');
		add_submenu_page('em_object_cache/pages/options-object-cache.php', __('Generic Options', 'emobjectcache'), __('Generic Options', 'emobjectcache'), 'manage_options', 'em_object_cache/pages/options-object-cache.php');

		$engines = EMOCCacheFactory::getEngines();
		if (!empty($engines)) {
			foreach ($engines as $engine) {
				if ($engine[2]) {
					add_submenu_page('em_object_cache/pages/options-object-cache.php', sprintf(__('%1$s Options', 'emobjectcache'), $engine[3]), $engine[3], 'manage_options', "em_object_cache/pages/{$engine[1]}.php");
				}
			}
		}
	}

	public function getOptions()
	{
		$this->loadOptions();
		return $this->options;
	}

}

EMObjectCache::instance();
