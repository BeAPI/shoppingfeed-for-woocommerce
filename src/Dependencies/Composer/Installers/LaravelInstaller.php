<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class LaravelInstaller extends BaseInstaller
{
    protected $locations = array(
        'library' => 'libraries/{$name}/',
    );
}
