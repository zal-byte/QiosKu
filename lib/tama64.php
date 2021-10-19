<?php

    class Tama64{
        private static $instance = null;
        public static function getInstance()
        {
            if( self::$instance == null )
            {
                self::$instance = new Tama64();
            }
            return self::$instance;
        }
        public static $dict = "QwErTyUiOpAsDfGhJkLZxCvBnMqWeRtYuIoPaSdFgHjKlzXcVbNm1923456780~)(*&^%!@#$%-+_=':;<.>,/?\" \\|";
        public static $dict_l = null;
        public static $_b = 64;
        public function __construct()
        {
            self::dictToArray();
        }

        public static function __encrypt($val)
        {
            return self::_encrypt(self::_encrypt(self::ord(self::_enc(self::_enc($val)))));
        }
        public static function __decrypt($val)
        {
            return self::_dec(self::_dec(self::chrs(self::_decrypt(self::_decrypt($val)))));
        }


        ///

        public static function _enc( $value )
        {
            $value = str_split($value);
            $res = "";
            $key = str_split(self::_encrypt("key"));
            for($i = 0; $i<count($value);$i++)
            {
                    $key_c = $key[$i % count($key)];
                    $msg_c = ord( $value[$i]);
                    $res .= chr( (self::int($msg_c) + self::int($key_c)) % 127);
            }
            return $res;
        }
        public static function _dec( $value )
        {
            $value = str_split($value);
            $res = "";
            $key = str_split(self::_encrypt("key"));
            for($i = 0; $i<count($value);$i++)
            {
                    $key_c = $key[$i % count($key)];
                    $msg_c = ord( $value[$i]);
                    $res .= chr( (self::int($msg_c) - self::int($key_c)) % 127);
            }
            return $res;
        }

        private static function int($val)
        {
            return (int) $val;
        }

        ///

        private static function ord($val)
        {
            $res = "";
            $list = self::toArray($val);
            for($i = 0; $i < count($list); $i++)
            {
                $res .= (string) ord( $list[$i]) . " ";
            }
            return $res;
        }

        private static function chrs($val)
        {
            $res = "";
            $list = explode(" ",$val);
            for ($i=0;$i<count($list);$i++)
            {
                $res .= chr((int) $list[$i]);
            }
           

            return $res;
        }

        private static function dictToArray( )
        {
            self::$dict_l = str_split(self::$dict);
        }
        private static function toArray( $val )
        {
            return str_split( $val );
        }
        private static function _encrypt( $val )
        {
            $list = self::toArray( $val );
            $res = "";
            for( $i= 0;$i< count($list);$i++)
            {
                for($a =0;$a<count(self::$dict_l);$a++)
                {
                    if( self::$dict_l[$a] == $list[$i])
                    {
                        $res .= self::$dict_l[self::limit($a + self::$_b) % count(self::$dict_l)];
                    }
                }
            }
            return $res;
        }
        private static function _decrypt( $val )
        {
            $list = self::toArray( $val );
            $res = "";
            for( $i= 0;$i< count($list);$i++)
            {
                for($a =0;$a<count(self::$dict_l);$a++)
                {
                    if( self::$dict_l[$a] == $list[$i])
                    {
                        $res .= self::$dict_l[self::limit($a - self::$_b) % count(self::$dict_l)];
                    }
                }
            }
            return $res;
        }
        private static function limit($val)
        {
            if( $val < 0)
            {
                return count(self::$dict_l) + $val;
            }else{
                return $val;
            }
        }

    }


?>