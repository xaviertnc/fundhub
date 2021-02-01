<div class="wrap">
<h1 class="wp-heading-inline">Theme Options</h1>
<hr class="wp-header-end">
<form action="<?=esc_url(admin_url('admin-post.php'))?>" method="post">
    <?=$nonce_field?>
    <input type="hidden" name="action" value="fh_export">
    <?=submit_button('Export Theme Data')?>
</form>
</div>