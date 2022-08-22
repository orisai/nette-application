export interface InspectorComponent {
    control: { dump: string; editorUri: string; fullName: string; shortName: string }
    depth: number
    fullName: string
    shortName: string
    template: null | { editorUri: string; fullName: string; renderTime: number; shortName: string }
}

export interface HighlighterRect {
    x: number
    y: number
    height: number
    width: number
}
