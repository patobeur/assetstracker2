<?php

session_start();

function getRelativePath($adjust = 1)
{

	// Obtenir l'URL actuelle
	$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	// Chemin du script actuel (ex: /rootering/rootering.php)
	$scriptPath = $_SERVER['SCRIPT_NAME'];

	// Racine du serveur, correspondant à DOCUMENT_ROOT (ex: /home/user/domains/site.com/public_html)
	$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));

	// Dossier où se trouve le fichier en cours (ex: /home/user/domains/site.com/public_html/rootering)
	$scriptDir = str_replace('\\', '/', realpath(dirname(__FILE__)));

	// Détection correcte de la racine du site
	$relativePath = str_replace($documentRoot, '', $scriptDir);

	// Nombre de dossiers après la racine
	$relativeDepth = substr_count($relativePath, '/') + $adjust;

	// Générer les "../" nécessaires pour revenir à la racine
	$relativeBackPath = str_repeat("../", $relativeDepth);
	// echo "URL actuelle : $currentUrl<br>";
	// echo "Chemin du script : $scriptPath<br>";
	// echo "Racine serveur : $documentRoot<br>";
	// echo "Dossier du script : $scriptDir<br>";
	// echo "Chemin relatif après la racine du domaine : $relativePath<br>";
	// echo "Profondeur réelle après la racine du domaine : $relativeDepth<br>";
	// echo "Chemin relatif pour accéder à la racine : $relativeBackPath";
	return $relativeBackPath;
}
define('ROOTPATH', getRelativePath(1));

$checkboxProd = false;
$checkboxbddByPass = false;
$checkboxDelTable = false;
$dbConfigPath = ROOTPATH . 'app/conf/dbconfig.php';
$defaultHost = 'localhost';
$defaultUser = '';
$defaultDb = 'assetsTracker';
$table_prefix = "";

$hostGlpi = '';
$dbGlpi = '';
$userGlpi = '';
$passGlpi = '';

$version = '0.6.0.0';
$creation = date('Y-m-d H:i:s');

$defaultAdminPseudo = 'admin';
$defaultAdminPasse = 'admin';
$defaultAdminMail = 'admin@example.com';
$defaultAdminPrenom = 'admin';
$defaultAdminNom = 'admin';
$defaultAdminTypeaccount = 9;

