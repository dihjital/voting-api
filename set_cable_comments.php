<?php

  # Record cable recorded comments

  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Set date in the past to prevent caching
  header("Content-Type: application/json; charset=UTF-8");

  session_start();

  define('BASE_DIR', '/var/www/html/cables');
  require('/var/www/html/cables/etc/cables.cfg');

  require(SMARTY_DIR.'Smarty.class.php');
  require(CLASS_DIR.'/db_mysql.class');
  require(CLASS_DIR.'/ticket.class.php');
  require(CLASS_DIR.'/session.class.php');
  require(CLASS_DIR.'/history.class.php');
  require(CLASS_DIR.'/login.class.php');

  # Read all data from request body (e.g. JSON data)
  $json = file_get_contents('php://input');
  $vars = (array) json_decode($json);

  # Read all POST and GET vars and store them in $vars
  foreach ($_POST as $param_name => $param_val) {
    $vars["$param_name"] = $param_val;
  }

  foreach ($_GET as $param_name => $param_val) {
    $vars["$param_name"] = $param_val;
  }

  if (!isset($vars)) $vars = array();

  $my_smarty  = new Smarty;
  $my_db      = new Database;

  $my_ticket  = new Ticket;
  $my_session = new Session($my_db, $my_smarty, $vars);
  $my_history = new History($my_db, $my_smarty, $vars);
  $my_login   = new LLogin($my_db, $my_smarty, $vars);

  if (!($user = $my_session->verifySession())) {
    exit;
  }

  // if called from JS .ajax then it is always UTF-8 encoded ...
  if (array_key_exists('cable_comment', $vars) && $vars['cable_comment']) {
    if (preg_match("//u", $vars['cable_comment'])) {
      $vars['cable_comment'] = iconv("UTF-8", "ISO-8859-2", $vars['cable_comment']);
    }
    $cable_comment = $my_db->escape_query_string($vars['cable_comment']);
  }

  if (array_key_exists('cable_id', $vars) && $vars['cable_id']) {

	$cable_id = $my_db->escape_query_string($vars['cable_id']);
	$q_str = "SELECT * FROM cable_comments WHERE cable_id = '$cable_id'";

	if ($my_db->query($q_str)) {

		$result = $my_db->getResults();
		$temp = array_pop($result);

		if (!isset($cable_comment)) {

			$q_str = "DELETE FROM cable_comments WHERE cable_id = '$cable_id'";

			if (!$my_db->exec($q_str)) {

                                $vars['error'] .= 'ADATBÁZIS HIBA: '.$my_db->getErrorMessage();

                        }

			$my_history->add('delete', "Kábelhez ($cable_id) kapcsolódó megjegyzés törlése");

		} elseif ($cable_comment != $temp->comment) {

			$q_str = "UPDATE cable_comments SET comment = '$cable_comment' WHERE cable_id = '$cable_id'";

			if (!$my_db->exec($q_str)) {

	               	        $vars['error'] .= 'ADATBÁZIS HIBA: '.$my_db->getErrorMessage();

                	}

			$my_history->add('modify', "Kábelhez ($cable_id) kapcsolódó megjegyzés módositása");

		}

	} else {

		if ($my_db->getErrorMessage()) {
        		$vars['error'] .= 'ADATBÁZIS HIBA: '.$my_db->getErrorMessage();
		} elseif ($cable_comment) {

			$q_str = "INSERT INTO cable_comments VALUES ('$cable_id', '$cable_comment')";

                        if (!$my_db->exec($q_str)) {

                                $vars['error'] .= 'ADATBÁZIS HIBA: '.$my_db->getErrorMessage();

                        }

			$my_history->add('add', "Kábelhez ($cable_id) kapcsolódó megjegyzés felvitele");

		}

  	}

  }

  $my_db->disconnect();

  if (array_key_exists('modal', $vars) && $vars['modal'] == 'newCableModal') {
    if (array_key_exists('error', $vars) && $vars['error']) {
      echo json_encode(['status' => 'error', 'error_message' => $vars['error']], JSON_INVALID_UTF8_IGNORE);
    } else {
      echo json_encode(['status' => 'success', 'message' => 'Kábelhez kapcsolódó megjegyzés felvitele sikeres'], JSON_INVALID_UTF8_IGNORE);
    }
  } else {
    $redirect_url = '/n_cable.php';
    if (array_key_exists('error', $vars) && $vars['error']) {
      $redirect_url .= '?error='.urlencode($vars['error']);
    }
    header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).$redirect_url);
  }

?>
