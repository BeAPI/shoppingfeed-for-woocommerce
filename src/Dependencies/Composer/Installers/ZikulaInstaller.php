<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class ZikulaInstaller extends BaseInstaller
{
    protected $locations = array(
        'module' => 'modules/{$vendor}-{$name}/',
        'theme'  => 'themes/{$vendor}-{$name}/'
    );
}
