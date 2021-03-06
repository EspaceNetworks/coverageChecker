<?php
namespace exussum12\CoverageChecker\tests;

use PHPUnit\Framework\TestCase;
use exussum12\CoverageChecker\CoverageCheck;
use exussum12\CoverageChecker\DiffFileLoader;
use exussum12\CoverageChecker\FileMatchers;
use exussum12\CoverageChecker\XMLReport;

class CoverageCheckTest extends TestCase
{
    public function testCoverage()
    {
        $diffFileState = $this->createMock(DiffFileLoader::class);
        $diffFileState->method('getChangedLines')
            ->willReturn([
                'testFile1.php' => [1,2,3,4],
                'testFile2.php' => [3,4]

            ]);

        $xmlReport = $this->createMock(XMLReport::class);
        $xmlReport->method('getLines')
            ->willReturn([
                '/full/path/to/testFile1.php' => [1 => 1,2 => 0,3 => 1,4 => 1],
                '/full/path/to/testFile2.php' => [3 => 1,4 => 0]

            ]);

        $xmlReport->method('isValidLine')
            ->will(
                $this->returnCallback(
                    function () {
                        $file = func_get_arg(0);
                        $line = func_get_arg(1);

                        if ($file == '/full/path/to/testFile1.php' && $line == 2) {
                            return false;
                        }
                        if ($file == '/full/path/to/testFile2.php' && $line == 4) {
                            return false;
                        }

                        return true;
                    }
                )
            );

        $matcher = new FileMatchers\EndsWith;
        $coverageCheck = new CoverageCheck($diffFileState, $xmlReport, $matcher);
        $lines = $coverageCheck->getCoveredLines();
        $uncoveredLines = [
            'testFile1.php' => [2 => 0],
            'testFile2.php' => [4 => 0],
        ];
        $coveredLines = [
            'testFile1.php' => [1,3,4],
            'testFile2.php' => [3],
        ];

        $expected = [
            'coveredLines' => $coveredLines,
            'uncoveredLines' => $uncoveredLines,
        ];

        $this->assertEquals($expected, $lines);
    }

    public function testCoverageFailed()
    {
        $diffFileState = $this->createMock(DiffFileLoader::class);
        $diffFileState->method('getChangedLines')
            ->willReturn([
                'testFile1.php' => [1,2,3,4],
                'testFile2.php' => [3,4],

            ]);

        $xmlReport = $this->createMock(XMLReport::class);
        $xmlReport->method('getLines')
            ->willReturn([
                '/full/path/to/testFile1.php' => [1 => 1,2 => 0,3 => 1,4 => 1],

            ]);

        $xmlReport->method('handleNotFoundFile')
            ->willReturn(null);

        $xmlReport->method('isValidLine')
            ->will(
                $this->returnCallback(
                    function () {
                        $file = func_get_arg(0);
                        $line = func_get_arg(1);

                        if ($file == '/full/path/to/testFile1.php' && $line == 2) {
                            return false;
                        }

                        return true;
                    }
                )
            );

        $matcher = new FileMatchers\EndsWith;
        $coverageCheck = new CoverageCheck($diffFileState, $xmlReport, $matcher);
        $lines = $coverageCheck->getCoveredLines();

        $uncoveredLines = [
            'testFile1.php' => [2 => 0],
        ];
        $coveredLines = [
            'testFile1.php' => [1,3,4],
        ];

        $expected = [
            'coveredLines' => $coveredLines,
            'uncoveredLines' => $uncoveredLines,
        ];

        $this->assertEquals($expected, $lines);
    }

