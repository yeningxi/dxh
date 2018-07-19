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
            return json_encode(['code'=>200,'msg'=>'用户已经登录']);
        }else{
            return json_encode(['code'=>302,'msg'=>'请登录']);
        }
    }

    /**
     用户授权
     */
    public function auth()
    {
        $_SESSION['openid'] = '123456789';
        return json_encode(['code'=>200,'msg'=>'登录成功']);
    }

    /**
     用户订单
     */
    public function getOrder()
    {
        $pdo = DB::getIntense();
        $res = $pdo->query('select * from active_buyer where openid="'.$_SESSION['openid'].'" limit 1');
        if (!$res) {
            # code...
            return json_encode(['code'=>301,'msg'=>'暂无订单']);
        }
        $row = $res->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($row)){
            return json_encode(['code'=>200,'msg'=>'ok','data'=>$row]);
        }
        
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
            return json_encode(['code'=>301,'msg'=>'用户未登录']);
        }
        if (!preg_match('/^\d{11}$/',$phonenum)){
            return json_encode(['code'=>400,'msg'=>'手机号格式不正确']);
        }
        if (!trim($address)){
            return json_encode(['code'=>400,'msg'=>'收货地址必须填写']);
        }
        if ($num!=1){
            $num = 1;
        }
        $pdo = DB::getIntense();
        $pdo->beginTransaction();

        $repertory = $pdo->query('select repertory,starttime,endtime from active_goods where id=1 for update');
        $repertory_num = $repertory->fetchAll(PDO::FETCH_ASSOC);
        if (empty($repertory_num)) {
            # code...
            return json_encode(['code'=>400,'msg'=>'没有活动商品']);
        }
        if ($repertory_num[0]['repertory']<=0){
            return json_encode(['code'=>400,'msg'=>'已售罄']);
        }
        if ($repertory_num[0]['starttime']<time()){
            return json_encode(['code'=>400,'msg'=>'活动还未开始']);
        }
        if ($repertory_num[0]['endtime']>time()){
            return json_encode(['code'=>400,'msg'=>'活动已结束']);
        }

        $freeorder = $pdo->query('select count(*) as count from active_buyer');
        $buyuser = $freeorder->fetchAll(PDO::FETCH_ASSOC);
        if ($buyuser['0']['count']<=100) {
            # code...
            $status=1;
        }else{
            $status=0;
        }
        $buy_sql = 'insert into active_buyer(openid,nick,num,phonenum,address,status) values (:openid,:nick,:num,:phonenum,:address,:status)';
        $query = $pdo->prepare($buy_sql);
        $result = $query->execute(array(
            ':openid'=>$openid,
            ':nick'=>$nick,
            ':num'=>$num,
            ':phonenum'=>$phonenum,
            ':address'=>$address,
            ':status'=>$status
        ));
        if (!$result){
            $pdo->rollback();
            return json_encode(['code'=>400,'msg'=>'购买失败']);
        }
        $goods_sql = 'update active_goods set num=num-1 where id=1';
        $res = $pdo->exec($goods_sql);
        if ($res<=0){
            $pdo->rollback();
            return json_encode(['code'=>400,'msg'=>'更改商品表失败']);
        }
        $pdo->commit();
        return json_encode(['code'=>200,'msg'=>'下单成功，请尽快支付']);
    }

    /**
     @支付
    */
    public function pay()
    {

    }

    /**
     支付回调   
    */
    public function payback
    {   
        $pdo = DB::getIntense();
    
        //失败
        //TODO
        //成功

        $sql = 'update active_buyer set status=1 where openid="'.$_SESSION['openid'].'"';
        $res = $pdo->exec($sql);
        return json_encode(['code'=>200,'msg'=>'支付成功']);
    }
}


$controller = new Active();

$action  = isset($_GET['action'])?$_GET['action']:'index';

var_dump($controller->$action());













