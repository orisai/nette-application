<script lang="ts">
	import RenderTime from "../RenderTime.svelte";
	import type { InspectorComponentItem } from './InspectorTypes'

	export let component: InspectorComponentItem
</script>

<h2>{component.fullName}</h2>
<div>
	<RenderTime time={component.template.renderTime}/>
</div>

{@html component.control.dump}

<style lang="sass">
	h2
		font-size: 17px
</style>
