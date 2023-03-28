<script lang="ts">
	export let time: number
</script>

<div>
	Render time: {time.toFixed(4)} ms
</div>

<style lang="postcss">
	div {
		display: inline-flex;
		align-items: center;
		opacity: 0.5;
	}
</style>
