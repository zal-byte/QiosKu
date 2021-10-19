<?php
	define('DIR','.tmp/token');
	define('newProduct','VjFjd2VGWXdNSGhXYTJ4VVlteHdhRlV3V21GalZuQkdWMnhPYTJKVlZqVldiVFZYVjFkV2NsWnFWbFZYUjAwMVZVWkZPVkJSUFQwPQ==');
	define('login','VjFkMGFrNVhTblJTYkdoUFZteGFjRlJYTlZOak1XeDBaSHBTYkZKVVJuaFdSbEYzVUZFOVBRPT0=');
	define('signup','VjJ0V2FrNVhUbk5qUm1oUFZteEtiMVpxU2xOTlZuQkhZVVZPWVdGNlJsWlZNV2gyVUZFOVBRPT0=');
	define('updateProfile','VjJ0YWIxRXlSWGhpUm14V1lsaG9WMVV3Vm5kTmJHUjBUVmhPYWsxWVFsaFdWelZoWVVVeGMxZHFSbGhUU0VKSFdsWlZlRTB4UWxWTlJEQTk=');
	define('fetchProfile','VmpJd2VGWXdNVWRoTTJ4b1VtdGFjVmxzVW5Oa1JteFhXa1JTYVZKc2NFbFVNV2hMV1ZaYU5sWnRNVlZXYkVwTFdsZGpPVkJSUFQwPQ==');
	define('addCart','VmpGYWExVXlSWGhTV0d4VFltMTRjbFZ0ZUV0TmJHeFZVMnhrYTFadFp6SlZiR2h6VkcxU05rMUVhejA9');
	define('buyProduct','VmpGak1WWXdOVmRXYTJ4VVlteHdhRlV3V21GalZuQkdWMnhPYTJKVlZqVldiVFZYVjFkV2NsWnFWbFZYUjAwMVZVWkZPVkJSUFQwPQ==');
	define('fetchMyCart','VmpJd2VGWXdNVWRoTTJ4b1VsUldjMVpzVlRGaU1XdDVUbFpPVjFZd2JEVmFSV1EwWVZkS1JXRkVVbFZXTTBKUFZVWkZPVkJSUFQwPQ==');
	define('fetchMyTransaction','VmpJd2VGWXdNVWRoTTJ4b1VsUldjMVp0ZUV0T1ZtUlhXWHBHYkdKSGVGbFdSM0JEWVVaYU5tSkVSbGRoTWsweFdUSjRkMWRGTlZoWGF6VlhVbGQzTWc9PQ==');
	class Token{
		private static $instance = null;
		public static function getInstance(){
			if( self::$instance == null ){
				self::$instance = new Token();
			}
			return self::$instance;
		}
		public static $con;
		public static function BASE_TOKEN( $u_username ){
			self::$con = Connection::getConnection();

			$query = "SELECT * FROM users WHERE u_username = :u_username ";
			$nil = self::$con->prepare($query);
			$nil->execute(['u_username'=>$u_username]);
			$nol = $nil->fetchAll()[0];

			return $nol;
		}
		public static function REQUEST_TOKEN( $request = null ){
			//cek semua token yang sama

			return self::tokens( $request['base'] );			
		}
		public static function tokens($base){
			$list = self::dir(DIR);

			$key = array('newProduct'=>newProduct,
			'login'=>login,
			'signup'=>signup,
			'updateProfile'=>updateProfile,
			'fetchProfile'=>fetchProfile);
			$blob = array();
			foreach($key as $yek => $val)
			{
				if ( self::decode(['base'=>$base,'count'=>5]) != null )
				{
					if( self::decode(['base'=>$base,'count'=>5]) == self::decode(['base'=>$val,'count'=>5]) )
					{
						array_push($blob, [true, self::decode(['base'=>$base, 'count'=>5])]);
						return $blob[0];						
						break;
					}else
					{
						array_push($blob, [false, self::decode(['base'=>$base, 'count'=>5])]);		
						return $blob[0];
						break;
					}
				}else
				{
					echo "[base] is not base64 values";
					break;
				}

			}

			// foreach($list as $lis){
			// 	echo $lis . "<br/>";
			// 	if ( self::decode( ['base' => file_get_contents( DIR . '/' . $lis), 'count'=>5] ) == self::decode( ['base' => $base, 'count' => 5] ) )
			// 	{
			// 		return [true, self::decode( ['base'=>$base, 'count'=>5])];
			// 		break;
			// 	}else{
			// 		return [false, self::decode( ['base'=>$base,'count'=>5])];
			// 	}

			// }
		}
		static function dir($dir){
			$dor = scandir($dir);
			$dor = array_diff($dor,['.','..']);

			return $dor;
		}
		static function encode($p){
			$boy = $p['base'];
			for( $i = 0; $i < $p['count']; $i++){
				$boy = base64_encode($boy);
			}
			return $boy;
		}
		static function decode($p){
			$boy = $p['base'];
			for( $i = 0; $i < $p['count']; $i++){
				$boy = base64_decode($boy) or die(self::print(['decode'=>false,'msg'=>'Not base64 values']));
			}
			return $boy;
		}
		static function print($data)
		{
			echo json_encode($data);
		}
	}

?>