<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector\Tracy;

use Latte\Engine;
use Nette\Application\Application;
use Nette\Application\UI\Component;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use stdClass;
use Tracy\IBarPanel;
use function file_get_contents;

final class InspectorPanel implements IBarPanel
{

	private Application $application;

	private Engine $engine;

	public function __construct(Application $application, LatteFactory $latteFactory)
	{
		$this->application = $application;
		$this->engine = $latteFactory->create();
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
				'scriptCode' => file_get_contents(__DIR__ . '/inspector.js'),
				'componentList' => $componentList,
			],
		);
	}

	/**
	 * @param array<int, stdClass> $componentList
	 */
	private function buildComponentList(array &$componentList, Component $component, int $depth = 0): void
	{
		$componentList[] = (object) [
			'name' => $component->getName(),
			'depth' => $depth,
			'isMultiplier' => $component instanceof Multiplier,
		];

		foreach ($component->getComponents() as $subcomponent) {
			if ($subcomponent instanceof Control || $subcomponent instanceof Multiplier) {
				$this->buildComponentList($componentList, $subcomponent, $depth + 1);
			}
		}
	}

}
