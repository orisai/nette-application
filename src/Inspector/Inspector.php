<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector;

use Latte\Runtime\Template as LatteTemplate;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template as ApplicationTemplate;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use ReflectionClass;
use ReflectionObject;
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

	/** @var array<int, array<int, LatteTemplate>> */
	private array $templates = [];

	public function __construct(TemplateFactory $templateFactory)
	{
		$templateFactory->onCreate[] = function (ApplicationTemplate $template): void {
			$engine = $template->getLatte();

			$engine->probe = function (LatteTemplate $template) use ($engine): void {
				$control = $engine->getProviders()['uiControl'] ?? null;

				if (!$control instanceof Control) {
					return;
				}

				$this->templates[spl_object_id($control)][] = $template;
			};
		};
	}

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

		$templateData = null;
		$latteTemplate = null;
		if ($component instanceof Control) {
			$templateData = $this->getTemplateData($component);
			$latteTemplate = $this->templates[spl_object_id($component)][0] ?? null;
		}

		$componentList[] = (object) [
			'showInTree' => $showInTree,
			'fullName' => $fullName,
			'shortName' => $component->getName(),
			'depth' => $depth,
			'id' => $id,
			'parentId' => $parentId,
			'control' => $this->getControlData($component),
			'template' => $templateData,
			'latteTemplates' => $latteTemplate !== null && $component instanceof Control
				? $this->buildTemplatesList($latteTemplate, $component)
				: null,
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
	 * @return array{shortName: string, fullName: string, editorUri: string|null, dump: string}
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
			'dump' => Dumper::toHtml($component, [
				Dumper::DEPTH => 2,
			]),
		];
	}

	/**
	 * @return array<array<mixed>>
	 */
	private function buildTemplatesList(LatteTemplate $template, Control $control): array
	{
		$list = [];
		$this->buildTemplatesListInternal($template, $control, $list);

		return $list;
	}

	/**
	 * @param array<array<mixed>> $list
	 */
	private function buildTemplatesListInternal(
		LatteTemplate $template,
		Control $control,
		array &$list,
		int $depth = 0,
		int $count = 1
	): void
	{
		$referenceType = $template->getReferenceType();

		$list[] = [
			'referenceType' => $referenceType,
			'referenceTypeEscaped' => $referenceType !== null ? Helpers::escapeHtml($referenceType) : null,
			'editorLink' => Helpers::editorLink($template->getName()),
			'phpFileUri' => Helpers::escapeHtml(Helpers::editorUri((new ReflectionObject($template))->getFileName())),
			'parametersDump' => Dumper::toHtml($template->getParameters(), [
				Dumper::DEPTH => 2,
			]),
			'depth' => $depth,
			'count' => $count,
		];

		$children = [];
		$counter = [];
		foreach ($this->templates[spl_object_id($control)] as $t) {
			if ($t->getReferringTemplate() === $template) {
				$name = $t->getName();
				$children[$name] = $t;

				if (isset($counter[$name])) {
					$counter[$name]++;
				} else {
					$counter[$name] = 1;
				}
			}
		}

		foreach ($children as $name => $t) {
			$this->buildTemplatesListInternal($t, $control, $list, $depth + 1, $counter[$name]);
		}
	}

}
