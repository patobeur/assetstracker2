<?php

namespace app\controllers\glpi;

class GlpiExt
{
	private $CheckDb;
	private $pdfAuth;
	private $conf;
	private $confGlpi;
	private $pdo;
	private $pdoGlpi;
	private $Console;
	private $LocalPcsIds;
	private $LocalElevesIds;
	// private $tablesGlpi = [
	// 		'pc'=>'glpi_computers',
	// 		'eleves'=>'glpi_users',
	// ];
	private $usersRules = [
		"is_active = '1'",
		"entities_id > '1'",
		"user_dn <> ''"
	];
	private $computersRules = [
		"is_deleted <> '1'",
		"states_id = '9'",
		"computertypes_id <> '4'",
		"computertypes_id <> '6'",
	];
	private $usersTitles = [
		"promo" => 'promo',
		"classe" => 'classe',
		"barrecode" => 'barrecode',
		"prenom" => 'prenom',
		"nom" => 'nom',
		"mail" => 'mail',
		"glpi_id" => 'glpi_id',
	];
	private $computersTitles = [
		"barrecode" => 'name',
		"serialnum" => 'serial',
		// "otherserial" => 'otherserial',
		// "contact" => 'contact',
		// "comment" => 'comment',
		"typeasset_id" => 'computermodels_id', //or 'computertypes_id'
		// "c.t._id" => 'computertypes_id',
		// "s._id" => 'states_id',
		// "is_del." => 'is_deleted',
		"glpi_id" => 'id',
		//"birth" => 'date_creation'
	];
	private $LocalEleves;
	private $LocalPcs;
	private $GlpiEleves;
	private $GlpiPcs;
	private $userstheadersAndSql;
	private $pctheadersAndSql;

