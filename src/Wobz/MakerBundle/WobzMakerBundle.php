<?php

namespace App\Wobz\MakerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WobzMakerBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Register your Maker commands here
        $container->register(BusClassesMaker::class)
            ->addTag('maker.bus.command')
            ->setAutoconfigured(true);

        $container->register(WorkflowTransitionMaker::class)
            ->addTag('maker.workflow.transition.command')
            ->setAutoconfigured(true);
    }
}