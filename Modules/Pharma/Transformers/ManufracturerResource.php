<?php

namespace Modules\Pharma\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ManufracturerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
