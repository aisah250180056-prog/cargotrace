<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ---------------------------------------------------------------------
        // 1. SEED USERS DENGAN 3 ROLE UTAMA (Slide 05 & 18)
        // ---------------------------------------------------------------------
        $admin = User::firstOrCreate(
            ['email' => 'admin@cargotrace.com'],
            [
                'name'     => 'Super Administrator',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        $operator = User::firstOrCreate(
            ['email' => 'operator@cargotrace.com'],
            [
                'name'     => 'Budi Santoso (Logistics Operator)',
                'password' => Hash::make('password'),
                'role'     => 'operator',
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@cargotrace.com'],
            [
                'name'     => 'PT Nusantara Global Elektronik',
                'password' => Hash::make('password'),
                'role'     => 'customer',
            ]
        );

        // ---------------------------------------------------------------------
        // 2. SEED CARRIERS DUNIA (Slide 09 & 11)
        // ---------------------------------------------------------------------
        $maersk = Carrier::firstOrCreate(
            ['code' => 'MAEU'],
            [
                'name'           => 'Maersk Line',
                'transport_mode' => 'SEA',
                'contact_email'  => 'operations@maersk.com',
                'is_active'      => true,
            ]
        );

        $cma = Carrier::firstOrCreate(
            ['code' => 'CMACGM'],
            [
                'name'           => 'CMA CGM Group',
                'transport_mode' => 'SEA',
                'contact_email'  => 'cargo@cma-cgm.com',
                'is_active'      => true,
            ]
        );

        $garuda = Carrier::firstOrCreate(
            ['code' => 'GIA'],
            [
                'name'           => 'Garuda Cargo International',
                'transport_mode' => 'AIR',
                'contact_email'  => 'cargo@garuda-indonesia.com',
                'is_active'      => true,
            ]
        );

        // ---------------------------------------------------------------------
        // 3. SEED LOCATIONS (PELABUHAN DUNIA & KOORDINAT GPS LEAFLET) (Slide 09)
        // ---------------------------------------------------------------------
        $priok = Location::firstOrCreate(
            ['unlocode' => 'IDJKT'],
            [
                'name'         => 'Port of Tanjung Priok',
                'city'         => 'Jakarta',
                'country_code' => 'ID',
                'latitude'     => -6.1018240,
                'longitude'    => 106.8837130,
            ]
        );

        $singapore = Location::firstOrCreate(
            ['unlocode' => 'SGSIN'],
            [
                'name'         => 'Port of Singapore',
                'city'         => 'Singapore',
                'country_code' => 'SG',
                'latitude'     => 1.2902700,
                'longitude'    => 103.8519590,
            ]
        );

        $rotterdam = Location::firstOrCreate(
            ['unlocode' => 'NLRTM'],
            [
                'name'         => 'Port of Rotterdam',
                'city'         => 'Rotterdam',
                'country_code' => 'NL',
                'latitude'     => 51.9054440,
                'longitude'    => 4.3312680,
            ]
        );

        $hamburg = Location::firstOrCreate(
            ['unlocode' => 'DEHAM'],
            [
                'name'         => 'Hamburg Airport Logistics Hub',
                'city'         => 'Hamburg',
                'country_code' => 'DE',
                'latitude'     => 53.6303890,
                'longitude'    => 9.9882280,
            ]
        );

        // ---------------------------------------------------------------------
        // 4. SEED CONTOH SHIPMENTS (DENGAN SKENARIO DELAY & EXCEPTION) (Slide 08 & 23)
        // ---------------------------------------------------------------------
        $now = Carbon::now();

        // Shipment 1: Sedang Berlayar & Mengalami Keterlambatan (PORT_CONGESTION)
        $shipment1 = Shipment::firstOrCreate(
            ['shipment_number' => 'CT-2026-0891'],
            [
                'customer_id'        => $customer->id,
                'carrier_id'         => $maersk->id,
                'origin_id'          => $priok->id,
                'destination_id'     => $rotterdam->id,
                'status'             => 'IN_TRANSIT',
                'expected_departure' => $now->copy()->subDays(6),
                'expected_arrival'   => $now->copy()->addDays(3),
                'estimated_arrival'  => $now->copy()->addDays(3)->addHours(18), // Keterlambatan 18 Jam
                'is_delayed'         => true,
                'delay_hours'        => 18,
                'delay_severity'     => 'MEDIUM', // Aturan: >6-24 jam = MEDIUM
                'notes'              => 'Kontainer elektronik high-priority, tujuan Rotterdam via Suez Canal.',
            ]
        );

        // Barang Shipment 1
        ShipmentItem::firstOrCreate(
            ['shipment_id' => $shipment1->id, 'item_name' => 'Lithium Battery Cells & Microchips'],
            [
                'quantity'           => 120,
                'weight_kg'          => 4250.00,
                'volume_cbm'         => 18.50,
                'hs_code'            => '8507.60.00',
                'is_dangerous_goods' => true,
            ]
        );

        // Tracking Events Kronologis untuk Shipment 1 (Slide 07)
        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment1->id, 'event_type' => 'PICKED_UP'],
            [
                'created_by'    => $operator->id,
                'location_name' => 'Cikarang Industrial Estate, Bekasi',
                'latitude'      => -6.3142000,
                'longitude'     => 107.1518000,
                'event_time'    => $now->copy()->subDays(6)->setTime(9, 0),
                'description'   => 'Muatan kontainer diambil oleh truk Maersk Logistics dari pabrik.',
            ]
        );

        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment1->id, 'event_type' => 'DEPARTED_PORT'],
            [
                'created_by'    => $operator->id,
                'location_name' => 'Port of Tanjung Priok (IDJKT)',
                'latitude'      => -6.1018240,
                'longitude'     => 106.8837130,
                'event_time'    => $now->copy()->subDays(5)->setTime(16, 30),
                'description'   => 'Kapal MV Northern Star diberangkatkan menuju transit Singapura.',
            ]
        );

        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment1->id, 'event_type' => 'PORT_CALL'],
            [
                'created_by'    => $operator->id,
                'location_name' => 'Port of Singapore (SGSIN)',
                'latitude'      => 1.2902700,
                'longitude'     => 103.8519590,
                'event_time'    => $now->copy()->subDays(3)->setTime(11, 15),
                'description'   => 'Bunkering dan transit peti kemas selesai di Singapura.',
            ]
        );

        TrackingEvent::firstOrCreate(
            ['shipment_id' => $shipment1->id, 'event_type' => 'IN_TRANSIT'],
            [
                'created_by'    => $operator->id,
                'location_name' => 'Red Sea Corridor Approach',
                'latitude'      => 27.8540000,
                'longitude'     => 34.2810000,
                'event_time'    => $now->copy()->subHours(8),
                'description'   => 'Posisi terkini: Kapal melintas di koridor Laut Merah menuju Kanal Suez.',
            ]
        );

        // Exception Aktif untuk Shipment 1 (Slide 08)
        ShipmentException::firstOrCreate(
            ['shipment_id' => $shipment1->id, 'exception_type' => 'PORT_CONGESTION'],
            [
                'logged_by'          => $operator->id,
                'severity'           => 'MEDIUM',
                'status'             => 'INVESTIGATING',
                'delay_impact_hours' => 18,
                'root_cause'         => 'Antrean labuh kapal padat di Port of Rotterdam akibat badai musiman.',
                'resolution_notes'   => 'Operator berkoordinasi dengan otoritas pelabuhan Rotterdam untuk prioritas sandar.',
                'detected_at'        => $now->copy()->subHours(6),
            ]
        );

        // Notifikasi untuk Customer (Slide 10)
        Notification::firstOrCreate(
            ['user_id' => $customer->id, 'title' => 'Peringatan Delay Pengiriman CT-2026-0891'],
            [
                'shipment_id' => $shipment1->id,
                'type'        => 'DELAY_ALERT',
                'message'     => 'Kargo Anda CT-2026-0891 mengalami penundaan sekitar 18 jam akibat kepadatan di Pelabuhan Rotterdam.',
                'is_read'     => false,
            ]
        );

        // Audit Log (Slide 10 & 16)
        AuditLog::create([
            'user_id'     => $operator->id,
            'action'      => 'LOG_EXCEPTION',
            'entity_type' => 'App\Models\ShipmentException',
            'entity_id'   => 1,
            'new_values'  => ['exception_type' => 'PORT_CONGESTION', 'impact' => '+18h'],
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);
    }
}