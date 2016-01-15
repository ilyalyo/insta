<?php

namespace ApiBundle\Serializer\Normalizer;

use AppBundle\Entity\Accounts;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * User normalizer
 */
class AccountNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'id'     => $object->getId(),
            'username'   => $object->getUsername(),
            'picture'   => $object->getPicture(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Accounts;
    }
}