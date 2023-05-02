<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class AttogramInstaller extends BaseInstaller
{
    protected $locations = array(
        'module' => 'modules/{$name}/',
    );
}
