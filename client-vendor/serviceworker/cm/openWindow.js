/**
 * @param {String} url
 * @returns {Promise}
 */
function openWindow(url) {
  return clients.matchAll({type: 'window'}).then(function(clientList) {
    for (var i = 0; i < clientList.length; i++) {
      var client = clientList[i];
      if (client.url == url && 'focus' in client) {
        return client.focus();
      }
    }
    if (clients.openWindow) {
      return clients.openWindow(url);
    }
  });
}

module.exports = openWindow;
