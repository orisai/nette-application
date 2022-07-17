<?php declare(strict_types = 1);

namespace Tests\OriNette\Application\Unit\FormMonitor;

use Nette\Application\UI\Presenter;
use OriNette\Application\FormMonitor\FormStack;
use OriNette\Application\FormMonitor\SignalFormExtractor;
use PHPUnit\Framework\TestCase;

final class SignalFormExtractorTest extends TestCase
{

	private Presenter $presenter;

	protected function setUp(): void
	{
		parent::setUp();

		$this->presenter = new class extends Presenter {

		};
	}

	public function testNoSignal(): void
	{
		$stack = new FormStack();
		$extractor = new SignalFormExtractor($stack);

		$extractor->extractForm($this->presenter);

		self::assertSame([], $stack->getAll());
	}

}
