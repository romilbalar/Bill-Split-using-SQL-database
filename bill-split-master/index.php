<?php
if (!isset($_SESSION)) {
    session_start();
}

$servername = "localhost";
$db_user    = "root";
$db_pass    = "";
$db_name    = "bill_split";


$username = $_SESSION['username'];

if(isset($_POST['logout'])){
	session_destroy();
	header("Location:landing.php");
}


try {
    $conn = new PDO("mysql:host=$servername;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute(array(
        $username
    ));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $name = $row['name'];
    
    
    if (isset($_POST['add_friend'])) {
        $friend_username = $_POST['friend_username'];
        if (strcmp($username, $friend_username) == 0) {
            throw new PDOException("Invalid username");
        }
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
        $stmt->execute(array(
            $friend_username
        ));
        // $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $stmt->rowCount();
        if ($count == 1) {
            if (strcmp($username, $friend_username) > 0) {
                $temp            = $username;
                $username        = $friend_username;
                $friend_username = $temp;
            }
            $stmt = $conn->prepare("INSERT INTO friends (user1, user2) VALUES (?, ?)");
            $stmt->execute(array(
                $username,
                $friend_username
            ));
        }
        
        header("Refresh:0");
        
    }
    
    if (isset($_POST['add_bill'])) {
        $description = $_POST['description'];
        $amount      = $_POST['amount'];
        $username = $_SESSION['username'];
        
        // echo $description;
        $stmt = $conn->prepare("INSERT INTO bills(description, amount, date, created_by) values(?, ?, NOW(), ?)");
        $stmt->execute(array(
            $description,
            $amount, 
            $username

        ));
        $bill_id = $conn->lastInsertId();
        
        
        
        // echo "hellp";
        
        $balance_pos = array();
        $balance_neg = array();
        $i           = 0;
        $sum         = 0;
        
        
        
        foreach ($_POST['person'] as $person) {
            // $record = array('person'=>$person, 'topay'=>$_POST['topay'][$i], 'paid'=>$_POST['paid'][$i]);
            // array_push($involved, $person=>$_POST['paid'][$i]-$_POST['topay'][$i]);
            
            if ($_POST['topay'][$i] - $_POST['paid'][$i] < 0) {
                array_push($balance_pos, array(
                    $person,
                    $_POST['paid'][$i] - $_POST['topay'][$i]
                ));
                $sum += $_POST['paid'][$i] - $_POST['topay'][$i];
            } else if ($_POST['topay'][$i] - $_POST['paid'][$i] > 0) {
                array_push($balance_neg, array(
                    $person,
                    $_POST['topay'][$i] - $_POST['paid'][$i]
                ));
            }
            
            $i++;
        }
        
        // asort($balance_pos);
        // asort($balance_neg);
        // echo array_key_first($balance_neg);
        
        // print_r($balance_neg);
        // print_r($balance_pos);
        
        
        
        
        
        while ($sum > 0) {
            // print_r($balance_pos);
            
            // print_r($balance_neg);
            
            
            $first  = array_pop($balance_pos);
            $second = array_pop($balance_neg);
            
            if ($first[1] >= $second[1]) {
                // echo ($first[0] . $second[0] . $second[1]);
                $stmt = $conn->prepare("INSERT INTO tuples(bill_id, from_user, to_user, amount) values(?,?,?,?)");
                $stmt->execute(array(
                    $bill_id,
                    $first[0],
                    $second[0],
                    $second[1]
                ));
                $sum       = $sum - $second[1];
                $first[1]  = $first[1] - $second[1];
                $second[1] = 0;
                
                
                
            } else {
                // echo ($first[0] . $second[0] . $first[1]);
                $stmt = $conn->prepare("INSERT INTO tuples(bill_id, from_user, to_user, amount) values(?,?,?,?)");
                $stmt->execute(array(
                    $bill_id,
                    $first[0],
                    $second[0],
                    $first[1]
                ));
                $sum       = $sum - $first[1];
                $second[1] = $second[1] - $first[1];
                $first[1]  = 0;
                
                
                
            }
            
            if ($first[1] > 0) {
                array_push($balance_pos, $first);
                
            }
            if ($second[1] > 0) {
                array_push($balance_neg, $second);
                
            }
            // echo $sum;
            
        }
        
        
        
    }
    
    if (isset($_POST['delete_bill'])) {
        $bill_id = $_POST['delete_bill'];
        
        $stmt = $conn->prepare("DELETE FROM bills WHERE bill_id=?");
        $stmt->execute(array(
            $bill_id
        ));
        
        $stmt = $conn->prepare("DELETE FROM tuples WHERE bill_id=?");
        $stmt->execute(array(
            $bill_id
        ));
        
    }
    
    if (isset($_POST['settle'])) {
        $friend1 = $_POST['friend1'];
        $friend2 = $_POST['friend2'];
        $amount  = $_POST['settlement_amount'];
        $username = $_SESSION['username'];
        
        // echo $friend1 . $friend2 . $amount;
        
        $stmt = $conn->prepare("INSERT INTO bills(is_settlement, amount, date, created_by) values(?, ?, NOW(), ?)");
        $stmt->execute(array(
            1,
            $amount,
            $username
        ));
        $bill_id = $conn->lastInsertId();
        
        
        $stmt = $conn->prepare("INSERT INTO tuples(bill_id, from_user, to_user, amount) values(?,?,?,?)");
        $stmt->execute(array(
            $bill_id,
            $friend2,
            $friend1,
            $amount
        ));
        
        
        
        
        
        
    }
    
    
    
    // echo "<script>alert('" + $row['email'] + "')</script>";
}
catch (PDOException $e) {
    // echo "Error: " + $e->getMessage();
    echo "<script>alert('" . $e->getMessage() . "');</script>";
}

