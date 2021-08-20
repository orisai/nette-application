<?php declare(strict_types = 1);

namespace Extension\ComponentInspector\DI;

use Extension\ComponentInspector\Tracy\InspectPanel;
use Extension\ComponentInspector\InspectTemplateFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\DI\CompilerExtension;
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

		$factoryDefinition = $builder->getDefinitionByType(TemplateFactory::class);
		$factoryDefinition->setAutowired(false);

		$builder->addDefinition($this->prefix('templateFactory'))
			->setFactory(InspectTemplateFactory::class, [
				$factoryDefinition
			]);
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
