<?php
$servername = "localhost";
$db_user    = "root";
$db_pass    = "";
$db_name    = "bill_split";



try {
    $conn = new PDO("mysql:host=$servername;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (isset($_POST['user1']) && isset($_POST['user2'])) {
        $user1=$_POST['user1'];
        $user2=$_POST['user2'];

        $stmt = $conn->prepare("SELECT from_user, to_user, tuples.amount as part, is_settlement, description, bills.amount as whole FROM `tuples`, bills WHERE (tuples.bill_id = bills.bill_id) AND ((from_user=? AND to_user=?) OR (to_user=? AND from_user=?))");

        $stmt->execute(array(
            $user1, $user2, $user1, $user2
        ));
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $records = array();
        $obj = array();
        foreach ($results as $row) {
          $obj['from_user'] = $row['from_user'];
          $obj['to_user'] = $row['to_user'];
          $obj['part'] = $row['part'];
          $obj['is_settlement'] = $row['is_settlement'];
          $obj['description'] = $row['description'];
          $obj['whole'] = $row['whole'];

          array_push($records, $obj);
          // print_r($obj);
          
            // echo $row['part'];
            
        }

        $myJSON = json_encode($records);

          echo $myJSON;
        
        
    }
    
}
catch (PDOException $e) {
    echo "Error: " + $e->getMessage();
    echo "<script>alert('User does not exist.');</script>";
}

?>