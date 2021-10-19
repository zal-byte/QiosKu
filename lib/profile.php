<?php
	define('path','asset/img/');

	class Profile{
		private static $instance = null;
		public static function getInstance(){

			if( self::$instance == null ){

				self::$instance = new Profile();

			}

			return self::$instance;

		}


		public static $con;
		public function __construct(){

			self::$con = Connection::getConnection();

		}

		public static function myData( $u_username )
		{

			$query = "SELECT * FROM users WHERE u_username = :u_username";

			$statement = self::$con->prepare( $query );
			$statement->execute( [ 'u_username'=>$u_username ] );
			$stat = $statement->fetch(PDO::FETCH_ASSOC);

			if( count( $stat ) > 0 && $stat != null )
			{

				self::print( ['fetchProfile'=> $stat, 'status'=>true ] );

			}else
			{

				self::print( ['fetchProfile'=>null, 'status'=>false, 'msg'=>'Something went wrong'] );

			}

		}

		public static function myRole( $u_username )
		{

			$query = "SELECT u_username, role FROM users WHERE u_username = :u_username";

			$statement = self::$con->prepare($query);
			$statement->execute(['u_username'=>$u_username]);
			$stat = $statement->fetch(PDO::FETCH_ASSOC);

			return $stat;

		}

		public static function Image( $param )
		{

			$base64 = $param['base'];
			$u_username = $param['u_username'];

			if( @file_put_contents(path . $u_username, $baes64) )
			{

				if( file_exists(path . $u_username) )
				{

					return [true, true];

				}else
				{

					return [false, false];

				}

			}else
			{

				return [false, true];

			}


		}

		public static function updateMyData( $param, $u_username )
		{

			//L1
			$query = self::updateMyDataQueries( $param , $u_username);

			$statement = self::$con->prepare($query);
			if ( $statement->execute() )
			{
				self::print(['updateProfile'=>True,'status'=>true,'msg'=>'Update successfuly']);
			}
		}
		public static function updateMyDataQueries($param, $u_username)
		{
			$query = "UPDATE users SET ";
			for ( $i = 0; $i < count($param); $i++ )
			{

				foreach( $param[$i] as $key => $value )
				{
					$query .= "`".$key."`";
					if ( $i >= count( $param ) )
					{
						break;
					}
					$query .= "=". "'".$value."'";
					if ( count($param) > 0 )
					{
						if ( $i >= count($param) - 1  )
						{
							break;
						}else{
							$query .= ",";
						}
					}
				}

			}
			$query .= " where u_username = '".$u_username."'";
			return $query;
		}
		public static function print( $string )
		{

			echo json_encode( $string );

		}

	}

?>
