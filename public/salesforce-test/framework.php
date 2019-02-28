<?php

require '_header.php';

use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;

$frameworkId = $_GET['framework_id'];

$frameworkRepository = new FrameworkRepository();
$framework = $frameworkRepository->findById($frameworkId, 'salesforce_id');

$lotRepository = new LotRepository();
$lots = $lotRepository->findAllById($frameworkId, 'framework_id');

?>

<h1 class="title">Framework title: <?php echo $framework->getTitle(); ?></h1>
<h2 class="subtitle">Framework ID: <?php echo $framework->getRmNumber(); ?></h2>

<h3><b>Terms:</b> <?php echo $framework->getTerms() ?></h3>
<h3><b>Pillar:</b> <?php echo $framework->getPillar() ?></h3>

<h3><b>Category:</b> <?php echo $framework->getCategory() ?></h3>

<h3><b>Status:</b> <?php echo $framework->getStatus() ?></h3>

<h3><b>Framework start date:</b> <?php echo !empty($framework->getStartDate()) ? $framework->getStartDate()->format('d/m/Y') : ''?></h3>
<h3><b>Framework end date:</b> <?php echo !empty($framework->getEndDate()) ? $framework->getEndDate()->format('d/m/Y') : '' ?></h3>

<h3><b>Tenders open date:</b> <?php echo !empty($framework->getTendersOpenDate()) ? $framework->getTendersOpenDate()->format('d/m/Y') : '' ?></h3>
<h3><b>Tenders close date:</b> <?php echo !empty($framework->getTendersCloseDate()) ? $framework->getTendersCloseDate()->format('d/m/Y') : '' ?></h3>

<h3><b>Expected live date:</b> <?php echo !empty($framework->getExpectedLiveDate()) ? $framework->getExpectedLiveDate()->format('d/m/Y') : '' ?></h3>
<h3><b>Expected award date:</b> <?php echo !empty($framework->getExpectedAwardDate()) ? $framework->getExpectedAwardDate()->format('d/m/Y') : '' ?></h3>



<hr>
<h2 class="subtitle is-4">Lots</h2>


<ul>
<?php
foreach ($lots as $lot)
{
    echo '<li>
<a href="lot.php?lot_id=' . $lot->getSalesforceId() . '" style="text-decoration: underline;">
<span style="width: 190px; display: inline-block">' . $lot->getSalesforceId() . '</span>' .
    $lot->getTitle() . '</a>';
}
?>
</ul>



<?php

require '_footer.php';

?>