// $conn = null;



?>




<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>BillSplit</title>
    <script type="text/javascript">

    	function add_bill_valid(){
          //  var description = document.getElementById('description').value;
          // var amount = document.getElementById('amount').value;

 var amount = document.getElementById("amount").value;
 amount = parseFloat(amount);
 if(!amount ){
  	  	  	alert("You must enter an amount.");


  	return false;
  }
    if(amount <=0 ){
  	  	  	alert("You must enter a positive amount.");


  	return false;
  }
 var description = document.getElementById("description").value;

 if(!description){
  	  	alert("You must enter a description.");

  	return false;
  }

 var topay = document.getElementsByName("topay[]");
 var paid = document.getElementsByName("paid[]");

 if(topay.length<2 || paid_sum.length<2){
  	  	  	  	alert("Select atleast two users.");

  	return false;
  }

var topay_sum = 0;
var paid_sum = 0;

 topay.forEach(function(x){
 	topay_sum += parseFloat(x.value);
 });

  paid.forEach(function(x){
 	paid_sum += parseFloat(x.value);
 });

  topay_sum = Math.round(topay_sum * 100) / 100;
  paid_sum = Math.round(paid_sum * 100) / 100;
  amount = Math.round(amount * 100) / 100;
 if(!description){
  	  	alert("You must enter a description.");

  	return false;
  }
  
  

  if(topay_sum!=paid_sum || paid_sum!=amount){
  	alert("The payment values do not add up to the total amount.");
  	return false;
  }
 
  return true;



    	}



    	function settle_valid(){
 var f1 = document.getElementById("friend1").value;
 var f2 = document.getElementById("friend2").value;
 var amount = document.getElementById("settlement_amount").value;
 console.log(f1, f2, amount);
 amount = parseFloat(amount);


  amount = Math.round(amount * 100) / 100;

  if(!amount ){
  	  	  	alert("You must enter an amount.");


  	return false;
  }
    if(amount <=0 ){
  	  	  	alert("You must enter a positive amount.");


  	return false;
  }
  if(f1==f2){
  	alert("You must select two different users.");
  	return false;
  }

 
  return true;



    	}
    	
  	function showUserData(user1, user2, name){
  		console.log(user1+user2);
	const Url = 'http://localhost/dbms/userdata.php';

	var bodyFormData = new FormData();

	bodyFormData.set('user1', user1);
	bodyFormData.set('user2', user2);

axios({
    method: 'post',
    url: Url,
    data: bodyFormData,
    config: { headers: {'Content-Type': 'multipart/form-data' }}
    })
    .then(function (response) {
        //handle success
         document.getElementById("txn-body").innerHTML = ""; 
         str="<ul class='list-group list-group-flush'>";
        response.data.forEach(function(record){

        	if(record.is_settlement=="1"){
        		if(record.from_user == user2){
		        		str += "<li class='list-group-item'>"+name+" paid <span class='badge badge-pill badge-success'>Rs. " + record.part + "</span> to you.</li>";

		        	}else{
			        		str += "<li class='list-group-item'>You paid <span class='badge badge-pill badge-danger'>Rs. " + record.part + "</span> to " + name + ".</li>";

		        	}





        	}else{

        	if(record.from_user==user2){
        		str += "<li class='list-group-item'>You owe <span class='badge badge-pill badge-danger'>Rs. " + record.part + "</span> to " + name + " for " + record.description + ".</li>";
        	}else{
        		str += "<li class='list-group-item'>You get back <span class='badge badge-pill badge-success'>Rs. " + record.part + "</span> from " + name + " for "+record.description + ".</li>";


        	}
        	}

        	console.log(record);
        });

        str+="</ul>";
           document.getElementById("txn-body").innerHTML = str;
        // console.log(response.data);
    })
    .catch(function (response) {
        //handle error
        console.log(response);
    });
}




    </script>

    <style type="text/css">
