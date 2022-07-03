<?php declare(strict_types = 1);

namespace OriNette\Application\ApplicationMap\DI;

use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\PhpGenerator\Literal;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use OriNette\Application\ApplicationMap\ApplicationMap;
use OriNette\Application\ApplicationMap\LinkGeneratingPresenter;
use OriNette\Application\ApplicationMap\Tracy\ApplicationMapPanel;
use stdClass;
use Tracy\Bar;
use function assert;

/**
 * @property-read stdClass $config
 */
final class ApplicationMapExtension extends CompilerExtension
{

	private ServiceDefinition $presenterDefinition;

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'debug' => Expect::structure([
				'panel' => Expect::bool(false),
			]),
		]);
	}

	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();

		$this->presenterDefinition = $builder->addDefinition($this->prefix('presenter'))
			->setFactory(LinkGeneratingPresenter::class)
			->setAutowired(false);
	}

	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$applicationDefinition = $builder->getDefinitionByType(Application::class);
		assert($applicationDefinition instanceof ServiceDefinition);

		$presenterNames = [];
		foreach ($builder->findByType(IPresenter::class) as $presenterDefinition) {
			assert($presenterDefinition instanceof ServiceDefinition);
			if ($presenterDefinition->getName() !== $this->presenterDefinition->getName()) {
				$presenterNames[] = new Literal("\\{$presenterDefinition->getType()}::class");
			}
		}

		$applicationMapDefinition = $builder->addDefinition($this->prefix('map'))
			->setFactory(ApplicationMap::class, [
				'presenterClasses' => $presenterNames,
				'presenter' => $this->presenterDefinition,
			]);

		if ($config->debug->panel) {
			$applicationDefinition->addSetup(
				[self::class, 'setupPanel'],
				[
					"$this->name.panel",
					$builder->getDefinitionByType(Bar::class),
					$applicationMapDefinition,
				],
			);
		}
	}

	public static function setupPanel(
		string $name,
		Bar $bar,
		ApplicationMap $applicationMap
	): void
	{
		$bar->addPanel(
			new ApplicationMapPanel($applicationMap),
			$name,
		);
	}

}
