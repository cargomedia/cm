(function(performance) {
  if (!performance.timing) {
    performance.timing = {
      navigationStart: Date.now(),
      isPolyfilled: true
    };
  }
  if (!performance.now) {
    performance.now = function() {
      return Date.now() - performance.timing.navigationStart;
    };
  }
})(window.performance = window.performance || {});
