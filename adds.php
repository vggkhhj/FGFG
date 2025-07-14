<?php
  require_once ('common.php');
  if(empty($_GET['add'])) header('Location: index.php');

  if (file_exists("adds/".$_GET['add'].".php")) {
    include ("adds/".$_GET['add'].".php");
    include ($MA_theme);
  } else header('Location: index.php');

