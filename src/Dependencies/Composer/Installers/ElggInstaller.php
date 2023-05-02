<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class ElggInstaller extends BaseInstaller
{
    protected $locations = array(
        'plugin' => 'mod/{$name}/',
    );
}
