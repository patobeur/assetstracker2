<?php
	namespace app\controllers;

	class ProfileController
	{
		private $view = '';

		private $pdo;
		private $CheckDb;
		private $contents = [
			'TITLE'=> 'Page Profil',
			'CONTENT'=> ''
		];
		private $tableprefix = '';	

		public function __construct($CheckDb) {
			if($CheckDb){
				$this->CheckDb = $CheckDb;
				$this->tableprefix = $this->CheckDb->getConf()['tableprefix'];
			}
			$this->view = file_get_contents(CONFIG['APPROOT'].'app/views/profile.php');
		}
		
		public function showProfile()
		{
			$this->pdo = $this->CheckDb->getPdo();
			if (!isset($_SESSION['user'])) {
				header('Location: /login');
			} else {

				$content = str_replace("{{TITLE}}",$this->contents['TITLE'],$this->view);
				$profileHtml = 'une erreur sans doute ?';
				$row = $this->getProfilRow();
				if ($row && count($row) > 0){
					$profileHtml = "
					<div class=\"text-container\">
					<h3>".$_SESSION['user']['pseudo']."</h3>
						<p>Pseudo: ".$row['pseudo']."</p>
						<p>Nom: ".$_SESSION['user']['nom']."</p>
						<p>Prénom: ".$_SESSION['user']['prenom']."</p>
						<p>account: ".$_SESSION['user']['typeaccount']." (lv:".$_SESSION['user']['typeaccount_id'].")</p>
						<p>Création: ".$row['birth']."</p>
						<p>mail: <a href=". '"mailto:' .$row['mail'].'"'.">M'envoyer un mail ?</a></p>
					</div>";
				}


				$content = str_replace("{{CONTENT}}",$profileHtml, $content);



				$this->contents['CONTENT'] = $content;
				return $this->contents;
			}
		}
		private function  getProfilRow(){
			if(!$this->pdo) return false;
			try{
				$id = $_SESSION['user']['id'];
				$sessionkey = $_SESSION['user']['sessionkey'];
				$select = 'pseudo, nom, prenom, birth, mail';
				$table = $this->tableprefix.'administrateurs';
				$query = "SELECT ".$select." FROM ".$table." WHERE id = :id AND sessionkey = :sessionkey LIMIT 1";
				
				$stmt = $this->pdo->prepare($query);
				$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
				$stmt->bindParam(':sessionkey', $sessionkey, \PDO::PARAM_STR);
				$stmt->execute();
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				if ($row && count($row) > 0){
					// on a une réponse
					return $row;
				}
			} 
			catch (\PDOException $e) {
				die("getProfilRow : Erreur de connexion à la base de données : " . $e->getMessage());
			} catch (\Exception $e) {
				die("getProfilRow : Erreur de deconnexion : " . $e->getMessage());
			}
			return false;
		}
	}