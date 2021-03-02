<div class="wrap">
<h1 class="wp-heading-inline">FUND HUB - Tools</h1>
<hr class="wp-header-end">
<form action="<?=esc_url( admin_url( 'admin-post.php' ) )?>" method="post">
	<h2>Reset Wordpress</h2>
  <p><label><input type="checkbox" name="clear_content" checked> Delete ALL Posts, Pages & Comments</label></p>
  <p><label><input type="checkbox" name="restore_plugins" checked> Reactivate Currently Active Plugins</label></p>
  <p><label><input type="checkbox" name="restore_theme" checked> Restore Current Theme</label></p>
  <p><label style="color:crimson"><input type="checkbox" id="delete_uploads"
    name="delete_uploads" onchange="fh_confirm_delete_uploads(event)">
      Delete ALL Uploads</label></p>
  <?=$nonce_field?>
  <input type="hidden" name="action" value="fh_wp_reset">
  <input type="hidden" id="confirm_delete" name="confirm_delete_uploads" value="0">
  <?=submit_button( 'Reset Wordpress' )?>
</form>
<br>
<form action="<?=esc_url( admin_url( 'admin-post.php' ) )?>" method="post">
  <h2>Export / Backup Data</h2>
  <?=$nonce_field?>
  <input type="hidden" name="action" value="fh_export_data">
  <?=submit_button( 'Export Data' )?>
</form>
<br>
<form action="<?=esc_url( admin_url( 'admin-post.php' ) )?>" method="post">
  <h2>Restore / Import Data</h2>
  <p><label><input type="checkbox" name="import_pages" checked> Import Pages</label></p>
  <p><label><input type="checkbox" name="import_asm_posts" checked> Import Asset Manager Posts</label></p>
  <p><label><input type="checkbox" name="use_shortcodes"> Import Content As Multisite-Content Short-Code</label></p>
  <p><label><input type="checkbox" name="import_options" checked> Import Wordpress Settings</label></p>
  <p><label><input type="checkbox" name="import_theme" checked> Import Theme Settings</label></p>
  <p><label><input type="checkbox" name="import_blogname"> Import Site Name</label></p>
  <?=$nonce_field?>
  <input type="hidden" name="action" value="fh_import_data">
  <?=submit_button( 'Import Data' )?>
</form>
<script>
  var elDelUploads = document.getElementById('delete_uploads');
  var elConfirmDelete = document.getElementById('confirm_delete');
  function fh_confirm_delete_uploads( event ) {
    if ( ! elDelUploads.checked ) { elConfirmDelete.value = 0; return true; }
    event.preventDefault();
    event.stopImmediatePropagation();
    if( confirm('Delete ALL files in UPLOADS... Are you sure!?') ) {
      elConfirmDelete.value = 1;
    } else {
      elDelUploads.checked = false;
    }
  }
</script>
</div>