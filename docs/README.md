# Nette Application

Extras for [nette/application](https://github.com/nette/application/)

## Content

- [Setup](#setup)
- [Application map](#application-map)
  - [Debug panel](#debug-panel)
- [Component inspector](#component-inspector)
- [Presenter mapping](#presenter-mapping)

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

## Component inspector

Component inspector is a [Tracy](https://github.com/nette/tracy/) panel which lists all Nette components with useful
info and enables visual debug of these rendered via Latte.

To use it, register and enable extension:

```neon
extensions:
	orisai.application.inspector: OriNette\Application\Inspector\DI\InspectorExtension

orisai.application.inspector:
	enabled: true
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
