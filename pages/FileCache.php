<?php defined('ABSPATH') or die(); ?>
<div class="wrap">
	<h2><?php _e("FileCache Caching Engine Options", 'emobjectcache'); ?></h2>

	<?php include(WP_PLUGIN_DIR . '/em-object-cache/pages/header.php'); ?>

	<form method="post" action="<?php echo esc_attr(admin_url('admin-post.php')); ?>">
		<table class="widefat">
			<tbody>
				<tr>
					<th scope="row" width="150"><?php _e("Cache path", 'emobjectcache'); ?></th>
					<td>
						<input type="text" name="options[path]" value="<?php echo esc_attr(false == empty($params['options']['path']) ? $params['options']['path'] : ''); ?>"/>
						<br/>
						<?php _e("If at all possible, try to place the cache to a RAM drive (or <code>/dev/shm</code> in Linux).", 'emobjectcache'); ?><br/>
						<?php _e("For security reasons the cache should be located outside the web root and be inaccessible from the web.", 'emobjectcache'); ?><br/>
						<?php _e("It is extremely important that the cache directory <strong>be writable by the server</strong>!", 'emobjectcache'); ?>
					</td>
					<td><?php echo sprintf(__('Default value is <code>%1$s</code>', 'emobjectcache'), dirname(dirname(__FILE__)) . '/cache'); ?></td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<input type="hidden" name="engine" value="<?php echo esc_attr($params['engine']); ?>"/>
			<input type="hidden" name="action" value="save_emoc_options_<?php echo esc_attr($params['engine']); ?>"/>
			<?php wp_nonce_field("emobjectcache-config_" . $params['engine']); ?>
			<input type="submit" name="submit" value="<?php esc_attr_e('Save Changes', 'emobjectcache'); ?>" class="button button-primary"/>
		</p>
	</form>
</div>