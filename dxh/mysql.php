<?php
class DB{

    protected static $intense;

    private function __construct()
    {

    }

    /**
     * @return mixed
     */
    public static function getIntense()
    {
        if (self::$intense==null){
            self::$intense = new PDO('mysql:host=localhost;dbname=dxh', 'root', 'root');;
        }
        return self::$intense;
    }


}