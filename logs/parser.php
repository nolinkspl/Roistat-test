<?php

//void для вызова AccessLogManager, для теста, т.к нет ядра системы.

include_once("../Service/AccessLogManager.php");

$logManager = new \Service\AccessLogManager();

echo $logManager->parseLog();