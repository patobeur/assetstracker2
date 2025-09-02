<?php
namespace app\core;
use app\vendor\fpdf\fpdf;

class Router
{
	private $routes = [];
	private $pdf = null;
	private $CheckDb;
	private $defaultPage = '';
	private $pdo = null;
	private $Console = null;

	public function __construct($CheckDb,$Console) {
		
		$CheckUser = new CheckUser($CheckDb, $Console); // lance un check sessionKey
		$CheckGlpi = new CheckGlpi($CheckDb, $Console); // lance un check pour Glpi

		// Définition des routes
		// c'est ici que l'on ajoutera les pages de l'app et les routes possibles
		$this->add(route: 'index',action: 'IndexController@showIndex@null@0@null');

		// c'est ici que l'on ajoutera les pages accessibles uniquement si non loggué 
		if (!isset($_SESSION['user'])) {
			$this->add(route: 'login',action: 'LoginController@handleLogin@db@0@null');
			$this->add(route: 'example',action: 'exampleController@ExampleHandler@db@0@null');
			$this->add(route: 'contact',action: 'exampleController@ContactHandler@db@0@null');
		}
		// c'est ici que l'on ajoutera les pages accessibles uniquement si loggué 
		if (isset($_SESSION['user'])) {
			$this->add('interface','InterfaceController@interfaceHandler@null@0@null');
			$this->add(route: 'profile',action: 'ProfileController@showProfile@db@1@null');
			$this->add(route: 'three',action: 'ThreeController@go@db@2@null');
			//
			$this->add(route: 'listpc',action: 'ListingController@listPc@db@1@pdf');
			$this->add(route: 'listeleves',action: 'ListingController@listEleves@db@1@pdf');
			$this->add(route: 'timeline',action: 'ListingController@listTimeline@db@1@pdf');
			//
			$this->add(route: 'out',action: 'OutController@handle@db@1@null');
			$this->add(route: 'in',action: 'InController@handle@db@1@null');
			$this->add(route: 'logout',action: 'LoginController@logout@db@1@null');
			// $this->add(route: 'codebare',action: 'Codebare@getCodebare@null@1@null');

			if (isset($_SESSION['user']['typeaccount_id']) && $_SESSION['user']['typeaccount_id']>2) {
				// $this->add(route: 'glpipc',action: 'GlpiController@handle@db@3@null');
				$this->add(route: 'exportpc',action: 'GlpiController@exportpc@db@3@null');
				$this->add(route: 'exportuser',action: 'GlpiController@exportuser@db@3@null');
			}
		}

		$pdf = new FPDF();// on charge la 'librarie' fpdf
		$this->pdf = $pdf;
		$this->Console = $Console;
		$this->CheckDb = $CheckDb;
		$this->pdo = $this->CheckDb->getPdo();
	}

    public function add($route, $action)
    {
        $this->routes[$route] = $action;
    }

	public function setdefaultPage()
	{
		$this->defaultPage = file_get_contents(CONFIG['APPROOT'].'app/views/front.php');
	}

	public function display()
	{
		echo $this->defaultPage;
	}

	public function dispatch($url="")
	{
		if ($url==="") {$url="index";}
		// if (!isset($_SESSION['user'])) {$url="login";}
		if (!$this->pdo) {$url="index";}

		$this->setdefaultPage();

		if (isset($this->routes[$url])) {
			$action = $this->routes[$url];
			list($controller, $method, $db, $lv, $pdf) = explode('@', $action);
			
			$controller = "app\\controllers\\$controller";

			if (class_exists($controller) && method_exists($controller, $method)) {
				$controllerInstance = new $controller($db==="db"?$this->CheckDb:null,$pdf==="pdf"?$this->pdf:null);
				return [
					'content'=>$controllerInstance->$method()
				];
			} else {
				return [
					'content'=>$this->notFound(false)
				];
			}
		} else {
			return [
				'url'=>'notfound',
				'content'=>$this->notFound(true)
			];
		}
	}

	private function notFound($boule)
	{		
		$notFoundController = "app\\controllers\\NotFoundController";
		$notFound = new $notFoundController();
		$content = $notFound->showIndex($boule);
		// http_response_code(404);
		return $content;
	}
}
