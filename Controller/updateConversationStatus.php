<?php
/**
 * Module : Communication parents
 * @author Rajhi Amen Allah <amenallah.rajhi@esprit.tn>
 */
require_once __DIR__ . "/ConversationController.php";

$controller = new ConversationController();
$controller->changeStatus();
