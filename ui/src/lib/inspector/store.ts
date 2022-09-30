import { writable } from "svelte/store"

export enum InspectorMode {
	Inspect
}

export const mode = writable<InspectorMode | null>(null)

export const showHelp = writable(false)
