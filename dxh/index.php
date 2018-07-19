<?php
include_once './mysql.php';

//$pdo = DB::getIntense();
session_start();
class Active{
    public function index()
    {
        /**
        @判断是否登录
         */
        if (isset($_SESSION['openid']) && $_SESSION['openid']){
            return ['code'=>200,'msg'=>'用户已经登录'];
        }else{
            return ['code'=>302,'msg'=>'请登录'];
        }
    }

    /**
     用户授权
     */
    public function auth()
    {
        $_SESSION['openid'] = '123456789';
        return ['code'=>200,'msg'=>'登录成功'];
    }

    /**
     用户订单
     */
    public function getOrder()
    {
        $pdo = DB::getIntense();
        $res = $pdo->query('select * from active_buyer where openid="'.$_SESSION['openid'].'" limit 1');
        $row = $res->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($row)){
            return ['code'=>200,'msg'=>'ok','data'=>$row];
        }
        return ['code'=>301,'msg'=>'暂无订单'];
    }

    /**
     @购买
     */
    public function buy()
    {
        $openid = $_SESSION['openid'];
        $nick = $_POST['nick'];
        $num = $_POST['num'];
        $phonenum = $_POST['phonenum'];
        $address  = $_POST['address'];
        if (!isset($_SESSION['openid']) || !$_SESSION['openid']){
            return ['code'=>301,'msg'=>'用户未登录'];
        }
        if (!preg_match('/^\d{11}$/')){
            return ['code'=>400,'msg'=>'手机号格式不正确'];
        }
        if (!trim($address)){
            return ['code'=>400,'msg'=>'收货地址必须填写'];
        }
        if ($num!=1){
            $num = 1;
        }
        $pdo = DB::getIntense();
        $pdo->beginTransaction();

        $repertory = $pdo->query('select repertory,starttime,endtime from active_goods where id=1 for update');
        $repertory_num = $repertory->fetchAll(PDO::FETCH_ASSOC);
        if ($repertory_num[0]['repertory']<=0){
            return ['code'=>400,'msg'=>'已售罄'];
        }
        if ($repertory_num[0]['starttime']<time()){
            return ['code'=>400,'msg'=>'活动还未开始'];
        }
        if ($repertory_num[0]['endtime']>time()){
            return ['code'=>400,'msg'=>'活动已结束'];
        }
        $buy_sql = 'insert into active_buyer(openid,nick,num,phonenum,address) values (:openid,:nick,:num,:phonenum,:address)';
        $query = $pdo->prepare($buy_sql);
        $result = $query->execute(array(
            ':openid'=>$openid,
            ':nick'=>$nick,
            ':num'=>$num,
            ':phonenum'=>$phonenum,
            ':address'=>$address

        ));
        if (!$result){
            $pdo->rollback();
            return ['code'=>400,'msg'=>'购买失败'];
        }
        $goods_sql = 'update active_goods set num=num-1 where id=1';
        $res = $pdo->exec($goods_sql);
        if ($res<=0){
            $pdo->rollback();
            return ['code'=>400,'msg'=>'更改商品表失败'];
        }
    }
}


$controller = new Active();

$action  = isset($_GET['action'])?$_GET['action']:'index';

var_dump($controller->$action());













