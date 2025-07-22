<?php

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Piedpiper\Component\JoomlaHits\Administrator\Extension\JoomlaHitsComponent;

return new class implements ServiceProviderInterface
{
	public function register(Container $container)
	{
        $container->registerServiceProvider(new MVCFactory('\\Piedpiper\\Component\\JoomlaHits'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Piedpiper\\Component\\JoomlaHits'));

        $container->set(
            ComponentInterface::class,
            function (Container $container)
            {
                $component = new JoomlaHitsComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                return $component;
            }
        );
	}
};