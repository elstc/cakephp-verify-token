<?php

namespace Elastic\VerifyToken\Form;

use Cake\ORM\Entity;
use InvalidArgumentException;
use RuntimeException;

/**
 */
interface TokenValidationInterface
{

    /**
     * トークンからユーザーを取得
     *
     * @param string $token
     * @return Entity
     * @throws RuntimeException
     */
    public function getEntityByToken($token);

    /**
     * トークンの有効性確認
     *
     * @param string $token
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function validateToken($token);
}
