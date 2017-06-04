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
    document.uploadFlag = false;
    $("input[type='file']").bindFirst("change",
        function(e){
            document.uploadFlag = true;
            console.log("first!");
        }
    );
}
(jQuery, this, document);