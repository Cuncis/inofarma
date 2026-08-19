<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One Biteship courier booking per `antar` order.
 *
 * `courier_company`/`courier_type`/`price` are set at checkout, from
 * whichever quote `POST /v1/rates/couriers` returned and the customer picked
 * — this is a reservation, not yet a booking. `biteship_order_id`,
 * `tracking_id`, `waybill_id` stay null until an admin actually books the
 * pickup from the branch (`POST /v1/orders`) — see ROADMAP.md 7.1, "buat
 * label dan resi dari admin cabang".
 *
 * `status` stores Biteship's own vocabulary verbatim (confirmed, allocated,
 * pickingUp, picked, inTransit, droppingOff, delivered, returned, cancelled,
 * courierNotFound, onHold, rejected, disposed) rather than remapping it —
 * unlike DOKU's small, stable status set, Biteship's is already exactly the
 * words that make sense to show an admin, and remapping would just be a
 * second vocabulary to keep in sync for no benefit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();

            $table->string('courier_company', 40);
            $table->string('courier_type', 40);
            $table->string('courier_name')->nullable();
            $table->string('courier_service_name')->nullable();
            $table->unsignedBigInteger('price');

            $table->string('biteship_order_id')->nullable();
            $table->string('tracking_id')->nullable();
            $table->string('waybill_id')->nullable();
            $table->string('courier_link')->nullable();
            $table->string('status', 40)->nullable();

            // Append-only trail of every status Biteship has reported, oldest first.
            $table->json('history')->nullable();
            $table->json('raw_response')->nullable();

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index('tracking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
