<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector;

use Nette\Application\UI\Component;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use ReflectionClass;
use stdClass;
use Tracy\Dumper;
use Tracy\Helpers;
use function assert;
use function is_string;
use function spl_object_id;

final class Inspector
{

	/** @var array<array{shortName: string|null, fullName: string, editorUri: string|null, renderTime: float}> */
	private array $templateData = [];

	/**
	 * @param array{shortName: string|null, fullName: string, editorUri: string|null, renderTime: float} $data
	 */
	public function addTemplateData(Control $control, array $data): void
	{
		$id = spl_object_id($control);
		$this->templateData[$id] = $data;
	}

	/**
	 * @return array{shortName: string|null, fullName: string, editorUri: string|null, renderTime: float}|null
	 */
	public function getTemplateData(Control $control): ?array
	{
		$id = spl_object_id($control);

		return $this->templateData[$id] ?? null;
	}

	public function getFullName(Component $component): string
	{
		if ($component instanceof Presenter) {
			return '__PRESENTER__';
		}

		return $component->lookupPath(Presenter::class, false)
			?? '__UNATTACHED_' . spl_object_id($component);
	}

	/**
	 * @return array<int, stdClass>
	 */
	public function buildComponentList(Presenter $presenter): array
	{
		$componentList = [];
		$this->buildComponentListInternal($componentList, $presenter);

		return $componentList;
	}

	/**
	 * @param array<int, stdClass> $componentList
	 */
	private function buildComponentListInternal(array &$componentList, Component $component, int $depth = 0): void
	{
		$fullName = $this->getFullName($component);

		$componentList[] = (object) [
			'fullName' => $fullName,
			'depth' => $depth,
			'control' => $this->getControlData($component),
			'template' => $component instanceof Control ? $this->getTemplateData($component) : null,
		];

		$subDepth = $depth + 1;
		foreach ($component->getComponents() as $subcomponent) {
			if ($subcomponent instanceof Component) {
				$this->buildComponentListInternal($componentList, $subcomponent, $subDepth);
			}
		}
	}

	/**
	 * @return array{shortName: string, fullName: string, editorUri: string|null}
	 */
	private function getControlData(Component $component): array
	{
		$reflection = new ReflectionClass($component);
		$fileName = $reflection->getFileName();
		assert(is_string($fileName));

		return [
			'shortName' => $reflection->getShortName(),
			'fullName' => $reflection->getName(),
			'editorUri' => Helpers::editorUri($fileName),
			'dump' => Dumper::toHtml($component),
		];
	}

}
