<?php declare(strict_types = 1);

namespace OriNette\Application\FormMonitor;

use Nette\Application\UI\Presenter;
use Nette\Forms\Form;
use Throwable;
use function array_pop;
use function count;
use function implode;

final class SignalFormExtractor
{

	private FormStack $formStack;

	public function __construct(FormStack $formStack)
	{
		$this->formStack = $formStack;
	}

	public function extractForm(Presenter $presenter): void
	{
		$signal = $presenter->getSignal();
		if ($signal === null || count($signal) < 2) {
			return;
		}

		array_pop($signal);
		$signalPath = implode('-', $signal);

		try {
			$component = $presenter[$signalPath] ?? null;
		} catch (Throwable $e) { // @phpstan-ignore-line Component creation may fail for any reason
			return;
		}

		if (!$component instanceof Form) {
			return;
		}

		$this->formStack->add($component, $signalPath);
	}

}
