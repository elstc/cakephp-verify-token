<?php

namespace Elastic\VerifyToken\Model\Table;

use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Tokens Model
 */
class TokensTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('tokens');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new',
                    'updated_at' => 'always'
                ]
            ],
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->add('id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('table', 'create')
            ->notEmpty('table');

        $validator
            ->requirePresence('type', 'create')
            ->notEmpty('type');

        $validator
            ->allowEmpty('token');

        $validator
            ->allowEmpty('token_secret');

        $validator
            ->allowEmpty('payload');

        $validator
            ->allowEmpty('expires');

        $validator
            ->allowEmpty('created_at')
            ->allowEmpty('updated_at');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        return $rules;
    }

    /**
     * トークンが有効か確認
     *
     * @param string $token
     * @param string $type
     * @param string $table
     * @return boolean
     */
    public function validateToken($token, $type = null, $table = null)
    {
        $conditions = [
            'token' => $token,
            'expires >=' => Time::now(),
        ];
        if (!empty($type)) {
            $conditions['type'] = $type;
        }
        if (!empty($table)) {
            $conditions['table'] = $table;
        }
        $exists = $this->exists($conditions);
        return $exists;
    }

    /**
     * トークンから親idを取得
     *
     * @param string $token
     * @param string $type
     * @param string $table
     * @return string|null
     */
    public function getForeignIdByToken($token, $type = null, $table = null)
    {
        $entity = $this->getFindTokenQuery($token, $type, $table)
            ->order(['expires' => 'desc'])
            ->first();

        return ($entity) ? $entity->foreign_id : null;
    }

    /**
     * トークンを削除
     *
     * @param string $token
     * @param string $type
     * @param string $table
     * @return boolean
     */
    public function dropToken($token, $type = null, $table = null)
    {
        $entity = $this->getFindTokenQuery($token, $type, $table)
            ->order(['expires' => 'desc'])
            ->first();
        return $this->delete($entity);
    }

    /**
     * トークンからの取得用クエリ
     *
     * @param string $token
     * @param string $type
     * @param string $table
     * @return Query
     */
    private function getFindTokenQuery($token, $type = null, $table = null)
    {
        $conditions = [
            'token' => $token,
        ];
        if (!empty($type)) {
            $conditions['type'] = $type;
        }
        if (!empty($table)) {
            $conditions['table'] = $table;
        }

        $query = $this->find()->where($conditions);
        return $query;
    }
}
