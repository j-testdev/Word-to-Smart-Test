<?php

function docx2text($filename){
	return readZippedXML($filename, "word/document.xml");
}
function readZippedXML($archiveFile, $dataFile){
		// Create new ZIP archive
	$zip = new Ziparchive;
		// Open received archive file
	if (true === $zip->open($archiveFile)){
			// If done, search for the da fil in the archive
		if(($index = $zip->locateName($dataFile)) !== false){
				// If found, read it to the string
			$data = $zip->getFromIndex($index);
				// Close archive file
			$zip->close();
				// Load XML fron a string
				// Skip errors and warnings
			$xml = new DOMDocument();
			$xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
				// Return data without XML formating tags
			return $xml->saveXML();
		}
		$zip->close();
	}
	// In case of failure return emoty string
	return "";
}


function crearTODO($root, $file){
	if(file_exists($root.$file)){
		//Arrays de búsqueda y reemplazao
		$buscar = $reemplazar = $reB = $reR = array();
		$reB[0] = '/<w:ilvl w:val="1"\/>/';
		$reR[0] = "##";
		$reB[1] = '/<w:ilvl w:val="0"\/>/';
		$reR[1] = "[Q]$1";
		$reB[2] = '/(<\/w:pPr>){1,2}((<w:[^t][A-Za-z\s0-9:="\/\-_]*>)*<w:rPr>(<w:[^t][A-Za-z\s0-9:="\/]*>)*<w:b(\s?w:val="1")?\/>(<w:[^t][A-Za-z\s0-9:="\/\-_]*>)*<\/w:rPr><w:t[A-Za-z\s0-9:="]*>)/';
		$reR[2] = "$1@@$2";
		$buscar[0] = "/\[Q]/";
		$reemplazar[0] = "\n\n$1";
		$buscar[1] = "/@@##/";
		$reemplazar[1] = "#ZZ#";
		$buscar[2] = "/#ZZ#/";
		$reemplazar[2] = "##@@";
		$buscar[3] = "/#([^\.#\n]*)\./";
		$reemplazar[3] = "#$1";
		$texto = docx2text($root.$file);
		$texto_separador = explode("PREGUNTAS</w:t>",$texto);
		$texto = count($texto_separador) > 0 ? $texto_separador[count($texto_separador)-1] : $texto;
		// Reemplazo + quitar elementos html,XML
		$texto = strip_tags(preg_replace($reB, $reR, $texto));
		$text = preg_replace($buscar, $reemplazar, $texto);
		$text = preg_replace_callback('/[^#@\n¿?]*/', function($match) {return ucfirst($match[0]);}, $text);
		$linea = explode("\n",$text);
		$nlineas = count($linea);
		$texto_sintitulos = "";
		for($x=0;$x<$nlineas;$x++){
			if(isset($linea[$x]) && strpos($linea[$x], "##") !== false){
				$contadorOpciones = substr_count($linea[$x], '##');
				$opcionesFaltantes = "";
				if($contadorOpciones < 4){for($z=0;$z<(4-$contadorOpciones);$z++){$opcionesFaltantes .= "##Opción de relleno";}}
				$saltos = !empty($texto_sintitulos) ? "\r\n\r\n" : "";
				$red = !strpos($linea[$x], "##@@") || substr_count($linea[$x], '##@@') > 1 || substr_count($linea[$x], '##') > 4 ? "[]=> " : "";
				$texto_sintitulos .= $saltos.$red.trim($linea[$x]).$opcionesFaltantes;
			}
		}
		$text = htmlspecialchars_decode($texto_sintitulos);
		$nombre_archivo_salida = preg_replace('/\\.[^.\\s]{3,4}$/', '.txt', "../downloads/".$file);
		if($archivo = fopen($nombre_archivo_salida, 'w')){
			fwrite($archivo,$text. "\n");
			fclose($archivo);
		}
		daypo($text,$file);
	}
}

