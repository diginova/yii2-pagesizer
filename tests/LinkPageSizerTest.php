<?php

declare(strict_types=1);

namespace diginova\pagesizer\tests;

use diginova\pagesizer\LinkPageSizer;
use portalium\site\components\Setting;
use tests\support\TestCase;
use Yii;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

/**
 * Page size choices came from a fixed list in the widget, so they could not be changed from the settings
 * (2026-11-03). They now follow the options of `theme::page_size`; a list given by the view still wins.
 */
class LinkPageSizerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    public function testSizesComeFromTheSettingOptions(): void
    {
        $this->settingConfig(['50' => '50', '10' => '10', '25' => '25']);

        $this->assertSame([10 => 10, 25 => 25, 50 => 50], $this->sizer()->availableSizes);
    }

    public function testInvalidOptionsAreDropped(): void
    {
        $this->settingConfig(['10' => '10', 'all' => 'All', '0' => '0', '-5' => '-5']);

        $this->assertSame([10 => 10], $this->sizer()->availableSizes);
    }

    public function testListGivenByTheViewWins(): void
    {
        $this->settingConfig(['10' => '10', '20' => '20']);

        $sizer = $this->sizer(['availableSizes' => [12 => 12, 24 => 24, 48 => 48]]);

        $this->assertSame([12 => 12, 24 => 24, 48 => 48], $sizer->availableSizes);
    }

    public function testMissingSettingFallsBackToDefaults(): void
    {
        $setting = $this->createMock(Setting::class);
        $setting->method('getConfig')->willThrowException(new NotFoundHttpException('missing'));
        Yii::$app->set('setting', $setting);

        $this->assertSame(LinkPageSizer::DEFAULT_SIZES, $this->sizer()->availableSizes);
    }

    public function testEmptyOrNonListConfigFallsBackToDefaults(): void
    {
        $this->settingConfig([]);
        $this->assertSame(LinkPageSizer::DEFAULT_SIZES, $this->sizer()->availableSizes);

        $this->settingConfig('10,20');
        $this->assertSame(LinkPageSizer::DEFAULT_SIZES, $this->sizer()->availableSizes);
    }

    public function testNoSettingComponentFallsBackToDefaults(): void
    {
        Yii::$app->clear('setting');

        $this->assertSame(LinkPageSizer::DEFAULT_SIZES, $this->sizer()->availableSizes);
    }

    /**
     * @param mixed $config decoded `config` column of the setting row
     */
    private function settingConfig($config): void
    {
        $setting = $this->createMock(Setting::class);
        $setting->method('getConfig')->with('theme::page_size')->willReturn($config);
        Yii::$app->set('setting', $setting);
    }

    private function sizer(array $config = []): LinkPageSizer
    {
        return new LinkPageSizer($config + ['pagination' => new Pagination(['totalCount' => 100])]);
    }
}
