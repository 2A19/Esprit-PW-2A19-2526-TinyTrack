<?php
/**
 * Module : Communication parents
 * @author Rajhi Amen Allah <amenallah.rajhi@esprit.tn>
 */
require_once __DIR__ . "/CommunicationMessageController.php";

$controller = new MessageController();
$controller->store();
