<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\FormMonitor\Tracy;

use Nette\Forms\Form;
use OriNette\Application\FormMonitor\FormStack;
use OriNette\Application\FormMonitor\Tracy\FormMonitorPanel;
use PHPUnit\Framework\TestCase;

final class FormMonitorPanelTest extends TestCase
{

	public function testRender(): void
	{
		$form = new Form();
		$form->addError('error');

		$stack = new FormStack();
		$stack->add($form);

		$panel = new FormMonitorPanel($stack);

		self::assertNotSame(
			'',
			$panel->getPanel(),
		);

		self::assertNotSame(
			'',
			$panel->getTab(),
		);
	}

}
