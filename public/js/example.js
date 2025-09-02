"use strict";
import { _App } from './example/App.js';
document.addEventListener("DOMContentLoaded", () => {
    try {
        const App = new _App();
        if (App) {
            App.init();
        } else {
            console.error("Erreur : Impossible d'initialiser l'application.");
        }
    } catch (error) {
        console.error("Une erreur s'est produite lors de l'initialisation :", error);
    }
});
