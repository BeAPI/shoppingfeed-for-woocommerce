<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class SyliusInstaller extends BaseInstaller
{
    protected $locations = array(
        'theme' => 'themes/{$name}/',
    );
}
