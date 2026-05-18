<?php

class ApiClient
{
    private $url;
    private $username;
    private $api_key;
    private $encrypt_method;
    private $iv;
    public $use_iv;

    public function __construct()
    {
        $this->url = 'http://apis.slemankab.go.id/';
        // $this->url = 'http://localhost/kominfo/apimanager/server/';
        $this->username = 'sso';
        $this->api_key = 'fa531adb3f816c0c57ee9c5e50ce04af';
        $this->encrypt_method = 'AES-256-CBC';
        $this->iv = 'ABCDEFGH12345678';
        $ver = explode('-', PHP_VERSION);
        $ver = explode('.', $ver[0]);
        if ((int) $ver[0] < 5 || ((int) $ver[0] == 5 && (int) $ver[1] < 3) || ((int) $ver[0] == 5 && (int) $ver[1] == 3 && (int) $ver[2] < 3)) {
            $this->use_iv = false;
        } else {
            $this->use_iv = true;
        }
    }

    private function encrypt($data, $key)
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        if ($this->use_iv == true) {
            $data = openssl_encrypt($data, $this->encrypt_method, $key, 0, $this->iv);
        } else {
            ini_set('display_errors', 0);
            $data = '[-]'.openssl_encrypt($data, $this->encrypt_method, $key, 0);
        }

        return $data;
    }

    private function decrypt($data, $key)
    {
        if (substr($data, 0, 3) == '[-]') {
            $data = substr_replace($data, '', 0, 3);
            $data = openssl_decrypt($data, $this->encrypt_method, $key, 0);
        } else {
            $data = openssl_decrypt($data, $this->encrypt_method, $key, 0, $this->iv);
        }

        return $data;
    }

    public function request($data)
    {
//        print_r($data);exit();
        $ch = curl_init($this->url);
        //curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $post_data = array(
          'app' => $data['app'],
          'module' => $data['module'],
          'action' => $data['action'],
          'sign' => md5(microtime().mt_rand()),
        );
        unset($data['app'], $data['module'], $data['action']);
        $post_data = $this->encrypt($post_data, $this->api_key);
        $post_data = array(
          'username' => $this->username,
          'data' => $post_data,
        );
        $post_data = $this->encrypt($post_data, $this->iv);
        $post_data = array(
          'data' => $post_data,
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $response = curl_exec($ch);
        $arr_response = json_decode($response, 1);
        if (isset($arr_response['data'])) {
            $response = json_decode($this->decrypt($arr_response['data'], $this->api_key), 1);

            $post_data = $this->encrypt($data, $this->api_key);
            $post_data = array(
                'token' => $response['token'],
                'data' => $post_data,
            );
            $post_data = $this->encrypt($post_data, $this->iv);
            $post_data = array(
              'data' => $post_data,
            );

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

            $response = curl_exec($ch);
            $arr_response = json_decode($response, 1);
            if (isset($arr_response['data'])) {
                $response = $this->decrypt($arr_response['data'], $this->api_key);
            }
        }
        curl_close($ch);

        return $response;
    }
}
