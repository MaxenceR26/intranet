<?php
require '../../php/Sample/core/connection.php';
require_once '../../php/Sample/core/permission.php';

if (!isset($_SESSION['state'])) {
    session_destroy();
    header('Location: ../../index.html');
    exit; // Assure la fin du script après la redirection
}

$email = $_SESSION['email'];

// Requête SQL pour compter les notifications de l'utilisateur
$sql_count = "SELECT COUNT(*) as notification_count FROM notifications WHERE (user_id = ? OR user_id = 'general') AND is_read = 0 ORDER BY created_at DESC LIMIT 4";
$stmt = $mysqli->prepare($sql_count);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($notification_count);
$stmt->fetch();
$stmt->close();


// Requête SQL pour récupérer les 5 dernières notifications de l'utilisateur par email
$sql_messages = "SELECT message FROM notifications WHERE (user_id = ? OR user_id = 'general') AND is_read = 0 ORDER BY created_at DESC LIMIT 4";
$stmt = $mysqli->prepare($sql_messages);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($message);

$messages = [];
while ($stmt->fetch()) {
    $messages[] = $message;
}


// Vérifier si un ID a été passé en paramètre
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Requête pour sélectionner l'actualité spécifique par ID
    $sql = "SELECT titre, head, body, texts, dates, image FROM actualites WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Aucune actualité trouvée pour cet ID.";
        exit;
    }
} else {
    echo "ID d'actualité non spécifié.";
    exit;
}
$stmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../CSS/styles.css">
    
    <script src="https://kit.fontawesome.com/11b0336c50.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Font Awesome -->
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    rel="stylesheet"
    />
    <!-- Google Fonts -->
    <link
    href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap"
    rel="stylesheet"
    />
    <!-- MDB -->
    <link
    href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.1/mdb.min.css"
    rel="stylesheet"
    />
    <title>Intranet Osartis</title>
    <link rel="icon" href="../../images/1719307876_logo.ico"/>
</head>
<body style="background-color: rgb(0, 117, 166);">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
        <!-- Container wrapper -->
        <div class="container-fluid">
        <!-- Toggle button -->
        <button
            data-mdb-collapse-init
            class="navbar-toggler"
            type="button"
            data-mdb-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <i class="fas fa-bars"></i>
        </button>
    
        <!-- Collapsible wrapper -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Navbar brand -->
            <a class="navbar-brand mt-2 mt-lg-0" href="#" style="user-select: none; pointer-events: none;">
            <img
                src="../../images/logo.jpg"
                height="35"
                alt="MDB Logo"
                loading="lazy"
                
            />
            </a>
            <!-- Left links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <div class="nav-item">
                    <a class="nav-link" href="../../intranet.php">Osartis Intranet</a>
                </div>
            
            </ul>
            <!-- Left links -->
        </div>
        <!-- Collapsible wrapper -->
    
        <!-- Right elements -->
        <div class="d-flex align-items-center">
            <!-- Notifications -->
            <div class="dropdown">
            <a
                data-mdb-dropdown-init
                class="text-reset me-3 dropdown-toggle hidden-arrow"
                href="#"
                id="navbarDropdownMenuLink"
                role="button"
                aria-expanded="false"
            >
                <i class="fas fa-bell" style="color: rgb(79 79 79)"></i>
                <?php if ($notification_count > 0 ):?>
                <span class="badge rounded-pill badge-notification bg-danger" id="notification-count"><?php if ($notification_count >= 9) { echo '+9';} else { echo $notification_count;} ?></span>
                <?php endif ?>
            </a>
            <ul
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="navbarDropdownMenuLink"
            >
            <span class="d-flex justify-content-center" style="color:#464646; font-weight: bold; font-size: 18px">Notifications</span>
            <hr class="m-0">
            <?php if (count($messages) > 0): ?>
                <?php foreach ($messages as $msg): ?>
                    <li>
                        <a class="dropdown-item" href="#" style="color:#979797"><?php echo '<span style="font-weight:bold; color:#464646">></span> '.htmlspecialchars($msg); ?></a>
                    </li>
                <?php endforeach; ?>
                <a href="#" class="d-flex justify-content-center" id="allIsRead">Tous lu</a>
            <?php else: ?>
                <li><a class="dropdown-item" href="#">Vous n'avez pas de notifications</a></li>
            <?php endif; ?>
            </ul>
            </div>
            <!-- Avatar -->
            <div class="dropdown">
            <a
                data-mdb-dropdown-init
                class="dropdown-toggle d-flex align-items-center hidden-arrow"
                href="#"
                id="navbarDropdownMenuAvatar"
                role="button"
                aria-expanded="false"
            >
            <div class="profile-pic-navbar">
                <img
                    src="<?php echo '../../'.$_SESSION['image'];?>"
                    alt="Profile Picture"
                    loading="lazy"
                    />
            </div>
                
            </a>
            <ul
                class="dropdown-menu dropdown-menu-end"
                aria-labelledby="navbarDropdownMenuAvatar"
            >
                <li>
                <a class="dropdown-item" href="../../pages/parametre.php">Mes paramètres</a>
                </li>
                <li>
                <a class="dropdown-item" href="../../pages/myTickets.php">Mes Tickets</a>
                <a class="dropdown-item" href="../../pages/link.php">Mes Liens</a>
                </li>
                <li><a class="dropdown-item" href="../../pages/newTickets.php">Nouveau ticket</a></li>
                <?php
                    if ($_SESSION['idroles'] >= 2) {
                        echo "<li>
                                <a class='dropdown-item' href='../../admin/index.php'>Panel Admin</a>
                            </li>";
                    }
                ?>
                
                <li>
                <a class="dropdown-item" onclick="deconnexion()" href="">Déconnexion</a>
                </li>
            </ul>
            </div>
        </div>
        <!-- Right elements -->
        </div>
        <!-- Container wrapper -->
    </nav>
    <!-- Navbar -->
    <div class="container-detailactu-page">
        <h1><?php echo htmlspecialchars($row['titre']); ?></h1>
        <span><?php echo $row['texts']; ?></span><br>
        <?php if ($row['image'] != NULL): ?>
            <img src="<?php echo '../../'.$row['image']; ?>" style="width: 250px;"><br>
        <?php endif; ?>
        <span>Date: <?php echo htmlspecialchars($row['dates']); ?></span>
    </div>
    
</body>
<script src="../../js/nav.js"></script>
<script
  type="text/javascript"
  src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.1/mdb.umd.min.js"
></script>

<script>
    buttonAllIsRead = document.getElementById("allIsRead");
    buttonAllIsRead.addEventListener("click", function() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../../php/admin/updateNotifications.php', true);
        xhr.send();

        window.location.reload();
    })
</script>
<script>
    function deconnexion() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../../php/Sample/core/deconnexion.php', true);
        xhr.send();

        window.location.href = 'index.html';
    }
</script>
</html>
