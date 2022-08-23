<script lang="ts">
    export let uri: string
    export let type: "latte" | "php"
</script>

<a href={uri} class:orisai-latte={type === "latte"} class:orisai-php={type === "php"}>
    {type}
</a>

<style lang="sass">
	a
		display: flex
		align-items: center
		border-radius: 48px
		padding: 0 6px
		font-size: 10px
		font-style: italic
		font-weight: bold
		height: 16px

	.orisai-php
		background-color: #b0b3d6
		color: #fff !important

		&:hover,
		&:focus-visible
			background-color: #787cb5 !important

	.orisai-latte
		background-color: #ffbe6c
		color: #fff !important

		&:hover,
		&:focus-visible
			background-color: #F1A443 !important
</style>
