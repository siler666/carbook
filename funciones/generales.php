<?php
    require("sqlErrorMessages.php");
    
    function fn_ejecuta_query($ps_sql){
		set_time_limit(0);
        $_SESSION['error_sql'] = "";
        $_SESSION['sql_debug'] = $ps_sql;

        date_default_timezone_set('America/Mexico_City');
		
		$usuario = $_SESSION['usuario'];
        $ip = $_SERVER['REMOTE_ADDR'];
        //$hoy = getdate();
        //$fecha = date('Y-m-d H:i:s', $hoy[0]);
        $modulo = $_SESSION['modulo'];
        //'I cannot connect to the database because: ' . mysql_error()
        //$link = mysql_connect ("localhost", "root", "toor") or die (json_encode(array('succcess'=>'false','error_sql'=>mysql_error(),'errorMessage'=>'Error de conexi&oacute;n')));
        //$link = mysql_connect ("192.168.10.4", "sima", "sima8319") or die (json_encode(array('succcess'=>'false','error_sql'=>mysql_error(),'errorMessage'=>'Error de conexi&oacute;n')));
        $link = mysql_connect ("10.1.2.174", "mario", "mario") or die (json_encode(array('succcess'=>'false','error_sql'=>mysql_error(),'errorMessage'=>'Error de conexi&oacute;n')));
        mysql_select_db ("tracomexdb");
        $result = mysql_query($ps_sql, $link);
        $rsArr = array('success'=>true,'records'=>mysql_num_rows($result));
        $iInt = 0;
        //To array
        while($row = mysql_fetch_assoc($result)){
            $rsArr['root'][$iInt] = $row;
            $iInt++;
        };

        if (!isset($rsArr['root'])) {
            $rsArr['root'] = null;
        }

        //Log
        $logFile = fopen(str_replace('funciones', 'log', str_replace('generales.php', "log_".date("Y-m-d", strtotime("now")).".log", __FILE__)), "a");
        fwrite($logFile, date("H:i:s - ", strtotime("now")).$ps_sql."\r\n");
        fclose($logFile);

        if (mysql_errno($link) == 0)
            $error_msg = "";
        else
            $error_msg = mysqlErrorMessages(mysql_errno($link));
        
		//$sqlMonitor = mysql_query("INSERT INTO lxs_monitor VALUES ('" . $_SESSION['usuario'] . "', '" . $_SESSION['ingreso'] . "', '" . $modulo . "', '" . $ps_sql . "', '" . $error_msg . "', NOW())", $link);
		 
        $_SESSION['error_sql'] = $error_msg;
        $_SESSION['error_sql_debug'] = $error_msg;
		      
        return $rsArr;
    }
?>