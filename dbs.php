<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user = 'root';
$pass = '';
$host = 'localhost';

$db_backup_folder = "db_backup";
$dir = dirname(__FILE__);

$link = mysqli_connect($host, $user, $pass);
if (!$link) {
    die('Not connected : ' . mysql_error());
}

if (!$db_list = mysqli_query($link, "SHOW DATABASES"))
    printf("Error: %s\n", mysqli_error($link));

$db_list_filtered = array();
$filter = array("information_schema", "mysql", "performance_schema");
while ($row = mysqli_fetch_row($db_list))
    if (!in_array($row[0], $filter)){
        $sql="SELECT table_schema 'db_name', 
            SUM( data_length + index_length) / (1024 * 1024) 'db_size_in_mb' 
            FROM information_schema.TABLES 
            WHERE table_schema='$row[0]' GROUP BY table_schema ;";
        $query=mysqli_query($link, $sql);
        $data=mysqli_fetch_row($query); 
        $db_list_filtered[$row[0]] = $data[1];
    }

if (isset($_GET['dump']) && array_key_exists($_GET['dump'], $db_list_filtered)){
    $mysqldump=exec('which mysqldump');
    $dbfilename = $_GET['dump'];
    $now = new DateTime();
    $dbfilepath = $db_backup_folder.'/'.$dbfilename.$now->format('_Y-m-d_H-i-s').'.sql';
    $command = "mysqldump --user={$user} --password={$pass} --host={$host} {$dbfilename} --result-file={$dbfilepath}";
    exec($command);
}

if (!file_exists($db_backup_folder)) {
    mkdir($db_backup_folder, 0777, true);
}

$files = new FilesystemIterator($db_backup_folder);

?>

<!doctype html>

<html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
    
      <title>Simple PHP MySQL dump script</title>
      <meta name="description" content="Easy way to dump ( backup ) a MySQL database with PHP script">
      <meta name="author" content="Thavarasa Prasanth">
    
      <meta property="og:title" content="Simple PHP MySQL dump script">
      <meta property="og:type" content="script">
      <meta property="og:description" content="Easy way to dump ( backup ) a MySQL database with PHP script">
    
      <style>
          .check-list {
              margin: 0;
              padding-left: 1.2rem;
          }
          .check-list li {
              position: relative;
              list-style-type: none;
              padding-left: 2.5rem;
              margin-bottom: 0.5rem;
          }
          :root{
            --pseudo-display: none;
            }
          .check-list li:before {
              content:'';
              display: var(--pseudo-display);
              position: absolute;
              left: 0;
              top: 4px;
              border: 4px solid #f3f3f3;
              border-radius: 50%;
              border-top: 4px solid #030303;
              width: 10px;
              height: 10px;
              animation: spin 2s linear infinite;
          }
          /* Boilerplate stuff */
          *, *:before, *:after {
              box-sizing: border-box;
          }
          html {
              -webkit-font-smoothing: antialiased;
              font-family:"Helvetica Neue", sans-serif;
              font-size: 62.5%;
          }
          body {
              font-size: 1.6rem;
              /* 18px */
              background-color: #efefef;
              color: #324047
          }
          html, body, section {
              height: 100%;
          }
          section {
              margin-left: auto;
              margin-right: auto;
              display: flex;
              align-items: center;
          }
          div {
              margin: auto;
          }
            
            @keyframes spin {
              0% { transform: rotate(0deg); }
              100% { transform: rotate(360deg); }
            }
      </style>
    
    </head>
    
    <body>
        <section>
            <div>
                <h2>Database Lists</h2>
                <ul class="check-list">
                    <?php if(!$db_list_filtered) echo '<ol>No database found!</ol>' ?>
                    <?php foreach ($db_list_filtered as $key => $value) echo '<li onclick="MysqlDump(\''.$key.'\')">'.$key.' : '.number_format($value, 2).' MB </li>' ?>
                </ul>
            </div>
            <div>
                <h2>Backup Lists</h2>
                <ul class="check-list">
                    <?php if(!iterator_count($files)) echo '<ol>No backup found!</ol>' ?>
                    <?php foreach($files as $file) echo '<li><a href="'.$db_backup_folder.'/'.$file->getFilename().'" download>'.$file->getFilename().'</a> : '.number_format($file->getSize()/1024/1024, 2).' MB'.' : '.date('m/d/Y H:i:s', $file->getMTime()).'</li>' ?>
                </ul>
            </div>
        </section>
    </body>
    <script>
        function MysqlDump(dbName) {
            const root = document.querySelector(":root");
            root.style.setProperty("--pseudo-display", 'block');

            fetch("?dump="+dbName)
                .then(response => {
                if (response.status !== 200) {
                    console.log('Looks like there was a problem. Status Code: ' +
                        response.status);
                    return;
                }
                return response.status;
            })
                .then(res => {
                
                window.location.reload();
            })
                .
            catch (err => {
                console.log('Fetch Error :-S', err);
            });
        }
    </script>
</html>