<?php
$host="localhost";
$port="3306";
$user="root";
$db="Safespend";
$pass="";

try{
 $pdo=new PDO("mysql:host=$host;dbname=$db;charset=utf8",$user,$pass);
 $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);}

catch(PDOException $e){ 
  die("Database connection faileed:".$e->getMessage());
  
  }