<script lang="ts">
	import EditorLinkList from "../EditorLinkList.svelte";
	import EditorLink from "../EditorLink.svelte";
	import type { InspectorComponentItem } from './InspectorTypes'

	export let component: InspectorComponentItem
</script>

<EditorLinkList>
	<EditorLink uri={component.control.editorUri} type="php"/>
	{#if component.template !== null}
		<EditorLink uri={component.template.editorUri} type="latte"/>
	{/if}
</EditorLinkList>
