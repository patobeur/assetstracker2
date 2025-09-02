<?php
namespace app\core;

class Navigation2
{
	private $pageToDisplay;
	private $navigation ='';
	private $menus;
	private $menus2;
	private $url;
	private $view = '
	<nav class="topnav">
		<ul>
			{{topnav}}
		</ul>
	</nav>';

	public function __construct() {
		$this->menus = [
			'index'=> [
				'ico'=>'index',
				'tag'=> 'a',
				'url'=> 'index',
				'content'=> 'Accueil',
				'class'=> 'index',
				'hiddenIfUrl'=> false,
				'href'=> '/',
				'lv'=> (int)(0),
			],
			'listes'=> [
				'ico'=>'listes',
				'tag'=> 'span',
				'url'=> '',
				'content'=> 'Listes',
				'hiddenIfUrl'=> false,
				'href'=> '',
				'class'=> 'listes',
				'needLog'=> true,
				'lv'=> (int)(1),
				'others'=> [
					'listpc'=> [
						'ico'=>'listpc',
						'tag'=> 'a',
						'url'=> 'listpc',
						'content'=> 'List Pc',
						'hiddenIfUrl'=> false,
						'href'=> '/listpc',
						'needLog'=> true,
						'lv'=> (int)(2),
					],
					'listeleves'=> [
						'ico'=>'listeleves',
						'tag'=> 'a',
						'url'=> 'listeleves',
						'content'=> 'List Élèves',
						'hiddenIfUrl'=> false,
						'href'=> '/listeleves',
						'needLog'=> true,
						'lv'=> (int)(2),
					],
					'timeline'=> [
						'ico'=>'timeline',
						'tag'=> 'a',
						'url'=> 'timeline',
						'content'=> 'Timeline',
						'hiddenIfUrl'=> false,
						'href'=> '/timeline',
						'class'=> 'timeline',
						'needLog'=> true,
						'lv'=> (int)(1),
					],
				]
			],
			'actions'=> [
				'ico'=>'actions',
				'tag'=> 'span',
				'url'=> '',
				'content'=> 'Actions',
				'hiddenIfUrl'=> false,
				'href'=> '',
				'class'=> 'actions',
				'needLog'=> true,
				'lv'=> (int)(1),
				'others'=> [
					'in'=> [
						'ico'=>'in',
						'tag'=> 'a',
						'url'=> 'in',
						'content'=> 'Rendez',
						'hiddenIfUrl'=> false,
						'href'=> '/in',
						'class'=> 'in',
						'needLog'=> true,
						'lv'=> (int)(1),
					],
					'out'=> [
						'ico'=>'out',
						'tag'=> 'a',
						'url'=> 'out',
						'content'=> 'Empruntez',
						'hiddenIfUrl'=> false,
						'href'=> '/out',
						'class'=> 'out',
						'needLog'=> true,
						'lv'=> (int)(1),
					],]
			],
			'admins'=> [
				'ico'=>'admins',
				'tag'=> 'span',
				'url'=> '',
				'content'=> 'Admins',
				'hiddenIfUrl'=> false,
				'href'=> '',
				'class'=> 'admins',
				'needLog'=> true,
				'lv'=> (int)(3),
				'others'=> [
					'glpipc'=> [
						'ico'=>'glpipc',
						'tag'=> 'a',
						'url'=> '',
						'content'=> 'Glpipc',
						'hiddenIfUrl'=> false,
						'href'=> '',
						'class'=> 'glpipc',
						'needLog'=> true,
						'lv'=> (int)(3),
						'others'=> [
							'exportpc'=> [
								'ico'=>'glpipc',
								'tag'=> 'a',
								'url'=> 'exportpc',
								'content'=> 'ExportPc',
								'hiddenIfUrl'=> false,
								'href'=> '/exportpc',
								'class'=> 'glpipc',
								'needLog'=> true,
								'lv'=> (int)(3),
							],
							'exportuser'=> [
								'ico'=>'glpipc',
								'tag'=> 'a',
								'url'=> 'exportuser',
								'content'=> 'ExportUser',
								'hiddenIfUrl'=> false,
								'href'=> '/exportuser',
								'class'=> 'glpipc',
								'needLog'=> true,
								'lv'=> (int)(3),
							],
						]
					],
					'plus'=> [
						'ico'=>'plus',
						'tag'=> 'span',
						'url'=> '',
						'content'=> 'plus',
						'hiddenIfUrl'=> false,
						'href'=> '',
						'class'=> 'plus',
						'needLog'=> true,
						'lv'=> (int)(3),
						'others'=> [
							'github'=> [
								'ico'=>'github',
								'tag'=> 'a',
								'url'=> 'github',
								'content'=> 'Github',
								'class'=> 'github',
								'hiddenIfUrl'=> false,
								'href'=> 'https://github.com/patobeur/assetstracker',
								'target'=> '_github',
								'needLog'=> true,
								'lv'=> (int)(3),
							],
							'plus'=> [
								'ico'=>'plus',
								'tag'=> 'span',
								'url'=> '',
								'content'=> 'plus',
								'hiddenIfUrl'=> false,
								'href'=> '',
								'class'=> 'plus',
								'needLog'=> true,
								'lv'=> (int)(3),
								'others'=> [
									'three'=> [
										'ico'=>'three',
										'tag'=> 'a',
										'url'=> 'three',
										'content'=> '3D',
										'hiddenIfUrl'=> false,
										'href'=> '/three',
										'class'=> 'three',
										'needLog'=> true,
										'lv'=> (int)(3),
									]
								]
							],
						]
					],
				]
			],
			'profile'=> [
				'ico'=>'profile',
				'tag'=> 'a',
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
				'ico'=>'login',
				'tag'=> 'a',
				'url'=> 'login',
				'content'=> 'LogIn',
				'class'=> 'login',
				'hiddenIfUrl'=> ['login'],
				'href'=> '/login',
				'needLog'=> false,
				'needUnlog'=> true,
				'lv'=> (int)(0),
				'classRight'=>true,
			],
			'interface'=> [
				'ico'=>'interface',
				'tag'=> 'a',
				'url'=> 'interface',
				'content'=> 'Options',
				'hiddenIfUrl'=> false,
				'href'=> '/interface',
				'class'=> 'interface',
				'needLog'=> true,
				'lv'=> (int)(1),
				'classHideContent'=>true,
			],
			'logout'=> [
				'ico'=>'logout',
				'tag'=> 'a',
				'url'=> 'logout',
				'content'=> 'Déconnexion',
				'class'=> 'deco',
				'hiddenIfUrl'=> false,
				'href'=> '/logout',
				'needLog'=> true,
				'lv'=> (int)(0),
				'classHideContent'=>true,
			],
		];
	}


