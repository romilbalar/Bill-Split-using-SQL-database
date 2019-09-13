<?php


if (isset($_POST['reg_user'])) {

  $servername = "localhost";
  $db_user = "root";
  $db_pass = "";
  $db_name = "bill_split";

  $username = $_POST['rusername'];
  $email = $_POST['email'];
  $name = $_POST['name'];

  $pass1 = $_POST['pass1'];
  $pass2 = $_POST['pass2'];
  $phone_no = $_POST['phone_no'];

  if($pass1 != $pass2){
    echo "<script>alert('Password do not match.');</script>";
  }

  try {
      $conn = new PDO("mysql:host=$servername;dbname=$db_name", $db_user, $db_pass);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $stmt = $conn->prepare("INSERT INTO users(username, name, email, password, phone_no)
      VALUES (?,?,?,?,?)");
     
       $stmt->execute(array($username, $name, $email,$pass1,$phone_no));
      echo "<script>alert('Registration successful.');</script>";
  }
  catch(PDOException $e){
      echo "Error: " + $e->getMessage();
    echo "<script>alert('User already exists.');</script>";
  }

  $conn = null;

}

?>



<?php

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_POST['login_user'])) {
    $servername = "localhost";
    $db_user    = "root";
    $db_pass    = "";
    $db_name    = "bill_split";
    
    $username = $_POST['lusername'];
    $pass     = $_POST['password'];
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$db_name", $db_user, $db_pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=?");
        $stmt->execute(array(
            $username,
            $pass
        ));
        $count  = $stmt->rowCount();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($count == 1) {
            $_SESSION['username'] = $username;
            
            echo "<script>alert('Login successful.');</script>";
            header('location: index.php');
        }
    }
    catch (PDOException $e) {
        echo "Error: " + $e->getMessage();
        echo "<script>alert('User does not exist.');</script>";
    }
    
    $conn = null;
}
?>



<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>BillSplit</title>
  <script type="text/javascript">
        function login_valid(){
           var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
           var username = document.getElementById('lusername').value;
           var password = document.getElementById('password').value;

                  if (!username) 
                  {
                      putError("Username is required");
                      return false;
                  }
                  if (!password) 
                  {
                      putError("Password is required");
                      return false;
                  }

                  return true;
        }

        function reg_valid(){
           var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                      var username = document.getElementById('rusername').value;

           var name = document.getElementById('name').value;

           var email = document.getElementById('email').value;
           var pass1 = document.getElementById('pass1').value;
           var pass2 = document.getElementById('pass2').value;
           var phone_no = document.getElementById('phone_no').value;

        if (!username) 
                  {
                      putError("Username is required");
                      return false;
                  }
          if (!name) 
                  {
                      putError("Name is required");
                      return false;
                  }
                  if (!email) 
                  {
                      putError("Email is required");
                      return false;
                  }
                  if (!reg.test(email)) 
                  {
                      putError("Invalid email address");
                      return false;
                  }
                  if (!pass1 || !pass2) 
                  {
                      putError("Password is required");
                      return false;
                  }
   
                  if(pass1 !== pass2){
                     putError("Passwords do not match");
                     return false;
                  }

                  if(!phone_no){
          putError("Phone number is required");
                  return false;
                  }

                  return true;
        }


        function putError(str){
          alert(str);
        }


    </script>
    <style>
      body{
        background: #f5f5dc;
      }
    </style>
  </head>
  <body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">
    <img src="logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
    Bill Split
  </a>


</nav>


    <div class="container">
      <br/>
      <br/>
      <br/>
      <div class="row">
              <div class="col col-md-8">
                <h1 class="display-3" style="text-align: right;">
                 Billsplit takes the trouble out of sharing expenses – with friends, with roommates, with anyone.
                 </h1> 
</div>
      <div class="col col-md-4"  style="background: #fbfbf1; border-radius: 10px; padding: 20px;">
        <ul class="nav nav-pills nav-justified" id="pills-tab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="pills-signup-tab" data-toggle="pill" href="#pills-signup" role="tab" aria-controls="pills-signup" aria-selected="true">Signup</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="pills-login-tab" data-toggle="pill" href="#pills-login" role="tab" aria-controls="pills-login" aria-selected="false">Login</a>
  </li>

</ul>
<div class="tab-content" id="pills-tabContent">
  <div class="tab-pane fade show active" id="pills-signup" role="tabpanel" aria-labelledby="pills-signup-tab">
    
<form method="post" action="landing.php" onsubmit="return(reg_valid())">
      <h2>Signup</h2>

        <div class="form-group">
    <label for="exampleInputEmail1">Name</label>
    <input type="text" class="form-control" id="name" name="name" aria-describedby="emailHelp" placeholder="Enter Name">
  </div>
  <div class="form-group">
    <label for="exampleInputEmail1">Username</label>
    <input type="text" class="form-control" id="rusername" name="rusername" aria-describedby="emailHelp" placeholder="Enter username">
  </div>
    <div class="form-group">
    <label for="exampleInputEmail1">Email</label>
    <input type="text" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email">
  </div>

  <div class="form-group">
    <label for="exampleInputPassword1">Password</label>
    <input type="password" class="form-control" id="pass1" name="pass1" placeholder="Password">
  </div>
    <div class="form-group">
    <label for="exampleInputPassword1">Confirm Password</label>
    <input type="password" class="form-control" id="pass2" name="pass2" placeholder="Confirm Password">
  </div>
      <div class="form-group">
    <label for="exampleInputEmail1">Phone Number</label>
    <input type="text" class="form-control" id="phone_no" name="phone_no" aria-describedby="emailHelp" placeholder="Enter Phone Number">
  </div>

  <button type="submit" name="reg_user" value="Signup" class="btn btn-primary">Signup</button>
</form>



  </div>
  <div class="tab-pane fade" id="pills-login" role="tabpanel" aria-labelledby="pills-login-tab">
    
<form method="post" action="landing.php" onsubmit="return(login_valid())">
      <h2>Login</h2>
  <div class="form-group">
    <label for="exampleInputEmail1">Username</label>
    <input type="text" class="form-control" id="lusername" name="lusername" aria-describedby="emailHelp" placeholder="Enter username">
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">Password</label>
    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
  </div>

  <button type="submit" name="login_user" value="Login" class="btn btn-primary">Login</button>
</form>

  </div>
</div>
    
     
</div>
</div>
</div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>