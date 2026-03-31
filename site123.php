<?php
session_start();

/* ================= LIGAÇÃO BASE DE DADOS ================= */
$ligacao = new mysqli("localhost", "root", "", "biblioteca_db");
if ($ligacao->connect_error) { die("Erro na ligação: " . $ligacao->connect_error); }

$mensagem = "";
$tipo_mensagem = "erro";

/* ================= REGISTO ================= */
if(isset($_POST["registar"])) {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if(empty($nome) || empty($email) || empty($password)) {
        $mensagem = "Todos os campos são obrigatórios.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Email inválido.";
    } elseif(strlen($password)<6) {
        $mensagem = "Password deve ter pelo menos 6 caracteres.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $ligacao->prepare("INSERT INTO utilizadores (nome,email,password) VALUES (?,?,?)");
        $stmt->bind_param("sss",$nome,$email,$password_hash);
        if($stmt->execute()) { 
            $mensagem = "Conta criada com sucesso."; 
            $tipo_mensagem = "sucesso";
        } else { 
            $mensagem = "Este email já está registado."; 
        }
    }
}

/* ================= LOGIN ================= */
if(isset($_POST["login"])) {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $ligacao->prepare("SELECT * FROM utilizadores WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows == 1) {
        $user = $resultado->fetch_assoc();
        if(password_verify($password,$user["password"])) {
            $_SESSION["user"] = $user["nome"];
            $tipo_mensagem = "sucesso";
        } else { 
            $mensagem="Password incorreta."; 
        }
    } else { 
        $mensagem="Utilizador não encontrado."; 
    }
}

/* ================= LOGOUT ================= */
if(isset($_GET["logout"])) { 
    session_destroy(); 
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit; 
}

/* ================= CARRINHO ================= */
if(!isset($_SESSION["carrinho"])) { $_SESSION["carrinho"] = []; }

$res = $ligacao->query("SELECT * FROM livros");
$livros = [];
while($row = $res->fetch_assoc()) { $livros[$row['id']] = $row; }

/* ================= ADICIONAR / REMOVER ================= */
if(isset($_GET["add"]) && isset($livros[$_GET["add"]])) { 
    $_SESSION["carrinho"][] = $_GET["add"]; 
    $mensagem = "Adicionado ao carrinho";
    $tipo_mensagem = "sucesso";
}

if(isset($_GET["remove"]) && isset($livros[$_GET["remove"]])) {
    $key = array_search($_GET["remove"], $_SESSION["carrinho"]);
    if($key!==false){ 
        unset($_SESSION["carrinho"][$key]); 
        $mensagem = "Removido do carrinho";
        $tipo_mensagem = "sucesso";
    }
}

