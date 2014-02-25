node default {

  include 'cm::services'

  class {'cm::application':
    development => true,
  }

}
