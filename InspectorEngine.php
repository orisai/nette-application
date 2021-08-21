<?php declare(strict_types = 1);

namespace OriNette\Application\Inspector;

use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Renderable;
use ReflectionClass;
use Tracy\Debugger;
use Tracy\Helpers;
use function array_map;
use function array_unshift;
use function basename;
use function file_exists;
use function implode;
use function json_encode;
use const JSON_THROW_ON_ERROR;
use const PHP_EOL;

final class InspectorEngine extends Engine
{

	/**
	 * @param object|array<mixed> $params
	 */
	public function render(string $name, $params = [], ?string $block = null): void
	{
		$control = $this->getProviders()['uiControl'] ?? null;
		if ($control instanceof Control) {
			Debugger::timer();
			$output = Helpers::capture(fn () => parent::render($name, $params, $block));
			$renderTime = Debugger::timer();

			echo $this->wrapOutput(
				$output,
				$control,
				$name,
				$renderTime
			);

			return;
		}

		parent::render($name, $params, $block);
	}

	/**
	 * @param object|array<mixed> $params
	 */
	public function renderToString(string $name, $params = [], ?string $block = null): string
	{
		$control = $this->getProviders()['uiControl'] ?? null;
		if ($control instanceof Control) {
			Debugger::timer();
			$output = parent::renderToString($name, $params, $block);
			$renderTime = Debugger::timer();
			return $this->wrapOutput(
				$output,
				$control,
				$name,
				$renderTime
			);
		}

		return parent::renderToString($name, $params, $block);
	}

	private function wrapOutput(string $output, Control $control, string $file, float $renderTime): string
	{
		$controlTreeInfo = $this->getControlTreeInfo($control, $file);
		$data = json_encode([
			'tree' => $controlTreeInfo,
			'renderTime' => $renderTime,
		], JSON_THROW_ON_ERROR);

		$name = implode(
			$control::NAME_SEPARATOR,
			array_map(static fn (array $item) => $item['name'], $controlTreeInfo),
		);

		$wrapped = "<!-- {control {$name} {$data}} -->" . PHP_EOL;
		$wrapped .= $output;
		$wrapped .= '<!-- {/control} -->';

		return $wrapped;
	}

	/**
	 * @return array<mixed>
	 */
	private function getControlTreeInfo(Control $control, string $file): array
	{
		$treeInfo = [];
		$lastRenderable = [];
		$fileExists = file_exists($file);
		$templateFile = $fileExists ? Helpers::editorUri($file) : '';
		$templateFileName = $fileExists ? basename($file) : '';

		while ($control !== null && !($control instanceof Presenter)) {
			$name = $control->getName();
			if ($name !== null) {

				if ($control instanceof Renderable) {
					$reflection = new ReflectionClass($control);
					$lastRenderable = [
						'templateFile' => $templateFile,
						'templateFileName' => $templateFileName,
						'file' => Helpers::editorUri($reflection->getFileName()),
						'className' => $reflection->getName(),
					];
				}

				array_unshift(
					$treeInfo,
					[
						'name' => $name,
					] + $lastRenderable,
				);
			}

			$control = $control->getParent();
		}

		return $treeInfo;
	}

}
