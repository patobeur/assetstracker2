<?php

	namespace app\core;

	class Console
	{
		private $defaultPage;
		private $msg = [];
		private $msgOnce = [];
		private $active = false;
		private $vue = '<div class="console hidden" id="console">{{console}}</div>';

		public function __construct($active=false) {
			if(!isset($_SESSION['msg'])){
				$_SESSION['msg'] = [];
			}
			$this->active = $active ?? false;
		}
	
		public function addMsgSESSION($datas): void {
			$datas['birth'] = date("H:i:s");
			$_SESSION['msg'][] = $datas;
		}
		public function addMsg($datas): void {
			$datas['birth'] = date("H:i:s");
			$this->msg[] = $datas;
		}

		public function addConsole($defaultPage): string {
			$this->defaultPage = $defaultPage ?? null;
			if ($this->active) {
				// merge des deux array msg
				$this->msg = array_merge($this->msg,$_SESSION['msg']);

				if (count(value: $this->msg)>0){
					$contents = '';
					for ($i=0; $i < count(value: $this->msg) ; $i++) {
						$contents .= "{{console{$i}}}";
					}
					$this->vue = str_replace(search: "{{console}}",replace: $contents,subject: $this->vue);
	
					for ($i=0; $i < count(value: $this->msg) ; $i++) {
						$class = ' class="'.($this->msg[$i]['class'] ?? '').'"';
						$pack = "<p{$class}>".($this->msg[$i]['title']??'?').": ";
						$pack .= "".$this->msg[$i]['content'];
						$pack .= " (".$this->msg[$i]['birth'].")</p>";
						
						$this->vue = str_replace(search: "{{console{$i}}}",replace: $pack,subject: $this->vue);
					}
				}
				else {
					$this->vue = str_replace(search: "{{console}}",replace: "",subject: $this->vue);
				}
				$this->defaultPage = str_replace(search: "{{console}}",replace: $this->vue, subject: $this->defaultPage);
			} else {
				$this->defaultPage = str_replace(search: "{{console}}",replace: '', subject: $this->defaultPage);
			}
			return $this->defaultPage;
		}
	}