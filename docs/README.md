# Nette Application

Extras for [nette/application](https://github.com/nette/application/)

## Content

- [Setup](#setup)
- [Application map](#application-map)
	- [Debug panel](#debug-panel)
- [Canonical link](#canonical-link)
	- [Extra parameters](#extra-parameters)
- [Form monitor](#form-monitor)
- [Inspector](#inspector)
- [Presenter mapping](#presenter-mapping)
- [Short default action name](#short-default-action-name)

## Setup

Install with [Composer](https://getcomposer.org)

```sh
composer require orisai/nette-application
```

## Application map

Application map is a [Tracy](https://github.com/nette/tracy/) panel which lists all Nette presenters and their actions
with corresponding links.

To use it, register extension:

```neon
extensions:
	orisai.application.map: OriNette\Application\ApplicationMap\DI\ApplicationMapExtension
```

You will also need to set up our [reworked presenter mapping](#presenter-mapping) to make it work.

### Debug panel

To show all presenters, their actions and links to them in Tracy panel, enable `debug > panel` option.

```neon
orisai.application.map:
	debug:
		panel: %debugMode%
```

## Canonical link

Generate canonical version of current url for better site indexing by webcrawlers.

Register service:

```neon
services:
	- OriNette\Application\CanonicalLink\CanonicalLinker()
```

Generate link to current page:

- without persistent parameters (of presenter and all components)
- without user-defined [extra parameters](#extra-parameters)

> Don't do this for error presenter. It is not routable and generating link would fail.

```php
use Nette\Application\UI\Presenter;
use OriNette\Application\CanonicalLink\CanonicalLinker;

abstract class BasePresenter extends Presenter
{

	private CanonicalLinker $canonicalLinker;

	final public function inject(CanonicalLinker $canonicalLinker): void
	{
		$this->canonicalLinker = $canonicalLinker;
	}

	public function beforeRender(): void
	{
		parent::beforeRender();

		$this->template->canonicalLink = $this->canonicalLinker->linkForPresenter($this);
	}

}
```

Render HTML tags for unique url:

```latte

<meta property="og:url" content="{$canonicalLink}">
<link rel="canonical" href="{$canonicalLink}">
```

### Extra parameters

Optionally, specify additional unwanted params:

```neon
services:
	- OriNette\Application\CanonicalLink\CanonicalLinker(['do'])
```

## Form monitor

Form monitor is a [Tracy](https://github.com/nette/tracy/) panel which lists all errors from a submitted
form (`Nette\Application\UI\Form`).

To use it, register and enable extension:

```neon
extensions:
	orisai.application.formMonitor: OriNette\Application\FormMonitor\DI\FormMonitorExtension

orisai.application.formMonitor:
	enabled: %debugMode%
	debug:
		panel: %debugMode%
```

## Inspector

Inspector is a [Tracy](https://github.com/nette/tracy/) panel which lists all Nette components with useful info and
enables visual debug of these rendered via Latte.

To use it, register and enable extension:

```neon
extensions:
	orisai.application.inspector: OriNette\Application\Inspector\DI\InspectorExtension

orisai.application.inspector:
	enabled: %debugMode%
```

## Presenter mapping

Overwrite default presenter factory for:

- `class-string<IPresenter>` to presenter name mapping (required by [application map](#application-map))
- mapping of each presenter individually

```neon
extensions:
	orisai.application.presenterFactory: OriNette\Application\Mapping\DI\PresenterFactoryExtension
```

Change also presenter factory callback to make sure all presenters are registered as services:

```neon
orisai.application.presenterFactory:
	presenterConstructor: 'strict'
```

# Short default action name

Use methods `action()` and `render()` instead of `actionDefault()` and `renderDefault()`

```diff
use Nette\Application\UI\Presenter;
+use OriNette\Application\Presenter\ShortDefaultActionName;

abstract class BasePresenter extends Presenter
{

+	use ShortDefaultActionName;

-	public function actionDefault(): void
+	public function action(): void
	{

	}

-	public function renderDefault(): void
+	public function render(): void
	{

	}

	public function actionOther(): void
	{

	}

	public function renderOther(): void
	{

	}

}
```
