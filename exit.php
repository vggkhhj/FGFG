<?php
	require_once('func.php');

	user_delToken((int)$_SESSION['user']['id']);
	unset($_SESSION['user']);
  
	header("Location: login.php");
