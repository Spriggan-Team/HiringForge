<?php 


namespace App\Infrastructure\Services\AI\Resume;

class ResumeResponseSchema
{
    
    /**
     * Build AI response format
     */
    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'firstName' => [
                    'type' => ['string', 'null'],
                ],
                'lastName' => [
                    'type' => ['string', 'null'],
                ],
                'email' => [
                    'type' => ['string', 'null'],
                ],
                'phone' => [
                    'type' => ['string', 'null'],
                ],
                'experiences' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'company' => [
                                'type' => ['string', 'null'],
                            ],
                            'position' => [
                                'type' => ['string', 'null'],
                            ],
                            'startDate' => [
                                'type' => ['string', 'null'],
                            ],
                            'endDate' => [
                                'type' => ['string', 'null'],
                            ],
                            'description' => [
                                'type' => ['string', 'null'],
                            ],
                        ],
                        'required' => [
                            'company',
                            'position',
                            'startDate',
                            'endDate',
                            'description',
                        ],
                    ],
                ],
                'educations' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'institution' => [
                                'type' => ['string', 'null'],
                            ],
                            'degree' => [
                                'type' => ['string', 'null'],
                            ],
                            'startDate' => [
                                'type' => ['string', 'null'],
                            ],
                            'endDate' => [
                                'type' => ['string', 'null'],
                            ],
                        ],
                        'required' => [
                            'institution',
                            'degree',
                            'startDate',
                            'endDate',
                        ],
                    ],
                ],
                'skills' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'required' => [
                'firstName',
                'lastName',
                'email',
                'phone',
                'experiences',
                'educations',
                'skills',
            ],
        ];
    }
}