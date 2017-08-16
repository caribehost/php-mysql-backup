<?php
/*
Autor: CaribeHost | Cloud Service
Website: https://www.caribehost.co
Desarroladores : Galy Ricardo Cerda | Freddy Cohen Arbelaez
Email: soporte@caribehost.co
*/
	//PARAMETROS DE LA BASE DATOS
	$db_host= '';  //mysql host
	$db_uname = '';  //user
	$db_password = ''; //pass
	$db_to_backup = ''; //database name
	
	$db_backup_path = ''; //ruta donde se almacena el archivo .sql eje: C:\/Inetpub\/vhosts\/project\/sqldump\/
	$db_exclude_tables = array();
	
	//ESTABLECEMOS LA ZONA HORARIA
	date_default_timezone_set('America/Bogota');
	
	$mtables = array(); 
	
	$contents = "-- Database: `".$db_to_backup."` --\n";
	
	//CONEXIÓN CON LA BASE DATOS
	$mysqli = new mysqli($db_host, $db_uname, $db_password, $db_to_backup);
	
    if ($mysqli->connect_error) {
        die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
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
       
	    $insert_head = "LOCK TABLES `".$table."` WRITE ;\n"; 
        $insert_head .= "INSERT INTO `".$table."` \n"; 
        $insert_head .= " VALUES (\n";       
               
        if($row_count>0){
            $r = 0;
			
            while($row = $results->fetch_array()){
                if($r == 0){
                    $contents .= $insert_head;
                }
                //$contents .= "(";
                for($i=0; $i < $fields_count; $i++){
                    $row_content =  str_replace("\n","\\n",$mysqli->real_escape_string($row[$i]));
					          $row_content =  str_replace("{}","\{\}",$mysqli->real_escape_string($row[$i]));
                   
					
					switch($fields[$i]->type){
                        case 3: case 8:
                            $contents .=  $row_content;
							break;
                        default:
                            $contents .= "'". $row_content ."'";
							
                    }
                    if($i < $fields_count-1){
                            $contents  .= ', ';
					}
					if($i == $fields_count-1){
						$contents  .= '), (';
					}
                }
                if(($r+1) == $row_count){
                    $contents .= ");\n\n";
					$contents =  str_replace(", ()","",$contents);
					$contents =  str_replace("'0.00'","0.00",$contents);
					$contents =  str_replace(", ,",",NULL,",$contents);
					
				}else
                $r++;
            }
        }
		$contents .= "UNLOCK TABLES;\n\n";
    }
   	//var_dump($contents);
	//die();
	
    
	
	  // VERIFICAMOS CANTOS BACKUP HAY 
		$creaBU = false; //la usaremos como sémaforo para saber si pasó sin problemas por las diferentes validaciones.
		$files 	= glob('C:\/Inetpub\/vhosts\/ministerioriosdevida.org\/sqldump\/' . '*.sql'); //busca todos los ficheros que sean .sql
		
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
				//áun no se llega al limite de respaldos
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

			$backup_file_name = $db_to_backup."-".date( "dmY").".sql";

			$fp = fopen($db_backup_path."/".$backup_file_name ,'w+');

			$dump="";
			if (($result = fwrite($fp, $contents))) {
				$dump = "Backup file created '$backup_file_name' ($result)<br>";
			}
			fclose($fp);

			echo $dump;

		endif;
	 

?>
