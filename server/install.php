<?php
class Install {
	private static $instance = null;
	private $install_script = "<script type=\"text/javascript\" src=\"client/installation.js\"></script>";
	private $jquery_script = "<script type=\"text/javascript\" src=\"libs/jquery_all.js\"></script>";
	private $less_script = "<script type=\"text/javascript\" src=\"libs/less-1.1.3.min.js\"></script>";
	private $css = "<link rel=\"stylesheet/less\" type=\"text/css\" href=\"install_styles.less\">";
	private $title = "<title>NO TITLE SET</title>";
	private $content = "<div class=\"wrapper\"><div class=\"main\"><h1 class=\"title\"></h1><div class=\"install-content\"></div><div class=\"install-error-msg\"></div><div class=\"button-lane\"></div></div></div>";

	public static function getInstance() {
		if(!isset(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {}
	private function __clone() {}

	public function startInstallation() {
		$site = "<!DOCTYPE HTML><html><head>" . $this->title . $this->jquery_script . $this->less_script . $this->install_script . $this->css . "</head><body>" . $this->content . "</body></html>";
		echo $site;
	}
}
