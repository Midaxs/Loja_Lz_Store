<?php
session_start();
unset($_SESSION['historico']); // Limpa o histórico local do cliente
session_destroy();
header('Location: login.php');
exit;