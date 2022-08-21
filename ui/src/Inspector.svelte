<script lang="ts">
	import type { InspectorComponentItem } from './lib/inspector/InspectorTypes'
	import ComponentList from "./lib/inspector/ComponentList.svelte";
	import InspectorToolbar from "./lib/inspector/InspectorToolbar.svelte";
	import { InspectorMode, mode } from './lib/inspector/store'
	import InspectMode from "./lib/inspector/mode/InspectMode.svelte";
	import ThreeDimensionalMode from "./lib/inspector/mode/ThreeDimensionalMode.svelte";
	import ComponentDetail from "./lib/inspector/ComponentDetail.svelte";
	import type { ComponentInfo } from './lib/inspector/mode/utils'

	export let componentList: InspectorComponentItem[] = []

	let selectedComponent: InspectorComponentItem | null = null

	function findComponent (fullName: string): InspectorComponentItem | null
	{
		return componentList.filter(item => item.fullName === fullName)[0] ?? null
	}

	function handleSelect (event: Event & { detail: ComponentInfo | null }) {
		if (event.detail !== null) {
			selectedComponent = findComponent(event.detail.name)
		} else {
			selectedComponent = null
		}
	}

	function handleSelectComponent (event: Event & { detail: InspectorComponentItem }) {
		selectedComponent = event.detail
	}
</script>

<div class="orisai-grid">
	<div>
		<ComponentList list={componentList} {selectedComponent} on:select={handleSelectComponent}/>
	</div>

	<div>
		<InspectorToolbar/>
		{#if selectedComponent !== null}
			<div>
				<ComponentDetail component={selectedComponent}/>
			</div>
		{/if}
	</div>
</div>

{#if $mode === InspectorMode.Inspect}
	<InspectMode on:inspect={handleSelect}/>
{:else if $mode === InspectorMode.ThreeDimensional}
	<ThreeDimensionalMode/>
{/if}

<style lang="sass">
	.orisai-grid
		display: grid
		grid-template-columns: auto 1fr
		column-gap: 24px

		> div
			width: auto
			overflow: auto

		> div:nth-child(2)
			display: flex
			flex-direction: column
			gap: 12px

			> div
				flex: 1
				overflow: auto

</style>
