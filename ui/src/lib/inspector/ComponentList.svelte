<script lang="ts">
	import type { InspectorComponentItem } from './InspectorTypes'
	import { createEventDispatcher} from 'svelte'

	export let list: InspectorComponentItem[]
	export let selectedComponent: InspectorComponentItem | null

	const dispatch = createEventDispatcher<{select: InspectorComponentItem}>()

	let query: string = ""

	function showFullName (name): boolean
	{
		return !/^__/.test(name)
	}
</script>

<input bind:value={query} placeholder="Filter&hellip;" type="search">

<ul>
	{#each list as item}
		<li
			on:click={() => dispatch("select", item)}
			class:orisai-muted={
				!item.fullName.toLowerCase().includes(query.toLowerCase())
				&& !item.control.shortName.toLowerCase().includes(query.toLowerCase())
			}
			class:orisai-active={selectedComponent === item}
		>
			{#if item.depth > 0}
				<span style="margin-left: {item.depth * 2}ex"></span>└ 
			{/if}

			<b>
				{item.control.shortName}
			</b>

			{#if showFullName(item.fullName)}
				&nbsp;
				({item.fullName})
			{/if}

			<div class="orisai-tag-list">
				<a href="{item.control.editorUri}" class="orisai-tag orisai-tag--php">
					php
				</a>
				{#if item.template !== null}
					<a href="{item.template.editorUri}" class="orisai-tag orisai-tag--latte">
						latte
					</a>
				{/if}
			</div>
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
			.orisai-tag-list
				visibility: visible

		&:not(:first-child)
			border-top: 1px solid var(--orisai-color-border)

	li:hover,
	.orisai-active
		background-color: #f5f4f2
		color: var(--orisai-color-active)

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

	.orisai-tag-list
		display: flex
		align-items: center
		margin-left: auto
		gap: 4px
		visibility: hidden
		padding-left: 16px

	.orisai-tag
		display: flex
		align-items: center
		border-radius: 48px
		padding: 0 6px
		font-size: 8px
		font-style: italic
		font-weight: bold
		height: 16px

		&--php
			background-color: #b0b3d6
			color: #fff !important

			&:hover,
			&:focus-visible
				background-color: #787cb5 !important

		&--latte
			background-color: #ffbe6c
			color: #fff !important

			&:hover,
			&:focus-visible
				background-color: #F1A443 !important
</style>
