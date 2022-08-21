interface ComponentInfo {
	componentElement: HTMLElement,
	hoverElement: HTMLElement,
	name: string,
	tree: [],
	renderTime: number,
}

export function getComponentInfo(element: HTMLElement): ComponentInfo|null
{
	let node: Node | null = element
	let commentNode: Node | null = null
	let inUnopenedComponent = false
	let componentElement: HTMLElement | null = null

	const startControlRegExp = new RegExp("\{control (.+) ")
	const endControlRegExp = new RegExp("\{\/control\}")

	while (node) {
		if (node.nodeType === Node.COMMENT_NODE) {
			// @ts-ignore
			if (!inUnopenedComponent && endControlRegExp.test(node.textContent.trim())) {
				inUnopenedComponent = true
				break
			} else { // @ts-ignore
				if (startControlRegExp.test(node.textContent.trim())) {
					if (inUnopenedComponent) {
						inUnopenedComponent = false
						break
					}
				}
			}

			commentNode = node
			node = null
		} else {
			if (node instanceof HTMLElement) {
				componentElement = node
			}

			if (node.previousSibling) {
				node = node.previousSibling
			} else {
				node = node.parentNode
			}
		}
	}

	if (commentNode === null) {
		return null
	}

	// @ts-ignore
	if (!startControlRegExp.test(commentNode.textContent.trim())) {
		return null
	}

	// @ts-ignore
	const splitted = commentNode.textContent.trim().split(" ")
	const name = splitted[1]
	const data = JSON.parse(splitted[2].slice(0, -1))
	const tree = data.tree || []

	if (componentElement === null) {
		return null
	}

	return {
		componentElement: componentElement,
		hoverElement: element,
		name: name,
		tree: tree,
		renderTime: data.renderTime,
	}
}
