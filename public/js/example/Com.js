"use strict";
class _Com {
    constructor() {
        this.url = '/contact';
        this.methode = 'POST';
    }

    /**
     * Envoie une requête au serveur et retourne la réponse en JSON.
     * @param {string|boolean} params - Données à envoyer (false si pas de données).
     * @returns {Promise<object>} Réponse du serveur en JSON.
     */
    async getJsonFromServeur(params = false) {
        try {
            const response = await fetch(this.url, {
                method: this.methode,
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `data=${params ?? ""}`
            });

            if (!response.ok) {
                throw new Error(`Erreur serveur: ${response.status} ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error("Erreur lors de la requête :", error);
            throw error; // Permet de gérer l'erreur ailleurs si besoin
        }
    }
}
export { _Com };
