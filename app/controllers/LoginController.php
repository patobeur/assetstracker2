<?php
	namespace app\controllers;
	
	class LoginController {
		private $pdo;
		private $CheckDb;
		private $tableprefix = '';	
	
		public function __construct($CheckDb=false) {
			if($CheckDb){
				$this->CheckDb = $CheckDb;
				$this->tableprefix = $this->CheckDb->getConf()['tableprefix'];
			}
		}
		
		// Gérer le traitement de connexion
		public function handleLogin() {
			$this->pdo = $this->CheckDb->getPdo();
			$errors = [];
			if(!$this->pdo){
				$errors[] = "Pas de connexion à la bdd !<br/>Merci de patienter !";
			}
			else {
				if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
					// Récupération et validation des données d'entrée
					$username = isset($_POST['username']) ? trim($_POST['username']) : '';
					$password = isset($_POST['password']) ? $_POST['password'] : '';
	
					// Valider le nom d'utilisateur pour éviter des caractères non valides
					$username = filter_var($username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
					
					if (!empty($username) && !empty($password)) {
						$errors=$this->login($username,$password);
					}
				}
			}            
			return $this->renderView($errors);
		}
	
		function login($username=null,$password=null): array{
			$errors = [];
			$username = trim(string: $username);  
			$password = trim(string: $password);
			if (empty($username) || empty($password)) {
				$errors[] = "Tous les champs doivent être remplis.";
			}
			if (empty($errors)) {
				if(!$this->pdo){
					$errors[] = "Pas de connexion active !";
				}
				else {
					$table = $this->tableprefix.'administrateurs';
					$query = "SELECT * FROM ".$table." WHERE pseudo = :username LIMIT 1";
					$stmt = $this->pdo->prepare($query);
					$stmt->bindParam(':username', $username, \PDO::PARAM_STR);
					$stmt->execute();
					$admin = $stmt->fetch(\PDO::FETCH_ASSOC);
					if ($admin && password_verify(password: $password, hash: $admin['motdepasse'])) {
						// Authentification réussie
	
	
						$this->setUserSession($admin);
						$this->sessionKeyUpdate();
	
						$this->loginUpdate();
	
						if($_SESSION['user']){
							$this->CheckDb->Console->addMsgSESSION([
								"content"=>"Bonjour {$_SESSION['user']['prenom']}",
								"title"=>'👋',
								"class"=>'succes',
								"birth"=>date("h:i:s")
							]);
						}
		
						header(header: "Location: /");
						exit;
					} else {
						$errors[] = "Pseudo ou mot de passe incorrect.";
					}
				}
			}
			return $errors;
		}
		
		/**
		 * Fonction pour noter les visites
		 */
		private function loginUpdate(): void{  
			if(isset($_SESSION['user']) && isset($_SESSION['user']['id'])){
				$login_date = date("Y-m-d H:i:s");
				$id = $_SESSION['user']['id'];
				$_SESSION['user']['login_date'] = $login_date;
				$table = $this->tableprefix.'visites';
				$query = "INSERT INTO ".$table." (administrateurs_id, login_date) VALUES (".$id.",'".$login_date."')";
				$stmt = $this->pdo->prepare($query);
				$stmt->execute();
			}
		}
		private function logoutUpdate(): void{  
			if(isset($_SESSION['user']) && isset($_SESSION['user']['id']) && isset($_SESSION['user']['login_date'])){
				try {
					$this->pdo = $this->CheckDb->getPdo();
					if($this->pdo){
						$logout_date = date("Y-m-d H:i:s");
						$login_date = $_SESSION['user']['login_date'];
						$administrateurs_id = $_SESSION['user']['id'];
						
						$table = $this->tableprefix.'visites';
						$query = "UPDATE ".$table." SET logout_date = :logout_date WHERE administrateurs_id = :id AND login_date = :login_date";
						$stmt = $this->pdo->prepare($query);
						$stmt->bindParam(':id', $administrateurs_id, \PDO::PARAM_STR);
						$stmt->bindParam(':login_date', $login_date, \PDO::PARAM_STR);
						$stmt->bindParam(':logout_date', $logout_date, \PDO::PARAM_STR);
						$stmt->execute();
					}
				} 
				catch (\PDOException $e) {
					die("Logout : Erreur de connexion à la base de données : " . $e->getMessage());
				} catch (\Exception $e) {
					die("Logout : Erreur de deconnexion : " . $e->getMessage());
				}
			}
		}
		private function sessionKeyUpdate(): void{  
			if(isset($_SESSION['user']) && isset($_SESSION['user']['id']) && isset($_SESSION['user']['id'])){
				$id = $_SESSION['user']['id'];
				$sessionkey = $_SESSION['user']['sessionkey'];
				$table = $this->tableprefix.'administrateurs';
				$query = "UPDATE ".$table." SET sessionkey = :sessionkey WHERE id = :id";
				$stmt = $this->pdo->prepare($query);
				$stmt->bindParam(':id', $id, \PDO::PARAM_STR);
				$stmt->bindParam(':sessionkey', $sessionkey, \PDO::PARAM_STR);
				$stmt->execute();
			}
		}
		
		/**
		 * Fonction pour créer la SESSION USER
		 */
		function setUserSession($admin){
			$typeaccount = $this->getTypeAccount($admin['typeaccount_id']);

			$_SESSION['user'] = [
				'id' => $admin['id'],
				'pseudo' => $admin['pseudo'],
				'nom' => $admin['nom'],
				'prenom' => $admin['prenom'],
				'typeaccount_id' => $admin['typeaccount_id'],
				'lv' => $admin['typeaccount_id'],
				'typeaccount' => $typeaccount['content'],
				'sessionkey' => $this->generateUuidV4(),
			];
		}
		/**
		 * Fonction pour login
		 */
		private function generateUuidV4() {
			return sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), // Partie 1
				mt_rand(0, 0xffff), // Partie 2
				mt_rand(0, 0x0fff) | 0x4000, // Version 4
				mt_rand(0, 0x3fff) | 0x8000, // Bits variant
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) // Partie 3
			);
		}
		/**
		 * Fonction getRights
		 */
		function getTypeAccount($id){
			try{
				$table = $this->tableprefix.'typeaccounts';
				$query = "SELECT * FROM ".$table." WHERE id = :id LIMIT 1";
				$stmt = $this->pdo->prepare($query);
				$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
				$stmt->execute();
				$typeAccount = $stmt->fetch(\PDO::FETCH_ASSOC);
				return $typeAccount;
			} catch (\PDOException $e) {
				die("Erreur de connexion à la base de données : " . $e->getMessage());
			} catch (\Exception $e) {
				die("Erreur de connexion à la base de données : " . $e->getMessage());
			}
		}

		// Afficher la vue login avec les erreurs
		private function renderView($errors = []) {
			$html = file_get_contents(CONFIG['APPROOT'].'app/views/login.php');
			$htmlform = file_get_contents(CONFIG['APPROOT'].'app/views/login/loginForm.php');
	
			// Ajouter les erreurs
			$errorHtml = '';
			
			if (!empty($errors)) {
				foreach ($errors as $error) {
					$errorHtml .= "<p class='error'>" . $error . "</p>";
				}
			}
	
			$html = str_replace('{{errors}}', $errorHtml, $html);
			if($this->pdo){
				$html = str_replace('{{loginform}}', $htmlform, $html);
			}
			else {
				$html = str_replace('{{loginform}}', '', $html);
			}
			return [
				'CONTENT'=> $html,
				'TITLE'=> 'Page Login',
			];
		}
		
		public function logout()
		{
			$this->logoutUpdate();
			session_destroy();
			header('Location: /login');
		}



		// BDD
	}