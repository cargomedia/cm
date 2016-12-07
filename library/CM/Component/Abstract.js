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

  popOut: function(options) {
    this.repaint();
    this.$el.floatbox(options);
    this.repaint();

    var self = this;
    this.$el.one('floatbox-close', function() {
      if (cm.window.isHidden(self.el)) {
        self.remove();
      }
      return false;
    });
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
   * @return Promise
   */
  reload: function(params) {
    return this.ajaxModal('reloadComponent', params);
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
