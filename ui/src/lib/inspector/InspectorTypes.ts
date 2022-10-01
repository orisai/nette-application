export interface InspectorComponent {
	showInTree: boolean
	fullName: string
	shortName: string
	depth: number
	id: null | string
	parentId: null | string
	control: null | Control
	template: null | Template
	latteTemplates: null | Array<LatteTemplate>
}

export interface Control {
	dump: string
	editorUri: string
	fullName: string
	shortName: string
}

export interface Template {
	editorUri: string
	fullName: string
	renderTime: number
	shortName: string
}

export interface LatteTemplate {
	referenceType: null | string
	referenceTypeEscaped: null | string
	editorLink: string
	phpFileUri: string
	parametersDump: string
	depth: number
	count: number
}

export interface HighlighterRect {
	x: number
	y: number
	height: number
	width: number
}
