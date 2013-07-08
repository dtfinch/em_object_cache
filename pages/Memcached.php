<?php defined('ABSPATH') or die(); ?>
<div class="wrap">
	<h2><?php _e("Memcached Caching Engine Options", 'emobjectcache'); ?></h2>

	<?php include(WP_PLUGIN_DIR . '/em-object-cache/pages/header.php'); ?>

	<form method="post" action="<?php echo esc_attr(admin_url('admin-post.php')); ?>">
		<table class="widefat">
			<tbody>
				<tr>
					<th scope="row" width="250"><?php _e("Prefix (must be unique for every site)", 'emobjectcache'); ?></th>
					<td><input type="text" name="options[prefix]" value="<?php echo esc_attr(!empty($params['options']['prefix']) ? $params['options']['prefix'] : ''); ?>"/></td>
					<td><?php echo sprintf(__('Default value is <code>%1$s</code>', 'emobjectcache'), md5($_SERVER['HTTP_HOST'])); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Memcached server', 'emobjectcache'); ?></th>
					<td>
<?php $i=1; foreach ($params['options']['server'] as $x) : ?>
						<div id="r<?php echo $i; ?>">
							<label><?php _e('Host:', 'emobjectcache'); ?> <input type="text" name="options[server][<?php echo $i; ?>][host]" value="<?php echo esc_attr($x['h']); ?>"/></label>
							<label><?php _e('Port:', 'emobjectcache'); ?> <input type="text" name="options[server][<?php echo $i; ?>][port]" value="<?php echo intval($x['p']); ?>" size="6"/></label>
							<label><?php _e('Weight:', 'emobjectcache'); ?> <input type="text" name="options[server][<?php echo $i; ?>][weight]" value="<?php echo intval($x['w']); ?>" size="5"/></label>
						</div>
<?php ++$i; endforeach; ?>
						<span class="hide-if-no-js" style="cursor: pointer; color: blue" id="addms"><?php _e('Add', 'emobjectcache'); ?></span>
					</td>
					<td>
						<?php _e('To delete a server, clear the <code>Host</code> field.', 'emobjectcache'); ?>
					</td>
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

<script type="text/javascript">
jQuery(
	function($)
	{
		$('#addms').click(
			function()
			{
				var r = $('#r1');
				var t = r.closest('td');
				var c = r.clone();
				var n = t.find('div').length;
				c.attr('id', '').find('input').val('').each(
					function()
					{
						$(this).attr('name', $(this).attr('name').replace(new RegExp('\[1\]', ''), n+1));
					}
				);
				t.find('span').before(c);
			}
		);
	}
);
</script>