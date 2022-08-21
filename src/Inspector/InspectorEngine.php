<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector;

use Latte\Engine;
use Nette\Application\UI\Control;
use Tracy\Debugger;
use Tracy\Helpers;
use function basename;
use function file_exists;
use const PHP_EOL;

/**
 * @internal
 */
final class InspectorEngine extends Engine
{

	private Inspector $inspector;

	public function setInspector(Inspector $inspector): void
	{
		$this->inspector = $inspector;
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
			$start = hrtime(true);
			$output = parent::renderToString($name, $params, $block);
			$renderTime = (hrtime(true) - $start) / 1e+6;

			return $this->wrapOutput($output, $control, $name, $renderTime);
		}

		return parent::renderToString($name, $params, $block);
	}

	private function wrapOutput(string $output, Control $control, string $file, float $renderTime): string
	{
		$info = $this->getTemplateData($file, $renderTime);
		$this->inspector->addTemplateData($control, $info);

		$fullName = $this->inspector->getFullName($control);

		$wrapped = "<!-- {control $fullName} -->" . PHP_EOL;
		$wrapped .= $output;
		$wrapped .= "<!-- {/control $fullName} -->" . PHP_EOL;

		return $wrapped;
	}

	/**
	 * @return array{shortName: string|null, fullName: string, editorUri: string|null, renderTime: float}
	 */
	private function getTemplateData(string $file, float $renderTime): array
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

}
