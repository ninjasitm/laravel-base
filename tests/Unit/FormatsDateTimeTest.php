<?php

use Nitm\Content\Models\CalendarEntry;
use Nitm\Content\Traits\FormatsDateTime;
use PHPUnit\Framework\TestCase;

class FormatsDateTimeTest extends TestCase {
    public function testEnsureFrequencyOptionReturnsCanonicalFrequencyKeys(): void {
        $subject = new class {
            use FormatsDateTime;

            public function normalizeFrequency($frequency): string {
                return $this->ensureFrequencyOption($frequency);
            }
        };

        $this->assertSame(CalendarEntry::FREQUENCY_DAILY, $subject->normalizeFrequency(CalendarEntry::FREQUENCY_DAILY));
        $this->assertSame(CalendarEntry::FREQUENCY_DAILY, $subject->normalizeFrequency('Day'));
        $this->assertSame(CalendarEntry::FREQUENCY_MONTHLY, $subject->normalizeFrequency('Month'));
        $this->assertSame(CalendarEntry::FREQUENCY_WEEKLY, $subject->normalizeFrequency('not-a-frequency'));
    }
}