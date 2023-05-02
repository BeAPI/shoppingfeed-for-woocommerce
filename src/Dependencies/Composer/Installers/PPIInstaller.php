<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class PPIInstaller extends BaseInstaller
{
    protected $locations = array(
        'module' => 'modules/{$name}/',
    );
}
