import './orisai.sass'
import Inspector from './Inspector.svelte'

const inspectorTarget = document.getElementById('orisai-inspector')

new Inspector({
	target: inspectorTarget,
	props: JSON.parse(inspectorTarget.dataset.props)
})
