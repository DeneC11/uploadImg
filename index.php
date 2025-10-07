<!-- <?php
        session_start();
        ?> -->

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de imágenes</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>

<body>

    <h1>Ejercicio práctico: Upload de imágenes</h1>
    <div class="container">
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="fileUpload" class="button">Seleccionar una imagen</label>
            <input type="file" name="myImage" id="fileUpload" accept=".jpg,.jpeg,.png,.gif,.webp">
            <p id="nombreArchivo">Ningun archivo seleccionado</p>
            <input type="submit" name="enviar" value="Subir imagen">
        </form>
    </div>
    <div class="mensajes">
        <!-- zona de errores -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error'];
                                unset($_SESSION['error']) ?></div>
        <?php endif; ?>
        <!-- zona de success -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= $_SESSION['success'];
                                    unset($_SESSION['success']) ?></div>
        <?php endif; ?>
    </div>
    <!-- zona foto -->
    <?php if (isset($_SESSION['ruta'])): ?>
        <div class="foto">
            <?php if (isset($_SESSION['ruta'])): ?>
                <img src="<?= $_SESSION['ruta'];
                            unset($_SESSION['ruta']) ?>">
            <?php endif; ?>
        </div>

    <?php endif; ?>
    <script>
        document.getElementById('fileUpload').addEventListener('change', function() {
            const archivo = this.files[0];
            console.log('imagen: ', archivo);
            console.log('nombre: ', archivo.name);
            console.log('tamaño: ', archivo.size);
            console.log('tipo: ', archivo.type);
            document.getElementById('nombreArchivo').textContent = archivo ? archivo.name : 'Ningun archivo seleccionado';
        })
    </script>
</body>

</html>