<?php declare(strict_types = 1);

namespace OriNette\Application\ApplicationMap;

use Nette\Application\IPresenter;
use function array_key_exists;
use function ksort;

/**
 * @internal
 */
final class PresenterMeta
{

	/** @var class-string<IPresenter> */
	private string $class;

	/** @var array<string, string|null> */
	private array $actionUrlPairs = [];

	/**
	 * @param class-string<IPresenter> $class
	 */
	public function __construct(string $class)
	{
		$this->class = $class;
	}

	public function addActionUrlPair(string $action, ?string $url): void
	{
		$this->actionUrlPairs[$action] = $url;
	}

	public function hasAction(string $action): bool
	{
		return array_key_exists($action, $this->actionUrlPairs);
	}

	/**
	 * @return class-string<IPresenter>
	 */
	public function getClass(): string
	{
		return $this->class;
	}

	/**
	 * @return array<string, string|null>
	 */
	public function getActionUrlPairs(): array
	{
		ksort($this->actionUrlPairs);

		return $this->actionUrlPairs;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'class' => $this->class,
			'actionUrlPairs' => $this->actionUrlPairs,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->class = $data['class'];
		$this->actionUrlPairs = $data['actionUrlPairs'];
	}

}
