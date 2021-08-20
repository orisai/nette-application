<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector\DI;

use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use OriNette\Application\Inspector\InspectorEngine;
use OriNette\Application\Inspector\Tracy\InspectorPanel;
use Tracy\Bar;
use function assert;

final class ComponentInspectorExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'enabled' => Expect::bool(false),
		]);
	}

	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		if (!$config->enabled) {
			return;
		}

		$latteFactoryDefinition = $builder->getDefinitionByType(LatteFactory::class);
		assert($latteFactoryDefinition instanceof FactoryDefinition);

		$latteDefinition = $latteFactoryDefinition->getResultDefinition();
		assert($latteDefinition instanceof ServiceDefinition);
		$latteDefinition->setFactory(InspectorEngine::class);

		$tracyPanel = $builder->addDefinition($this->prefix('tracy.panel'))
			->setFactory(InspectorPanel::class)
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
