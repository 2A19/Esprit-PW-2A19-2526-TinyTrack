<?php
require_once __DIR__ . '/../../Controller/AuthController.php';
$authCtrl = new AuthController();
$authCtrl->logout();
header('Location: login.php');
exit;
