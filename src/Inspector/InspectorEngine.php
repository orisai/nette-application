<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector;

use Latte\Engine;
use Nette\Application\UI\Component;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Renderable;
use ReflectionClass;
use Tracy\Debugger;
use Tracy\Helpers;
use function array_map;
use function array_unshift;
use function assert;
use function basename;
use function file_exists;
use function implode;
use function is_string;
use const PHP_EOL;

final class InspectorEngine extends Engine
{

	private InspectorDataStorage $storage;

	public function setDataStorage(InspectorDataStorage $storage): void
	{
		$this->storage = $storage;
	}

	/**
	 * {@inheritDoc}
	 */
	public function render(string $name, $params = [], ?string $block = null): void
	{
		$control = $this->getProviders()['uiControl'] ?? null;
		if ($control instanceof Control) {
			Debugger::timer();
			$output = Helpers::capture(fn () => parent::render($name, $params, $block));
			$renderTime = Debugger::timer();

			echo $this->wrapOutput($output, $control, $name, $renderTime);

			return;
		}

		parent::render($name, $params, $block);
	}

	/**
	 * {@inheritDoc}
	 */
	public function renderToString(string $name, $params = [], ?string $block = null): string
	{
		$control = $this->getProviders()['uiControl'] ?? null;
		if ($control instanceof Control) {
			Debugger::timer();
			$output = parent::renderToString($name, $params, $block);
			$renderTime = Debugger::timer();

			return $this->wrapOutput($output, $control, $name, $renderTime);
		}

		return parent::renderToString($name, $params, $block);
	}

	private function wrapOutput(string $output, Control $control, string $file, float $renderTime): string
	{
		$info = $this->getTemplateInfo($file, $renderTime);
		$this->storage->add($control, $info);

		$fullName = $this->getFullName($control);

		$wrapped = "<!-- {control $fullName} -->" . PHP_EOL;
		$wrapped .= $output;
		$wrapped .= "<!-- {/control $fullName} -->" . PHP_EOL;

		return $wrapped;
	}

	/**
	 * @return array<mixed>
	 */
	private function getTemplateInfo(string $file, float $renderTime): array
	{
		if (file_exists($file)) {
			$editorUri = Helpers::editorUri($file);
			$shortName = basename($file);
		} else {
			$editorUri = null;
			$shortName = null;
		}

		return [
			'shortName' => $shortName,
			'fullName' => $file,
			'editorUri' => $editorUri,
			'renderTime' => $renderTime,
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
