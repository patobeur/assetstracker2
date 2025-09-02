<?php
	namespace app\controllers;	

	class ListingController {
		private $CheckDb;
		private $fpdfPath = CONFIG['APPROOT'].'app/vendor/fpdf/fpdf.php';
		private $pdf;
		private $pdo;
		private $pdfAuth;
		private $tableprefix = '';	
	
		public function __construct($CheckDb,$pdf) {
			$this->pdfAuth = (isset($_SESSION['user']) && isset($_SESSION['user']['typeaccount_id']) && (int)$_SESSION['user']['typeaccount_id']>=3 );
			$this->CheckDb = $CheckDb;
			$this->pdo = $CheckDb->getPdo();
			$this->pdf = $pdf;
			$this->tableprefix = $this->CheckDb->getConf()['tableprefix'];
		}
		
		/**
		 * Fonction Lire listPc
		*/
		public function listPc() {
			$formatPdf = (isset($_POST['print']) );
			$sqlList = [];
			if($formatPdf){
				$titles = [
					"ID" => 'id',
					"barrecode" => 'barrecode',
					"Modèle" => 'model',
					"Numéro de Série" => 'serialnum',
					"État" => 'etat',
					"Entrée" => 'birth',
					"Position" => 'position',
					"Last" => 'lasteleve_id',
				];
				
				foreach ($titles as $key => $value) {
					$sqlList[] = $value;
				}
			}
			else {
				$html = file_get_contents(CONFIG['APPROOT'].'app/views/listes.php');
				$titles = [
					"ID" => 'id',
					"barrecode" => 'barrecode',
					"Modèle" => 'model',
					// "Numéro de Série" => 'serialnum',
					"État" => 'etat',
					"Entrée" => 'birth',
					"Position" => 'position',
					"Last" => 'lasteleve_id',
				];
				$theaders = "<tr>";
				foreach ($titles as $key => $value) {
					$theaders .= "<th>".$key."</th>";
					$sqlList[] = $value;
				}
				if ($this->pdfAuth) $theaders .= '<th>Check</th>';
				$theaders .= "</tr>";
			}
			
			$items = $this->getRowsFrom('pc',implode(",", $sqlList));

			if($formatPdf){
				$this->displayPdf($items);
			}
			else {
				$content = '';
				foreach ($items as $item) {
						$content .= '<tr id="row_'.$item['id'].'">';
						foreach ($item as $value) {
							$content .= "<td>".($value??'<em class="null">null</em>')."</td>";
						}
						if ($this->pdfAuth) $content .= '<td class="check"><input type="checkbox" id="item_'.$item['id'].'" name="item_'.$item['id'].'" checked /></td>';
					$content .= "</tr>";
				}
				
				$html = str_replace('#PAGETITLE#', 'Liste des Pc', $html);
				$html = str_replace('{{TITLES}}', $theaders, $html);
				$html = str_replace('{{CONTENT}}', $content, $html);
				$html = str_replace('{{FORMACTION}}', $this->pdfAuth?' target="_blank" action="listpc"':'', $html);
				$html = str_replace('{{buttons}}', '<input type="submit" value="Pdf Barrecode">', $html);
				
				return [
					'CONTENT'=> $html,
					'TITLE'=> 'Pc list'
				];
			}

		}
		private function displayPdf($items=[]){
			if($items && count($items)> 0){
				if(isset($_SESSION['user']) && isset($_SESSION['user']['typeaccount_id']) && (int)$_SESSION['user']['typeaccount_id']>=3 ){
					$x = 40;
					$y = 10;
					$this->pdf->AddPage();
					$this->pdf->SetFont('Arial','B',16);
					$this->pdf->Cell(40,10,'Assets-Time-Tracker !');
					
					$this->pdf->SetFont('Arial','',10);
					foreach ($items as $item) {
						if(isset($_POST['item_'.$item['id']]) ){
							$y+=5;
							$this->pdf->SetY($y,true);
							$this->pdf->Cell(20,10,'id:'.$item['id']);
							$this->pdf->Cell(100,10,'barrecode:'.$item['barrecode']);
							// $this->pdf->Cell(10,10,'item_'.$item['id'].':'.$_POST['item_'.$item['id']]);

						}
					}
					$this->pdf->Output();
				}
			}
			die();			
		}
		/**
		 * Fonction Lire listEleves
		*/
		public function listEleves() {
			$html = file_get_contents(CONFIG['APPROOT'].'app/views/listes.php');
			// id 	barrecode 	nom 	prenom 	promo 	classe 	birth 	mail 	lastpc_id 	
			$sqlList = [];
			$titles = [
				"ID" => 'id',
				"barrecode" => 'barrecode',
				"nom" => 'nom',
				"prenom" => 'prenom',
				"classe" => 'classe',
				"promo" => 'promo',
				"birth" => 'birth',
				"glpi_id" => 'glpi_id',
				// "mail" => 'mail',
				"lastpc_id" => 'lastpc_id'
			];

			$theaders = "<tr>";
			$theaders .= "<th>Check</th>";
			foreach ($titles as $key => $value) {
				$theaders .= "<th>".$key."</th>";
				$sqlList[] = $value;
			}
			$theaders .= "<th>Action</th>";
			$theaders .= "</tr>";

			$items = $this->getRowsFrom('eleves',implode(",", $sqlList));

			$content = '';			
			foreach ($items as $item) {
					$content .= "<tr>";
					$content .= '<td><input type="checkbox" id="item_'.$item['id'].'" name="item_'.$item['id'].'" /></td>';
					foreach ($item as $value) {
						$content .= "<td>".($value??'<em class="null">null</em>')."</td>";
					}
					$content .= '<td><a href="/eleve?num='.$item['id'].'">ref: '.$item['id'].'</a></td>';
				$content .= "</tr>";
			}
			
            $html = str_replace('#PAGETITLE#', 'Liste des Élèves', $html);
            $html = str_replace('{{TITLES}}', $theaders, $html);
            $html = str_replace('{{CONTENT}}', $content, $html);
			$html = str_replace('{{FORMACTION}}', $this->pdfAuth?' target="_blank" action="listeleve"':'', $html);
			$html = str_replace('{{buttons}}', '', $html);
			
			return [
				'CONTENT'=> $html,
				'TITLE'=> 'Élèves list'
			];
		}

		/**
		 * Fonction Lire listTimeline
		*/
		public function listTimeline() {
			$html = file_get_contents(CONFIG['APPROOT'].'app/views/listes.php');
			$content = '';
			$sqlList = [];
			$titles = [
				"ID" => 'id',
				"idpc" => 'idpc',
				"ideleves" => 'ideleves',
				"typeaction" => 'typeaction',
				"Date" => 'birth',
			];
			$theaders = "<tr>";
			foreach ($titles as $key => $value) {
				$theaders .= "<th>".$key."</th>";
				$sqlList[] = $value;
			}
			$theaders .= "</tr>";

			$items = $this->getRowsFrom('timeline',implode(",", $sqlList));

			foreach ($items as $item) {
					$content .= "<tr>";
					foreach ($item as $value) {
						$content .= "<td>".$value."</td>";
					}
				$content .= "</tr>";
			}
			
            $html = str_replace('#PAGETITLE#', 'Timeline', $html);
            $html = str_replace('{{TITLES}}', $theaders, $html);
            $html = str_replace('{{CONTENT}}', $content, $html);
			$html = str_replace('{{FORMACTION}}', $this->pdfAuth?' target="_blank" action="timeline"':'', $html);
			$html = str_replace('{{buttons}}', '', $html);
			
			return [
				'CONTENT'=> $html,
				'TITLE'=> 'Timeline'
			];
		}

		// BDD
		
		/**
		 * Fonction pour lire certaines colonnes d'une table
		 */
		private function getRowsFrom($table=null,$cols=null): array{ 
			$respons = [];
			if($table && $cols){
				try {
					$pcs = []; 
					if($table){
						$table = $this->tableprefix.$table;
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
