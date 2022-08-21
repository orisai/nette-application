export interface InspectorComponentItem {
    control: { dump: string; editorUri: string; fullName: string; shortName: string }
    depth: number
    fullName: string
    template: null | { editorUri: string; fullName: string; renderTime: number; shortName: string }
}
