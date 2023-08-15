<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->username,
            'attributes' => [
                'firstName' =>  $this->first_name,
                'lastName' => $this->last_name,
                'fullName' => $this->first_name . ' ' . $this->last_name,
                'email' => $this->email,
                'phone_number' => $this->phone_number
            ],
        ];
    }
}
