<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A parent account can own many child accounts, so writers.parent_user_id must not be unique.
 * Some databases ended up with a UNIQUE index on it (e.g. via an SQL import), which makes a
 * Reseller's second "Add Account" fail with "Duplicate entry ... for key 'writers.parent_user_id'".
 * This drops any unique index on the column and makes sure a normal index exists.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('writers') || !Schema::hasColumn('writers', 'parent_user_id')) {
            return;
        }

        foreach ($this->uniqueIndexesOnColumn() as $indexName) {
            DB::statement('ALTER TABLE `writers` DROP INDEX `' . str_replace('`', '``', $indexName) . '`');
        }

        if (!$this->hasPlainIndex()) {
            DB::statement('ALTER TABLE `writers` ADD INDEX `writers_parent_user_id_index` (`parent_user_id`)');
        }
    }

    public function down()
    {
        // Intentionally not restoring the UNIQUE index: it was the bug.
    }

    /** Unique, non-primary indexes whose only column is parent_user_id. */
    private function uniqueIndexesOnColumn(): array
    {
        return collect($this->indexes())
            ->filter(function ($columns, $name) {
                return $name !== 'PRIMARY'
                    && $columns['unique']
                    && $columns['columns'] === ['parent_user_id'];
            })
            ->keys()
            ->all();
    }

    private function hasPlainIndex(): bool
    {
        return collect($this->indexes())->contains(function ($index) {
            return !$index['unique'] && ($index['columns'][0] ?? null) === 'parent_user_id';
        });
    }

    /** @return array<string, array{unique: bool, columns: string[]}> */
    private function indexes(): array
    {
        $indexes = [];
        foreach (DB::select('SHOW INDEX FROM `writers`') as $row) {
            $indexes[$row->Key_name]['unique'] = (int) $row->Non_unique === 0;
            $indexes[$row->Key_name]['columns'][(int) $row->Seq_in_index - 1] = $row->Column_name;
        }
        foreach ($indexes as &$index) {
            ksort($index['columns']);
            $index['columns'] = array_values($index['columns']);
        }

        return $indexes;
    }
};
