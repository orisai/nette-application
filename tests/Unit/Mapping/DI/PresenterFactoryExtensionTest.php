<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\Mapping\DI;

use Nette\Application\IPresenterFactory;
use OriNette\Application\Mapping\DefaultPresenterFactory;
use OriNette\Application\Mapping\DI\StrictPresenterFactoryCallback;
use OriNette\DI\Boot\ManualConfigurator;
use Orisai\Exceptions\Logic\InvalidState;
use PHPUnit\Framework\TestCase;
use function dirname;

final class PresenterFactoryExtensionTest extends TestCase
{

	public function testFullOverride(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/PresenterFactoryExtension.fullOverride.neon');

		$container = $configurator->createContainer();

		$factory = $container->getByType(IPresenterFactory::class);
		self::assertInstanceOf(DefaultPresenterFactory::class, $factory);
		self::assertSame($factory, $container->getService('nette.application.presenterFactory'));

		self::assertNull($container->getByType(StrictPresenterFactoryCallback::class, false));
		self::assertInstanceOf(
			StrictPresenterFactoryCallback::class,
			$container->getService('orisai.application.presenterFactory.presenterConstructor'),
		);
	}

	public function testKeepCallback(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/PresenterFactoryExtension.keepCallback.neon');

		$container = $configurator->createContainer();

		$factory = $container->getByType(IPresenterFactory::class);
		self::assertInstanceOf(DefaultPresenterFactory::class, $factory);
		self::assertSame($factory, $container->getService('nette.application.presenterFactory'));

		self::assertNull($container->getByType(StrictPresenterFactoryCallback::class, false));
		self::assertFalse(
			$container->hasService('orisai.application.presenterFactory.presenterConstructor'),
		);
	}

	public function testMissingExtension(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/PresenterFactoryExtension.missingExtension.neon');

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(<<<'MSG'
Context: Registration of custom Nette\Application\IPresenterFactory failed.
Problem: Required extension Nette\Bridges\ApplicationDI\ApplicationExtension is
         not registered.
Solution: Register required extension.
MSG);

		$configurator->createContainer();
	}

}
