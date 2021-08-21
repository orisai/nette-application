<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector\Tracy;

use Latte\Engine;
use Nette\Application\Application;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Tracy\IBarPanel;
use function assert;

final class InspectorPanel implements IBarPanel
{

	private Application $application;

	private Engine $engine;

	/**
	 * @var array<string, mixed>
	 */
	private array $componentTree;

	public function __construct(Application $application, LatteFactory $latteFactory)
	{
		$this->application = $application;
		$this->engine = $latteFactory->create();
	}

	public function getTab(): string
	{
		return $this->engine->renderToString(__DIR__ . '/Inspector.tab.latte');
	}

	public function getPanel(): string
	{
		$scriptCode = \file_get_contents(__DIR__ . '/inspector.js');

		$this->componentTree = [];
		if (($presenter = $this->application->getPresenter()) !== null) {
			assert($presenter instanceof Presenter);
			$this->buildComponentTree($presenter);
		}

		return $this->engine->renderToString(__DIR__ . '/Inspector.panel.latte', [
			'scriptCode' => $scriptCode,
			'componentTree' => $this->componentTree,
		]);
	}

	/**
	 * @param Presenter|Control|Multiplier $control
	 * @param int $depth
	 */
	private function buildComponentTree($control, int $depth = 0): void
	{
		$this->componentTree[] = (object) [
			'name' => $control->name,
			'depth' => $depth,
			'isMultiplier' => $control instanceof Multiplier,
		];

		/** @var Control[] $components */
		$components = $control->getComponents();

		foreach ($components as $component) {
			if ($component instanceof Control || $component instanceof Multiplier) {
				$this->buildComponentTree($component, $depth + 1);
			}
		}
	}

}
