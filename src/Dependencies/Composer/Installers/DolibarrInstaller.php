<?php
namespace ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers;

/**
 * Class DolibarrInstaller
 *
 * @package ShoppingFeed\ShoppingFeedWC\Dependencies\Composer\Installers
 * @author  Raphaël Doursenaud <rdoursenaud@gpcsolutions.fr>
 */
class DolibarrInstaller extends BaseInstaller
{
    //TODO: Add support for scripts and themes
    protected $locations = array(
        'module' => 'htdocs/custom/{$name}/',
    );
}
