<?php

namespace PandawanTechnology\Neo4jBundle;

use PandawanTechnology\Neo4jBundle\DependencyInjection\CompilerPass\ConnectionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PandawanTechnologyNeo4jBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConnectionCompilerPass());
    }
}
