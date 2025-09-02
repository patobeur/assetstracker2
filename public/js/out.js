// Exécuter la fonction après que la page est complètement chargée
document.addEventListener('DOMContentLoaded', () => {
	barcodeEleve()
});

function focusInputs() {
	let codeeleve = document.getElementById('codeeleve');
	let codepc = document.getElementById('codepc');
	if (codeeleve.value != "" && codepc.value == "") codepc.focus();
	if (codeeleve.value == "" && codepc.value != "") codeeleve.focus();
	if (codeeleve.value == "" && codepc.value == "") codeeleve.focus();
}
// Fonction qui met à jour la date et l'heure
function barcodeEleve() {
	let codeeleve = document.getElementById('codeeleve');
	let codepc = document.getElementById('codepc');

	let svgpc = document.getElementById('barcodePC');
	let svgeleve = document.getElementById('barcodeEleve');

	let format = {
		format: "CODE128",	// Format du code-barres
		lineColor: "#000",	// Couleur des lignes
		width: 2,			// Largeur de chaque barre
		height: 30,			// Hauteur du code-barres
		displayValue: true	// Afficher la valeur sous le code-barres
	}

	if (codeeleve && svgeleve.style.display!='none' && codeeleve.value != ""){
		console.log('gobarcodeEleve')
		JsBarcode("#barcodeEleve", codeeleve.value, format);
	}
	else {
		svgeleve.style.display = 'none';
	}

	if (codepc && svgpc.style.display!='none' && codepc.value != ""){
		console.log('gobarcodePC')
		JsBarcode("#barcodePC", codepc.value, format);
	}
	else {
		svgpc.style.display = 'none';
	}
	focusInputs()
}