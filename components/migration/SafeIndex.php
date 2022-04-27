<?php

namespace app\components\migration;

use yii\db\Migration;

/**
 * @mixin Migration
 */
trait SafeIndex
{
    /**
     * @param string $name
     * @param string $table
     * @throws \yii\db\Exception
     * @see Migration::dropIndex()
     */
    public function dropIndexIfExists(string $name, string $table)
    {
        $time = $this->beginCommand("drop index if exists {$name} on {$table}");

        $nameQuoted = $this->db->quoteTableName($name);
        $tableQuoted = $this->db->quoteTableName($table);
        $this->db
            ->createCommand("DROP INDEX IF EXISTS {$nameQuoted} ON {$tableQuoted}")
            ->execute();

        $this->endCommand($time);

        // refresh schema
        $this->db->getTableSchema($table, true);
    }

    /**
     * @param string $name
     * @param string $table
     * @param string|array $columns
     * @param bool $unique
     * @return void
     * @throws \yii\db\Exception
     * @see Migration::createIndex()
     */
    public function createIndexIfNotExists(string $name, string $table, $columns, bool $unique = false)
    {
        $time = $this->beginCommand(
            'create' . ($unique ? ' unique' : '')
            . " index if not exists $name on $table (" . implode(',', (array)$columns) . ')'
        );

        $nameQuoted = $this->db->quoteTableName($name);
        $tableQuoted = $this->db->quoteTableName($table);
        $uniqueSql = $unique ? ' UNIQUE' : '';
        $columnsSql = $this->db->getQueryBuilder()->buildColumns($columns);
        $this->db
            ->createCommand("CREATE{$uniqueSql} INDEX IF NOT EXISTS {$nameQuoted} ON {$tableQuoted} ({$columnsSql})")
            ->execute();

        $this->endCommand($time);

        // refresh schema
        $this->db->getTableSchema($table, true);
    }
}