/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
    var xf_getImageModal = XenForo.BbCodeWysiwygEditor.prototype.getImageModal;
    XenForo.BbCodeWysiwygEditor.prototype.getImageModal = function(ed) {
        var dialogUrl = this.dialogUrl;
        var href = $("input#ctrl_uploader").data("href");
        if (href && this.dialogUrl) {
            var i = href.indexOf('?');
            dialogUrl = dialogUrl + "&" + href.substring(i + 1);
        }
        var self = this;

        ed.saveSelection();

        var selHtml = ed.getSelectedHtml(), defaultVal;
        if (selHtml.match(/^\s*<img[^>]+src="([^"]+)"[^>]*>\s*$/) && !selHtml.match(/mceSmilie|attachFull|attachThumb/))
        {
            defaultVal = RegExp.\$1; // this assignment is needed because jQuery uses a regex
            defaultVal = $('<textarea>').html(defaultVal).text();
        }

        ed.modalInit(this.getText('image'), { url: dialogUrl + '&dialog=image' }, 600, $.proxy(function()
        {
            var $input = $('#redactor_image_link');

            $('#redactor_image_btn').click(function(e) {
                e.preventDefault();

                // ed.restoreSelection();

                // var val = $input.val();
                // if (val !== '')
                // {
                //    if (!val.match(/^https?:|ftp:/i))
                //    {
                //        val = 'http://' + val;
                //    }

                //    ed.pasteHtmlAtCaret('<img src="' + XenForo.htmlspecialchars(val) + '" alt="[IMG]" unselectable="on" />&nbsp;');
                // }

                // ed.modalClose();
                // ed.observeImages();
                // ed.syncCode();
                // ed.focus();
            });

            if (defaultVal)
            {
                $input.val(defaultVal);
            }

            setTimeout(function() {
                $input.focus();
            }, 100);
            
            $('#redactor_modal_inner').xfActivate();

            $('#redactor_modal_inner .AttachmentUploadForm').bind('AutoInlineUploadComplete', function(e) {
                // upload complete, see; e.$form & e.ajaxData
                console.log("boop");
            });

        }, ed));
    };
}
(jQuery, this, document);