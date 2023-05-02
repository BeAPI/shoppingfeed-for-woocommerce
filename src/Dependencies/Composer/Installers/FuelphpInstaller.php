<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class FuelphpInstaller extends BaseInstaller
{
    protected $locations = array(
        'component'  => 'components/{$name}/',
    );
}
