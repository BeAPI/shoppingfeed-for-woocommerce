<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class MODULEWorkInstaller extends BaseInstaller
{
    protected $locations = array(
        'module'    => 'modules/{$name}/',
    );
}
