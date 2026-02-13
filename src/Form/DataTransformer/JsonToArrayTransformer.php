<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class JsonToArrayTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): string
    {
        if (empty($value)) {
            return '';
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function reverseTransform(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransformationFailedException('Invalid JSON: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
