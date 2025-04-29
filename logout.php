<?php
session_start();
session_destroy();
header("Location: ldp.php");
exit();
