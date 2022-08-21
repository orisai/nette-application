interface RenderInfo {
	className: string
	file: string
	name: string
	templateFile: string
	templateFileName: string
}

export interface InspectorComponentItem {
	classShortName: string
	depth: number
	name: string
	render: { renderTime: number, tree: RenderInfo[] } | null
}
