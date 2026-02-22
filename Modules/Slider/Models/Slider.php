<?php

namespace Modules\Slider\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Category\Models\Category;
use Modules\Service\Models\Service;
use Modules\Constant\Models\Constant;
use Modules\Service\Models\SystemServiceCategory;
use Modules\Clinic\Models\SystemService;

class Slider extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sliders';
    
    protected $appends = ['file_url'];

    const CUSTOM_FIELD_MODEL = 'Modules\Slider\Models\Slider';

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Slider\database\factories\SliderFactory::new();
    }

    public function module()
    {
        if ($this->type === 'service') {
            return $this->belongsTo(SystemService::class, 'link_id');
        } elseif ($this->type === 'category') {
            return $this->belongsTo(SystemServiceCategory::class, 'link_id');
        }
        return $this->belongsTo(SystemService::class, 'link_id')->whereRaw('1=0');
    }

    // Update the typeConstant relationship
    public function typeConstant()
    {
        return $this->belongsTo(Constant::class, 'type', 'id');
    }

    // Update systemServiceCategory relationship
    public function systemServiceCategory()
    {
        return $this->belongsTo(SystemServiceCategory::class, 'link_id')
            ->withDefault(['name' => '-']);
    }

    // Update systemService relationship
    public function systemService()
    {
        return $this->belongsTo(SystemService::class, 'link_id')
            ->withDefault(['name' => '-']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    
    protected function getFileUrlAttribute()
    {
        // Try media library first (for newly uploaded images)
        $media = $this->getFirstMediaUrl('file_url');
        if (isset($media) && !empty($media)) {
            return $media;
        }
        
        // Fallback to dummy-images for existing slider images
        $dummyImagePath = 'dummy-images/sliders/' . $this->id . '.jpg';
        if (file_exists(public_path($dummyImagePath))) {
            return asset($dummyImagePath);
        }
        
        // Final fallback to default banner
        return asset('img/frontend/banner.jpg');
    }
}
