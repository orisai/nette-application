<script lang="ts">
	import type { InspectorComponentItem } from './InspectorTypes'
	import { createEventDispatcher} from 'svelte'

	export let list: InspectorComponentItem[]

	const dispatch = createEventDispatcher<{select: any}>()

	let query: string = ""
</script>

<input bind:value={query} placeholder="Filter&hellip;" type="search">

<ul>
	{#each list as item}
		<li on:click={() => dispatch("select", item)} class:orisai-muted={!item.name.toLowerCase().includes(query.toLowerCase())}>
			{#if !item.isRenderable}
				<span>
					{item.classShortName}
				</span>
			{/if}
			{#if item.depth > 0}
				<span style="margin-left: {item.depth * 2}ex"></span>└ 
			{/if}
			{item.name}
			<a href="{item.editorLink}" class="orisai-php">
				php
			</a>
		</li>
	{/each}
</ul>

<style lang="sass">
	ul
		border: 1px solid var(--orisai-color-border)
		border-radius: 0 0 var(--orisai-radius) var(--orisai-radius)
		overflow: hidden

	li
		padding: 4px 4px 4px 8px
		margin: 0
		display: flex
		align-items: center
		color: var(--orisai-color-link)
		cursor: pointer
		transition: background-color 0.16s ease, opacity 0.2s ease

		&:hover
			background-color: #f5f4f2
			color: var(--orisai-color-active)

			.orisai-php
				visibility: visible

		&:not(:first-child)
			border-top: 1px solid var(--orisai-color-border)

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
		opacity: 0.5

	.orisai-php
		display: inline-flex
		align-items: center
		background-color: #b0b3d6
		color: #fff !important
		border-radius: 48px
		padding: 0 6px
		font-size: 8px
		font-style: italic
		font-weight: bold
		margin-left: auto
		visibility: hidden
		height: 16px

		&:hover,
		&:focus-visible
			background-color: #787cb5 !important
</style>
