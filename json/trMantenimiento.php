<?php
	session_start();
	$_SESSION['modulo'] = "trMantenimiento";
    require_once("../funciones/generales.php");
    require_once("../funciones/construct.php");
    require_once("../funciones/utilidades.php");

    $_REQUEST = trasformUppercase($_REQUEST);

    switch($_SESSION['idioma']){
        case 'ES':
            include("../funciones/idiomas/mensajesES.php");
            break;
        case 'EN':
            include("../funciones/idiomas/mensajesEN.php");
            break;
        default:
            include("../funciones/idiomas/mensajesES.php");
    }
	
    switch($_REQUEST['trMantenimientoActionHdn']) {
        case 'getLogMantenimiento':
            getLogMantenimiento();
            break;
        case 'addLogMantenimiento':
            addLogMantenimiento();
            break;
        case 'getMantenimientoDetalle':
            getMantenimientoDetalle();
            break;
        case 'addMantenimiento':
            addMantenimiento();
            break;
        case 'updMantenimiento':
            updMantenimiento();
            break;
        default:
            echo '';
    }

    function getLogMantenimiento(){
        $lsWhereStr = "";

        if ($gb_error_filtro == 0){
            $lsCondicionStr = fn_construct($_REQUEST['trMantenimientoIdTractorHdn'], "idTractor", 0);
            $lsWhereStr = fn_concatena_condicion($lsWhereStr, $lsCondicionStr);
        }
        if ($gb_error_filtro == 0){
            $lsCondicionStr = fn_construct($_REQUEST['trMantenimientoIdViajeHdn'], "idViajeTractor", 0);
            $lsWhereStr = fn_concatena_condicion($lsWhereStr, $lsCondicionStr);
        }
        if ($gb_error_filtro == 0){
            $lsCondicionStr = fn_construct($_REQUEST['trMantenimientoPantallaTxt'], "pantalla", 1);
            $lsWhereStr = fn_concatena_condicion($lsWhereStr, $lsCondicionStr);
        }
        if ($gb_error_filtro == 0){
            $lsCondicionStr = fn_construct($_REQUEST['trMantenimientoUsuarioTxt'], "idUsuario", 1);
            $lsWhereStr = fn_concatena_condicion($lsWhereStr, $lsCondicionStr);
        }
        if ($gb_error_filtro == 0){
            $lsCondicionStr = fn_construct($_REQUEST['trMantenimientoIpTxt'], "ip", 1);
            $lsWhereStr = fn_concatena_condicion($lsWhereStr, $lsCondicionStr);
        }

        $sqlGetMantenimiento = "SELECT * FROM trLogMantenimientoTbl ".$lsWhereStr;
        
        $rs = fn_ejecuta_query($sqlGetMantenimiento);
            
        echo json_encode($rs);
    }

    function addLogMantenimiento(){
    	$a = array();
        $e = array();
        $a['success'] = true;

        if($_REQUEST['trMantenimientoIdTractorHdn'] == ""){
            $e[] = array('id'=>'trMantenimientoIdTractorHdn','msg'=>getRequerido());
            $a['errorMessage'] = getErrorRequeridos();
            $a['success'] = false;
        }
        if($_REQUEST['trMantenimientoIdViajeHdn'] == ""){
            $e[] = array('id'=>'trMantenimientoIdViajeHdn','msg'=>getRequerido());
            $a['errorMessage'] = getErrorRequeridos();
            $a['success'] = false;
        }
        if($_REQUEST['trMantenimientoPantallaTxt'] == ""){
            $e[] = array('id'=>'trMantenimientoPantallaTxt','msg'=>getRequerido());
            $a['errorMessage'] = getErrorRequeridos();
            $a['success'] = false;
        }

        if ($a['success'] == true) {
        	$sqlAddMantenimientoStr = "INSERT INTO trLogMantenimientoTbl ".
        							  "(idTractor, idViajeTractor, pantalla, idUsuario, ip, fechaEvento) ".
        							  "VALUES (".
        							  	$_REQUEST['trMantenimientoIdTractorHdn'].",".
        							  	$_REQUEST['trMantenimientoIdViajeHdn'].",".
        							  	"'".$_REQUEST['trMantenimientoPantallaTxt']."',".
        							  	$_SESSION['idUsuario'].",".
        							  	"'".$_SERVER['REMOTE_ADDR']."',".
                                        "'".date("Y-m-d")."')";

			$rs = fn_ejecuta_query($sqlAddMantenimientoStr);

			if((!isset($_SESSION['error_sql'])) || (isset($_SESSION['error_sql']) && $_SESSION['error_sql'] == "")) {
                $a['sql'] = $sqlAddMantenimientoStr;
                $a['successMessage'] = getLogMantenimientoSuccessMsg();
            } else {
                $a['success'] = false;
                $a['errorMessage'] = $_SESSION['error_sql'] . "<br>" . $sqlAddMantenimientoStr;
            }   
        }
        $a['errors'] = $e;
        $a['successTitle'] = getMsgTitulo();
        echo json_encode($a);
    }

    function getMantenimientoDetalle(){
        $lsWhereStr = "WHERE cd.numeroMantenimiento = cm.numeroMantenimiento ".
                      "AND ct.idTractor = cm.idTractor ".
                      "AND tv.idTractor = cm.idTractor ".
                      "AND tv.idViajeTractor = (SELECT MAX(tv2.idViajeTractor) FROM trviajestractorestbl tv2 ".
                      "WHERE tv.idTractor = tv2.idTractor) ".
                      "AND cd.idViajeTractor = (SELECT MAX(cd3.idViajeTractor) FROM caMantenimientoTractoresDetalleTbl cd3 ".
                        "WHERE cd.numeroMantenimiento = cd3.numeroMantenimiento) ";

        if ($gb_error_filtro == 0){
            $lsCondicionStr = fn_construct($_REQUEST['trMantenimientoIdTractorHdn'], "cm.idTractor", 0);
            $lsWhereStr = fn_concatena_condicion($lsWhereStr, $lsCondicionStr);
        }
        if ($gb_error_filtro == 0){
            $lsCondicionStr = fn_construct($_REQUEST['trMantenimientoClaveMovimientoHdn'], "cm.claveMovimiento", 1);
            $lsWhereStr = fn_concatena_condicion($lsWhereStr, $lsCondicionStr);
        }
        $sqlGetMantenimiento = "SELECT cd.*, cm.claveMovimiento, ct.idTractor, ct.kilometrosservicio, ".
                               "(SELECT sum(kilometrosrecorridos) FROM caMantenimientoTractoresDetalleTbl cd2 ".
                               "WHERE cd.numeroMantenimiento = cd2.numeroMantenimiento ) as kilometrosRecorridosTotal, ".
                               "tv.idViajeTractor as idViajeTractorUltimo ".
                               "FROM caMantenimientoTractoresDetalleTbl cd, caMantenimientoTractoresTbl cm, catractorestbl ct, ".
                               "trviajestractorestbl tv ".
                               $lsWhereStr;

        $rs = fn_ejecuta_query($sqlGetMantenimiento);

        echo json_encode($rs);
    }

    function addMantenimiento(){

    }

    function updMantenimiento(){

    }
?>