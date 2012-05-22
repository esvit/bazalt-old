<?php

interface CMS_IUser
{
    public static function getUser();

    public static function isLogined();

    public static function criptPassword($password);
    
    public static function login($login, $password);

    public static function logout();
}