	public function __construct($CheckDb, $pdfAuth)
	{
		$this->CheckDb = $CheckDb;
		$this->pdfAuth = $pdfAuth;
		// ----------------------------------------
		$this->conf = $this->CheckDb->getConf();
		$this->confGlpi = $this->CheckDb->getConfGlpi();
		$this->pdo = $this->CheckDb->getPdo();
		$this->pdoGlpi = $this->CheckDb->getPdoGlpi();
		$this->Console = $this->CheckDb->Console;
		// ----------------------------------------
		// $this->LocalPcsIds = $LocalPcsIds;
		// $this->LocalElevesIds = $LocalElevesIds;
	}
	public function getTheadersAndCols($titles, $table)
	{
		$cols = [];
		$theaders = "<tr>";
		if ($table === 'eleves') {
			$theaders .= '<th>New</th>';
			foreach ($titles as $key => $value) {
				$theaders .= '<th class="plus">' . $key . "</th>";
				$cols[] = $value;
			}
			$theaders .= '<th class="plus">Check</th>';
		}

		if ($table === 'pc') {
			$theaders .= '<th>New</th>';
			foreach ($titles as $key => $value) {
				// if($key==='states_id') {$addCols = true; }
				$theaders .= '<th class="plus">' . $key . "</th>";
				$cols[] = $value;
			}
			$theaders .= '<th class="plus">Check</th>';
		}
		// barrecode nom prenom promo classe birth mail
		// glpi_id lastpc_id in_date out_date pivot_id


		$theaders .= "</tr>";
		return ["theaders" => $theaders, "cols" => $cols];
	}
	public function getComputersHtmlList()
	{
		$html = file_get_contents(CONFIG['APPROOT'] . 'app/views/glpipc/listesGlpi.php');

		$content = '';


		// $sqlPcQuery = "";
		foreach ($this->GlpiPcs as $item) {

			$content .= "<tr>";
			$new = false;
			$new = !in_array($item['id'], $this->LocalPcsIds);
			$content .= '<td class="check">' . ($new ? 'new' : 'old') . '</td>';

			// id
			// barrecode = $item['name']
			// model = $item['computermodels_id'] // or 'computertypes_id'
			// serialnum = $item['serial']
			// birth = $item['date_creation']
			// etat
			// typeasset_id = $item['computertypes_id']
			// glpi_id = $item['id']

			foreach ($this->computersTitles as $key => $value) {
				$content .= "<td>" . $item[$value] . "</td>";
			}

			if (!$new) {
				$content .= '<td class="check">existe</td>';
			} else {
				// if($sqlPcQuery=="") {$sqlPcQuery = "INSERT INTO ".$this->conf['tableprefix']."pc (barrecode,model,serialnum,typeasset_id,position,glpi_id) VALUES \n";}
				// else {$sqlPcQuery .=",\n";}
				// $sqlPcQuery .= "('".$item['name']."','Admission','".$item['serial']."',".$item['computertypes_id'].",'in','".$item['id']."')";

				$content .= $this->pdfAuth ? '<td class="check"><input type="checkbox" id="item_' . $item['id'] . '" name="item_' . $item['id'] . '" checked /></td>' : '<td class="check">new</td>';
			}
			$content .= "</tr>";
		}
		// if($sqlPcQuery!="") $sqlPcQuery.=";";

		$html = str_replace('{{PAGETITLE}}', 'Liste des Computers', $html);
		$html = str_replace('{{TITLES}}', $this->pctheadersAndSql['theaders'], $html);
		$html = str_replace('{{CONTENT}}', $content, $html);


		$actionss = '<div class="actions"><button>IMPORTER EN LOCAL</button><input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '"></div>';

		$html = str_replace('{{FORMNAME}}', 'IMPORTPC', $html);
		$html = str_replace('{{FORMACTION}}', ' action="exportpc?export"', $html);
		$html = str_replace('{{ACTIONS}}', $actionss, $html);
		$html = str_replace('{{SQL}}', 'vide', $html);

		// $html = str_replace('{{buttons}}', '', $html);

		return ['CONTENT' => $html];
	}
	public function getList($title, $table, $theaders, $rows, $categorie)
	{
		$html = file_get_contents(CONFIG['APPROOT'] . 'app/views/glpipc/listesGlpi.php');

		$content = '';
		foreach ($rows as $item) {
			$content .= "<tr>";
			$new = false;
			if ($table === $this->confGlpi['tableprefix'] . 'users') {

				$textes = "INSERT INTO " . $this->conf['tableprefix'] . "eleves (barrecode, nom, prenom, promo, classe, mail, glpi_id) VALUES" . PHP_EOL;

				// "INSERT INTO ".$table_prefix."eleves (barrecode, nom, prenom, promo, classe, mail, glpi_id) VALUES
				// 					('00000001', 'Doe', 'John', '2426', 'BTSCOM', 'john.doe@example.com', '1'),

				$new = !in_array($item['id'], $this->LocalElevesIds);

				foreach ($item as $key => $value) {
					if ($key === 'user_dn' && $value != '') {
						$content .= '<td class="check">' . ($new ? 'new' : 'old') . '</td>';

						// $content .= "<td>".($value??'<em class="null">null</em>')."</td>";
						$string = explode(",", $value);
						$paquet = substr($string[1], 3);
						$classe = substr($paquet, 0, -4);

						$classe = str_replace("BTS", "", $classe);
						$promo = substr($paquet, -4);

						$content .= "<td>" . $promo . "</td>";
						$content .= "<td>" . $classe . "</td>";
						$content .= '<td>?</td>';
						$content .= '<td>' . $item['firstname'] . '</td>';
						$content .= '<td>' . $item['realname'] . '</td>';
						$content .= '<td>null</td>';
						$content .= '<td>' . $item['id'] . '</td>';
					} else {
						// $content .= "<td>".($value??'<em class="null">null</em>')."</td>";
					}
				}
				$mail = "??";
				$textes .= "('" . $item['id'] . ")" . PHP_EOL;
				// $textes .= "('?????????????','".$this->GlpiEleves[$item['id']]['realname']."','".$this->GlpiEleves[$item['id']]['firstname']."', '".$promo."', '".$classe."', '".$mail."', '".$item['id']."'),".PHP_EOL;

				if (!$new) {
					$content .= '<td class="check">existe</td>';
				} else {
					if ($this->pdfAuth) {
						$content .= '<td class="check"><input type="checkbox" id="item_' . $item['id'] . '" name="item_' . $item['id'] . '" checked /></td>';
					} else {
						$content .= '<td class="check">new</td>';
					}
				}
			}


			if ($table === $this->confGlpi['tableprefix'] . 'computers') {
				$new = !in_array($item['id'], $this->LocalPcsIds);

				$content .= '<td class="check">' . ($new ? 'new' : 'old') . '</td>';

				foreach ($this->computersTitles as $key => $value) {
					$content .= "<td>" . $item[$value] . "</td>";
				}



				if (!$new) {
					$content .= '<td class="check">existe</td>';
				} else {
					if ($this->pdfAuth) {
						$content .= '<td class="check"><input type="checkbox" id="item_' . $item['id'] . '" name="item_' . $item['id'] . '" checked /></td>';
					} else {
						$content .= '<td class="check">new</td>';
					}
				}
			}


			$content .= "</tr>";
		}
		$html = str_replace('{{PAGETITLE}}', $title, $html);
		$html = str_replace('{{TITLES}}', $theaders, $html);
		$html = str_replace('{{CONTENT}}', $content, $html);


		$actionss = '<div class="actions"><input name="IMPORTPC" type="submit" value="IMPORTER"></div><input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
		if ($categorie === 'pc') {
			$html = str_replace('{{FORMNAME}}', 'IMPORTPC', $html);
			$html = str_replace('{{FORMACTION}}', ' action="glpipc?importpc"', $html);
			$html = str_replace('{{ACTIONS}}', $actionss, $html);
		} elseif ($categorie === 'eleve') {
			$html = str_replace('{{FORMNAME}}', 'IMPORTUSER', $html);
			$html = str_replace('{{FORMACTION}}', ' action="glpipc?importeleve"', $html);
			$html = str_replace('{{ACTIONS}}', $actionss, $html);
		} else {
			$html = str_replace('{{FORMNAME}}', '', $html);
			$html = str_replace('{{FORMACTION}}', '', $html);
			$html = str_replace('{{ACTIONS}}', '', $html);
		}
		// $html = str_replace('{{buttons}}', '', $html);

			
		return ['CONTENT' => $html];
		// return ['CONTENT' => $html,'Redirect' => [
		// 	'url'=> '/export',
		// 	'refresh'=> CONFIG['REFRESH']['in']
		// ]];
	}
	public function getRowsFromSource($source = 'local', $table = null, $cols = null, $wheres = null, $orders = null): array
	{


		$pdo = ($source === 'glpi') ? $this->pdoGlpi : $this->pdo;
		$respons = [];

		if ($table && $cols) {
			try {
				$where = "";
				$order = "";
				$rows = [];
				if ($wheres && gettype($wheres) === 'array') {
					foreach ($wheres as $value) {
						if ($where === "") {
							$where = " WHERE " . $value;
						} else {
							$where .= " AND " . $value;
						}
					}
				}
				if ($orders && gettype($orders) === 'array') {
					foreach ($orders as $value) {
						if ($order === "") {
							$order = " ORDER BY " . $value;
						} else {
							$order .= ", " . $value;
						}
					}
				}
				if ($table) {
					$query = "SELECT {$cols} FROM {$table}{$where}{$order}";
					$stmt = $pdo->prepare($query);
					$stmt->execute();
					$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
				}
				return $rows;
			} catch (\PDOException $e) {
				die("Erreur de connexion à la base de données : " . $e->getMessage());
			} catch (\Exception $e) {
				die("Erreur de connexion à la base de données : " . $e->getMessage());
			}
		}

		return $respons;
	}
	public function setPcDatas()
	{
		$this->LocalPcs = $this->getRowsFromSource('local', $this->conf['tableprefix'] . 'pc', '*', ["glpi_id<>'null'"], ['id']);
		$this->GlpiPcs = $this->getRowsFromSource('glpi', $this->confGlpi['tableprefix'] . 'computers', '*', $this->computersRules, ['id']);
	}
	public function setUserDatas()
	{
		// appel bdd pour prendre les données des eleves existant en local
		$this->LocalEleves = $this->getRowsFromSource('local', $this->conf['tableprefix'] . 'eleves', '*', ["glpi_id<>'null'"], ['id']);
		// appel bdd pour prendre les données des eleves existant dans glpi
		$this->GlpiEleves = $this->getRowsFromSource('glpi', $this->confGlpi['tableprefix'] . 'users', '*', $this->usersRules, ['id']);
	}
	public function setPcHeaders()
	{
		// creation des entêtes pour les tableaux
		$this->pctheadersAndSql = $this->getTheadersAndCols($this->computersTitles, 'pc');
	}
	public function setUserHeaders()
	{
		// creation des entêtes pour les tableaux
		$this->userstheadersAndSql = $this->getTheadersAndCols($this->usersTitles, 'eleves');
	}
	public function setPcIds()
	{
		// on garde uniquement le pc qui n'ont pas deja un 'glpi_id' existant en local
		// on vire tout ce qui est vide ou null
		$this->LocalPcsIds = array_unique(
			array_filter(
				array_column($this->LocalPcs, 'glpi_id'),
				fn($value) => !is_null($value) && $value !== ''
			)
		);
		// $this->LocalPcsIds = array_values($this->LocalPcsIds);// Réindexation du tableaux
	}
	public function setUserIds()
	{
		// creation d'array avec les glpi_id locaux non vide et non null
		$this->LocalElevesIds = array_unique(
			array_filter(
				array_column($this->LocalEleves, 'glpi_id'), // Extraction des valeurs
				fn($value) => !is_null($value) && $value !== '' // Filtre pour exclure les vides et null
			)
		);
		$this->LocalElevesIds = array_values($this->LocalElevesIds); // Réindexation du tableaux
	}
	public function getPcHtmlTables()
	{
		if ($this->pdoGlpi) {
			if ($this->pdo) {

				$this->setPcDatas();
				$this->setPcHeaders();
				$this->setPcIds();

				// $pcCols = implode(",", $this->pctheadersAndSql['cols']);
				// $pctheaders = $this->pctheadersAndSql['theaders'];

				$this->Console->addMsg(["content" => count($this->LocalPcs) . ' pc trouvés en LOCAL', "title" => 'ℹ️', "class" => 'info', "birth" => date("h:i:s")]);
				$this->Console->addMsg(["content" => count($this->GlpiPcs) . ' pc trouvés dans GLPI', "title" => 'ℹ️', "class" => 'info', "birth" => date("h:i:s")]);

				return $this->getComputersHtmlList();
			}
			return [
				['CONTENT' => 'bdd'],
				['TITLES' => ' local ?']
			];
		}
		return [
			['CONTENT' => 'bdd'],
			['TITLES' => ' no glpi ?']
		];

		
	}
	public function getUserHtmlTables()
	{
		if ($this->pdoGlpi) {
			if ($this->pdo) {


				$this->setUserDatas();
				$this->setUserIds();
				$this->setUserHeaders();



				$usersCols = implode(",", $this->userstheadersAndSql['cols']);
				$userstheaders = $this->userstheadersAndSql['theaders'];
				$this->Console->addMsg(["content" => count($this->LocalEleves) . ' eleves trouvés en LOCAL', "title" => 'ℹ️', "class" => 'info', "birth" => date("h:i:s")]);
				$this->Console->addMsg(["content" => count($this->GlpiEleves) . ' eleves trouvés dans GLPI', "title" => 'ℹ️', "class" => 'info', "birth" => date("h:i:s")]);


				$glpiElevesHtml = $this->getList('Liste des users', $this->confGlpi['tableprefix'] . 'users', $userstheaders, $this->GlpiEleves, 'eleve');

				return [$glpiElevesHtml];
			}
			return [
				['CONTENT' => 'bdd'],
				['CONTENT' => ' local ?']
			];
		}
		return [
			['CONTENT' => 'bdd'],
			['CONTENT' => ' glpi ?']
		];
	}

