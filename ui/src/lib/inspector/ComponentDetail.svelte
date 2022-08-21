<script lang="ts">
	import RenderTime from "../RenderTime.svelte";

	export let component

	let isError = false

	// @todo
	if (!component || !component.tree || !component.renderTime) {
		isError = true
	}
</script>

{#if isError}
	Chyba @todo
{:else}
	<h2>{component.name}</h2>
	{#if component.tree.length > 0}
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>Class</th>
					<th>Template</th>
				</tr>
			</thead>
			{#each component.tree as info}
				<tr>
					<td>{info.name}</td>
					<td>
						<a href="{info.file}">
							{info.className}
						</a>
					</td>
					<td>
						<a href="{info.templateFile}">
							{info.templateFileName}
						</a>
					</td>
				</tr>
			{/each}
		</table>
	{/if}

	{#if component.renderTime}
		<div>
			<RenderTime timeInSeconds={component.renderTime}/>
		</div>
	{/if}
{/if}

<style lang="sass">
	h2
		font-size: 17px

	table
		margin-bottom: 8px
</style>
