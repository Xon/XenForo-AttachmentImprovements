/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
    var xf_getImageModal = XenForo.BbCodeWysiwygEditor.prototype.getImageModal;
    XenForo.BbCodeWysiwygEditor.prototype.getImageModal = function(ed) {
        originalUrl = this.dialogUrl;
        var href = $("input#ctrl_uploader").data("href");
        if (href && this.dialogUrl) {
            var i = href.indexOf('?');
            this.dialogUrl = this.dialogUrl + "&" + href.substring(i + 1);
        }
        try
        {
            return xf_getImageModal.apply(this, arguments);
        }
        finally
        {
            this.dialogUrl = originalUrl;
        }
    };
}
(jQuery, this, document);