	public function insertComputersNews()
	{
		if ($this->pdoGlpi) {
			if ($this->pdo) {
				if (isset($_POST) && isset($_GET['export'])) {
					
					if (isset($_POST['csrf_token'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
						// OK, traitement du formulaire
						$this->setPcDatas();
						$this->setPcIds();
						$sqlPcQuery = '';
						$allIds = [];
						foreach ($_POST as $key => $value) {
							if (preg_match('/^item_(\d+)$/', $key, $matches)) {
								$allIds[] = (int) $matches[1];
							}
						}
						foreach ($this->GlpiPcs as $item) {
							$new = !in_array($item['id'], $this->LocalPcsIds);
							$requested = in_array($item['id'], $allIds);
							if ($new and $requested) {
								if ($sqlPcQuery == "") {
									$sqlPcQuery = "INSERT INTO " . $this->conf['tableprefix'] . "pc (barrecode,model,serialnum,typeasset_id,position,glpi_id) VALUES";
								} else {
									$sqlPcQuery .= ",";
								}
								$sqlPcQuery .= "('" . $item['name'] . "','Admission','" . $item['serial'] . "'," . $item['computertypes_id'] . ",'in','" . $item['id'] . "')";
							}
						}
						if ($sqlPcQuery != "") {
							$sqlPcQuery .= ";";
							try {
								$stmt = $this->pdo->prepare($sqlPcQuery);
								$stmt->execute();
								unset($_SESSION['csrf_token']);
							} catch (\Throwable $th) {
								print_r($th);
								exit;
							}
							return $sqlPcQuery;
						}
					} else {
						// Erreur CSRF
						http_response_code(403);
						echo "Requête invalide.";
						header("Location: /exportpc");
						exit;
					}
				}
			}
		}
		return false;
	}










	// public function handlePOST_USERS()
	// {



	// 	print_r("<br>");
	// 	print_r("<br>");
	// 	print_r("<br>");
	// 	print_r("<br>");
	// 	print_r("<br>");
	// 	print_r("<br>");
	// 	var_dump($this->GlpiEleves);
	// 	print_r("<br>");
	// 	print_r("<br>");
	// 	print_r("<br>");
	// 	print_r("<br>");
	// 	print_r("<br>");



	// 	$_SESSION['importPc'] = true;
	// 	$textes = "INSERT INTO " . $this->conf['tableprefix'] . "eleves (barrecode, nom, prenom, promo, classe, mail, glpi_id) VALUES" . PHP_EOL;

	// 	// "INSERT INTO ".$table_prefix."eleves (barrecode, nom, prenom, promo, classe, mail, glpi_id) VALUES
	// 	// 					('00000001', 'Doe', 'John', '2426', 'BTSCOM', 'john.doe@example.com', '1'),

	// 	foreach ($_POST as $key => $value) {
	// 		if (str_contains($key, 'item_')) {
	// 			$currentId = str_replace('item_', '', $key);


	// 			print_r($currentId . "<br>");
	// 			print_r($this->GlpiEleves[$currentId]['user_dn'] . "<br>");
	// 			$user_dn = $this->GlpiEleves[$currentId]['user_dn'];

	// 			// // $content .= "<td>".($value??'<em class="null">null</em>')."</td>";
	// 			$string = explode(",", $this->GlpiEleves[$currentId]['user_dn']);
	// 			$paquet = substr($string[1], 3);
	// 			$classe = substr($paquet, 0, -4);

	// 			$classe = str_replace("BTS", "", $classe);
	// 			$promo = substr($paquet, -4);
	// 			$mail = null;






	// 			$textes .= "('?????????????','" . $this->GlpiEleves[$currentId]['realname'] . "','" . $this->GlpiEleves[$currentId]['firstname'] . "', '" . $promo . "', '" . $classe . "', '" . $mail . "', '" . $currentId . "')," . PHP_EOL;


	// 			$this->CheckDb->Console->addMsg(["content" => $currentId . " " . $value]);
	// 		}
	// 	}
	// 	// print_r($textes);

	// }
}
