<script lang="ts">
	import { createEventDispatcher } from 'svelte'
	import { mode } from '../store'
	import Portal from "svelte-portal/src/Portal.svelte"
	import { getComponentInfo } from './utils'

	enum HighlightingMode {
		Info = "info",
		PHP = "php",
		Latte = "latte"
	}

	const dispatch = createEventDispatcher()

	let name: string | null = null
	let highlightingElement: HTMLDivElement
	let highlightingMode: HighlightingMode = HighlightingMode.Info

	function highlightElement(target: HTMLElement, n: string)
	{
		const domRect = target.getBoundingClientRect()

		name = n

		highlightingElement.style.transform = `translate3d(${domRect.left}px, ${domRect.top + window.document.documentElement.scrollTop}px, 0)`
		highlightingElement.style.left = "0px"
		highlightingElement.style.top = "0px"
		highlightingElement.style.width = domRect.width + "px"
		highlightingElement.style.height = domRect.height + "px"
		highlightingElement.classList.remove("orisai-HighlightingElement--invisible")

		highlightingElement.addEventListener("transitionend", () => {
			highlightingElement.classList.add("orisai-HighlightingElement--active")
		}, {
			once: true
		})
	}

	function handleMouseMove (event: MouseEvent) {
		const componentInfo = getComponentInfo(event.target as HTMLElement)

		if (componentInfo !== null) {
			highlightElement(componentInfo.componentElement, componentInfo.name)
		} else if (highlightingElement) {
			highlightingElement.classList.add("orisai-HighlightingElement--invisible")
		}
	}

	function handleClick(event: MouseEvent) {
		event.preventDefault()
		event.stopImmediatePropagation()
		dispatch("inspect", getComponentInfo(event.target as HTMLElement))
		$mode = null
	}

	function handleKeyDown(event: KeyboardEvent) {
		if (event.key === "Escape") {
			event.preventDefault()
			event.stopImmediatePropagation()
			$mode = null
		}

		if (event.metaKey || event.ctrlKey) {
			highlightingMode = HighlightingMode.PHP
		} else if (event.shiftKey) {
			highlightingMode = HighlightingMode.Latte
		} else {
			highlightingMode = HighlightingMode.Info
		}
	}

	function handleKeyUp () {
		highlightingMode = HighlightingMode.Info
	}
</script>

<svelte:window
	on:mousemove={handleMouseMove}
	on:click={handleClick}
	on:keydown={handleKeyDown}
	on:keyup={handleKeyUp}
/>

<Portal target={document.body}>
	<div bind:this={highlightingElement}
		 class="orisai-HighlightingElement"
		 class:orisai-HighlightingElement--mode-info={highlightingMode === HighlightingMode.Info}
		 class:orisai-HighlightingElement--mode-php={highlightingMode === HighlightingMode.PHP}
		 class:orisai-HighlightingElement--mode-latte={highlightingMode === HighlightingMode.Latte}
	>
		<span class="orisai-HighlightingElement-name">
			{name}
			-
			{highlightingMode}
		</span>
	</div>
</Portal>

<style lang="sass">
	.orisai-HighlightingElement
		--color: hsla(205, 100%, 50%, 1)
		--background: hsla(205, 100%, 50%, 0.32)
		--on-color: white

		border: 1px solid var(--color)
		background: var(--background)
		pointer-events: none
		position: absolute
		border-radius: var(--orisai-radius)
		transition: opacity 0.24s ease

	.orisai-HighlightingElement--active
		transition-property: all
		will-change: transform
		z-index: 20227 /* 20228 ma panel od Tracy */

	.orisai-HighlightingElement--invisible
		opacity: 0

	.orisai-HighlightingElement--mode-info
		--color: hsla(205, 100%, 50%, 1)
		--background: hsla(205, 100%, 50%, 0.32)

	.orisai-HighlightingElement--mode-php
		--color: #787cb5
		--background: rgba(120, 124, 181, 0.32)

	.orisai-HighlightingElement--mode-latte
		--color: #F1A443
		--background: rgba(241, 164, 67, 0.32)

	.orisai-HighlightingElement-name
		position: absolute
		padding: 2px 4px
		background-color: var(--color)
		color: var(--on-color)
		border-radius: var(--orisai-radius) 0 var(--orisai-radius) 0
		font-family: system, sans-serif
		font-size: 11px
</style>
