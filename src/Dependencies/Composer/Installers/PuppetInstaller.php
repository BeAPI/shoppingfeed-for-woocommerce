<?php

namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class PuppetInstaller extends BaseInstaller
{

    protected $locations = array(
        'module' => 'modules/{$name}/',
    );
}
