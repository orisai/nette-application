<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\Mapping\DI;

use Nette\Application\IPresenterFactory;
use OriNette\Application\Mapping\DefaultPresenterFactory;
use OriNette\DI\Boot\ManualConfigurator;
use PHPUnit\Framework\TestCase;
use function dirname;

final class PresenterFactoryOverrideTest extends TestCase
{

	public function testManualOverride(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 4));
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/PresenterFactoryOverride.neon');

		$container = $configurator->createContainer();

		$factory = $container->getByType(IPresenterFactory::class);
		self::assertInstanceOf(DefaultPresenterFactory::class, $factory);
		self::assertSame($factory, $container->getService('nette.application.presenterFactory'));
	}

}
