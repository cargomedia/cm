var Event = require('../../event');
var Audio = require('../audio');

/**
 * @class AudioEngine
 * @extends Event
 */
var AudioEngine = Event.extend({

  /** @param {String} */
  _url: null,

  /** @param {AudioContext} */
  _context: null,

  /** @param {AudioNode[]} */
  _audioNodes: null,

  /**
   * @param {String} url
   */
  constructor: function(url) {
    if (!AudioEngine.isAudioAPISupported()) {
      throw new Error('Web Audio API not supported.');
    }

    this._url = url;
    this._context = AudioEngine.createAudioContext();
    this._audioNodes = [];

    this.initialize();
  },

  initialize: function() {
    var source = this.getContext().createBufferSource();

    var name, el = source;
    for (name in el) {
      if (/^on/.test(name)) {
        (function(event) {
          el.addEventListener(event, function() {
            console.debug(event, arguments);
          });
        })(name.match(/^on(.*)/)[1]);
      }
    }

    var gain = this.getContext().createGain();

    this.addAudioNode('source', source, gain);
    this.addAudioNode('gain', gain);

    this._connect();
  },

  /**
   * @returns {Promise}
   */
  start: function() {
    var source = this.getAudioNode('source');
    return this
      ._fetch()
      .then(function(buffer) {
        source.buffer = buffer;
      })
      .then(function() {
        source.start();
      });
  },

  /**
   * @param {String} name
   * @param {AudioNode} node
   * @param {AudioNode} [destination]
   */
  addAudioNode: function(name, node, destination) {
    this._audioNodes.push({
      name: name,
      node: node,
      destination: destination || this.getContext().destination
    });
  },

  /**
   * @param name
   * @returns {AudioNode|null}
   */
  getAudioNode: function(name) {
    var audioNode = _.find(this._audioNodes, function(audioNode) {
      return name === audioNode.name;
    });
    return audioNode ? audioNode.node : null;
  },

  /**
   * @returns {AudioContext}
   */
  getContext: function() {
    return this._context;
  },

  /**
   * @param {Number} value 0.0 - 1.0
   */
  setGain: function(value) {
    this.getAudioNode('gain').gain.value = value;
  },

  /**
   * @returns {Number} 0.0 - 1.0
   */
  getGain: function() {
    return this.getAudioNode('gain').gain.value;
  },

  _connect: function() {
    _.each(this._audioNodes, function(audioNode) {
      audioNode.node.connect(audioNode.destination);
    });
  },

  /**
   * @returns {Promise}
   * @private
   */
  _fetch: function() {
    var url = this._url;
    var context = this.getContext();
    var xhr = new XMLHttpRequest();

    return new Promise(function(resolve, reject) {
      xhr.addEventListener('error', reject);
      xhr.addEventListener('load', function() {
        if (xhr.status >= 200 && xhr.status < 400) {
          resolve(context.decodeAudioData(xhr.response));
        } else {
          reject(new Error('`' + url + '` request failed, status code: `' + xhr.status + '`.'));
        }
      });

      xhr.open('get', url);
      xhr.responseType = 'arraybuffer';
      xhr.send();
    });
  }
}, {

  /**
   * @returns {AudioContext}
   */
  createAudioContext: function() {
    return new (window.AudioContext || window.webkitAudioContext)();
  },

  /**
   * @returns {Boolean}
   */
  isAudioAPISupported: function() {
    return 'webkitAudioContext' in window || 'AudioContext' in window;
  }
});


module.exports = AudioEngine;
