<script lang="ts">
	export let timeInSeconds: number
	export let showText = true

	const timeInMs = (timeInSeconds * 1000).toFixed(4)
	const slowRendered = timeInMs > 0.2
</script>

<div class:orisai-warning={slowRendered}>
	{#if slowRendered}
		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M10.212 4.57725C10.9491 3.10316 13.0527 3.10315 13.7897 4.57725L20.5537 18.1051C21.2186 19.4349 20.2516 20.9995 18.7648 20.9995H5.23696C3.75019 20.9995 2.7832 19.4349 3.4481 18.1051L10.212 4.57725ZM13.5009 17.5074C13.5009 18.3358 12.8293 19.0074 12.0009 19.0074C11.1725 19.0074 10.5009 18.3358 10.5009 17.5074C10.5009 16.6789 11.1725 16.0074 12.0009 16.0074C12.8293 16.0074 13.5009 16.6789 13.5009 17.5074ZM11.6138 7.99954C11.0194 7.99954 10.5562 8.51505 10.6195 9.10613L11.1709 14.2499C11.2164 14.674 11.5743 14.9956 12.0009 14.9956C12.4274 14.9956 12.7854 14.674 12.8308 14.2499L13.3823 9.10613C13.4456 8.51505 12.9824 7.99954 12.388 7.99954H11.6138Z" fill="currentColor"/>
		</svg>
	{/if}
	{#if showText}
		Render time:
	{/if}
	{timeInMs} ms
</div>

<style lang="sass">
	div
		display: inline-flex
		align-items: center
		opacity: 0.5

	svg
		margin-right: 4px
		width: 16px

	.orisai-warning
		color: var(--orisai-color-warning)
		opacity: 1
</style>
