<?php

declare(strict_types=1);

namespace Extension\ComponentInspector;

use Nette\Application\UI\TemplateFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Tracy\Bar;
use Tracy\Debugger;

class ComponentInspectorExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        Debugger::getBar()->addPanel(new InspectPanel());

        return Expect::structure([
            'active' => Expect::bool(false),
        ]);
    }

    public function loadConfiguration()
    {
        if ($this->config->active) {
            $builder = $this->getContainerBuilder();

            $factoryName = $builder->getByType(TemplateFactory::class);
            $factory = $builder->getDefinition($factoryName);
            $factory->setAutowired(false);

            $definition = new ServiceDefinition();
            $definition
                ->setFactory(InspectTemplateFactory::class)
                ->addSetup('setOriginalFactory', [$factoryName]);

            $builder->addDefinition($this->prefix('templateFactory'), $definition);
        }
    }

    public function beforeCompile()
    {
        if ($this->config->active) {
            $builder = $this->getContainerBuilder();

            $tracyPanel = $builder->addDefinition($this->prefix('tracy.panel'))
                ->setFactory(InspectPanel::class)
                ->setAutowired(false);

            $this->getInitialization()->addBody(
                '$this->getService(?)->addPanel($this->getService(?));',
                [
                    $builder->getByType(Bar::class),
                    $tracyPanel->getName(),
                ],
            );
        }
    }
}
