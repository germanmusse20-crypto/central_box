<?php
// Generar hash bcrypt para contraseña "Admin123"
$password = "Admin123";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Contraseña: " . $password . "\n";
echo "Hash: " . $hash . "\n";

// Conectar a la base de datos y actualizar
$conn = new PDO('mysql:host=127.0.0.1;port=3306;dbname=central_box', 'root', '');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("UPDATE usuarios SET password = :password WHERE email = 'admin@central-box.com'");
$stmt->execute([':password' => $hash]);

echo "\nUsuario actualizado. Puedes usar:\n";
echo "Email: admin@central-box.com\n";
echo "Contraseña: Admin123\n";
?>
