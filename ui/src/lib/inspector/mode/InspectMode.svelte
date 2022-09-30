<script lang="ts">
	import { createEventDispatcher } from "svelte"
	import { mode } from "../store"
	import { getComponentDescriptor } from "./utils"
	import type { ComponentDescriptor } from "./utils"
	import { SelectionMode } from "./utils"
	import Highlighter from "../Highlighter.svelte"
	import type { HighlighterRect } from "../InspectorTypes"

	interface HighlighterOptions {
		name: string
		rect: HighlighterRect
	}

	const dispatch = createEventDispatcher<{
		inspect: { componentDescriptor: ComponentDescriptor | null; selectionMode: SelectionMode }
	}>()

	let selectionMode: SelectionMode = SelectionMode.Info
	let highlighterOptions: HighlighterOptions[] = []

	function prepareHighlighters(componentDescriptor: ComponentDescriptor | null) {
		highlighterOptions = []

		if (componentDescriptor === null) {
			return
		}

		const preparedHighlighterOptionss: HighlighterOptions[] = []
		componentDescriptor.rootElements.forEach((rootElement) => {
			const domRect = rootElement.getBoundingClientRect()
			preparedHighlighterOptionss.push({
				name: componentDescriptor.fullName,
				rect: {
					x: domRect.left,
					y: domRect.top + window.document.documentElement.scrollTop,
					width: domRect.width,
					height: domRect.height
				}
			})
		})

		highlighterOptions = preparedHighlighterOptionss
	}

	function handleMouseMove(event: MouseEvent) {
		prepareHighlighters(getComponentDescriptor(event.target as HTMLElement))
	}

	function handleClick(event: MouseEvent) {
		event.preventDefault()
		event.stopImmediatePropagation()
		dispatch("inspect", {
			componentDescriptor: getComponentDescriptor(event.target as HTMLElement),
			selectionMode
		})
		$mode = null
		highlighterOptions = []
	}

	function handleKeyDown(event: KeyboardEvent) {
		if (event.key === "Escape") {
			event.preventDefault()
			event.stopImmediatePropagation()
			$mode = null
			return
		}

		if (event.metaKey || event.ctrlKey) {
			selectionMode = SelectionMode.PHP
		} else if (event.shiftKey) {
			selectionMode = SelectionMode.Latte
		} else {
			selectionMode = SelectionMode.Info
		}
	}

	function handleKeyUp() {
		selectionMode = SelectionMode.Info
	}
</script>

<svelte:window
	on:mousemove={handleMouseMove}
	on:click={handleClick}
	on:keydown={handleKeyDown}
	on:keyup={handleKeyUp}
/>

{#each highlighterOptions as option}
	<Highlighter name={option.name} {selectionMode} rect={option.rect} />
{/each}
