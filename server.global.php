<?php

	class _{
		private static $instance = null;
		public static function getInstance($PARAM)
		{
			if( self::$instance == null )
			{
				self::$instance = new _($PARAM);
			}
			return self::$instance;
		}
		public static $param = null;
		public  function __construct($PARAM)
		{
			self::$param = $PARAM;
		}

		public static function get()
		{
			return self::$param['GET'];
		}
		public static function post()
		{
			return self::$param['POST'];
		}
		public static function method()
		{
			return self::$param['METHOD'];
		}
		
	}

?>