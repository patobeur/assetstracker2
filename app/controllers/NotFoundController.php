<?php

	namespace app\controllers;

	class NotFoundController
	{

		private $boule = '';
		private $contents = [
			1=> [
				'TITLE' => "c'est pourquoi ?",
				'CONTENT'   => "404 - Page not found"
			],
			2=> [
				'TITLE' => "c'est ou ?",
				'CONTENT'   => 'Unknow Page'
			]
		];
		private $content = 	[];
		
		public function showIndex($boule=1)
		{	
			$this->content = $this->contents[$boule];
			$this->renderView();
			return $this->content;
		}
		// Afficher la vue login avec les erreurs
		private function renderView(){
			$htmlView = file_get_contents(filename: CONFIG['APPROOT'].'app/views/notfound.php');
				
			$htmlView = str_replace('{{TITLE}}', $this->content['TITLE'], $htmlView);
			$htmlView = str_replace('{{CONTENT}}', $this->content['CONTENT'], $htmlView);

			$this->content['CONTENT'] = $htmlView;

		}
	}
