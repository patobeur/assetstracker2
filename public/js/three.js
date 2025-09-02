import * as THREE from "three";
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import * as SkeletonUtils from 'three/addons/utils/SkeletonUtils.js';
import { FontLoader } from 'three/addons/loaders/FontLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { TextGeometry } from 'three/addons/geometries/TextGeometry.js';
import Stats from 'three/addons/libs/stats.module.js';
import { WiresGroupe } from './three/wiresGroupe.js';
import { ObjMeshLoader } from './three/GltfLoader.js';
import { _front } from './front.js';
// import { _client } from './client.js'

let scene,camera,renderer,clock,raycaster;
let Font;
let controls;
let domEvents;
let INTERSECTED;
let theta = 0;
let models = undefined;

const pointer = new THREE.Vector2();
const mouse = {x:0,y:0};
const radius = 5;
let stats
let divInfo,divInfoTarget,divInfoClose

document.addEventListener("DOMContentLoaded", () => {
    if(THREE) {
        start()
    };
})
function start() {
    const loader = new FontLoader();
    loader.load('./node_modules_min/three/examples/fonts/helvetiker_regular.typeface.json', (font) => {
        Font = font;
        ObjMeshLoader.init(models,(loadedModels)=>{
            models=loadedModels
            go()
        })
    })
}

