<script lang="ts">
	import { onDestroy } from 'svelte'
	import { mode } from '../store'

	onDestroy(() => {
		document.body.style.transform = ""
	})

	function handleMouseMove (event) {
		if (event.ctrlKey) {
			const rotateX = event.pageX;
			const rotateY = event.pageY;
			document.body.style.transform = `rotateX(${rotateY}deg) rotateY(${rotateX}deg)`
		}
	}

	function handleKeyDown (event: KeyboardEvent) {
		if (event.key === "Escape") {
			$mode = null
		}
	}
</script>

<svelte:window
	on:mousemove={handleMouseMove}
	on:keydown={handleKeyDown}
/>

<style lang="sass">
	.tracy-InspectorPanel-3DMode
		perspective: 1800px
		transform-style: preserve-3d

	.tracy-InspectorPanel-3DMode body
		transform-origin: center center
</style>