    public function testAddingAllUnknownsCovered()
    {
        $diffFileState = $this->createMock(DiffFileLoader::class);
        $diffFileState->method('getChangedLines')
            ->willReturn([
                'testFile1.php' => [1,2,3,4],
                'testFile2.php' => [3,4],

            ]);

        $xmlReport = $this->createMock(XMLReport::class);
        $xmlReport->method('getLines')
            ->willReturn([
                '/full/path/to/testFile1.php' => [1 => 1,2 => 0,3 => 1,4 => 1],

            ]);

        $xmlReport->method('handleNotFoundFile')
            ->willReturn(true);

        $xmlReport->method('isValidLine')
            ->will(
                $this->returnCallback(
                    function () {
                        $file = func_get_arg(0);
                        $line = func_get_arg(1);

                        if ($file == '/full/path/to/testFile1.php' && $line == 2) {
                            return false;
                        }

                        return true;
                    }
                )
            );

        $matcher = new FileMatchers\EndsWith;
        $coverageCheck = new CoverageCheck($diffFileState, $xmlReport, $matcher);
        $lines = $coverageCheck->getCoveredLines();

        $uncoveredLines = [
            'testFile1.php' => [2 => 0],
        ];
        $coveredLines = [
            'testFile1.php' => [1,3,4],
            'testFile2.php' => [3,4],
        ];

        $expected = [
            'coveredLines' => $coveredLines,
            'uncoveredLines' => $uncoveredLines,
        ];

        $this->assertEquals($expected, $lines);
    }

    public function testAddingAllUnknownsUnCovered()
    {
        $diffFileState = $this->createMock(DiffFileLoader::class);
        $diffFileState->method('getChangedLines')
            ->willReturn([
                'testFile1.php' => [1,2,3,4],
                'testFile2.php' => [3,4],

            ]);

        $xmlReport = $this->createMock(XMLReport::class);
        $xmlReport->method('getLines')
            ->willReturn([
                '/full/path/to/testFile1.php' => [1 => 1,2 => 0,3 => 1,4 => 1],

            ]);

        $xmlReport->method('handleNotFoundFile')
            ->willReturn(false);

        $xmlReport->method('isValidLine')
            ->will(
                $this->returnCallback(
                    function () {
                        $file = func_get_arg(0);
                        $line = func_get_arg(1);

                        if ($file == '/full/path/to/testFile1.php' && $line == 2) {
                            return false;
                        }

                        return true;
                    }
                )
            );

        $matcher = new FileMatchers\EndsWith;
        $coverageCheck = new CoverageCheck($diffFileState, $xmlReport, $matcher);
        $lines = $coverageCheck->getCoveredLines();

        $uncoveredLines = [
            'testFile1.php' => [2 => 0],
            'testFile2.php' => [3 => 0, 4 => 0],
        ];
        $coveredLines = [
            'testFile1.php' => [1,3,4],
        ];

        $expected = [
            'coveredLines' => $coveredLines,
            'uncoveredLines' => $uncoveredLines,
        ];

        $this->assertEquals($expected, $lines);
    }

    public function testCoverageForContextLines()
    {
        $diffFileState = $this->createMock(DiffFileLoader::class);
        $diffFileState->method('getChangedLines')
            ->willReturn([
                'testFile1.php' => [1,2,4],

            ]);

        $xmlReport = $this->createMock(XMLReport::class);
        $xmlReport->method('getLines')
            ->willReturn([
                '/full/path/to/testFile1.php' => [1 => 1,4 => 1],

            ]);

        $xmlReport->method('handleNotFoundFile')
            ->willReturn(false);

        $xmlReport->method('isValidLine')
            ->will(
                $this->returnCallback(
                    function () {
                        $file = func_get_arg(0);
                        $line = func_get_arg(1);

                        if ($file == '/full/path/to/testFile1.php' && $line == 2) {
                            return null;
                        }

                        return true;
                    }
                )
            );

        $matcher = new FileMatchers\EndsWith;
        $coverageCheck = new CoverageCheck($diffFileState, $xmlReport, $matcher);
        $lines = $coverageCheck->getCoveredLines();

        $coveredLines = [
            'testFile1.php' => [1,4],
        ];

        $expected = [
            'coveredLines' => $coveredLines,
            'uncoveredLines' => [],
        ];

        $this->assertEquals($expected, $lines);
    }
}
