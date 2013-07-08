=== EM Object Cache ===

Contributors: extrememember
Tags: cache, object cache, performance, APC, xCache, memcached, eAccelerator
Requires at least: 3.0
Tested up to: 3.6
Stable tag: trunk
License: MIT
License URI: http://opensource.org/licenses/MIT
Donate link: TBD

Persistent caching using APC, xCache, eAccelerator, Zend Disk Cache, Zend Shared Memory Cache or files.

== Description ==

The plugin implements object level persistent caching and can be used instead of the built in WordPress `WP_Object_Cache`.
Unlike WP Super Cache, Hyper Cache and other plugins, EM Object Cache does not cache the entire page; instead, it caches the data WordPress explicitly asks it to cache (using `wp_cache_xxx()` API functions).
Although this means that the performance will be less than with, say, WP Super Cache, all your pages remain dynamic.

EM Object Cache won't help you much if the plugins or theme you are using do not use [WordPress Cache API](http://codex.wordpress.org/Class_Reference/WP_Object_Cache).
This is by design, since the plugin tries to play nice. However, for most WordPress installations this will not be critical.

EM Object Cache significantly reduces the load from your database. Say, my blog's home page without the plugin
executes 24 queries (0.02403 sec); with the plugin enabled, only 4 queries (0.00188 sec).
Unlike DB Cache/DB Cache Reloaded, the plugin will work in the Admin Panel and supports all plugins that use WordPress Cache API.

