<?php
	class Auth{
		private static $instance = null;
		public static function getInstance(){
			if( self::$instance==null){
				self::$instance = new Auth();
			}
			return self::$instance;
		}
		public static $con;
		public function __construct(){
			self::$con = Connection::getConnection();
			Tama64::getInstance();
		}

		//Signup
		public static function Signup( $param )
		{
			$u_name = isset($param['u_name']) ? $param['u_name'] : die('Name is null');
			$u_username = isset($param['u_username']) ? $param['u_username'] : die("Username is null");
			$u_phone = $param['u_phone'];
			$u_email = $param['u_email'];
			$u_password = $param['u_password'];
			//default image
			$u_img = "asset/img/" . $param['u_username'] . ".png";
			//default token
			$token = Token::encode( ['base'=> $u_name . '_' . $u_username . '_' . uniqid(),'count'=>5] );
			//default role
			$role = 'member';

			$query = "INSERT INTO users (`u_name`,`u_email`,`u_phone`,`u_username`,`u_password`,`u_img`,`token`,`role`) VALUES ";
			$query .= "( :u_name, :u_email, :u_phone, :u_username, :u_password, :u_img, :token, :role )";
			if ( self::checkUser( $u_username )  != false )
			{
				$statement = self::$con->prepare($query);
				if( $statement->execute(['u_name'=>$u_name,'u_email'=>$u_email,'u_phone'=>$u_phone,'u_username'=>$u_username,'u_password'=>Tama64::__encrypt(md5($u_password)),'u_img'=>$u_img,'token'=>$token,'role'=>$role]) )
				{
					self::print(['signup_status'=>true,'msg'=>'Signup Successfuly']);
				}else
				{
					self::print(['signup_status'=>false,'msg'=>'Signup Unsuccessfuly']);
				}
			}else{
				self::print(['signup_status'=>false,'msg'=>'User already exists']);
			}
		}

		private static function checkUser( $u_username )
		{
			$query = "select * from users where u_username=:u_username";
			$statement = self::$con->prepare( $query );
			$statement->execute(['u_username'=>$u_username]);
			if( count($statement->fetchAll(PDO::FETCH_ASSOC)) > 0 )
			{
				//User already exists
				// self::print(['signup_status'=>false,'msg'=>'User already exists']);
				return false;
			}else
			{
				return true;
			}
		}

		//Login
		public static function Login( $param ){
			$username = $param['u_username'] != null ? $param['u_username'] : self::error(['status'=>false,'msg'=>'Username is null']);
			$password = $param['u_password'] != null ? $param['u_password'] : self::error(['status'=>false,'msg'=>'Password is null']);


			$query = "SELECT * FROM users where u_username = :u_username";
			$nil = self::$con->prepare($query);
			$nil->execute(['u_username'=>$username]);
			$nol = $nil->fetchAll();
			if( $nol[0]['u_password'] == Tama64::__encrypt(md5($password)) ){
				self::print(['login_status'=>true,'msg'=>'Login Successfuly.','u_username'=>$nol[0]['u_username'],'u_name'=>$nol[0]['u_name'],'token'=>$nol[0]['token'],'role'=>$nol[0]['role']]);
			}else{
				self::print(['login_status'=>false,'msg'=>'Login Unsuccessfuly.']);
			}

		}
		static function print($msg){
			echo json_encode($msg);
		}
		public static function error($msg){
			echo json_encode($msg);
		}
	}

?>