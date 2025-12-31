<?php

namespace Modules\Pharma\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pharma\Database\factories\MedicineFactory;
use Modules\Tax\Models\Tax;
use Modules\Pharma\Models\MedicineHistory;

class Medicine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'dosage', 'pharma_id', 'category_id', 'form_id', 'expiry_date', 'note', 'supplier_id', 'contact_number', 'payment_terms', 'quntity', 're_order_level', 'manufacturer_id', 'batch_no', 'start_serial_no', 'end_serial_no', 'purchase_price', 'selling_price', 'stock_value', 'is_inclusive_tax'];

    protected
        $casts = [
            'expiry_date' => 'datetime',
            'is_inclusive_tax' => 'boolean',
            'purchase_price' => 'double',
            'selling_price' => 'double',
            'stock_value' => 'integer',
            'quntity' => 'integer',
        ];

    protected static function newFactory()
    {
    }

    public function category()
    {
        return $this->belongsTo(MedicineCategory::class);
    }
    public function form()
    {
        return $this->belongsTo(MedicineForm::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function supplierIds()
    {
        return json_decode($this->supplier_id ?? '[]');
    }

    public function suppliers()
    {
        return Supplier::whereIn('id', $this->supplierIds())->get();
    }

    public function taxes()
    {
        return Tax::whereIn('id', $this->taxIds())->get(); // assuming Tax model exists
    }

    public function taxIds()
    {
        return json_decode($this->tax_id ?? '[]');
    }

    public function getSuppliersListAttribute()
    {
        return Supplier::whereIn('id', $this->supplierIds())->get();
    }

    public function getTaxListAttribute()
    {
        return Tax::whereIn('id', $this->taxIds())->get();
    }

    public function pharmaUser()
    {
        return $this->belongsTo(User::class, 'pharma_id')->with('clinic');
    }

    public function purchasedOrders()
    {
        return $this->hasMany(PurchasedOrder::class);
    }
    public function history()
    {
        return $this->belongsTo(MedicineHistory::class);
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

        if ($user->hasRole('pharma')) {

            $query = $query->where('pharma_id', $user_id);
        }

        return $query;
    }
}