function go() {
    clock = new THREE.Clock();
    stats = new Stats();
    stats.dom.style.top = '50px';
    document.body.appendChild( stats.dom );
    createScene();
    WiresGroupe.init(scene)
    addAmbiance();
    addElements();
    addOrbitControls();
    addScene();
    //-------
    setDomEvents();
    addPcs({
        pcs: pcs, // le tableau
        center: {x: 0, y: 0, z:-5}, // position centrale de la grille
        gap: 0.2,                   // espace entre les objets
        layout: "tall"              // "flat" pour xz, "tall" pour xy
    });
    addTimeline();
    addEleves();
    //-------
    // animate();
    document.addEventListener( 'click', onClick );
    document.addEventListener( 'mousemove', onPointerMove );
    window.addEventListener('resize', onWindowResize);
    function onWindowResize() {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    }
    addCssToDom()
    createDivInfo()

}
function animate() {
    render();
    stats.update();
}
function render() {    
    const delta = clock.getDelta();

    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    
    controls.update();
    // findIntersection()
    renderer.render(scene, camera);
}
//------------------------------------------
function createScene(){
    // Créer une nouvelle scène 3D
    scene = new THREE.Scene();

    // Créer une caméra perspective
    camera = new THREE.PerspectiveCamera(40, window.innerWidth / window.innerHeight, 1, 1000);
    camera.position.set(0,15,20);

    // Créer un rendu WebGL
    renderer = new THREE.WebGLRenderer({antialias: true});

    // Configuration du rendu
    renderer.setPixelRatio( window.devicePixelRatio );
    renderer.setSize( window.innerWidth, window.innerHeight );
    renderer.shadowMap.enabled = true;
    renderer.outputEncoding = THREE.sRGBEncoding;
    renderer.shadowMap.enabled = true;

    raycaster = new THREE.Raycaster();
    // Configuration de setAnimationLoop
	renderer.setAnimationLoop( animate );
}
function addElements(){
    
    const grid = new THREE.GridHelper( 111, 111, 0xFFFFFF, 0xFFFFFF );
    grid.material.opacity = 0.7;
    grid.material.transparent = true;
    scene.add( grid );


    // Créer le sol
    const groundGeometry = new THREE.BoxGeometry(1000, 0.5, 1000);
    const groundMaterial = new THREE.MeshPhongMaterial({
        color: 0xfafafa
    });
    const groundMesh = new THREE.Mesh(groundGeometry, groundMaterial);
    groundMesh.receiveShadow = true;
    // groundMesh.castShadow = true;
    groundMesh.position.y = -0.25;
    scene.add(groundMesh);
}
function addAmbiance(){
    // Créer un brouilalrd d'ambiance
    scene.fog = new THREE.Fog(0x000020, 10, 120);
    scene.background = new THREE.Color( 0x000020  );
                
    // const hemiLight = new THREE.HemisphereLight( 0xffffff, 0x8d8d8d, 1 );
    // hemiLight.position.set( 0, 20, 0 );
    // scene.add( hemiLight );

				const dirLight = new THREE.DirectionalLight( 0xffffff, 1 );
				// dirLight.position.set( - 3, 10, - 10 );
                dirLight.position.set( 0, 30, -10 );
				dirLight.castShadow = true;
				dirLight.shadow.camera.top = 40;
				dirLight.shadow.camera.bottom = - 40;
				dirLight.shadow.camera.left = - 40;
				dirLight.shadow.camera.right = 40;
				dirLight.shadow.camera.near = 0.1;
				dirLight.shadow.camera.far = 50;
				scene.add( dirLight );


    // Créer une lumière ambiante
    const ambient = new THREE.AmbientLight(0xffffff, 1);
    scene.add(ambient);

    // const hemiLight = new THREE.HemisphereLight( 0xffffff, 0x8d8d8d, 1 );
    // hemiLight.position.set( 0, 20, 0 );
    // scene.add( hemiLight );

    // Créer une lampe projecteur (spotlight)
    // const spotLight = new THREE.SpotLight(0xffffff, .5);
    // spotLight.position.set(0, 0, 20);
    // spotLight.angle = Math.PI / 3;
    // spotLight.penumbra = 0.5;
    // spotLight.decay = 1;
    // spotLight.distance = 300;

    // // Activer les ombres pour la lampe projecteur
    // spotLight.castShadow = true;
    // spotLight.shadow.mapSize.width = 512;
    // spotLight.shadow.mapSize.height = 512;
    // spotLight.shadow.camera.near = 1;
    // spotLight.shadow.camera.far = 300;
    // spotLight.shadow.focus = 1;

    // // Ajouter la lampe projecteur, son assistant, et sa cible à la scène
    // scene.add(spotLight, spotLight.target);

    // // Créer un assistant visuel pour la lampe projecteur
    // const slHelper = new THREE.SpotLightHelper(spotLight);
    // scene.add(slHelper);
}
function setDomEvents(){
    // var geometry	= new THREE.BoxGeometry( 1, 1, 1);
	// var material	= new THREE.MeshNormalMaterial();
	// var mesh	= new THREE.Mesh( geometry, material );
	// scene.add( mesh );

    // // Contrôles pour déplacer la caméra
    // console.log('THREE',THREE)
    // console.log('THREEx',THREEx)
    // var domEvents	= new THREEx.DomEvents(camera, renderer.domElement)
    // domEvents	= new THREEx.DomEvents()
}
function addOrbitControls(){
    // Contrôles pour déplacer la caméra
    controls = new OrbitControls(camera, renderer.domElement);
}
function addScene(){
    // Ajouter le rendu au dom
    let container = document.getElementById('container')
    renderer.domElement.style.position = 'absolute';
    renderer.domElement.style.top = '-5px';
    renderer.domElement.style.left = '-5px';
    container.appendChild(renderer.domElement);
}
//------------------------------------------
function getTextMesh(message) {
    const textGeometry = new TextGeometry(message, {
        font: Font,
        size: .3,
        height: 0.05,
        curveSegments: 12,
        bevelEnabled: true,
        bevelThickness: 0.01,
        bevelSize: 0.02,
        bevelSegments: 5,
    });

    const textMaterial = new THREE.MeshStandardMaterial({
        color: 0x000000
    });
    const textMesh = new THREE.Mesh(textGeometry, textMaterial);

    return textMesh;
}
//------------------------------------------
function addTimeline(){
    // La timeline
    let size = {x: 0.9,y: 0.9,z: 0.9};
    let gap = {x: 0.1};
    let max = 10;
    let count = Object.keys(timeline).length
    let length = count > max ? max : count;
    let start = 0 - (Math.floor(length/2) * size.x) - (Math.floor(length/2) * gap.x )
    let pos = {x: start, y: size.z / 2 , z: 2};
    
    if(count>0){
        timeline.forEach(row => {
            // Créer un cube (id,birth,typeaction,ideleves,idpc)
            let color = (row.typeaction === 'in') ? 0x00ff00 : 0xff0000;
            let pc = new THREE.BoxGeometry(size.x, size.y, size.z);
            let pcMate = new THREE.MeshPhongMaterial({color:color,opacity:0.3,transparent:true});
            let cubeMesh = new THREE.Mesh(pc, pcMate);
            cubeMesh.position.x = pos.x;
            cubeMesh.position.y = pos.y;
            cubeMesh.position.z = pos.z;
            cubeMesh.rotation.y = -Math.PI/8
            row['uuid'] = cubeMesh.uuid
            row['table'] = 'timeline'
            let texte = getTextMesh(row.idpc.toString());
            addInfoToMeshDatasUser(row,cubeMesh)
            cubeMesh.add(texte);

            const actionObj = models.timeline.clone();
            actionObj.castShadow = true;
            actionObj.receiveShadow = true;

            texte.position.z = size.z / 2;
            pos.x += size.x + gap.x


            cubeMesh.add(actionObj);
            scene.add(cubeMesh);
            count++;
            if(count>max) {count=0;pos.x=start;pos.z+=size.z+gap.x}
        });
    }
}

