<?php defined('ABSPATH') or die(); ?>
<div class="wrap">
	<h2><?php echo($GLOBALS['title']); ?></h2>

	<?php include(WP_PLUGIN_DIR . '/em-object-cache/pages/header.php'); ?>

	<form method="post" action="<?php echo esc_attr(admin_url('admin-post.php')); ?>">
		<table class="widefat">
			<tbody>
				<tr>
					<th scope="row"><label for="emoc_enabled"><?php _e('Enable WordPress Object Cache', 'emobjectcache'); ?></label></th>
					<td><input type="checkbox" id="emoc_enabled" name="options[enabled]" value="1"<?php checked(1, $params['enabled']); ?>/></td>
					<td>
						<strong><?php _e('Disabling WordPress Object Cache can make WordPress crawl!', 'emobjectcache') ?></strong><br/>
						<?php _e("If you disable WordPress Object Cache, caching will be completely disabled, and WordPress will have to use the database every time it needs data. This is really slow.", 'emobjectcache') ?>
						<?php _e("This can be useful only if you are a WordPress developer.", 'emobjectcache'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="emoc_persist"><?php _e('Save cached data across sessions', 'emobjectcache'); ?></label></th>
					<td><input type="checkbox" id="emoc_persist" name="options[persist]" value="1"<?php checked(1, $params['persist']); ?>/></td>
					<td>
						<?php _e("If this option is set, EM Object Cache will maintain its cache between sessions to improve overall performance. Actually, this is what this plugin was made for and we strongly recommend that you do not turn this option off.", 'emobjectcache'); ?><br/>
						<small><?php _e("<strong>Boring technical details:</strong> EM Object Cache will save only those data that were not marked as 'non-persistent'.", 'emobjectcache'); ?></small>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="emoc_engine"><?php _e('Cache engine', 'emobjectcache'); ?></label></th>
					<td>
						<select id="emoc_path" name="options[engine]">
						<?php foreach ($params['modules'] as $key => $val) : ?>
							<option value="<?php echo esc_attr($key); ?>"<?php selected($params['engine'], $key); ?>><?php echo esc_attr($val[3]); ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<th scope="row"><label for="emoc_np"><?php _e('Non-persistent groups', 'emobjectcache'); ?></label></th>
					<td><input type="text" id="emoc_np" name="options[nonpersistent]" value="<?php echo esc_attr($params['nonpersistent']); ?>"/></td>
					<td>
						<?php _e("Comma separated list of the cache groups which should never be stored across sessions.", 'emobjectcache'); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="emoc_ttl"><?php _e('Maximum TTL', 'emobjectcache'); ?></label></th>
					<td><input type="text" id="emoc_ttl" name="options[maxttl]" value="<?php echo esc_attr($params['maxttl']); ?>"/></td>
					<td>
						<?php _e("<code>0</code> is engine dependent and not recommended unless you have much memory allocated for the cache.", 'emobjectcache'); ?>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="submit">
			<?php wp_nonce_field('configure-objectcache'); ?>
			<input type="hidden" name="action" value="save_emoc_generic_options"/>
			<input type="submit" name="submit" value="<?php esc_attr_e('Save Changes', 'emobjectcache'); ?>" class="button button-primary"/>
		</p>
	</form>

	<form method="post" action="<?php echo esc_attr(admin_url('admin-post.php')); ?>">
		<p class="submit">
			<?php wp_nonce_field('purge-objectcache'); ?>
			<input type="hidden" name="action" value="purge_emoc_cache"/>
			<input type="submit" name="purge" value="<?php esc_attr_e('Purge Cache', 'emobjectcache'); ?>" class="button" onclick="return confirm('<?php _e("Are you sure?", 'emobjectcache'); ?>')"/>
		</p>
	</form>
</div>
