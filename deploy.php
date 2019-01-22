<?php

namespace Deployer;

require 'recipe/symfony4.php';

// Project name
set('application', 'orbitrondev_account');

// Environment vars
add('env', [
    'APP_ENV' => 'prod',
]);

// Project repository
set('repository', 'https://github.com/D3strukt0r/service-account.git');
set('branch', 'master');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', ['var/data']);

// Writable dirs by web server
set('writable_dirs', []);

// Hosts
set('default_stage', 'dev');
host('local')
    ->hostname('local')
    ->set('deploy_path', '/var/www/{{application}}')
    ->set('http_user', 'www-data')
    ->set('ssh_multiplexing', true);
host('prod')
    ->hostname('prod')
    ->multiplexing(true)
    ->set('deploy_path', '/home/manuelec/public_html/{{application}}')
    ->stage('prod');

// Tasks
task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
//before('deploy:symlink', 'database:migrate');
