<?php
namespace app\core;

class Navigation
{
	private $pageToDisplay;
	private $navigation ='';
	private $menus;
	private $menus2;
	private $url;
	private $view = '
	<nav class="nav-top">
		<div class="nav-top-brand"><img src="/img/assets.png" alt="AssetsTracker Logo"></div>
		<ul class="nav-top-ul">
			{{topnav}}
		</ul>
	</nav>';
	private $view2 = '
	<nav class="topnav">
		<ul>
			<li class="ico">
				<image class="svg" width="20px" height="20px" src="computer.svg">
			</li>
			{{topnav}}
			<li>Edit
				<ul>
					<li>File</li>
					<li>Edit</li>
					<li>Selection</li>
				</ul>
			</li>
			<li>Selection
				<ul>
					<li>File</li>
					<li>Edit</li>
					<li>Selection</li>
				</ul>
			</li>
		</ul>
	</nav>';

	public function __construct() {
		$this->menus = [
			'index'=> [
				'type'=> 'a',
				'url'=> 'index',
				'content'=> 'Accueil',
				'class'=> 'index',
				'hiddenIfUrl'=> false,
				'href'=> '/',
				'lv'=> (int)(0),
			],
			'in'=> [
				'type'=> 'a',
				'url'=> 'in',
				'content'=> 'Rendez',
				'hiddenIfUrl'=> false,
				'href'=> '/in',
				'class'=> 'in',
				'needLog'=> true,
				'lv'=> (int)(1),
			],
			'out'=> [
				'type'=> 'a',
				'url'=> 'out',
				'content'=> 'Empruntez',
				'hiddenIfUrl'=> false,
				'href'=> '/out',
				'class'=> 'out',
				'needLog'=> true,
				'lv'=> (int)(1),
			],
			'listpc'=> [
				'type'=> 'a',
				'url'=> 'listpc',
				'content'=> 'List Pc',
				'hiddenIfUrl'=> false,
				'href'=> '/listpc',
				'needLog'=> true,
				'lv'=> (int)(2),
			],
			'listeleves'=> [
				'type'=> 'a',
				'url'=> 'listeleves',
				'content'=> 'List Élèves',
				'hiddenIfUrl'=> false,
				'href'=> '/listeleves',
				'needLog'=> true,
				'lv'=> (int)(2),
			],
			'timeline'=> [
				'type'=> 'a',
				'url'=> 'timeline',
				'content'=> 'Timeline',
				'hiddenIfUrl'=> false,
				'href'=> '/timeline',
				'class'=> 'timeline',
				'needLog'=> true,
				'lv'=> (int)(1),
			],
			'glpipc'=> [
				'type'=> 'a',
				'url'=> 'glpipc',
				'content'=> 'Glpipc',
				'hiddenIfUrl'=> false,
				'href'=> '/glpipc',
				'class'=> 'glpipc',
				'needLog'=> true,
				'lv'=> (int)(3),
			],
			'profile'=> [
				'type'=> 'a',
				'url'=> 'profile',
				'content'=> 'Profile',
				'hiddenIfUrl'=> false,
				'href'=> '/profile',
				'needLog'=> true,
				'lv'=> (int)(2),
				'classHideContent'=>true,
				'classRight'=>true,
			],
			'login'=> [
				'type'=> 'a',
				'url'=> 'login',
				'content'=> 'LogIn',
				'class'=> 'login',
				'hiddenIfUrl'=> ['login'],
				'href'=> '/login',
				'needLog'=> false,
				'needUnlog'=> true,
				'lv'=> (int)(0),
			],
			'interface'=> [
				'type'=> 'a',
				'url'=> 'interface',
				'content'=> 'Interface',
				'hiddenIfUrl'=> false,
				'href'=> '/interface',
				'class'=> 'interface',
				'needLog'=> true,
				'lv'=> (int)(1),
				'classHideContent'=>true,
			],
			'three'=> [
				'type'=> 'a',
				'url'=> 'three',
				'content'=> '3D',
				'hiddenIfUrl'=> false,
				'href'=> '/three',
				'class'=> 'three',
				'needLog'=> true,
				'lv'=> (int)(3),
				'classHideContent'=>true,
			],
			'logout'=> [
				'type'=> 'a',
				'url'=> 'logout',
				'content'=> 'Déconnexion',
				'class'=> 'deco',
				'hiddenIfUrl'=> false,
				'href'=> '/logout',
				'needLog'=> true,
				'lv'=> (int)(0),
				'classHideContent'=>true,
			],
			'github'=> [
				'type'=> 'a',
				'url'=> 'github',
				'content'=> 'Github',
				'class'=> 'github',
				'hiddenIfUrl'=> false,
				'href'=> 'https://github.com/patobeur/assetstracker',
				'target'=> '_github',
				'needLog'=> true,
				'lv'=> (int)(3),
				'classHideContent'=>true,
			],
		];
	}
	public function addNavigation($pageToDisplay,$url): string
	{
		$this->url= $url;
		$this->pageToDisplay= $pageToDisplay;
		$this->navigation = $this->getTopNav();

		$this->view = str_replace(search: "{{topnav}}",replace: $this->navigation,subject: $this->view);

		$this->pageToDisplay = str_replace(search: "{{topNavViewA}}",replace: $this->view, subject: $this->pageToDisplay);
		return $this->pageToDisplay;
	}
	private function getI($index,$key)
	{
		 return (isset($this->menus[$index]) && isset($this->menus[$index][$key]) )
			? '<i class="'.$this->menus[$index][$key].'"></i>'
			: '';
	}
	private function getLi($index)
	{
		$needLog = $this->menus[$index]['needLog'] ?? false;
		$needUnlog = $this->menus[$index]['needUnlog'] ?? false;
		$requestUrl = $this->menus[$index]['url'];
		$requestedLv = $this->menus[$index]['lv'];
		$requestClass = $this->menus[$index]['class'] ?? '';
		$requestHref = $this->menus[$index]['href'] ? ' title="'.$this->menus[$index]['url'].'" href='.$this->menus[$index]['href'] : '' ;
		$requestcontent = $this->menus[$index]['content'];
		$url = $this->url;

		$hiddenRules = $this->menus[$index]['hiddenIfUrl'] ?? [];
		$displayItem = true;
		if ($hiddenRules && count($hiddenRules)>0){
			foreach ($hiddenRules as $value) if($url === $value) $displayItem = false;
		}
		$li= "";
		if(
			$displayItem && 
			!($needLog && !isset($_SESSION['user'])) && 
			!($needUnlog && isset($_SESSION['user'])) &&
			!(isset($this->menus[$index]['lv']) && isset( $_SESSION['user']) && isset( $_SESSION['user']['lv']) && $_SESSION['user']['lv'] < $requestedLv)
		){
			//class
			$liClass = ' class="item';
				if($url===$requestUrl) $liClass .=" on";
				if($requestClass != '') $liClass .=" ".$requestClass;
				if(isset($this->menus[$index]['classRight'])) $liClass .=' right';
				if(isset($this->menus[$index]['classHideContent'])) $liClass .=' hidea';
			$liClass .= '"';

			//title
			$title = $this->menus[$index]['href'] ? " title=\"".$this->menus[$index]['url']."\"" : "";
			//onclick
			$onclick = $this->menus[$index]['href'] ? " onclick=\"location.href='".$this->menus[$index]['href']."'\"" : "";
			//href
			$href = $this->menus[$index]['href'] ? ' href="'.$this->menus[$index]['href'].'"' : '';


			

			// icone
			$li = '<li'.$liClass.$title.$onclick.'>';
			$li .= '<i class="ico fico-'.$requestUrl.'"></i>';
				$li .= '<a'. $title.$href.'>';
				$li .= $requestcontent;
				$li .= '</a>';
			$li .= '</li>';	
		}
		return $li;
	}
	public function getTopNav(): string
	{	
		$string = '';	
		foreach ($this->menus as $key => $value) {
			$string .= $this->getLi($key);
		}

		return $string;
	}
}
