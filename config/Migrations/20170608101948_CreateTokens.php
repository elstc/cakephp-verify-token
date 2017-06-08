<?php

use Migrations\AbstractMigration;

class CreateTokens extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('tokens');
        $table->addTimestamps();

        $table->addColumn('table', 'string', [
            'default' => null,
            'limit' => 64,
            'null' => false,
        ]);
        $table->addColumn('type', 'string', [
            'default' => null,
            'limit' => 64,
            'null' => false,
        ]);
        $table->addColumn('foreign_id', 'string', [
            'default' => null,
            'limit' => 36,
            'null' => false,
        ]);
        $table->addColumn('token', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('token_secret', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
        ]);
        $table->addColumn('expires', 'timestamp', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('payload', 'text', [
            'default' => null,
            'null' => true,
        ]);

        // 同じタイプのトークンは重複しない
        $table->addIndex(['table', 'type', 'token'], ['unique' => true, 'name' => 'U_token_identifier']);
        // 一つのオブジェクトに対して、同じタイプのトークンは作成させない
        $table->addIndex(['table', 'type', 'foreign_id'], ['unique' => true, 'name' => 'U_token_user_identifier']);
        $table->addIndex(['table', 'type'], ['name' => 'IX_token_type']);
        $table->create();
    }

    public function down()
    {
        $this->table('tokens')->drop();
    }
}
