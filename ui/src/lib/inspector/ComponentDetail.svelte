<script lang="ts">
	import RenderTime from "../RenderTime.svelte";
	import type { InspectorComponentItem } from './InspectorTypes'

	export let component: InspectorComponentItem
</script>

<h2>{component.name}</h2>
{#if component.render !== null}
	{#if component.render.tree.length > 0}
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>Class</th>
					<th>Template</th>
				</tr>
			</thead>
			{#each component.render.tree as info}
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

	{#if component.render.renderTime}
		<div>
			<RenderTime timeInSeconds={component.render.renderTime}/>
		</div>
	{/if}
{/if}

<style lang="sass">
	h2
		font-size: 17px

	table
		margin-bottom: 8px
</style>
