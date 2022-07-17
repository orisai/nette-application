<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\FormMonitor\DI;

use Nette\Application\Application;
use OriNette\Application\FormMonitor\FormStack;
use OriNette\Application\FormMonitor\SignalFormExtractor;
use OriNette\Application\FormMonitor\Tracy\FormMonitorPanel;
use OriNette\DI\Boot\ManualConfigurator;
use PHPUnit\Framework\TestCase;
use Tracy\Bar;
use function dirname;

final class FormMonitorExtensionTest extends TestCase
{

	private const FormStackService = 'orisai.application.formMonitor.formStack',
		SignalFormExtractorService = 'orisai.application.formMonitor.signalFormExtractor';

	private const FormMonitorPanel = 'orisai.application.formMonitor.panel';

	public function testNotEnabled(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/FormMonitorExtension.notEnabled.neon');

		$container = $configurator->createContainer();

		self::assertFalse($container->hasService(self::FormStackService));
		self::assertFalse($container->hasService(self::SignalFormExtractorService));
	}

	public function testEnabled(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/FormMonitorExtension.enabled.neon');

		$container = $configurator->createContainer();

		$formStack = $container->getService(self::FormStackService);
		self::assertInstanceOf(FormStack::class, $formStack);
		self::assertSame($formStack, $container->getByType(FormStack::class));

		$signalFormExtractor = $container->getService(self::SignalFormExtractorService);
		self::assertInstanceOf(SignalFormExtractor::class, $signalFormExtractor);
		self::assertNull($container->getByType(SignalFormExtractor::class, false));
	}

	public function testSignalExtractorWiredToApplication(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/FormMonitorExtension.signalExtractorWiredToApplication.neon');

		$container = $configurator->createContainer();

		self::assertFalse($container->isCreated(self::SignalFormExtractorService));
		$container->getByType(Application::class);
		self::assertTrue($container->isCreated(self::SignalFormExtractorService));
	}

	public function testPanelWiring(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/FormMonitorExtension.panelWiring.neon');

		$container = $configurator->createContainer();

		$bar = $container->getByType(Bar::class);
		self::assertNull($bar->getPanel(self::FormMonitorPanel));
		$container->getByType(FormStack::class);
		self::assertInstanceOf(FormMonitorPanel::class, $bar->getPanel(self::FormMonitorPanel));
	}

}
