<?php

@include_once("db.php");
header('Content-type: application/json');

$conn = new mysqli($servername, $username, $password, $database);

if (isset($_GET["function"])) {
    if ($_GET["function"] == "getmineable") {
        $result = $conn->query("SELECT * FROM `mineable`");

        if ($result->num_rows > 0) {
            
            while($row = $result->fetch_assoc()) {
                $resp = [
                    "success" => "true",
                    "hash" => $row["hash"],
                    "worth" => $row["worth"]
                ];
                echo json_encode($resp);
            }
        
        } else {
            $resp = [
                "success" => "false",
                "error" => "NO_MINEABLES_FOUND"
            ];
            echo json_encode($resp);
        }
    } elseif ($_GET["function"] == "submit") {
        if (
            isset($_GET["number"]) &&
            isset($_GET["publickey"])
        ) {
            $result = $conn->query("SELECT * FROM `wallets` WHERE `publickey` = '". $_GET["publickey"]. "'");

            if ($result->num_rows > 0) {
                $result = $conn->query("SELECT * FROM `mineable`");

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        if ($_GET["number"] == $row["number"]) {

                            $result = $conn->query("SELECT * FROM `wallets` WHERE `publickey` = '". $_GET["publickey"]. "'");

                            if ($result->num_rows > 0) {
                                while($row2 = $result->fetch_assoc()) {
                                    $newBalance = $row["worth"] + $row2["balance"];
                                }
                            }

                            $conn->query("UPDATE `wallets` SET `balance` = $newBalance");
                            $conn->query("DELETE FROM `mineable` WHERE `number` = '". $row["number"]. "'");

                            $randomNum = rand(21423, 121423);
                            $randomNumHash = strtolower(hash("SHA512", "$randomNum"));
                            $newMineableWorth = ($randomNum / (21423 - 121423)) * (1 - 5) + 1;
                            $worth = explode(".", $newMineableWorth)[0];


                            $conn->query("INSERT INTO `mineable` VALUES ('$randomNum', '$randomNumHash', '$worth')");

                            $resp = [
                                "success" => "true",
                                "amount" => $row["worth"],
                                "new_balance" => $newBalance
                            ];

                            echo json_encode($resp);
                        } else {
                            $resp = [
                                "success" => "false",
                                "error" => "INVALID_HASH"
                            ];

                            echo json_encode($resp);
                        }
                    }
                } else {
                    $resp = [
                        "success" => "false",
                        "error" => "MINEABLE_NOT_AVAILABLE"
                    ];

                    echo json_encode($resp);
                }
            } else {
                $resp = [
                    "success" => "false",
                    "error" => "WALLET_DOES_NOT_EXIST"
                ];

                echo json_encode($resp);
            }
        }
    }
}

?>