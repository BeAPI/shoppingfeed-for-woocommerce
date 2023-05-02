<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class CiviCrmInstaller extends BaseInstaller
{
    protected $locations = array(
        'ext'    => 'ext/{$name}/'
    );
}
