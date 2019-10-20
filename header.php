<?php
 
// header.php 
// 20191019 1823
// www.criticalinfrastructureprotector.com 
// CIP Engine by ProactivePaul
 
// *****************************************************************************************************************************
// 0000TITLE
 
// do not specify the title in this script - the title is determined by the script that required header.php
 
//$title              = "header";
 
 $thisScript         = "header.php";
  
// *****************************************************************************************************************************
// 0011HOUSEKEEPING
 
//require ('config.php');
//require ('commonfns.php'); 
 
// *****************************************************************************************************************************
// 1111VARIABLES SET OR RESET 
 
$errMsg        = "";
$navBarString  = ""; 
$navBar001     = "<P>navBar place holder";
$navBar002     = "";
$navBar003     = "";
$echoID        = 0;
 
// *****************************************************************************************************************************
// 2222VALIDATE
 
// *****************************************************************************************************************************
// 3333DATABASE WORK
 
// *****************************************************************************************************************************
// 4444PHP WORK


$navBar001 = $navBarString;
 
//$navBar002 = "becomes equal to something that we want to echo";
//$navBar003 = "at the same time we probably want to make navBar001 become equal to the empty string";
 
// *****************************************************************************************************************************
// 5555HTML IS ECHOED

echo "<!DOCTYPE html>
<html lang='en-GB'>
 
<head>
 
<link rel='stylesheet' type='text/css' href='focuscommon.css'>
 
<title>
$title
</title>
 
</head>
 
<body>
 
 ";
 
// php ends
 ?>   