<?php

@include_once("db.php");
header('Content-type: application/json');

if (isset($_GET["key"]) && $_GET["key"] != "") {
    $conn = new mysqli($servername, $username, $password, $database);

    $result = $conn->query("SELECT * FROM `walletkeys` WHERE `key` = '". $_GET["key"]. "'");

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $publickey = hash("sha256", rand());
            $privatekey = hash("sha256", rand());

            $final_data = [
                "success" => "true",
                "public_key" => $publickey, 
                "private_key" => $privatekey
            ];

            echo json_encode($final_data);
            
            $conn->query("DELETE FROM `walletkeys` WHERE `key` = '". $_GET["key"]. "'");
            $conn->query("INSERT INTO `wallets` (`publickey`, `privatekey`, `balance`) VALUES ('$publickey', '$privatekey', 0)");
        }
      } else {
            $final_data = [
                "success" => "false",
                "error" => "WALLET_GENERATION_KEY_DOES_NOT_EXIST"
            ];

            echo json_encode($final_data);
      }
      $conn->close();
}

?>