function daypo($text,$file){
	if(!substr_count($text, '[]=>')){
		$linea = explode("\n",$text);
		$nlineas = count($linea);
		$nombre = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);
		$contenido = "<test v=\"2.0\"><p><t>{$nombre}</t><d>Sistemas informáticos</d><a>Juanjo</a><c>8</c><o/><p/><e>207803843192851</e><r>50718403882185</r></p><f/>";
		for ($x=0;$x<$nlineas;$x++) {
			if(strpos($linea[$x], "##")){
				$contenido .= $x == 0? "<c><c><t>0</t><p>" :"<c><t>0</t><p>";
				$solu = preg_replace('/[^#@]+/', '',$linea[$x]);
				$solu = str_replace('##@@', '2', $solu);
				$solu = str_replace('##', '1', $solu);
				$mostrar = str_replace('##Opción de relleno','',$linea[$x]);
				$mostrar = str_replace('##@@', '##',$mostrar);
				$mostrar = preg_replace('/##/', '[@FP]<o>',$mostrar,1);
				$mostrar = str_replace('##', '</o><o>',$mostrar);
				$mostrar = str_replace('[@FP]', '</p><c>'.$solu.'</c><r>', $mostrar);
				$contenido .= $mostrar;
				$contenido .= "</o></r></c>";
			}

		}
		$contenido .= "</c><i/></test>";
		$contenido = preg_replace("/[\r\n|\n|\r]+/", " ", $contenido);
		$nombre_archivo_salida = preg_replace('/\\.[^.\\s]{3,4}$/', ' - daypo', "../downloads/".$file);
		writeStringToFile($nombre_archivo_salida, $contenido);
	}
}

function writeStringToFile($file, $string){
	$f=fopen($file, "wb");
    $file="\xEF\xBB\xBF".$file; // this is what makes the magic
    fputs($f, $string);
    fclose($f);
}
function borratodo($carpeta){
	foreach(glob($carpeta . "/*") as $archivos_carpeta){             
		if (is_dir($archivos_carpeta)){
			borratodo($archivos_carpeta);
		} else {
			unlink($archivos_carpeta);
		}
	}
	if(substr_count($carpeta,"/") > 1){rmdir($carpeta);}
}

function showinfo($name,&$descarga){
	if(file_exists('../downloads/'.$name)){
		$file = fopen('../downloads/'.$name, "r") or exit("Unable to open file!");
		//Output a line of the file until the end is reached
		$errores = 0;
		$lineas = 0;
		$preguntasConError = "";
		while(!feof($file))
		{
			$contenido = fgets($file);
			if(strpos($contenido, "##")) $lineas++;
			if(substr_count($contenido,"[]=>") != 0){
				$errores++;
				$tipoError="";
				//Errores
				if(substr_count($contenido,"##@@") > 1){
					$tipoError .= "Hay más de una respuesta correcta.</br>";
				}
				if(substr_count($contenido,"##@@") == 0){
					$tipoError .= "No hay ninguna respuesta correcta.</br>";
				}
				if(substr_count($contenido,"##") > 4){
					$tipoError .= "Hay más de 4 opciones (".substr_count($contenido,"##Opción de relleno") - substr_count($contenido,"##")." respuestas)</br>";
				}
				if(substr_count($contenido,"##") < 4){
					$tipoError .= "Hay menos de 4 respuestas.</br>";
				}

				if(empty($tipoError)){
					$tipoError = "Error desconocido.</br>";
				}

				
				$preguntasConError .= $lineas . ' - '.$tipoError;
			}
		}
		fclose($file);
		$mensaje = "Número de preguntas: ".$lineas."<hr>";
		if($errores != 0){
			$mensaje .= "</br>Errores encontrados: ".$errores;
			$mensaje.= "</br>Preguntas con error: </br>".$preguntasConError;
		}
		$descarga = $errores!=0 || $lineas == 0 ? false : true;
		return $mensaje;
	}
	else{
		return "Error: ¡El archivo no existe!";
	}
}

function borrarzip($namer){
	$namer = preg_replace('/\\.[^.\\s]{3,4}$/', '.txt', $namer);
	if($namer == 'qweqweqwedwdfdestriurtodoqweqweqweqwe'){
		borratodo('../downloads'); 
		borratodo('../uploads');
	}else{
		sleep(8);
		unlink('../downloads/'.$namer); 
		unlink('../uploads/'.$namer);
	}
}

if(isset($_GET['code'])){
	switch (addslashes($_GET['code'])) {
		case 1: //borrar
		borrarzip(addslashes($_GET['nm']));
		break;
		case 2: // comprobador de subida
		$exitwo = file_exists('../downloads/'.addslashes($_GET['nm'])) ? 1 : 0;
		$ar["exitoo"] = $exitwo;
		$ar["estado"] = showinfo(addslashes($_GET['nm']),$descargar);
		$ar["descargar"] = $descargar;
		echo json_encode($ar);
		break;
		default:
		crearTODO('../uploads/',addslashes($_GET['nm']));
		break;
	}
}

?>