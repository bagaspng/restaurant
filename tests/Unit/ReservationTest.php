<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ReservationService;
use App\Models\Reservation;
use InvalidArgumentException;

/**
 * Unit test untuk logika reservasi meja (durasi slot 120 menit, deteksi bentrok, dan validasi kontak).
 */
class ReservationTest extends TestCase
{
    private ReservationService $service;

    protected function setUp(): void
    {
        $this->service = new ReservationService();
    }

    /**
     * Memastikan durasi default reservasi dihitung tepat 120 menit (2 Jam)
     */
    public function testCalculateEndTimeFixedTwoHours(): void
    {
        $startTime = '2026-10-12 10:00:00';
        $endTime = $this->service->calculateEndTime($startTime, 120);

        $this->assertEquals('2026-10-12 12:00:00', $endTime);
    }

    /**
     * Memastikan slot operasional dipecah secara terstruktur 120 menit antara 10:00 s.d. 22:00
     */
    public function testGetOperatingSlotsStructure(): void
    {
        $slots = $this->service->getOperatingSlots();

        $this->assertIsArray($slots);
        $this->assertCount(6, $slots);

        // Slot 1: 10:00 - 12:00
        $this->assertEquals('10:00', $slots[0]['start']);
        $this->assertEquals('12:00', $slots[0]['end']);
        $this->assertEquals(120, $slots[0]['duration_minutes']);

        // Slot terakhir: 20:00 - 22:00
        $lastSlot = end($slots);
        $this->assertEquals('20:00', $lastSlot['start']);
        $this->assertEquals('22:00', $lastSlot['end']);
    }

    /**
     * Memastikan validasi nomor telepon hanya menerima format wajar 10-16 digit
     */
    public function testValidatePhoneNumber(): void
    {
        // Valid
        $this->assertTrue($this->service->validatePhoneNumber('081234567890'));
        $this->assertTrue($this->service->validatePhoneNumber('+6281234567890'));
        $this->assertTrue($this->service->validatePhoneNumber('0812-3456-7890'));
        $this->assertTrue($this->service->validatePhoneNumber('0819 8765 4321'));

        // Tidak Valid
        $this->assertFalse($this->service->validatePhoneNumber('12345')); // Terlalu pendek
        $this->assertFalse($this->service->validatePhoneNumber('abc0812345678')); // Mengandung huruf
        $this->assertFalse($this->service->validatePhoneNumber('')); // Kosong
    }

    /**
     * Memastikan serialisasi catatan khusus terstruktur menghasilkan JSON valid
     */
    public function testFormatNotesToStructuredJson(): void
    {
        $requests = ['baby_chair', 'window_seat', 'invalid_hack'];
        $otherNote = 'Dekorasi bunga mawar';

        $json = $this->service->formatNotesToStructuredJson($requests, $otherNote);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertContains('baby_chair', $decoded['requests']);
        $this->assertContains('window_seat', $decoded['requests']);
        $this->assertNotContains('invalid_hack', $decoded['requests']); // Opsi ilegal difilter

        // Ringkasan terbaca manusia
        $summary = ReservationService::formatNotesSummary($json);
        $this->assertStringContainsString('Baby Chair', $summary);
        $this->assertStringContainsString('Meja Dekat Jendela', $summary);
    }

    /**
     * Memastikan model Reservation mem-parse catatan terstruktur secara benar
     */
    public function testReservationModelStructuredNotes(): void
    {
        $notesJson = json_encode([
            'requests' => ['wheelchair_access'],
            'other_note' => 'Bawa lansia'
        ]);

        $res = new Reservation(
            1,
            2,
            1,
            'Ahmad Sanusi',
            '081234567890',
            '2026-10-12 12:00:00',
            '2026-10-12 14:00:00',
            null,
            'confirmed',
            $notesJson
        );

        $this->assertEquals('Ahmad Sanusi', $res->getCustomerName());
        $this->assertEquals('081234567890', $res->getCustomerPhone());
        $this->assertTrue($res->isConfirmed());

        $parsed = $res->getStructuredRequests();
        $this->assertEquals(['wheelchair_access'], $parsed['requests']);
    }
}