.friend-list-item { cursor: pointer; }
/* For Firefox */
input[type='number'] {
    -moz-appearance:textfield;
}

/* Webkit browsers like Safari and Chrome */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
    </style>
   
  </head>
  <body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">
    <img src="logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
    Bill Split
  </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <button type="button" class="btn btn-primary btn-sm ml-auto" style="margin-left: 10px; align-self: right;">Welcome <?php echo $name; ?></button>

<form method="post" action="index.php">
<button name="logout" type="submit" class="btn btn-secondary btn-sm" style="margin-left: 10px; align-self: right;">Logout</button>
</form>
  </div>
</nav>

<br/>

<div class="container">
  <div class="row">
    <div class="col-md-3">
    <h3>Friends</h3>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addFriendModal"> + Add Friend</button>


<!-- Modal -->
<div class="modal fade" id="addFriendModal" tabindex="-1" role="dialog" aria-labelledby="addFriendModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addFriendModaLabel">Add Friend</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
              <form method="POST" action="index.php">

      <div class="modal-body">

  <div class="form-group">
    <label for="friendUsernameInput">Username</label>
    <input type="text" class="form-control" id="friendUsernameInput" placeholder="Enter username" name="friend_username">
  </div>

  <!-- <button type="submit" class="btn btn-primary">Add</button> -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
  <button type="submit" class="btn btn-primary" name="add_friend">Add</button>
        </div>
      </form>

    </div>
  </div>
</div>


<?php


