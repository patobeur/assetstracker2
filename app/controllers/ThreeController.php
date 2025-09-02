<?php
	namespace app\controllers;
	
	class ThreeController {
		private $CheckDb;
		private $pdo;
		private $pcs = [];
		private $eleves = [];
		private $timeline = [];
		private $pcsJson = '{}';
		private $elevesJson = '{}';
		private $timelineJson = '{}';
		private $tableprefix;


		private $content = [
				'TITLE' => "Three",
				'CONTENT'   => 'ok',
				'Redirect'   => [
					'url'=> false,
					'refresh'=> false
				],
		];

		public function __construct($CheckDb) {
			$this->CheckDb = $CheckDb;
			$this->pdo = $this->CheckDb->getPdo();
			$this->tableprefix = $this->CheckDb->getConf()['tableprefix'];
		}
        
		public function go()
		{	
			$this->setPcsJson();
			$this->setTimelineJson();
			$this->setElevesJson();
			$this->renderView();
			return $this->content;
		}
		
		private function setPcsJson(){
			$this->pcs = $this->list($this->tableprefix.'pc','*');
			if($this->pcs && count($this->pcs)>0) $this->pcsJson = json_encode($this->pcs);
		}
		private function setElevesJson(){
			$this->eleves = $this->list($this->tableprefix.'eleves','*');
			if($this->eleves && count($this->eleves)>0) $this->elevesJson = json_encode($this->eleves);
		}
		private function setTimelineJson(){
			$this->timeline = $this->list($this->tableprefix.'timeline','*');
			if($this->timeline && count($this->timeline)>0) $this->timelineJson = json_encode($this->timeline);
		}
		
		private function renderView(){
			$htmlView = file_get_contents(filename: CONFIG['APPROOT'].'app/views/three.php');
			
			// $htmlView = str_replace('{{TITLE}}', $this->content['TITLE'], $htmlView);
			// $htmlView = str_replace('{{CONTENT}}', $this->content['CONTENT'], $htmlView);
			$htmlView = str_replace('{{pcsJson}}', $this->pcsJson, $htmlView);
			$htmlView = str_replace('{{timelineJson}}', $this->timelineJson, $htmlView);
			$htmlView = str_replace('{{elevesJson}}', $this->elevesJson, $htmlView);

			$this->content['CONTENT'] = $htmlView;

		}
		// BDD
		
		/**
		 * Fonction pour avoir la liste des Pc
		 */
		private function list($table=null,$cols=null): array{ 
			$respons = [];
			if($table && $cols){
				try {
					$pcs = []; 
					if($table){
						$query = "SELECT {$cols} FROM {$table} ORDER by birth DESC";
						$stmt = $this->pdo->prepare($query);
						$stmt->execute();
						$pcs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
					}
					return $pcs;
				} catch (\PDOException $e) {
					die("Erreur de connexion à la base de données : " . $e->getMessage());
				} catch (\Exception $e) {
					die("Erreur de connexion à la base de données : " . $e->getMessage());
				}
			}
			return $respons;
		}
		
    }