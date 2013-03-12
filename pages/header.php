<?php if (!empty($params['error'])) : ?>
<div class="error"><p><?php echo $params['error']; ?></p></div>
<?php endif; ?>

<?php if (!empty($params['message'])) : ?>
<div class="updated fade"><p><?php echo $params['message']; ?></p></div>
<?php endif; ?>