//$stmt = $conn->prepare("SELECT * FROM friends WHERE user1=? OR user2=?");
$stmt = $conn->prepare("SELECT username, name FROM friends, users WHERE (user1=? OR user2=?) AND (user1=username OR user2=username) AND username!=? 
");

$stmt->execute(array(
    $username,
    $username,
    $username
));
// $row = $stmt->fetch(PDO::FETCH_ASSOC);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $rows is an array containing all records...
echo "<br/><br/><ul class='list-group'>";

foreach ($results as $row) {
    // if ($row['user1'] == $username)
    // 	echo "<li class='list-group-item'>".$row['user2']."</li>";
    // else
    // 	echo "<li class='list-group-item'>".$row['user1']."</li>";
    echo "<li class='list-group-item friend-list-item' data-user1='" . $row['username'] . "' data-user2='" . $_SESSION['username'] . "' data-name='".$row['name']."' data-toggle='modal' data-target='#viewTxnModal'>" . $row['name'] . " <span class='badge badge-dark'>" . $row['username'] . "</span></li>";
    
}

echo "</ul>";




?>


    </div>
    <div class="col-md-6">
<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#createBillModal">Create Bill</button>


<!-- Modal -->
<div class="modal fade" id="createBillModal" tabindex="-1" role="dialog" aria-labelledby="createBillModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createBillLabel">Create Bill</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
 

 <!-- <div class=""> -->
	
    <form method="POST" action="index.php" onsubmit="return(add_bill_valid())">

      <div class="modal-body">

<strong style="margin-left: 15px;">Select Friends:</strong>
    <select id="multiple-checkboxes" multiple="multiple">

    	<?php


$stmt = $conn->prepare("SELECT * FROM friends WHERE user1=? OR user2=?");

$stmt->execute(array(
    $username,
    $username
));
// $row = $stmt->fetch(PDO::FETCH_ASSOC);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $rows is an array containing all records...
echo "<br/><br/><ul class='list-group'>";
echo "<option value=" . $username . ">" . $username . "</option>";

foreach ($results as $row) {
    if ($row['user1'] == $username)
        echo "<option value=" . $row['user2'] . ">" . $row['user2'] . "</option>";
    else
        echo "<option value=" . $row['user1'] . ">" . $row['user1'] . "</option>";
    
}

?>


    </select>
  <div class="form-group">
    <!-- <label for="friendUsernameInput">Amount</label> -->
    <!-- <input type="text" class="form-control" id="amountInput" placeholder="Enter Total Amount" name="amount"> -->
    <label for="friendUsernameInput">Amount</label>
<input type="number" name="amount" id="amount" step="0.01" class="form-control quantity"  />
<br/>
<div id="names">
	</div>

<br/>
    <label for="friendUsernameInput">Description</label>
    <input type="text" class="form-control" id="description" placeholder="" name="description">
  </div>


  <!-- <button type="submit" class="btn btn-primary">Add</button> -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
  <button type="submit" class="btn btn-primary" name="add_bill">Add</button>
        </div>
      </form>
<!-- </div> -->




    </div>
  </div>
</div>

<button type="button" class="btn btn-outline-dark" data-toggle="modal" data-target="#settleModal">Settle</button>


<!-- Modal -->
<div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-labelledby="settleModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="settleLabel">Settle</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
 

 <!-- <div class=""> -->
	
    <form method="POST" action="index.php" onsubmit="return(settle_valid())">

      <div class="modal-body">





<?php

echo "<select class='form-control' id='friend1' name='friend1'>";
$stmt = $conn->prepare("SELECT * FROM friends WHERE user1=? OR user2=?");

$stmt->execute(array(
    $username,
    $username
));
// $row = $stmt->fetch(PDO::FETCH_ASSOC);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $rows is an array containing all records...

echo "<option value=" . $username . ">" . $username . "</option>";

foreach ($results as $row) {
    if ($row['user1'] == $username)
        echo "<option value=" . $row['user2'] . ">" . $row['user2'] . "</option>";
    else
        echo "<option value=" . $row['user1'] . ">" . $row['user1'] . "</option>";
    
}

echo "</select>";
echo "<span>paid</span>";


echo "<select class='form-control' id='friend2' name='friend2'>";
echo "<option value=" . $username . ">" . $username . "</option>";

foreach ($results as $row) {
    if ($row['user1'] == $username)
        echo "<option value=" . $row['user2'] . ">" . $row['user2'] . "</option>";
    else
        echo "<option value=" . $row['user1'] . ">" . $row['user1'] . "</option>";
    
}


echo "</select>";

?>


  <div class="form-group">
    <!-- <label for="friendUsernameInput">Amount</label> -->
    <!-- <input type="text" class="form-control" id="amountInput" placeholder="Enter Total Amount" name="amount"> -->
    <label for="friendUsernameInput">Amount</label>
<input type="number" name="settlement_amount" id="settlement_amount" step="0.01" class="form-control quantity"  />
<br/>

<br/>
  </div>


  <!-- <button type="submit" class="btn btn-primary">Add</button> -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
  <button type="submit" class="btn btn-primary" name="settle">Settle</button>
        </div>
      </form>
<!-- </div> -->




    </div>
  </div>
</div>






<div class="modal fade" id="viewTxnModal" tabindex="-1" role="dialog" aria-labelledby="viewTxnModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewTxnModalLabel">Transactions</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="txn-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <!-- <button type="button" class="btn btn-primary">Send message</button> -->
      </div>
    </div>
  </div>
</div>






<br/>
<br/>




<?php

$friends      = array();
$friends_name = array();
// $stmt = $conn->prepare("SELECT * FROM friends WHERE user1=? OR user2=?");
$stmt         = $conn->prepare("SELECT username, name FROM friends, users WHERE (user1=? OR user2=?) AND (user1=username OR user2=username) AND username!=?");

$stmt->execute(array(
    $username,
    $username,
    $username
));
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    // if ($row['username'] == $username)
    // 	$friends[$row['user2']] = 0;
    // else
    // 	$friends[$row['user1']] = 0;
    
    $friends[$row['username']]      = 0;
    $friends_name[$row['username']] = $row['name'];
    
}









$stmt = $conn->prepare("SELECT * FROM tuples WHERE (from_user=? OR to_user=?)");
$stmt->execute(array(
    $username,
    $username
));


