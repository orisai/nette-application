<?php declare(strict_types = 1);

namespace OriNette\Application\FormMonitor\DI;

use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use OriNette\Application\FormMonitor\FormStack;
use OriNette\Application\FormMonitor\SignalFormExtractor;
use OriNette\Application\FormMonitor\Tracy\FormMonitorPanel;
use stdClass;
use Tracy\Bar;
use function assert;

/**
 * @property-read stdClass $config
 */
final class FormMonitorExtension extends CompilerExtension
{

	private ServiceDefinition $formStackDefinition;

	private ServiceDefinition $signalFormExtractorDefinition;

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'enabled' => Expect::bool(false),
			'debug' => Expect::structure([
				'panel' => Expect::bool(false),
			]),
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

		$this->registerExtractor($builder);
		$this->registerFormStack($builder);
	}

	private function registerExtractor(ContainerBuilder $builder): void
	{
		$this->signalFormExtractorDefinition = $builder->addDefinition($this->prefix('signalFormExtractor'))
			->setFactory(SignalFormExtractor::class)
			->setAutowired(false);
	}

	private function registerFormStack(ContainerBuilder $builder): void
	{
		$this->formStackDefinition = $builder->addDefinition($this->prefix('formStack'))
			->setFactory(FormStack::class);
	}

	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		if (!$config->enabled) {
			return;
		}

		$this->addExtractorToApplication($builder);
		$this->addPanelToFormStack($builder, $config);
	}

	private function addExtractorToApplication(ContainerBuilder $builder): void
	{
		foreach ($builder->findByType(Application::class) as $applicationDefinition) {
			assert($applicationDefinition instanceof ServiceDefinition);

			$applicationDefinition->addSetup(
				[self::class, 'setupSignalFormExtractor'],
				[
					$applicationDefinition,
					$this->signalFormExtractorDefinition,
				],
			);
		}
	}

	private function addPanelToFormStack(ContainerBuilder $builder, stdClass $config): void
	{
		if (!$config->debug->panel) {
			return;
		}

		$this->formStackDefinition->addSetup(
			[self::class, 'setupPanel'],
			[
				"$this->name.panel",
				$builder->getDefinitionByType(Bar::class),
				$this->formStackDefinition,
			],
		);
	}

	public static function setupSignalFormExtractor(Application $application, SignalFormExtractor $extractor): void
	{
		$application->onPresenter[] = static function (Application $application, IPresenter $presenter) use ($extractor): void {
			if ($presenter instanceof Presenter) {
				$presenter->onStartup[] = static function () use ($presenter, $extractor): void {
					$extractor->extractForm($presenter);
				};
			}
		};
	}

	public static function setupPanel(
		string $name,
		Bar $bar,
		FormStack $formStack
	): void
	{
		$bar->addPanel(
			new FormMonitorPanel($formStack),
			$name,
		);
	}

}
