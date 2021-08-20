<?php declare(strict_types = 1);

namespace Extension\ComponentInspector;

use Nette\Application\UI\TemplateFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Tracy\Bar;

final class ComponentInspectorExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'enabled' => Expect::bool(false),
		]);
	}

	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		if (!$this->config->enabled) {
			return;
		}

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

	public function beforeCompile(): void
	{
		parent::beforeCompile();

		if (!$this->config->enabled) {
			return;
		}

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
