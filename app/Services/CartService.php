<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    /** Cart items with their courses eager-loaded. */
    public function items(User $user): Collection
    {
        return $user->cartItems()
            ->with('course.instructor')
            ->latest()
            ->get()
            ->filter(fn (CartItem $item) => $item->course !== null)
            ->values();
    }

    /** Add a course to the cart. Returns a short status code. */
    public function add(User $user, Course $course): string
    {
        if ($course->status !== 'published') {
            return 'unavailable';
        }

        if ($course->isFree()) {
            return 'free';           // free courses are enrolled directly, not bought
        }

        if ($user->isEnrolledIn($course)) {
            return 'owned';
        }

        if ($user->hasInCart($course)) {
            return 'exists';
        }

        CartItem::create(['user_id' => $user->id, 'course_id' => $course->id]);

        return 'added';
    }

    public function remove(User $user, Course $course): void
    {
        $user->cartItems()->where('course_id', $course->id)->delete();
    }

    public function clear(User $user): void
    {
        $user->cartItems()->delete();
    }

    public function subtotal(User $user): float
    {
        return (float) $this->items($user)->sum(fn (CartItem $item) => (float) $item->course->price);
    }

    public function count(User $user): int
    {
        return $user->cartItems()->count();
    }
}
