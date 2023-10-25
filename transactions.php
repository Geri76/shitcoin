<?php

@include_once("db.php");
header('Content-type: application/json');

$conn = new mysqli($servername, $username, $password, $database);
$result = $conn->query("SELECT * FROM `transactions`");

if ($result->num_rows > 0) {
    $transactions = ["success" => "true"];

    while($row = $result->fetch_assoc()) {
        array_push($transactions, 
            [
                "date" => $row["date"],
                "from" => $row["from"],
                "to" => $row["to"],
                "amount" => $row["amount"]
            ]
        );
    }

    echo json_encode($transactions);
} else {
    $final_data = [
        "success" => "false",
        "error" => "NO_TRANSACTIONS_FOUND"
    ];
    echo json_encode($final_data);
}

$conn->close();

?>