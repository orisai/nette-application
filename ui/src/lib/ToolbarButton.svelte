<script lang="ts">
	export let active = false
</script>

<button on:click|stopPropagation class:orisai-active={active}>
	<slot />
</button>

<style lang="sass">
	button
		cursor: pointer
		display: flex
		align-items: center
		justify-content: center
		padding: 0 12px
		height: 32px
		border: 1px solid var(--orisai-color-border)
		transition: background-color 0.16s ease
		white-space: nowrap

		&:not(.orisai-active):hover
			background-color: #f5f4f2

		&:focus
			outline: none

		&:focus-visible
			border-color: var(--orisai-color-border-active)
			z-index: 1

		:global(svg)
			margin-right: 6px
			flex: 0 0 auto

	.orisai-active
		border-color: var(--orisai-color-border-active)
		color: var(--orisai-color-active)
		position: relative
		z-index: 1
</style>
