<script lang="ts">
	export let time: number
	export let showText = true
</script>

<div>
	{#if showText}
		Render time:
	{/if}
	{time.toFixed(4)} ms
</div>

<style lang="sass">
	div
		display: inline-flex
		align-items: center
		opacity: 0.5

	svg
		margin-right: 4px
		width: 16px
</style>
