<?php declare(strict_types = 1);

namespace OriNette\Application\Mapping;

use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;

interface PresenterFactory extends IPresenterFactory
{

	/**
	 * Generates and checks presenter class name.
	 *
	 * @return class-string<IPresenter>
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(string &$name): string;

	/**
	 * Formats presenter name from class name.
	 *
	 * @param class-string<IPresenter> $class
	 */
	public function getPresenterName(string $class): string;

}
