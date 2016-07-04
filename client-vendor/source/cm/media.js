var Event = require('./event');
var Observer = require('./observer');

/**
 * @class Media
 * @extends Event
 */
var Media = Event.extend({

  /**
   * @param {HTMLMediaElement} element
   * @param {Object} [options]
   * @param {Boolean} [options.loop=false]
   * @param {Boolean} [options.autoplay=false]
   * @param {String} [options.crossOrigin='anonymous']
   */
  constructor: function(element, options) {
    this._element = element;
    this._options = _.defaults(options || {}, {
      loop: false,
      autoplay: false,
      crossOrigin: 'anonymous'
    });

    this._sources = [];
    this._isPlaying = false;

    this.setOptions();
    this._setPromiseLoaded();
    this._bindElementEvents();
    this._shimElementPlay();
  },

  /**
   * @returns {HTMLMediaElement}
   */
  getElement: function() {
    return this._element;
  },

  /**
   * @param {Object} [options]
   */
  setOptions: function(options) {
    var element = this.getElement();
    _.chain(this._options)
      .extend(options || {})
      .each(function(value, name) {
        element[name] = value;
      });
  },

  /**
   * @returns {Promise}
   */
  play: function() {
    this.trigger('media:play');
    return this._play().then(function() {
      this.trigger('media:playing');
    }.bind(this));
  },

  /**
   * @returns {Promise}
   */
  stop: function() {
    this.trigger('media:stop');
    return this._stop().then(function() {
      this.trigger('media:stopped');
    }.bind(this));
  },

  /**
   * @returns {Promise}
   */
  release: function() {
    this.trigger('media:release');
    return this._release().then(function() {
      this.trigger('media:released');
    }.bind(this));
  },

  /**
   * @param {Boolean} state
   */
  mute: function(state) {
    this.getElement().muted = Boolean(state);
    this.trigger('media:mute', state);
  },

  /**
   * @param {MediaStream} stream
   */
  attachStream: function(stream) {
    this._setPromiseLoaded();
    var element = this.getElement();
    if ('srcObject' in element) {
      element.srcObject = stream;
    } else if ('mozSrcObject' in element) {
      element.mozSrcObject = stream;
    } else if ('src' in element) {
      element.src = URL.createObjectURL(stream);
    } else {
      throw Error('Failed to attach a stream to the media.');
    }
    this.trigger('media:attachStream', stream);
  },

  /**
   * @param {String[]} srcList
   */
  setSources: function(srcList) {
    _.each(srcList, this.setSource, this);
  },

  /**
   * @param {String} src
   */
  setSource: function(src) {
    this._setPromiseLoaded();
    var source = document.createElement('source');
    source.src = src;
    this.getElement().appendChild(source);
    this._sources.push(source);
    this.trigger('media:setSource', src);
  },

  /**
   * @returns {Boolean}
   */
  hasSource: function() {
    return this._sources.length > 0 || !!this.getElement().src || !!this.getElement().srcObject || !!this.getElement().mozSrcObject;
  },

  /**
   * @returns {Boolean}
   */
  isPlaying: function() {
    return this._isPlaying;
  },

  /**
   * @returns {Number|null}
   */
  getDuration: function() {
    var element = this.getElement();
    return !_.isNaN(element.duration) ? Math.round(element.duration * 1000) : null;
  },

  /**
   * @returns {Promise}
   */
  getPromiseLoaded: function() {
    return this._promiseLoaded;
  },

  /**
   * @returns {Promise}
   */
  getPromisePlaying: function() {
    return this._getPromiseForEvent('playing');
  },

  /**
   * @returns {Promise}
   */
  getPromiseStopped: function() {
    return this._getPromiseForEvent('stop');
  },

  /**
   * @returns {Promise}
   */
  getPromiseEmptied: function() {
    return this._getPromiseForEvent('emptied');
  },

  /**
   * @returns {Promise}
   * @private
   */
  _play: function() {
    var self = this;
    var element = this.getElement();

    if (!this.hasSource()) {
      return Promise.reject(new Error('Failed to play, no media source found.'));
    }

    if (this.isPlaying()) {
      return Promise.resolve(this);
    }

    var playing = self.getPromisePlaying();
    element.play();  // "canplay" is triggered by firefox only after calling play on the media
    return this
      .getPromiseLoaded()
      .then(function() {
        element.play();
      })
      .then(function() {
        return playing;
      });
  },

  /**
   * @returns {Promise}
   * @private
   */
  _stop: function() {
    if (!this.isPlaying()) {
      return Promise.resolve();
    }

    var stopped = this.getPromiseStopped();
    var element = this.getElement();
    element.pause();
    if (element.seekable.length > 0) {
      element.currentTime = 0;
    }
    return stopped;
  },

  /**
   * @returns {Promise}
   * @private
   */
  _release: function() {
    if (!this.hasSource()) {
      return Promise.resolve();
    }

    var emptied = this.getPromiseEmptied();
    var sources = this._sources;
    var element = this.getElement();
    return this
      .stop()
      .then(function() {
        _.each(sources, function(source) {
          element.removeChild(source);
        });
        sources.splice(0, sources.length);
        element.removeAttribute('src');
        element.removeAttribute('srcObject');
        element.removeAttribute('mozSrcObject');
        element.load();
        return emptied;
      });
  },

  /**
   * @private
   */
  _setPromiseLoaded: function() {
    this._promiseLoaded = this._getPromiseForEvent('canplay');
  },

  /**
   * @params {String}
   * @returns {Promise}
   * @private
   */
  _getPromiseForEvent: function(eventName) {
    var self = this;
    var observer = new Observer();
    var promise = new Promise(function(resolve, reject) {
      observer.listenTo(self, eventName, resolve);
      observer.listenTo(self, 'error', reject);
    });
    return promise.finally(function() {
      observer.stopListening();
    });
  },

  /**
   * @private
   */
  _bindElementEvents: function() {
    var self = this;

    this._element.addEventListener('playing', function() {
      self._isPlaying = true;
      self.trigger('playing');
    });

    this._element.addEventListener('pause', function() {
      self._isPlaying = false;
      self.trigger('stop');
    });

    this._element.addEventListener('emptied', function() {
      self._isPlaying = false;
      self._setPromiseLoaded();
      self.trigger('emptied');
    });

    this._element.addEventListener('canplay', function() {
      self.trigger('canplay');
    });

    this._element.addEventListener('error', function() {
      var errorCode = this.error ? this.error.code : null;
      var error = null;

      // see https://dev.w3.org/html5/spec-preview/media-elements.html#error-codes
      switch (errorCode) {
        case MediaError.MEDIA_ERR_ABORTED:
          error = new Error("The fetching process for the media resource was aborted by the user agent at the user's request.");
          break;
        case MediaError.MEDIA_ERR_NETWORK:
          error = new Error("A network error of some description caused the user agent to stop fetching the media resource, after the resource was established to be usable.");
          break;
        case MediaError.MEDIA_ERR_DECODE:
          error = new Error("An error of some description occurred while decoding the media resource, after the resource was established to be usable.");
          break;
        case MediaError.MEDIA_ERR_SRC_NOT_SUPPORTED:
          error = new Error("The media resource indicated by the src attribute was not suitable.");
          break;
        default:
          error = new Error('Unexpected media error.');
      }
      self.trigger('error', error);
    });

    this.on('all', function() {
      var args = _.toArray(arguments);
      if (/^media\:/.test(args[0])) {
        this.trigger.apply(this, ['media'].concat(args));
      }
    });
  },

  /**
   * Shim the "media.play()" to catch additional errors as warning only
   *
   * "media.play()" return a promise on Chrome, but not on FF
   * It's described as "void play();" in the W3C specs:
   * http://w3c.github.io/html/semantics-embedded-content.html#media-elements
   *
   * @private
   */
  _shimElementPlay: function() {
    var element = this.getElement();
    var originPlay = element.play;
    element.play = function() {
      (originPlay.call(this) || Promise.resolve()).catch(function(e) {
        console.warn(e);
      });
    };
  }
});

module.exports = Media;
