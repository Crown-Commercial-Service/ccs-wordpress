<?php

namespace CCS\MDMImport;

// Mock functions in the namespace of the class under test
if (!function_exists('CCS\MDMImport\wp_insert_post')) {
    function wp_insert_post($args) {
        return 12345;
    }
}

if (!function_exists('CCS\MDMImport\is_wp_error')) {
    function is_wp_error($thing) {
        return false; // Default to success for tests
    }
}

if (!function_exists('CCS\MDMImport\update_field')) {
    function update_field($selector, $value, $post_id) {
        return true;
    }
}

namespace CCS\MDMImport\Tests;

use CCS\MDMImport\Import;
use App\Model\Framework;
use App\Model\Lot;
use Mockery;
use ReflectionClass;
use PHPUnit\Framework\TestCase;

// Define WP_CLI mocks if not present
if (!class_exists('\WP_CLI')) {
    class WP_CLI_Mock_Test {
        public static function add_command($name, $class) {}
        public static function success($msg) {}
        public static function error($msg, $exit = true) {}
        public static function line($msg) {}
    }
    class_alias(WP_CLI_Mock_Test::class, '\WP_CLI');
}

if (!class_exists('\WP_CLI_Command')) {
    class WP_CLI_Command_Mock_Test {}
    class_alias(WP_CLI_Command_Mock_Test::class, '\WP_CLI_Command');
}

// Require the class file. This file also loads vendor/autoload.php
require_once __DIR__ . '/cli-commands.php';

class ImportTest extends TestCase
{
    protected $import;
    protected $mdmApiMock;
    protected $frameworkRepoMock;
    protected $lotRepoMock;
    protected $lotSupplierRepoMock;
    protected $supplierRepoMock;
    protected $dbManagerMock;
    protected $syncTextMock;
    protected $loggerMock;
    protected $frameworkSearchClientMock;
    protected $supplierSearchClientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mdmApiMock = Mockery::mock('App\Services\MDM\MdmApi');
        $this->frameworkRepoMock = Mockery::mock('App\Repository\FrameworkRepository');
        $this->lotRepoMock = Mockery::mock('App\Repository\LotRepository');
        $this->lotSupplierRepoMock = Mockery::mock('App\Repository\LotSupplierRepository');
        $this->supplierRepoMock = Mockery::mock('App\Repository\SupplierRepository');
        $this->dbManagerMock = Mockery::mock('CCS\MDMImport\dbManager');
        $this->syncTextMock = Mockery::mock('CCS\MDMImport\SyncText');
        $this->loggerMock = Mockery::mock('App\Services\Logger\ImportLogger');
        $this->frameworkSearchClientMock = Mockery::mock('App\Search\FrameworkSearchClient');
        $this->supplierSearchClientMock = Mockery::mock('App\Search\SupplierSearchClient');

        // Instantiate Import without constructor to avoid real dependencies
        $reflection = new ReflectionClass(Import::class);
        $this->import = $reflection->newInstanceWithoutConstructor();

        // Inject mocks
        $this->setProtectedProperty('mdmApi', $this->mdmApiMock);
        $this->setProtectedProperty('frameworkRepository', $this->frameworkRepoMock);
        $this->setProtectedProperty('lotRepository', $this->lotRepoMock);
        $this->setProtectedProperty('lotSupplierRepository', $this->lotSupplierRepoMock);
        $this->setProtectedProperty('supplierRepository', $this->supplierRepoMock);
        $this->setProtectedProperty('dbManager', $this->dbManagerMock);
        $this->setProtectedProperty('syncText', $this->syncTextMock);
        $this->setProtectedProperty('logger', $this->loggerMock);
        $this->setProtectedProperty('frameworkSearchClient', $this->frameworkSearchClientMock);
        $this->setProtectedProperty('supplierSearchClient', $this->supplierSearchClientMock);
        
        $this->setProtectedProperty('importCount', ['frameworks' => 0, 'lots' => 0, 'suppliers' => 0]);
        $this->setProtectedProperty('errorCount', ['frameworks' => 0, 'lots' => 0, 'suppliers' => 0]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function setProtectedProperty($property, $value)
    {
        $reflection = new ReflectionClass($this->import);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->import, $value);
    }

