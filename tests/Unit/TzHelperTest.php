<?php

namespace Tests\Unit;

use App\Support\Tz;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TzHelperTest extends TestCase
{
    #[Test]
    public function it_formats_carbon_instances_in_jakarta_timezone(): void
    {
        $utcTime = Carbon::parse('2025-10-13 12:00:00', 'UTC');
        
        $formatted = Tz::format($utcTime, 'd/m/Y H:i:s');
        
        // UTC+7 means 12:00 UTC becomes 19:00 WIB
        $this->assertStringContainsString('19:00:00', $formatted);
        $this->assertStringContainsString('13/10/2025', $formatted);
    }

    #[Test]
    public function it_handles_null_values_gracefully(): void
    {
        $result = Tz::format(null);
        
        $this->assertSame('', $result);
    }

    #[Test]
    public function it_formats_strings_correctly(): void
    {
        $result = Tz::format('2025-10-13 12:00:00', 'd/m/Y H:i');
        
        // Should convert string to Carbon and apply timezone
        $this->assertStringContainsString('19:00', $result);
        $this->assertStringContainsString('13/10/2025', $result);
    }

    #[Test]
    public function it_uses_custom_format(): void
    {
        $utcTime = Carbon::parse('2025-10-13 12:00:00', 'UTC');
        
        $formatted = Tz::format($utcTime, 'Y-m-d');
        
        $this->assertSame('2025-10-13', $formatted);
    }

    #[Test]
    public function it_handles_invalid_strings_gracefully(): void
    {
        $result = Tz::format('invalid-date-string');
        
        $this->assertSame('', $result);
    }
}