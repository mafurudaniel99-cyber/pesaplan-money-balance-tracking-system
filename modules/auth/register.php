<?php require("connect.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form method="POST">
   
     <h2>Sign Up</h2>
     <p id="remark">    <?php 
    if(isset($_POST['submit'])){
              $c=$_POST['username'];
               $b=$_POST['password'];
           $f="Insert into users(username,password) values(?,?)";
             $insert=$pdo->prepare($f);
                 if( $insert->execute([$c,$b])){
                    echo "Registered";
                 }
                 else{echo "Something went wrong!.";}
           
    }
    
    ?></p>
     <label >Username</label>
    <input type="text" placeholder="Create Your username" name="username"><br>
      <label for="" >Password</label>
    <input type="text" placeholder="Create Strong password" name="password">
  
    
       <button name="submit">Register</button>
       
      <div class="right_normal"> <a href="login.php">Login</a></div>

    </form>

    
</body>
</html>