<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2019 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Fisharebest\Webtrees\Module;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Report\HtmlRenderer;
use Fisharebest\Webtrees\Report\ReportParserGenerate;
use Fisharebest\Webtrees\Report\ReportParserSetup;
use Fisharebest\Webtrees\Report\PdfRenderer;
use Fisharebest\Webtrees\Services\UserService;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Webtrees;
use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\Filesystem;

/**
 * Test harness for the class IndividualReportModule
 *
 * @covers \Fisharebest\Webtrees\Module\IndividualReportModule
 * @covers \Fisharebest\Webtrees\Module\ModuleReportTrait
 * @covers \Fisharebest\Webtrees\Report\AbstractRenderer
 * @covers \Fisharebest\Webtrees\Report\ReportBaseCell
 * @covers \Fisharebest\Webtrees\Report\ReportBaseElement
 * @covers \Fisharebest\Webtrees\Report\ReportBaseFootnote
 * @covers \Fisharebest\Webtrees\Report\ReportBaseHtml
 * @covers \Fisharebest\Webtrees\Report\ReportBaseImage
 * @covers \Fisharebest\Webtrees\Report\ReportBaseLine
 * @covers \Fisharebest\Webtrees\Report\ReportBasePageHeader
 * @covers \Fisharebest\Webtrees\Report\ReportBaseText
 * @covers \Fisharebest\Webtrees\Report\ReportBaseTextbox
 * @covers \Fisharebest\Webtrees\Report\ReportExpressionLanguageProvider
 * @covers \Fisharebest\Webtrees\Report\HtmlRenderer
 * @covers \Fisharebest\Webtrees\Report\ReportHtmlCell
 * @covers \Fisharebest\Webtrees\Report\ReportHtmlFootnote
 * @covers \Fisharebest\Webtrees\Report\ReportHtmlHtml
 * @covers \Fisharebest\Webtrees\Report\ReportHtmlImage
 * @covers \Fisharebest\Webtrees\Report\ReportHtmlLine
 * @covers \Fisharebest\Webtrees\Report\ReportHtmlPageHeader
 * @covers \Fisharebest\Webtrees\Report\ReportHtmlText
 * @covers \Fisharebest\Webtrees\Report\ReportHtmlTextbox
 * @covers \Fisharebest\Webtrees\Report\ReportParserBase
 * @covers \Fisharebest\Webtrees\Report\ReportParserGenerate
 * @covers \Fisharebest\Webtrees\Report\ReportParserSetup
 * @covers \Fisharebest\Webtrees\Report\PdfRenderer
 * @covers \Fisharebest\Webtrees\Report\ReportPdfCell
 * @covers \Fisharebest\Webtrees\Report\ReportPdfFootnote
 * @covers \Fisharebest\Webtrees\Report\ReportPdfHtml
 * @covers \Fisharebest\Webtrees\Report\ReportPdfImage
 * @covers \Fisharebest\Webtrees\Report\ReportPdfLine
 * @covers \Fisharebest\Webtrees\Report\ReportPdfPageHeader
 * @covers \Fisharebest\Webtrees\Report\ReportPdfText
 * @covers \Fisharebest\Webtrees\Report\ReportPdfTextBox
 * @covers \Fisharebest\Webtrees\Report\ReportTcpdf
 * @covers \Fisharebest\Webtrees\Report\PdfRenderer
 */
class IndividualReportModuleTest extends TestCase
{
    protected static $uses_database = true;

    /**
     * @return void
     */
    public function testReportRunsWithoutError(): void
    {
        $data_filesystem = new Filesystem(new NullAdapter());

        $user = (new UserService())->create('user', 'User', 'user@example.com', 'secret');
        $user->setPreference(User::PREF_IS_ADMINISTRATOR, '1');
        Auth::login($user);

        $tree = $this->importTree('demo.ged');
        $xml  = Webtrees::ROOT_DIR . 'resources/xml/reports/individual_report.xml';
        $vars = [
            'id'       => ['id' => 'X1030'],
            'sources'   => ['id' => 'on'],
            'notes'     => ['id' => 'on'],
            'photos'    => ['id' => 'highlighted'],
            'colors'    => ['id' => 'on'],
            'pageSize'  => ['id' => 'A4'],
        ];

        $report = new ReportParserSetup($xml);
        $this->assertIsArray($report->reportProperties());

        ob_start();
        new ReportParserGenerate($xml, new HtmlRenderer(), $vars, $tree, $data_filesystem);
        $html = ob_get_clean();
        $this->assertStringStartsWith('<', $html);
        $this->assertStringEndsWith('>', $html);

        ob_start();
        new ReportParserGenerate($xml, new PdfRenderer(), $vars, $tree, $data_filesystem);
        $pdf = ob_get_clean();
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertStringEndsWith("%%EOF\n", $pdf);
    }
}
