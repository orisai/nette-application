<?php declare(strict_types = 1);

namespace OriNette\Application\FormMonitor;

use Nette\Forms\Container;
use Nette\Forms\Control;
use Nette\Forms\Form;
use function array_merge;

final class FormErrorExtractor
{

	/**
	 * @return array<string, string>
	 */
	public function getErrors(Form $form): array
	{
		$errors = [];
		foreach ($form->getOwnErrors() as $i => $error) {
			$errors["_$i"] = $error;
		}

		return array_merge($errors, $this->getContainerErrors($form, ''));
	}

	/**
	 * @return array<string, string>
	 */
	private function getContainerErrors(Container $container, string $parentKey): array
	{
		$errorsByComponent = [];
		foreach ($container->getComponents() as $name => $component) {
			if ($component instanceof Container) {
				$errorsByComponent[] = $this->getContainerErrors(
					$component,
					$parentKey === '' ? "$name-" : "$parentKey$name-",
				);
			}

			if ($component instanceof Control) {
				$errorsByComponent[] = $this->getControlErrors($component, $name, $parentKey);
			}
		}

		return array_merge(...$errorsByComponent);
	}

	/**
	 * @param int|string $name
	 * @return array<string, string>
	 */
	private function getControlErrors(Control $control, $name, string $parentKey): array
	{
		$errors = [];
		foreach ($control->getErrors() as $i => $error) {
			$errors["$parentKey{$name}_$i"] = $error;
		}

		return $errors;
	}

}
