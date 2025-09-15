<?php
 require_once __DIR__.'/../models/User.php';
 require_once __DIR__ .'/../utils/jwtUtil.php';

 class AuthController{
    private $user;

    public function __construct(){
        $this->user = new User();
    }


    public function register($data) {
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])){
            htt
        }
    }
 }