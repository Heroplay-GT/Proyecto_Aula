<?php
session_start();
session_unset();
session_destroy();
header("Location: ../View/Home.php");
exit;
