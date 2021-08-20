<?php

namespace Extension\ComponentInspector;

use Nette\Application\UI\Control;
use Nette\Application\UI\Template;
use Nette\Application\UI\TemplateFactory;
use Nette\DI\Container;

class InspectTemplateFactory implements TemplateFactory
{
    public TemplateFactory $originalFactory;

    public Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function setOriginalFactory(string $factoryName): void
    {
        /** @var TemplateFactory $service */
        $service = $this->container->getService($factoryName);
        $this->originalFactory = $service;
    }

    function createTemplate(Control $control = null): Template
    {
        return $this->originalFactory->createTemplate($control, InspectTemplate::class);
    }
}
