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
        
        // Holder variable for uploads
        document.newAttachments = typeof document.newAttachments !== 'undefined' ? document.newAttachments : [];

        ed.saveSelection();

        var selHtml = ed.getSelectedHtml(), defaultVal;
        if (selHtml.match(/^\s*<img[^>]+src="([^"]+)"[^>]*>\s*$/) && !selHtml.match(/mceSmilie|attachFull|attachThumb/))
        {
            defaultVal = RegExp.$1; // this assignment is needed because jQuery uses a regex
            defaultVal = $('<textarea>').html(defaultVal).text();
        }

        ed.modalInit(this.getText('image'), { url: dialogUrl + '&dialog=image' }, 600, $.proxy(function()
        {
            var $input = $('#redactor_image_link');

            $('#redactor_image_btn').click(function(e) {
                e.preventDefault();

                ed.restoreSelection();

                var val = $input.val();
                if (val !== '')
                {
                   if (!val.match(/^https?:|ftp:/i))
                   {
                       val = 'http://' + val;
                   }

                   ed.pasteHtmlAtCaret('<img src="' + XenForo.htmlspecialchars(val) + '" alt="[IMG]" unselectable="on" />&nbsp;');
                }

                ed.modalClose();
                ed.observeImages();
                ed.syncCode();
                ed.focus();
            });

            if (defaultVal)
            {
                $input.val(defaultVal);
            }

            setTimeout(function() {
                $input.focus();
            }, 100);

            // Move the upload form out
            var $h = $("form form#hiddenAttachmentForm");
            $h.parent().closest("form.xenForm").siblings("form").remove();
            $h.insertAfter($h.parent().closest("form.xenForm"));

            // Activate XenForo scipts
            $('form#hiddenAttachmentForm').parent().xfActivate();

            // Function to insert to editor
            DialogAttachmentInserter = function(e) {
                var $attachment = $("img", this), $form = $('form#hiddenAttachmentForm');

                var target = $form.find("input[name='attachmentIdNormalizer']").val();
                var payload = {
                    "attachmentID": $attachment.data('attachmentid'),
                    "key": $form.find("input[name='key']").val(),
                    "hash": $form.find("input[name='hash']").val(),
                    "contentType": $form.find("input[name='content_type']").val(),
                };

                if (!target) {
                    console.log("No target URL found.");
                    return;
                }

                XenForo.ajax(
                    XenForo.canonicalizeUrl(target),
                    payload,
                    function(ajaxData){
                        if ('url' in ajaxData) {
                            ed.pasteHtmlAtCaret(
                                '<img src="' + ajaxData['url'] + '" class="attachFull bbCodeImage" alt="attachFull' + ajaxData['id'] + '" /> '
                            );

                            ed.modalClose();
                            ed.observeImages();
                            ed.syncCode();
                            ed.focus();
                        }
                    }
                );
            };

            // Bind swap function
            $("input:file.uploadFileInputOutside").change(function(e) {
                // Remove the existing hidden input
                $("form#hiddenAttachmentForm input:file").remove();

                // Clone modified file input and empty it
                var $clone = $("input:file.uploadFileInputOutside").clone(true);
                $clone.wrap('<form>').closest('form').get(0).reset();
                $clone.unwrap();

                // Replace the existing input in place, insert to hidden form and detach
                $("input:file.uploadFileInputOutside")
                    .replaceWith($clone)
                    .appendTo($("form#hiddenAttachmentForm"))
                    .removeClass("uploadFileInputOutside")
                    .unbind("change");

                // Bind new onchange
                XenForo.AutoInlineUploader($('form#hiddenAttachmentForm'));

                // Trigger the upload
                $('form#hiddenAttachmentForm input:file').trigger("change");
            });

            // Bind attachment inserter
            $(".singleAttachment").click(DialogAttachmentInserter);

            // Bind upload completion handler
            $('#redactor_modal #hiddenAttachmentForm').bind('AutoInlineUploadComplete', function(e) {
                var id = $("a._not_LbTrigger", e.ajaxData.templateHtml).data('attachmentid');

                var newAttachment = $("img", e.ajaxData.templateHtml)
                    .clone()
                    .attr('data-attachmentid', id)
                    .wrap('<div class="Thumbnail singleAttachment">')
                    .before('<span class="centeringHelper"></span>').parent()
                    .prependTo("#redactor_modal div.attachmentContainer")
                    .click(DialogAttachmentInserter);

                // Save element
                document.newAttachments.push(newAttachment);
            });

            // Reattach attachments on reloading overlay
            if (document.newAttachments.length){
                var container = $('#redactor_modal div.attachmentContainer');
                $.each(document.newAttachments, function(i, ele){
                    ele.prependTo(container).click(DialogAttachmentInserter);
                });
            }

        }, ed));
    };
}
(jQuery, this, document);