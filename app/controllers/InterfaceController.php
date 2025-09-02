<?php
	namespace app\controllers;
	
	class InterfaceController {
		private $content = [
				'TITLE' => "Interface",
				'CONTENT'   => ''
		];

		public function __construct() {
		}
        
		public function interfaceHandler($boule=1)
		{	
			$this->renderView();
			return $this->content;
		}
		
		private function renderView(){
			$htmlView = file_get_contents(filename: CONFIG['APPROOT'].'app/views/interface.php');
				
			$htmlView = str_replace('{{TITLE}}', $this->content['TITLE'], $htmlView);
			$htmlView = str_replace('{{CONTENT}}', $this->content['CONTENT'], $htmlView);

			$this->content['CONTENT'] = $htmlView;

		}
		
    }