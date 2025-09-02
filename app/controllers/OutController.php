<?php
	namespace app\controllers;
	
	class OutController {
		private $pdo;
		private $CheckDb;
		private $eleve = null;
		private $pc = null;
		private $messages = [];
		private $tableprefix = '';	
	
		public function __construct($CheckDb=false) {
			if($CheckDb){
				$this->CheckDb=$CheckDb;
				$this->pdo = $this->CheckDb->getPdo();
				$this->tableprefix = $this->CheckDb->getConf()['tableprefix'];
			}
		}
		
		// Gérer le traitement de connexion
		public function handle(): array{
			$this->messages = [];
			if ($_SERVER['REQUEST_METHOD'] === 'GET') {
				if(isset($_GET['a'])) {
					// die($_GET['a']);
				}
			}

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$this->eleve = null;
				$this->pc = null;

				if (!empty($_POST['eleve'])){
					$memberBarrecode = isset($_POST['eleve']) ? trim($_POST['eleve']) : '';
					$memberBarrecode = filter_var($memberBarrecode, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
					if (!empty($memberBarrecode)) {
						// recherche du barrecode dans eleve
						$row = $this->CheckDb->once('eleves',$memberBarrecode);
						if(count($row)===1) {
							$this->eleve = $row[0];
						}
						else {
							$this->messages[]=["content"=>"BarreCode élève introuvable !","result"=>"error"];
						}
					}
				}
				if (!empty($_POST['pc'])){
					$assetBarrecode = $_POST['pc'] ?? '';
					$assetBarrecode = filter_var($assetBarrecode, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
					if (!empty($assetBarrecode)) {
						// recherche du barrecode dans eleve
						$row = $this->CheckDb->once('pc',$assetBarrecode);
						if(count($row)===1) {
							$this->pc = $row[0];
						}
						else {
							$this->messages[]=["content"=>"BarreCode PC introuvable !","result"=>"alerte"];
						}
					}
				}

				if($this->pc && $this->eleve ){
					$insertRespons = $this->insertTimelineOut($this->pc['id'], $this->eleve['id'], 'out') ;
					$this->messages[] = $insertRespons
						?["content"=>"ENREGISTREMENT OK !","result"=>"succes"]
						:["content"=>"ENREGISTREMENT Raté  !","result"=>"succes"];
				}
			}
			if($this->pc && $this->eleve ){
				$html = $this->renderView();
				
				$contents = [
					'CONTENT'=> $html,
					'TITLE'=> 'Page Login'
				];
				$contents['Redirect'] = [
					'url'=> '/out?',
					'refresh'=> CONFIG['REFRESH']['out']
				];
			}
			else {
				$html = $this->renderView();
				
				$contents = [
					'CONTENT'=> $html,
					'TITLE'=> 'Page Login'
				];
			}

			return $contents;
		}

		public function insertTimelineOut($idpc, $ideleves = null, $typeaction = null) { 

			// [02-Feb-2025 14:58:28 UTC] PHP Deprecated:  Required parameter $typeaction follows 
			// optional parameter $ideleves in /home/u701179126/domains/zarbix.com/app/controllers/OutController.php on line 91

			if(($ideleves && $idpc) || (gettype($ideleves)==='null' && $idpc) ) {
				try {
					$birth = date(format: "y-m-d H:i:s");
					$table = $this->tableprefix.'timeline';
					$query = "INSERT INTO ".$table." (idpc, ideleves, typeaction, birth) VALUES (:idpc, :ideleves, :typeaction, :birth)";
					$stmt = $this->pdo->prepare($query);
					$stmt->bindParam(':ideleves', $ideleves, \PDO::PARAM_STR);
					$stmt->bindParam(':idpc', $idpc, \PDO::PARAM_STR);
					$stmt->bindParam(':typeaction', $typeaction, \PDO::PARAM_STR);
					$stmt->bindParam(':birth', $birth, \PDO::PARAM_STR);
					$stmt->execute(); 
					$this->CheckDb->Console->addMsgSESSION([
						"content"=>"Élève {$ideleves} et PC {$idpc}",
						"title"=>'⬅️',
						"class"=>'',
						"birth"=>$birth
					]);
					
					$used = (int)$this->pc['used']+1;
					$this->CheckDb->setPcPosition($ideleves, $idpc, $typeaction, $used, $birth);
					$this->CheckDb->setEleveLastpcid($ideleves, $idpc, $typeaction, $birth);
	
				} catch (\PDOException $e) {
					die("insertTimelineOut: Erreur d'enregistrement des données : " . $e->getMessage());
				} catch (\Exception $e) {
					die("insertTimelineOut: Erreur d'enregistrement des données : " . $e->getMessage());
				}
				return true;
			}
			return false;
		}
	
		// Afficher la vue login avec les erreurs
		private function renderView(): string {
			$html = file_get_contents(filename: CONFIG['APPROOT'].'app/views/out.php');
			$messageeleve = "";
			$messagepc = "";
			$messages = '';			
	
			
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				if($this->eleve){
					$messageeleve .= $this->eleve['barrecode'];
					$html = str_replace('{{msgeleve}}', $this->eleve['prenom']." ".$this->eleve['nom']."<br>", $html);
					$html = str_replace('{{elevebarrecode}}', $this->eleve['barrecode'], $html);
				}
				else {
					$html = str_replace('{{elevebarrecode}}', '', $html);
					$html = str_replace('{{msgeleve}}', '', $html);
				}

				if($this->pc){
					$messagepc .= $this->pc['barrecode'];
					$html = str_replace('{{msgpc}}', $this->pc['barrecode'], $html);
					$html = str_replace('{{pcbarrecode}}', $this->pc['barrecode'], $html);
				}
				else {
					$html = str_replace('{{pcbarrecode}}', '', $html);
					$html = str_replace('{{msgpc}}', '', $html);
				}


				// Ajouter les erreurs
				if (!empty($this->messages)) {
					foreach ($this->messages as $error) {
						$content = $error['content'];
						$result = $error['result'];
						$messages .= '<p class="'.$result.'">' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . "</p>";
					}
					$html = str_replace('{{errors}}', $messages, $html);
				}
				else {
					$html = str_replace('{{errors}}', '', $html);
				}
			}
			else {
				$html = str_replace('{{msgpc}}', '', $html);
				$html = str_replace('{{msgeleve}}', '', $html);
				$html = str_replace('{{errors}}', '', $html);
				$html = str_replace('{{pcbarrecode}}', '', $html);
				$html = str_replace('{{elevebarrecode}}', '', $html);
			}


			return $html;
		}
	}