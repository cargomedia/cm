/**
 * @class CM_Component_Abstract
 * @extends CM_View_Abstract
 */
var CM_Component_Abstract = CM_View_Abstract.extend({
  _class: 'CM_Component_Abstract',

  _ready: function() {
    CM_View_Abstract.prototype._ready.call(this);

    cm.dom.setup(this.$());

    this.on('destruct', function() {
      cm.dom.teardown(this.$());
    })
  },

  /**
   * Called on popOut()
   */
  repaint: function() {
  },

  bindRepaintOnWindowResize: function() {
    var self = this;
    var callback = function() {
      self.repaint();
    };
    $(window).on('resize', callback);
    this.on('destruct', function() {
      $(window).off('resize', callback);
    });
  },

  /**
   * @return jQuery
   */
  $: function(selector) {
    if (!selector) {
      return this.$el;
    }
    return $(selector, this.el);
  },

  /**
   * @param {Object} [options]
   * @param {Boolean} [removeOnClose]
   */
  popOut: function(options, removeOnClose) {
    this.repaint();
    //we don't use `this.$el.floatbox(options);` cause `this` component can be reloaded.
    var floatbox = new $.floatbox(options);
    floatbox.show(this.$el);
    this.repaint();

    if (removeOnClose) {
      var self = this;
      floatbox.$floatbox.one('floatbox-close', function() {
        self.remove();
      });
    }
  },

  popIn: function() {
    this.$el.floatbox('close');
  },

  /**
   * @param {String} message
   */
  error: function(message) {
    cm.window.hint(message);
  },

  /**
   * @param {String} message
   */
  message: function(message) {
    cm.window.hint(message);
  },

  /**
   * @param {Object} [params]
   * @param {Object} [options]
   * @return Promise
   */
  reload: function(params, options) {
    options = _.defaults(options || {}, {
      'modal': true,
      'method': 'reloadComponent'
    });
    params = params || {};
    return this
      .try(function() {
        return this.ajax(options.method, params, options);
      })
      .then(function(response) {
        return this._replaceView(response);
      });
  },

  /**
   * @param {String} className
   * @param {Object|Null} [params]
   * @param {Object|Null} [options]
   * @return Promise
   */
  replaceWithComponent: function(className, params, options) {
    if (!this.getParent()) {
      throw new CM_Exception('Cannot replace root component');
    }
    var handler = this;
    options = _.defaults(options || {}, {
      'modal': false
    });
    return this.getParent().prepareComponent(className, params, options)
      .then(function(component) {
        component.replaceWithHtml(this.$el);
        return component;
      });
  }
});
