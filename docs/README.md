# Nette Application

Extras for [nette/application](https://github.com/nette/application/)

## Content

- [Setup](#setup)
- [Component inspector](#component-inspector)

## Setup

Install with [Composer](https://getcomposer.org)

```sh
composer require orisai/nette-application
```

## Component inspector

Component inspector is a [Tracy](https://github.com/nette/tracy/) panel which lists all Nette components with
useful info and enables visual debug of these rendered via Latte.

To use it, register and enable extension:

```neon
extensions:
	uiInspector: OriNette\Application\Inspector\DI\InspectorExtension

uiInspector:
	enabled: true
```
