<?php
namespace Joomla\Component\JoomlaHits\Administrator\Extension;

use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

class JoomlaHitsComponent extends MVCComponent implements BootableExtensionInterface
{
    public function boot(ContainerInterface $container)
    {
    }
}