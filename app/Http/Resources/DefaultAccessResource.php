<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class DefaultAccessResource extends JsonResource
{

    public $info;

    public function __construct($info)
    {
        parent::__construct($info);
        $this->info = $info;
    }


    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) : array
    {
        // Get branch_id from info array, fallback to current user's branch_id if not set
        $branchId = $this->info['branch_id'] ?? \Illuminate\Support\Facades\Auth::user()->branch_id;
        
        return [
            "branch_id" => $branchId
        ];
    }

}
