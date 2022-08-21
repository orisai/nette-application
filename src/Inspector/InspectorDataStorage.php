<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector;

use Nette\Application\UI\Control;
use function spl_object_id;

final class InspectorDataStorage
{

	/** @var array<array<mixed>> */
	private array $data;

	/**
	 * @param array<mixed> $data
	 */
	public function add(Control $control, array $data): void
	{
		$id = spl_object_id($control);
		$this->data[$id] = $data;
	}

	/**
	 * @return array<mixed>|null
	 */
	public function get(Control $control): ?array
	{
		$id = spl_object_id($control);

		return $this->data[$id] ?? null;
	}

}
