<script src="./js/three/threex.domevents.js"></script>
<script src="./js/three/threex.linkify.js"></script>
<script type="importmap">
	{
		"imports": {
		"three": "/node_modules_min/three/build/three.module.js",
		"three/addons/": "/node_modules_min/three/examples/jsm/"
		}
	}
</script>
<script>	
	const pcs = {{pcsJson}}; 
	const timeline = {{timelineJson}}; 
	const eleves = {{elevesJson}}; 
</script>
<script defer type="module" src="./js/three.js"></script>

