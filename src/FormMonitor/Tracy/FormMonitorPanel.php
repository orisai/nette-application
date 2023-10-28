<?php declare(strict_types = 1);

namespace OriNette\Application\FormMonitor\Tracy;

use OriNette\Application\FormMonitor\FormErrorExtractor;
use OriNette\Application\FormMonitor\FormStack;
use Tracy\Helpers;
use Tracy\IBarPanel;

final class FormMonitorPanel implements IBarPanel
{

	private FormStack $formStack;

	private FormErrorExtractor $errorExtractor;

	public function __construct(FormStack $formStack)
	{
		$this->formStack = $formStack;
		$this->errorExtractor = new FormErrorExtractor();
	}

	public function getTab(): string
	{
		return Helpers::capture(function (): void {
			// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
			$formStack = $this->formStack;
			$errors = $this->getErrors();
			// phpcs:enable

			require __DIR__ . '/FormMonitor.tab.phtml';
		});
	}

	public function getPanel(): string
	{
		return Helpers::capture(function (): void {
			// phpcs:disable SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
			$formStack = $this->formStack;
			$errors = $this->getErrors();
			// phpcs:enable

			require __DIR__ . '/FormMonitor.panel.phtml';
		});
	}

	/**
	 * @return array<int|string, array<string, string>>
	 */
	private function getErrors(): array
	{
		$errors = [];
		foreach ($this->formStack->getAll() as $name => $form) {
			$formErrors = $this->errorExtractor->getErrors($form);

			if ($formErrors !== []) {
				$errors[$name] = $formErrors;
			}
		}

		return $errors;
	}

}
