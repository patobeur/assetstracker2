<?php
	namespace app\controllers;
	use app\controllers\glpi\GlpiExt;
	class GlpiController {
		private $lvAuth = 6; // niveau necéssaire
		private $pdfAuth;
		private $CheckDb;
		private $GlpiExt;
		private $pdoGlpi;
		private $content = 	[
			'TITLE' => "GLPI",
			'CONTENT'   => "<h2>Réservé aux Admin.</h2>",
		];

		public function __construct($CheckDb) {
			// accréditation
			$this->pdfAuth = (isset($_SESSION['user']) && isset($_SESSION['user']['typeaccount_id']) && (int)$_SESSION['user']['typeaccount_id']>=$this->lvAuth );
			
			if($this->pdfAuth){
				$this->CheckDb = $CheckDb;
	
				$this->GlpiExt = new GlpiExt(
					$this->CheckDb,
					$this->pdfAuth
				);
			}

		}
		
		public function csrf_token() {
			if (!isset($_SESSION['csrf_token'])) {
				$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
			}
		}
		
		public function exportpc() {
			if($this->pdfAuth){
				if(isset($_POST) && isset($_GET['export'])){
					$this->csrf_token();
					$newss = $this->GlpiExt->insertComputersNews();
				}
				$this->csrf_token();
				$contents = $this->GlpiExt->getPcHtmlTables();
				$this->content['CONTENT'] = $contents['CONTENT'];
			}
			$this->renderView();
			return $this->content;
		}

		public function exportuser() {
			if($this->pdfAuth){
				$contents = $this->GlpiExt->getUserHtmlTables();
				$this->content['CONTENT'] = $contents[0]['CONTENT'];
			}
			$this->renderView();
			return $this->content;
		}		
		
		private function renderView(){
			$htmlView = file_get_contents(filename: CONFIG['APPROOT'].'app/views/glpipc.php');
			$htmlView = str_replace('{{TITLE}}', $this->content['TITLE'], $htmlView);
			$htmlView = str_replace('{{CONTENT}}', $this->content['CONTENT'], $htmlView);

			$this->content['CONTENT'] = $htmlView;
		}		
	}
