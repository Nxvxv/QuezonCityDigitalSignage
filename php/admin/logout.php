<?php
session_start();
session_destroy();
header('Location: /QCPLibrary/php/admin/login/login.php');
exit;
