<?php
session_start();
session_destroy();
header('Location: ../admin/admin_login.php');
exit;