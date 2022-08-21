<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector\Tracy;

use Latte\Engine;
use Nette\Application\Application;
use Nette\Application\UI\Component;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Renderable;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use ReflectionClass;
use stdClass;
use Tracy\Helpers;
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

		$development = true; // @todo sem poslat nastaveni z NEONu

		return $this->engine->renderToString(
			__DIR__ . '/Inspector.panel.latte',
			[
				'development' => $development,
				'props' => json_encode([
					'componentList' => $componentList,
				]),
				'scriptCode' => !$development ? file_get_contents(__DIR__ . '/../../../ui/dist/assets/main.js') : null,
				'styleCode' => !$development ? file_get_contents(__DIR__ . '/../../../ui/dist/assets/main.css') : null,
			],
		);
	}

	/**
	 * @param array<int, stdClass> $componentList
	 */
	private function buildComponentList(array &$componentList, Component $component, int $depth = 0): void
	{
		$reflection = new ReflectionClass($component);

		$componentList[] = (object) [
			'name' => $component->getName(),
			'depth' => $depth,
			'isRenderable' => $component instanceof Renderable,
			'classShortName' => (new ReflectionClass($component))->getShortName(),
			'editorLink' => Helpers::editorUri($reflection->getFileName()),
		];

		$subDepth = $depth + 1;
		foreach ($component->getComponents() as $subcomponent) {
			if ($subcomponent instanceof Component) {
				$this->buildComponentList($componentList, $subcomponent, $subDepth);
			}
		}
	}

}
