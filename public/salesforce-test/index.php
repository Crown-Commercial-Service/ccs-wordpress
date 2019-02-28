<?php

require '_header.php';

use App\Repository\FrameworkRepository;

$frameworkRepository = new FrameworkRepository();
$frameworks = $frameworkRepository->findAll();

echo '<h1 class="title">Frameworks</h1>';
echo '<h2 class="subtitle">' . count($frameworks) . ' records found</h2>';

echo '<ul>';

foreach ($frameworks as $framework)
{
    echo  '<li><span style="width: 150px; display: inline-block">' . $framework->getRmNumber() . '</span> <a href="framework.php?framework_id=' . $framework->getSalesforceId() . '" style="text-decoration: underline;">' . $framework->getTitle() . '</a></li>';
}

echo '</ul>';


require '_footer.php';