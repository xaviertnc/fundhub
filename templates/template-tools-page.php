<div class="wrap">
<h1 class="wp-heading-inline">FUND HUB - Tools</h1>
<hr class="wp-header-end">
<form action="<?=esc_url( admin_url( 'admin-post.php' ) )?>" method="post">
	<h2>Reset Wordpress</h2>
  <p><label><input type="checkbox" name="clear_content" checked> Clear ALL Posts, Pages & Comments</label></p>
  <p><label><input type="checkbox" name="restore_plugins" checked> Restore Currently Active Plugins</label></p>
  <p><label><input type="checkbox" name="restore_theme" checked> Restore Current Theme</label></p>
  <?=$nonce_field?>
  <input type="hidden" name="action" value="fh_wp_reset">
  <?=submit_button( 'Reset Wordpress' )?>
</form>
<br>
<form action="<?=esc_url( admin_url( 'admin-post.php' ) )?>" method="post">
    <?=$nonce_field?>
    <input type="hidden" name="action" value="fh_export_data">
    <?=submit_button( 'Export Data' )?>
</form>
<br>
<form action="<?=esc_url( admin_url( 'admin-post.php' ) )?>" method="post">
    <?=$nonce_field?>
    <input type="hidden" name="action" value="fh_import_data">
    <?=submit_button( 'Import Data' )?>
</form>
</div>