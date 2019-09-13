<?php


if (isset($_POST['reg_user'])) {

	$servername = "localhost";
	$db_user = "root";
	$db_pass = "";
	$db_name = "bill_split";

	$username = $_POST['username'];
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


<!DOCTYPE html>
<html>
<head>
	<title>Signup</title>
	<script type="text/javascript">
        function reg_valid(){
           var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                      var username = document.getElementById('username').value;

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
            // document.getElementById('errors').innerHTML = "<div class='notification is-danger'>" + str + "</div>";
        }


    </script>
</head>
<body>
	<form method="post" action="signup.php" onsubmit="return(reg_valid())">

    	<input type="text" placeholder="Name" id="name" name="name">
    	<input type="text" placeholder="Email" id="email" name="email">
        <input type="password" placeholder="Password" id="pass1" name="pass1">
        <input type="password" placeholder="Confirm Password" id="pass2" name="pass2">
        <input type="text" placeholder="Phone Number" id="phone_no" name="phone_no">                 
        <input type="submit" name="reg_user" value="Signup" />
    </form>




</body>
</html>