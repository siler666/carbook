<?php

  /*********************************************
  Sólo necesita reciboGastosIdViajeTractor para funcionar
  *********************************************/
	require_once("../funciones/fpdf/fpdf.php");
  require_once("../funciones/generales.php");
  require_once("../funciones/construct.php");
  require_once("../funciones/utilidades.php");
  require_once("../funciones/funcionesGlobales.php");
  require_once("trGastosViajeTractor.php");
  $_SESSION['usuario'] = 'MARIO MARTINEZ FERNANDEZ';
  $_SESSION['usuCto'] = "CDTOL";

  switch($_SESSION['idioma']){
        case 'ES':
            include_once("../funciones/idiomas/mensajesES.php");
            break;
        case 'EN':
            include_once("../funciones/idiomas/mensajesEN.php");
            break;
        default:
            include_once("../funciones/idiomas/mensajesES.php");
    }
    $a = array();
    $e = array();
        
  $success = true;
  if($_REQUEST['reciboGastosIdViajeTractor'] == ""){
      $e[] = array('id'=>'reciboGastosIdViajeTractor','msg'=>getRequerido());
      $success = false;
  }


  $pdf = new FPDF('P', 'mm', array(100, 150));

  if ($success) {
        $sqlGetDataStr = "SELECT tv.claveChofer, cc.apellidoPaterno,cc.apellidoMaterno,cc.nombre, ".
        "tv.viaje,ct.tractor, ct.compania, tv.claveMovimiento, ".
        "(SELECT folio from trfoliostbl tf WHERE tf.compania = 'TR' AND tf.tipoDocumento = 'TG' ".
        " AND tf.CentroDistribucion = '".$_SESSION['usuCto']."') AS folio, ".
        "(SELECT plaza FROM caplazastbl cp WHERE tv.idPlazaOrigen = cp.idPlaza) AS plazaOrigen, ".
        "(SELECT plaza FROM caplazastbl cp WHERE tv.idPlazaDestino = cp.idPlaza) AS plazaDestino, ".
        "tv.kilometrosTabulados, ct.rendimiento, tv.numeroRepartos, tv.numeroUnidades, ".
        "(SELECT count(*)FROM trtalonesviajestbl tt WHERE tt.idViajeTractor = tv.idViajeTractor) AS talonesTotal, ".
        "(SELECT valor FROM cageneralestbl where tabla = 'trgastosviajetractortbl' AND columna = 'ISO') AS ISO ".
        "FROM trviajestractorestbl tv, cachoferestbl cc, catractorestbl ct ". 
        "WHERE tv.claveChofer = cc.claveChofer ".
        "AND tv.idtractor = ct.idtractor ".
        "AND tv.idViajeTractor = ".$_REQUEST['reciboGastosIdViajeTractor'];

      $rs = fn_ejecuta_query($sqlGetDataStr);

      $data = $rs['root'][0];
      if (sizeof($data) > 0) {
        $pdf = generarPdf($data, $pdf, $nInt); 
      }

    $pdf->Output('RECIBO DE '.$data['claveChofer'].' - '.$data['apellidoPaterno'].' '.$data['apellidoMaterno'].' '.
        $data['nombre'].'.pdf', I);
  } else {
      $a['errorMessage'] = getErrorRequeridos();
      $a['errors'] = $e;
      $a['successTitle'] = getMsgTitulo();
      echo json_encode($a);
    }
    function generarPdf($data, $pdf, $n){
    //Solo para pruebas, por defecto 0
    $sqlGetCalculosGastosComplementosStr = "SELECT tg.concepto AS id,sum(tg.importe) AS cantidad, tg.observaciones, ".
                                              "(SELECT nombre FROM caconceptostbl cc WHERE cc.concepto = tg.concepto) ". 
                                              "AS DescConcepto FROM trgastosviajetractortbl tg WHERE tg.idViajeTractor = ".$_REQUEST['reciboGastosIdViajeTractor'].
                                              " AND tg.folio = (SELECT folio from trfoliostbl tf WHERE tf.compania = 'TR' AND tf.tipoDocumento = 'TG' ".
                                              " AND tf.CentroDistribucion = '".$_SESSION['usuCto']."') ".
                                              "GROUP BY tg.concepto;";

    $rsConcepto = fn_ejecuta_query($sqlGetCalculosGastosComplementosStr);

    $border = 0;
    if(sizeof($data) > 0){
      $pdf->AddPage();
      $pdf->SetMargins(0.2, 0.2, 0.2, 0.2);
      if ($n > 0) {
        $pdf->SetY(10);
        $pdf->SetX(10);
      }
      //Etiqueta dependiendo de la clave Movimiento
      switch ($data['claveMovimiento']) {
        case 'VA':  
          $pdf->setY(5);
          $pdf->SetX(0);
          $pdf->SetFont('Arial','',5);
          $pdf->Cell(0,10,utf8_decode('RECIBO DE ANTICIPO DE GASTOS ACOMPAÑANTE'), $border,0, 'C');
          break;
        
        case 'VG':
          $pdf->setY(5);
          $pdf->SetX(0);
          $pdf->SetFont('Arial','',5);
          $pdf->Cell(0,10,'RECIBO DE ANTICIPO DE GASTOS', $border,0, 'C');
          break;
        case 'VC':
          $pdf->setY(5);
          $pdf->SetX(0);
          $pdf->SetFont('Arial','',5);
          $pdf->Cell(0,10,'RECIBO DE ANTICIPO DE GASTOS VIAJE VACIO', $border,0, 'C');
          break;

      }
      //numeroTalon  
      $pdf->setY(5);
      $pdf->SetX(0);
      $pdf->SetFont('Arial','',5);
      $pdf->Cell(93,10,$data['folio'], $border,0, 'R');  
      //FechaOriginal
      $pdf->setY(8);
      $pdf->SetX(0);
      $pdf->SetFont('Arial','',5);
      $pdf->Cell(93,10,date("d/m/Y H:i:s"), $border,0, 'R');  

      //OPERADOR
      $pdf->SetY(12);
      $pdf->SetX(5);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'OPERADOR:   '.$data['claveChofer'].' - '.$data['apellidoPaterno'].' '.$data['apellidoMaterno'].' '.
        $data['nombre'], $border,1, 'L');

      //TRACTOR
      $pdf->SetY(12);
      $pdf->SetX(60);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'TRACTOR:   '.$data['tractor'], $border,1, 'L');

      //VIAJE
      $pdf->SetY(12);
      $pdf->SetX(84);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'VIAJE:   '.$data['viaje'], $border,1, 'L');

      //ORIGEN
      $pdf->SetY(15);
      $pdf->SetX(5);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'ORIGEN:   '.$data['plazaOrigen'], $border,1, 'L');

      //DESTINO
      $pdf->SetY(15);
      $pdf->SetX(40);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'DESTINO:   '.$data['plazaDestino'], $border,1, 'L');

      //KILOMETROS TABULADOS
      $pdf->SetY(15);
      $pdf->SetX(81);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'KMS:   '.$data['kilometrosTabulados'], $border,1, 'L');

      //KILOMETROS TABULADOS  
      $pdf->SetY(18);
      $pdf->SetX(5);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'RENDIMIENTO:   '.$data['rendimiento'], $border,1, 'L');

      //NUMERO DE REPARTOS
      $pdf->SetY(18);
      $pdf->SetX(30);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'NO. REPARTOS:   '.$data['numeroRepartos'], $border,1, 'L');

      //NUMERO DE TALONES
      $pdf->SetY(18);
      $pdf->SetX(53);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'NO. TALONES:   '.$data['talonesTotal'], $border,1, 'L');

      //NUMERO DE UNIDADES
      $pdf->SetY(18);
      $pdf->SetX(76);
      $pdf->SetFont('Arial','',5);    
      $pdf->Cell(40,10,'NO. UNIDADES:   '.$data['numeroUnidades'], $border,1, 'L');
