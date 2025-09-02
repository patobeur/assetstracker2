import * as THREE from "three";
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
let ObjMeshLoader = {
    loader:new GLTFLoader(),
    modelEntries:{},
    loadedCount:0,
    loadedModels:{},
    modelsToLoad: [
        {
            pc:{type:"pc",src:'../../gltfModels/Laptop.glb'},
            timeline:{type:"timeline",src:'../gltfModels/Pickup Crate.glb'},
            h:{type:"h",src:'../gltfModels/Houseplant.glb'},
            f:{type:"f",src:'../gltfModels/Houseplant2.glb'},

        }
    ],
    defaultCallBackFunction:(loadedModels)=>{
        console.log('no default CallBack Function',loadedModels??'empty')
    },
    init:function(models,CallBackFunction){
        this.CallBackFunction = CallBackFunction ?? this.defaultCallBackFunction;
        this.modelEntries = Object.entries(this.modelsToLoad[0]);
        
        this.chargeObj()
    },
    chargeObj: function(){
        this.modelEntries.forEach(([key, modelInfo]) => {
            this.loader.load(
                modelInfo.src,
                (gltf) => {
                    this.loadedModels[key] = gltf.scene; // Stocke le modèle sous sa clé (pc, eleves, timeline)
                    this.loadedCount++;
    
                    if (this.loadedCount === this.modelEntries.length) {

                        
                    this.loadedModels.pc.scale.set(0.005,0.005,0.005)
                    this.loadedModels.f.children[0].scale.set(0.15,0.15,0.15)
                    this.loadedModels.h.children[0].scale.set(0.005,0.005,0.005)
        
                    this.loadedModels.pc.castShadow = true;
                    this.loadedModels.pc.receiveShadow = true;
                    this.loadedModels.f.castShadow = true;
                    this.loadedModels.h.receiveShadow = true;
                    this.loadedModels.timeline.receiveShadow = true;


                        this.CallBackFunction(this.loadedModels); // Appelle la fonction quand tout est chargé
                    }
                },
                (xhr) => {
                    console.log(`Chargement de ${modelInfo.src}: ${((xhr.loaded / xhr.total) * 100).toFixed(2)}%`);
                },
                (error) => {
                    console.error(`Erreur lors du chargement de ${modelInfo.src}:`, error);
                }
            );
        });
    }
}
export {ObjMeshLoader}