$comptes = [
	[
		'pseudo' => 'mathis',
		'passe' => '$2y$10$TZK1uohk6L92OLWJBTjMzuGWDfVOXDuutka0YAEAleUOQPBzW5oO2',
		'nom' => 'mathis',
		'prenom' => 'mathis',
		'mail' => 'mathis@example.com',
		'typeaccount' => (int)5,
	],
	[
		'pseudo' => 'alicia',
		'passe' => '$2y$10$Tm3enNS6dOrNn3ctFU8J5.szmmYUxrdhlVvJzfNVdZiUvFZICrZZK',
		'nom' => 'alicia',
		'prenom' => 'alicia',
		'mail' => 'alicia@example.com',
		'typeaccount' => (int)8,
	],
	[
		'pseudo' => 'eric',
		'passe' => '$2y$10$YtJKxpdGqvrBlxyZLdM8yOdnrF.Njg2E8OzEsn8HfSEaU2jWZc6w.',
		'nom' => 'eric',
		'prenom' => 'eric',
		'mail' => 'eric@example.com',
		'typeaccount' => (int)9,
	]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$dbHost = trim($_POST['host']) ?? '';
	$dbName = trim($_POST['db']) ?? '';
	$dbUser = trim($_POST['user']) ?? '';
	$dbPassword = $_POST['pass'] ?? '';
	$checkboxDelTable = $_POST['deltable'] ?? false;
	$checkboxbddByPass = $_POST['bddbypass'] ?? false;
	$checkboxProd = $_POST['production'] ?? false;
	$checkboxGlpi = $_POST['glpitrigger'] ?? null;

	// todo à sécuriser
	$table_prefix = ($_POST['prefix'] && $_POST['prefix'] != '') ? $_POST['prefix'] : '';

	if ($checkboxGlpi) {
		$hostGlpi = ($_POST['hostGlpi'] && $_POST['hostGlpi'] != '') ? $_POST['hostGlpi'] : $dbHost;
		$dbGlpi = ($_POST['dbGlpi'] && $_POST['dbGlpi'] != '') ? $_POST['dbGlpi'] : 'glpi';
		$userGlpi = ($_POST['userGlpi'] && $_POST['userGlpi'] != '') ? $_POST['userGlpi'] : $dbUser;
		$passGlpi = ($_POST['passGlpi'] && $_POST['passGlpi'] != '') ? $_POST['passGlpi'] : $dbPassword;
	}

	$defaultAdminPseudo = trim($_POST['adminpseudo'] ?? '');
	$defaultAdminPasse = $_POST['adminpass'] ?? '';

	$dbHost = filter_var($dbHost, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

	if (!filter_var($dbHost, FILTER_VALIDATE_DOMAIN)) {
		die("Hôte invalide");
	}

	if (empty($dbHost) || empty($dbName) || empty($dbUser) || empty($defaultAdminPseudo) || empty($defaultAdminPasse)) {
		$error = "Veuillez remplir tous les champs.";
	} else {
		if (!empty($dbHost) && !empty($dbName) && !empty($dbUser)) {
			try {
				// Créer une connexion PDO
				$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8";
				$pdo = new \PDO($dsn, $dbUser, $dbPassword);
				if (!$checkboxbddByPass) {
					$error = "La base de données '" . $dbName . "' existe déjà: ";
				}
			} catch (\PDOException $e) {
				// c'est normal si n'existe pas !
				// $error = "BErreur : " . $e->getMessage();
			} catch (Exception $e) {
				$error = "Erreur de connexion à la base: " . $e->getMessage();
			}
		}

		if ($checkboxGlpi === 'on' && !empty($hostGlpi) && !empty($dbGlpi) && !empty($userGlpi)) {
			try {
				// Créer une connexion PDO
				$dsnGlpi = "mysql:host=$hostGlpi;dbname=$dbGlpi;charset=utf8";
				$pdoGlpi = new \PDO($dsnGlpi, $userGlpi, $passGlpi);
			} catch (\PDOException $e) {

				$error = "La base de données " . $dbGlpi . " n'existe pas: ";
				$error = "Erreur 1: " . $e->getMessage();
			} catch (Exception $e) {
				$error = "La base de données " . $dbGlpi . " n'existe pas: ";
				$error = "Erreur 2: " . $e->getMessage();
			}
		} else {
			$checkboxGlpi = false;
			$hostGlpi = null;
			$dbGlpi = null;
			$userGlpi = null;
			$passGlpi = null;
		}
		if (!isset($error)) {
			try {
				// Connexion à la base de données
				$dsn = "mysql:host=" . $dbHost;
				$pdo = new PDO($dsn, $dbUser, $dbPassword);

				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

				// Création de la base de données si elle n'existe pas
				$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
				$pdo->exec("USE `$dbName`");


				if ($checkboxDelTable) {
					$tables = ['administrateurs', 'timeline', 'typeaccounts', 'visites'];
					$tables2 = [
						['eleves', ['lastpc_id']],
						['pc', ['typeasset_id', 'lasteleve_id']],
						['typeassets', ['typeasset_id', 'lasteleve_id']],
					];
					$pdo = new PDO($dsn . ";dbname=" . $dbName, $dbUser, $dbPassword);
					foreach ($tables as $table) {
						$query = $pdo->prepare("SHOW TABLES LIKE ?");
						$table = $table_prefix . $table;
						$query->execute([$table]);
						if ($query->rowCount() > 0) {
							$pdo->exec("DROP TABLE " . $table_prefix . $table);
						}
					}
					$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
					foreach ($tables2 as $table) {
						$query = $pdo->prepare("SHOW TABLES LIKE ?");
						$table[0] = $table_prefix . $table[0];
						$query->execute([$table[0]]);
						if ($query->rowCount() > 0) {
							foreach ($table[1] as $col) {
								$query2 = $pdo->prepare("ALTER TABLE " . $table[0] . " DROP FOREIGN KEY " . $col);
								$query2->execute();
							}
						}
					}
				}


				// Création des tables
				$queries = [
					"CREATE TABLE IF NOT EXISTS " . $table_prefix . "visites (
							id INT AUTO_INCREMENT PRIMARY KEY,
							login_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'date de la dernière connection',
							logout_date TIMESTAMP NULL COMMENT 'date de la dernière deco',
							administrateurs_id INT NOT NULL
						)",
					// type d'assets (pc, tv, ou autre)
					"CREATE TABLE IF NOT EXISTS " . $table_prefix . "typeassets (
							id INT AUTO_INCREMENT PRIMARY KEY,
							content VARCHAR(100)
						)",
					// type d'assets (pc, tv, ou autre)
					"INSERT INTO " . $table_prefix . "typeassets (content) VALUES 
							('Ordinateur portable'),
							('Chargeur'),
							('Cable Hdmi'),
							('Rallonge'),
							('Tv')",
					"CREATE TABLE IF NOT EXISTS " . $table_prefix . "typeaccounts (
							id INT AUTO_INCREMENT PRIMARY KEY,
							content VARCHAR(30)
						)",
					"INSERT INTO " . $table_prefix . "typeaccounts  (id, content) VALUES 
							(1, 'Opérateur'),
							(2, 'Opérateur de niveau 2'),
							(3, 'Opérateur de niveau 3'),
							(4, 'Opérateur de niveau 4'),
							(5, 'Constructeur'),
							(6, 'Architecte'),
							(7, 'Contrôleur Central'),
							(8, 'Contrôleur Maître'),
							(9, 'Contrôleur Suprême')",
					"CREATE TABLE IF NOT EXISTS " . $table_prefix . "administrateurs (
							id INT AUTO_INCREMENT PRIMARY KEY,
							pseudo VARCHAR(30) NOT NULL UNIQUE,
							motdepasse VARCHAR(255) NOT NULL,
							nom VARCHAR(30),
							prenom VARCHAR(30),
							birth TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
							sessionkey VARCHAR(255),
							mail VARCHAR(50),
							typeaccount_id INT NOT NULL)",
					"INSERT INTO " . $table_prefix . "administrateurs (pseudo, motdepasse, nom, prenom, mail, typeaccount_id) VALUES 
							('" . $defaultAdminPseudo . "', '" . password_hash($defaultAdminPasse, PASSWORD_DEFAULT) . "', '" .
						$defaultAdminNom . "', '" . $defaultAdminPrenom . "', '" .
						$defaultAdminMail . "', '" . $defaultAdminTypeaccount . "')",
					// TABLE DES ASSETS
					"CREATE TABLE IF NOT EXISTS " . $table_prefix . "pc (
							id INT AUTO_INCREMENT PRIMARY KEY,
							barrecode VARCHAR(50) UNIQUE NOT NULL,
							model VARCHAR(100),
							serialnum VARCHAR(100),
							birth TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
							etat VARCHAR(50),
							typeasset_id INT NOT NULL,
							used INT DEFAULT 0,
							position VARCHAR(10) DEFAULT 'in',
							glpi_id INT NULL COMMENT 'id dans la table computer de glpi',
							lasteleve_id INT NULL COMMENT 'last owner id',
							in_date TIMESTAMP NULL COMMENT 'last date in',
							out_date TIMESTAMP NULL COMMENT 'last date out'
						)",
					// TABLE DES CLIENTS
					"CREATE TABLE IF NOT EXISTS " . $table_prefix . "eleves (
							id INT AUTO_INCREMENT PRIMARY KEY,
							barrecode VARCHAR(50) UNIQUE COMMENT 'doit être unique',
							nom VARCHAR(100) NOT NULL,
							prenom VARCHAR(100) NOT NULL,
							promo VARCHAR(50) NOT NULL COMMENT 'exemple:2426',
							classe VARCHAR(50) NOT NULL COMMENT 'exemple:BTSCOM',
							birth TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'date de création',
							mail VARCHAR(255) NOT NULL,
							glpi_id INT NULL COMMENT 'id user dans la table glpi',
							lastpc_id INT NULL COMMENT 'id pc du dernier emprunt',
							in_date TIMESTAMP NULL COMMENT 'date du dernier retour',
							out_date TIMESTAMP NULL COMMENT 'date du dernier emprunt',
							pivot_id INT NULL COMMENT 'idpivot pour un service en paralèlle')
						",
					"INSERT INTO " . $table_prefix . "pc (barrecode, model, serialnum, etat, typeasset_id, position) VALUES
							('10000001', 'Dell Inspiron', 'SN12345', 'Disponible', 1, 'in'),
							('10000011', 'HP EliteBook', 'SN67890', 'En réparation', 1, 'in'),
							('30089587', 'Air ProMaster', 'SN00007', 'Disponible', 1, 'in'),
							('4056489371724', 'WTF ChallengerPro', 'SN00008', 'Disponible', 1, 'in')",
					"INSERT INTO " . $table_prefix . "eleves (barrecode, nom, prenom, promo, classe, mail) VALUES
							('00000001', 'Doe', 'John', '2426', 'BTSCOM', 'john.doe@example.com'),
							('00000011', 'Smith', 'Jane', '2325', 'BTSAG', 'jane.smith@example.com'),
							('4006396038531', 'Smith', 'Alice', '2325', 'BTSCOM', 'alice.smith@example.com')",
					"CREATE TABLE IF NOT EXISTS " . $table_prefix . "timeline (
							id INT AUTO_INCREMENT PRIMARY KEY,
							birth TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
							typeaction VARCHAR(50) NOT NULL,
							ideleves INT NULL,
							idpc INT NOT NULL
						)",
					// on ajoute une FK
					// "ALTER TABLE ".$table_prefix."pc ADD CONSTRAINT lasteleve_id FOREIGN KEY (lasteleve_id) REFERENCES ".$table_prefix."eleves(id)",

					// on ajoute des timelines
					"INSERT INTO " . $table_prefix . "timeline (ideleves, idpc, typeaction) VALUES (1,1,'out'),(1,2,'in')",
				];
				foreach ($comptes as $compte) {
					$queries[] = "INSERT INTO " . $table_prefix . "administrateurs (pseudo, motdepasse, nom, prenom, mail, typeaccount_id) VALUES ('" .
						$compte['pseudo'] . "', '" . $compte['passe'] . "', '" . $compte['nom'] . "', '" .
						$compte['prenom'] . "', '" . $compte['mail'] . "', '" . $compte['typeaccount'] . "')";
				}
				foreach ($queries as $query) {
					$pdo->exec($query);
				}

				// // éffacement du fichier dbconfig.php
				// if (file_exists($dbConfigPath)) {
				// 	deleteConfigFile($checkboxProd,$dbConfigPath); // Supprime ce script
				// }

				// Création du fichier dbconfig.php
				$host = 'http://' . $_SERVER['HTTP_HOST'];
				$sitedir = stripslashes(dirname($_SERVER['PHP_SELF']));
				$configContent = creationContentdbConfig(
					$dbHost,
					$dbName,
					$dbUser,
					$dbPassword,
					$table_prefix,
					$hostGlpi,
					$dbGlpi,
					$userGlpi,
					$passGlpi,
					$version,
					$creation,
					$host,
					$sitedir,
					$checkboxProd
				);

				creationFichiers($dbConfigPath, $configContent, $checkboxProd);
			} catch (PDOException $e) {
				$error = "Erreur de connexion ou de création de la base de données : " . $e->getMessage();
			} catch (Exception $e) {
				$error = "Erreur : " . $e->getMessage();
			}
		}
	}
}
/**
 * Fonction pour créer le fichier install.php
 */
