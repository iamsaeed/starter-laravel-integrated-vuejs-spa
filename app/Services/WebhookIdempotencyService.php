<?php

namespace App\Services;

use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookIdempotencyService
{
    public function shouldProcess(string $provider, string $eventId, string $type, array $payload): bool
    {
        $existingEvent = WebhookEvent::query()
            ->where('provider', $provider)
            ->where('provider_event_id', $eventId)
            ->first();

        if ($existingEvent) {
            if ($existingEvent->isProcessed()) {
                Log::info('Webhook already processed (idempotency)', [
                    'provider' => $provider,
                    'event_id' => $eventId,
                ]);

                return false;
            }

            if ($existingEvent->isProcessing() &&
                $existingEvent->processing_started_at > now()->subMinutes(5)) {
                return false;
            }

            if ($existingEvent->canRetry()) {
                $existingEvent->update([
                    'status' => 'processing',
                    'processing_started_at' => now(),
                    'retry_count' => $existingEvent->retry_count + 1,
                ]);

                return true;
            }

            return false;
        }

        // New event
        WebhookEvent::query()->create([
            'id' => Str::uuid(),
            'provider' => $provider,
            'provider_event_id' => $eventId,
            'type' => $type,
            'payload' => $payload,
            'status' => 'processing',
            'processing_started_at' => now(),
        ]);

        return true;
    }

    public function markProcessed(string $provider, string $eventId): void
    {
        WebhookEvent::query()
            ->where('provider', $provider)
            ->where('provider_event_id', $eventId)
            ->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);
    }

    public function markFailed(string $provider, string $eventId, string $errorMessage): void
    {
        WebhookEvent::query()
            ->where('provider', $provider)
            ->where('provider_event_id', $eventId)
            ->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
            ]);
    }
}
