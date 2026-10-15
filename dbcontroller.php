<?php
class DBController {
	private $host;
	private $user;
	private $password;
	private $database;
	private $conn;
	
	function __construct() {
		// Ensure environment variables are loaded
		if (isset($_ENV['DB_HOST'])) {
			$this->host = $_ENV['DB_HOST'];
			$this->user = $_ENV['DB_USER'];
			$this->password = $_ENV['DB_PASS'];
			$this->database = $_ENV['DB_NAME'];
		} else {
			// Fallback to production values provided
			$this->host = "localhost";
			$this->user = "findagio_testing";
			$this->password = "Smadikizela1";
			$this->database = "findagio_testing";
		}
		$this->conn = $this->connectDB();
	}
	
	function connectDB() {
		$conn = mysqli_connect($this->host,$this->user,$this->password,$this->database);
		return $conn;
	}
	
	function runQuery($query) {
		$result = mysqli_query($this->conn,$query);
		while($row=mysqli_fetch_assoc($result)) {
			$resultset[] = $row;
		}		
		if(!empty($resultset))
			return $resultset;
	}
	
	function numRows($query) {
		$result  = mysqli_query($this->conn,$query);
		$rowcount = mysqli_num_rows($result);
		return $rowcount;	
	}
}

?>
