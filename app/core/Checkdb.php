<?php
namespace app\core;
use app\core\checkdb\CheckInstall;

class CheckDb
{
	private $dbConfigPath = '';
	private $installPath = 'install.php';
	private $pdo;
	private $pdoGlpi;
	public $Console;
	public $CheckInstall;

	private $conf = [
		'dbHost'=> null,
		'dbName'=> null,
		'dbUser'=> null,
		'dbPassword'=> null,
		'tableprefix'=> null
	];
	
	private $confGlpi = [
		'dbHost'=> null,
		'dbName'=> null,
		'dbUser'=> null,
		'dbPassword'=> null,
		'tableprefix'=> null
	];

	public function __construct($Console,$dbConfigPath) {
		$this->Console = $Console;
		$this->dbConfigPath = $dbConfigPath;
		$this->CheckInstall = new CheckInstall(
			CheckDb: $this,
			Console: $this->Console
		);
		if(isset($_SESSION['user']) && isset($_SESSION['user']['typeaccount_id']) ){
			$lv=(int)$_SESSION['user']['typeaccount_id']??(int)0;
			if($lv>=5){
				$this->CheckInstall->addCheckMessage(lv: $lv);
			}
		}
	}

	public function checkIfDb(): array
	{
		require_once $this->dbConfigPath;
		$dberror = [];
		$this->conf = [
			'dbHost'=> $dbHost??null,
			'dbName'=> $dbName??null,
			'dbUser'=> $dbUser??null,
			'dbPassword'=> $dbPassword??null,
			'tableprefix'=> $table_prefix??''
		];
		$this->confGlpi = [
			'dbHost'=> $hostGlpi??null,
			'dbName'=> $dbGlpi??null,
			'dbUser'=> $userGlpi??null,
			'dbPassword'=> $passGlpi??null,
			'tableprefix'=> $table_prefixGlpi??''
		];
		
		if (!empty($this->conf['dbHost']) && !empty($this->conf['dbName']) && !empty($this->conf['dbUser']) && isset($this->conf['dbPassword'])) {
			try {
				// Créer une connexion PDO
				$this->pdo = new \PDO(dsn: "mysql:host=".$this->conf['dbHost'].";dbname=".$this->conf['dbName'].";charset=utf8", username: $this->conf['dbUser'], password: $this->conf['dbPassword']);

				// Si la connexion est ok
				if($this->pdo){
					(CONFIG['PROD'])
						? $this->pdo->setAttribute(attribute: \PDO::ATTR_ERRMODE, value: \PDO::ERRMODE_SILENT)
						: $this->pdo->setAttribute(attribute: \PDO::ATTR_ERRMODE, value: \PDO::ERRMODE_EXCEPTION);

					// Vérifier si la table assetstracker existe
					$query = $this->pdo->query("SHOW TABLES LIKE '".$this->conf['tableprefix']."administrateurs'");

					if ($query->rowCount() < 1) {
						$dberror[]="La table '".$this->conf['tableprefix']."administrateurs' n'existe pas dans la base de données.";
						// header('Location: /');
					}
					else {
						// all good
					}
				}
				else {
					die("no bdd");
				}
			} catch (\PDOException $e) {
				$dberror[]="Erreur lors de la connexion à la base de données : " . $e->getMessage();
				$this->Console->addMsg([
					"content"=>"Erreur lors de la connexion à la base de données",
					"title"=>'⚠️',
					"class"=>'alerte',
					"birth"=>date("h:i:s")
				]);
				// header('Location: /');
			}
		} else {
			$dberror[]="Les paramètres de connexion à la base de données sont incomplets.";
			// header('Location: /');
		}
		return $dberror;
	}

