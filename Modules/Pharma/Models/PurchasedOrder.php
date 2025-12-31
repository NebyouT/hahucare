<?php

namespace Modules\Pharma\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Pharma\Database\factories\PurchasedOrderFactory;
use App\Models\User;

class PurchasedOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['medicine_id', 'pharma_id', 'quantity', 'delivery_date', 'payment_status', 'total_amount', 'order_status'];
    
    /**
     * Get the medicine associated with the purchase order.
     */
    
    protected static function newFactory()
    {
    }
    public function medicine()
    {
        return $this->belongsTo(Medicine::class)->with('manufacturer');
    }

    public function pharmaUser()
    {
        return $this->belongsTo(User::class, 'pharma_id')->with('clinic');
    }
    public function scopesetRole($query, $user)
    {

        $user_id = $user->id;

        if (auth()->user()->hasRole(['admin', 'demo_admin'])) {

                $user_ids = User::role(['admin', 'demo_admin'])->pluck('id');

                $query = $query;
        }

        if ($user->hasRole('vendor')) {

            $query = $query->whereHas('pharmaUser', function ($qry) use ($user_id) {
                $qry->whereHas('clinic', function ($q) use ($user_id) {
                    $q->where('vendor_id', $user_id);
                });
            });

        }

        return $query;
    }

}
