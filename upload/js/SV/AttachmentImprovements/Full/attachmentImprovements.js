    /** @param {jQuery} $ jQuery Object */
    !function($, window, document, _undefined)
    {
        var href = $("input#ctrl_uploader").data("href");
        XenForo.ajax(
            href,
            
        )

        $('#uploadButton').click(function(e) {
            e.preventDefault();

            console.log("boop");
        });
    }
    (jQuery, this, document);