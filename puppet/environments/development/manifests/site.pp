node default {

  require 'ucf'

  class {'cm::application':
    development => true,
  }

  class { 'cm::services':
    ssl_key  => template('cm_ssl/wildcard.dev.cargomedia.ch.key'),
    ssl_cert => template('cm_ssl/wildcard.dev.cargomedia.ch.pem'),
  }

  env::variable {'PHP_IDE_CONFIG':
    value => 'serverName=www.cm.dev',
  }

}
