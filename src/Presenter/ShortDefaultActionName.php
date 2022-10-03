<?php declare(strict_types = 1);

namespace OriNette\Application\Presenter;

use function ucfirst;

trait ShortDefaultActionName
{

	final public static function formatActionMethod(string $action): string
	{
		return parent::formatActionMethod($action !== self::DEFAULT_ACTION ? ucfirst($action) : '');
	}

	final public static function formatRenderMethod(string $view): string
	{
		return parent::formatRenderMethod($view !== self::DEFAULT_ACTION ? ucfirst($view) : '');
	}

}
