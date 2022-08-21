<script lang="ts">
    import { onDestroy, onMount } from "svelte"
    import { mode } from "../store"

    onMount(() => {
        document.documentElement.classList.add("orisai-Inspector-3dMode")
    })

    onDestroy(() => {
        document.documentElement.classList.remove("orisai-Inspector-3dMode")
        document.body.style.transform = ""
    })

    function handleMouseMove(event: MouseEvent) {
        if (event.ctrlKey) {
            const rotateX = event.pageX
            const rotateY = event.pageY
            document.body.style.transform = `rotateX(${rotateY}deg) rotateY(${rotateX}deg)`
        }
    }

    function handleKeyDown(event: KeyboardEvent) {
        if (event.key === "Escape") {
            $mode = null
        }
    }
</script>

<svelte:window on:mousemove={handleMouseMove} on:keydown={handleKeyDown} />
