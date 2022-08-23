<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use ReflectionClass;
use stdClass;
use Tracy\Dumper;
use Tracy\Helpers;
use function assert;
use function is_string;
use function spl_object_id;

/**
 * @internal
 */
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
		$showInTree = true;

		$id = null;
		$parentId = null;
		if ($component instanceof Form) {
			$id = $component->getElementPrototype()->id;
		} elseif ($component instanceof BaseControl) {
			// Control interface is not supported
			$id = $component->getControlPrototype()->id;
			$showInTree = false;
			$form = $component->getForm();
			assert($form !== null);
			$parentId = $form->getElementPrototype()->id;
		}

		$controlData = null;
		$templateData = null;
		if ($component instanceof Control) {
			$controlData = $this->getControlData($component);
			$templateData = $this->getTemplateData($component);
		}

		$componentList[] = (object) [
			'showInTree' => $showInTree,
			'fullName' => $fullName,
			'shortName' => $component->getName(),
			'depth' => $depth,
			'id' => $id,
			'parentId' => $parentId,
			'control' => $controlData,
			'template' => $templateData,
		];

		$subDepth = $depth + 1;
		if ($component instanceof IContainer) {
			foreach ($component->getComponents() as $subcomponent) {
				if (!$subcomponent instanceof Component) {
					continue; // IComponent is not supported
				}

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
			'fullName' => $reflection->getName(),
			'shortName' => $reflection->getShortName(),
			'editorUri' => Helpers::editorUri($fileName),
			'dump' => Dumper::toHtml($component),
		];
	}

}
