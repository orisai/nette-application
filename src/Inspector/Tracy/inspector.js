const switchButton = document.getElementById("switch-button")
const livePreviewButton = document.getElementById("live-preview-button")
const threeDimensionalModeButton = document.getElementById("three-dimensional-mode-button")

const targetContainer = document.getElementById("target")
let isActive = false
let highlightingElement = null
let isLivePreview = false
let is3DMode = false

switchButton.addEventListener("click", () => {
    toggleState()
})

livePreviewButton.addEventListener("click", () => {
	isLivePreview = !isLivePreview
	livePreviewButton.classList.toggle("is-on", isLivePreview)
})

threeDimensionalModeButton.addEventListener("click", () => {
	is3DMode = !is3DMode
	document.documentElement.classList.toggle("tracy-InspectorPanel-3DMode", is3DMode)
	threeDimensionalModeButton.classList.toggle("is-active", is3DMode)

	if (!is3DMode) {
		document.body.style.transform = "";
	}

	document.documentElement.addEventListener("mousemove", (event) => {
		if (event.ctrlKey) {
			let rotate_X = event.pageX;
			let rotate_Y = event.pageY;
			document.body.style.transform = `rotateX(${rotate_Y}deg) rotateY(${rotate_X}deg)`
		}
	})
})

function toggleState() {
    isActive = !isActive
	switchButton.classList.toggle("is-active", isActive)

    if (isActive) {
        addEvents()
    } else {
        removeEvents()
    }
}

toggleState()

function displayInfo(componentInfo) {

    // @todo nekde je chyba
    if (!componentInfo || !componentInfo.name) {
        return
    }

    const divComponentName = document.createElement("div")
    divComponentName.textContent = componentInfo.name

    let html = `
            <h2 class="tracy-Inspector-componentName">${componentInfo.name}</h2>
        `

    if (componentInfo.tree.length > 0) {
        html += `
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Template</th>
                        </tr>
                    </thead>
            `

        componentInfo.tree.forEach(info => {
            html += `
                    <tr>
                        <td>${info.name}</td>
                        <td>
                            <a href="${info.file}">
                                ${info.className}
                            </a>
                        </td>
                        <td>
                            <a href="${info.templateFile}">
                                ${info.templateFileName}
                            </a>
                        </td>
                    </tr>
                `
        })

        html += '</table>';
    }

    if (componentInfo.renderTime) {
    	html += `
			<p class="tracy-Inspector-renderTime">Render time: ${componentInfo.renderTime} seconds</p>
    	`
	}

    targetContainer.innerHTML = ""
    targetContainer.appendChild(divComponentName)
    targetContainer.innerHTML = html
}

function handleDocumentMouseMove (event) {
	const componentInfo = getComponentInfo(event.target)

	if (componentInfo /* !== null */) {
		if (isLivePreview) {
			displayInfo(componentInfo)
		}
		highlightElement(componentInfo.componentElement, componentInfo.name)
	}
}

function handleDocumentClick(event) {
    toggleState()
    event.preventDefault()
    event.stopImmediatePropagation()

	const componentInfo = getComponentInfo(event.target)
	if (componentInfo) {
		displayInfo(componentInfo)
	}
}

function handleDocumentKeydown(event) {
    if (event.key === "Escape") {
        toggleState()
        event.preventDefault()
        event.stopImmediatePropagation()
    }
}

function addEvents() {
    document.addEventListener("mousemove", handleDocumentMouseMove)

    setTimeout(() => {
        document.addEventListener("click", handleDocumentClick, {
            once: true,
        })

        document.addEventListener("keydown", handleDocumentKeydown)
    }, 0)
}

function removeEvents() {
    document.removeEventListener("mousemove", handleDocumentMouseMove)
    document.removeEventListener("click", handleDocumentClick)
    document.removeEventListener("keydown", handleDocumentKeydown)
}

/**
 *
 * @param {HTMLElement} element
 */
function getComponentInfo(element)
{
    let node = element
    let commentNode = null
    let inUnopenedComponent = false
    let componentElement = null

    // PhpStorm ukazuje chybu
    const startControlRegExp = new RegExp("\{control")
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
            if (node.nodeType === Node.ELEMENT_NODE) {
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

function getHighlightingElement() {
    if (highlightingElement === null) {
        highlightingElement = document.createElement("div")
        highlightingElement.classList.add("pl-HighlightingElement")
        document.body.appendChild(highlightingElement)
    }

    return highlightingElement
}

/**
 * @param {HTMLElement} element
 * @param {string} name
 */
function highlightElement(element, name) {

    /** @var {DOMRect} domRect */
    const domRect = element.getBoundingClientRect()

    const highlightingElement = getHighlightingElement()

    const nameElement = document.createElement("div")
    nameElement.classList.add("pl-HighlightingElement-name")
    nameElement.textContent = name

    highlightingElement.innerHTML = ""
    highlightingElement.appendChild(nameElement)

    highlightingElement.style.transform = `translate3d(${domRect.left}px, ${domRect.top + window.document.documentElement.scrollTop}px, 0)`

    highlightingElement.style.left = "0px"
    highlightingElement.style.top = "0px"

    // highlightingElement.style.left = domRect.left + "px"
    // highlightingElement.style.top = (domRect.top + window.document.documentElement.scrollTop) + "px"
    highlightingElement.style.width = domRect.width + "px"
    highlightingElement.style.height = domRect.height + "px"
}


/*********************************************************** */

document.getElementById("filter-query").addEventListener("input", (event) => {
	const styleFiltering = document.getElementById("style-filtering")
	if (event.target.value) {
		// @todo css selektor
		styleFiltering.innerHTML = `
			#tree td:not([data-name*='${event.target.value}']) {
				opacity: 0.5 !important;
			}
		`
	} else {
		styleFiltering.innerHTML = ""
	}
})


// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.
function debounce(func, wait, immediate) {
	let timeout;
	return function() {
		let context = this, args = arguments;
		let later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		let callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};
