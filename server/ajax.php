<?php
$db_connection_path = $_SERVER["DOCUMENT_ROOT"] . "/cms/server/db_connection.php";
require($db_connection_path);

$db = DB::getInstance();

if (isset($_POST["action"])) {
	$action = $_POST["action"];

	switch($action) {
		case "check_db_credentials":
			$response_value = $db->checkCredentials();
			break;
		case "setup_db":
			$response_value = $db->setup();
			break;
		case "create_admin":
			if (!isset($_POST["username"]) && !isset($_POST["password"]) && !isset($_POST["email"])) {
				$response_value = json_encode(array("success" => false, "msg" => array("error" => "No valid username, password or email specified", "errno" => "")));
			} else {
				$response_value = $db->createAdminUser($_POST["username"], $_POST["password"], $_POST["email"]);
			}
			break;
		case "select":
			if (!isset($_POST["table"])) {
				$response_value = json_encode(array("success" => false, "msg" => array("error" => "No table for select statement provided", "errno" => "")));
			} else {
				if (!isset($_POST["id"])) {
					$response_value = $db->select($_POST["table"], null);
				} else {
					$response_value = $db->select($_POST["table"], $_POST["id"]);
				}
			}
			break;
		case "insert":
			if (!isset($_POST["table"])) {
				$response_value = json_encode(array("success" => false, "msg" => array("error" => "No table for insert statement provided", "errno" => "")));
			} else {
				if (!isset($_POST["data"])) {
					$response_value = json_encode(array("success" => false, "msg" => array("error" => "No data to insert provided", "errno" => "")));
				} else {
					$response_value = $db->insert($_POST["table"], $_POST["data"]);
				}
			}
	}

	echo $response_value;
}
