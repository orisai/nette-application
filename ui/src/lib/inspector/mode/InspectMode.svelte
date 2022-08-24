<script lang="ts">
    import { createEventDispatcher } from "svelte"
    import { mode } from "../store"
    import { getComponentDescriptor } from "./utils"
    import type { ComponentDescriptor } from "./utils"
    import { SelectionMode } from "./utils"
    import Highlighter from "../Highlighter.svelte"
    import type { HighlighterRect } from "../InspectorTypes"

    const dispatch = createEventDispatcher<{
        inspect: { componentDescriptor: ComponentDescriptor | null; selectionMode: SelectionMode }
    }>()

    let componentName: string | null = null
    let selectionMode: SelectionMode = SelectionMode.Info
    let rect: HighlighterRect | null = null
    let invisible = false

    function highlightElement(target: HTMLElement, name: string) {
        const domRect = target.getBoundingClientRect()
        componentName = name
        rect = {
            x: domRect.left,
            y: domRect.top + window.document.documentElement.scrollTop,
            width: domRect.width,
            height: domRect.height
        }
    }

    function handleMouseMove(event: MouseEvent) {
        const componentDescriptor = getComponentDescriptor(event.target as HTMLElement)

        if (componentDescriptor !== null) {
            highlightElement(componentDescriptor.rootElement, componentDescriptor.fullName)
            invisible = false
        } else {
            invisible = true
        }
    }

    function handleClick(event: MouseEvent) {
        event.preventDefault()
        event.stopImmediatePropagation()
        dispatch("inspect", {
            componentDescriptor: getComponentDescriptor(event.target as HTMLElement),
            selectionMode
        })
        $mode = null
    }

    function handleKeyDown(event: KeyboardEvent) {
        if (event.key === "Escape") {
            event.preventDefault()
            event.stopImmediatePropagation()
            $mode = null
            return
        }

        if (event.metaKey || event.ctrlKey) {
            selectionMode = SelectionMode.PHP
        } else if (event.shiftKey) {
            selectionMode = SelectionMode.Latte
        } else {
            selectionMode = SelectionMode.Info
        }
    }

    function handleKeyUp() {
        selectionMode = SelectionMode.Info
    }
</script>

<svelte:window
    on:mousemove={handleMouseMove}
    on:click={handleClick}
    on:keydown={handleKeyDown}
    on:keyup={handleKeyUp}
/>

<Highlighter
    name={componentName || ""}
    {selectionMode}
    {rect}
    invisible={rect === null || invisible}
/>
