<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

/**
 *
 * Installer for kanboard plugins
 *
 * kanboard.net
 *
 * Class KanboardInstaller
 * @package ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers
 */
class KanboardInstaller extends BaseInstaller
{
    protected $locations = array(
        'plugin'  => 'plugins/{$name}/',
    );
}
