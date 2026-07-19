<?php 
    session_start();
    include("connect.php");
    if(!isset($_SESSION['username'])){
        header("Location:login.php");
        exit();
    }?>

<!DOCTYPE html> 
<html> 
<head> 
    <title>Homepage</title> 

    <link rel="stylesheet" href="safe.css"> 
       <style>
        input{width:50%;padding:1%; margin-right:none; 
        border-left:1px solid blue;border-right-style:none;
        border-radius:20px;}

        button{margin-left:none;background-color:none;
        border-left-style:none; font-size:30px;color:none;}

        form{display:flex;flex-direction:row;height:0.5%;
        justify-content:space-evenly; margin-top:none;
         width:30%;margin-left:40%;border-radius:20px;}
       </style>
</head> 
<body> 
 
<header> 
    <h1>SAFESPEND</h1> 
 
    <nav> 
        <a href="index.php">Home</a> 
        <a href="add_item.php">Add</a> 
         <a href="calculator.php">Calculator</a>
        <a href="pocket_balance.php">Balance</a> 
        <a href="logout.php">Logout</a>
    </nav> 
</header> 
 
<section class="hero" style="margin-top:10%;"> 
    <h2>Track your life expense</h2> 
    
    <p>Retrive your dail usage, measure your spending habit and recorganize the deviations.</p> 
</section> 
 <form   method="POST"   >
            
            <input type="search" name="q" class="search_input" 
            placeholder="Search anything..." autocomplete="on" autofocus required>
            <button name="submit" style="background-color:none;">🔍</button>
        </form>
 
</body> 
</html> 
 
