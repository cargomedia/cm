node default {

  include 'cm::services'

  class {'cm::application':
    development => true,
  }

  environment::variable {'PHP_IDE_CONFIG':
    value => 'serverName=www.cm.dev',
  }

}
