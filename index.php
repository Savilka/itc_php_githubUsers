<?php
session_start();
spl_autoload_register(function ($className) {
    include $className . '.php';
});

$gu = new GithubUsers("Savilka", ["login", "id", "html_url"]);

$funcName = "getUserByUserName";

if ($gu->$funcName()) {
    print_r(GithubUsers::getUserData());
}
//if ($gu->$funcName()) {
//    print_r($gu->getProfileCard());
//}