function addPcs({
    pcs,
    center = {x: 0, y: 0, z: 0},
    gap = 0.2,
    layout = "flat", // "flat" pour xz, "tall" pour xy
    size = {x: 0.9, y: 0.9, z: 0.9}
}) {
    let count = pcs.length;
    if (count === 0) return;

    // Calcule la taille de la grille
    let gridSize = Math.ceil(Math.sqrt(count));

    // Décale pour centrer la grille autour de "center"
    let offset = (gridSize - 1) / 2;

    pcs.forEach((row, index) => {
        let i = index % gridSize;
        let j = Math.floor(index / gridSize);

        let posX = center.x + (i - offset) * (size.x + gap);
        let posY = center.y + (layout === "tall" ? ((j - offset) * (size.y + gap))+(offset+size.y) : size.y / 2);
        let posZ = center.z + (layout === "flat" ? (j - offset) * (size.z + gap) : 0);

        // Couleur selon état
        let color = (row.position === 'in') ? 0x00ff00 : 0xff0000;
        let geometry = new THREE.BoxGeometry(size.x, size.y, size.z);
        let material = new THREE.MeshPhongMaterial({ color: color, opacity: 0.3, transparent: true });
        let cubeMesh = new THREE.Mesh(geometry, material);
        cubeMesh.position.set(posX, posY, posZ);
        cubeMesh.rotation.y = -Math.PI / 8;

        row['uuid'] = cubeMesh.uuid;
        row['table'] = 'pc';

        // Texte
        let texte = getTextMesh(row.id.toString());
        texte.position.z = size.z / 2;
        cubeMesh.add(texte);

        // Clone modèle
        const pcObj = models.pc.clone();
        pcObj.castShadow = true;
        pcObj.receiveShadow = true;
        cubeMesh.add(pcObj);

        addInfoToMeshDatasUser(row, cubeMesh);
        scene.add(cubeMesh);
    });
}

