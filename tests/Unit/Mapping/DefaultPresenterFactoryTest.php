<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\Mapping;

use Generator;
use Nette\Application\IPresenter;
use OriNette\Application\Mapping\DefaultPresenterFactory;
use Orisai\Exceptions\Logic\InvalidArgument;
use PHPUnit\Framework\TestCase;

final class DefaultPresenterFactoryTest extends TestCase
{

	private DefaultPresenterFactory $presenterFactory;

	protected function setUp(): void
	{
		parent::setUp();

		$cb = new class() {

			/**
			 * @param class-string<IPresenter> $class
			 */
			public function __invoke(string $class): IPresenter
			{
				return new $class();
			}

		};
		$this->presenterFactory = new DefaultPresenterFactory($cb);
	}

	public function testMappingStandard(): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([
			'Foo2' => 'App2\*\*Presenter',
			'Foo3' => 'My\App\*Mod\*Presenter',
		]);

		self::assertSame('FooPresenter', $factory->formatMappedPresenterClass('Foo'));
		self::assertSame('FooModule\BarPresenter', $factory->formatMappedPresenterClass('Foo:Bar'));
		self::assertSame('FooModule\BarModule\BazPresenter', $factory->formatMappedPresenterClass('Foo:Bar:Baz'));

		self::assertSame('Foo2Presenter', $factory->formatMappedPresenterClass('Foo2'));
		self::assertSame('App2\BarPresenter', $factory->formatMappedPresenterClass('Foo2:Bar'));
		self::assertSame('App2\Bar\BazPresenter', $factory->formatMappedPresenterClass('Foo2:Bar:Baz'));

		self::assertSame('My\App\BarPresenter', $factory->formatMappedPresenterClass('Foo3:Bar'));
		self::assertSame('My\App\BarMod\BazPresenter', $factory->formatMappedPresenterClass('Foo3:Bar:Baz'));

		self::assertSame('NetteModule\FooPresenter', $factory->formatMappedPresenterClass('Nette:Foo'));
	}

	public function testMappingWithUnspecifiedModule(): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([
			'Foo2' => 'App2\*Presenter',
			'Foo3' => 'My\App\*Presenter',
		]);

		self::assertSame('Foo2Presenter', $factory->formatMappedPresenterClass('Foo2'));
		self::assertSame('App2\BarPresenter', $factory->formatMappedPresenterClass('Foo2:Bar'));
		self::assertSame('App2\BarModule\BazPresenter', $factory->formatMappedPresenterClass('Foo2:Bar:Baz'));

		self::assertSame('My\App\BarPresenter', $factory->formatMappedPresenterClass('Foo3:Bar'));
		self::assertSame('My\App\BarModule\BazPresenter', $factory->formatMappedPresenterClass('Foo3:Bar:Baz'));
	}

	public function testMappingAllToOne(): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([
			'*' => ['App', 'Module\*', 'Presenter\*'],
		]);
		self::assertSame('App\Module\Foo\Presenter\Bar', $factory->formatMappedPresenterClass('Foo:Bar'));
		self::assertSame(
			'App\Module\Universe\Module\Foo\Presenter\Bar',
			$factory->formatMappedPresenterClass('Universe:Foo:Bar'),
		);
	}

	public function testMappingUniversal(): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([
			'*' => ['', '*', '*'],
		]);
		self::assertSame('Module\Foo\Bar', $factory->formatMappedPresenterClass('Module:Foo:Bar'));
	}

	/**
	 * @param array<string, string|array<string>> $mapping
	 *
	 * @dataProvider provideInvalidMapping
	 */
	public function testInvalidMapping(array $mapping, string $message): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage($message);

		$factory = $this->presenterFactory;
		$factory->setMapping($mapping);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidMapping(): Generator
	{
		yield [
			[
				'*' => ['*', '*'],
			],
			"Invalid mapping mask for module '*'.",
		];

		yield [
			[
				'Foo' => ['*', '*'],
			],
			"Invalid mapping mask for module 'Foo'.",
		];

		yield [
			[
				'*' => 'App\****Presenter',
			],
			"Invalid mapping mask 'App\****Presenter' for module '*'.",
		];

		yield [
			[
				'Foo' => 'App\****Presenter',
			],
			"Invalid mapping mask 'App\****Presenter' for module 'Foo'.",
		];
	}

}
