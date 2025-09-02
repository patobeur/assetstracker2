<?php
namespace app\core;

class CheckUser
{
	private $CheckDb = null;
	private $pdo = null;
	private $Console = null;

	private $CheckInstall;
	private $tableprefix;

	public function __construct($CheckDb, $Console) {
		$this->CheckDb = $CheckDb;
		$this->Console = $Console;
		$this->pdo = $this->CheckDb->getPdo();
		$this->tableprefix = $this->CheckDb->getConf()['tableprefix'];
		$this->checkUserCase();
	}
	private function checkUserCase() {
		$existe = false;
		if(isset($_SESSION['user']) && $_SESSION['user']['id'] && $_SESSION['user']['sessionkey']){
			$result = $this->checkSessionIsSame();
			$existe = count($result)===1;
			if(!$existe) unset($_SESSION['user']);
		}
	}
	
	/**
	 * Fonction pour vérifier la sessionkey
	 */
	private function checkSessionIsSame($eleveId=false): array{
		$respons = [];
		$id = $_SESSION['user']['id'];
		$sessionkey = $_SESSION['user']['sessionkey'];
		if($id && $sessionkey){
			try {
				$table = $this->tableprefix."administrateurs";
				$query = "SELECT * FROM ".$table ." WHERE id = :id AND sessionkey = :sessionkey  LIMIT 1";
				$stmt = $this->pdo->prepare($query);
				$stmt->bindParam(':id', $id, \PDO::PARAM_INT);
				$stmt->bindParam(':sessionkey', $sessionkey, \PDO::PARAM_STR);
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
}