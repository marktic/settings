<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMktSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $tableName = 'mkt_settings';

        if ($this->hasTable($tableName)) {
            return;
        }

        $this->table($tableName, ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'biginteger', ['identity' => true, 'signed' => false])
            ->addColumn('name', 'string', ['limit' => 191])
            ->addColumn('group', 'string', ['limit' => 100, 'default' => 'default'])
            ->addColumn('value', 'text', ['null' => true])
            ->addColumn('type', 'string', ['limit' => 20, 'default' => 'string'])
            ->addColumn('tenant_type', 'string', ['limit' => 191, 'null' => true])
            ->addColumn('tenant_id', 'biginteger', ['signed' => false, 'null' => true])
            ->addTimestamps()
            ->addIndex(['name', 'group', 'tenant_type', 'tenant_id'], ['unique' => true, 'name' => 'idx_settings_unique'])
            ->addIndex(['tenant_type', 'tenant_id'], ['name' => 'idx_settings_tenant'])
            ->create();
    }
}