$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $rows is an array containing all records...
echo "<br/><br/><ul class='list-group'>";



foreach ($results as $row) {
    // echo $row['description'];
    
    if ($row['from_user'] == $username) {
        $friends[$row['to_user']] -= $row['amount'];
    } else {
        $friends[$row['from_user']] += $row['amount'];
    }
    
    // echo $row['from_user'].$row['to_user'].$row['amount'];;
}





foreach ($friends as $key => $value) {
    if ($value != 0) {
        // echo $key.$value;
        
        if ($value > 0) {
            $str = "<div class='alert alert-success' role='alert'>" . $friends_name[$key] . " owes you Rs. " . $value . "</div>";
            echo "<div class='card text-white bg-success'>";
            
            
        } else {
            $str = "<div class='alert alert-danger' role='alert'>" . "You owe Rs. " . -$value . " to " . $friends_name[$key] . "</div>";
            echo "<div class='card text-white bg-danger'>";
            
            
            
        }
        
        
        
        
        echo "<div class='card-body'>
    <h5 class='card-title'>" . $friends_name[$key] . "</h5>" . $str . "
  </div>
</div><hr/>";
    }
}



?>



    </div>
    <div class="col-md-3">
    	    <h3>Bills</h3>



<?php

$stmt = $conn->prepare("SELECT bills.bill_id, is_settlement, description, bills.amount, date, created_by FROM bills, tuples WHERE (from_user=? OR to_user=?) AND bills.bill_id = tuples.bill_id");
$stmt->execute(array(
    $username,
    $username
));


$results = $stmt->fetchAll(PDO::FETCH_ASSOC);





foreach ($results as $row) {
    $dt = new DateTime($row['date']);
    
    if ($row['is_settlement']) {
        echo "<div class='card text-white bg-dark'>";
        
    } else {
        echo "<div class='card bg-light'>";
    }
    
    
    echo "<div class='card-header'>" . $dt->format('d M Y | h:i A') . "</div>

  <div class='card-body'>";
    
    
    
    if ($row['is_settlement']) {
        echo "<span class='badge badge-pill badge-secondary'>Settlement</span>";
    } else {
        echo "<h5 class='card-title'>" . $row['description'] . "</h5>";
        echo "<span class='badge badge-pill badge-info'>Bill</span>";
        
    }
    
    
    echo "<p class='card-text'>Rs. " . $row['amount'] . "</p>
    		<p>Created by ".$row['created_by']."</p>


    <form method='POST' action='index.php'>
      <button type='submit' class='btn btn-danger' name='delete_bill' value='" . $row['bill_id'] . "'>Delete</button>
      </form>
  </div>
</div><hr/>";
}


?>







    </div>
  </div>
</div>



  	<!-- Image and text -->

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

     <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.js"></script> -->
  <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"> -->
  <!-- <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script> -->

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">

  <script type="text/javascript">
  	    $(document).ready(function() {

  	    	$(".friend-list-item").click(function(){
  // Holds the product ID of the clicked element
  var user1 = $(this).attr("data-user1");
var user2 = $(this).attr("data-user2");
var name = $(this).attr("data-name");



  showUserData(user1, user2, name);

});





        $('#multiple-checkboxes').multiselect({
          includeSelectAllOption: true,
        });

$('#viewTxnModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var recipient = button.data('name') 

  // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('.modal-title').text('Transactions with ' + recipient)
  // modal.find('.modal-body input').val(recipient)
});
    });


$("#multiple-checkboxes").bind("change keyup", function(event){
   console.log("changed");
   document.getElementById("names").innerHTML="";


           var selectedPerson = $(this).children("option:selected").val();

               var person = [];

        $.each($("#multiple-checkboxes option:selected"), function(){    


  var node = document.createElement("p");
     node.innerHTML = `<label for="name">`+$(this).val()+`</label>

     <input type="hidden" name="person[]" value="`+$(this).val()+`">

<input type="number" name="topay[]" step="0.01" class="form-control quantity"  placeholder="Amount Paid" style="width: 150px; display: inline-block; float: right; margin-right: 5px;" />


<input type="number" name="paid[]" step="0.01" class="form-control quantity"  placeholder="To Be Paid" style="width: 150px; display: inline-block; float: right; margin-right: 5px;"/>`;


document.getElementById("names").appendChild(node);


            // person.push($(this).val());

        });
});




  </script>



  </body>
</html>