	/**
	 * Fonction pour avoir une seule réponse
	 */
	public function once($table=false, $barrecode=false): array{
		$respons = [];
		if($table && $barrecode){
			$table = $this->conf['tableprefix'].$table;
			try {
				$query = "SELECT * FROM {$table} WHERE barrecode = :barrecode LIMIT 1";
				$stmt = $this->pdo->prepare($query);
				$stmt->bindParam(':barrecode', $barrecode, \PDO::PARAM_STR);
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
	 * Fonction pour mettre la position d'un pc a jour (in ou out)
	 */
	public function setPcPosition($ideleves=false, $idpc=false, $typeaction=false, $used=false, $birth=false): void	{
		if($idpc && ($typeaction ==='in' || $typeaction ==='out')){
			try {
				$ideleves = $ideleves ?? NULL;
				$birth = $birth ?? NULL;
				$used = $used ?? NULL;

				$table = $this->conf['tableprefix'].'pc';
				// if($used && $typeaction && $used>0 && $typeaction="out")
				$sqlTxt = "position = :position, in_date = :in_date";
				if($ideleves) $sqlTxt .= ", lasteleve_id = :lasteleve_id";				
				if($used && $used>0 && $typeaction="out") $sqlTxt .= ", used = :used";
				if($typeaction === 'out') $sqlTxt.=", out_date = :out_date";
				$query = "UPDATE ".$table." SET ".$sqlTxt." WHERE id = :id";

				$stmt = $this->pdo->prepare($query);
				$stmt->bindParam(':position', $typeaction, \PDO::PARAM_STR);
				$stmt->bindParam(':id', $idpc, \PDO::PARAM_STR);

				if($ideleves) {
					$stmt->bindParam(':lasteleve_id', $ideleves, \PDO::PARAM_INT);
				}
				if($used && $used>0 && $typeaction="out") {
					$stmt->bindParam(':used', $used, \PDO::PARAM_INT);
				}

				if($typeaction === 'out') {
					$indate= NULL;
					$stmt->bindParam(':out_date', $birth, \PDO::PARAM_STR);
					$stmt->bindParam(':in_date', $indate, \PDO::PARAM_STR);
				} else {
					$stmt->bindParam(':in_date', $birth, \PDO::PARAM_STR);
				}
				
				$stmt->execute();
			} catch (\PDOException $e) {
				die("setPcPosition PDOException: Erreur d'enregistrement des données : " . $e->getMessage());
			} catch (\Exception $e) {
				die("setPcPosition Exception: Erreur d'enregistrement des données : " . $e->getMessage());
			}
		}
	}

	/**
	 * Fonction pour indiquer le pc emprunté par le client
	 */
	public function setEleveLastpcid($ideleve=false, $idpc=false, $typeaction=false, $birth=false): void {
		if($idpc && $ideleve && $birth){
			try {
				$cols = 'lastpc_id = :lastpc_id, in_date = :in_date';
				if($typeaction === 'out') $cols.=", out_date = :out_date";

				$table = $this->conf['tableprefix'].'eleves';
				$query = "UPDATE ".$table." SET ".$cols." WHERE id = :id";
				$stmt = $this->pdo->prepare($query);
				$stmt->bindParam(':id', $ideleve, \PDO::PARAM_STR);
				$stmt->bindParam(':lastpc_id', $idpc, \PDO::PARAM_STR);
				
				if($typeaction === 'out') {
					$indate= NULL;
					$stmt->bindParam(':out_date', $birth, \PDO::PARAM_STR);
					$stmt->bindParam(':in_date', $indate, \PDO::PARAM_STR);
				} else {
					$stmt->bindParam(':in_date', $birth, \PDO::PARAM_STR);
				}

				$stmt->execute();
			} catch (\PDOException $e) {
				die("setEleveLastpcid PDOException: Erreur d'enregistrement des données : " . $e->getMessage());
			} catch (\Exception $e) {
				die("setEleveLastpcid Exception: Erreur d'enregistrement des données : " . $e->getMessage());
			}
		}
	}

	// SETS	
	public function setPdoGlpi($pdoGlpi) {
		$this->pdoGlpi = $pdoGlpi;
	}

	// GETS	
	public function getDbConfigPath(): string {return $this->dbConfigPath;}

	public function getInstallPath(): string {return $this->installPath;}
	public function getConfGlpi(): array {return $this->confGlpi;}
	public function getConf(): array {return $this->conf;}

	public function getPdo(): \PDO {return $this->pdo;}
	public function getPdoGlpi(){return $this->pdoGlpi;}

}