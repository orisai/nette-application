<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\Presenter;

use Generator;
use PHPUnit\Framework\TestCase;
use Tests\OriNette\Application\Doubles\ShortDefaultActionNamePresenter;

final class ShortDefaultActionNameTest extends TestCase
{

	/**
	 * @dataProvider provide
	 */
	public function test(string $action, string $actionMethod, string $renderMethod): void
	{
		self::assertSame(
			$actionMethod,
			ShortDefaultActionNamePresenter::formatActionMethod($action),
		);

		self::assertSame(
			$renderMethod,
			ShortDefaultActionNamePresenter::formatRenderMethod($action),
		);
	}

	public function provide(): Generator
	{
		yield [
			'default',
			'action',
			'render',
		];

		yield [
			'other',
			'actionOther',
			'renderOther',
		];
	}

}
