<?php
// Version 1 API

	//Server
	define('req_method)', $_SERVER['REQUEST_METHOD']);
	//
	include 'lib/tama64.php';
	include 'lib/product.php';	
	include 'lib/connection.php';
	include 'lib/token.php';
	include 'lib/auth.php';
	include 'lib/profile.php';
	include "server.global.php";

	Tama64::getInstance();
	Product::getInstance();
	Auth::getInstance();
	Token::getInstance();
	Profile::getInstance();
	$con = Connection::getInstance();

	$POST = isset($_POST) ? $_POST : null;
	$GET = isset($_GET) ? $_GET : null;
	$METHOD = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;

	_::getInstance(['GET'=>$GET,'POST'=>$POST,'METHOD'=>$METHOD]);


	//Permintaan GET
	//Biasanya digunakan untuk menampilkan list produk, informasi profile pengguna, dan lain lain.
	//Digunakan token untuk, melihat informasi profile pengguna, menambahkan produk, membeli produk, komentar, update profile supaya tidak bisa diakses dengan mudah oleh pengguna orang lain.

	$token = "VjJ0V2FrNVhUbk5qUm1oUFZteEtiMVpxU2xOTlZuQkhZVVZPWVdGNlJsWlZNV2gyVUZFOVBRPT0=";
	// print_r( Token::REQUEST_TOKEN([ 'base'=>$token]) );

	// echo Token::encode(['base'=>'updateProfileToken_123','count'=>5]);



	if( _::method() == 'GET' ){

		$req = isset(_::get()['req']) ? _::get()['req'] : die('Something went wrong');

		if ( $req == 'tokenEncoder' ) 
		{
			echo Token::encode(['base'=>_::get()['q'],'count'=>5]);
		}else if ( $req == "tokenDecoder" )
		{
			echo Token::decode(['base'=>_::get()['q'],'count'=>5]);
		}


		if( $req == 'showProduct' ){

			Product::showProduct();

		}else if( $req == "fetchProfile" ){

			$u_username = isset(_::get()['u_username']) ? _::get()['u_username'] : Auth::error(['fetchProfile'=>false,'msg'=>'Username is null']);
			$token = isset(_::get()['token']) ? _::get()['token'] : Auth::error(['fetchProfile'=>false,'msg'=>'Token Error (0)']);
			$user_token = isset(_::get()['user_token']) ? _::get()['user_token'] : Auth::error(['fetchProfile'=>false,'msg'=>'Token Error (1)']);
			//Required 2 tokens, REQUEST_TOKEN and USER_TOKEN

			if ( Token::REQUEST_TOKEN( ['base'=>$token] )[1] == Token::decode( ['base'=>$token, 'count'=>5 ] ) )
			{

				// print_r( Token::BASE_TOKEN(  $u_username ) );

				if( Token::decode( ['base'=> $user_token, 'count'=>5 ] ) == Token::decode( ['base'=>Token::BASE_TOKEN( $u_username )['token'], 'count'=>5 ] ) )
				{

					Profile::myData( $u_username );

				}else
				{

					Auth::error( ['fetchProfile'=>null, 'status'=>false, 'msg'=>'User token error'] );

				}

			}else
			{

				Auth::error( ['fetchProfile'=>null,'status'=>false,'msg'=>'Token error'] );

			}

		}else if( $req=='productDetail')
		{
			$p_id = isset(_::get()['p_id']) ? _::get()['p_id'] : die("p_id parameter not found");
			Product::detailProduk($p_id);
		}else if( $req == "fetchMyCart" )
		{
			$u_id = isset(_::get()['u_id']) ? _::get()['u_id'] : die('missing u_id');
			$token = isset(_::get()['token']) ? _::get()['token'] : die('missing token');
			$u_username = isset(_::get()['u_username']) ? _::get()['u_username'] : die('missing u_username');
			$user_token = isset(_::get()['user_token']) ? _::get()['user_token'] : die("missing user_token");


			if ( Token::REQUEST_TOKEN( ['base'=>$token] )[1] == Token::decode( ['base'=>$token, 'count'=>5 ] ) )
			{

				// print_r( Token::BASE_TOKEN(  $u_username ) );

				if( Token::decode( ['base'=> $user_token, 'count'=>5 ] ) == Token::decode( ['base'=>Token::BASE_TOKEN( $u_username )['token'], 'count'=>5 ] ) )
				{


					Product::fetchMyCart( $u_id );

				}else
				{

					Auth::error( ['fetchMyCart'=>null, 'status'=>false, 'msg'=>'User token error'] );

				}

			}else
			{

				Auth::error( ['fetchMyCart'=>null,'status'=>false,'msg'=>'Token error'] );

			}

		}else if( $req == "fetchMyTransaction")
		{
			$u_username = isset(_::get()['u_username']) ? _::get()['u_username'] : die('missing u_username');
			$u_id = isset(_::get()['u_id']) ? _::get()['u_id'] : die('missing u_id');
			$token = isset(_::get()['token']) ? _::get()['token'] : die('missing token');
			$user_token = isset(_::get()['user_token']) ? _::get()['user_token'] : die('missing user_token');

			if ( Token::REQUEST_TOKEN( ['base'=>$token] )[1] == Token::decode( ['base'=>$token, 'count'=>5 ] ) )
			{

				// print_r( Token::BASE_TOKEN(  $u_username ) );

				if( Token::decode( ['base'=> $user_token, 'count'=>5 ] ) == Token::decode( ['base'=>Token::BASE_TOKEN( $u_username )['token'], 'count'=>5 ] ) )
				{

					Product::fetchMyTransaction( $u_id );

				}else
				{

					Auth::error( ['fetchMyTransaction'=>null, 'status'=>false, 'msg'=>'User token error'] );

				}

			}else
			{

				Auth::error( ['fetchMyTransaction'=>null,'status'=>false,'msg'=>'Token error'] );

			}

		}

	}else if ( _::method() == 'POST' ){
		$req = isset(_::post()['req']) ? _::post()['req'] : die("Something went wrong ( POST )");

		if( $req == 'authLogin' ){

			$param = array('u_username'=>_::post()['u_username'],'u_password'=>_::post()['u_password']);

			if( Token::REQUEST_TOKEN( ['base'=>_::post()['token']] )[1] == Token::decode( ['base'=>_::post()['token'], 'count'=>5] )){

				Auth::Login( $param );

			}else{

				Auth::error( ['login_status'=>false, 'msg'=>'Token Error'] );

			}

		}else if ( $req == 'authSignup' ){

			$param = array("u_name"=>_::post()['u_name'],'u_username'=>_::post()['u_username'],'u_phone'=>_::post()['u_phone'],'u_password'=>_::post()['u_password'],'u_email'=>_::post()['u_email']);

			// print_r( Token::REQUEST_TOKEN( ['base'=>_::post()['tokens']] ) );
			if( Token::REQUEST_TOKEN( ['base'=>_::post()['token']] )[1] == Token::decode( ['base'=>_::post()['token'],'count'=>5]) ){

				Auth::Signup ( $param );

			}else{

				Auth::error(['signup_status'=>false, 'Token Error']);

			}

		}else if ( $req == "updateProfile" ){
			$latom = array();
			$u_username = isset(_::post()['u_username']) ? _::post()['u_username'] : Auth::error(['updateProfile'=>false,'msg'=>'Username null']);
			$token = isset(_::post()['user_token']) ? _::post()['user_token'] : Auth::error(['updateProfile'=>false,'msg'=>'User token error']);

			isset(_::post()['image']) ? Profile::Image( ['base'=> Token::decode( ['base'=>_::post()['image'], 'count'=>1 ] ) ] ) : null;
			isset(_::post()['u_name']) ? array_push($latom, ['u_name'=>_::post()['u_name']]) : null;
			isset(_::post()['u_email']) ? array_push($latom, ['u_email'=>_::post()['u_email']]) : null;
			isset(_::post()['u_phone']) ? array_push($latom, ['u_phone'=>_::post()['u_phone']]) : null;
			isset(_::post()['u_password']) ? array_push($latom, ['u_password'=>_::post()['u_password']]) : null;
			isset(_::post()['u_address']) ? array_push($latom, ['u_address'=>_::post()['u_address']]) : null;

			if( Token::decode( ['base'=>_::post()['token'], 'count'=>5] ) == Token::REQUEST_TOKEN( ['base'=>_::post()['token']] )[1] )
			{

				if( Token::decode( ['base'=>$token, 'count'=>5] ) == Token::decode( ['base'=>Token::BASE_TOKEN( $u_username )['token'],'count'=>5 ] ) )
				{

					Profile::updateMyData( $latom , $u_username);

				}else
				{

					Auth::error( [ 'updateMyData'=>false, 'msg'=>'Token error' ] );

				}

			}else
			{

				Auth::error( ['updateMyData'=>false, 'msg'=>'Request token error'] );

			}


		}else if( $req == "newProduct" )
		{
			$p_name = "";
			$p_description = "";
			$p_price = "";
			$p_quantity = "";
			$p_image_data="";
			$u_username = isset(_::post()['u_username']) ? _::post()['u_username'] : die('Username error');
			$u_id = isset(_::post()['u_id']) ? _::post()['u_id'] : die("u_id error");
			$user_token = isset(_::post()['user_token']) ? _::post()['user_token'] : die("Usertoken Null");
			$token = isset(_::post()['token']) ? _::post()['token'] : die("Token error");
			isset(_::post()['p_name']) ? $p_name .= _::post()['p_name'] : die("Error");
			isset(_::post()['p_description']) ? $p_description .= _::post()['p_description'] : die('Error');
			isset(_::post()['p_price']) ? $p_price .= _::post()['p_price'] : die("Error");
			isset(_::post()['p_quantity']) ? $p_quantity .= _::post()['p_quantity'] : die("Error");
			isset(_::post()['p_image_data']) ? $p_image_data .= _::post()['p_image_data'] : null;


			if( Token::decode( ['base'=>$token, 'count'=>5] ) == Token::REQUEST_TOKEN( ['base'=>$token] )[1] )
			{

				if( Token::decode( ['base'=>$user_token, 'count'=>5] ) == Token::decode( ['base'=>Token::BASE_TOKEN( $u_username )['token'],'count'=>5 ] ) )
				{

					if( Profile::myRole( $u_username )['role'] == "petugas" )
					{

						Product::newProduct(['p_name'=>$p_name,'p_description'=>$p_description,'p_price'=>$p_price,'p_quantity'=>$p_quantity,'p_image_data'=>$p_image_data,'u_username'=>$u_username,'u_id'=>$u_id]);

					}else
					{

						Auth::error( ['newProduct'=>false, 'msg'=>"You're not have a permission"]);

					}
				}else
				{

					Auth::error( [ 'newProduct'=>false, 'msg'=>'Token error' ] );

				}

			}else
			{

				Auth::error( ['newProduct'=>false, 'msg'=>'Request token error'] );

			}

		}
		//
		else if( $req == "addCart" )
		{
			$p_id = _::post()['p_id'];
			$u_id = _::post()['u_id'];
			$quantity = _::post()['quantity'];
			$payment = _::post()['payment'];

			$user_token = _::post()['user_token'];
			$token = _::post()['token'];
			$u_username = _::post()['u_username'];

			if( Token::decode( ['base'=>$token, 'count'=>5] ) == Token::REQUEST_TOKEN( ['base'=>$token] )[1] )
			{

				if( Token::decode( ['base'=>$user_token, 'count'=>5] ) == Token::decode( ['base'=>Token::BASE_TOKEN( $u_username )['token'],'count'=>5 ] ) )
				{
						Product::tambahKeranjang(['p_id'=>$p_id,'u_id'=>$u_id,'quantity'=>$quantity,'payment'=>$payment]);
				}else
				{

					Auth::error( [ 'addCart'=>false, 'msg'=>'Token error' ] );

				}

			}else
			{

				Auth::error( ['addCart'=>false, 'msg'=>'Request token error'] );

			}

		}else if( $req == "buyProduct" )
		{
			$p_id = isset(_::post()['p_id']) ? _::post()['p_id'] : die("missing p_id");
			$u_id = isset(_::post()['u_id']) ? _::post()['u_id']  : die('missing u_id');
			$quantity = isset(_::post()['quantity']) ? _::post()['quantity'] : die('missing quantity');
			$payment = isset(_::post()['payment']) ? _::post()['payment'] : die('missing payment');
			$u_username = isset(_::post()['u_username']) ? _::post()['u_username'] : die('missing u_username');

			$token = isset(_::post()['token']) ? _::post()['token'] : die('missing token');
			$user_token = isset(_::post()['user_token']) ? _::post()['user_token'] : die('missing user_token');

			if( Token::decode( ['base'=>$token, 'count'=>5] ) == Token::REQUEST_TOKEN( ['base'=>$token] )[1] )
			{

				if( Token::decode( ['base'=>$user_token, 'count'=>5] ) == Token::decode( ['base'=>Token::BASE_TOKEN( $u_username )['token'],'count'=>5 ] ) )
				{
			
					Product::pesanProduk(['p_id'=>$p_id,'u_id'=>$u_id,'quantity'=>$quantity,'payment'=>$payment]);
			
				}else
				{

					Auth::error( [ 'buyProduct'=>false, 'msg'=>'Token error' ] );

				}

			}else
			{

				Auth::error( ['buyProduct'=>false, 'msg'=>'Request token error'] );

			}
		}
		if( $req == "buyProductCart")
		{
			// echo "ASU";
			$u_username = isset(_::post()['u_username']) ? _::post()['u_username'] : die('missing u_username');
			$c_id = isset(_::post()['c_id']) ? _::post()['c_id'] : die("missing c_id");
			$token = isset(_::post()['token']) ? _::post()['token'] : die('missing token');
			$user_token = isset(_::post()['user_token']) ? _::post()['user_token'] : die('missing user_token');

			if( Token::decode( ['base'=>$token, 'count'=>5] ) == Token::REQUEST_TOKEN( ['base'=>$token] )[1] )
			{

				if( Token::decode( ['base'=>$user_token, 'count'=>5] ) == Token::decode( ['base'=>Token::BASE_TOKEN( $u_username )['token'],'count'=>5 ] ) )
				{
						Product::pesanProdukDariKeranjang(['c_id'=>$c_id]);
				}else
				{

					Auth::error( [ 'buyProductCart'=>false, 'msg'=>'Token error' ] );

				}

			}else
			{

				Auth::error( ['buyProductCart'=>false, 'msg'=>'Request token error'] );

			}			
		}

	}
?>
