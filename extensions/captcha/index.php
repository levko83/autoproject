<?php
session_start();
require_once('../../extensions/captcha/kcaptcha.php');
$captcha = new KCAPTCHA();
$_SESSION['captcha_keystring'] = md5($captcha->getKeyString());
?>