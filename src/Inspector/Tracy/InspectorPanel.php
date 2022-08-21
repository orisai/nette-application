<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector\Tracy;

use Latte\Engine;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\Utils\Json;
use OriNette\Application\Inspector\Inspector;
use Tracy\IBarPanel;
use function file_get_contents;

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

		return $this->engine->renderToString(
			__DIR__ . '/Inspector.panel.latte',
			[
				'development' => $this->development,
				'props' => Json::encode([
					'componentList' => $this->inspector->buildComponentList($presenter),
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

}
