<?php
$servername = "localhost";
$username = "findagio_testing";	
$password = "Smadikizela1";
$dbname = "findagio_testing";
//$tblname ="scanner";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if($conn ->connect_error){
    die("Connetion failed: ".$conn->connect_error);
}

//select sw.name 'Social Worker Name',sw.surname 'Social Worker Surname',sw.email 'Social Worker Email',sw.phoneNo 'Social Worker Phone Number',
//v.COL9 'Victim Surname',v.COL10 'Victim Name',v.COL11 'Victim Gender',v.COL13 'Victim School' from socialworkers sw inner join assignedsocialworker asw on sw.id=asw.social_worker_id INNER JOIN victim2 v on asw.victim_id= v.victim_id


// select v.COL9 'Victim Surname',v.COL10 'Victim Name',v.COL11 'Victim Gender',v.COL13 'Victim School',sw.name 'Social Worker Name',sw.surname 'Social Worker Surname',sw.email 'Social Worker Email',sw.phoneNo 'Social Worker Phone Number'
// from victim2 v inner join assignedsocialworker asw on v.victim_id=asw.victim_id inner join socialworkers sw on asw.social_worker_id=sw.social_worker_id
?>
