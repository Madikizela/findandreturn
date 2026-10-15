<?php
session_start();
require_once("dbcontroller.php");
$db_handle = new DBController();

if (isset($_POST["checkout"]) && !empty($_SESSION["cart_item"])) {
    // Process the checkout (e.g., save order to database, redirect to payment gateway)
    $total_price = 0;
    $total_quantity = 0;

    // Calculate totals and prepare order details
    foreach ($_SESSION["cart_item"] as $item) {
        $total_quantity += $item["quantity"];
        $total_price += ($item["price"] * $item["quantity"]);
    }

    // Example: Save order to a database (adjust according to your database schema)
    $order_date = date("Y-m-d H:i:s");
    $order_query = "INSERT INTO orders (total_quantity, total_price, order_date) VALUES ('$total_quantity', '$total_price', '$order_date')";
    $db_handle->runQuery($order_query);

    // Clear the cart after checkout
    unset($_SESSION["cart_item"]);

    // Redirect to a thank you page or payment gateway
    header("Location: thank_you");
    exit();
} else {
    // Redirect back to cart if it's empty or checkout not initiated
    header("Location: payed_for_service");
    exit();
}
?>
