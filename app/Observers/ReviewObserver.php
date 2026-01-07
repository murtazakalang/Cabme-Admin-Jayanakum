<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\UserApp;
use App\Models\Driver;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        $this->updateRating($review, 'add');
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        if ($review->isDirty('rating')) {
            $this->updateRating($review, 'update', $review->getOriginal('rating'));
        }
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        $this->updateRating($review, 'delete');
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }

    public function updateRating(Review $review, $action, $originalRating = null)
    {
        if ($review->review_to === "customer") {
            $user = UserApp::find($review->user_id);
        } elseif ($review->review_to === "driver") {
            $user = Driver::find($review->driver_id);
        }
        if (!$user) return;

        if ($action === 'add') {
            $user->review_sum += $review->rating;
            $user->review_count += 1;
        } elseif ($action === 'update') {
            $user->review_sum += ($review->rating - $originalRating);
        } elseif ($action === 'delete') {
            $user->review_sum -= $review->rating;
            $user->review_count -= 1;
        }
        $user->average_rating = $user->review_count > 0
        ? number_format($user->review_sum / $user->review_count, 1)
        : '0.0';
        $user->save();
    }
}
