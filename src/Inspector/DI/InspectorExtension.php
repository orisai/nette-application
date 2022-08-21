<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector\DI;

use Nette\Application\Application;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use OriNette\Application\Inspector\InspectorEngine;
use OriNette\Application\Inspector\Tracy\InspectorPanel;
use stdClass;
use Tracy\Bar;
use function assert;

/**
 * @property-read stdClass $config
 */
final class InspectorExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'development' => Expect::bool(false),
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
		$latteDefinition->setFactory(InspectorEngine::class);

		$applicationDefinition = $builder->getDefinitionByType(Application::class);
		assert($applicationDefinition instanceof ServiceDefinition);

		$applicationDefinition->addSetup(
			[self::class, 'setupPanel'],
			[
				"$this->name.panel",
				$builder->getDefinitionByType(Bar::class),
				$applicationDefinition,
				$latteFactoryDefinition,
				$config->development,
			],
		);
	}

	public static function setupPanel(
		string $name,
		Bar $bar,
		Application $application,
		LatteFactory $latteFactory,
		bool $development
	): void
	{
		$bar->addPanel(
			new InspectorPanel($application, $latteFactory, $development),
			$name,
		);
	}

}
