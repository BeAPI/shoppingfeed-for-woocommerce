<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class ChefInstaller extends BaseInstaller
{
    protected $locations = array(
        'cookbook'  => 'Chef/{$vendor}/{$name}/',
        'role'      => 'Chef/roles/{$name}/',
    );
}

