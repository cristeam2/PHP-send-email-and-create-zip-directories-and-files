<?php 

//Configurar la hora del servidor
date_default_timezone_set('Europe/Madrid');

echo "\nIniciamos: ".date('d m Y H:i:s')."\n";

//Libraria para enviar emails
require_once('./PHPMailer_5.2.4/class.phpmailer.php');


//Ruta donde guardar los resultados
$rutaProvicional='/fill out this with the path/';

//Creo directorio segun la fecha actual
mkdir($rutaProvicional.date("Y m d").'/',0777);

//Doy permisos para luego porder borrar los archivos y directorio
chmod($rutaProvicional.date("Y m d").'/',0777);

//Agrego a la ruta la fecha
$ruta=$rutaProvicional.date("Y m d").'/';




//Funcion para generar un fichero con resultados si es que el array no esta vacio 
//Crea los ficheros para los reportes de actualizacion de fechas
function crearFichero($array,$nombre)
{
  global $ruta;
  if (!empty($array)) 
  { 
    $nombre=$ruta.date("Y m d ").$nombre;
    
    if (!$myfile=fopen($nombre, "w"))
    {
          throw new Exception("Unable to open file: $nombre");
    }
    foreach ($array as $key => $value) 
    { 
            fwrite($myfile, $value."\n\n");    
    }
    fclose($myfile);
  }
}



//Comprime los archivos en .zip en el servidor
function comprimirYborraFicheros($rutaProvicional)
{
      //El real path a la carpeta
      $rootPath = realpath($rutaProvicional.date("Y m d").'/');

      //Initializacion del zip
      $zip = new ZipArchive();
      $zip->open($rutaProvicional.date("Y m d").'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

      // Initialicion de empty "delete list"
      $filesToDelete = array();


      // Creacion de  directorio recursivi  iterador
      $files = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($rootPath),
          RecursiveIteratorIterator::LEAVES_ONLY
      );

      foreach ($files as $name => $file)
      {
          // Saltar estos directorios ya que se agregaran luego
          if (!$file->isDir())
          {
              //Realy relativo path para el file
              $filePath = $file->getRealPath();
              $relativePath = substr($filePath, strlen($rootPath) + 1);

              //Agrego el actual file al archivo
              $zip->addFile($filePath, $relativePath);

              //Agrego el actual file a "delete list"
              //Lo borro
              //Esto es solo salvo que no querramos borrar algun archivo( puede resultar interesante para tests)
              if ($file->getFilename() != 'important.txt')
              {
                  $filesToDelete[] = $filePath;
              }
          }
      }
      //Se crean los .Zip
      $zip->close();

      //Borro todos los files de "delete list"
      foreach ($filesToDelete as $file)
      {
          unlink($file);
      }

      rmdir($rootPath);
}

//Enviar email 
function enviarEmailSpamMode($array,$centro,$motivo)
{
    $para=dameEmailPorCentro($centro);
    $mensaje ="";
    foreach ($array as $key => $value) {
        
        $mensaje .= ($key+1)."- ".$value."\r\n\r\n";
    }
    $header = "From:  Procesos Automáticos <mail@domain.com>";
    if (!empty($para))
    {		
        mail($para, $motivo, $mensaje,$header);
    }
}

//Enviar email 
function enviarEmail($array,$para,$motivo)
{
      if (!empty($para))
      {
            $to=explode(", ",$para);
            $mensaje ="";
            foreach ($array as $key => $value) 
            {
                $mensaje .= ($key+1)."- ".$value."\r\n\r\n";
            }
            $mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
            $mail->CharSet = 'UTF-8';
            $mail->IsSMTP(); // telling the class to use SMTP

            try 
            {
              //$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
              $mail->SMTPAuth   = true;                  // enable SMTP authentication
              $mail->Host       = "smtp.gmail.com"; // sets the SMTP server
              $mail->Port       = 465;                    // set the SMTP port for the GMAIL server
              $mail ->SMTPSecure = "ssl";
              $mail->Username   = "user@domain.com"; // SMTP account username

              $mail ->From = "mail@domain.com";

              $mail ->Body = $mensaje;

              $mail->Password   = "";        // SMTP account password
              foreach($to as $to_add)
              {
                    $mail->AddAddress($to_add);                  // name is optional
              }
              //$mail->AddAddress($para, '');
              $mail ->FromName = "";

              $mail->SetFrom('mail@domain.com', 'Procesos Automáticos');
              //$mail->AddReplyTo('name@yourdomain.com', 'First Last');
              $mail->Subject = $motivo;
              //$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
              //$mail->MsgHTML(file_get_contents('contents.html'));
              $mail->Body =$mensaje;
              //$mail->MsgHTML($mensaje);
              //$mail->AddAttachment('images/phpmailer.gif');      // attachment
              //$mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
              $mail->Send();
              //echo "Mensaje enviado\n";
            } 
            catch (phpmailerException $e) 
            {
                echo $e->errorMessage(); //Pretty error messages from PHPMailer
            }
            catch (Exception $e) 
            {
                echo $e->getMessage(); //Boring error messages from anything else!
            }
    }
}



$arrayConContenido[]="ejemplo";

try
{
  crearFichero($arrayConContenido,"Nombre del fichero.txt");
}
catch (Exception $e) 
{
  echo 'Excepción capturada: ',  $e->getMessage(), "\n";
}
enviarEmail($arrayConContenido,"email1@dominio.com, email2@dominio.com","motivo");


comprimirYborraFicheros($rutaProvicional);


echo "\nFin: ".date('d m Y H:i:s')."\n";

?>

