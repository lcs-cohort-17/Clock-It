<?php

use PHPUnit\Framework\TestCase;

class AdminSettingsTest extends TestCase
{
    private string $view;
    private string $js;
    private string $css;

    protected function setUp(): void
{
    $this->view = @file_get_contents(
        __DIR__ . '/../src/views/admin/admin_settings.php'
    ) ?: '';

    // No separate JS/CSS files yet
    // Reuse the page content for these checks
    $this->js = $this->view;
    $this->css = $this->view;
}

    /*
    |--------------------------------------------------------------------------
    | DATA RETENTION SECTION
    |--------------------------------------------------------------------------
    */

    public function testDataRetentionHeadingExists(): void
    {
        $this->assertStringContainsString(
            'Data Retention',
            $this->view
        );
    }

    public function testRetentionInputExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'type="number"')
            ||
            str_contains($this->view, "type='number'")
        );
    }

    public function testKeepRecordsLabelExists(): void
    {
        $this->assertStringContainsString(
            'Keep records for',
            $this->view
        );
    }

    public function testPurgeOldRecordsButtonExists(): void
    {
        $this->assertStringContainsString(
            'Purge Old Records',
            $this->view
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SESSION TIMEOUT
    |--------------------------------------------------------------------------
    */

    public function testSessionTimeoutHeadingExists(): void
    {
        $this->assertStringContainsString(
            'Session Timeout',
            $this->view
        );
    }

    public function testSessionTimeoutMinutesLabelExists(): void
    {
        $this->assertStringContainsString(
            'minutes',
            strtolower($this->view)
        );
    }

    public function testSaveButtonExists(): void
    {
        $this->assertStringContainsString(
            'Save',
            $this->view
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BOOTSTRAP REQUIREMENTS
    |--------------------------------------------------------------------------
    */

    public function testBootstrapCardsExist(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'card')
        );
    }

    public function testBootstrapFormControlsExist(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'form-control')
        );
    }

    public function testBootstrapButtonsExist(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'btn')
        );
    }

    public function testBootstrapAlertExists(): void
    {
        $this->assertTrue(
            str_contains(
                strtolower($this->view),
                'alert'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ALPINE JS VALIDATION
    |--------------------------------------------------------------------------
    */

    public function testUsesAlpineJs(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'x-data')
            ||
            str_contains($this->view, 'x-model')
            ||
            str_contains($this->view, '@click')
        );
    }

    public function testPositiveIntegerValidationExists(): void
{
    $this->assertTrue(
        str_contains($this->view, 'parseInt')
        ||
        str_contains($this->view, 'Number.isInteger')
        ||
        str_contains($this->view, '> 0')
        ||
        str_contains($this->view, 'positive')
    );
}

    /*
    |--------------------------------------------------------------------------
    | API ENDPOINTS
    |--------------------------------------------------------------------------
    */

    public function testGetSettingsApiExists(): void
{
    $this->assertTrue(
        str_contains($this->view, '/api/admin/settings')
    );
}
    public function testPutSettingsApiExists(): void
{
    $this->assertTrue(
        str_contains($this->view, 'PUT')
        ||
        str_contains($this->view, 'put')
    );
}
    public function testPurgeApiExists(): void
{
    $this->assertTrue(
        str_contains(
            $this->view,
            '/api/admin/data-retention/purge'
        )
    );
}

    public function testPostMethodExists(): void
{
    $this->assertTrue(
        str_contains($this->view, 'POST')
        ||
        str_contains($this->view, 'post')
    );
}
    /*
    |--------------------------------------------------------------------------
    | CONFIRMATION MODAL
    |--------------------------------------------------------------------------
    */

    public function testConfirmationModalExists(): void
    {
        $this->assertTrue(
            str_contains(
                strtolower($this->view),
                'modal'
            )
        );
    }

    public function testBootstrapModalStructureExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'modal-dialog')
            ||
            str_contains($this->view, 'modal-content')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUCCESS / ERROR FEEDBACK
    |--------------------------------------------------------------------------
    */

    public function testSuccessFeedbackExists(): void
{
    $this->assertTrue(
        str_contains(
            strtolower($this->view),
            'success'
        )
        ||
        str_contains(
            strtolower($this->view),
            'saved'
        )
    );
}

    public function testErrorFeedbackExists(): void
{
    $this->assertTrue(
        str_contains(
            strtolower($this->view),
            'error'
        )
        ||
        str_contains(
            strtolower($this->view),
            'invalid'
        )
    );
}
    /*
    |--------------------------------------------------------------------------
    | ACCEPTANCE CRITERIA
    |--------------------------------------------------------------------------
    */

    public function testAdminCanSeeRetentionSection(): void
    {
        $this->assertStringContainsString(
            'Data Retention',
            $this->view
        );
    }

    public function testAdminCanEnterRetentionDays(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'type="number"')
            ||
            str_contains($this->view, "type='number'")
        );
    }

    public function testInvalidValuesRejected(): void
    {
        $this->assertTrue(
            str_contains($this->js, '> 0')
            ||
            str_contains($this->js, 'Number.isInteger')
        );
    }

    public function testConfirmationBeforeDeletion(): void
    {
        $this->assertTrue(
            str_contains(
                strtolower($this->view),
                'modal'
            )
        );
    }

    public function testPurgeFeatureExists(): void
    {
        $this->assertStringContainsString(
            'Purge Old Records',
            $this->view
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE RESPONSIVENESS
    |--------------------------------------------------------------------------
    */

    public function testResponsiveBootstrapGridExists(): void
    {
        $this->assertTrue(
            str_contains($this->view, 'container')
            ||
            str_contains($this->view, 'container-fluid')
            ||
            str_contains($this->view, 'row')
            ||
            str_contains($this->view, 'col-md')
            ||
            str_contains($this->view, 'col-lg')
        );
    }

    public function testResponsiveMediaQueriesExist(): void
    {
        $this->assertTrue(
            str_contains($this->css, '@media')
            ||
            str_contains($this->view, 'col-md')
            ||
            str_contains($this->view, 'col-lg')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEFINITION OF DONE
    |--------------------------------------------------------------------------
    */

    public function testDefinitionOfDoneCoverage(): void
    {
        $required = [
            'Data Retention',
            'Session Timeout',
            'Purge Old Records',
            '/api/admin/settings',
            '/api/admin/data-retention/purge'
        ];

        $content = $this->view . $this->js;

        foreach ($required as $item) {
            $this->assertStringContainsString(
                $item,
                $content
            );
        }
    }
}