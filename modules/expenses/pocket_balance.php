  <?php require("connect.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="safe.css"> 
      <link rel="stylesheet" href="form.css"> 
    <title>Balance</title>
    <style>
        input{width:50%;padding:3%;margin:3%;}
        form{margin-top:10%;}
        button{margin:2%;}
        p{margin:2%;font-family:arial; font-size:30px;font-weight:bold;}
       
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
        
        <form method="post">
              <p>Actual Balance:
                <?php
            
            $a="select amount from balance
             GROUP BY balance_id
             ORDER BY amount DESC 
             LIMIT 1;";
               $in=$pdo->prepare($a);
                   $in->execute([]);
                   $result=$in->fetch(PDO::FETCH_ASSOC);
                   // $g=$result['0'];
           foreach($result as $s){
            echo  "<span style='color:blue'>$s</span>";
           };
            
             ?>
              
              
            </p>
            <input type="number" placeholder="Recharge Balance:" required autofocus autocomplete="on" name="balance"><br>
            <button name="submit">Recharge</button>
        </form>

          <?php
          
              
           if(isset($_POST['submit'])){
               
             function add(){
                $b= $GLOBALS['s']+$_POST['balance'];
           return $b;
           }
               
            $c=add();
             
                $sql="insert into balance (amount) values(?)";
                 $insert=$pdo->prepare($sql);
                  $insert->execute([$c]);
           }
          
         
          
          
          ?>


</body>
</html>