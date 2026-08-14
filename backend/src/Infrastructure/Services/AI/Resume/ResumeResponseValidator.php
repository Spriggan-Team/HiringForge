<?php


namespace App\Infrastructure\Services\AI\Resume;


use RuntimeException;


readonly final class ResumeResponseValidator
{
    /**
     * Validate Response
     */
    public function validate(mixed $data): array
    {
        if (!is_array($data)) {
            throw new RuntimeException(
                'Invalid AI response: expected an object.'
            );
        }

        $required = [
            'firstName',
            'lastName',
            'email',
            'phone',
            'experiences',
            'educations',
            'skills',
        ];

        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid AI response: missing field "%s".',
                        $field
                    )
                );
            }
        }

        if (
            $data['firstName'] !== null &&
            !is_string($data['firstName'])
        ) {
            throw new RuntimeException(
                'Invalid AI response: firstName must be a string or null.'
            );
        }

        if (!is_array($data['experiences'])) {
            throw new RuntimeException(
                'Invalid AI response: experiences must be an array.'
            );
        }

        if (!is_array($data['educations'])) {
            throw new RuntimeException(
                'Invalid AI response: educations must be an array.'
            );
        }

        if (!is_array($data['skills'])) {
            throw new RuntimeException(
                'Invalid AI response: skills must be an array.'
            );
        }

        return $data;
    }

}