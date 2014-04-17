Vagrant.configure('2') do |config|
  config.ssh.forward_agent = true
  config.vm.box = 'cargomedia/debian-7-amd64-cm'

  config.vm.hostname = 'www.cm.dev'

  config.vm.network :private_network, ip: '10.10.10.13'
  config.vm.synced_folder '.', '/home/vagrant/cm', :type => 'nfs'

  config.phpstorm_tunnel.project_home = '/home/vagrant/cm'

  config.librarian_puppet.puppetfile_dir = 'puppet'
  config.librarian_puppet.placeholder_filename = '.gitkeep'
  config.librarian_puppet.resolve_options = {:force => true}
  config.vm.provision :puppet do |puppet|
    puppet.module_path = 'puppet/modules'
    puppet.manifests_path = 'puppet/manifests'
  end

  config.vm.provision 'shell', inline: [
    'cd /home/vagrant/cm',
    'composer --no-interaction install --dev',
  ].join(' && ')
end
