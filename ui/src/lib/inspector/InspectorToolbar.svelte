<script lang="ts">
	import Toolbar from "../Toolbar.svelte"
	import ToolbarButton from "../ToolbarButton.svelte"
	import { InspectorMode, mode, showHelp } from "./store"
</script>

<Toolbar>
	<ToolbarButton
		on:click={() => ($mode = $mode === InspectorMode.Inspect ? null : InspectorMode.Inspect)}
		active={$mode === InspectorMode.Inspect}
	>
		<svg
			width="16"
			height="16"
			viewBox="0 0 16 16"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
		>
			<path
				d="M3.33333 3.33333V12.6667H5.85185V14H2.66667C2.29848 14 2 13.7015 2 13.3333V2.66667C2 2.29848 2.29848 2 2.66667 2H13.3333C13.7015 2 14 2.29848 14 2.66667V5.85185H12.6667V3.33333H3.33333Z"
				fill="currentColor"
			/>
			<path
				d="M7.4075 7.61148L9.18437 14.2747C9.22274 14.4187 9.41622 14.4438 9.49011 14.3145L10.9016 11.8444L13.5286 14.4714C13.7889 14.7317 14.211 14.7317 14.4714 14.4714C14.7317 14.211 14.7317 13.7889 14.4714 13.5286L11.8444 10.9016L14.3145 9.49011C14.4438 9.41622 14.4187 9.22274 14.2747 9.18437L7.61148 7.4075C7.48768 7.37448 7.37448 7.48768 7.4075 7.61148Z"
				fill="currentColor"
			/>
		</svg>
		Inspect
	</ToolbarButton>

	<ToolbarButton on:click={() => ($showHelp = !$showHelp)} active={$showHelp}>Help</ToolbarButton>
</Toolbar>