    public function testImportSingleSuccess()
{
    $rmNumber = 'RM1001';
    $sfId = 'sf-framework-1';
    $lotSfId = 'sf-lot-1';

    // 1. SyncText Mocking
    $this->syncTextMock->shouldReceive('getFrameworksFromWordPress')->once()->andReturn([]);
    $this->syncTextMock->shouldReceive('getLotsFromWordPress')->once()->andReturn([]);

    // 2. Framework Mocking
    $framework = Mockery::mock(Framework::class);
    $framework->shouldReceive('getSalesforceId')->andReturn($sfId);
    $framework->shouldReceive('getTitle')->andReturn('Test Framework');
    $framework->shouldReceive('getWordpressId')->andReturn(null);
    $framework->shouldReceive('setWordpressId')->with(12345);

    $this->mdmApiMock->shouldReceive('getAgreement')->with($rmNumber)->once()->andReturn($framework);

    $this->frameworkRepoMock->shouldReceive('createOrUpdateExcludingWordpressFields')
        ->with('salesforce_id', $sfId, $framework)->once();
    
    $this->frameworkRepoMock->shouldReceive('findById')
        ->with($sfId, 'salesforce_id')->once()->andReturn($framework);
    
    $this->frameworkRepoMock->shouldReceive('update')
        ->with('salesforce_id', $sfId, $framework, true)->once();

    // 3. Lot Mocking
    $lot = Mockery::mock(Lot::class);
    $lot->shouldReceive('getSalesforceId')->andReturn($lotSfId);
    $lot->shouldReceive('getTitle')->andReturn('Test Lot');
    $lot->shouldReceive('getWordpressId')->andReturn(null);
    
    // Expect two calls - first one with null from DB, second with 12345 from WP creation
    $lot->shouldReceive('setWordpressId')->with(null)->once();
    $lot->shouldReceive('setWordpressId')->with(12345)->once();
    
    $lot->shouldReceive('isHideSuppliers')->andReturn(false);

    $lots = [$lot];
    $this->mdmApiMock->shouldReceive('getAgreementLots')->with($sfId)->once()->andReturn($lots);

    $this->dbManagerMock->shouldReceive('getLotSalesforceIdByFrameworkId')->with($sfId)->andReturn([]);
    $this->dbManagerMock->shouldReceive('getLotWordpressIdBySalesforceId')->with($lotSfId)->andReturn(null);
    
    $this->lotRepoMock->shouldReceive('createOrUpdateExcludingWordpressFields')
        ->with('salesforce_id', $lotSfId, $lot)->once();
        
    $this->lotRepoMock->shouldReceive('findById')
        ->with($lotSfId, 'salesforce_id')->andReturn($lot);
        
    $this->lotRepoMock->shouldReceive('update')
        ->with('salesforce_id', $lotSfId, $lot, true)->once();

    // 4. Other dependencies
    $this->mdmApiMock->shouldReceive('getLotSuppliers')->with($lotSfId)->andReturn([]);
    $this->dbManagerMock->shouldReceive('getLotSuppliersSalesforceIdByLotId')->with($lotSfId)->andReturn([]);
    $this->supplierRepoMock->shouldReceive('findAll')->andReturn([]);
    $this->dbManagerMock->shouldReceive('updateFrameworkTitleInWordpress')->once();
    $this->dbManagerMock->shouldReceive('updateLotTitleInWordpress')->once();
    
    $this->frameworkRepoMock->shouldReceive('printImportCount');
    $this->lotRepoMock->shouldReceive('printImportCount');
    $this->lotSupplierRepoMock->shouldReceive('printImportCount');

    // FIX: Using try/finally ensures the output buffer is cleaned up even if assertions fail
    ob_start();
    try {
        $this->import->importSingle([$rmNumber]);
    } finally {
        ob_end_clean();
    }
    
    $this->assertTrue(true);
}

