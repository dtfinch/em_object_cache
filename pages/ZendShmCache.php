<div class="wrap">
	<h2><?php _e("ZSC Caching Engine Options", 'emobjectcache'); ?></h2>

	<?php include(WP_PLUGIN_DIR . '/em-object-cache/pages/header.php'); ?>

	<form method="post" action="<?php echo esc_attr(admin_url('admin-post.php')); ?>">
		<table class="widefat">
			<tbody>
				<tr>
					<th scope="row" width="250"><?php _e("Namespace (must be unique for every site)", 'emobjectcache'); ?></th>
					<td><input type="text" name="options[prefix]" value="<?php echo esc_attr(false == empty($params['options']['prefix']) ? $params['options']['prefix'] : ''); ?>"/></td>
					<td><?php echo sprintf(__('Default value is <code>%1$s</code>', 'emobjectcache'), md5($_SERVER['HTTP_HOST'])); ?></td>
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