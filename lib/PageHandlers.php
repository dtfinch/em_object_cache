<?php

class EMOCPageHandlers
{
	public static function instance()
	{
		static $self = false;
		if (false === $self) {
			$self = new self;
		}

		return $self;
	}

	private function __construct()
	{
		add_action('load-em-object-cache/pages/options-object-cache.php', array($this, 'load_generic_options_page'));
		add_action('admin_post_save_emoc_generic_options',                array($this, 'admin_post_save_emoc_generic_options'));
		add_action('admin_post_purge_emoc_cache',                         array($this, 'admin_post_purge_emoc_cache'));

		$engines = EMOCCacheFactory::getEngines();
		if (!empty($engines)) {
			foreach ($engines as $id => $engine) {
				if ($engine[2]) {
					add_action("load-em-object-cache/pages/{$engine[1]}.php", array($this, 'load_ce_options'));
					add_action("admin_post_save_emoc_options_{$id}",          array($this, 'prepare_post_data'), 10);
					add_action("admin_post_save_emoc_options_{$id}",          array($this, 'save_ce_options'),   30);
				}

				if (2 == $engine[2]) {
					if (include_once(WP_PLUGIN_DIR . '/em-object-cache/lib/ph/' . $engine[1] . '.php')) {
						add_action("load-em-object-cache/pages/{$engine[1]}.php", array('PH_' . $engine[0], 'load_options'));
						add_action("admin_post_save_emoc_options_{$id}",          array('PH_' . $engine[0], 'save_options'), 20);
					}
				}
			}
		}
	}

	public function load_ce_options()
	{
		global $params;
		if (!is_array($params)) {
			$params = array();
		}

		$params['message'] = null;
		$params['error']   = null;

		if (!empty($_GET['message'])) {
			$params['message'] = __("The settings have been updated.", 'emobjectcache');
		}

		if (!empty($_GET['error'])) {
			$params['error'] = sprintf(
				__("Unable to write to <code>%1\$s</code>. Please make sure that it is writable by the server.", 'emobjectcache'),
				ABSPATH . 'wp-content/object-cache.php'
			);
		}

		$options = EMObjectCache::instance()->getOptions();
		$matches = array();

		if (preg_match('!^em-object-cache/pages/options\\.([^.]+)\\.php$!', stripslashes($_GET['page']), $matches)) {
			$file = $matches[1];
			unset($matches);
			$engines = EMOCCacheFactory::getEngines();
			foreach ($engines as $id => $engine) {
				if ($engine[1] == $file) {
					$params['engine']  = $id;
					$params['options'] = (isset($options['options'][$id])) ? $options['options'][$id] : array();
					break;
				}
			}
		}
	}

	public function load_generic_options_page()
	{
		global $params;
		if (!is_array($params)) {
			$params = array();
		}

		$options = EMObjectCache::instance()->getOptions();

		$params['modules']       = EMOCCacheFactory::getEngines();
		$params['enabled']       = $options['enabled'];
		$params['nonpersistent'] = $options['nonpersistent'];
		$params['persist']       = $options['persist'];
		$params['engine']        = $options['engine'];
		$params['maxttl']        = $options['maxttl'];
		$params['message']       = null;
		$params['error']         = null;

		if (!empty($_GET['message'])) {
			switch ((int)$_GET['message']) {
				case 1:
					$params['message'] = __("The settings have been updated.", 'emobjectcache');
					break;

				case 2:
					$params['message'] = __("The cache has been cleared.", 'emobjectcache');
					break;
			}
		}

		if (!empty($_GET['error'])) {
			$params['error'] = sprintf(
				__("Unable to write to <code>%1\$s</code>. Please make sure that it is writable by the server.", 'emobjectcache'),
				ABSPATH . 'wp-content/object-cache.php'
			);
		}
	}

	public function admin_post_save_emoc_generic_options()
	{
		check_admin_referer('configure-objectcache');

		$options    = EMObjectCache::instance()->getOptions();
		$old_engine = $options['engine'];
		foreach ($options as $key => $value) {
			if ('options' != $key) {
				if (isset($_POST['options'][$key])) {
					$options[$key] = stripslashes($_POST['options'][$key]);
				}
				else {
					$options[$key] = '';
				}
			}
		}

		if ($old_engine != $options['engine']) {
			wp_cache_flush();
		}

		$options['nonpersistent'] = str_replace(' ', '', $options['nonpersistent']);

		if (EMObjectCache::instance()->writeOptions($options)) {
			$redir = array('message' => 1);
		}
		else {
			$redir = array('error' => 1);
		}

		wp_redirect(add_query_arg($redir, $_POST['_wp_http_referer']));
		die();
	}

	public function admin_post_purge_emoc_cache()
	{
		check_admin_referer('purge-objectcache');
		wp_cache_flush();
		wp_redirect(add_query_arg(array('message' => 2), $_POST['_wp_http_referer']));
	}

	public function prepare_post_data()
	{
		$_POST  = stripslashes_deep($_POST);
		$engine = $_POST['engine'];
		check_admin_referer("emobjectcache-config_{$engine}");
	}

	public function save_ce_options()
	{
		$options = EMObjectCache::instance()->getOptions();
		$engine  = $_POST['engine'];
		$options['options'][$engine] = $_POST['options'];

		if (EMObjectCache::instance()->writeOptions($options)) {
			$redir = array('message' => 1);
		}
		else {
			$redir = array('error' => 1);
		}

		wp_redirect(add_query_arg($redir, $_POST['_wp_http_referer']));
		die();
	}
}

EMOCPageHandlers::instance();

if (!function_exists('esc_attr_e')) {
	function esc_attr_e($text, $domain = 'default') { echo esc_attr(translate($text, $domain)); }
}

if (!function_exists('esc_attr')) {
	function esc_attr($text) { return attribute_escape($text); }
}
