/**
 * @param {String} method
 * @param {Object} params
 * @returns {Promise}
 */
function sendRpc(method, params) {
  var rpcData = JSON.stringify({method: method, params: params});
  return fetch('/rpc/' + config['site']['type'], {method: 'POST', body: rpcData}).then(function(response) {
    return response.json().then(function(result) {
      if (result['error']) {
        throw new Error('RPC call failed: ' + result['error']);
      }
      return result['success']['result'];
    });
  });
}

module.exports = sendRpc;
