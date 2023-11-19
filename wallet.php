<?php

@include_once("db.php");
header('Content-type: application/json');

if (isset($_GET["publickey"]) && $_GET["publickey"] != "") {
    $publickey = $_GET["publickey"];

    $conn = new mysqli($servername, $username, $password, $database);
    $result = $conn->query("SELECT * FROM `wallets` WHERE `publickey` = '$publickey'");

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $final_data = [
                "success" => "true",
                "public_key" => $publickey,
                "balance" => $row["balance"]
            ];
            echo json_encode($final_data);
        }
    } else {
        $final_data = [
            "success" => "false",
            "error" => "WALLET_DOES_NOT_EXIST"
        ];
        echo json_encode($final_data);
    }
}

?>