    public function testCheckAndDeleteLots(): void
    {
        // 1. Define local variables to fix the "Undefined variable" warning
        $rmNumber = 'RM1001';
        $frameworkId = 'sf-f-1';
        $lotIdToDelete = 'lot-2';

        // 2. Setup the Framework Mock
        $framework = Mockery::mock(Framework::class);
        $framework->shouldReceive('getSalesforceId')->andReturn($frameworkId);
        $framework->shouldReceive('getRmNumber')->andReturn($rmNumber);

        // 3. Setup Logger Expectation (Matches the new PHP 8 string format)
        $this->loggerMock->shouldReceive('info')
            ->with("Deleting lot: $lotIdToDelete from $rmNumber")
            ->once();

        // 4. Setup DB Manager Mock
        $this->dbManagerMock->shouldReceive('getLotSalesforceIdByFrameworkId')
            ->with($frameworkId)
            ->andReturn([$lotIdToDelete]);

        $this->dbManagerMock->shouldReceive('getLotWordpressIdBySalesforceId')
            ->with($lotIdToDelete)
            ->andReturn(99);

        $this->dbManagerMock->shouldReceive('deleteLotPostInWordpress')
            ->with(99)
            ->once();

        // 5. Setup Lot Repository Mock 
        // CHECK THIS NAME: Ensure it matches the property in your setUp() method
        $this->lotRepoMock->shouldReceive('delete')
            ->with($lotIdToDelete)
            ->once();

        // 6. Execute the method under test
        $this->import->checkAndDeleteLots([], $framework);

        $this->assertTrue(true);
    }

    public function testCheckAndDeleteSuppliers()
    {
        // Access private method via Reflection
        $method = new \ReflectionMethod(Import::class, 'checkAndDeleteSuppliers');
        $method->setAccessible(true);

        $lotId = 'sf-lot-1';

        // Mock Suppliers from API (only supp-1 exists remotely)
        $supplier1 = Mockery::mock(\App\Model\Supplier::class);
        $supplier1->shouldReceive('getSalesforceId')->andReturn('supp-1');
        $suppliersFromApi = [$supplier1];

        // Mock DB returning supp-1 and supp-2 (supp-2 needs deletion)
        $this->dbManagerMock->shouldReceive('getLotSuppliersSalesforceIdByLotId')
            ->with($lotId)
            ->once()
            ->andReturn(['supp-1', 'supp-2']);

        // Expectations for deletion of supp-2
        $this->lotSupplierRepoMock->shouldReceive('deleteByLotIdAndSupplierId')
            ->with($lotId, 'supp-2')
            ->once();

        // Execute
        $method->invoke($this->import, $suppliersFromApi, $lotId);
        
        $this->assertTrue(true);
    }

    public function testImportSingleWithExistingWordPressIds()
    {
        $rmNumber = 'RM999';
        $sfId = 'sf-existing-1';
        $wpId = 555;

        // Framework Setup
        $framework = Mockery::mock(Framework::class);
        $framework->shouldReceive('getSalesforceId')->andReturn($sfId);
        $framework->shouldReceive('getWordpressId')->andReturn($wpId);
        $framework->shouldReceive('getTitle')->andReturn('Existing Framework');

        $this->mdmApiMock->shouldReceive('getAgreement')->with($rmNumber)->andReturn($framework);
        $this->syncTextMock->shouldReceive('getFrameworksFromWordPress')->andReturn([]);
        $this->syncTextMock->shouldReceive('getLotsFromWordPress')->andReturn([]);

        $this->frameworkRepoMock->shouldReceive('createOrUpdateExcludingWordpressFields')->once();
        $this->frameworkRepoMock->shouldReceive('findById')->andReturn($framework);
        
        // API logic
        $this->mdmApiMock->shouldReceive('getAgreementLots')->andReturn([]);
        $this->dbManagerMock->shouldReceive('getLotSalesforceIdByFrameworkId')->andReturn([]);
        $this->supplierRepoMock->shouldReceive('findAll')->andReturn([]);
        
        // WordPress Updates
        $this->dbManagerMock->shouldReceive('updateFrameworkTitleInWordpress')->once();
        $this->dbManagerMock->shouldReceive('updateLotTitleInWordpress')->once();

        // MISSING EXPECTATIONS: printSummary() calls these
        $this->frameworkRepoMock->shouldReceive('printImportCount')->once();
        $this->lotRepoMock->shouldReceive('printImportCount')->once();
        $this->lotSupplierRepoMock->shouldReceive('printImportCount')->once();

        // Use try/finally to close the buffer
        ob_start();
        try {
            $this->import->importSingle([$rmNumber]);
        } finally {
            ob_end_clean();
        }

        $this->assertTrue(true);
    }
    