/* ================= CALCULA QUANTIDADES ================= */
$carrinho_count = [];
foreach($_SESSION["carrinho"] as $id) {
    if(isset($carrinho_count[$id])) $carrinho_count[$id]++;
    else $carrinho_count[$id]=1;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #141414;
            --bg-tertiary: #1a1a1a;
            --accent: #e63946;
            --accent-hover: #d62828;
            --text-primary: #f1f1f1;
            --text-secondary: #a0a0a0;
            --border: #2a2a2a;
            --shadow: rgba(0, 0, 0, 0.5);
        }

        body {
            font-family: 'IBM Plex Sans', -apple-system, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
        }

        /* Navbar minimalista */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.2rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: -0.5px;
            color: var(--text-primary);
        }

        .nav-menu {
            display: flex;
            gap: 2.5rem;
            align-items: center;
            list-style: none;
        }

        .nav-menu a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 400;
            transition: color 0.2s ease;
            letter-spacing: 0.3px;
        }

        .nav-menu a:hover {
            color: var(--text-primary);
        }

        .cart-link {
            position: relative;
            color: var(--text-primary) !important;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -12px;
            background: var(--accent);
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .user-name {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .logout-link {
            color: var(--text-secondary) !important;
            font-size: 0.9rem;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 90px auto 50px;
            padding: 0 2rem;
        }

        /* Notificação minimalista */
        .notification {
            position: fixed;
            top: 80px;
            right: 2rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            padding: 1rem 1.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
            color: var(--text-primary);
            animation: slideIn 0.3s ease;
            z-index: 2000;
            box-shadow: 0 4px 12px var(--shadow);
        }

        .notification.sucesso {
            border-left: 2px solid var(--accent);
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Hero minimalista */
        .hero {
            text-align: center;
            padding: 6rem 0 4rem;
        }

        .hero h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3.5rem;
            font-weight: 600;
            letter-spacing: -2px;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .hero p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            font-weight: 300;
            letter-spacing: 0.3px;
        }

        /* Auth forms minimalista */
        .auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .auth-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 3rem;
        }

        .auth-card h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            letter-spacing: -0.5px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text-primary);
            font-size: 0.95rem;
            font-family: 'IBM Plex Sans', sans-serif;
            transition: all 0.2s ease;
            font-weight: 400;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--bg-secondary);
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: var(--accent);
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'IBM Plex Sans', sans-serif;
            letter-spacing: 0.3px;
        }

        .btn:hover {
            background: var(--accent-hover);
        }

        .btn.secondary {
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            color: var(--text-primary);
        }

        .btn.secondary:hover {
            background: var(--bg-secondary);
            border-color: var(--text-secondary);
        }

        /* Section headers minimalistas */
        .section-header {
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: -1px;
            margin-bottom: 0.5rem;
        }

        .section-header p {
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 300;
        }

        /* Grid minimalista */
        .manga-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 5rem;
        }

        .manga-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .manga-card:hover {
            border-color: var(--text-secondary);
            transform: translateY(-4px);
        }

        .manga-cover {
            position: relative;
            width: 100%;
            padding-top: 145%;
            overflow: hidden;
            background: var(--bg-tertiary);
        }

        .manga-cover img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .manga-card:hover .manga-cover img {
            transform: scale(1.05);
        }

        .manga-info {
            padding: 1.25rem;
        }

        .manga-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            letter-spacing: -0.3px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .manga-author {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            font-weight: 300;
        }

        .manga-price {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .add-btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text-primary);
            font-weight: 400;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: block;
            text-align: center;
            letter-spacing: 0.3px;
        }

        .add-btn:hover {
            background: var(--accent);
            border-color: var(--accent);
        }

        /* Carrinho minimalista */
        .cart-section {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 2.5rem;
            margin-top: 3rem;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 1.5rem;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-img {
            width: 100px;
            height: 145px;
            object-fit: cover;
            border-radius: 4px;
            background: var(--bg-tertiary);
        }

        .cart-item-info h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            letter-spacing: -0.3px;
        }

        .cart-item-details {
            display: flex;
            gap: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .cart-qty {
            color: var(--text-primary);
            font-weight: 500;
        }

        .remove-btn {
            padding: 0.7rem 1.2rem;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text-secondary);
            font-weight: 400;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            letter-spacing: 0.3px;
        }

        .remove-btn:hover {
            background: var(--bg-tertiary);
            border-color: var(--text-secondary);
            color: var(--text-primary);
        }

        .cart-total {
            background: var(--bg-tertiary);
            padding: 2rem;
            border-radius: 4px;
            text-align: right;
            margin-top: 2rem;
        }

        .cart-total h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: -1px;
        }

        .empty-cart {
            text-align: center;
            padding: 5rem 2rem;
            color: var(--text-secondary);
        }

        .empty-cart h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 500;
            margin-top: 1rem;
            letter-spacing: -0.5px;
        }

        .empty-cart p {
            margin-top: 0.5rem;
            font-weight: 300;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .manga-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1.5rem;
            }

            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 1rem;
            }

            .remove-btn {
                grid-column: 2;
                justify-self: end;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .nav-menu {
                gap: 1.5rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav>
    <div class="nav-container">
        <div class="logo">BIBLIOTECA</div>
        <?php if(isset($_SESSION["user"])): ?>
        <ul class="nav-menu">
            <li><span class="user-name"><?= htmlspecialchars($_SESSION["user"]) ?></span></li>
            <li><a href="#catalogo">Catálogo</a></li>
            <li>
                <a href="#carrinho" class="cart-link">
                    Carrinho
                    <?php if(count($_SESSION["carrinho"]) > 0): ?>
                    <span class="cart-badge"><?= count($_SESSION["carrinho"]) ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li><a href="?logout=1" class="logout-link">Sair</a></li>
        </ul>
        <?php endif; ?>
    </div>
</nav>

<!-- Notificação -->
<?php if($mensagem): ?>
<div class="notification <?= $tipo_mensagem ?>" id="notification">
    <?= htmlspecialchars($mensagem) ?>
</div>
<script>
    setTimeout(() => {
        const notif = document.getElementById('notification');
        if(notif) {
            notif.style.opacity = '0';
            notif.style.transform = 'translateX(400px)';
            setTimeout(() => notif.remove(), 300);
        }
    }, 3500);
</script>
<?php endif; ?>

<div class="container">

<?php if(!isset($_SESSION["user"])): ?>
    <!-- Hero -->
    <div class="hero">
        <h1>Biblioteca Digital</h1>
        <p>A tua coleção de manga</p>
    </div>

    <!-- Auth forms -->
    <div class="auth-grid">
        <div class="auth-card">
            <h2>Entrar</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="seu@email.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" name="login" class="btn">Entrar</button>
            </form>
        </div>

        <div class="auth-card">
            <h2>Criar Conta</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="nome" placeholder="O teu nome" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="seu@email.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>
                <button type="submit" name="registar" class="btn secondary">Criar Conta</button>
            </form>
        </div>
    </div>

<?php else: ?>

    <!-- Catálogo -->
    <section id="catalogo">
        <div class="section-header">
            <h2>Catálogo</h2>
            <p>Explora a coleção</p>
        </div>

        <div class="manga-grid">
            <?php foreach($livros as $id => $livro): ?>
            <div class="manga-card">
                <div class="manga-cover">
                    <img src="capas/<?= $id ?>.jpg" 
                         alt="<?= htmlspecialchars($livro['titulo']) ?>"
                         onerror="this.src='https://via.placeholder.com/200x290/1a1a1a/666666?text=Sem+Capa'">
                </div>
                <div class="manga-info">
                    <div class="manga-title"><?= htmlspecialchars($livro['titulo']) ?></div>
                    <div class="manga-author"><?= htmlspecialchars($livro['autor']) ?></div>
                    <div class="manga-price">€<?= number_format($livro['preco'], 2) ?></div>
                    <a href="?add=<?= $id ?>#catalogo" class="add-btn">Adicionar</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Carrinho -->
    <section id="carrinho">
        <div class="section-header">
            <h2>Carrinho</h2>
            <p>Itens selecionados</p>
        </div>

        <div class="cart-section">
            <?php if(count($carrinho_count) > 0): ?>
                <?php 
                $total = 0;
                foreach($carrinho_count as $id => $qtd): 
                    $subtotal = $livros[$id]['preco'] * $qtd;
                    $total += $subtotal;
                ?>
                <div class="cart-item">
                    <img src="capas/<?= $id ?>.jpg" 
                         alt="<?= htmlspecialchars($livros[$id]['titulo']) ?>" 
                         class="cart-item-img"
                         onerror="this.src='https://via.placeholder.com/100x145/1a1a1a/666666?text=Sem+Capa'">
                    <div class="cart-item-info">
                        <h3><?= htmlspecialchars($livros[$id]['titulo']) ?></h3>
                        <div class="cart-item-details">
                            <span>€<?= number_format($livros[$id]['preco'], 2) ?></span>
                            <span class="cart-qty">× <?= $qtd ?></span>
                            <span>€<?= number_format($subtotal, 2) ?></span>
                        </div>
                    </div>
                    <a href="?remove=<?= $id ?>#carrinho" class="remove-btn">Remover</a>
                </div>
                <?php endforeach; ?>

                <div class="cart-total">
                    <h3>Total: €<?= number_format($total, 2) ?></h3>
                </div>

            <?php else: ?>
                <div class="empty-cart">
                    <h3>Carrinho vazio</h3>
                    <p>Adiciona alguns itens</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php endif; ?>

</div>

<script>
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if(target) {
                window.scrollTo({
                    top: target.offsetTop - 90,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>

</body>
</html>