<script lang="ts">
	import { createEventDispatcher, onMount } from 'svelte'
	import { mode } from '../store'

	const dispatch = createEventDispatcher()

	enum HighlightingMode {
		Info = "info",
		PHP = "php",
		Latte = "latte"
	}

	let highlightingElement: HTMLDivElement
	let highlightingMode: HighlightingMode = HighlightingMode.Info

	onMount(() => {
		highlightingElement = document.createElement("div")
		highlightingElement.classList.add("orisai-HighlightingElement")
		document.body.appendChild(highlightingElement)

		return () => {
			highlightingElement.remove()
		}
	})

	function highlightElement(element: HTMLElement, name: string)
	{
		const domRect = element.getBoundingClientRect()

		const nameElement = document.createElement("div")
		nameElement.classList.add("orisai-HighlightingElement-name")
		nameElement.textContent = name + " - " + (highlightingMode)

		highlightingElement.innerHTML = ""
		highlightingElement.appendChild(nameElement)

		highlightingElement.style.transform = `translate3d(${domRect.left}px, ${domRect.top + window.document.documentElement.scrollTop}px, 0)`
		highlightingElement.style.left = "0px"
		highlightingElement.style.top = "0px"
		highlightingElement.style.width = domRect.width + "px"
		highlightingElement.style.height = domRect.height + "px"
		highlightingElement.classList.remove("orisai-HighlightingElement--invisible")

		highlightingElement.addEventListener("transitionend", () => {
			highlightingElement.classList.add("orisai-HighlightingElement--active")
		}, {
			once: true
		})
	}

	function handleMouseMove (event: MouseEvent) {
		const componentInfo = getComponentInfo(event.target as HTMLElement)

		if (componentInfo !== null) {
			highlightElement(componentInfo.componentElement, componentInfo.name)
		} else if (highlightingElement) {
			highlightingElement.classList.add("orisai-HighlightingElement--invisible")
		}
	}

	function handleClick(event: MouseEvent) {
		event.preventDefault()
		event.stopImmediatePropagation()
		dispatch("inspect", getComponentInfo(event.target as HTMLElement))
		$mode = null
	}

	function handleKeyDown(event: KeyboardEvent) {
		if (event.key === "Escape") {
			event.preventDefault()
			event.stopImmediatePropagation()
			$mode = null
		}

		if (event.metaKey || event.ctrlKey) {
			highlightingMode = HighlightingMode.PHP
		} else if (event.shiftKey) {
			highlightingMode = HighlightingMode.Latte
		} else {
			highlightingMode = HighlightingMode.Info
		}

		Object.values(HighlightingMode).forEach((value) => {
			if (value !== highlightingMode) {
				highlightingElement.classList.remove("orisai-HighlightingElement--mode-" + value)
			} else {
				highlightingElement.classList.add("orisai-HighlightingElement--mode-" + highlightingMode)
			}
		})
	}

	function handleKeyUp () {
		highlightingMode = HighlightingMode.Info

		Object.values(HighlightingMode).forEach((value) => {
			if (value !== highlightingMode) {
				highlightingElement.classList.remove("orisai-HighlightingElement--mode-" + value)
			} else {
				highlightingElement.classList.add("orisai-HighlightingElement--mode-" + highlightingMode)
			}
		})
	}

	interface ComponentInfo {
		componentElement: HTMLElement,
		hoverElement: HTMLElement,
		name: string,
		tree: [],
		renderTime: number,
	}

	function getComponentInfo(element: HTMLElement): ComponentInfo|null
	{
		let node: Node = element
		let commentNode: Node = null
		let inUnopenedComponent = false
		let componentElement: HTMLElement = null

		const startControlRegExp = new RegExp("\{control (.+) ")
		const endControlRegExp = new RegExp("\{\/control\}")

		while (node) {
			if (node.nodeType === Node.COMMENT_NODE) {

				if (!inUnopenedComponent && endControlRegExp.test(node.textContent.trim())) {
					inUnopenedComponent = true
					return
				} else if (startControlRegExp.test(node.textContent.trim())) {
					if (inUnopenedComponent) {
						inUnopenedComponent = false
						return
					}
				}

				commentNode = node;
				node = null;
			} else {
				if (node instanceof HTMLElement) {
					componentElement = node
				}

				if (node.previousSibling !== null) {
					node = node.previousSibling;
				} else {
					node = node.parentNode;
				}
			}
		}

		if (commentNode === null || !startControlRegExp.test(commentNode.textContent.trim())) {
			return null;
		}

		const splitted = commentNode.textContent.trim().split(" ")
		const name = splitted[1]
		const data = JSON.parse(splitted[2].slice(0, -1))
		const tree = data.tree || []

		return {
			componentElement: componentElement,
			hoverElement: element,
			name: name,
			tree: tree,
			renderTime: data.renderTime,
		}
	}
</script>

<svelte:window
	on:mousemove={handleMouseMove}
	on:click={handleClick}
	on:keydown={handleKeyDown}
	on:keyup={handleKeyUp}
/>
