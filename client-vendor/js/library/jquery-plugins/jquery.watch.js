/*
 * http://james.padolsey.com/javascript/monitoring-dom-properties/
 */

jQuery.fn.watch = function( id, fn ) {
 
    return this.each(function(){
 
        var self = this;
 
        var oldVal = self[id];
        $(self).data(
            'watch_timer',
            setInterval(function(){
                if (self[id] !== oldVal) {
                    fn.call(self, id, oldVal, self[id]);
                    oldVal = self[id];
                }
            }, 100)
        );
 
    });
 
    return self;
};
 
jQuery.fn.unwatch = function( id ) {
 
    return this.each(function(){
        clearInterval( $(this).data('watch_timer') );
    });
 
};
