<?php

class DB {
	private static $instance = null;

	public static function getInstance() {
		if(!isset(self::$instance)) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {}
	private function __clone() {}

	public function getState() {
		return false;
	}

	public function checkCredentials() {
		$db_info_path = $_SERVER["DOCUMENT_ROOT"] . "/cms/libs/db_info.php";
		require($db_info_path);

		$mysqli = new mysqli($db_server, $db_username, $db_password, $db_database);
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
		} else {
			$response = array("success" => true);
		}
		$mysqli->close();

		return json_encode($response);
	}

	public function setup() {
		$db_info_path = $_SERVER["DOCUMENT_ROOT"] . "/cms/libs/db_info.php";
		require($db_info_path);

		$mysqli = new mysqli($db_server, $db_username, $db_password, $db_database);

		//Create the table for the user data
		$mysqli->query("CREATE TABLE users (
							id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
							username VARCHAR(255) NOT NULL UNIQUE,
							password BLOB NOT NULL,
							email VARCHAR(255) NOT NULL UNIQUE,
							role ENUM('admin', 'author') NOT NULL)
							ENGINE = InnoDB,
							CHARACTER SET = utf8,
							COLLATE = utf8_general_ci;");
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
			$mysqli->close();
			return json_encode($response);
		}

		//Create the table for all the available themes
		$mysqli->query("CREATE TABLE themes (
							id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
							theme_name VARCHAR(255) NOT NULL UNIQUE,
							theme_description VARCHAR(255) NOT NULL,
							theme_layout VARCHAR(255) NOT NULL,
							theme_path VARCHAR(255) NOT NULL)
							ENGINE = InnoDB,
							CHARACTER SET = utf8,
							COLLATE = utf8_general_ci;");
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
			$mysqli->close();
			return json_encode($response);
		}

		//Fill the themes table with some themes
		$mysqli->query("INSERT INTO themes (theme_name, theme_description, theme_layout, theme_path)
							VALUES('default', 'The default theme for the cms. A simple, yet elegant theme with focus on the content.', 'head;top-nav;column-1;column-2', 'default'),
								  ('simple', 'This theme consists only of one column and a top navigation.', 'head;top-nav;column-1', 'simple');");
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
			$mysqli->close();
			return json_encode($response);
		}

		//Settings for the page
		$mysqli->query("CREATE TABLE settings (
							id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
							layout INT)
							ENGINE = InnoDB,
							CHARACTER SET = utf8,
							COLLATE = utf8_general_ci;");
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
			$mysqli->close();
			return json_encode($response);
		}

		//Content (only text) of the page is stored in this table
		$mysqli->query("CREATE TABLE content (
							id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
							data TEXT,
							published TINYINT(1))
							ENGINE = InnoDB,
							CHARACTER SET = utf8,
							COLLATE = utf8_general_ci;");
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
			$mysqli->close();
			return json_encode($response);
		}

		//Links of the page are stored here
		$mysqli->query("CREATE TABLE links (
							id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
							name VARCHAR(255),
							type ENUM('external', 'internal', 'file'),
							description VARCHAR(255),
							ref VARCHAR(255))
							ENGINE = InnoDB,
							CHARACTER SET = utf8,
							COLLATE = utf8_general_ci;");
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
			$mysqli->close();
			return json_encode($response);
		}

		//Layout. Which content or link is on which position. weight is also added here.
		$mysqli->query("CREATE TABLE layout (
							id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
							position VARCHAR(255),
							type ENUM('links', 'content'),
							weight INT(4),
							data_id INT)
							ENGINE = InnoDB,
							CHARACTER SET = utf8,
							COLLATE = utf8_general_ci;");
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
			$mysqli->close();
			return json_encode($response);
		}

		$mysqli->close();

		return json_encode(array("success" => true));
	}

	public function createAdminUser($username, $password, $email) {
		$db_info_path = $_SERVER["DOCUMENT_ROOT"] . "/cms/libs/db_info.php";
		require($db_info_path);

		$mysqli = new mysqli($db_server, $db_username, $db_password, $db_database);
		$mysqli->query("INSERT INTO users (username, password, email, role)
							VALUES('" . $username . "','" . $password . "', '" . $email . "','admin');");
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
		} else {
			$response = array("success" => true);
		}

		$mysqli->close();
		return json_encode($response);
	}

	public function select($table, $id) {
		$db_info_path = $_SERVER["DOCUMENT_ROOT"] . "/cms/libs/db_info.php";
		require($db_info_path);

		$mysqli = new mysqli($db_server, $db_username, $db_password, $db_database);

		if ($id === null) {
			$result = $mysqli->query("SELECT * FROM " . $table . ";");
			if ($mysqli->connect_error) {
				$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
				$result->free();
				$mysqli->close();
				return json_encode($response);
			}
		} else {
			$result = $mysqli->query("SELECT * FROM " . $table . " WHERE id=" . $id . ";");
			if ($mysqli->connect_error) {
				$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
				$result->free();
				$mysqli->close();
				return json_encode($response);
			}
		}

		$data = array();
		while($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
		$result->free();
		$response = array("success" => true, "data" => $data);

		$mysqli->close();

		return json_encode($response);
	}

	public function insert($table, $data) {
		$assoc_data = json_decode($data, true);
		$data_keys = array_keys($assoc_data);

		$db_info_path = $_SERVER["DOCUMENT_ROOT"] . "/cms/libs/db_info.php";
		require($db_info_path);

		$mysqli = new mysqli($db_server, $db_username, $db_password, $db_database);

		$tbl_data = "";
		$tbl_values = "";
		for ($i = 0; $i < count($data_keys); $i++) {
			$tbl_data .= $data_keys[$i] . ", ";
			$tbl_values .= "'" . $assoc_data[$data_keys[$i]] . "', ";
		}
		$tbl_data .= "" . $data_keys[count($data_keys)];
		$tbl_values .= "'" . $assoc_data[$data_keys[count($data_keys)]] . "'";

		$query = "INSERT INTO " . $table . " (" . $tbl_data . ") VALUES (" . $tbl_values . ");";
		$mysqli->query($query);
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
		} else {
			$response = array("success" => true);
		}

		$result->free();
		$mysqli->close();

		return json_encode($response);
	}
}
