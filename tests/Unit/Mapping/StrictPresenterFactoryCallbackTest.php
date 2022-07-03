<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\Mapping;

use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenter;
use OriNette\Application\Mapping\StrictPresenterFactoryCallback;
use OriNette\DI\Boot\ManualConfigurator;
use PHPUnit\Framework\TestCase;
use Tests\OriNette\Application\Doubles\IPresenterImpl1;
use Tests\OriNette\Application\Doubles\IPresenterImpl2;
use Tests\OriNette\Application\Doubles\UiPresenterA;
use Tests\OriNette\Application\Doubles\UiPresenterAB;
use function dirname;

final class StrictPresenterFactoryCallbackTest extends TestCase
{

	public function testNotAService(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 3));
		$configurator->setForceReloadContainer();

		$container = $configurator->createContainer();

		$cb = new StrictPresenterFactoryCallback($container);

		$this->expectException(InvalidPresenterException::class);
		$this->expectExceptionMessage(
			"Presenter 'Tests\OriNette\Application\Doubles\IPresenterImpl1' is not registered as a service.",
		);

		$cb(IPresenterImpl1::class);
	}

	public function testExists(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 3));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/StrictPresenterFactoryCallback.neon');

		$container = $configurator->createContainer();

		$cb = new StrictPresenterFactoryCallback($container);

		$presenter = $cb(IPresenterImpl1::class);
		self::assertInstanceOf(IPresenterImpl1::class, $presenter);
	}

	public function testMultipleServicesCollision(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 3));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/StrictPresenterFactoryCallback.neon');

		$container = $configurator->createContainer();

		$cb = new StrictPresenterFactoryCallback($container);

		$this->expectException(InvalidPresenterException::class);
		$this->expectExceptionMessage(
			"Multiple services of type 'Nette\Application\IPresenter' found: 01, 02, 03, 04, 05.",
		);

		$cb(IPresenter::class);
	}

	public function testMultipleExactMatchCollision(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 3));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/StrictPresenterFactoryCallback.neon');

		$container = $configurator->createContainer();

		$cb = new StrictPresenterFactoryCallback($container);

		$this->expectException(InvalidPresenterException::class);
		$this->expectExceptionMessage(
			"Multiple services of type 'Tests\OriNette\Application\Doubles\IPresenterImpl2' found: 02, 03.",
		);

		$cb(IPresenterImpl2::class);
	}

	public function testMultipleServicesPreferExactMatch(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 3));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/StrictPresenterFactoryCallback.neon');

		$container = $configurator->createContainer();

		self::assertCount(2, $container->findByType(UiPresenterA::class));

		$cb = new StrictPresenterFactoryCallback($container);

		$presenter = $cb(UiPresenterA::class);
		self::assertInstanceOf(UiPresenterA::class, $presenter);
		self::assertNotInstanceOf(UiPresenterAB::class, $presenter);
	}

}
