<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShipmentPolicy
{
    /**
     * Siapa yang boleh melihat daftar shipment? (Semua role yang terautentikasi)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Aturan Inti UAS (Slide 05 & 13):
     * Admin & Operator bisa lihat semua shipment.
     * Customer HANYA BISA LIHAT SHIPMENT MILIKNYA SENDIRI.
     */
    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->isAdmin() || $user->isOperator()) {
            return true;
        }

        return $user->id === $shipment->customer_id;
    }

    /**
     * Hanya Admin dan Operator yang boleh membuat shipment baru
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Hanya Admin dan Operator yang boleh mengedit data shipment
     */
    public function update(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin() || $user->isOperator();
    }

    /**
     * Hanya Administrator yang boleh menghapus shipment
     */
    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->isAdmin();
    }
}