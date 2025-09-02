<?php
	namespace app\controllers;
	
	class InController {
		private $pdo;
		private $CheckDb;
		private $eleve = null;
		private $pc = null;
		private $messages = [];
		private $messagepc = '';
		private $lastClientDatas = [];
		private $lastLastTimeline = [];	
		private $lastPcDatas = [];	
		private $tableprefix = '';	
		private $contents = [
			'CONTENT'=> '',
			'TITLE'=> 'Page Login',
			'Redirect'=> false
		];	
	
		public function __construct($CheckDb=false) {
			if($CheckDb){
				$this->CheckDb = $CheckDb;
				$this->pdo = $this->CheckDb->getPdo();
				$this->tableprefix = $this->CheckDb->getConf()['tableprefix'];
			}
		}
		
		// Gérer le traitement de connexion
		public function handle(): array{
			$this->messages = [];
			

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$this->pc = null;

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
							$this->messages[]=["content"=>"BarreCode PC introuvable !","result"=>"error"];
						}
					}
				}

				if($this->pc){


					// qui a réservé ce pc dans la derniere action ?
					$this->lastPcDatas = $this->getLastTimelineBy($this->pc['id'],'out');


					$lpd = $this->lastPcDatas;


					
					if($lpd && count($lpd)> 0){
						$this->messagepc .= "<div>Pc N°".$lpd[0]['idpc']." loué le: ".$lpd[0]['birth']."</div>";
						$this->messagepc .= "<div>Model:".$this->pc['model']."</div>";
						$this->messagepc .= "<div>état: ".$this->pc['etat']."</div>";
						$this->messagepc .= "<div>Par ideleve: ".$lpd[0]['ideleves']."</div>";

						// qui était locataire by id
						$this->lastClientDatas = $this->getClientById($lpd[0]['ideleves']) ;
						$lcd = $this->lastClientDatas;
						if($lpd && count($lpd)> 0){
							$this->messagepc .= "<div>lastClientDatas: ".$lcd[0]['id']."</div> ";
							$this->messagepc .= "<div>nom prenom: ".$lcd[0]['nom']." ".$lcd[0]['prenom']."</div>";
							$this->messagepc .= "<div>[".$lcd[0]['classe']."</div> ";
							$this->messagepc .= "<div>".$lcd[0]['promo']."]</div>";
							$this->messagepc .= '<img src="/vendor/feunico/svg/profile.svg" style="width:100px">';
						}

						
						// renregistrement dans Timeline
						$insertRespons = $this->insertTimeline($this->pc['id'],$lpd[0]['ideleves'], 'in',$this->pc,$lcd[0]);
					}
					else {
						$insertRespons = $this->insertTimeline($this->pc['id'],null, 'in',$this->pc);
					}


					$this->messages[] = $insertRespons
						? ["content"=>"ENREGISTREMENT OK !","result"=>"succes"]
						: ["content"=>"ENREGISTREMENT Raté  !","result"=>"alerte"];

					$id = $last[0]['id'] ?? '';				
					$this->contents['Redirect'] = [
						'url'=> '/in?last='.$id,
						'refresh'=> CONFIG['REFRESH']['in']
					];
				}
			}

			$html = $this->renderView();
			
			$this->contents = [
				'CONTENT'=> $html,
				'TITLE'=> 'Page Login',
				'Redirect'=> $this->contents['Redirect'] ?? false,
			];
			
			return $this->contents;
		}
	
		/**
		 * Fonction pour avoir une seule réponse
		 */
		public function getClientById($eleveId=false): array{
			$respons = [];
			if($eleveId){
				try {
					$table = $this->tableprefix.'eleves';
					$query = "SELECT * FROM ".$table." WHERE id = :id LIMIT 1";
					$stmt = $this->pdo->prepare($query);
					$stmt->bindParam(':id', $eleveId, \PDO::PARAM_INT);
					$stmt->execute();
					$respons = $stmt->fetchAll(\PDO::FETCH_ASSOC);
				} catch (\PDOException $e) {
					die("Erreur de connexion ou de création de la base de données : " . $e->getMessage());
				} catch (\Exception $e) {
					die("Erreur : " . $e->getMessage());
				}
			}
			return $respons;
		}
		/**
		 * Fonction pour avoir une seule réponse
		 * 
		 */
		public function getLastTimelineBy($idpc=false,$typeaction=false): array{
			$respons = [];
			if($idpc && $typeaction){
				try {
					$table = $this->tableprefix.'timeline';
					$query = "SELECT * FROM ".$table." WHERE idpc=:idpc AND typeaction=:typeaction ORDER BY id DESC LIMIT 1";
					$stmt = $this->pdo->prepare($query);
					$stmt->bindParam(':idpc', $idpc, \PDO::PARAM_INT);
					$stmt->bindParam(':typeaction', $typeaction, \PDO::PARAM_STR);
					$stmt->execute();
					$respons = $stmt->fetchAll(\PDO::FETCH_ASSOC);
				} catch (\PDOException $e) {
					die("Erreur de connexion ou de création de la base de données : " . $e->getMessage());
				} catch (\Exception $e) {
					die("Erreur : " . $e->getMessage());
				}
			}
			return $respons;
		}
		/**
		 * Fonction pour avoir la dernière Timeline
		 */
		public function getLastTimeLine($table=null,$cols=null): array{ 
			$last = [];
			if($table && $cols){
				try {
					if($table){
						$table = $this->tableprefix.$table;
						$query = "SELECT {$cols} FROM {$table} ORDER by id DESC LIMIT 1";
						$stmt = $this->pdo->prepare($query);
						$stmt->execute();
						$last = $stmt->fetchAll(\PDO::FETCH_ASSOC);
					}
					return $last;
				} catch (\PDOException $e) {
					die("Erreur de connexion à la base de données : " . $e->getMessage());
				} catch (\Exception $e) {
					die("Erreur de connexion à la base de données : " . $e->getMessage());
				}
			}
			return $last;
		}		
		public function insertTimeline($idpc, $ideleves, $typeaction, $pc=false, $eleve=false) { 
			
			$idpc = ($pc && $pc['id']) ? $pc['id'] : false;			
			$ideleves = ($eleve && $eleve['id']) ? $eleve['id'] : null;
			if(($ideleves && $idpc) || ($ideleves===null && $idpc) ) {
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
						"content"=>($typeaction==='in')?"{$eleve['prenom']} {$eleve['nom']} {$eleve['classe']}{$eleve['promo']} rend PC {$pc['id']}{$pc['barrecode']}":"Élève {$ideleves} emprunte PC {$idpc}",
						"title"=>($typeaction==='in')?'➡️':'⬅️',
						"class"=>'',
						"birth"=>$birth
					]);
					
					$used = ($typeaction==='out') ? (int)$this->pc['used']+1 : false;

					$this->CheckDb->setPcPosition($ideleves, $idpc, $typeaction, $used, $birth);
					$this->CheckDb->setEleveLastpcid($ideleves, $idpc, $typeaction, $birth);
	
				} catch (\PDOException $e) {
					die("Erreur d'enregistrement des données : " . $e->getMessage());
				} catch (\Exception $e) {
					die("Erreur d'enregistrement des données : " . $e->getMessage());
				}
				return true;
			}
			return false;
		}

		// Afficher la vue login avec les erreurs
		private function renderView(): string {
			$html = file_get_contents(filename: CONFIG['APPROOT'].'app/views/in.php');
			$messageeleve = "";



			$messages = '';			
	
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
			
			if ($_SERVER['REQUEST_METHOD'] === 'POST') {

				if($this->pc){
					$html = str_replace('{{msgpc}}', $this->messagepc, $html);
					$html = str_replace('{{pcbarrecode}}', $this->pc['barrecode'], $html);
				}
				else {
					$html = str_replace('{{pcbarrecode}}', '', $html);
					$html = str_replace('{{msgpc}}', '', $html);
				}


			}
			else {
				$html = str_replace('{{msgpc}}', '', $html);

				


				// $html = str_replace('{{errors}}', '', $html);
				$html = str_replace('{{pcbarrecode}}', '', $html);
			}


			return $html;
		}
	}