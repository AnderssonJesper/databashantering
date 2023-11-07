<?php
$host = "localhost";
$port = 3306;
$database = "test";
$username = "root";
$password = "";

$connection = new mysqli($host, $username, $password, $database, $port);

if ($connection->connect_error != null) {
   die("Anslutningen misslyckades: " . $connection->connect_error);
} else {
   echo "Anslutningen lyckades!<br>";
}

$checkTableQuery = "SHOW TABLES LIKE 'customers'";
$result = $connection->query($checkTableQuery);

if ($result->num_rows == 0) {

   $query10 = "CREATE TABLE IF NOT EXISTS customers (
      customer_id INT AUTO_INCREMENT PRIMARY KEY,
      first_name VARCHAR(50),
      last_name VARCHAR(50),
      email VARCHAR(100),
      phone VARCHAR(20)
   )";

   $result1 = $connection->query($query10);

   if ($result1) {
      echo "Tabell 'customers' skapades framgångsrikt.<br>";
   } else {
      echo "Fel vid skapandet av tabell 'customers': " . $connection->error . "<br>";
   }

   $query11 = "CREATE TABLE IF NOT EXISTS orders (
      order_id INT AUTO_INCREMENT PRIMARY KEY,
      customer_id INT,
      order_date DATE,
      total_amount DECIMAL(10,2),
      FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
   )";

   $result2 = $connection->query($query11);

   if ($result2) {
      echo "Tabell 'orders' skapades framgångsrikt.<br>";
   } else {
      echo "Fel vid skapandet av tabell 'orders': " . $connection->error . "<br>";
   }

   $query12 = "CREATE TABLE IF NOT EXISTS order_items (
      item_id INT AUTO_INCREMENT PRIMARY KEY,
      order_id INT,
      product_name VARCHAR(100),
      quantity INT,
      price DECIMAL(10,2)
   )";

   $result3 = $connection->query($query12);

   if ($result3) {
      echo "Tabell 'order_items' skapades framgångsrikt.<br>";
   } else {
      echo "Fel vid skapandet av tabell 'order_items': " . $connection->error . "<br>";
   }
}

$logFile = "log.txt";
$loggingEnabled = true;

if (isset($_POST['update_order'])) {
   $order_id = $_POST['order_id'];
   $new_total_amount = $_POST['new_total_amount'];

   $updateOrderQuery = "UPDATE orders SET total_amount = $new_total_amount WHERE order_id = $order_id";
   $result = $connection->query($updateOrderQuery);

   if ($result) {
      echo "Order uppdaterad. <br>";

      if ($loggingEnabled) {
         $logMessage = date("Y-m-d H:i") . " Order med ID $order_id uppdaterades till ett nytt totalbelopp: $new_total_amount kr.";
         file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
      }
   } else {
      echo "Fel vid uppdatering:" . $connection->error . "<br>";
   }
}


if (isset($_POST['delete_customer'])) {
   $customer_id = $_POST['customer_id'];

   $logMessage = date("Y-m-d H:i ") . "Kund med ID $customer_id raderades.";
   file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);

   $deleteOrdersQuery = "DELETE FROM orders WHERE customer_id = $customer_id";
   $result = $connection->query($deleteOrdersQuery);

   $deleteCustomerQuery = "DELETE FROM customers WHERE customer_id = $customer_id";
   $result = $connection->query($deleteCustomerQuery);

   if ($result) {
      echo "Kund och dess ordrar raderades. <br>";
   } else {
      echo "Fel vid radering:" . $connection->error . "<br>";
   }
}

if (isset($_POST['show_log'])) {
   if (file_exists($logFile)) {
      $logContent = file_get_contents($logFile);
      echo "Loggmeddelanden:<br><pre>$logContent </pre>";
   } else {
      echo "Loggfilen finns inte ännu.";
   }
}

if (isset($_POST['disable_logging'])) {
   $loggingEnabled = false;
   echo "Loggningen är avstängd.";
}

$connection->close();


?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kundhantering</title>
</head>

<body>

   <h1>Kundhantering</h1>

   <form method="post">
      <label for="orders_id">Order ID:</label>
      <input type="text" name="order_id">
      <label for="new_total_amount"></label>
      <input type="text" name="new_total_amount">
      <input type="submit" name="update_order" value="Uppdatera order">
   </form>

   <form method="post">
      <label for="customer_id">Kund ID:</label>
      <input type="text" name="customer_id">
      <input type="submit" name="delete_customer" value="Radera kund och ordrar">
   </form>

   <form method="post">
      <input type="submit" name="show_log" value="Visa loggmeddelanden">
      <input type="submit" name="disable_logging" value="Stäng av loggning">
   </form>

</body>

</html>