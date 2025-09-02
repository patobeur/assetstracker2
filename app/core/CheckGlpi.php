<?php
namespace app\core;

class CheckGlpi
{
	private $Console;
	private $conf;
	private $CheckDb;
	private $pdoGlpi;

	
	public function __construct($CheckDb,$Console) {
		$this->CheckDb = $CheckDb;
		$this->Console = $Console;
		$this->conf = $this->CheckDb->getConfGlpi();
		$this->checkGlpiDb();
	}
	private function checkGlpiDb() {
		$dberror = [];	
		if (!empty($this->conf['dbHost']) && !empty($this->conf['dbName']) && !empty($this->conf['dbUser']) && isset($this->conf['dbPassword'])) {
			try {
				// Créer une connexion PDO
				$this->pdoGlpi = new \PDO(dsn: "mysql:host=".$this->conf['dbHost'].";dbname=".$this->conf['dbName'].";charset=utf8", username: $this->conf['dbUser'], password: $this->conf['dbPassword']);
				
				// Si la connexion est ok
				if(!$this->pdoGlpi){
					$dberror[]="La Bdd '".$this->conf['dbName']."' n'existe pas.";
					$this->CheckDb->Console->addMsg([
						"content"=>"Glpi n'existe pas.",
						"title"=>'ℹ️',
						"class"=>'info',
						"birth"=>date("h:i:s")
					]);

				}
			} catch (\PDOException $e) {
				$dberror[]="Erreur lors de la connexion à la base de données Glpi: " . $e->getMessage();
				$this->CheckDb->Console->addMsg([
					"content"=>"La connection à Glpi est introuvable.",
					"title"=>'ℹ️',
					"class"=>'info',
					"birth"=>date("h:i:s")
				]);
				// header('Location: /');
			}
		} else {
			$dberror[]="Les paramètres de connexion à la base de données '".$this->conf['dbName']."' sont incomplets.";
			// $this->CheckDb->Console->addMsg([
			// 	"content"=>"Les paramètres de connexion à Glpi sont incomplets.",
			// 	"title"=>'⚠️',
			// 	"class"=>'alerte',
			// 	"birth"=>date("h:i:s")
			// ]);
			// header('Location: /');
		}
		$this->setPdoGlpi($dberror);
	}
	private function setPdoGlpi($dberror=[]) {
		if(count($dberror) < 1){
			$this->CheckDb->setPdoGlpi($this->pdoGlpi);
		}
	}
}