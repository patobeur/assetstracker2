
document.addEventListener("DOMContentLoaded", () => {
  // Remplacer les balises <i> avec une image <img> uniquement si elles ont la classe "ico"
  const prefix = 'fico-'
  const iconLibrary = {
      'user':'user',
      'index':'index',
      'in':'in',
      'out':'out',
      'profile':'profile',
      'login':'login',
      'logout':'logout',
      'listes':'listes',
      'actions':'actions',
      'admins':'admins',
      'glpi':'glpi',
      'glpipc':'listpc',
      'plus':'plus',
      'timeline':'timeline',
      'interface':'interface',
      'listpc':'listpc',
      'listeleves':'listeleves',
      'github':'github',
      'three':'three',
      'interrogation':'interrogation',
  }
  // Sélectionner toutes les balises <i> avec la classe "ico"
  const icons = document.querySelectorAll("i.ico");

  icons.forEach(icon => {
    // Récupérer le contenu de l'attribut alt ou une classe spécifique
    const altValue = icon.getAttribute("alt");
    const classValue = icon.className;

    // Vérifier si la classe contient autre chose que "ico"
    const additionalClass = classValue.split(" ").find(cls => cls !== "ico");

    // Trouver la classe qui commence par "fico-"
    const ficoClass = classValue.split(" ").find(cls => cls.startsWith("fico-"));

    // Supprimer le préfixe "fico-" du nom de la classe
    const classWithoutFico = ficoClass ? ficoClass.replace("fico-", "") : null;

    let imageName = "";
    if(classWithoutFico){
      imageName = `${iconLibrary[classWithoutFico] ?? 'default'}.svg`;
    }

    if (imageName) {
      // Créer une nouvelle balise <img>
      const img = document.createElement("img");
      img.src = '/vendor/feunico/svg/' + imageName;
      img.alt = altValue || "";

      // Remplacer la balise <i> par la balise <img>
      icon.parentNode.replaceChild(img, icon);
    }
  });
});
