<?php
	error_reporting(E_ALL);

	// PHP BackUP MySQL ver. 1.0 from 2017-08-17
	// Este script realiza backup de la base datos, lo almacena en un repositorio local
	// permite controlar el numero de archivos en el repositorio.
	
	// Author:      Freddy Cohen Arbelaez | Galy Ricardo Cerda 
	// Company:     CaribeHost | Cloud Service 
	// Copyright:   GPL (C) 2003-2017
	// URL:   	https://www.caribehost.co/
	// e-Mail:   	soporte@caribehost.co

	// This program is free software; you can redistribute it and/or modify it under the
	// terms of the GNU General Public License as published by the Free Software Foundation;
	// either version 2 of the License, or (at your option) any later version.

	// THIS SCRIPT IS PROVIDED AS IS, WITHOUT ANY WARRANTY OR GUARANTEE OF ANY KIND

	// USAGE

	// */15 * * * * /path-source/mysqli-dump.php source_db_host=localhost source_db_user=root source_db_pass=12345 source_db_name=bd_test

	//OBTENEMOS DEL ARRAY() PARAMETROS DE LA BASE DATOS LOCAL
	parse_str($argv[1], $params); $source_db_host = $params['source_db_host'];
	parse_str($argv[2], $params); $source_db_user = $params['source_db_user'];
	parse_str($argv[3], $params); $source_db_pass = $params['source_db_pass'];
	parse_str($argv[4], $params); $source_db_name = $params['source_db_name'];
	
	ini_set('memory_limit', '-1');
		
	// RUTA ALMACENAMIENTO BACKUP
	$db_backup_path = ''; //ESCRIBA LA RUTA DE LA CARPETA DONDE SE ALMACENARA EL BACKUP (IMPORTANTE ESTA TENGA PERMISOS DE ESCRITURA)

	$db_exclude_tables = array();
	
	//ESTABLECEMOS LA ZONA HORARIA
	date_default_timezone_set('America/Bogota');
	
	$mtables = array(); 
	
	$contents = "-- Database: `".$source_db_name."` --\n";
	
	//CONEXIÓN CON LA BASE DATOS
	$mysqli = new mysqli($source_db_host, $source_db_user, $source_db_pass, $source_db_name);
	
    if ($mysqli->connect_error) {
		trigger_error('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error, E_USER_ERROR);
        die();
    }
    
	mysqli_set_charset( $mysqli, 'utf8');
    
	$results = $mysqli->query("SHOW TABLES");
   
    while($row = $results->fetch_array()){
        if (!in_array($row[0], $db_exclude_tables)){
            $mtables[] = $row[0];
        }
    }
    foreach($mtables as $table){
        $contents .= "-- Table `".$table."` --\n\n";
		
        $contents .= "DROP TABLE IF EXISTS `".$table."` ;\n";
        $results = $mysqli->query("SHOW CREATE TABLE `".$table."` ");
        while($row = $results->fetch_array()){
            $contents .= $row[1].";\n\n";
        }
        $results = $mysqli->query("SELECT * FROM `".$table."` ");
        $row_count = $results->num_rows;
        $fields = $results->fetch_fields();
        $fields_count = count($fields);
       
	    $contents .= "LOCK TABLES `".$table."` WRITE;\n";
		$insert_head = "INSERT INTO `".$table."` (";
        for($i=0; $i < $fields_count; $i++){
            $insert_head  .= "`".$fields[$i]->name."`";
                if($i < $fields_count-1){
                        $insert_head  .= ', ';
                    }
        }
        $insert_head .=  ")";
        $insert_head .= " VALUES"; 
               
        if($row_count>0){
            $r = 0;
            while($row = $results->fetch_array()){
		// NO MODIFIQUE EL RESIDUO, SE ESTABLECE EN 100
                if(($r % 100)  == 0){
                    $contents .= $insert_head;
                }
                $contents .= "(";
                for($i=0; $i < $fields_count; $i++){
                    $row_content =  str_replace("\n","\\n",$mysqli->real_escape_string($row[$i]));
                    
                    switch($fields[$i]->type){
                        case 246: case 8: case 3: case 2:
                            $contents .=  $row_content;
                            break;
                        default:
                            $contents .= "'". $row_content ."'";
                    }
                    if($i < $fields_count-1){
                            $contents  .= ',';
                        }
                }
		// NO MODIFIQUE EL RESIDUO, SE ESTABLECE EN 100
                if(($r+1) == $row_count || ($r % 100) == 99){
                    $contents .= ");\n";
                }else{
                    $contents .= "),";
                }
                $r++;
            }
        }
		$contents .= "UNLOCK TABLES;\n\n";
		$contents =  str_replace(",," , ",NULL,",$contents);
		$contents =  str_replace(",)" , ",NULL)",$contents);
    }
   	
	// VERIFICAMOS CANTOS BACKUP HAY 
		$creaBU = false; //la usaremos como sémaforo para saber si pasó sin problemas por las diferentes validaciones.
		$files 	= glob($db_backup_path . '*.sql'); //busca todos los ficheros que sean .sql
		//Verifica la cantidad de ficheros y si es que hay que borrar y cual.
		if ( $files !== false ){
			$cant = count( $files );
			if($cant >= 45){
				//genera un array para tomar el archivo más antiguo de los que se encuentran
				array_multisort(
					array_map( 'filemtime', $files ),
					SORT_NUMERIC,
					SORT_ASC,
					$files
				);
				//Si ya se alcanzó el máximo número, se borra el backup más antiguo.
				unlink($files[0]);
				$creaBU 	= true;
			}else{
				//áun no se llega a los 45 respaldos
				$creaBU 	= true;
			}
		}else{
			//si no encuentra quiere decir que no hay ficheros y crea el back de todas formas.
			$creaBU 	= true;
		}
		
		if($creaBU): 

			if (!is_dir ( $db_backup_path )) {
				mkdir ( $db_backup_path, 0777, true );
			 }

			$backup_file_name = $source_db_name."-".date( "dmY").".sql";

			$fp = fopen($db_backup_path."/".$backup_file_name ,'w+');

			if (($result = fwrite($fp, $contents))) {
				echo "Backup file created"." ".$backup_file_name." "."(".$result.")";
			} else {
				trigger_error("No se pudo crear el BackUP", E_USER_ERROR);
			}
			fclose($fp);

		endif;
?>
