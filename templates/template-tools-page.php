<div class="wrap">
<h1 class="wp-heading-inline">FUND HUB - Tools</h1>
<hr class="wp-header-end">
<form action="<?=esc_url( admin_url( 'admin-post.php' ) )?>" method="post">
	<h2>Reset Wordpress</h2>
  <p><label><input type="checkbox" name="keep_theme" checked> Keep Current Theme</label></p>
  <p><label><input type="checkbox" name="keep_plugins" checked> Keep Currently Active Plugins</label></p>
  <p><label><input type="checkbox" name="clear_content" checked> Clear Posts, Pages & Comments</label></p>
  <?=$nonce_field?>
  <input type="hidden" name="action" value="fh_wp_reset">
  <?=submit_button( 'Reset Now !' )?>
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