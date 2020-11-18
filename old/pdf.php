<?php
require_once("pdf2text.php");
$result = pdf2text ('El ordenador en sí está constituido por.pdf');
echo '<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
</hea>
<body>
'.$result.'
</body>
</html>';