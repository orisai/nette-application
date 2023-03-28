<script lang="ts">
	import Portal from "svelte-portal/src/Portal.svelte"
	import { SelectionMode } from "./mode/utils"
	import type { HighlighterRect } from "./InspectorTypes"

	export let name: string
	export let selectionMode: SelectionMode
	export let rect: HighlighterRect | null = null
</script>

<Portal target={document.body}>
	<div
		class:orisai-mode-info={selectionMode === SelectionMode.Info}
		class:orisai-mode-php={selectionMode === SelectionMode.PHP}
		class:orisai-mode-latte={selectionMode === SelectionMode.Latte}
		style={rect === null
			? ""
			: `
			transform: translate3d(${rect.x}px, ${rect.y}px, 0);
			left: 0px;
			top: 0px;
			width: ${rect.width}px;
			height: ${rect.height}px;
		`}
	>
		<span>
			{name}
			-
			{selectionMode}
		</span>
	</div>
</Portal>

<style lang="postcss">
	div {
		--color: hsla(205, 100%, 50%, 1);
		--background: hsla(205, 100%, 50%, 0.32);
		--on-color: white;

		border: 1px solid var(--color);
		background: var(--background);
		pointer-events: none;
		position: absolute;
		border-radius: 0 var(--orisai-radius) var(--orisai-radius);
		z-index: 19000; /* 20000+ have Tracy panels */
	}

	.orisai-mode-info {
		--color: hsla(205, 100%, 50%, 1);
		--background: hsla(205, 100%, 50%, 0.32);
	}

	.orisai-mode-php {
		--color: #787cb5;
		--background: rgba(120, 124, 181, 0.32);
	}

	.orisai-mode-latte {
		--color: #f1a443;
		--background: rgba(241, 164, 67, 0.32);
	}

	span {
		position: absolute;
		padding: 2px 6px;
		background-color: var(--color);
		color: var(--on-color);
		border-radius: var(--orisai-radius) var(--orisai-radius) 0 0;
		font-family: system, sans-serif;
		font-size: 11px;
		bottom: 100%;
		left: -1px;
	}
</style>