function creationFichiers($dbConfigPath, $configContent, $checkboxProd)
{
	file_put_contents($dbConfigPath, $configContent);
	deleteInstallFile($checkboxProd);

	print_r($dbConfigPath . "<br/>");
	print_r($configContent . "<br/>");

	// header('Location: /');
	// exit;
}
/**
 * Fonction pour supprimer le fichier install.php
 */
function deleteInstallFile($checkboxProd)
{
	!$checkboxProd ? rename('install.php', "install.save") : unlink(__FILE__);
}
/**
 * Fonction pour supprimer le fichier dbConfig.php
 */
function deleteConfigFile($checkboxProd, $dbConfigPath)
{
	if ($checkboxProd) unlink($dbConfigPath);
}
/**
 * Fonction pour créer le content du  futur fichier dbConfig
 */
function creationContentdbConfig($dbHost, $dbName, $dbUser, $dbPassword, $table_prefix, $hostGlpi, $dbGlpi, $userGlpi, $passGlpi, $version, $creation, $host, $sitedir, $checkboxProd)
{
	$checkboxProd = $checkboxProd ? 'true' :  'false';
	$rootpath = ROOTPATH ?? '';
	return <<<PHP
<?php
	\$dbHost = '$dbHost';
	\$dbName = '$dbName';
	\$dbUser = '$dbUser';
	\$dbPassword = '$dbPassword';
	\$table_prefix = '$table_prefix';
	
	\$hostGlpi = '$hostGlpi';
	\$dbGlpi = '$dbGlpi';
	\$userGlpi = '$userGlpi';
	\$passGlpi = '$passGlpi';

	\$version = '$version';
	\$creation = '$creation';
	
	define('CONFIG', [
		'WEBSITE' => [
			'header' => 'Content-type: text/html; charset=UTF-8',
			'siteurl' => '$host',
			'sitedir' => '$sitedir',
		],
		'REFRESH' => [
			'in' => 2,
			'out' => 2
		],
		'PROD' => $checkboxProd, // false en dev, true en prod
		'APPROOT' => '$rootpath', // false en dev, true en prod
	]);
PHP;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Installer les tables</title>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
		}

		body {
			background-color: rgb(38, 50, 184);
			background-image: linear-gradient(hsla(0, 0%, 0%, .05) 2px, transparent 0), linear-gradient(90deg, hsla(0, 0%, 0%, .05) 2px, transparent 0), linear-gradient(hsla(0, 0%, 0%, .05) 1px, transparent 0), linear-gradient(90deg, hsla(0, 0%, 0%, .05) 1px, transparent 0);
			background-position: -2px -2px, -2px -2px, -1px -1px, -1px -1px;
			background-size: 100px 100px, 100px 100px, 20px 20px, 20px 20px;
			color: #333;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.container {
			background: #fff;
			border-radius: 8px;
			width: 350px;
			background-color: rgba(236, 236, 236, 0.95);
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
			padding: 10px;
			margin: 10px;
			display: flex;
			flex-direction: column;
		}

		form {
			display: flex;
			flex-direction: column;
		}

		.blocs {
			padding: 10px 10px;
			margin-top: 5px;
			border-radius: 15px;

			&.bloc-glpi {
				background-color: rgb(255, 208, 208);
				border-radius: 15px;
				border-bottom-left-radius: 0px;
				border-bottom-right-radius: 0px;
			}

			&.bloc-glpi2 {
				margin-top: 0;
				background-color: rgb(255, 208, 208);
				border-top-left-radius: 0px;
				border-top-right-radius: 0px;
				display: none;
			}

			&.bloc-datas {
				background-color: rgb(250, 255, 208);
			}

			&.bloc-admin {
				background-color: rgb(215, 221, 255);
				border-radius: 15px;
			}

			&.bloc-bdd {
				background-color: rgb(215, 255, 218);
				border-radius: 15px;
			}

			&.bloc-prod {
				background-color: rgb(250, 215, 255);
			}

			&.center {
				display: flex;
				justify-content: center;
			}
		}

		h1 {
			font-size: 24px;
			color: #555;
			margin-bottom: 0;
			text-align: center;
		}

		p {
			text-align: center;
		}

		.error {
			color: #ff4d4d;
			font-size: 14px;
			margin-bottom: 15px;
		}

		label {
			position: relative;
			font-weight: bold;
			text-align: left;
			padding: 7px;
		}

		.input-container {
			position: relative;
			display: flex;
			align-items: center;

			&.checkboxed {
				display: flex;
				flex-direction: row;
				flex-wrap: wrap;
				align-content: center;
				align-items: center;
				justify-content: flex-start;

				label {
					text-wrap: nowrap;
				}

				input {
					padding: 0;
					border: 1px solid #ddd;
					border-radius: 4px;
					width: initial;
					border-radius: 0;
				}
			}

			&.bdd {
				background-color: #4178a9;
			}

			p {
				text-align: left;
			}
		}

		.input-container input {
			padding: 10px 10px 10px 35px;
			border: 1px solid #ddd;
			border-radius: 4px;
			font-size: 16px;
			width: 100%;
			border-radius: 9px;
			box-sizing: border-box;
		}

		.input-container input:focus {
			border-color: #5b9bd5;
			outline: none;
			box-shadow: 0 0 5px rgba(91, 155, 213, 0.5);
		}

		.input-container .icon {
			position: absolute;
			left: 10px;
			color: rgb(77, 77, 77);
			font-size: 18px;
		}

		.input-container:hover .icon {
			color: rgb(7, 77, 5);
		}

		button {
			padding: 10px;
			background-color: #5b9bd5;
			color: #fff;
			border: none;
			border-radius: 4px;
			font-size: 16px;
			cursor: pointer;
			width: fit-content;
			transition: background-color 0.3s ease;
		}

		button:hover {
			background-color: #4178a9;
		}
	</style>
