<?php

namespace Modules\Pharma\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Pharma\Database\factories\SupplierFactory;
use Spatie\MediaLibrary\HasMedia;
use app\Models\User;
use Spatie\MediaLibrary\InteractsWithMedia;

class Supplier extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'contact_number', 'status', 'supplier_type_id', 'pharma_id', 'payment_terms'];
    
    protected static function newFactory(): SupplierFactory
    {
    }

    public function supplierType()
    {
        return $this->belongsTo(SupplierType::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function medicine()
    {
        return $this->hasMany(Medicine::class)->with('purchasedOrders');
    }

    protected function getProfileImageAttribute()
    {
        $media = $this->getFirstMediaUrl('supplier_image');

        return isset($media) && !empty($media) ? $media : asset(config('app.avatar_base_path') . 'avatar.webp');
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
                $qry->whereHas('clinic', function ($qry) use ($user_id) {
                    $qry->where('vendor_id', $user_id);
                });
            });

        }

        return $query;
    }

}
