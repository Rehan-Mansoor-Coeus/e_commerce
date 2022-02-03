<?php
namespace App\Serializer;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MyCustomProblemNormalizer implements NormalizerInterface
{
    /**
     * @param $exception
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function normalize($exception, string $format = null, array $context = [])
    {
        return [
            'content' => 'This is my custom problem normalizer.',
            'exception'=> [
                'message' => $exception->getMessage(),
                'code' => $exception->getStatusCode(),
            ],
        ];
    }

    /**
     * @param $data
     * @param string|null $format
     * @return bool
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof FlattenException;
    }
}