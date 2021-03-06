# EM Object Cache

Persistent caching using APC, xCache, eAccelerator, Zend Disk Cache, Zend Shared Memory Cache or files.

## Description

The plugin implements object level persistent caching and can be used instead of the built in WordPress `WP_Object_Cache`.
Unlike WP Super Cache, Hyper Cache and other plugins, EM Object Cache does not cache the entire page; instead, it caches the data WordPress explicitly asks it to cache (using `wp_cache_xxx()` API functions).
Although this means that the performance will be less than with, say, WP Super Cache, all your pages remain dynamic.

EM Object Cache won't help you much if the plugins or theme you are using do not use [WordPress Cache API](http://codex.wordpress.org/Class_Reference/WP_Object_Cache).
This is by design, since the plugin tries to play nice. However, for most WordPress installations this will not be critical.

EM Object Cache significantly reduces the load from your database. Say, my blog's home page without the plugin
executes 24 queries (0.02403 sec); with the plugin enabled, only 4 queries (0.00188 sec).
Unlike DB Cache/DB Cache Reloaded, the plugin will work in the Admin Panel and supports all plugins that use WordPress Cache API.

## Installation

  1. Upload `em_object_cache` folder to the `wp-content/plugins/` directory.
  2. Please make sure that `wp-content` directory is writable by the web server: the plugin will need to copy `object-cache.php` file into it.
  3. Please make sure that `wp-content/plugins/em_object_cache` directory is writable by the web server: the plugin will store its configuration (`options.php`) there.
  4. Activate the plugin in the 'Plugins' menu in WordPress.
  5. Make sure that `wp-content/object-cache.php` file exists. If it is not, please copy it from `wp-content/plugins/em_object_cache/object-cache.php`
  6. `wp-content/object-cache.php` file wust me writable by the server since plugin stores its options in that file.
  7. That's all :-)

## Deactivation/Removal

  1. Please make sure that `wp-content` directory is writable by the web server: the plugin will need to delete `object-cache.php` from it.
  2. Deactivate/uninstall the plugin in the 'Plugins' menu in WordPress.
  3. Please verify that `wp-content/object-cache.php` file was removed.

## Branches

Right now there are two branches:
  * master: this is the development branch;
  * wordpress.org: this is the snapshot of EMOC @ wordpress.org plugin repository. Its main difference is that the plugin is located in `em-object-cache`,
    not in `em_object_cache` (due to WP SVN rules). If you plan to update the plugin from the official WordPress plugin repository, please use this branch.

The reason to keep two branches is to keep the existsing plugin installations safe to update.
