<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class ReIndexInstaller extends BaseInstaller
{
    protected $locations = array(
        'theme'     => 'themes/{$name}/',
        'plugin'    => 'plugins/{$name}/'
    );
}
