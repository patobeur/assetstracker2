"use strict";
class _Login {
	constructor() {
		this.form = {
			'id':"loginForm",
			'method':"POST",
			'action':"",
		}
	}
	async init() {
		console.log('-----------------_Login ok--------------------')
	}
	addForm() {
		console.log('-----------------addForm _Login ok--------------------')
		let target = document.getElementById('container')
		// Création du conteneur principal
		const formContainer = document.createElement("div");
		formContainer.classList.add("form-container");
		formContainer.id = "login_container";

		// Création du formulaire
		const form = document.createElement("div");
		form.id = this.form.id;
		form.classList.add("form");
		// form.method = this.form.post;
		// form.action = this.form.action;

		// Contenu dynamique (placeholder pour une éventuelle injection)
		const contentPlaceholder = document.createElement("div");

		// Boîte de connexion
		const loginBox = document.createElement("div");
		loginBox.classList.add("login-box");

		const title = document.createElement("h2");
		title.textContent = "Vous n'êtes pas connecté.";
		const chapeau = document.createElement("p");
		chapeau.textContent = "Connectez-vous pour avoir plus de services.";

		// Message d'erreur ou de confirmation
		const messageDiv = document.createElement("div");
		messageDiv.id = "message";
		messageDiv.classList.add("message");

		// Bloc contenant les champs d'entrée
		const blocsDiv = document.createElement("div");
		blocsDiv.classList.add("blocs");

		// Champ Username
		const inputContainerUser = document.createElement("div");
		inputContainerUser.classList.add("input-container");

		const labelUser = document.createElement("label");
		labelUser.setAttribute("for", "username");
		labelUser.style.display = "none";
		labelUser.textContent = "Username";

		const iconUser = document.createElement("span");
		iconUser.classList.add("icon");
		iconUser.textContent = "🤚";

		const inputUser = document.createElement("input");
		inputUser.type = "text";
		inputUser.id = "username";
		inputUser.name = "username";
		inputUser.placeholder = "Pseudo";
		inputUser.required = true;
		inputUser.autofocus = true;

		inputContainerUser.append(labelUser, iconUser, inputUser);

		// Champ Mot de passe
		const inputContainerPass = document.createElement("div");
		inputContainerPass.classList.add("input-container");

		const labelPass = document.createElement("label");
		labelPass.setAttribute("for", "password");
		labelPass.style.display = "none";
		labelPass.textContent = "Mot de Passe";

		const iconPass = document.createElement("span");
		iconPass.classList.add("icon");
		iconPass.textContent = "🔒";

		const inputPass = document.createElement("input");
		inputPass.type = "password";
		inputPass.id = "password";
		inputPass.name = "password";
		inputPass.placeholder = "Mot de passe";
		inputPass.required = true;

		inputContainerPass.append(labelPass, iconPass, inputPass);

		// Ajout des champs au bloc
		blocsDiv.append(inputContainerUser, inputContainerPass);

		// Bouton de soumission
		const buttonContainer = document.createElement("div");
		buttonContainer.classList.add("blocs", "center");

		const submitButton = document.createElement("button");
		submitButton.id = "submitlogin";
		submitButton.type = "submit";
		submitButton.textContent = "Se connecter";

		buttonContainer.appendChild(submitButton);

		// Ajout des éléments à la boîte de connexion
		loginBox.append(title, chapeau, messageDiv, blocsDiv, buttonContainer);

		// Ajout des éléments au formulaire
		form.append(contentPlaceholder, loginBox);

		// Ajout du formulaire au conteneur
		formContainer.appendChild(form);

		// Ajout du formulaire dans le `target`
		target.appendChild(formContainer);
		this.listenForm()
	}
	listenForm = () => {
		let loginForm = document.getElementById(this.form.id);
		let loginSubmit = document.getElementById('submitlogin');
		const messageDiv = document.getElementById("message");
		if(loginForm && messageDiv){
			console.log('-----------------listenForm ok--------------------')
			loginSubmit.addEventListener("click", async (event) => {
				event.preventDefault();
		
				const username = document.getElementById("username").value;
				const password = document.getElementById("password").value;
		
				try {
					const response = await fetch("/contact", {
						method: "POST",
						headers: {
							"Content-Type": "application/x-www-form-urlencoded"
						},
						body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
					});
		
					const data = await response.json();
		

					if (response.ok) {
						console.log('reponse ok')
						if(data.message==="USER"){
							this.delForm();
							//set user
							console.log(data)
						}
						else {
							messageDiv.innerHTML = `<p style="color: red;">❌ ${data.message || "Un drôle de soucis !."}</p>`;
							document.getElementById("username").value='';document.getElementById("password").value='';
						}
					} else {
						messageDiv.innerHTML = `<p style="color: red;">❌ ${data.message || "Échec de la connexion."}</p>`;
						document.getElementById("username").value='';document.getElementById("password").value='';
					}
				} catch (error) {
					console.error("Erreur :", error);
					messageDiv.innerHTML = `<p style="color: red;">❌ Une erreur est survenue.</p>`;
					document.getElementById("username").value='';document.getElementById("password").value='';
				}
			});

		}
		else{
			console.log('-----------------listenForm Bug--------------------')
		}
    }
	delForm = () => {
		let target = document.getElementById('login_container')
		target.remove()
	}
}
export { _Login };
