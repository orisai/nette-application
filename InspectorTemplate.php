<?php declare(strict_types = 1);

namespace Extension\ComponentInspector;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Renderable;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\InvalidStateException;
use Nette\Security\User;
use Nette\Utils\Arrays;
use ReflectionClass;
use stdClass;
use Tracy\Helpers;
use function array_map;
use function array_unshift;
use function basename;
use function implode;
use function json_encode;
use function property_exists;
use const JSON_THROW_ON_ERROR;

class InspectorTemplate extends Template
{

	public Presenter $presenter;

	public Control $control;

	public User $user;

	public string $baseUrl;

	public string $basePath;

	/** @var array<stdClass> */
	public array $flashes = [];

	/**
	 * @param mixed $value
	 * @return static
	 */
	public function add(string $name, $value): self
	{
		if (property_exists($this, $name)) {
			throw new InvalidStateException("The variable '$name' already exists.");
		}

		$this->$name = $value;

		return $this;
	}

	/**
	 * @param array<mixed> $params
	 * @return static
	 */
	public function setParameters(array $params): self
	{
		Arrays::toObject($params, $this);

		return $this;
	}

	/**
	 * @param array<mixed> $params
	 */
	public function render(?string $file = null, array $params = []): void
	{
		$controlTreeInfo = $this->getControlTreeInfo($this->control, $file ?? $this->getFile());
		$data = json_encode($controlTreeInfo, JSON_THROW_ON_ERROR);

		$name = implode(
			$this->control::NAME_SEPARATOR,
			array_map(static fn (array $item) => $item['name'], $controlTreeInfo),
		);

		echo "<!-- {control {$name} {$data}} -->";
		parent::render($file, $params);
		echo '<!-- {/control} -->';
	}

	/**
	 * @return array<mixed>
	 */
	private function getControlTreeInfo(Control $control, ?string $file): array
	{
		$treeInfo = [];
		$lastRenderable = [];

		while ($control !== null && !($control instanceof Presenter)) {
			$name = $control->getName();
			if ($name !== null) {

				if ($control instanceof Renderable) {
					$reflection = new ReflectionClass($control);
					$lastRenderable = [
						'templateFile' => $file !== null ? Helpers::editorUri($file) : '',
						'templateFileName' => $file !== null ? basename($file) : '',
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
