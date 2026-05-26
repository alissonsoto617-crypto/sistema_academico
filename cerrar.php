<?php
session_start();

// Destruir la sesión completamente
session_unset();
session_destroy();

// Redirigir al login
header("Location: index.php");
exit();
?>
