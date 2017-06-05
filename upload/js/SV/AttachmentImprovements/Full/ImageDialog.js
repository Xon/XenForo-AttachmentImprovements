/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
    $.fn.bindFirst = function(name, fn) {
        var elem, handlers, i, _len;
        this.bind(name, fn);
        for (i = 0, _len = this.length; i < _len; i++) {
            elem = this[i];
            handlers = jQuery._data(elem).events[name.split('.')[0]];
            handlers.unshift(handlers.pop());
        }
    };

    // Add pre-commands
    document.uploadTarget = null;
    $("input:file").bindFirst("change",
        function(e){
            document.uploadTarget = e.target;
            console.log("first!");
        }
    );

    $("input:file").each(function(k, e){
        console.log(e);
    });
}
(jQuery, this, document);