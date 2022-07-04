<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\Mapping;

use Generator;
use Nette\Application\IPresenter;
use OriNette\Application\Mapping\DefaultPresenterFactory;
use Orisai\Exceptions\Logic\InvalidArgument;
use PHPUnit\Framework\TestCase;
use Tests\OriNette\Application\Doubles\IPresenterImpl1;
use Tests\OriNette\Application\Doubles\IPresenterImpl2;

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

	/**
	 * @dataProvider provideStandardMapping
	 */
	public function testStandardMapping(string $class, string $name): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([
			'Foo2' => 'App2\*\*Presenter',
			'Foo3' => 'My\App\*Mod\*Presenter',
		]);

		self::assertSame($class, $factory->formatMappedPresenterClass($name));
		self::assertSame($name, $factory->formatMappedPresenterName($class));
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideStandardMapping(): Generator
	{
		yield ['FooPresenter', 'Foo'];
		yield ['FooModule\BarPresenter', 'Foo:Bar'];
		yield ['FooModule\BarModule\BazPresenter', 'Foo:Bar:Baz'];
		yield ['Foo2Presenter', 'Foo2'];
		yield ['App2\BarPresenter', 'Foo2:Bar'];
		yield ['App2\Bar\BazPresenter', 'Foo2:Bar:Baz'];
		yield ['My\App\BarPresenter', 'Foo3:Bar'];
		yield ['My\App\BarMod\BazPresenter', 'Foo3:Bar:Baz'];
		yield ['NetteModule\FooPresenter', 'Nette:Foo'];
	}

	/**
	 * @dataProvider provideMappingWithUnspecifiedModule
	 */
	public function testMappingWithUnspecifiedModule(string $class, string $name): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([
			'Foo2' => 'App2\*Presenter',
			'Foo3' => 'My\App\*Presenter',
		]);

		self::assertSame($class, $factory->formatMappedPresenterClass($name));
		self::assertSame($name, $factory->formatMappedPresenterName($class));
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideMappingWithUnspecifiedModule(): Generator
	{
		yield ['Foo2Presenter', 'Foo2'];
		yield ['App2\BarPresenter', 'Foo2:Bar'];
		yield ['App2\BarModule\BazPresenter', 'Foo2:Bar:Baz'];
		yield ['My\App\BarPresenter', 'Foo3:Bar'];
		yield ['My\App\BarModule\BazPresenter', 'Foo3:Bar:Baz'];
	}

	/**
	 * @dataProvider provideMappingAllToOne
	 */
	public function testMappingAllToOne(string $class, string $name): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([
			'*' => ['App', 'Module\*', 'Presenter\*'],
		]);

		self::assertSame($class, $factory->formatMappedPresenterClass($name));
		self::assertSame($name, $factory->formatMappedPresenterName($class));
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideMappingAllToOne(): Generator
	{
		yield ['App\Module\Foo\Presenter\Bar', 'Foo:Bar'];
		yield ['App\Module\Universe\Module\Foo\Presenter\Bar', 'Universe:Foo:Bar'];
	}

	/**
	 * @dataProvider provideUniversalMapping
	 */
	public function testUniversalMapping(string $class, string $name): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([
			'*' => ['', '*', '*'],
		]);

		self::assertSame($class, $factory->formatMappedPresenterClass($name));
		self::assertSame($name, $factory->formatMappedPresenterName($class));
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideUniversalMapping(): Generator
	{
		yield ['Module\Foo\Bar', 'Module:Foo:Bar'];
	}

	/**
	 * @dataProvider provideExactPresenterMapping
	 */
	public function testExactPresenterMapping(string $class, string $name): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([
			'Doggo:Does:Bjork' => IPresenterImpl1::class,
			'Neko:Does:Nyan' => IPresenterImpl2::class,
		]);

		self::assertSame($class, $factory->getPresenterClass($name));
		self::assertSame($name, $factory->getPresenterName($class));
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideExactPresenterMapping(): Generator
	{
		yield [IPresenterImpl1::class, 'Doggo:Does:Bjork'];
		yield [IPresenterImpl2::class, 'Neko:Does:Nyan'];
	}

	/**
	 * @dataProvider provideUsingClassDirectly
	 */
	public function testUsingClassDirectly(string $class, string $name): void
	{
		$factory = $this->presenterFactory;
		$factory->setMapping([]);

		self::assertSame($class, $factory->getPresenterClass($name));
		self::assertSame($name, $factory->getPresenterName($class));
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideUsingClassDirectly(): Generator
	{
		yield [IPresenterImpl1::class, IPresenterImpl1::class];
		yield [IPresenterImpl2::class, IPresenterImpl2::class];
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
