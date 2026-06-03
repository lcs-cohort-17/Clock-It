<?php

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverSelect;
use Symfony\Component\Panther\PantherTestCase;

class AdminLeaveRequestsTest extends PantherTestCase
{
    private static $serverUri;

    public static function setUpBeforeClass(): void
    {
        self::$serverUri = self::startWebServer(__DIR__ . '/../../public');
    }

    private function openAdminLeaveRequests($client)
    {
        $crawler = $client->request('GET', self::$serverUri . '/admin-leave-requests');
        $client->waitForVisibility('table tbody tr');
        return $crawler;
    }

    /**
     * @test
     * Criteria: Admin can see all leave requests from all employees
     */
    public function admin_can_see_all_leave_requests()
    {
        $client = static::createPantherClient([], self::$serverUri);
        $this->openAdminLeaveRequests($client);

        $rows = $client->getCrawler()->filter('table tbody tr');
        $this->assertCount(4, $rows, 'Should display all 4 default mock leave requests.');
    }

    /**
     * @test
     * Criteria: Admin can search/filter by employee name
     */
    public function admin_can_search_by_employee_name()
    {
        $client = static::createPantherClient([], self::$serverUri);
        $this->openAdminLeaveRequests($client);

        $searchInput = $client->findElement(WebDriverBy::cssSelector('input[placeholder="Search by employee name..."]'));
        $searchInput->sendKeys('Sarah');

        $client->waitForVisibility('table tbody tr');
        $rows = $client->getCrawler()->filter('table tbody tr');

        $this->assertCount(1, $rows);
        $this->assertStringContainsString('Sarah Jenkins', $rows->first()->text());
    }

    /**
     * @test
     * Criteria: Admin can filter by status (Pending / Approved / Rejected)
     */
    public function admin_can_filter_by_status()
    {
        $client = static::createPantherClient([], self::$serverUri);
        $this->openAdminLeaveRequests($client);

        $select = new WebDriverSelect($client->findElement(WebDriverBy::cssSelector('select')));
        $select->selectByVisibleText('Approved');

        $client->waitForVisibility('div.text-center h5');
        $this->assertStringContainsString('No leave requests found', $client->getCrawler()->filter('div.text-center h5')->text());
    }

    /**
     * @test
     * Criteria: Confirmation modal prevents accidental actions
     * Criteria: Admin can approve a pending request
     * Criteria: After approve/reject, status badge updates and request disappears from pending filter
     */
    public function admin_can_approve_a_pending_request_through_confirmation_modal()
    {
        $client = static::createPantherClient([], self::$serverUri);
        $this->openAdminLeaveRequests($client);

        $client->findElement(WebDriverBy::cssSelector('table tbody tr .btn-success'))->click();

        $client->waitForVisibility('#confirmationModal');
        $this->assertStringContainsString('Confirm Approval', $client->getCrawler()->filter('#confirmationModal')->text());
        $this->assertStringContainsString('Sarah Jenkins', $client->getCrawler()->filter('#confirmationModal')->text());

        $client->findElement(WebDriverBy::cssSelector('#confirmationModal .btn-success'))->click();
        $client->waitForInvisibility('#confirmationModal');
        $client->waitForVisibility('table tbody tr');

        $select = new WebDriverSelect($client->findElement(WebDriverBy::cssSelector('select')));
        $select->selectByVisibleText('Approved');
        $client->waitForVisibility('table tbody tr');

        $this->assertStringContainsString('Sarah Jenkins', $client->getCrawler()->filter('table tbody')->text());
        $this->assertStringContainsString('Approved', $client->getCrawler()->filter('table tbody .badge')->first()->text());

        $select->selectByVisibleText('Pending');
        $client->waitForVisibility('table tbody tr');
        $this->assertStringNotContainsString('Sarah Jenkins', $client->getCrawler()->filter('table tbody')->text());
    }

    /**
     * @test
     * Criteria: Admin can reject a pending request
     */
    public function admin_can_reject_a_pending_request()
    {
        $client = static::createPantherClient([], self::$serverUri);
        $this->openAdminLeaveRequests($client);

        $client->findElement(WebDriverBy::cssSelector('table tbody tr .btn-outline-danger'))->click();

        $client->waitForVisibility('#confirmationModal');
        $client->findElement(WebDriverBy::cssSelector('#confirmationModal .btn-danger'))->click();
        $client->waitForInvisibility('#confirmationModal');
        $client->waitForVisibility('table tbody tr');

        $select = new WebDriverSelect($client->findElement(WebDriverBy::cssSelector('select')));
        $select->selectByVisibleText('Rejected');
        $client->waitForVisibility('table tbody tr');

        $this->assertStringContainsString('Rejected', $client->getCrawler()->filter('table tbody .badge')->first()->text());

        $select->selectByVisibleText('Pending');
        $client->waitForVisibility('table tbody tr');
        $this->assertStringNotContainsString('Sarah Jenkins', $client->getCrawler()->filter('table tbody')->text());
    }

    /**
     * @test
     * Criteria: The table is responsive on mobile, laptop and Monitor.
     */
    public function page_renders_correctly_on_different_breakpoints()
    {
        $client = static::createPantherClient([], self::$serverUri);
        
        // Test Mobile Breakpoint
        $client->getWebDriver()->manage()->window()->setSize(new WebDriverDimension(375, 812));
        $this->openAdminLeaveRequests($client);
        $this->assertGreaterThan(0, $client->getCrawler()->filter('.table-responsive')->count());

        // Test Monitor Breakpoint
        $client->getWebDriver()->manage()->window()->setSize(new WebDriverDimension(1920, 1080));
        $this->openAdminLeaveRequests($client);
        $this->assertGreaterThan(0, $client->getCrawler()->filter('.container-fluid')->count());
    }
}
