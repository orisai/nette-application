const switchButton = document.getElementById("switch")
const targetContainer = document.getElementById("target")
let isActive = false
let highlightingElement = null

switchButton.addEventListener("click", () => {
    toggleState()
})

function toggleState() {
    isActive = !isActive
    switchButton.innerText = isActive ? "Vypnout" : "Zapnout"

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
            <h2>${componentInfo.name}</h2>
        `

    if (componentInfo.treeInfo.length > 0) {
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

        componentInfo.treeInfo.forEach(info => {
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

    targetContainer.innerHTML = ""
    targetContainer.appendChild(divComponentName)
    targetContainer.innerHTML = html
}

function handleDocumentMouseMove(event) {
    const componentInfo = getComponentInfo(event.target)

    if (componentInfo /* !== null */) {
        displayInfo(componentInfo)
        highlightElement(componentInfo.componentElement, componentInfo.name)
    }
}

function handleDocumentClick(event) {
    toggleState()
    event.preventDefault()
    event.stopImmediatePropagation()
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
    const treeInfo = splitted[2] ? JSON.parse(splitted[2].slice(0, -1)) : []

    return {
        componentElement: componentElement,
        hoverElement: element,
        name: name,
        treeInfo: treeInfo,
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
