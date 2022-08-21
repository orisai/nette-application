<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector;

use Nette\Application\UI\Component;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use function spl_object_id;

final class Inspector
{

	/** @var array<array<mixed>> */
	private array $data;

	/**
	 * @param array<mixed> $data
	 */
	public function addTemplateData(Control $control, array $data): void
	{
		$id = spl_object_id($control);
		$this->data[$id] = $data;
	}

	/**
	 * @return array<mixed>|null
	 */
	public function getTemplateData(Control $control): ?array
	{
		$id = spl_object_id($control);

		return $this->data[$id] ?? null;
	}

	public function getFullName(Component $component): string
	{
		if ($component instanceof Presenter) {
			return '__PRESENTER__';
		}

		return $component->lookupPath(Presenter::class, false)
			?? '__UNATTACHED_' . spl_object_id($component);
	}

}
