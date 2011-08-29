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
		$db_info_path = $_SERVER["DOCUMENT_ROOT"] . "/cms/libs/db_info.php";
		require($db_info_path);

		$mysqli = new mysqli($db_server, $db_username, $db_password, $db_database);
		if ($mysqli->connect_error) {
			$response = false;
		} else {
			$result = $mysqli->query("SELECT * FROM settings WHERE id=1");
			if ($result) {
				$data = $result->fetch_assoc();

				if ($data["enabled"] == 1) {
					$response = true;
				} else {
					$response = false;
				}
				$result->free();
			} else {
				$response = false;
			}
		}

		$mysqli->close();

		return $response;
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
							VALUES('default', 'The default theme for the cms. A simple, yet elegant theme with focus on the content.', 'head;top-nav;column-1', 'default'),
								  ('simple', 'This theme consists of two columns. Left is a navigation, right the content. There is a header on top.', 'head;side-nav;column-2', 'simple');");
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
			$mysqli->close();
			return json_encode($response);
		}

		//Settings for the page
		$mysqli->query("CREATE TABLE settings (
							id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
							layout INT,
							enabled BOOLEAN)
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
		$db_info_path = $_SERVER["DOCUMENT_ROOT"] . "/cms/libs/db_info.php";
		require($db_info_path);

		$assoc_data = json_decode($data, true);
		$data_keys = array_keys($assoc_data);

		$mysqli = new mysqli($db_server, $db_username, $db_password, $db_database);

		$tbl_data = "";
		$tbl_values = "";
		if (count($data_keys) !== 1) {
			for ($i = 0; $i < count($data_keys) - 1; $i++) {
				$tbl_data .= $data_keys[$i] . ", ";
				$tbl_values .= "'" . $assoc_data[$data_keys[$i]] . "', ";
			}
		}
		$tbl_data .= "" . $data_keys[count($data_keys) - 1];
		$tbl_values .= "'" . $assoc_data[$data_keys[count($data_keys) - 1]] . "'";

		$query = "INSERT INTO " . $table . " (" . $tbl_data . ") VALUES (" . $tbl_values . ");";
		$mysqli->query($query);
		if ($mysqli->connect_error) {
			$response = array("success" => false, "msg" => array("errno" => $mysqli->connect_errno, "error" => $mysqli->connect_error));
		} else {
			$response = array("success" => true);
		}

		$mysqli->close();

		return json_encode($response);
	}
}
