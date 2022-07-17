<?php declare(strict_types = 1);

namespace OriNette\Application\FormMonitor;

use Nette\Forms\Form;

final class FormStack
{

	/** @var array<int|string, Form> */
	private array $forms = [];

	public function add(Form $form, ?string $key = null): void
	{
		if ($key !== null) {
			$this->forms[$key] = $form;
		} else {
			$this->forms[] = $form;
		}
	}

	/**
	 * @return array<int|string, Form>
	 */
	public function getAll(): array
	{
		return $this->forms;
	}

}
