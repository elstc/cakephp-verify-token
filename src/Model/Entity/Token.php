<?php

namespace Elastic\VerifyToken\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use Cake\Utility\Security;

/**
 * Token Entity.
 *
 * @property string $table
 * @property string $type
 * @property string $foreign_id
 * @property string $token
 * @property string $token_secret
 * @property string $payload
 * @property FrozenTime $expires
 * @property FrozenTime $created_at
 * @property FrozenTime $updated_at
 */
class Token extends Entity
{

    protected $_accessible = [
        'table' => true,
        'type' => true,
        'foreign_id' => true,
        'token' => true,
        'token_secret' => true,
        'payload' => true,
        'expires' => true,
    ];

    protected $_hidden = ['token', 'token_secret'];

    /**
     *
     * @return string
     */
    public function generateToken()
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
        } else {
            $token = Security::hash(uniqid($this->type, true));
        }

        return $token;
    }
}
