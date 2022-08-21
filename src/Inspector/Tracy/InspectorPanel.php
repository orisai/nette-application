<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector\Tracy;

use Latte\Engine;
use Nette\Application\Application;
use Nette\Application\UI\Component;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use OriNette\Application\Inspector\InspectorDataStorage;
use ReflectionClass;
use stdClass;
use Tracy\Helpers;
use Tracy\IBarPanel;
use function file_get_contents;
use function json_encode;

final class InspectorPanel implements IBarPanel
{

	private Application $application;

	private Engine $engine;

	private InspectorDataStorage $storage;

	private bool $development;

	public function __construct(
		Application $application,
		LatteFactory $latteFactory,
		InspectorDataStorage $storage,
		bool $development
	)
	{
		$this->application = $application;
		$this->engine = $latteFactory->create();
		$this->storage = $storage;
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
		$fullName = $this->getFullName($component);

		$componentList[$fullName] = (object) [
			'name' => $fullName,
			'depth' => $depth,
			'control' => $this->getControlInfo($component),
			'template' => $component instanceof Control ? $this->storage->get($component) : null,
		];

		$subDepth = $depth + 1;
		foreach ($component->getComponents() as $subcomponent) {
			if ($subcomponent instanceof Component) {
				$this->buildComponentList($componentList, $subcomponent, $subDepth);
			}
		}
	}

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

	private function getFullName(Component $component): string
	{
		if ($component instanceof Presenter) {
			return '__PRESENTER__';
		}

		return $component->lookupPath(Presenter::class, false)
			?? '__UNATTACHED_' . spl_object_id($component);
	}

}
