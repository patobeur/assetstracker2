// Exécuter la fonction après que la page est complètement chargée
document.addEventListener('DOMContentLoaded', () => {
	barcode()
});
// Fonction qui met à jour la date et l'heure
function barcode() {
	let codepc = document.getElementById('codepc');
	let svgpc = document.getElementById('barcodePC');

	let format = {
		format: "CODE128",	// Format du code-barres
		lineColor: "#000",	// Couleur des lignes
		width: 2,			// Largeur de chaque barre
		height: 30,			// Hauteur du code-barres
		displayValue: true	// Afficher la valeur sous le code-barres
	}
	
	if (codepc && codepc.value != ""){
		JsBarcode("#barcodePC", codepc.value, format);
	}
	else {svgpc.style.display = 'none';
	}
	if (codepc.value == "") codepc.focus();
}