</head>

<body>
	<div class="container">
		<h1>Assets Tracker Installation</h1>
		<?php if (!empty($error)): ?>
			<p class="error"><?php echo htmlspecialchars($error); ?></p>
		<?php endif; ?>
		<form class="form" method="post">
			<div class="blocs bloc-datas">
				<h2>Informations de la BDD !</h2>
				<label for="host">Hôte :</label>
				<div class="input-container">
					<span class="icon">🎁</span>
					<input type="text" id="host" name="host" required value="<?php echo $defaultHost; ?>">
				</div>

				<label for="db">Nom de la base de données :</label>
				<div class="input-container">
					<span class="icon">💽</span>
					<input type="text" id="db" name="db" required value="<?php echo $defaultDb; ?>">
				</div>

				<label for="user">Utilisateur :</label>
				<div class="input-container">
					<span class="icon">🤚</span>
					<input type="text" id="user" name="user" required value="<?php echo $defaultUser; ?>">
				</div>

				<label for="pass">Mot de passe :</label>
				<div class="input-container">
					<span class="icon">🔒</span>
					<input type="password" id="pass" name="pass" style="padding-right: 30px;">
					<span id="togglePass" style="
						position: absolute;
						top: 50%;
						right: 10px;
						transform: translateY(-50%);
						cursor: pointer;
						color: #999;
					">&#128065;</span>
				</div>
			</div>
			<div class="blocs bloc-admin">
				<h2>Compte admin</h2>
				<label for="user">Pseudo Admin :</label>
				<div class="input-container">
					<span class="icon">🤚</span>
					<input type="text" id="adminpseudo" name="adminpseudo" required value="<?php echo $defaultAdminPseudo; ?>">
				</div>
				<label for="pass">Mot de passe Admin :</label>
				<div class="input-container">
					<span class="icon">🔒</span>
					<input type="password" id="adminpass" name="adminpass" style="padding-right: 30px;" value="">
					<span id="toggleAdminPass" style="position: absolute;top: 50%;right: 10px;transform: translateY(-50%);cursor: pointer;color: #999;">&#128065;</span>
				</div>
			</div>

			<div class="blocs bloc-bdd">
				<h2>Création de la BDD ?</h2>
				<div class="input-container checkboxed">
					<label for="bddbypass">Même si elle existe déjà </label>
					<input type="checkbox" id="bddbypass" name="bddbypass" />
				</div>
				<div class="input-container prod">
					<p>Les tables seront créer par dessus celle existantes si la bdd existe déja.<br>Attention cela causera des erreurs si des ids exitent déjà dans certainnes tables.</p>
				</div>

				<label for="deltable">Préfixe des tables</label>
				<div class="input-container">
					<span class="icon">📑</span>
					<input type="text" id="prefix" name="prefix" style="padding-right: 30px;" value="" placeholder="tat_ (par exemple)">
				</div>
				<div class="input-container dbtable">
					<p>Les tables seront préfixé, ce qui évitera des les mélanger avec d'autres.<br>(exemple : tat_users à la place de user).</p>
				</div>

				<div class="input-container checkboxed">
					<label for="deltable">Suppression des tables </label>
					<input type="checkbox" id="deltable" name="deltable" />
				</div>
				<div class="input-container dbtable">
					<p>Les tables seront éffacées avant l'installation si elles existent. Cela évitera les erreurs si des ids exitent déjà dans certainnes tables.</p>
				</div>
			</div>

			<div class="blocs bloc-prod">
				<h2>Prod ou Dev ?</h2>
				<div class="input-container checkboxed">
					<label for="production">Production</label>
					<input type="checkbox" id="production" name="production" />
				</div>
				<div class="input-container prod">
					<p>En mod Prod le fichier 'install.php' est supprimé après l'installation alors qu'en mode Dev celui si est renommé en "install.save".</p>
				</div>
			</div>
			<div class="blocs bloc-glpi">
				<h2>Compte Glpi</h2>
				<div class="input-container checkboxed">
					<label for="pass">activer Glpi </label>
					<input type="checkbox" id="glpitrigger" name="glpitrigger" />
				</div>
			</div>

			<div class="blocs bloc-glpi2" id="blocGlpi">
				<label for="host">Nom de l'Hôte Glpi :</label>
				<div class="input-container">
					<span class="icon">🎁</span>
					<input type="text" id="hostGlpi" name="hostGlpi" placeholder="idem que plus haut si vide">
				</div>
				<label for="db">Nom de la base de données Glpi:</label>
				<div class="input-container">
					<span class="icon">💽</span>
					<input type="text" id="dbGlpi" name="dbGlpi" placeholder="glpi si vide">
				</div>

				<label for="user">Utilisateur :</label>
				<div class="input-container">
					<span class="icon">🤚</span>
					<input type="text" id="userGlpi" name="userGlpi" placeholder="idem que plus haut si vide">
				</div>

				<label for="pass">Mot de passe :</label>
				<div class="input-container">
					<span class="icon">🔒</span>
					<input type="password" id="passGlpi" name="passGlpi" style="padding-right: 30px;" placeholder="idem que plus haut si vide">
					<span id="togglePassGlpi" style="
						position: absolute;
						top: 50%;
						right: 10px;
						transform: translateY(-50%);
						cursor: pointer;
						color: #999;
					">&#128065;</span>
				</div>
			</div>



			<div class="blocs bloc-dir" id="blocDir">
				<h2>Installation à la racine du domaine ?</h2>
				<div class="input-container checkboxed">
					<label for="production">Oui</label>
					<input type="checkbox" id="production" name="production" />
				</div>
				<div class="input-container prod">
					<p>Si votre site est dans un dossier à la racine du domaine (exemple:www.site.com/dossier/), </p>
					<p>uu si votre domaine pointe vers un sous-dossier, </p>
					<p>...alors décochez cette case !</p>
				</div>
			</div>


			<div class="blocs center">
				<button type="submit">Installer</button>
			</div>
		</form>
	</div>
	<script>
		const passwordField = document.getElementById('pass');
		const togglePass = document.getElementById('togglePass');
		const passwordAField = document.getElementById('adminpass');
		const toggleAdminPass = document.getElementById('toggleAdminPass');

		const passwordAFieldGlpi = document.getElementById('passGlpi');
		const toggleAdminPassGlpi = document.getElementById('togglePassGlpi');

		const glpitrigger = document.getElementById('glpitrigger');
		const blocGlpi = document.getElementById('blocGlpi');

		glpitrigger.addEventListener('click', () => {
			if (glpitrigger.checked) {
				blocGlpi.style.display = 'initial'
			} else {
				blocGlpi.style.display = 'none'
			}
		});
		togglePass.addEventListener('click', () => {
			if (passwordField.type === 'password') {
				passwordField.type = 'text';
				togglePass.innerHTML = '&#128064;';
			} else {
				passwordField.type = 'password';
				togglePass.innerHTML = '&#128065;';
			}
		});
		toggleAdminPass.addEventListener('click', () => {
			if (passwordAField.type === 'password') {
				passwordAField.type = 'text';
				toggleAdminPass.innerHTML = '&#128064;';
			} else {
				passwordAField.type = 'password';
				toggleAdminPass.innerHTML = '&#128065;';
			}
		});
		toggleAdminPassGlpi.addEventListener('click', () => {
			if (passwordAFieldGlpi.type === 'password') {
				passwordAFieldGlpi.type = 'text';
				toggleAdminPassGlpi.innerHTML = '&#128064;';
			} else {
				passwordAFieldGlpi.type = 'password';
				toggleAdminPassGlpi.innerHTML = '&#128065;';
			}
		});
	</script>
</body>

</html>