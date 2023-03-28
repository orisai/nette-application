<script lang="ts">
	import RenderTime from "../RenderTime.svelte"
	import type { InspectorComponent } from "./InspectorTypes"
	import { getComponentViewName } from "./mode/utils.js"
	import ComponentEditorLinks from "./ComponentEditorLinks.svelte"

	export let component: InspectorComponent

	export const colors = {
		include: "#00000052",
		extends: "#cd1c1c7d",
		import: "#17c35b8f",
		includeblock: "#17c35b8f",
		embed: "#4f1ccd7d",
		sandbox: "black"
	}
</script>

<h2>{getComponentViewName(component)}</h2>

<div class="orisai-grid">
	<ComponentEditorLinks {component} />
	{#if component.template !== null}
		<RenderTime time={component.template.renderTime} />
	{/if}
</div>

{#if component.control !== null}
	{@html component.control.dump}
{/if}

{#if component.latteTemplates !== null}
	<div class="InspectorPanel">
		<table>
			{#each component.latteTemplates as latteTemplate}
				<tr>
					<td>
						{#if latteTemplate.referenceType}
							<span style="margin-left: {latteTemplate.depth * 4}ex" />└ 
							<span
								class="InspectorPanel-type"
								style="background: {colors[latteTemplate.referenceType]}"
							>
								{@html latteTemplate.referenceTypeEscaped}
							</span>
						{/if}

						{@html latteTemplate.editorLink}

						<a href={latteTemplate.phpFileUri} class="InspectorPanel-php">php</a>
					</td>

					<td>{latteTemplate.count > 1 ? latteTemplate.count + "×" : ""}</td>
				</tr>
			{/each}
		</table>

		<div class="tracy-inner">
			{@html component.latteTemplates[0].parametersDump}
		</div>
	</div>
{/if}

<style lang="postcss">
	h2 {
		font-size: 17px;
		margin: 0 0 8px !important;
	}

	.orisai-grid {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.InspectorPanel td {
		white-space: nowrap;
	}

	.InspectorPanel-php {
		background: #8993be;
		color: white;
		border-radius: 79px;
		padding: 1px 4px 3px 4px;
		font-size: 75%;
		font-style: italic;
		font-weight: bold;
		vertical-align: text-top;
		opacity: 0.5;
		margin-left: 2ex;
	}

	.InspectorPanel-type {
		border-radius: 2px;
		padding: 2px 4px;
		font-size: 80%;
		color: white;
		font-weight: bold;
	}
</style>