    public function addNavigation($pageToDisplay, $url): string
    {
        $this->url = $url;
        $this->pageToDisplay = $pageToDisplay;
        $this->navigation = $this->generateMenu($this->menus);

        $this->view = str_replace("{{topnav}}", $this->navigation, $this->view);
        return str_replace("{{topNavViewB}}", $this->view, $this->pageToDisplay);
    }
    private function generateMenu(array $menus, int $level = 0): string
    {

		$html = "";
		$close = false;
		if($level > 0) {
			$html = '<ul class="ul-lv'.$level.'">';
			$close = true;
		}
        foreach ($menus as $key => $menu) {
			// var_dump($key);	
			
			$tag = $menu['tag'] ?? 'a';
			$needLog = $menu['needLog'] ?? false;
			$needUnlog = $menu['needUnlog'] ?? false;
			$requestUrl = $menu['url'] ?? false;
			$requestedLv = $menu['lv'] ?? false;
			$requestClass = $menu['class'] ?? '';
			$itemIco = $menu['ico'] ?? '';

			$requestHref = $menu['href'] ? ' href="'.$menu['href'].'"' : '' ;
			$requestHrefTarget = ($menu['href'] && isset($menu['target']) && $menu['target']) ? ' target="'.$menu['target'].'"' : '' ;
			$requestTitle = $menu['content'] ? ' title="'.$menu['content'].'"' : '' ;


			$requestcontent = $menu['content'];
			$url = $this->url;

			$hiddenRules = $menu['hiddenIfUrl'] ?? [];
			$displayItem = true;
			if ($hiddenRules && count($hiddenRules)>0){
				foreach ($hiddenRules as $pageName) if($url === $pageName) $displayItem = false;
			}
			if(
				$displayItem && 
				!($needLog && !isset($_SESSION['user'])) && 
				!($needUnlog && isset($_SESSION['user'])) &&
				!(isset($menu['lv']) && isset( $_SESSION['user']) && isset( $_SESSION['user']['lv']) && $_SESSION['user']['lv'] < $requestedLv)
			){
				// class
				$liClass = ' class="item li-lv'.$level;
					if($url===$requestUrl) $liClass .=" on";
					if($requestClass != '') $liClass .=" ".$requestClass;
					if(isset($menu['classRight'])) $liClass .=' right';
					if(isset($menu['classHideContent']) && $menu['classHideContent'])  $liClass .=' hidea';
				$liClass .= '"';
	
				//title onclick href
				if(($menu['href'] && isset($menu['target']) && $menu['target']!='')){
					$onclick = $menu['href'] ? " window.open('".$menu['href']."')" : "";
				}
				elseif ($menu['href'] && !isset($menu['target'])){
					$onclick = $menu['href'] ? " onclick=\"location.href='".$menu['href']."'\"" : "";
				}
				else {
					$onclick = "";
				}
	

				$html .= '<li'.$requestTitle.$onclick.$liClass.'>';
				$html .= '<i alt="'.$requestClass.'" class="ico fico-'.$itemIco.'"></i>';
				switch ($tag) {
					case 'a':						
						$html .= "<a" .$requestTitle. $requestHref.$requestHrefTarget. ">" . $requestcontent . "</a>";
						break;
					case 'span':						
						$html .= "<span".$requestTitle.">". $requestcontent . "</span>";
						break;
					default:						
						$html .= "<span></span>";
						break;
				}

				if (isset($menu['others']) && is_array($menu['others'])) {
					$html .= $this->generateMenu($menu['others'], $level + 1);
				}
				$html .= "</li>";
			}
        }
        $html .= $close ? "</ul>" : "";
        return $html;
    }









	// public function addNavigation($pageToDisplay,$url): string
	// {
	// 	$this->url= $url;
	// 	$this->pageToDisplay= $pageToDisplay;
	// 	$this->navigation = $this->getTopNav();

	// 	$this->view = str_replace(search: "{{topnav}}",replace: $this->navigation,subject: $this->view);

	// 	$this->pageToDisplay = str_replace(search: "{{topNavViewB}}",replace: $this->view, subject: $this->pageToDisplay);
	// 	return $this->pageToDisplay;
	// }
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
