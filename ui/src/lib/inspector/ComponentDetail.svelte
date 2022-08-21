<script lang="ts">
    import RenderTime from "../RenderTime.svelte"
    import type { InspectorComponent } from "./InspectorTypes"
    import { getComponentViewName } from "./mode/utils.js"
    import ComponentEditorLinks from "./ComponentEditorLinks.svelte"

    export let component: InspectorComponent
</script>

<h2>{getComponentViewName(component)}</h2>

<div class="orisai-grid">
    <ComponentEditorLinks {component} />
    {#if component.template !== null}
        <RenderTime time={component.template.renderTime} />
    {/if}
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
