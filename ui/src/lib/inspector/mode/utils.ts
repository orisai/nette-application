import type { InspectorComponent } from "../InspectorTypes"

export interface ComponentDescriptor {
    rootElement: HTMLElement
    fullName: string
}

export enum SelectionMode {
    Info = "info",
    PHP = "php",
    Latte = "latte"
}

export function getComponentViewName(component: InspectorComponent): string {
    if (!/^__/.test(component.fullName)) {
        return component.shortName
    }

    if (component.control !== null) {
        return component.control.shortName
    }

    if (component.id !== null) {
        return component.id
    }

    throw new Error("Should not happen")
}

export function getComponentDescriptor(element: HTMLElement): ComponentDescriptor | null {
    let node: Node | null = element
    let commentNode: Node | null = null
    let inUnopenedComponent = false
    let rootElement: HTMLElement | null = null

    // Forms
    if (element instanceof HTMLFormElement || element.closest("form")) {
        const form = element.closest("form") as HTMLFormElement
        return {
            rootElement: form,
            fullName: form.id.split("-").pop() as string
        }
    }

    const startComponentRegExp = new RegExp("{control (.+)}")
    const endComponentRegExp = new RegExp("{/control (.+)}")

    while (node) {
        if (node.nodeType === Node.COMMENT_NODE) {
            if (
                !inUnopenedComponent &&
                node.textContent !== null &&
                endComponentRegExp.test(node.textContent.trim())
            ) {
                inUnopenedComponent = true
                break
            } else {
                if (
                    node.textContent !== null &&
                    startComponentRegExp.test(node.textContent.trim())
                ) {
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
                rootElement = node
            }

            if (node.previousSibling) {
                node = node.previousSibling
            } else {
                node = node.parentNode
            }
        }
    }

    if (commentNode === null || commentNode.textContent === null) {
        return null
    }

    if (!startComponentRegExp.test(commentNode.textContent.trim())) {
        return null
    }

    const matchedComment = commentNode.textContent.trim().match(startComponentRegExp)

    if (rootElement === null || matchedComment === null) {
        return null
    }

    const fullName = matchedComment[1]

    if (
        rootElement === document.documentElement ||
        rootElement === document.body ||
        window.getComputedStyle(rootElement).display === "none"
    ) {
        return null
    }

    return {
        rootElement,
        fullName
    }
}
