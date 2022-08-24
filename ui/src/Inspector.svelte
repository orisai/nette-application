<script lang="ts">
    import type { InspectorComponent } from "./lib/inspector/InspectorTypes"
    import ComponentList from "./lib/inspector/ComponentList.svelte"
    import InspectorToolbar from "./lib/inspector/InspectorToolbar.svelte"
    import { InspectorMode, mode } from "./lib/inspector/store"
    import InspectMode from "./lib/inspector/mode/InspectMode.svelte"
    import ThreeDimensionalMode from "./lib/inspector/mode/ThreeDimensionalMode.svelte"
    import ComponentDetail from "./lib/inspector/ComponentDetail.svelte"
    import type { ComponentDescriptor } from "./lib/inspector/mode/utils"
    import { SelectionMode } from "./lib/inspector/mode/utils"
    import { showHelp } from "./lib/inspector/store.js"
    import Key from "./lib/Key.svelte"

    export let componentList: InspectorComponent[] = []

    let selectedComponent: InspectorComponent | null = null

    function handleInspect(
        event: Event & {
            detail: {
                componentDescriptor: ComponentDescriptor | null
                selectionMode: SelectionMode
            }
        }
    ) {
        const { componentDescriptor, selectionMode } = event.detail

        if (componentDescriptor === null) {
            selectedComponent = null
            return
        }

        selectedComponent =
            componentList.find((item) => item.fullName === componentDescriptor.fullName) || null

        if (selectedComponent === null) {
            return
        }

        if (selectionMode === SelectionMode.PHP && selectedComponent.control !== null) {
            window.location.href = selectedComponent.control.editorUri
        } else if (selectionMode === SelectionMode.Latte && selectedComponent.template !== null) {
            window.location.href = selectedComponent.template.editorUri
        }
    }

    function handleSelect(event: Event & { detail: InspectorComponent }) {
        selectedComponent = event.detail
    }

    function handleKeyDown(event: KeyboardEvent) {
        if (event.ctrlKey && event.metaKey && event.key === "c") {
            event.preventDefault()
            event.stopImmediatePropagation()
            $mode = InspectorMode.Inspect
        }
    }

    mode.subscribe(() => ($showHelp = false))
</script>

<svelte:window on:keydown={handleKeyDown} />

<div class="orisai-grid">
    <div>
        <ComponentList list={componentList} {selectedComponent} on:select={handleSelect} />
    </div>

    <div>
        <InspectorToolbar />
        {#if $showHelp}
            <p>
                Inspect element on the page by click <b>Inspect</b> button or
                <Key key="⌘" />
                <Key key="ctrl" />
                <Key key="c" />
            </p>
            <p>For quickly open:</p>
            <ul>
                <li><b>PHP class of component</b> - hold <Key key="⌘" /> while inspecting,</li>
                <li><b>Latte template</b> - hold <Key key="⇧" /> while inspecting.</li>
            </ul>
        {:else if selectedComponent !== null}
            <div>
                <ComponentDetail component={selectedComponent} />
            </div>
        {/if}
    </div>
</div>

{#if $mode === InspectorMode.Inspect}
    <InspectMode on:inspect={handleInspect} />
{:else if $mode === InspectorMode.ThreeDimensional}
    <ThreeDimensionalMode />
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
