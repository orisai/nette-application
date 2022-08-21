<script lang="ts">
	import Toolbar from "../Toolbar.svelte";
	import ToolbarButton from "../ToolbarButton.svelte";
	import { InspectorMode, mode } from './store'

	function handleKeyDown (event: KeyboardEvent) {
		// @todo vymyslet nejakou klavesovou zkratku pro inspect
		if ((event.ctrlKey || event.metaKey) && event.shiftKey && event.key === "C") {
			// event.preventDefault()
			$mode = InspectorMode.Inspect
		}
	}
</script>

<Toolbar>
	<ToolbarButton on:click={() => $mode = InspectorMode.Inspect} active={$mode === InspectorMode.Inspect}>
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M3.33333 3.33333V12.6667H5.85185V14H2.66667C2.29848 14 2 13.7015 2 13.3333V2.66667C2 2.29848 2.29848 2 2.66667 2H13.3333C13.7015 2 14 2.29848 14 2.66667V5.85185H12.6667V3.33333H3.33333Z"
				  fill="currentColor"/>
			<path d="M7.4075 7.61148L9.18437 14.2747C9.22274 14.4187 9.41622 14.4438 9.49011 14.3145L10.9016 11.8444L13.5286 14.4714C13.7889 14.7317 14.211 14.7317 14.4714 14.4714C14.7317 14.211 14.7317 13.7889 14.4714 13.5286L11.8444 10.9016L14.3145 9.49011C14.4438 9.41622 14.4187 9.22274 14.2747 9.18437L7.61148 7.4075C7.48768 7.37448 7.37448 7.48768 7.4075 7.61148Z"
				  fill="currentColor"/>
		</svg>
		Inspect
	</ToolbarButton>

<!--	<ToolbarButton on:click={() => $mode = InspectorMode.LivePreview} active={$mode === InspectorMode.LivePreview}>-->
<!--		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">-->
<!--			<path fill-rule="evenodd" clip-rule="evenodd"-->
<!--				  d="M7.99998 13.3333C10.9455 13.3333 13.3333 10.9455 13.3333 7.99998C13.3333 5.05446 10.9455 2.66665 7.99998 2.66665C5.05446 2.66665 2.66665 5.05446 2.66665 7.99998C2.66665 10.9455 5.05446 13.3333 7.99998 13.3333ZM7.99998 14.6666C11.6819 14.6666 14.6666 11.6819 14.6666 7.99998C14.6666 4.31808 11.6819 1.33331 7.99998 1.33331C4.31808 1.33331 1.33331 4.31808 1.33331 7.99998C1.33331 11.6819 4.31808 14.6666 7.99998 14.6666Z"-->
<!--				  fill="currentColor"/>-->
<!--			<path fill-rule="evenodd" clip-rule="evenodd"-->
<!--				  d="M6.46594 6.15418C6.46594 5.63257 7.0366 5.32144 7.46263 5.61078L10.1805 7.4566C10.5611 7.71511 10.5611 8.28489 10.1805 8.5434L7.46263 10.3892C7.0366 10.6786 6.46594 10.3674 6.46594 9.84582V6.15418Z"-->
<!--				  fill="currentColor"/>-->
<!--		</svg>-->
<!--		Live Preview-->
<!--	</ToolbarButton>-->

<!--	<ToolbarButton on:click={() => $mode = InspectorMode.ThreeDimensional} active={$mode === InspectorMode.ThreeDimensional}>-->
<!--		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">-->
<!--			<path d="M6.07202 13.4871V2.51294C6.07202 2.3503 6.22487 2.23096 6.38266 2.27041L13.6257 4.08116C13.737 4.10898 13.815 4.20898 13.815 4.3237V11.6763C13.815 11.791 13.737 11.891 13.6257 11.9189L6.38266 13.7296C6.22487 13.7691 6.07202 13.6497 6.07202 13.4871Z"-->
<!--				  stroke="currentColor" stroke-width="1.3"/>-->
<!--			<path d="M4.12079 3.60852V12.3916" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>-->
<!--			<path d="M2.185 4.48676V11.5132" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>-->
<!--		</svg>-->
<!--		3D Mode-->
<!--	</ToolbarButton>-->
</Toolbar>

<svelte:window on:keydown={handleKeyDown} />

<style lang="sass">
	svg
		margin-right: 4px
</style>
