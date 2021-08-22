<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\Inspector\DI;

use Nette\Application\Application;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use OriNette\Application\Inspector\InspectorEngine;
use OriNette\Application\Inspector\Tracy\InspectorPanel;
use OriNette\DI\Boot\ManualConfigurator;
use PHPUnit\Framework\TestCase;
use Tracy\Bar;
use function dirname;

/**
 * @runTestsInSeparateProcesses
 */
final class InspectorExtensionTest extends TestCase
{

	public function testEnabled(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setDebugMode(true);
		$configurator->addConfig(__DIR__ . '/extensions.neon');
		$configurator->addConfig(__DIR__ . '/inspector.enabled.neon');

		$container = $configurator->createContainer();

		$latteFactory = $container->getByType(LatteFactory::class);
		self::assertInstanceOf(InspectorEngine::class, $latteFactory->create());

		self::assertFalse($container->isCreated('application.application'));
		self::assertFalse($container->isCreated('tracy.bar'));

		$container->getByType(Application::class);

		self::assertTrue($container->isCreated('application.application'));
		self::assertTrue($container->isCreated('tracy.bar'));

		$bar = $container->getByType(Bar::class);
		self::assertInstanceOf(InspectorPanel::class, $bar->getPanel('uiInspector.panel'));
	}

	public function testDisabled(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setDebugMode(true);
		$configurator->addConfig(__DIR__ . '/extensions.neon');
		$configurator->addConfig(__DIR__ . '/inspector.disabled.neon');

		$container = $configurator->createContainer();

		$latteFactory = $container->getByType(LatteFactory::class);
		self::assertNotInstanceOf(InspectorEngine::class, $latteFactory->create());

		self::assertFalse($container->isCreated('application.application'));
		self::assertFalse($container->isCreated('tracy.bar'));

		$container->getByType(Application::class);

		self::assertTrue($container->isCreated('application.application'));
		self::assertTrue($container->isCreated('tracy.bar'));

		$bar = $container->getByType(Bar::class);
		self::assertNull($bar->getPanel('uiInspector.panel'));
	}

}
