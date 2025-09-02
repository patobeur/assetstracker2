<?php
namespace app\core\checkdb;

class CheckInstall
{
	private $dbConfigPath;
	private $installPath;

	private $CheckDb;
	private $Console;

	public function __construct($CheckDb,$Console) {

		$this->CheckDb = $CheckDb;
		$this->Console = $Console;
		$this->dbConfigPath = $this->CheckDb->getDbConfigPath();
		$this->installPath = $this->CheckDb->GetInstallPath();
		$this->checkInstallAndConfig();

	}
	public function checkInstallAndConfig(): void
	{
		$dbConfigExiste = file_exists(filename: $this->dbConfigPath);
		$installExiste = file_exists(filename: $this->installPath);

		if ($dbConfigExiste){
			// dbconfig.php ok
			if ($installExiste){
				// install.php aussi et ce n'est pas normal
				// on check la db voir si il y a des erreur !
				$dbErrors = $this->CheckDb->checkIfDb();


				if(count($dbErrors)>0) {
					// si il y a des erreur on die
					die("L'installation a eu lieu mais la base de donnée n'existe pas. Regardez votre dbConfig ? Attention un fichier install exite encore !");
				}


			}
			else {	
				// Pas d'install.php
				// on check la db voir si il y a des erreur !
				$dbErrors = $this->CheckDb->checkIfDb();
				if(count($dbErrors)>0) {
					// si il y a des erreur on die
					die("L'installation a eu lieu mais la base de donnée n'existe pas. Pour info: il n'y a aucun fichier d'installation.");
				} 
				else {
					// tout est ok !!!
				}
			}
		}
		else {
			// PAS de dbconfig.php
			if ($installExiste){
				// Fichier d'install trouvé alors on lance l'install
				session_destroy();
				header(header: 'Location: /install.php');
				die("Fichier d'install trouvé alors on lance l'install");
			}
			else {	
				// Pas de fichier install trouvé
				// alors !!! on die
				die("Il n'y a pas de fichier config et pas de fichier d'installation !!");
			}
		}

	}
	public function addCheckMessage($lv=0): void
	{
		if ($lv<5) return;

		if ($lv>=5) {

			$dbConfigExiste = file_exists(filename: $this->dbConfigPath);
			$installExiste = file_exists(filename: $this->installPath);

			if(!CONFIG['PROD']){
				$this->Console->addMsg([
					"content"=>"vous êtes en mode : ".(CONFIG['PROD']?'prod':'dev'),
					"title"=>(CONFIG['PROD']?'✅':'ℹ️'),
					"class"=>(CONFIG['PROD']?'succes':'info')
				]);
				if ($dbConfigExiste && $installExiste){
					$this->Console->addMsg([
						"content"=>'Vous devriez éffacer le fichier install ?',
						"title"=>'🚫',
						"class"=>'error'
					]);
				}
				if ($dbConfigExiste && !$installExiste){
					$this->Console->addMsg([
						"content"=>'Installation ok !',
						"title"=>'✅',
						"class"=>'succes'
					]);
				}

				if (!$dbConfigExiste && $installExiste){
					$this->Console->addMsg([
						"content"=>"Vous ne devriez pas voir ce message pour cause d'Installation en cours.",
						"title"=>'🚫',
						"class"=>'alerte'
					]);
				}
				if (!$dbConfigExiste && !$installExiste){
					$this->Console->addMsg([
						"content"=>"Allo ! Youston ? on a un problème ?? (ni dbConfig ni install)",
						"title"=>'🚫',
						"class"=>'alerte'
					]);
				}
			}
			else {
				if ($dbConfigExiste && $installExiste){
					$this->Console->addMsg([
						"content"=>"Un probleme est survenu (dbConfigExiste && installExiste) ?? Contactez votre Administrateur !",
						"title"=>'🚫',
						"class"=>'alerte',
						"birth"=>date("h:i:s")
					]);
				}
			}




		}
	}
}