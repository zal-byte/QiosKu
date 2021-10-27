<?php

	class Product{
		private static $instance = null;
		public static function getInstance(){
			if( self::$instance == null){
				self::$instance = new Product();
			}
			return self::$instance;
		}
		public static $con;
		public function __construct(){
			self::$con = Connection::getConnection();
		}
		public static function showProduct( $additional = null){
			
			$res['showProduct'] = array();

			$query = "select * from products order by p_id desc";

			// $query .= isset($additional['manual']) ? (isset($additional['manual']['query']) ? $additional['manual']['query'] : self::echo(['showProduct'=>['status'=>false,'msg'=>'Null Queries']])) : self::default();
			// $param = isset($additional['manual']['query']) ? $additional['manual']['parameter'] : null;




			$stat = self::$con->prepare($query);
			if ( $stat->execute() )
			{
				// self::echo(['showProduct'=>true,"data"=>$stat->fetchall(PDO::FETCH_ASSOC)]);
				array_push($res['showProduct'], array('status'=>true,'data'=>$stat->fetchAll(PDO::FETCH_ASSOC)));
			}else
			{
				// self::echo(['showProduct'=>false]);
				array_push($res['showProduct'], array('status'=>false,'msg'=>'Cannot get product data'));
			}
			// if($param!=null)
			// {

			// 	if( $stat->execute($param) )
			// 	{

			// 		self::echo(['showProduct'=>true,$stat->fetch(PDO::FETCH_ASSOC)]);

			// 	}else
			// 	{

			// 		self::echo(['showProduct'=>false]);

			// 	}

			// }else
			// {

			// 	if( $stat->execute() )
			// 	{

			// 		self::echo(['showProduct'=>true,$stat->fetch(PDO::FETCH_ASSOC)]);

			// 	}else
			// 	{

			// 		self::echo(['showProduct'=>false]);

			// 	}

			// }
			self::print($res);

		}

		///Pake bahasa indonesia, supaya ga pusing.


		private static function removeFromCart( $c_id )
		{
			$query = "DELETE FROM cart WHERE c_id = :c_id";
			$statement = self::$con->prepare( $query );
			if ( $statement->execute(['c_id'=>$c_id]) )
			{
				return true;
			}else
			{
				return false;
			}
		}

		public static function pesanProdukDariKeranjang( $kriteria )
		{

			$c_id = isset($kriteria['c_id']) ? $kriteria['c_id'] : self::echo(['buyProductCart'=>false, 'msg'=>'missing c_id']);

			$default_approval_code = "0";

			// print_r($kriteria);

			$data = self::fetch_from_cart( $c_id );
			$query = "INSERT INTO transaction (`p_id`,`u_id`,`quantity`,`payment`,`approvalCode`) VALUES (:p_id, :u_id, :quantity, :payment, :approvalCode)";
			$statement = self::$con->prepare($query);
			if ( $statement->execute(['p_id'=>$data['p_id'], 'u_id'=>$data['u_id'], 'quantity'=>$data['quantity'], 'payment'=>$data['payment'], 'approvalCode'=>$default_approval_code]) )
			{
				if( self::removeFromCart( $c_id ) == true )
				{
					self::echo(['buyProductCart'=>true, 'msg'=>'Successfuly']);
				}else
				{
					self::echo(['buyProductCart'=>false,'msg'=>'Unsuccessfuly']);
				}
			}else
			{
				self::echo(['buyProductCart'=>false, 'msg'=>'Couldnt execute queries']);
			}
			
		}
		private static function fetch_from_cart( $c_id )
		{
			$query = "SELECT * FROM cart WHERE c_id = :c_id ";
			$statement = self::$con->prepare($query);
			$statement->execute(['c_id'=>$c_id]);

			$stat = $statement->fetchAll(PDO::FETCH_ASSOC);
			$stat = $stat[0];
			$arr = array();
			$arr['c_id'] = $stat['c_id'];
			$arr['p_id'] = $stat['p_id'];
			$arr['u_id'] = $stat['u_id'];
			$arr['quantity'] = $stat['quantity'];
			$arr['payment'] = $stat['payment'];

			return $arr;
		}

		public static function pesanProduk( $kriteria )
		{
			$p_id = isset($kriteria['p_id']) ? $kriteria['p_id'] : self::echo(['buyProduct'=>false,'msg'=>'missing p_id']);
			$u_id = isset($kriteria['u_id']) ? $kriteria['u_id'] : self::echo(['buyProduct'=>false,'msg'=>'missing u_id']);
			$quantity = isset($kriteria['quantity']) ? $kriteria['quantity'] : self::echo(['buyProduct'=>false,'msg'=>'missing quantity']);
			$payment = isset($kriteria['payment']) ? $kriteria['payment'] : self::echo(['buyProduct'=>false,'msg'=>'missing payments']);
			$default_approval_code = "0";


			print_r( $kriteria );

			//Apakah masih ada stok produknya ?

			if( self::inOrder( $p_id, $u_id ) == false )
			{
				$is_available = self::is_available( $p_id );
				if( $is_available[0] == true )
				{
					$arith = $is_available[1] - $quantity;
					if( $arith != ( $arith <= 0 ) )
					{
						//Melanjutkan pembelian
						$kueri = "INSERT INTO transaction (`p_id`,`u_id`,`quantity`,`approvalCode`,`payment`) VALUES (:p_id,:u_id,:quantity,:approvalCode,:payment)";
						$pernyataan = self::$con->prepare($kueri);
						if ( $pernyataan->execute(['p_id'=>$p_id,'u_id'=>$u_id,'quantity'=>$quantity,'approvalCode'=>$default_approval_code,'payment'=>$payment]) )
						{
							//Berhasil dimasukan dalam proses transaksi

							//mengurangi jumlah produk pada tabel produk
							if (self::decrease_quantity_of_product_id( $p_id , $quantity ) )
							{
								self::echo(['buyProduct'=>true,'msg'=>'Successfuly']);
							}else
							{
								self::echo(['buyProduct'=>false,'msg'=>'Unsuccessfuly']);
							}
						}else
						{
							self::echo(['buyProduct'=>false,'msg'=>'Unsuccessfuly']);
							//Gagal menambahkan kedalam proses transaksi
						}
					}else
					{
						//Melebihi jumlah pesanan
						self::echo(['buyProduct'=>false,'msg'=>'Out of stock']);
					}
				}else
				{
					//Produk habis
					self::echo(['buyProduct'=>false,'msg'=>'Out of Stock']);
				}
			}else
			{
				//Produk ini sedang dalam proses transaksi
				self::echo(['buyProduct'=>false, 'msg'=>'InOrder product']);
			}

		}

		//

		private static function decrease_quantity_of_product_id( $p_id, $num_of_quantity )
		{


			$quantity = self::getQuantity( $p_id );
			$quantity = $quantity - $num_of_quantity;

			$query = "UPDATE products SET `p_quantity`=:p_quantity WHERE p_id = :p_id";
			$statement = self::$con->prepare($query);
			if ( $statement->execute(['p_quantity'=>$quantity,'p_id'=>$p_id]) )
			{
				return true;
			}else
			{
				return false;
			}


		}

		private static function getQuantity( $p_id )
		{
			$query = "SELECT p_quantity FROM products WHERE p_id = :p_id ";
			$statement = self::$con->prepare($query);
			$statement->execute(['p_id'=>$p_id]);

			$stat = $statement->fetchAll(PDO::FETCH_ASSOC);

			return $stat[0]['p_quantity'];
		}

		//

		private static function inOrder( $p_id, $u_id )
		{
			$query = "SELECT * FROM `transaction` WHERE p_id = :p_id AND u_id = :u_id";
			$statement = self::$con->prepare($query);
			$statement->execute(['u_id'=>$u_id,'p_id'=>$p_id]);

			$stat = $statement->fetchAll(PDO::FETCH_ASSOC);
			if( count( $stat ) > 0 )
			{
				return true;
			}else
			{
				return false;
			}
		}

		//

		public static function tambahKeranjang( $kriteria )
		{
			$p_id = isset($kriteria['p_id']) ? $kriteria['p_id'] : self::echo(['addCart'=>false,'msg'=>'missing p_id']);
			$u_id = isset($kriteria['u_id']) ? $kriteria['u_id'] : self::echo(['addCart'=>false,'msg'=>'missing u_id']);
			$quantity = isset($kriteria['quantity']) ? $kriteria['quantity'] : self::echo(['addCart'=>false,'msg'=>'missing quantity']);
			$payment = isset($kriteria['payment']) ? $kriteria['payment'] : self::echo(['addCart'=>false,'msg'=>'missing payment method']);
			is_numeric($p_id) ? null : self::echo(['addCart'=>false,'msg'=>'p_id is not numeric values']);
			is_numeric($u_id) ? null : self::echo(['addCart'=>false,'msg'=>'u_id is not numeric values']);
			is_numeric($quantity) ? null : self::echo(['addCart'=>false,'msg'=>'quantity is not numeric values']);


			//Cek dulu apakah ada yang sama didalam keranjang dengan produk yang sama

			// self::is_available_cart( $p_id , $u_id ) == true ? self::echo(['addCart'=>false,'msg'=>'SameProduct']) && die("") : null;

			//Cek dulu ketersedian stok pada produk

			// self::is_available( $p_id ) == true ? null : self::echo(['addCart'=>false,'msg'=>'Out of stock']) && die("");

			if( self::is_available_cart( $p_id, $u_id ) == true )
			{
				self::echo(['addCart'=>false,'msg'=>'SameProduct']);
			}else
			{
				$is_available = self::is_available( $p_id );
				if( $is_available[0] == true )
				{
					$kueri = "INSERT INTO cart (`u_id`,`p_id`,`quantity`,`payment`) VALUES (:u_id,:p_id,:quantity,:payment)";
					$pernyataan = self::$con->prepare($kueri);
					if ( $pernyataan->execute(['u_id'=>$u_id,'p_id'=>$p_id,'quantity'=>$quantity,'payment'=>$payment]) )
					{
						self::echo(['addCart'=>true,'msg'=>'Successfuly']);
					}else
					{
						self::echo(['addCart'=>false,'msg'=>'Unsuccessfuly']);
					}					
				}else
				{
					self::echo(['addCart'=>false,'msg'=>'Out of stock']);
				}
			}
		}

		private static function is_available_cart( $p_id, $u_id )
		{
			$query = "SELECT * FROM cart WHERE p_id = :p_id AND u_id = :u_id";
			$statement = self::$con->prepare($query);
			$statement->execute(['p_id'=>$p_id,'u_id'=>$u_id]);

			$stat = $statement->fetchAll(PDO::FETCH_ASSOC);
			if( count( $stat) > 0 )
			{
				return true;
			}else
			{
				return false;
			}
		}

		private static function is_available( $id )
		{
			$query = "SELECT p_quantity FROM products WHERE p_id = :p_id";
			$statement = self::$con->prepare($query);
			$statement->execute(['p_id'=>$id]);

			$stat = $statement->fetchAll(PDO::FETCH_ASSOC);

			if ( $stat[0]['p_quantity'] <= 0 )
			{
				return [false, $stat[0]['p_quantity']];
			}
			else
			{
				return [true, $stat[0]['p_quantity']];
			}
		}

		//
		public static function detailProduk( $p_id )
		{
			$kueri = "select * from products left join users on users.u_username = products.u_username where p_id = :p_id ";
			$pernyataan = self::$con->prepare($kueri);
			$pernyataan->execute(['p_id'=>$p_id]);
			$status = $pernyataan->fetchAll(PDO::FETCH_ASSOC);

			$filter['products'] = array();
			$filter['users'] = array();
			for($i=0;$i<count($status);$i++)
			{
				$get = $status[$i];

				//dari tabel produk
				$filter['products']['p_id'] = $get['p_id'];
				$filter['products']['p_name'] = $get['p_name'];
				$filter['products']['p_description'] = $get['p_description'];
				$filter['products']['p_price'] = $get['p_price'];
				$filter['products']['p_quantity'] = $get['p_quantity'];
				$filter['products']['p_identifier'] = $get['p_identifier'];
				$filter['products']['p_image'] = $get['p_image'];

				//dari tabel pengguna petugas
				$filter['users']['id'] = $get['id'];
				$filter['users']['u_name'] = $get['u_name'];
				$filter['users']['u_username'] = $get['u_username'];
				$filter['users']['u_phone'] = $get['u_phone'];
				$filter['users']['u_address'] = $get['u_address'];
				$filter['users']['u_img'] = $get['u_img'];
				$filter['users']['role'] = $get['role'];
			}

			self::echo(['productDetail'=>true,'data'=>$filter]);
		}

		public static function newProduct($data)
		{
			$upload_path = "asset/img/";
			$p_name = $data['p_name'];
			$p_price = $data['p_price'];
			$p_quantity = $data['p_quantity'];
			$p_description = $data['p_description'];
			$p_identifier = "product_" . base64_encode(strtolower($p_name));
			$p_image = $upload_path . str_replace(' ', '_', $p_name) ."_".time().".jpg";
			$u_id = $data['u_id'];
			$query = "INSERT products (`p_name`,`p_description`,`p_price`,`p_quantity`,`p_image`,`p_identifier`,`u_id`) VALUES (:p_name,:p_description,:p_price,:p_quantity,:p_image,:p_identifier,:u_id)";

			$statement = self::$con->prepare($query);
			isset($data['p_image_data']) ? (self::save($data['p_image_data'], $p_image) == true ? self::echo(['newProduct'=>true,'msg'=>'imgUpload Success']) : self::echo(['newProduct'=>'false','msg'=>'imgUpload Error'])) : null;
			if ( $statement->execute(['p_name'=>$p_name,'p_description'=>$p_description,'p_price'=>$p_price,'p_quantity'=>$p_quantity,'p_image'=>$p_image,'p_identifier'=>$p_identifier,'u_id'=>$u_id]) )
			{

				self::echo( ['newProduct'=>true,'msg'=>'Product has been added'] );

			}else
			{

				self::echo( ['newProduct'=>false,'msg'=>'Cannot add new product'] );

			}
		}

		private static function save($base64, $path)
		{
			$bit = base64_decode($base64);
			if ( file_put_contents($path, $bit) )
			{
				return true;
			}else
			{
				return false;
			}
		}

		private static function echo($string)
		{
			// header("Content-Type: application/json");
			echo json_encode($string);

		}

		private static function default()
		{

			return 'select * from products';

		}


		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		//user

		

		public static function removeMyCart( $c_id )
		{
			$query = "DELETE FROM cart WHERE c_id = :c_id";
			$statement = self::$con->prepare( $query );
			if ( $statement->execute(['c_id'=>$c_id]) )
			{
				self::echo(['removeMyCart'=>true,'msg'=>'Successfuly']);
			}else
			{
				self:echo(['removeMyCart'=>false,'msg'=>'Couldnt execute queries']);
			}

		}

		public static function fetchMyTransaction( $u_id )
		{
			/***
			 * transaction_id
			 * transaction_u_id
			 * transaction_quantity
			 * transaction_payment
			 * transaction_approval_code
			 * **/

			$query = "SELECT transaction.p_id, transaction.u_id, transaction.t_id, transaction.quantity, transaction.approvalCode, transaction.payment, products.p_name, products.p_description, products.p_price, products.p_quantity, products.p_image FROM transaction LEFT JOIN products ON products.p_id = transaction.p_id WHERE transaction.u_id = :u_id";

			$statement = self::$con->prepare( $query );
			$statement->execute(['u_id'=> $u_id]);

			$stat = $statement->fetchAll(PDO::FETCH_ASSOC);
			$arr = array();
			if ( count( $stat ) > 0 )
			{
				$stat = $stat[0];
				$arr['t_id'] = $stat['t_id'];
				$arr['p_id'] = $stat['p_id'];
				$arr['quantity'] = $stat['quantity'];
				$arr['payment'] = $stat['payment'];
				$arr['p_name'] = $stat['p_name'];
				$arr['p_description'] = $stat['p_description'];
				$arr['p_price'] = $stat['p_price'];
				$arr['p_quantity'] = $stat['p_quantity'];
				$arr['p_image'] = $stat['p_image'];

				self::echo(['fetchMyTransaction'=>true,'data'=>$arr]);

			}else
			{
				self::echo(['fetchMyTransaction'=>false, 'msg'=>'Unsuccessfuly']);
			}
		}

		public static function fetchMyCart ( $u_id )
		{
			/***
			 * cart_id
			 * cart_u_id
			 * cart_payment
			 * cart_quantity
			 * 
			 * products_name
			 * products_description
			 * products_quantity
			 * products_price
			 * products_image
			 * ***/
			$query = "SELECT cart.c_id, cart.u_id, cart.p_id, cart.payment, cart.quantity, products.p_name, products.p_description, products.p_price, products.p_quantity, products.p_image FROM cart left join products on products.p_id = cart.p_id WHERE cart.u_id = :u_id";
			$statement = self::$con->prepare( $query );
			$statement->execute(['u_id'=>$u_id]);
			$stat = $statement->fetchAll(PDO::FETCH_ASSOC);

			if ( count( $stat ) > 0 )
			{
				self::echo(['fetchMyCart'=>true,'data'=>$stat[0]]);
			}else
			{
				self::echo(['fetchMyCart'=>false,'msg'=>'Couldnt fetch cart data']);
			}
		}

	}

?>