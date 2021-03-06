<?php
class Website {
	private static $instance = null;
	private $website_script = "<script type=\"text/javascript\" src=\"client/website.js\"></script>";
	private $jquery_script = "<script type=\"text/javascript\" src=\"libs/jquery_all.js\"></script>";
	private $less_script = "<script type=\"text/javascript\" src=\"libs/less-1.1.3.min.js\"></script>";
	private $css = "<link rel=\"stylesheet/less\" type=\"text/css\" href=\"styles.less\">";
	private $title = "<title>NO TITLE SET</title>";
	private $content = "<div class=\"wrapper\"><div class=\"main\"></div></div>";

	public static function getInstance() {
		if(!isset(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {}
	private function __clone() {}

	public function build() {
		$site = "<!DOCTYPE HTML><html><head>" . $this->title . $this->jquery_script . $this->less_script . $this->website_script . $this->css . "</head><body>" . $this->content . "</body></html>";
		echo $site;
	}
}
