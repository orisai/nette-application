import "./orisai.pcss"
import Inspector from "./Inspector.svelte"

const inspectorTarget = <HTMLElement>document.getElementById("orisai-inspector")

!inspectorTarget.dataset.initialized &&
	new Inspector({
		target: inspectorTarget,
		props: JSON.parse(<string>inspectorTarget.dataset.props)
	})

inspectorTarget.dataset.initialized = "1"
