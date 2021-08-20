<?php declare(strict_types = 1);

namespace Extension\ComponentInspector\Tracy;

use Latte\Engine;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Tracy\IBarPanel;

final class InspectPanel implements IBarPanel
{

	private Engine $engine;

	public function __construct(LatteFactory $latteFactory)
	{
		$this->engine = $latteFactory->create();
	}

	public function getTab(): string
	{
		return $this->engine->renderToString(__DIR__ . '/InspectPanel.tab.latte');
	}

	public function getPanel(): string
	{
		return $this->engine->renderToString(__DIR__ . '/InspectPanel.panel.latte');
	}

}
