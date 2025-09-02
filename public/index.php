<?php
	session_start();
	function getRelativePath($adjust = 1) {
		
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


		return $relativeBackPath;

	}
	define('ROOTPATH', getRelativePath(1)); 


	// print_r('---<br>');
	// print_r(ROOTPATH.'---<br>');
	// print_r( substr_count(trim($_SERVER['REQUEST_URI'], '/'), '/').'---<br>');

	require_once ROOTPATH.'app/core/autoloader.php';	
	$dbConfigPath = ROOTPATH.'app/conf/dbconfig.php';

	use app\core\Console;
	use app\core\Checkdb;
	use app\core\Router;
	use app\core\FrontConstructor;

	$Console = new Console(true);
	$CheckDb = new CheckDb($Console,$dbConfigPath); // lance checkInstallAndConfig
	$router = new Router($CheckDb,$Console);
	$frontConstructor = new FrontConstructor(Console: $Console);

	// Récupération de l'URL
	$url = trim(string: parse_url(url: $_SERVER['REQUEST_URI'], component: PHP_URL_PATH), characters: '/');

	// récupération des blocs à afficher en fonction de l'url
	$contentDatas = $router->dispatch(url: $url);

	// récuperation de l'url retournée le cas échéant
	if(isset($contentDatas['url'])) $url = $contentDatas['url']; 

	$content = $contentDatas['content'];
	// on ajoute le contenu au front
	$frontConstructor->addContentToStack($content);

	// on pourrais en rajouter à la main aussi
	// $frontConstructor->addContentToStack(['TITLE' => "en plus",'CONTENT'   => '<h3>un test en plus ?</h3>']);
	
	// on récupère la page construite avec l'url d'origine
	$pageToDisplay = $frontConstructor->getPageToDisplay($url);

	// récuperation d'un 'Redirect' le cas échéant
	if(isset($content['Redirect']) && $content['Redirect'] && isset($content['Redirect']['url'])){
		$newurl = $content['Redirect']['url'];
		$delay = $content['Redirect']['refresh'];
		$redirect = 'refresh:'.$delay.';url='.$newurl;
		header(header: $redirect);
	}
	if (!headers_sent()) {
		header(header: CONFIG['WEBSITE']['header']);
	}
	// on affiche la page
	echo $pageToDisplay;