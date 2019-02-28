<?php

require '_header.php';

use App\Repository\LotRepository;
use App\Repository\SupplierRepository;

$lotId = $_GET['lot_id'];

$lotRepository = new LotRepository();
$lot = $lotRepository->findById($lotId, 'salesforce_id');

$supplierRepository = new SupplierRepository();
//$suppliers = $supplierRepository->findById()

?>

<h1 class="title"><b>Name:</b> <?php echo $lot->getTitle() ?></h1>

<h3><b>ID:</b> <?php echo $lot->getId() ?></h3>
<h3><b>Framework ID:</b> <?php echo $lot->getFrameworkId() ?></h3>
<h3><b>Wordpress ID:</b> <?php echo $lot->getWordpressId() ?></h3>
<h3><b>Salesforce ID:</b> <?php echo $lot->getSalesforceId() ?></h3>
<h3><b>Lot number:</b> <?php echo $lot->getLotNumber() ?></h3>
<h3><b>Lot status:</b> <?php echo $lot->getStatus() ?></h3>
<h3><b>Expiry date:</b> <?php echo !empty($lot->getExpiryDate()) ? $lot->getExpiryDate()->format('d/m/Y') : '' ?></h3>
<h3><b>Should we hide suppliers?:</b> <?php echo $lot->isHideSuppliers() ?></h3>



<hr>
<h3 class="subtitle is-4">Suppliers</h3>

<ul>
<?php
foreach ($suppliers as $supplier)
{
    echo  '<li><a href="supplier.php?account_id=' . $supplier->getSalesforceId() . '" style="text-decoration: underline;"><span style="width: 190px; display: inline-block">' . $supplier->getSalesforceId() . '</span>' . $supplier->getName() . '</a></li>';
}
?>
</ul>

<?php

require '_footer.php';

?>