/**
 * CONCEPTOS
 */
$y = 20;
$x = 8;
//for ($nInt=0; $nInt < sizeof($concepto); $nInt++) { 
for ($iInt=0; $iInt < sizeof($rsConcepto['root']); $iInt++){
  $totalImporte = $totalImporte + $rsConcepto['root'][$iInt]['cantidad'];
  $importe = number_format($rsConcepto['root'][$iInt]['cantidad'], 2, ".", ",");
   
      if($iInt == 3 || $iInt == 6){
        $x = $x+34;
        $y = 20;
      }
      $y = $y+3;
      $pdf->SetY($y);
      $pdf->SetX($x);
      $pdf->SetFont('Arial','',5);
      if($rsConcepto['root'][$iInt]['observaciones'] == '' && $rsConcepto['root'][$iInt]['id'] != '2315'){    
        $pdf->Cell(40,10,$rsConcepto['root'][$iInt]['DescConcepto'].': $'.$rsConcepto['root'][$iInt]['cantidad'], $border,1, 'L');
    }else{
      $pdf->Cell(40,10,$rsConcepto['root'][$iInt]['DescConcepto'].': '.$rsConcepto['root'][$iInt]['observaciones'].'  lts.   $'.$rsConcepto['root'][$iInt]['cantidad'], $border,1, 'L');
    }
}
  //TOTAL KILOMETROS
  $TotalKms = $data['kilometrosTabulados'] * 2;
  $TotalKms = number_format($TotalKms, 2, ".", ",");
  $pdf->SetY(34);
  $pdf->SetX(8);
  $pdf->SetFont('Arial','',5);    
  $pdf->Cell(40,10,'RECORRIDO TOTAL: '.$TotalKms.' Kms.', $border,1, 'L');


  //TOTAL GASTOS
  $cent = ((($totalImporte / 100) - floor($totalImporte/100))*100);
  if ($cent > 1) {
  $totalImporte += 100 - $cent;
  }
    //NUMERO EN LETRAS
  $pdf->SetY(37);
  $pdf->SetX(8);
  $pdf->SetFont('Arial','',5);    
  $pdf->Cell(40,10,numtoletras($totalImporte), $border,1, 'L');

  $totalImporte = number_format($totalImporte, 2, ".", ",");

  $pdf->SetY(34);
  $pdf->SetX(65);
  $pdf->SetFont('Arial','',5);    
  $pdf->Cell(40,10,'TOTAL DE GASTOS: $'.$totalImporte, $border,1, 'L');



  //FIRMAS
  //CAPUTURADOR
  $pdf->SetY(60);
  $pdf->SetX(12);
  $pdf->SetFont('Arial','',5);    
  $pdf->Cell(40,10,'----------------------------------------------', $border,1, 'L');
  $pdf->SetY(62);
  $pdf->SetX(12);
  $pdf->Cell(40,10,$_SESSION['usuario'] , $border,1, 'L');
  //OPERADOR
  $pdf->SetY(60);
  $pdf->SetX(60);
  $pdf->SetFont('Arial','',5);    
  $pdf->Cell(40,10,'----------------------------------------------', $border,1, 'L');
  $pdf->SetY(62);
  $pdf->SetX(64);
  $pdf->Cell(40,10,'FIRMA DEL OPERADOR' , $border,1, 'L');
  //ISO
  $pdf->SetY(68);
  $pdf->SetX(62);
  $pdf->Cell(40,10,$data['ISO'] , $border,1, 'L');
      return $pdf;

    } else {
      echo json_encode(array('success'=>false, 'errorMessage'=>$_SESSION['error_sql']." <br> ".$sqlGetDataStr));
    }
  }
?>
