<?php
	namespace app\controllers;
	
	class Codebare {
		private $view = '<img src="{{image}}" alt="barcode" />';
		public function __construct() {
		}
		//test_ean13.php
		public function getCodebare() {
			// barecode/code/test_code128.php?text=123456789
			if(isset($_GET['text']) && !empty($_GET['text']) && strlen($_GET['text'])){
				// $text = '/packbarecode/code/test_code128.php?text='.trim($_GET['text']);
				$text = '/packbarecode/code/test_ean13.php?text='.trim($_GET['text']);
				$this->view = str_replace("{{image}}",$text, $this->view);
				echo $this->view;
			}
			die();
		}
		
	}
