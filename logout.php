<?php
session_start();
session_unset(); session_destroy();
header(header: 'Location: tes1.html'); exit;
?>