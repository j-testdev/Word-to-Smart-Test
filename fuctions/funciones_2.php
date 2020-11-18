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
		$reB[0] = '/(<w:r([A-Za-z0-9:\"\/=\s\-]+)?><w:rPr><w:b(\s?w:val="1")?\/>(<[A-Za-z0-9:\"\/=\s\-]+>){0,}<w:t\s?[A-Za-z0-9:\"\/=\s]{0,}>\s?[ABCDabcd][).]\s?)/';
		$reR[0] = "@@$1";
		$reB[1] = '/(<w:ilvl w:val="1"\/>(<[A-Za-z0-9:\"\/=\s"]{0,}>){0,}<w:b(\s?w:val="1")?\/>(<[A-Za-z0-9:\"\/=\s"]{0,}>){0,}<w:t\s?[A-Za-z0-9:\"\/=\s]{0,}>(\s?[A-Za-z0-9áéíóúÁÉÍÓÚäëïöüÄËÏÖÜñ¿?\-\"!¡.():=%\/]{0,}))/';
		$reR[1] = "@@$1";
		$reB[2] = '/<w:ilvl w:val="1"\/>(<[A-Za-z0-9:\"\/=\s"]{0,}>){0,}<w:t\s?[A-Za-z0-9:\"\/=\s]{0,}>(\s?[A-Za-z0-9áéíóúÁÉÍÓÚäëïöüÄËÏÖÜñ¿?\-\"!¡.():=%\/]{0,})/';
		$reR[2] = "##$2";
		$reB[3] = '/(<w:ilvl w:val="0"\/>(<[A-Za-z0-9:\"\/=\s"]{0,}>){0,}<w:t\s?[A-Za-z0-9:\"\/=\s]{0,}>(\s?[A-Za-z0-9áéíóúÁÉÍÓÚäëïöüÄËÏÖÜñ¿?\-\"!¡.():=%\/]{0,}))/';
		$reR[3] = "\n\n$1";
		$buscar[0] = "/(([0-9]+)\.\s[¿?A-Za-z-_:ÁÉÍÓÚáéíóúñ\s])/";
		$reemplazar[0] = "\n\n$1";
		//$buscar[1] = "/(<w:rPr>(<w:b (w:val=\"1\")?\/>){0}[A-Za-z0-9:\"\/=\s<>]{0,}<w:t [A-Za-z0-9:\"\/=\s]{0,}>\s?[ABCDabcd]\s?[.]\s{1,}|[ABCDabcd]\s?\)\s{0,}|(val=\"[ABCDabcd]\s?[.)]\"\/>))/";
		//$reemplazar[1] = "##$1";
		$buscar[2] = "/@@##/";
		$reemplazar[2] = "#ZZ#";
		$buscar[3] = "/@@/";
		$reemplazar[3] = "";
		$buscar[4] = "/#ZZ#/";
		$reemplazar[4] = "##@@";
		$texto = docx2text($root.$file);
		$texto_separador = explode("PREGUNTAS</w:t>",$texto);
		$texto = count($texto_separador) > 0 ? $texto_separador[count($texto_separador)-1] : $texto;
		// Reemplazao + quitar elementos html,XML
		$texto = strip_tags(preg_replace($reB, $reR, $texto));
		$text = preg_replace($buscar, $reemplazar, $texto);
		$linea = explode("\n",$text);
		$nlineas = count($linea);
		$texto_sintitulos = "";
		for($x=0;$x<$nlineas;$x++){
			if(isset($linea[$x]) && strpos($linea[$x], "##") !== false){
				$contadorOpciones = substr_count($linea[$x], '##');
				$opcionesFaltantes = "";
					if($contadorOpciones < 4){for($z=0;$z<(4-$contadorOpciones);$z++){$opcionesFaltantes .= "##Opción de relleno ";}}
				$saltos = !empty($texto_sintitulos) ? "\r\n\r\n" : "";
				$red = !strpos($linea[$x], "##@@") || substr_count($linea[$x], '##@@') > 1 || substr_count($linea[$x], '##') > 4 ? "[]=> " : "";
				$texto_sintitulos .= $saltos.$red.trim($linea[$x]).$opcionesFaltantes;
			}
		}
		$text = $texto_sintitulos;
		$nombre_archivo_salida = preg_replace('/\\.[^.\\s]{3,4}$/', '.txt', "../downloads/".$file);
    	if($archivo = fopen($nombre_archivo_salida, 'w')){
	      	fwrite($archivo,$text. "\n");
	       	fclose($archivo);
	    }
	}
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
			echo json_encode($ar);
			break;
		default:
				crearTODO('../uploads/',addslashes($_GET['nm']));
			break;
	}
}

?>