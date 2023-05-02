<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class BonefishInstaller extends BaseInstaller
{
    protected $locations = array(
        'package'    => 'Packages/{$vendor}/{$name}/'
    );
}
