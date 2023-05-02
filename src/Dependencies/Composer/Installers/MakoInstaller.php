<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

class MakoInstaller extends BaseInstaller
{
    protected $locations = array(
        'package' => 'app/packages/{$name}/',
    );
}
