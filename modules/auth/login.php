<?php
  //senssion start
            session_start();
          require("connect.php");
           if(isset($_POST['submit'])){
             $a=$_POST['username'];
             $b=$_POST['password'];
            
            $sql="select username,password FROM users WHERE username=:username AND password=:password";

              $stmt=$pdo->prepare($sql);
             $stmt->execute([':username'=>$a,':password'=>$b]);
            

      if ($stmt->rowCount()===1){
         $_SESSION['username']=$a;

          header("Location:index.php");
              echo "<script>alert('Login successfull')</script>";
        exit();
          }

     else{
        echo"<script>alert('Invalid username/pasword!')</script>";}
  }?>



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
   
     <h2>Login</h2>
     <p id="remark"></p>
     <label >Username</label>
    <input type="text" placeholder="Type your username" name="username"><br>
      <label for="" >Password</label>
    <input type="text" placeholder="Type your Password" name="password">
  
    <div class="right"> <a href="forgot_password.php">Forgot password</a></div>
       <button name="submit">LOGIN</button>
       
      <div class="right_normal"> <a href="register.php">SIGN UP</a></div>

    </form>
    
</body>
</html>