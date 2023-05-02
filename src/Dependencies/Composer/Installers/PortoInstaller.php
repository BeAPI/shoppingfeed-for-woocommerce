<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class PortoInstaller extends BaseInstaller
{
    protected $locations = array(
        'container' => 'app/Containers/{$name}/',
    );
}
