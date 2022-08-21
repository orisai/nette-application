import { writable } from "svelte/store"

export enum InspectorMode {
    Inspect,
    ThreeDimensional
}

export const mode = writable<InspectorMode | null>(null)
