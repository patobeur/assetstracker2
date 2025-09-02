<?php
	namespace app\controllers;
	
	class ExampleController {
		private $menus = [];
		private $content = [
				'TITLE' => "example",
				'CONTENT'   => ''
		];
		private $tableprefix;
		private $pdo;
		private $CheckDb;
	
		public function __construct($CheckDb=false) {
			if($CheckDb){
				$this->CheckDb = $CheckDb;
				$this->tableprefix = $this->CheckDb->getConf()['tableprefix'];
			}
		}
        
		public function ExampleHandler()
		{	
			$this->renderView();
			return $this->content;
		}
		public function ContactHandler()
		{	
			$this->contact();
		}
		
		private function contact(){
			header('Content-Type: application/json');
			$this->handlePdoOrDie();
			$this->handlePostOrDie();
			die();
		}
		
		private function renderView(){
			$htmlView = file_get_contents(filename: CONFIG['APPROOT'].'app/views/example.php');		
			$htmlView = str_replace('{{TITLE}}', $this->content['TITLE'], $htmlView);
			// $htmlView = str_replace('{{CONTENT}}', $this->content['CONTENT'], $htmlView);

			$this->content['CONTENT'] = $htmlView;

		}


		// Gérer le traitement de connexion
		public function handlePostOrDie() {
			
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				// ok
				// rules
				if (isset($_POST['say']) && $_POST['say']==='hi' && !isset($_SESSION['user'])) {
					echo json_encode(["status" => "errors", "message" => "NEED LOGIN", "post" => $_POST]);
					http_response_code(200);	
					die();
				}
				elseif(isset($_POST['username']) && isset($_POST['password'])){
					$this->handleContactOrDie();
				}
			}
			else {
				// pas de POST alors pas de reponse
				echo json_encode(["status" => "errors", "message" => "pas de post.", "post" => []]);
				http_response_code(200);	
				die();
			}
		}

		// Gérer le traitement Pdo
		public function handlePdoOrDie() {
			try {
				$this->pdo = $this->CheckDb->getPdo();
				if(!$this->pdo){
					echo json_encode(["status" => "error", "message" => "Pas de connexion active !<br/>Merci de patienter !.", "post" => "Pas de connexion à la bdd : "]);
					http_response_code(200);
					die();
				}
			} catch (\PDOException $e) {
				echo json_encode(["status" => "error", "message" => "erreurs", "post" => "Erreur de connexion à la base de données : " . $e->getMessage()]);
				http_response_code(200);
			} catch (\Exception $e) {
				echo json_encode(["status" => "error", "message" => "erreurs.", "post" => "Erreur de connexion à la base de données : " . $e->getMessage()]);
				http_response_code(200);
			}
		}
		// Gérer le traitement de connexion
		public function handleContactOrDie() {
				// Récupération et validation des données d'entrée
				$username = isset($_POST['username']) ? trim($_POST['username']) : '';
				$password = isset($_POST['password']) ? $_POST['password'] : '';

				// Valider le nom d'utilisateur pour éviter des caractères non valides
				$username = filter_var($username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
				
				if (!empty($username) && !empty($password)) {
					// $_POST['password']= md5($_POST['password']);
					$errors=$this->login($username,$password);
				}
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
					$table = $this->tableprefix."administrateurs";
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

						echo json_encode(["status" => "success", "message" => "USER", "post" => [], "action" => ["datasNav" => $this->menus], "user" => $admin]);
						http_response_code(200);
						exit;

					} else {
						echo json_encode(["status" => "error", "message" => "Mauvaises données.", "post" => 'ko', "action" => '']);
						http_response_code(200);
					}
				}
			}
			return $errors;
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
		private function sessionKeyUpdate(): void{  
			if(isset($_SESSION['user']) && isset($_SESSION['user']['id']) && isset($_SESSION['user']['id'])){
				$id = $_SESSION['user']['id'];
				$sessionkey = $_SESSION['user']['sessionkey'];
				$table = $this->tableprefix."administrateurs";
				$query = "UPDATE ".$table." SET sessionkey = :sessionkey WHERE id = :id";
				$stmt = $this->pdo->prepare($query);
				$stmt->bindParam(':id', $id, \PDO::PARAM_STR);
				$stmt->bindParam(':sessionkey', $sessionkey, \PDO::PARAM_STR);
				$stmt->execute();
			}
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
				$table = $this->tableprefix."typeaccounts";
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
		/**
		 * Fonction pour noter les visites
		 */
		private function loginUpdate(): void{  
			if(isset($_SESSION['user']) && isset($_SESSION['user']['id'])){
				$login_date = date("Y-m-d H:i:s");
				$id = $_SESSION['user']['id'];
				$_SESSION['user']['login_date'] = $login_date;
				$table = $this->tableprefix."visites";
				$query = "INSERT INTO ".$table." (administrateurs_id, login_date) VALUES (".$id.",'".$login_date."')";
				$stmt = $this->pdo->prepare($query);
				$stmt->execute();
			}
		}
		private function setDatasNav(){
			$this->menus = [
				'index'=> [
					'ico'=>'index',
					'tag'=> 'a',
					'url'=> 'index',
					'content'=> 'Accueil',
					'class'=> 'index',
					'hiddenIfUrl'=> false,
					'href'=> '/',
					'lv'=> (int)(0),
				],
				'listes'=> [
					'ico'=>'listes',
					'tag'=> 'span',
					'url'=> '',
					'content'=> 'Listes',
					'hiddenIfUrl'=> false,
					'href'=> '',
					'class'=> 'listes',
					'needLog'=> true,
					'lv'=> (int)(1),
					'others'=> [
						'listpc'=> [
							'ico'=>'listpc',
							'tag'=> 'a',
							'url'=> 'listpc',
							'content'=> 'List Pc',
							'hiddenIfUrl'=> false,
							'href'=> '/listpc',
							'needLog'=> true,
							'lv'=> (int)(2),
						],
						'listeleves'=> [
							'ico'=>'listeleves',
							'tag'=> 'a',
							'url'=> 'listeleves',
							'content'=> 'List Élèves',
							'hiddenIfUrl'=> false,
							'href'=> '/listeleves',
							'needLog'=> true,
							'lv'=> (int)(2),
						],
						'timeline'=> [
							'ico'=>'timeline',
							'tag'=> 'a',
							'url'=> 'timeline',
							'content'=> 'Timeline',
							'hiddenIfUrl'=> false,
							'href'=> '/timeline',
							'class'=> 'timeline',
							'needLog'=> true,
							'lv'=> (int)(1),
						],
					]
				],
				'actions'=> [
					'ico'=>'actions',
					'tag'=> 'span',
					'url'=> '',
					'content'=> 'Actions',
					'hiddenIfUrl'=> false,
					'href'=> '',
					'class'=> 'actions',
					'needLog'=> true,
					'lv'=> (int)(1),
					'others'=> [
						'in'=> [
							'ico'=>'in',
							'tag'=> 'a',
							'url'=> 'in',
							'content'=> 'Rendez',
							'hiddenIfUrl'=> false,
							'href'=> '/in',
							'class'=> 'in',
							'needLog'=> true,
							'lv'=> (int)(1),
						],
						'out'=> [
							'ico'=>'out',
							'tag'=> 'a',
							'url'=> 'out',
							'content'=> 'Empruntez',
							'hiddenIfUrl'=> false,
							'href'=> '/out',
							'class'=> 'out',
							'needLog'=> true,
							'lv'=> (int)(1),
						],]
				],
				'admins'=> [
					'ico'=>'admins',
					'tag'=> 'span',
					'url'=> '',
					'content'=> 'Admins',
					'hiddenIfUrl'=> false,
					'href'=> '',
					'class'=> 'admins',
					'needLog'=> true,
					'lv'=> (int)(3),
					'others'=> [
						'glpipc'=> [
							'ico'=>'glpipc',
							'tag'=> 'a',
							'url'=> 'glpipc',
							'content'=> 'Glpipc',
							'hiddenIfUrl'=> false,
							'href'=> '/glpipc',
							'class'=> 'glpipc',
							'needLog'=> true,
							'lv'=> (int)(3),
						],
						'plus'=> [
							'ico'=>'plus',
							'tag'=> 'span',
							'url'=> '',
							'content'=> 'plus',
							'hiddenIfUrl'=> false,
							'href'=> '',
							'class'=> 'plus',
							'needLog'=> true,
							'lv'=> (int)(3),
							'others'=> [
								'github'=> [
									'ico'=>'github',
									'tag'=> 'a',
									'url'=> 'github',
									'content'=> 'Github',
									'class'=> 'github',
									'hiddenIfUrl'=> false,
									'href'=> 'https://github.com/patobeur/assetstracker',
									'target'=> '_github',
									'needLog'=> true,
									'lv'=> (int)(3),
								],
								'plus'=> [
									'ico'=>'plus',
									'tag'=> 'span',
									'url'=> '',
									'content'=> 'plus',
									'hiddenIfUrl'=> false,
									'href'=> '',
									'class'=> 'plus',
									'needLog'=> true,
									'lv'=> (int)(3),
									'others'=> [
										'three'=> [
											'ico'=>'three',
											'tag'=> 'a',
											'url'=> 'three',
											'content'=> '3D',
											'hiddenIfUrl'=> false,
											'href'=> '/three',
											'class'=> 'three',
											'needLog'=> true,
											'lv'=> (int)(3),
										]
									]
								],
							]
						],
					]
				],
				'profile'=> [
					'ico'=>'profile',
					'tag'=> 'a',
					'url'=> 'profile',
					'content'=> 'Profile',
					'hiddenIfUrl'=> false,
					'href'=> '/profile',
					'needLog'=> true,
					'lv'=> (int)(2),
					'classHideContent'=>true,
					'classRight'=>true,
				],
				'login'=> [
					'ico'=>'login',
					'tag'=> 'a',
					'url'=> 'login',
					'content'=> 'LogIn',
					'class'=> 'login',
					'hiddenIfUrl'=> ['login'],
					'href'=> '/login',
					'needLog'=> false,
					'needUnlog'=> true,
					'lv'=> (int)(0),
					'classRight'=>true,
				],
				'interface'=> [
					'ico'=>'interface',
					'tag'=> 'a',
					'url'=> 'interface',
					'content'=> 'Options',
					'hiddenIfUrl'=> false,
					'href'=> '/interface',
					'class'=> 'interface',
					'needLog'=> true,
					'lv'=> (int)(1),
					'classHideContent'=>true,
				],
				'logout'=> [
					'ico'=>'logout',
					'tag'=> 'a',
					'url'=> 'logout',
					'content'=> 'Déconnexion',
					'class'=> 'deco',
					'hiddenIfUrl'=> false,
					'href'=> '/logout',
					'needLog'=> true,
					'lv'=> (int)(0),
					'classHideContent'=>true,
				],
			];

		}
    }