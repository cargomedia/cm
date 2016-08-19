Vagrant.configure('2') do |config|
  config.ssh.forward_agent = true
  config.vm.box = 'cargomedia/debian-8-amd64-cm'

  config.vm.hostname = 'www.cm.dev.cargomedia.ch'

  config.vm.network :private_network, ip: '10.10.10.13'
  config.vm.synced_folder '.', '/home/vagrant/cm', :type => 'nfs'

  config.librarian_puppet.puppetfile_dir = 'puppet'
  config.librarian_puppet.placeholder_filename = '.gitkeep'
  config.librarian_puppet.resolve_options = {:force => true}
  config.vm.provision :puppet do |puppet|
    puppet.environment_path = 'puppet/environments'
    puppet.environment = 'development'
    puppet.module_path = ['puppet/modules', 'puppet/environments/development/modules']
  end

  config.vm.provision 'shell', inline: [
    'cd /home/vagrant/cm',
    'composer --no-interaction install',
  ].join(' && ')
end
