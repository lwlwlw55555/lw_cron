 <?php
 require("includes/init.php");
 require("Models/ShopModel.php");
 require("Models/OrderModel.php");
 use Models\ShopModel;
 use Models\OrderModel;
 require("Services/ExpressApiService.php");
 use Services\ExpressApiService;
 include 'request/OrderNumberListGetRequest.php';
 include 'PddClient.php';

 $index = empty($argv[1])?2:$argv[1];
 if ($index < 2) {
     $index = 2;
 }

 global $oms_db;
 $oms_db_conf = array(
     "host" => "100.65.1.202:32001",
     "user" => "mddomsapi",
     "pass" => "123JoisnD0",
     "charset" => "utf8",
     "pconnect" => "1",
     "name" => "mddoms_".($index-1)
 );
 $oms_db = ClsPdo::getInstance($oms_db_conf);

 $oms_2_db_conf = array(
     "host" => "100.65.1.202:32001",
     "user" => "mddomsapi",
     "pass" => "123JoisnD0",
     "charset" => "utf8",
     "pconnect" => "1",
     "name" => "mddoms_".($index)
 );
 $oms_2_db = ClsPdo::getInstance($oms_2_db_conf);


 $tables = $oms_db->getAll("show tables");
  // var_dump($tables);
 foreach ($tables as $table) {
     foreach ($table as $v) {
         if (startwith($v,'back')) {
            continue;
         }
         if (startwith($v,'jwang')) {
            continue;
         }
         if(strpos($v, 'back') !== false){
            continue;
         }
          $sql = "show create table {$v}";
          $t = $oms_db->getAll($sql);
          // var_dump($t[0]['Create Table']);
          $sql = $t[0]['Create Table'];
          echo $sql.PHP_EOL;
          try{
            // $oms_2_db->query($sql);

            if ($v == 'shipment_exception_flag') {
               continue;
            }
           $sql = "SELECT auto_increment FROM information_schema.tables where table_schema='mddoms_".($index-1)."' and table_name='{$v}'";
            var_dump($sql);
           $num = $oms_2_db->getOne($sql);
            var_dump($num);
           // $inteval = 10000000;
           // if ($num/1000000 > 1) {
               $inteval = 1000000000;
               $sql = "alter table {$v} auto_increment =".($num+$inteval);
               var_dump($sql);
               $oms_2_db->query($sql);
           // }
         }catch(\Exception $e){

          }
     }
 } 

function startwith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}
