<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddNamespaceToMktSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $tableName = 'mkt_settings-settings';

        if (!$this->hasTable($tableName)) {
            return;
        }

        $table = $this->table($tableName);

        if (!$table->hasColumn('namespace')) {
            $table->addColumn('namespace', 'string', ['limit' => 100, 'null' => true, 'after' => 'group'])
                ->save();
        }
    }
}
