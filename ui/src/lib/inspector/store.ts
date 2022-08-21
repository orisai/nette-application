import { writable } from "svelte/store"

export enum InspectorMode {
    Inspect,
    LivePreview,
    ThreeDimensional
}

export const mode = writable<InspectorMode | null>(null)
