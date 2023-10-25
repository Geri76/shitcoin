<?php

@include_once("db.php");
header('Content-type: application/json');

if (
    isset($_GET["toaddress"]) &&
    $_GET["toaddress"] != "" &&

    isset($_GET["fromaddress"]) &&
    $_GET["fromaddress"] != "" &&

    isset($_GET["fromkey"]) &&
    $_GET["fromkey"] != "" &&

    isset($_GET["amount"]) &&
    $_GET["amount"] != ""
) {
    $conn = new mysqli($servername, $username, $password, $database);

    $one_week = date("Y-m-d H:i:s", strtotime("+7 days"));

    $currentDate = new DateTime();
    $date_to_db = $currentDate->format('Y-m-d H:i:s');

    $result = $conn->query("SELECT * FROM `wallets` WHERE `privatekey` = '". $_GET["fromkey"]. "' ". " AND `publickey` = '". $_GET["fromaddress"]. "'");
    $bans_result = $conn->query("SELECT * FROM `bans` WHERE `wallet` = '". $_GET["fromaddress"]. "' ". "AND `until` >= '". $date_to_db. "'". " ORDER BY `id` DESC LIMIT 1");

    if ($_GET["fromaddress"] == $_GET["toaddress"]) {
        $final_data = [
            "success" => "false",
            "error" => "FROM_WALLET_EQUALS_TO_WALLET"
        ];

        echo json_encode($final_data);

        exit();
    }

    if ($bans_result->num_rows > 0) {
        
        $final_data = [
            "success" => "false",
            "error" => "ACCOUNT_BANNED"
        ];

        while($row = $bans_result->fetch_assoc()) {
            $final_data["expires"] = $row["until"];
        }
        echo json_encode($final_data);

        exit();
    }

    if ($_GET["amount"] <= 0) {
        $final_data = [
            "success" => "false",
            "error" => "FRAUD_DETECTED_ACCOUNT_BANNED_ONE_WEEK"
        ];

        $conn->query("INSERT INTO `bans` (`wallet`, `until`) VALUES ('". $_GET["fromaddress"]. "', '". $one_week. "')");
        echo json_encode($final_data);
        
        exit();
    }

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $from_balance = (int) $row["balance"];
            $transfer_amount = (int) $_GET["amount"];

            if ($from_balance >= $transfer_amount) {
                $wallet_search = $conn->query("SELECT * FROM `wallets` WHERE `publickey` = '". $_GET["toaddress"]. "'");

                if ($wallet_search->num_rows > 0) {
                    while($row = $wallet_search->fetch_assoc()) {

                        $to_balance = (int) $row["balance"];

                        $new_from_balance = $from_balance - $transfer_amount;
                        $new_to_balance = $to_balance + $transfer_amount;

                        $conn->query("UPDATE `wallets` SET `balance` = '". $new_from_balance. "' WHERE `publickey` = '". $_GET["fromaddress"]. "' ". "AND `privatekey` = '". $_GET["fromkey"]. "'");
                        $conn->query("UPDATE `wallets` SET `balance` = '". $new_to_balance. "' WHERE `publickey` = '". $_GET["toaddress"]. "'");

                        $conn->query("INSERT INTO `transactions` (`date`, `from`, `to`, `amount`) VALUES ('$date_to_db', '". $_GET["fromaddress"]. "', '". $_GET["toaddress"]. "', '". $_GET["amount"]. "')");

                        $final_data = [
                            "success" => "true"
                        ];
                        echo json_encode($final_data);
                    }
                  } else {
                        $final_data = [
                            "success" => "false",
                            "error" => "TO_WALLET_DOES_NOT_EXIST"
                        ];
                        echo json_encode($final_data);
                  }
            } else {
                $final_data = [
                    "success" => "false",
                    "error" => "INSUFFICIENT_FUNDS"
                ];
                echo json_encode($final_data);
            }
        }
      } else {
        $final_data = [
            "success" => "false",
            "error" => "FROM_WALLET_DOES_NOT_EXIST"
        ];
        echo json_encode($final_data);
      }
      $conn->close();
}

?>