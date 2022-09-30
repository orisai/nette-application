<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector\DI;

use Nette\Application\Application;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use OriNette\Application\Inspector\Inspector;
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

	private ServiceDefinition $inspectorDefinition;

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'_dev' => Expect::bool(false),
			'enabled' => Expect::bool(false),
		]);
	}

	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		if (!$config->enabled) {
			return;
		}

		$this->inspectorDefinition = $builder->addDefinition($this->prefix('inspector'))
			->setFactory(Inspector::class)
			->setAutowired(false);
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
		$latteDefinition->addSetup('setInspector', [
			$this->inspectorDefinition,
		]);

		$applicationDefinition = $builder->getDefinitionByType(Application::class);
		assert($applicationDefinition instanceof ServiceDefinition);

		$applicationDefinition->addSetup(
			[self::class, 'setupPanel'],
			[
				"$this->name.panel",
				$builder->getDefinitionByType(Bar::class),
				$applicationDefinition,
				$latteFactoryDefinition,
				$this->inspectorDefinition,
				$config->_dev,
			],
		);
	}

	public static function setupPanel(
		string $name,
		Bar $bar,
		Application $application,
		LatteFactory $latteFactory,
		Inspector $inspector,
		bool $development
	): void
	{
		$bar->addPanel(
			new InspectorPanel($application, $latteFactory, $inspector, $development),
			$name,
		);
	}

}
