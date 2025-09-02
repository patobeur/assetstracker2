<?php
	namespace app\controllers;

	class IndexController
	{
		private $content = 	[
			'TITLE' => "Accueil title",
			'CONTENT'   => "<h1>Accueil</h1>"
		];
		
		public function showIndex()
		{
			return $this->content;
		}
	}