<script lang="ts">
	import RenderTime from "../RenderTime.svelte";
	import type { InspectorComponentItem } from './InspectorTypes'
	import { getComponentViewName } from "./mode/utils.js";
	import ComponentEditorLinks from "./ComponentEditorLinks.svelte";

	export let component: InspectorComponentItem
</script>

<h2>{getComponentViewName(component)}</h2>

<div class="orisai-grid">
	<ComponentEditorLinks {component} />
	<RenderTime time={component.template.renderTime}/>
</div>

{@html component.control.dump}

<style lang="sass">
	h2
		font-size: 17px
		margin: 0 0 8px !important

	.orisai-grid
		display: flex
		align-items: center
		gap: 8px
</style>
