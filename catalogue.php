<?php
session_start();

// DB Connection
$conn = new mysqli("localhost", "root", "Shreyash1504", "grocery_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Registration
if (isset($_POST['register'])) {
    $name = $_POST['reg_name'];
    $email = $_POST['reg_email'];
    $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);
    $address = $_POST['reg_address'];
    $phone = $_POST['reg_phone'];

    $stmt = $conn->prepare("INSERT INTO Consumer (full_name, email, password, address, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $address, $phone);
    $stmt->execute();
    $reg_message = "Registration successful. Please log in.";
}

// Handle Login
if (isset($_POST['login'])) {
    $email = $_POST['login_email'];
    $password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT id, password FROM Consumer WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password);
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $login_message = "Login successful!";
        } else {
            $login_error = "Incorrect password.";
        }
    } else {
        $login_error = "User not found.";
    }
}

// Fetch catalogue items
$items = $conn->query("SELECT * FROM Items");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Grocery Catalogue</title>
    <style>
        body { font-family: Arial; margin: 0; padding: 0; background: #f4f4f4; }
        header, footer { background: #28a745; color: white; padding: 15px; text-align: center; }
        nav a { margin: 0 15px; color: white; text-decoration: none; font-weight: bold; }
        .container { padding: 20px; max-width: 1000px; margin: auto; }
        h2 { color: #333; }
        form { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        input, button { width: 100%; padding: 10px; margin-top: 10px; }
        .catalogue { display: flex; flex-wrap: wrap; gap: 20px; }
        .item { background: #fff; border: 1px solid #ccc; padding: 10px; width: 200px; border-radius: 5px; text-align: center; }
        .item img { max-width: 100%; height: 100px; object-fit: contain; }
        .message { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 10px; border: 1px solid #c3e6cb; }
        .error { padding: 10px; background: #f8d7da; color: #721c24; margin-bottom: 10px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<header>
    <h1>Online Grocery Shop</h1>
    <nav>
        <a href="#catalogue">Catalogue</a>
        <a href="#login">Login</a>
        <a href="#register">Register</a>
    </nav>
</header>

<div class="container">

    <!-- Login Form -->
    <section id="login">
        <h2>Login</h2>
        <?php if (!empty($login_message)) echo "<div class='message'>$login_message</div>"; ?>
        <?php if (!empty($login_error)) echo "<div class='error'>$login_error</div>"; ?>
        <form method="POST">
            <input type="email" name="login_email" required placeholder="Email">
            <input type="password" name="login_password" required placeholder="Password">
            <button type="submit" name="login">Login</button>
        </form>
    </section>

    <!-- Registration Form -->
    <section id="register">
        <h2>Register</h2>
        <?php if (!empty($reg_message)) echo "<div class='message'>$reg_message</div>"; ?>
        <form method="POST">
            <input type="text" name="reg_name" required placeholder="Full Name">
            <input type="email" name="reg_email" required placeholder="Email">
            <input type="password" name="reg_password" required placeholder="Password">
            <input type="text" name="reg_address" placeholder="Address">
            <input type="text" name="reg_phone" placeholder="Phone">
            <button type="submit" name="register">Register</button>
        </form>
    </section>

    <!-- Catalogue -->
    <section id="catalogue">
        <h2>Product Catalogue</h2>
        <div class="catalogue">
            <?php while ($row = $items->fetch_assoc()): ?>
                <div class="item">
                    <img src="<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    <strong>$<?= $row['price'] ?></strong>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
</div>

<footer>
    <p>&copy; 2025 Online Grocery Shop</p>
</footer>

</body>
</html>
