# php-mysql-backup
Realiza BackUP de Base Datos MySQL Usando PHP CRON

Autor: CaribeHost | Cloud Service<br>
Website: https://www.caribehost.co<br>
Desarroladores : Galy Ricardo Cerda | Freddy Cohen Arbelaez<br>
Email: soporte@caribehost.co<br>

Este Scrpt PHP permite programar un CRON para realizar BackUP de Base Datos MySQL a un repositorio local, asi mimso puedes controlar cuantos archivos quieres tener en tu repositorio.
<hr>

<h3>COMO USARLO</h3>
este es modelo de como programar el cron con los argumentos (source_db_host=localhost source_db_user=root source_db_pass=12345 source_db_name=bd_test)
<h5>Ejemplo</h5>
*/15 * * * * /path-source/mysqli-dump.php source_db_host=localhost source_db_user=root source_db_pass=12345 source_db_name=bd_test
<br>	
<h5>Establezca la ruta de su repositorio</h5>
$db_backup_path = ''; //ESCRIBA LA RUTA DE LA CARPETA DONDE SE ALMACENARA EL BACKUP (IMPORTANTE ESTA TENGA PERMISOS DE ESCRITURA)

