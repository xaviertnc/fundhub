/* Fund Hub Admin JS - Widgets Page */
jQuery(document).ready(function ($) {
  //console.log('FundHub:Image Widget JS says hi!');
  function media_upload(button_selector) {
    var _custom_media = true,
        _orig_send_attachment = wp.media.editor.send.attachment;
    $('body').on('click', button_selector, function () {
      var button_id = $(this).attr('id');
      wp.media.editor.send.attachment = function (props, attachment) {
        //console.log('FundHub:Image Widget wp.media.editor.send.attachment =', attachment);
        if (_custom_media) {
          $('.' + button_id + '_img').attr('src', attachment.url);
          var $input_url = $('.' + button_id + '_url');
          $input_url.val(attachment.url);
          $input_url.trigger('change');
        }
      };
      wp.media.editor.open($('#' + button_id));
      return false;
    });
  }
  media_upload('.js_custom_upload_media');
});