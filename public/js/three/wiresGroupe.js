import * as THREE from "three";
let WiresGroupe = {
    wiresGroupe:new THREE.Group(),
    scene:false,
    createSpheresBetweenObjects:(object1, object2)=> {
        const pos1 = object1.position.clone();
        const pos2 = object2.position.clone();
    
        // Vecteur directionnel normalisé
        const direction = new THREE.Vector3().subVectors(pos2, pos1).normalize();
    
        // Distance totale
        const distance = pos1.distanceTo(pos2);
    
        // Création des sphères tous les 1 unité
        const sphereGeometry = new THREE.SphereGeometry(0.1, 16, 16); // Sphères de rayon 0.2
        const sphereMaterial = new THREE.MeshBasicMaterial({ color: 0xff0000 }); // Rouge
        for (let i = 0; i < distance; i+=.2) {
            const position = pos1.clone().addScaledVector(direction, i); // Calcul de la position
    
            const sphere = new THREE.Mesh(sphereGeometry, sphereMaterial);
            sphere.position.copy(position);
            WiresGroupe.wiresGroupe.add(sphere);
        }
        WiresGroupe.scene.add(WiresGroupe.wiresGroupe);
    },
    clear:()=> {
        WiresGroupe.scene.remove(WiresGroupe.wiresGroupe);
        WiresGroupe.wiresGroupe = new THREE.Group();
    },
    init:(scene)=> {
        WiresGroupe.scene=scene
    }
}
export {WiresGroupe}