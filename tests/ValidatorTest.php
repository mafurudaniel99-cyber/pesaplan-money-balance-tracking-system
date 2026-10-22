<?php
/**
 * PesaPlan — Unit tests for the pure-PHP utility classes.
 *
 * These tests do NOT touch the database. They verify:
 *   - Validator::amount() parsing and rejection
 *   - Validator::validate() rule engine
 *   - ReportService::csvCell() formula injection prevention
 *
 * To run:
 *   composer require phpunit/phpunit
 *   ./vendor/bin/phpunit tests/ValidatorTest.php
 *
 * Database-backed tests for financial calculations are documented in
 * docs/TESTING.md as a manual test plan (they require a fixture database).
 */

declare(strict_types=1);

use App\Core\Validator;
use App\Services\ReportService;

require_once __DIR__ . '/../app/Core/Validator.php';
require_once __DIR__ . '/../app/Services/ReportService.php';

class ValidatorTest extends PHPUnit\Framework\TestCase
{
    public function test_amount_parses_plain_integer(): void
    {
        $this->assertSame(1500.0, Validator::amount('1500'));
    }

    public function test_amount_parses_thousand_separators(): void
    {
        $this->assertSame(1500000.0, Validator::amount('1,500,000'));
    }

    public function test_amount_parses_decimal(): void
    {
        $this->assertSame(1500.50, Validator::amount('1500.50'));
    }

    public function test_amount_rejects_empty(): void
    {
        $this->assertNull(Validator::amount(''));
    }

    public function test_amount_rejects_text(): void
    {
        $this->assertNull(Validator::amount('hello'));
    }

    public function test_amount_parses_negative(): void
    {
        // Negative is parsed (callers must check > 0 themselves).
        $this->assertSame(-100.0, Validator::amount('-100'));
    }

    public function test_validate_required(): void
    {
        $v = new Validator(['name' => ''], ['name' => 'required']);
        $this->assertFalse($v->validate());
        $this->assertArrayHasKey('name', $v->errors());
    }

    public function test_validate_email(): void
    {
        $v = new Validator(['email' => 'not-an-email'], ['email' => 'required|email']);
        $this->assertFalse($v->validate());
    }

    public function test_validate_min_length(): void
    {
        $v = new Validator(['pw' => 'abc'], ['pw' => 'required|min:8']);
        $this->assertFalse($v->validate());
    }

    public function test_validate_passes_valid_data(): void
    {
        $v = new Validator(
            ['email' => 'a@b.com', 'pw' => 'longpassword'],
            ['email' => 'required|email', 'pw' => 'required|min:8']
        );
        $this->assertTrue($v->validate());
        $this->assertEmpty($v->errors());
    }

    public function test_sanitize_strips_tags(): void
    {
        $this->assertSame('hello', Validator::sanitize('<script>hello</script>'));
    }
}

class CsvInjectionTest extends PHPUnit\Framework\TestCase
{
    public function test_normal_value_unchanged(): void
    {
        $this->assertSame('"1500"', ReportService::csvCell('1500'));
    }

    public function test_formula_prefixed_with_quote(): void
    {
        $this->assertSame("'=HYPERLINK(\"http://evil\")", ReportService::csvCell('=HYPERLINK("http://evil")'));
    }

    public function test_plus_prefixed_with_quote(): void
    {
        $this->assertSame("'+cmd", ReportService::csvCell('+cmd'));
    }

    public function test_at_prefixed_with_quote(): void
    {
        $this->assertSame("'@sum", ReportService::csvCell('@sum'));
    }

    public function test_tab_prefixed_with_quote(): void
    {
        $this->assertSame("'\"\\tcell\"", ReportService::csvCell("\tcell"));
    }

    public function test_quote_escaped(): void
    {
        $this->assertSame('"hello ""world"""', ReportService::csvCell('hello "world"'));
    }
}
