export interface InspectorComponent {
	showInTree: boolean
	fullName: string
	shortName: string
	depth: number
	id: null | string
	parentId: null | string
	control: null | { dump: string; editorUri: string; fullName: string; shortName: string }
	template: null | { editorUri: string; fullName: string; renderTime: number; shortName: string }
}

export interface HighlighterRect {
	x: number
	y: number
	height: number
	width: number
}
