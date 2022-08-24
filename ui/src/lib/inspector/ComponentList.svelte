<script lang="ts">
    import type { InspectorComponent } from "./InspectorTypes"
    import { createEventDispatcher } from "svelte"
    import { getComponentViewName } from "./mode/utils.js"
    import ComponentEditorLinks from "./ComponentEditorLinks.svelte"

    export let list: InspectorComponent[]
    export let selectedComponent: InspectorComponent | null

    const dispatch = createEventDispatcher<{
        select: InspectorComponent
    }>()

    let query: string = ""
</script>

<input bind:value={query} placeholder="Filter&hellip;" type="search" />

<ul>
    {#each list as component}
        {#if component.control !== null && component.showInTree}
            <li
                on:click={() => dispatch("select", component)}
                class:orisai-muted={!component.fullName
                    .toLowerCase()
                    .includes(query.toLowerCase()) &&
                    !component.control.shortName.toLowerCase().includes(query.toLowerCase())}
                class:orisai-active={selectedComponent === component}
            >
                {#if component.depth > 0}
                    <span style="margin-left: {component.depth * 2}ex" />└
                {/if}

                {getComponentViewName(component)}

                <div class="orisai-editor-links">
                    <ComponentEditorLinks {component} />
                </div>
            </li>
        {/if}
    {/each}
</ul>

<style lang="sass">
	ul
		border: 1px solid var(--orisai-color-border)
		border-radius: 0 0 var(--orisai-radius) var(--orisai-radius)
		overflow: hidden
		margin: 0
		padding: 0

	li
		padding: 4px 4px 4px 8px
		margin: 0
		display: flex
		align-items: center
		color: var(--orisai-color-link)
		cursor: pointer
		transition: background-color 0.16s ease, opacity 0.2s ease

		&:hover
			.orisai-editor-links
				visibility: visible

		&:not(:first-child)
			border-top: 1px solid var(--orisai-color-border)

	li:hover,
	.orisai-active
		background-color: #f5f4f2
		color: var(--orisai-color-active)

	.orisai-active,
	.orisai-active:hover
		color: black

	input
		display: block
		width: 100%
		border: 1px solid var(--orisai-color-border)
		border-radius: var(--orisai-radius) var(--orisai-radius) 0 0
		padding: 0 8px
		line-height: 32px
		height: 32px

		&:focus
			border-color: var(--orisai-color-border-active)
			outline: none

	.orisai-muted
		opacity: 0.32

	.orisai-editor-links
		margin-left: auto
		visibility: hidden
		padding-left: 16px
</style>
