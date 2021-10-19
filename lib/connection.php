<?php

	class Connection{
		private static $instance = null;
		public static function getInstance(){
			if( self::$instance == null ){
				self::$instance = new Connection();
			}
			return self::$instance;
		}
		public $con;
		public static $conn;
		public function __construct(){
			// $this->con = new PDO('mysql:host=localhost;dbname=qiosku','kali','');
			// return $this->con;
		}
		public static function getConnection(){
			if( self::$conn != null ){
				return self::$conn;
			}else{
				self::$conn = new PDO('mysql:host=localhost;dbname=qiosku','kali','');
				return self::$conn;
			}
		}
	}

?>