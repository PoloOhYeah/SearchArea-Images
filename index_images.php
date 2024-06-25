<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'searchimg';
$user = 'root';
$password = '';

// Traitement du formulaire d'ajout d'image
$pdo = null;
$errors = [];

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
        $uploadDir = 'images/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);

        // Récupérer le titre de l'image depuis le formulaire
        $imageName = $_POST['imageTitle']; // Assurez-vous de valider et échapper cette valeur correctement
        $imageDescription = $_POST['description'];

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imagePath = $uploadFile;
            $imageSize = $_FILES['image']['size'];
            $imageType = $_FILES['image']['type'];

            // Insertion des données dans la base
            $stmt = $pdo->prepare("INSERT INTO images (name, path, size, type, description) VALUES (:name, :path, :size, :type, :description)");
            $stmt->bindParam(':name', $imageName);
            $stmt->bindParam(':path', $imagePath);
            $stmt->bindParam(':size', $imageSize);
            $stmt->bindParam(':type', $imageType);
            $stmt->bindParam(':description', $imageDescription);
            $stmt->execute();

            echo "<p>Image ajoutée avec succès à la base de données.</p>";
        } else {
            $errors[] = "Erreur lors du téléchargement de l'image.";
        }
    }
} catch (PDOException $e) {
    $errors[] = "Erreur de connexion à la base de données : " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout d'Images - SearchArea</title>
    <link rel="stylesheet" href="styles.css">
    <!-- Inclusion de Fuse.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fuse.js/6.4.6/fuse.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Ajout d'Images - SearchArea</h1>
        
        <!-- Formulaire d'ajout d'image -->
        <form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
            <label for="image">Sélectionnez une image à télécharger :</label>
            <input type="file" name="image" id="image" required>
            <br>
            <label for="imageTitle">Titre de l'image :</label>
            <input type="text" name="imageTitle" id="imageTitle" required>
            <br>
            <label for="description">Description de l'image :</label>
            <textarea name="description" id="description" rows="4"></textarea>
            <br>
            <button type="submit">Télécharger</button>
        </form>

        <?php
        // Affichage des erreurs éventuelles
        if (!empty($errors)) {
            echo '<div class="errors">';
            foreach ($errors as $error) {
                echo '<p>' . $error . '</p>';
            }
            echo '</div>';
        }
        ?>

        <hr>

        <h2>Recherche d'Images :</h2>
        
        <input type="text" id="searchInput" placeholder="Recherche par titre ou description...">

        <div id="imageResults" class="image-container">
            <?php
            // Affichage des images déjà indexées
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM images");
                    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($images) > 0) {
                        foreach ($images as $image) {
                            echo '<div class="image">';
                            echo '<img src="' . $image['path'] . '" alt="' . $image['name'] . '">';
                            echo '<p>Nom: ' . $image['name'] . '</p>';
                            echo '<p>Description: ' . $image['description'] . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo "<p>Aucune image trouvée.</p>";
                    }
                } catch (PDOException $e) {
                    echo "Erreur : " . $e->getMessage();
                }
            } else {
                echo "<p>Erreur de connexion à la base de données.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        // Initialisation de Fuse.js pour la recherche côté client
        document.addEventListener('DOMContentLoaded', function() {
            const images = <?php echo json_encode($images); ?>; // Récupération des images depuis PHP
            const fuse = new Fuse(images, {
                keys: ['name', 'description'],
                includeScore: true,
                threshold: 0.3,
                ignoreLocation: true
            });

            const searchInput = document.getElementById('searchInput');
            const imageResults = document.getElementById('imageResults');

            searchInput.addEventListener('input', function() {
                const searchTerm = searchInput.value.trim();
                const results = fuse.search(searchTerm);

                let html = '';
                results.forEach(result => {
                    const image = result.item;
                    html += '<div class="image">';
                    html += '<img src="' + image.path + '" alt="' + image.name + '">';
                    html += '<p>Nom: ' + image.name + '</p>';
                    html += '<p>Description: ' + image.description + '</p>';
                    html += '</div>';
                });

                imageResults.innerHTML = html;
            });
        });
    </script>
</body>
</html>
