<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class ItopInstaller extends BaseInstaller
{
    protected $locations = array(
        'extension'    => 'extensions/{$name}/',
    );
}
