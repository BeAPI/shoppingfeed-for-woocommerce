<?php

namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class MiaoxingInstaller extends BaseInstaller
{
    protected $locations = array(
        'plugin' => 'plugins/{$name}/',
    );
}
