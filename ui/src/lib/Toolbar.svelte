<div>
	<slot />
</div>

<style lang="postcss">
	div {
		display: inline-flex;

		& :global(> *:first-child) {
			border-top-left-radius: var(--orisai-radius);
			border-bottom-left-radius: var(--orisai-radius);
		}

		& :global(> *:last-child) {
			border-top-right-radius: var(--orisai-radius);
			border-bottom-right-radius: var(--orisai-radius);
		}

		& :global(> *:not(:first-child)) {
			margin-left: -1px;
		}
	}
</style>
