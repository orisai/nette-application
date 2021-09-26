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

	/** @var array<string, string|MissingLink> */
	private array $actionLinkPairs = [];

	private ?string $mappedName = null;

	/**
	 * @param class-string<IPresenter> $class
	 */
	public function __construct(string $class)
	{
		$this->class = $class;
	}

	/**
	 * @param string|MissingLink $link
	 */
	public function addActionLinkPair(string $action, $link): void
	{
		$this->actionLinkPairs[$action] = $link;
	}

	public function hasAction(string $action): bool
	{
		return array_key_exists($action, $this->actionLinkPairs);
	}

	/**
	 * @return class-string<IPresenter>
	 */
	public function getClass(): string
	{
		return $this->class;
	}

	/**
	 * @return array<string, string|MissingLink>
	 */
	public function getActionLinkPairs(): array
	{
		ksort($this->actionLinkPairs);

		return $this->actionLinkPairs;
	}

	public function setMappedName(string $mappedName): void
	{
		$this->mappedName = $mappedName;
	}

	public function getMappedName(): ?string
	{
		return $this->mappedName;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'class' => $this->class,
			'actionLinkPairs' => $this->actionLinkPairs,
			'mappedName' => $this->mappedName,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->class = $data['class'];
		$this->actionLinkPairs = $data['actionLinkPairs'];
		$this->mappedName = $data['mappedName'];
	}

}
