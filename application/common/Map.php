<?php
namespace app\common;

use think\Controller;

class Map extends Controller
{
    private static $_instance;

    const REQ_GET = 1;
    const REQ_POST = 2;

    /**
     * 单例模式
     * @return map
     */
    public static function instance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * 执行CURL请求
     */
    protected function async($api, array $params)
    {
        $ch = curl_init($api . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }

    /**
     * ip定位
     * @param string $ip
     * @return array
     * @throws Exception
     */
    public function locationByIP($ip)
    {
        //检查是否合法IP
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return 'ip地址不合法';
        }
        $params = array(
            'ak' => 'PPpGz16KGl7utwG1GaXR9MgyZnLgfE3d',
            'ip' => $ip,
            'coor' => 'bd09ll', //百度地图GPS坐标
        );
        $api = 'http://api.map.baidu.com/location/ip';
        $resp = $this->async($api, $params);
        $data = json_decode($resp, true);
        //有错误
        if ($data['status'] != 0) {
            return $data['message'];
        }
        //返回地址信息
        return array(
            'address' => $data['content']['address'],
            'province' => $data['content']['address_detail']['province'],
            'city' => $data['content']['address_detail']['city'],
            'district' => $data['content']['address_detail']['district'],
            'street' => $data['content']['address_detail']['street'],
            'street_number' => $data['content']['address_detail']['street_number'],
            'city_code' => $data['content']['address_detail']['city_code'],
            'lng' => $data['content']['point']['x'],
            'lat' => $data['content']['point']['y'],
        );
    }

    /**
     * GPS定位
     * @param $lng
     * @param $lat
     * @return array
     * @throws Exception
     */
    public function locationByGPS($lng, $lat)
    {
        $params = array(
            'coordtype' => 'wgs84ll',
            'location' => $lat . ',' . $lng,
            'ak' => '百度地图API KEY',
            'output' => 'json',
            'pois' => 0,
        );
        $resp = $this->async('http://api.map.baidu.com/geocoder/v2/', $params, false);
        $data = json_decode($resp, true);
        if ($data['status'] != 0) {
            return $data['message'];
        }
        return array(
            'address' => $data['result']['formatted_address'],
            'province' => $data['result']['addressComponent']['province'],
            'city' => $data['result']['addressComponent']['city'],
            'street' => $data['result']['addressComponent']['street'],
            'street_number' => $data['result']['addressComponent']['street_number'],
            'city_code' => $data['result']['cityCode'],
            'lng' => $data['result']['location']['lng'],
            'lat' => $data['result']['location']['lat'],
        );
    }
}
