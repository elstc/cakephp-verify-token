<?php

namespace Elastic\VerifyToken\Form;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Form\Schema;
use Cake\ORM\Entity;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use InvalidArgumentException;
use RuntimeException;

/**
 * トークンチェックを含むフォーム
 */
trait TokenValidatorTrait
{
    use LocatorAwareTrait;

    /**
     *
     * @var Table
     */
    protected $table;

    protected array $settings = [];

    /**
     *
     * @param string|\Cake\ORM\Table $table the table
     * @param array $options the options
     */
    public function __construct($table, $options = [])
    {
        $defaults = [
            'type' => 'email_verification',
        ];
        $this->settings = Hash::merge($defaults, $options);
        $this->setTable($table);
    }

    /**
     *
     * @param mixed $table
     */
    public function setTable($table)
    {
        if (is_subclass_of($table, '\Cake\ORM\Table')) {
            $this->table = $table;
        } else {
            $this->table = $this->getTableLocator()->get($table);
        }
    }

    /**
     *
     * @param Schema $schema
     * @return Schema
     */
    protected function appendTokenSchema(Schema $schema)
    {
        return $schema->addField('token', ['string']);
    }

    /**
     * トークンチェックの追加
     *
     * @param Validator $validator
     * @return Validator
     */
    protected function appendTokenValidator(Validator $validator)
    {
        $tableName = $this->table->getAlias();
        $type = $this->settings['type'];
        $tokenTable = TableRegistry::get('Elastic/VerifyToken.Tokens');

        $validator
            ->notEmptyString('token', __('トークンは必ず入力してください。'))
            ->add('token', 'exists', [
                'rule' => function ($value, $context) use ($tokenTable, $tableName, $type) {
                    // トークンの存在確認
                    return $tokenTable->validateToken($value, $type, $tableName);
                },
                'message' => __('無効なトークンです。'),
        ]);

        return $validator;
    }

    /**
     * トークンからユーザーを取得
     *
     * @param string $token
     * @return Entity
     * @throws RuntimeException
     */
    public function getEntityByToken($token)
    {
        $tokenTable = $this->getTableLocator()->get('Elastic/VerifyToken.Tokens');
        $foreignId = $tokenTable->getForeignIdByToken($token, $this->settings['type'], $this->table->alias());

        $user = $this->table->get($foreignId);

        if (empty($user)) {
            throw new RecordNotFoundException(__('ユーザーが見つかりません'));
        }

        return $user;
    }

    /**
     * トークンの有効性確認
     *
     * @param string $token
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function validateToken($token)
    {
        $tokenTable = $this->getTableLocator()->get('Elastic/VerifyToken.Tokens');
        $valid = $tokenTable->validateToken($token, $this->settings['type'], $this->table->alias());
        if (!$valid) {
            throw new InvalidArgumentException(__('無効なトークンです。'));
        }

        return $valid;
    }
}
