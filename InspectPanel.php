<?php

declare(strict_types=1);

namespace Extension\ComponentInspector;

use Latte\Engine;
use Tracy\IBarPanel;

class InspectPanel implements IBarPanel
{
    private Engine $latte;

    public function __construct(/*Engine $latte*/)
    {
        // @todo mozna jde ziskat nejak pres DI
        //$this->latte = $latte;
        $this->latte = new Engine();
    }

    public function getTab()
    {
        return $this->latte->renderToString(__DIR__ . '/InspectPanel.tab.latte');
	}

    public function getPanel()
    {
        return $this->latte->renderToString(__DIR__ . '/InspectPanel.panel.latte');
	}
}