This plugin is the successor of [WP File Cache](http://blog.sjinks.pro/wordpress-plugins/wp-file-cache/) and [SJ Object Cache](http://blog.sjinks.pro/wordpress/plugins/776-sj-object-cache-faster-object-cache-for-wordpress/) and is based on their codebases.

*CAVEATS:*
* if you want to use FileCache caching engine, chances are that the plugin will not work when PHP safe mode is enabled and web server is operated by a different user than owns the files.

== Installation ==

1. Upload `em-object-cache` folder to the `wp-content/plugins/` directory.
1. Please make sure that `wp-content` directory is writable by the web server: the plugin will need to copy `object-cache.php` file into it.
1. Please make sure that `wp-content/plugins/em-object-cache` directory is writable by the web server: the plugin will store its configuration (`options.php`) there.
1. Activate the plugin in the 'Plugins' menu in WordPress.
1. Make sure that `wp-content/object-cache.php` file exists. If it is not, please copy it from `wp-content/plugins/em-object-cache/object-cache.php`
1. `wp-content/object-cache.php` file wust me writable by the server since plugin stores its options in that file.
1. That's all :-)

*WARNING:* if you are upgrading from 2.0, please make sure to read "Upgrade Notice".

== Deactivation/Removal ==

1. Please make sure that `wp-content` directory is writable by the web server: the plugin will need to delete `object-cache.php` from it.
1. Deactivate/uninstall the plugin in the 'Plugins' menu in WordPress.
1. Please verify that `wp-content/object-cache.php` file was removed.

== Frequently Asked Questions ==

= The plugin does not work with Custom Field Template plugin. =

This is because Custom Field Template maintains its own cache for the post meta data which gets out of sync with WordPress cache. Please add `cft_post_meta` to the list of the non-persistent groups (EM Object Cache > Generic Options)

= After activation of FileCache engine, I see an error: "Warning: file_exists(): open_basedir restriction in effect. File(`filename`) is not within the allowed path(s)". What to do? =

A1: Try to get rid of `open_basedir` form your php.ini/Apache config. `open_basedir` is considered a "broken" security measure anyway and only slows down file operations.

A2: If disabling `open_basedir` is not an option, set the `Cache location` under the EM Object Cache > FileCache to the directory that satisfies the `open_basedir` restriction.

== Changelog ==

= EMOC 2.1 (Jul 08, 2013) =
* Changed all paths from `em_object_cache` to `em-object-cache` due to WP requirements ("an underscore character is not valid in a path, therefore you can't have em_object_cache as the plugin's slug. Only alphanumeric characters and the dash are valid.").

= EMOC 2.0 (Jun 08, 2013) =
* First public release of EM Object Cache
* Work around a weird bug when $_wp_using_ext_object_cache somehow resets to true (0a41da8)
* Major refactoring of SJOC codebase
* igbinary support (bbf61ad)
* Compatibility with WP 3.6
* Added guards to fix path disclosure vulnerability (d595b19)

= WPFC 1.2.9.1 (Dec 16, 2010) =
* Fixed stupid bug

= WPFC 1.2.9 (Dec 15, 2010) =
* Ability to disable caching when memory is low

= SJOC 1.3 (Oct 31, 2010) =
* Bug fix with Memcached engine registration
* Removed FileGroupCache
* Get rid of `maybe_serialize()`
* Optimizations

= SJOC 1.2 (Oct 14, 2010) =
* Added Memcache/Memcached support

= WPFC 1.2.8.2 (Apr 8, 2010) =
* Suppress 'stat failed' warning for `filemtime`

= WPFC 1.2.8.1 (Apr 7, 2010) =
* Save options bug fix

= WPFC 1.2.8 (Mar 27, 2010) =
* Added Ukrainian translation (props [Andrey K.](http://andrey.eto-ya.com/))
* Fixed typos in readme.txt

= WPFC 1.2.7 (Mar 12, 2010) =
* Option to always use fresh data in the Admin Panel
* Added Belarussian translation (props [Antsar](http://antsar.info/))

= SJOC 1.1 (Mar 25, 2010) =
* Experimental WPMU support

= SJOC 1.0 (Mar 8, 2010) =
* Declared RC3 stable

= WPFC 1.2.6 (Mar 6, 2010) =
* Updated FAQ
* Added an experimental option to partially disable the cache in the Admin panel

= WPFC 1.2.5 (Feb 15, 2010) =
* Data to be cached are not passed by reference anymore to ensure there are no side effects
* Objects are cloned before caching to avoid any side effects

= WPFC 1.2.4 (Feb 14, 2010) =
* Fixed wrong directory name

= WPFC 1.2.3 (Feb 12, 2010) =
* readme.txt bug fix

= WPFC 1.2.2 (Feb 12, 2010) =
* Compatibility with WP 3.0

= WPFC 1.2.1 (Jan 14, 2010) =
* optimized the code, speeded up `FileCache` class methods by moving all sanity checks to `wp_cache_xxx()` functions
* file lock on write
* less system calls are used
* compatibility with WordPress 2.6
* plugin won't cause WSoD if the plugin is deleted but wp-content/object-cache.php file is not

= SJOC 1.0rc3 (Jan 4, 2010) =
* `$__sjfc_options` is forcefully global variable
* admin_menu and admin_init hooks are set up only when is_admin() is true
* unset all internally used variables in object-cache.php
* added wp_cache_reset() support
* cache is not persistent when `$_SERVER['HTTP_HOST']` is not set (CLI mode)
* moved sanity checks from BaseCache to wp_cache_add_global_groups() and wp_cache_add_non_persistent_groups()
* removed eAcceleratorGroupCache and xGroupCache
* FileCache: use `LOCK_EX` with `file_get_contents()`
* Minor PHP code optimizations
* `&$this` => `$this`

= SJOC 1.0rc2 (Jan 3, 2010) =
* Renamed SjObjectCache to SJ Object Cache
* load_plugin_textdomain() is called only if is_admin() is true (translations are not used in user mode)

= SJOC 1.0rc1 (Dec 20, 2009) =
* Added eAcceleratorGroupCache
* Moved several checks from BaseCache to wp_cache_XXX() functions to speed up internally used functions
* Minor PHP code optimizations
* Better wp_cache_flush() implementation for APC, eAccelerator and XCache

= WPFC 1.1 (Dec 19, 2009) =
* Fixed serious floating bug in `FileCache::get()`

= SJOC 0.01 (Sep 22, 2009) =
* The first version of SJ Object Cache (proof of concept). Supported engines: APC, XCache, eAccelerator, FileCache, FileGroupCache, Zend Disk Cache, Zend Shared Memory Cache, XGroupCache

= WPFC 1.0 (Dec 2, 2008) =
* Really do not remember

= WPFC 0.2.1 (Jun 12, 2008) =
* First public release

== Upgrade Notice ==

If upgrading from WP File Cache or SJ Object Cache, please deactivate and remove them first, the activate EM Object Cache.

If upgrading from EM Object Cache 2.0, please note that the plugin path has changed (`em_object_cache` => `em-object-cache`).
Thios is because "an underscore character is not valid in a path, therefore you can't have em_object_cache as the plugin's slug. Only alphanumeric characters and the dash are valid."
Therefore please uninstall EMOC 2.0 completely and then install 2.1 from the wordpress.org.

== Screenshots ==

None
