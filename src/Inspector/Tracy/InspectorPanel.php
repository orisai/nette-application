<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector\Tracy;

use Latte\Engine;
use Nette\Application\Application;
use Nette\Application\UI\Component;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use OriNette\Application\Inspector\Inspector;
use ReflectionClass;
use stdClass;
use Tracy\Helpers;
use Tracy\IBarPanel;
use function assert;
use function file_get_contents;
use function is_string;
use function json_encode;

final class InspectorPanel implements IBarPanel
{

	private Application $application;

	private Engine $engine;

	private Inspector $inspector;

	private bool $development;

	public function __construct(
		Application $application,
		LatteFactory $latteFactory,
		Inspector $inspector,
		bool $development
	)
	{
		$this->application = $application;
		$this->engine = $latteFactory->create();
		$this->inspector = $inspector;
		$this->development = $development;
	}

	public function getTab(): string
	{
		$presenter = $this->application->getPresenter();
		if (!$presenter instanceof Presenter) {
			return '';
		}

		return $this->engine->renderToString(__DIR__ . '/Inspector.tab.latte');
	}

	public function getPanel(): string
	{
		$presenter = $this->application->getPresenter();
		if (!$presenter instanceof Presenter) {
			return '';
		}

		$componentList = [];
		$this->buildComponentList($componentList, $presenter);

		return $this->engine->renderToString(
			__DIR__ . '/Inspector.panel.latte',
			[
				'development' => $this->development,
				'props' => json_encode([
					'componentList' => $componentList,
				]),
				'scriptCode' => !$this->development
					? file_get_contents(__DIR__ . '/../../../ui/dist/assets/main.js')
					: null,
				'styleCode' => !$this->development
					? file_get_contents(__DIR__ . '/../../../ui/dist/assets/main.css')
					: null,
			],
		);
	}

	/**
	 * @param array<int, stdClass> $componentList
	 */
	private function buildComponentList(array &$componentList, Component $component, int $depth = 0): void
	{
		$fullName = $this->inspector->getFullName($component);

		$componentList[] = (object) [
			'fullName' => $fullName,
			'depth' => $depth,
			'control' => $this->getControlInfo($component),
			'template' => $component instanceof Control ? $this->inspector->getTemplateData($component) : null,
		];

		$subDepth = $depth + 1;
		foreach ($component->getComponents() as $subcomponent) {
			if ($subcomponent instanceof Component) {
				$this->buildComponentList($componentList, $subcomponent, $subDepth);
			}
		}
	}

	/**
	 * @return array<mixed>
	 */
	private function getControlInfo(Component $component): array
	{
		$reflection = new ReflectionClass($component);
		$fileName = $reflection->getFileName();
		assert(is_string($fileName));

		return [
			'shortName' => $reflection->getShortName(),
			'fullName' => $reflection->getName(),
			'editorUri' => Helpers::editorUri($fileName),
		];
	}

}