    public function testImportLotWithHiddenSuppliers()
    {
        $rmNumber = 'RM1234';
        $sfId = 'sf-framework-1';
        $lotSfId = 'lot-hidden-1';

        // 1. Basic Setup for Framework
        $this->syncTextMock->shouldReceive('getFrameworksFromWordPress')->andReturn([]);
        $this->syncTextMock->shouldReceive('getLotsFromWordPress')->andReturn([]);

        $framework = Mockery::mock(Framework::class);
        $framework->shouldReceive('getSalesforceId')->andReturn($sfId);
        $framework->shouldReceive('getWordpressId')->andReturn(100);
        $framework->shouldReceive('getTitle')->andReturn('Test Framework');
        
        $this->mdmApiMock->shouldReceive('getAgreement')->with($rmNumber)->andReturn($framework);
        $this->frameworkRepoMock->shouldReceive('createOrUpdateExcludingWordpressFields')->once();
        $this->frameworkRepoMock->shouldReceive('findById')->andReturn($framework);

        // 2. Setup the Hidden Lot
        $lot = Mockery::mock(Lot::class);
        $lot->shouldReceive('getSalesforceId')->andReturn($lotSfId);
        $lot->shouldReceive('getWordpressId')->andReturn(200);
        $lot->shouldReceive('getTitle')->andReturn('Hidden Lot');
        $lot->shouldReceive('isHideSuppliers')->andReturn(true); // Trigger the deletion logic
        // Add these to satisfy the update/find calls in the loop
        $lot->shouldReceive('setWordpressId'); 
        
        // Crucial: The API must return this lot for the loop to run
        $this->mdmApiMock->shouldReceive('getAgreementLots')->with($sfId)->andReturn([$lot]);
        
        // 3. Repository and DB Expectations
        $this->dbManagerMock->shouldReceive('getLotSalesforceIdByFrameworkId')->andReturn([]);
        $this->dbManagerMock->shouldReceive('getLotWordpressIdBySalesforceId')->andReturn(200);
        $this->lotRepoMock->shouldReceive('createOrUpdateExcludingWordpressFields')->once();
        $this->lotRepoMock->shouldReceive('findById')->andReturn($lot);

        // This is the call that was previously failing (called 0 times)
        $this->lotSupplierRepoMock->shouldReceive('deleteById')
            ->with($lotSfId, 'lot_id')
            ->once();

        // 4. Finalizing expectations (Summary and Cleanup)
        $this->supplierRepoMock->shouldReceive('findAll')->andReturn([]);
        $this->dbManagerMock->shouldReceive('updateFrameworkTitleInWordpress')->once();
        $this->dbManagerMock->shouldReceive('updateLotTitleInWordpress')->once();
        $this->frameworkRepoMock->shouldReceive('printImportCount')->once();
        $this->lotRepoMock->shouldReceive('printImportCount')->once();
        $this->lotSupplierRepoMock->shouldReceive('printImportCount')->once();

        ob_start();
        try {
            $this->import->importSingle([$rmNumber]);
        } finally {
            ob_end_clean();
        }

        $this->assertTrue(true);
    }

    public function testImportSingleHandlesApiFailure()
    {
        $rmNumber = 'RM-FAIL';
        
        $this->syncTextMock->shouldReceive('getFrameworksFromWordPress')->andReturn([]);
        $this->syncTextMock->shouldReceive('getLotsFromWordPress')->andReturn([]);

        // Simulate API throwing an exception
        $this->mdmApiMock->shouldReceive('getAgreement')
            ->with($rmNumber)
            ->andThrow(new \Exception("API Timeout"));

        // Expect the logger to receive the error message
        $this->loggerMock->shouldReceive('error')
            ->with(Mockery::pattern('/Something went wrong while importing RM-FAIL/'))
            ->once();

        ob_start();
        $this->import->importSingle([$rmNumber]);
        ob_end_clean();

        $this->assertTrue(true);
    }

    public function testImportSingleFailsOnInvalidFrameworkData()
    {
        $rmNumber = 'RM_INVALID';
        
        // Mock API returning null
        $this->mdmApiMock->shouldReceive('getAgreement')->with($rmNumber)->andReturn(null);
        
        // Mock logger to expect the error message
        $this->loggerMock->shouldReceive('error')
            ->with(Mockery::pattern('/Framework data for RM_INVALID is invalid/'))
            ->once();

        $this->syncTextMock->shouldReceive('getFrameworksFromWordPress')->andReturn([]);
        $this->syncTextMock->shouldReceive('getLotsFromWordPress')->andReturn([]);

        ob_start();
        $this->import->importSingle([$rmNumber]);
        ob_end_clean();

        $this->assertTrue(true);
    }
}