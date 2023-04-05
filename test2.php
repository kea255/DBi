<?
error_reporting(E_ALL);
require_once 'dbi.class.php';

const dbhost = 'localhost';
const dbuser = 'root';
const dbpass = 'abmin';
const dbname = 'testz';

DBi::transaction();
DBi::query("UPDATE `rosreestr_zemly` SET cena=? WHERE id=?", rand(1,1000), 1);
DBi::query("UPDATE `rosreestr_zemly` SET cena=? WHERE id=?", rand(1,1000), 2);
DBi::commit();

//$rows = DBi::select('SELECT id, kn, cena FROM rosreestr_zemly LIMIT 100000');
/*
$timer = new ATimer\Timer(true);
foreach($rows as $row){
	DBi::query("UPDATE `rosreestr_zemly` SET cena=? WHERE id=?", rand(1,1000), $row['id']);
}
echo "\nsimple update: ".$timer->getDurationFormatted();
*/
//simple update: 2m 28.556s

/*
foreach([500, 1000] as $query_in_trans){
	$timer = new ATimer\Timer(true);
	$cnt = 0;
	DBi::transaction();
	foreach($rows as $row){
		$cnt++;
		if($cnt % $query_in_trans == 0){ DBi::commit(); DBi::transaction(); }
		DBi::query("UPDATE LOW_PRIORITY `rosreestr_zemly` SET cena=? WHERE id=?", rand(1,1000), $row['id']);
	}
	DBi::commit();
	echo "\ntransaction update batch=$query_in_trans: ".$timer->getDurationFormatted();
}
*/
/*
transaction update batch=100: 30.363s
transaction update batch=500: 29.092s
transaction update batch=1000: 30.167s
transaction update batch=10000: 28.139s
transaction update batch=50000: 28.26s
transaction update batch=100000: 30.382s
*/


/*
$timer = new ATimer\Timer(true);
DBi::query("create temporary table tmp(id bigint(20) primary key, cena double(10,2))");
foreach($rows as $row){
	DBi::query("insert into tmp values (?, ?)", $row['id'], rand(1,1000));
}
DBi::query("UPDATE rosreestr_zemly, tmp SET rosreestr_zemly.cena=tmp.cena WHERE rosreestr_zemly.id=tmp.id");
echo "\ntemp table update: ".$timer->getDurationFormatted();
//temp table update: 57.682s
*/

/*
$timer = new ATimer\Timer(true);
$update_arr = [];
foreach($rows as $row){
	$newcena = rand(1,5);
	$update_arr[$newcena][] = $row['id'];
}
foreach($update_arr as $cena=>$ids){
	DBi::query("UPDATE `rosreestr_zemly` SET cena=? WHERE id IN(?a)", $cena, $ids);
}
echo "\nIN update: ".$timer->getDurationFormatted();
//IN update: 1m 11.079s
*/

echo "\n\n";