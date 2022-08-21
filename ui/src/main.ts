import './orisai.sass'
import Inspector from './Inspector.svelte'

const inspectorTarget = <HTMLElement> document.getElementById('orisai-inspector')

new Inspector({
	target: inspectorTarget,
	props: JSON.parse(<string> inspectorTarget.dataset.props)
})
