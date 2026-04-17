<?php
session_start();
session_destroy();
header("Location: ?p=login");
exit;
?>
