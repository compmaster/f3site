<?php
if(iCMSa!=1 || !admit('E')) exit;
db_q('DROP TABLE '.$db_pre.'f3pzd');
db_q('DELETE FROM '.$db_pre.'plugins WHERE ID="pozdrowka"');
db_q('DELETE FROM '.$db_pre.'admmenu WHERE ID="f3pzd"');
db_q('DELETE FROM '.$db_pre.'menu WHERE value="plugins/pozdrowka/m.php"');
?>
