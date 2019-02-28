<?php

require '_header.php';
use App\Services\Salesforce\SalesforceApi;


$salesforceApi = new SalesforceApi();


$details = $salesforceApi->getContact('a0bb000000bcVzrAAE', '001b000003OCdKHAA1');


var_dump($details);

die('dead');

$accountId = $_GET['account_id'];


$account = $salesforceApi->getAccount($accountId);

print_r($account);
die();

?>

<h1 class="title"><?php echo $supplier->Organisation_Name__c ?></h1>

<h3 class="subtitle is-4">Contact Name: <?php echo $supplier->Contact_Name__c ?></h3>
<h3 class=" is-4">Category: <?php echo $supplier->Categorisation__c ?></h3>

<h3 class=" is-4">Email: <?php echo $supplier->Email__c ?></h3>
<h3 class=" is-4">Phone: <?php echo $supplier->Phone__c ?></h3>

<hr>
<ul>
<?php
echo  '<li><span style="width: 190px; display: inline-block">Name:</span>' . $supplier->Name . '</li>';
echo  '<li><span style="width: 190px; display: inline-block">Contact Type:</span>' . $supplier->Contact_Type__c . '</li>';
echo  '<li><span style="width: 190px; display: inline-block">Address 1:</span>' . $supplier->Address1__c . '</li>';
echo  '<li><span style="width: 190px; display: inline-block">Address 2:</span>' . $supplier->Address2__c . '</li>';
echo  '<li><span style="width: 190px; display: inline-block">Address 3:</span>' . $supplier->Address3__c . '</li>';
echo  '<li><span style="width: 190px; display: inline-block">Website Contact:</span>' . $supplier->Website_Contact__c . '</li>';


?>
</ul>


<?php

require '_footer.php';

?>