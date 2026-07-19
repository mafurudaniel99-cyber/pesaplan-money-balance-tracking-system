<?php  require("connect.php");     ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items</title>
     <link rel="stylesheet" href="safe.css"> 
     <link rel="stylesheet" href="form.css"> 
    <style>
        body{background:lightblue;}
        #div{
            width:80%;
             height:60vh;
            overflow:auto;
        display:flex;
    flex-direction:column;
     margin-left:10%;
     margin-top:10%;
     border:2px solid blue; 
    background:white;
    position:fixed;
    top:5%;
}
    
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
<div id="div">

    <h1 style="margin-left:40%;">Add Usage</h1>
     <form method="POST">
   
    <div id="form_div">
              <section><p id="remark"><?php
          
              
           if(isset($_POST['submit'])){
                $item=$_POST['item'];
                 $amount=$_POST['amount'];

             
                $sql="insert into items (name,amount) values(?,?)";
                 $insert=$pdo->prepare($sql);
                 
                  if( $insert->execute([$item,$amount])){
                    echo "Item added";
                  }
                  else{
                    echo "Try again!";
                  }
           }?>

</p>
     <label >Item</label><br>
    <input type="text" placeholder="What item bought?" name="item" required><br>
    </section>
    <section>
      <label for="" >Amount</label><br>
    <input type="number" placeholder="Enter amount" name="amount" required><br>
      </section>
    </div>
       <button name="submit">Add</button><br>
   

    </form>

</div>
</body>
</html