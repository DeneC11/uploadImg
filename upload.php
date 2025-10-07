<?php
session_start();
define('MAX_FILE_SIZE',5*1024*1024); //5MB
define('CARPETA_UPLOADS','fotos'); //carpeta

/**
 * valida una imagen subida
 * @param array $img, imagen subida
 * @return true|false devuelve si la imagen es valida o un string con error 
 */
function validarImagen($img){
    $extensionesPermitidas=['jpg','jpeg','png','gif','webp'];
    //1.- verificar si se subio correctamente un archivo al servidor
    if($img['error']=== UPLOAD_ERR_NO_FILE){
        return'La imagen no se ha subido correctamente al servidor';
    }
    //2.- verificar otros errores de subida
    if($img['error'] !== UPLOAD_ERR_OK){
        return"Error al subir la imagen: {$img['error']}";
    }
    //3.- verificar el tamaño del archivo
    if($img['size']> MAX_FILE_SIZE){
        return"Archivo demasiado grande (max 5MB)";
    }
    //4.- obtener y validar extension
    $extension=strtolower(pathinfo($img['name'],PATHINFO_EXTENSION));
    if(!in_array($extension,$extensionesPermitidas)){
        return 'Solo se permiten imagenes con extensiones: '. implode(', ',$extensionesPermitidas);
    }
    //5.- verificar el tipo MIME real para mayor seguridad
    $mimeTypesPermitidos=[
        'image/gif'=>'gif',
        'image/png'=>'png',
        'image/jpeg'=>'jpeg',
        'image/bmp'=>'bmp',
        'image/webp'=>'webp'
    ];
    $mime=mime_content_type($img['tmp_name']);
    // echo 'Tipo mime: '.$mime .'<br>';
    if(!array_key_exists($mime,$mimeTypesPermitidos)){
        return"La extension del archivo no coincide con su extension real";        
    }
    //6.- verificar que es una imagen real y no un archivo malicioso
    if(getimagesize($img['tmp_name'])===false){
        return"El archivo no es una imagen valida";                
    }
    //Si llega aqui es una imagen valida
    return true;
}
/**
 * Crea una carpeta si no existe
 * @param string la ruta de la carpeta
 */
function crearDirectorioSiNoExiste($dir){
    if(!is_dir($dir)){
        mkdir($dir,0755,true);//el true hace que cree las carpetas necesarias
    }
}
/**
 * Limpia y formatea el nombre de un archivo para hacerlo seguro
 * @param string $nombre. Elnombre del archivo
 * @return string. El nombre del archio 'sanitizado' limpio
 */
function sanitizarNombreArchivo($nombre){
    //habilitar intl de PHP(internationalization extension)
    $nombre=transliterator_transliterate('Any-Latin; Latin-ASCII;[\u0100-\u7fff] remove',$nombre);
    //Any-Latin -> Convierte cualquier alfabeto a caracteres latinos
    //Latin-ASCII -> Sustituye letras con acentos o simbolos especiales pos su variante ASCII (ñ->n,é->e,ü->u)
    //[\u0100-\u7fff] -> elimina cualquier caracter cuyo codigo UNICODE este entre \u0100-\u7fff (simbolos raros, caracteres no latinos)

    //reemplazar cualquier caracter que no sea letra, numero, punto o guion
    $nombre = preg_replace('/[^a-zA-Z0-9.\-_]/','-',$nombre);
    //reemplaza multiples guiones por uno solo
    $nombre = preg_replace('/-+/','-',$nombre);
    $nombre = preg_replace('/_+/','_',$nombre);
    //elimina los guiones iniciales y finales
    $nombre=trim($nombre,'-');
    return $nombre;
}
/**
 * Genera un nombre unico, sanitizandolo (sin acentos, signos de puntuacion, etc...) y añadiendo un contador de imagen si ya existe
 * @param string $nombreOriginal. El nombre del archivo original
 * @param string $dir. Ruta del directorio donde se guardara imagen
 * @return string. El nombre del archivo final y unico
 */
function generarNombreUnico($nombreOriginal,$dir){
    $extension = strtolower(pathinfo($nombreOriginal,PATHINFO_EXTENSION));
    $nombreBase = pathinfo($nombreOriginal,PATHINFO_FILENAME);
    //sanitizar el nombre base de la imagen
    $nombreBaseSanitizado=sanitizarNombreArchivo($nombreBase);
    //concadenar nombre sanitizado, punto y la extension
    $nombreFinal=$nombreBaseSanitizado.'.'. $extension;
    // $nombreFinal="{$nombreBaseSanitizado}.{$extension}";
    $contador=1;
    While(file_exists("{$dir}/{$nombreFinal}")){
        $nombreFinal="{$nombreBaseSanitizado}-{$contador}.{$extension}";
        $contador++;
    }
    return $nombreFinal;
}

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['myImage'])){
    $image=$_FILES['myImage'];
    $validacion=validarImagen($image);
    if($validacion===true){
        crearDirectorioSiNoExiste(CARPETA_UPLOADS);
        $nombreFinal=generarNombreUnico($image['name'],CARPETA_UPLOADS);
        $rutaFinal=CARPETA_UPLOADS. '/'.$nombreFinal;
        if(move_uploaded_file($image['tmp_name'],$rutaFinal)){
            $_SESSION['success']='Imagen subida correctamente al servidor: '.$nombreFinal;
            $_SESSION['ruta']=$rutaFinal;
        }else{
            $_SESSION['error']='Error al intentar guardar la imagen';
        }
    }else{
        $_SESSION['error']='Error: '.$validacion;
    }
}else{
    $_SESSION['error']='No se ha mandado ningun archivo';
}
header('Location:index.php');
exit;
?>