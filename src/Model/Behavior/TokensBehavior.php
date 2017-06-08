<?php

namespace Elastic\VerifyToken\Model\Behavior;

use Cake\I18n\FrozenTime;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Elastic\VerifyToken\Model\Entity\Token;

/**
 * Tokens behavior
 */
class TokensBehavior extends Behavior
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    public function initialize(array $config)
    {
        parent::initialize($config);
        // attach token
        $this->_table->hasMany('Tokens', [
            'className' => 'Elastic/VerifyToken.Tokens',
            'foreignKey' => 'foreign_id',
            'conditions' => [
                'table' => $this->_table->alias(),
            ],
            'sort' => ['expires' => 'desc'],
            'dependent' => true,
            'propertyName' => 'tokens',
        ]);
    }

    /**
     *
     * @param Entity $entity user entity
     * @param string $type
     */
    public function generateToken($entity, $type)
    {
        if (!$entity->has($type)) {
            $token = $this->_table->Tokens->newEntity();
            $token->table = $this->_table->alias();
            $token->type = $type;
            $token->foreign_id = $entity->id;
        } else {
            $token = $entity->$type;
        }
        /* @var $token Token */
        $token->expires = FrozenTime::now()->addHours(24);
        $token->token = $token->generateToken();

        $entity->$type = $token;
        return $token;
    }
}
