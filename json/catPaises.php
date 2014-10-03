<?php
	session_start();
	$_SESSION['modulo'] = "catPaises";
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
	
    switch($_REQUEST['catPaisesActionHdn']){
        case 'getPaises':
            getPaises();
            break;
        case 'addPais':
        	addPais();
        	break;
        case 'updPais':
        	updPais();
        	break;
        case 'dltPais':
        	dltPais();
        	break;
    }

    function getPaises(){
    	$lsWhereStr = "";

	    if ($gb_error_filtro == 0){
    		$lsCondicionStr = fn_construct($_REQUEST['catPaisesIdHdn'], "idPais", 0);
		    $lsWhereStr = fn_concatena_condicion($lsWhereStr, $lsCondicionStr);
	    }
		if ($gb_error_filtro == 0){
    		$lsCondicionStr = fn_construct($_REQUEST['catPaisesPaisTxt'], "pais", 1);
		    $lsWhereStr = fn_concatena_condicion($lsWhereStr, $lsCondicionStr);
	    }

		$sqlGetPaisStr = "SELECT * FROM caPaisesTbl " . $lsWhereStr;
			
        $rs = fn_ejecuta_query($sqlGetPaisStr);
        
		echo json_encode($rs);
    }

    function addPais(){

    	$a = array();
        $e = array();
        $a['success'] = true;

    	if($_REQUEST['catPaisesPaisTxt'] == ""){
            $e[] = array('id'=>'catPaisesPaisTxt','msg'=>getRequerido());
			$a['errorMessage'] = getErrorRequeridos();
            $a['success'] = false;
        }

        //Revisa que no exista un país igual ya hecho
        if ($a['success'] == true) {
            $sqlCheckPaisStr = "SELECT * FROM caPaisesTbl ".
                               "WHERE pais= '".$_REQUEST['catPaisesPaisTxt']."'";
        
            $rs = fn_ejecuta_query($sqlCheckPaisStr);

            if($rs == false || sizeof($rs['root']) > 0){
                $a['errorMessage'] = getPaisDuplicateMsg();
                $a['success'] = false;
            }
        }

        if ($a['success'] == true){
        	$sqlAddPaisStr = "INSERT INTO caPaisesTbl (pais) ".
        					 "VALUES ('".$_REQUEST['catPaisesPaisTxt']."')";

			$rs = fn_ejecuta_query($sqlAddPaisStr);

			if((!isset($_SESSION['error_sql'])) || (isset($_SESSION['error_sql']) && $_SESSION['error_sql'] == "")){
			    $a['sql'] = $sqlAddPaisStr;
                $a['successMessage'] = getPaisSuccessMsg();
                $a['id'] = $_REQUEST['catPaisesIdHdn'];
			} else {
                $a['success'] = false;
                $a['errorMessage'] = $_SESSION['error_sql'] . "<br>" . $sqlAddPaisStr;
			}
        }
        $a['errors'] = $e;
		$a['successTitle'] = getMsgTitulo();
        echo json_encode($a);
    }

    function updPais(){
    	$a = array();
        $e = array();
        $a['success'] = true;

    	if($_REQUEST['catPaisesPaisTxt'] == ""){
            $e[] = array('id'=>'catPaisesPaisTxt','msg'=>getRequerido());
			$a['errorMessage'] = getErrorRequeridos();
            $a['success'] = false;
        }

        //Revisa que no exista un país igual ya hecho
        $sqlCheckPaisStr = "SELECT * FROM caPaisesTbl ".
                           "WHERE pais= '".$_REQUEST['catPaisesPaisTxt']."'";
        
        $rs = fn_ejecuta_query($sqlCheckPaisStr);

        if($rs == false || sizeof($rs['root'])> 0){
            $a['errorMessage'] = getPaisDuplicateMsg();
            $a['success'] = false;
        }

        if ($a['success'] == true){
        	$sqlUpdatePaisStr = "UPDATE caPaisesTbl ".
        						"SET pais= '".$_REQUEST['catPaisesPaisTxt']."' ".
        						"WHERE idPais=".$_REQUEST['catPaisesIdHdn'];

        	$rs = fn_ejecuta_query($sqlUpdatePaisStr);

        	if((!isset($_SESSION['error_sql'])) || (isset($_SESSION['error_sql']) && $_SESSION['error_sql'] == "")){
			    $a['sql'] = $sqlUpdatePaisStr;
                $a['successMessage'] = getPaisUpdateMsg();
                $a['id'] = $_REQUEST['catPaisesIdHdn'];
			} else {
                $a['success'] = false;
                $a['errorMessage'] = $_SESSION['error_sql'] . "<br>" . $sqlUpdatePaisStr;
			}
        }
        $a['errors'] = $e;
		$a['successTitle'] = getMsgTitulo();
        echo json_encode($a);

    }

    function dltPais(){

    	$a = array();
        $e = array();
        $a['success'] = true;

    	$sqlDeletePaisStr = "DELETE FROM caPaisesTbl WHERE idPais=".$_REQUEST['catPaisesIdHdn'];
        
        $rs = fn_ejecuta_query($sqlDeletePaisStr);

        if((!isset($_SESSION['error_sql'])) || (isset($_SESSION['error_sql']) && $_SESSION['error_sql'] == "")) {
			$a['sql'] = $sqlDeletePaisStr;
            $a['successMessage'] = getPaisDeleteMsg();
            $a['id'] = $_REQUEST['catPaisesIdHdn'];
		} else {
	        $a['success'] = false;
            $a['errorMessage'] = $_SESSION['error_sql'] . "<br>" . $sqlDeletePaisStr;
		}
		
		$a['errors'] = $e;
		$a['successTitle'] = getMsgTitulo();
        echo json_encode($a);		

    }
?>