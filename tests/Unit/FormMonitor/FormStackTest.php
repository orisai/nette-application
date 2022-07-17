<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\FormMonitor;

use Nette\Forms\Form;
use OriNette\Application\FormMonitor\FormStack;
use PHPUnit\Framework\TestCase;

final class FormStackTest extends TestCase
{

	public function test(): void
	{
		$stack = new FormStack();
		self::assertSame([], $stack->getAll());

		$f1 = new Form();
		$stack->add($f1, 'name');
		self::assertSame(
			[
				'name' => $f1,
			],
			$stack->getAll(),
		);

		$f2 = new Form();
		$stack->add($f2);
		self::assertSame(
			[
				'name' => $f1,
				0 => $f2,
			],
			$stack->getAll(),
		);
	}

}
