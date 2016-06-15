node default {}

node 'cm.dev.cargomedia.ch' {

  class {'cm::application':
    development => true,
  }

  class { 'cm::services':
    ssl_key  => template('cm_ssl/*.dev.cargomedia.ch.key'),
    ssl_cert => template('cm_ssl/*.dev.cargomedia.ch.pem'),
  }

  environment::variable {'PHP_IDE_CONFIG':
    value => 'serverName=www.cm.dev',
  }

}
