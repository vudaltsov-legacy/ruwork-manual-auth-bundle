<?php

namespace Ruvents\ManualAuthenticationBundle\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class Factory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        if (!$container->has(Provider::class)) {
            $container->register(Provider::class);
        }

        if (!$container->has(AuthenticationList::class)) {
            $container->register(AuthenticationList::class);
        }

        $container->autowire($listenerId = 'ruvents.manual_authentication.listener.'.$id, Listener::class)
            ->setArguments([
                '$firewallConfig' => new Reference('security.firewall.map.config.'.$id),
                '$manager' => new Reference('security.authentication.manager'),
            ])
            ->setPublic(false)
            ->addTag('kernel.event_subscriber')
            ->addTag('security.remember_me_aware', ['id' => $id, 'provider' => $userProvider]);

        return [
            Provider::class,
            $listenerId,
            $defaultEntryPoint,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'manual';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $builder)
    {
    }
}