function addEleves(){
    let count = Object.keys(eleves).length
    if(count>0){
        let size = {x: 0.9,y: 0.9,z: 0.9};
        let gap = {x: 0.1};
        let start = 0 - (Math.round(Math.sqrt(eleves.length)) * (size.x + gap.x ))
        let starts = {x:0,y:3,z:0}
        // console.log(start)
        let pos = {x: starts.x+(start/2), y: starts.y+(size.z / 2), z: starts.z+(start/2)};

        eleves.forEach(row => {

            let color = (row.position === 'in') ? 0x00ff00 : 0xff0000;
            let pc = new THREE.BoxGeometry(size.x, size.y, size.z);
            let pcMate = new THREE.MeshPhongMaterial({color:color,opacity:0.1,transparent:true});
            let cubeMesh = new THREE.Mesh(pc, pcMate);
            cubeMesh.position.x = pos.x;
            cubeMesh.position.y = pos.y;
            cubeMesh.position.z = pos.z;
            cubeMesh.rotation.y = -Math.PI/8
            row['uuid'] = cubeMesh.uuid
            row['table'] = 'eleves'
            let texte = getTextMesh(row.id.toString());
            cubeMesh.add(texte);
            texte.position.z = size.z / 2;

            const eleveObj = ( _front.rand(1,2) === 2 ) ? models.h.clone().children[0] : models.f.clone().children[0];
            // const eleveObj = models.eleve.clone().children[0];

            addInfoToMeshDatasUser(row,cubeMesh)
    
            cubeMesh.add(eleveObj);
            eleveObj.position.y=eleveObj.position.y-0.4;
            pos.x += size.x + gap.x
    
            scene.add(cubeMesh);
        });
    }
}
// -----------------------------------------
function onPointerMove( event ) {
    mouse.x =event.clientX
    mouse.y =event.clientY
    pointer.x = ( event.clientX / window.innerWidth ) * 2 - 1;
    pointer.y = - ( event.clientY / window.innerHeight ) * 2 + 1;

}
// -----------------------------------------
function onClick( event ) {
    if(event.target.tagName && event.target.tagName ==="CANVAS" || (event.target.id != 'close')) {
        // WiresGroupe.clear()
        let mesh = findIntersectionObject()
        console.log('click',event,mesh)
        if(mesh && mesh.userData.hover && mesh.userData.hover.active){
            console.log(mesh)
            findWires(mesh)
        }
        else {
            divInfo.classList.remove('clicked')
        }
    }
}
// -----------------------------------------
function findWires( mesh ) {
    let table = mesh.userData.row.table
    let wires= {
        pc:false,
        eleves:false,
        timeline:false        
    }
    console.log(mesh.userData.row.table)
    WiresGroupe.clear()
    if(table === 'eleves'){
        if(mesh.userData.row.lastpc_id) {
            wires.eleves = mesh
            let pcRow = pcs.find(pc => pc.id === mesh.userData.row.lastpc_id);
            wires.pc = scene.getObjectByProperty( 'uuid' , pcRow.uuid ) 
            WiresGroupe.createSpheresBetweenObjects(wires.eleves,wires.pc)
            if(mesh.userData.row.out_date){
                let timelineRow = timeline.find(actions => actions.idpc === mesh.userData.row.lastpc_id && actions.birth === mesh.userData.row.out_date);
                wires.timeline = scene.getObjectByProperty( 'uuid' , timelineRow.uuid ) 
                WiresGroupe.createSpheresBetweenObjects(wires.pc,wires.timeline)
            }
        }
    }
    if(table === 'pc'){
        if(mesh.userData.row.lasteleve_id) {
            wires.pc = mesh
            let elevesRow = eleves.find(eleve => eleve.id === mesh.userData.row.lasteleve_id);
            wires.eleves = scene.getObjectByProperty( 'uuid' , elevesRow.uuid ) 
            WiresGroupe.createSpheresBetweenObjects(wires.pc,wires.eleves)
            if(mesh.userData.row.out_date){
                let timelineRow = timeline.find(actions => actions.ideleves === mesh.userData.row.lasteleve_id && actions.birth === mesh.userData.row.out_date);
                wires.timeline = scene.getObjectByProperty( 'uuid' , timelineRow.uuid ) 
                WiresGroupe.createSpheresBetweenObjects(wires.pc,wires.timeline)
            }
        }
    }
    if(table === 'timeline'){
        if(mesh.userData.row.ideleves) {
            wires.timeline = mesh
            let elevesRow = eleves.find(eleve => eleve.id === mesh.userData.row.ideleves);
            wires.eleves = scene.getObjectByProperty( 'uuid' , elevesRow.uuid ) 
            WiresGroupe.createSpheresBetweenObjects(wires.timeline,wires.eleves)
            if(mesh.userData.row.idpc){
                let pcRow = pcs.find(pc => pc.id === mesh.userData.row.idpc );
                console.log(pcRow)
                wires.pc = scene.getObjectByProperty( 'uuid' , pcRow.uuid ) 
                WiresGroupe.createSpheresBetweenObjects(wires.timeline,wires.pc)
            }
        }
    }
}
function findIntersectionObject(){
    // find intersections
    raycaster.setFromCamera( pointer, camera );
    const intersects = raycaster.intersectObjects( scene.children, false );
    if ( intersects.length > 0 ) {
        INTERSECTED = intersects[ 0 ].object;
        hydrateDivInfos(divInfoTarget,INTERSECTED.userData)
        divInfo.classList.add('clicked')
        divInfo.classList.remove('out')
        return INTERSECTED
    }
    return false
}
function addInfoToMeshDatasUser(row,mesh) {
    // console.log(row,mesh)
    mesh.userData['row'] = row
    mesh.userData['hover'] = {active:true}
}
// -----------------------------------------
function createDivInfo() {
    divInfo = _front.createDiv({attributes: {className:'divinfo'}})
    divInfoClose = _front.createDiv({attributes: {id:'close',className:'divinfo-close',textContent:'Close'}})
    divInfoTarget = _front.createDiv({attributes: {className:'divinfo-target'}})
    divInfo.appendChild(divInfoTarget)
    divInfo.appendChild(divInfoClose)
    divInfoClose.addEventListener('click',(event)=>{
        event.preventDefault()
        divInfo.classList.remove('clicked')
    })
    document.body.appendChild(divInfo)
}
// -----------------------------------------
function hydrateDivInfos(target,userData) {
    let row = userData.row
    if(target && row){
        target.textContent=''
        let container = _front.createDiv({
            tag:'div',
            attributes:  {className:'datainfo'},
            style:  {display:"flex",flexDirection:"column"}
        })
        for (const key in row) {
            // if (Object.prototype.hasOwnProperty.call(row, key)) {
                let classname = (key == 'uuid' || key == 'table') ?  ' item-wire' : ''
                let item = _front.createDiv({
                    attributes:  {className:'item'+classname},
                    // style:  {display:"flex", flexDirection:"row"}
                })
                let label = _front.createDiv({
                    attributes:  {textContent: key,className:'item-label'},
                    // style:  {padding:"2px 5px",color:'white',backgroundColor:'rgba(25, 25, 49, 0.8)'}
                })
                let data = _front.createDiv({
                    attributes:  {textContent: row[key]??'null',className:'item-data'},
                    // style:  {flexGrow: 2,padding:"2px 5px",backgroundColor:'rgba(198, 198, 240, 0.8)'}
                })
                item.appendChild(label)
                item.appendChild(data)
                container.appendChild(item)
            // }
        }

        target.appendChild(container)
    }
    // divInfo.style.top = ((mouse.y>window.innerHeight/2)?(mouse.y-80):(mouse.y-50)) + 'px'
    // divInfo.style.left =((mouse.x>window.innerWidth/2)?(mouse.x-200):(mouse.x+70)) + 'px'
    // divInfo.style.top = '20%'
    // divInfo.style.left ='30px'
}
// -----------------------------------------
function closeDivsInfo() {
    divInfo.style.display = 'none'
    divInfo.textContent = ''
}
// -----------------------------------------
function addCssToDom() {
    let strinCss = ''
    strinCss += '.divinfo {top:20%;left:-30px;opacity:0;transition: all .5s ease-out;border:1px solid rgba(43, 6, 6, 0.5);width:max-content;'
    strinCss += 'border-radius:15px;position:absolute;background-color:rgba(255,255,255,0.8);width:max-content;}'
    strinCss += '.divinfo-target {}'
    strinCss += '.datainfo {margin:4px;;overflow:hidden;border-radius: 12px;}'
    strinCss += '.divinfo-close {cursor:pointer;z-index:0;position:absolute;top:0px;right:0px;border-bottom-left-radius:9px;border-top-right-radius:15px;background-color:rgba(3, 1, 133, 0.8);color:white;padding:2px 5px;}'
    strinCss += '.divinfo.active {left:20px;opacity:1;transition: all .2s ease-in;}'
    strinCss += '.divinfo.clicked {left:20px;opacity:1;transition: all .2s ease-in;}'
    strinCss += '.item {display: flex; flex-direction: row;}'
    strinCss += '.item .item-label{padding:2px 5px;color:white;background-color:rgba(25, 25, 49, 0.8);}'
    strinCss += '.item .item-data {flex-grow:2;padding:2px 5px;background-color:rgba(198, 198, 204, 0.8);}'
    strinCss += '.item.item-wire .item-label{background-color:rgb(0, 0, 0);}'
    strinCss += '.item.item-wire .item-data{background-color:rgba(150, 150, 150, 0.81);}'
    _front.addCss(strinCss,'hover')
}
