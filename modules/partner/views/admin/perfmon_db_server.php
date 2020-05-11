<?php
/* @var $mysql \yii\db\Query */
ini_set('max_execution_time',3000);

$this->title = 'SQL';
$this->params['breadtitle'] = 'SQL';


$vars = array();
$rs = $mysql->createCommand("SHOW GLOBAL VARIABLES")->query();
while ($ar = $rs->read())
    $vars[$ar["Variable_name"]] = $ar["Value"];

$stat = array();
$rs = $mysql->createCommand("SHOW GLOBAL STATUS")->query();
if (!$rs)
    $rs = $mysql->createCommand("SHOW STATUS")->query();
while ($ar = $rs->read())
    $stat[$ar["Variable_name"]] = $ar["Value"];

$data = array(
    array(
        "TITLE" => GetMessage("PERFMON_STATUS_TITLE"),
        "HEADERS" => array(
            array(
                "id" => "KPI_NAME",
                "content" => GetMessage("PERFMON_KPI_NAME"),
                "align" => "left\" nowrap=\"nowrap",
                "default" => true,
            ),
            array(
                "id" => "KPI_VALUE",
                "content" => GetMessage("PERFMON_KPI_VALUE"),
                "align" => "right\" nowrap=\"nowrap",
                "default" => true,
            ),
            array(
                "id" => "KPI_RECOMMENDATION",
                "content" => GetMessage("PERFMON_KPI_RECOMENDATION"),
                "default" => true,
            ),
        ),
        "ITEMS" => array(),
    )
);

$arVersion = array();
if (preg_match("/^(\\d+)\\.(\\d+)/", $vars["version"], $arVersion)) {
    if ($arVersion[1] < 5)
        $rec = GetMessage("PERFMON_KPI_REC_VERSION_OLD");
    elseif ($arVersion[1] == 5)
        $rec = GetMessage("PERFMON_KPI_REC_VERSION_OK");
    else
        $rec = GetMessage("PERFMON_KPI_REC_VERSION_NEW");
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_VERSION"),
        "IS_OK" => $arVersion[1] == 5,
        "KPI_VALUE" => $vars["version"],
        "KPI_RECOMMENDATION" => $rec,
    );
}
$uptime = array(
    "#SECONDS#" => $stat['Uptime'] % 60,
    "#MINUTES#" => intval(($stat['Uptime'] % 3600) / 60),
    "#HOURS#" => intval(($stat['Uptime'] % 86400) / (3600)),
    "#DAYS#" => intval($stat['Uptime'] / (86400)),
);

if ($stat['Uptime'] >= 86400)
    $rec = GetMessage("PERFMON_KPI_REC_UPTIME_OK");
else
    $rec = GetMessage("PERFMON_KPI_REC_UPTIME_TOO_SHORT");
$data[0]["ITEMS"][] = array(
    "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_UPTIME"),
    "IS_OK" => $stat['Uptime'] >= 86400,
    "KPI_VALUE" => GetMessage("PERFMON_KPI_VAL_UPTIME", $uptime),
    "KPI_RECOMMENDATION" => $rec,
);

if ($stat["Questions"] < 1) {
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_QUERIES"),
        "IS_OK" => false,
        "KPI_VALUE" => $stat["Questions"],
        "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_NO_QUERIES"),
    );
} else {
    // Server-wide memory
    $calc['server_buffers'] = $vars['key_buffer_size'];
    $server_buffers = 'key_buffer_size';
    if ($vars['tmp_table_size'] > $vars['max_heap_table_size']) {
        $calc['server_buffers'] += $vars['max_heap_table_size'];
        $server_buffers .= ' + max_heap_table_size';
    } else {
        $calc['server_buffers'] += $vars['tmp_table_size'];
        $server_buffers .= ' + tmp_table_size';
    }

    if (isset($vars['innodb_buffer_pool_size'])) {
        $calc['server_buffers'] += $vars['innodb_buffer_pool_size'];
        $server_buffers .= ' + innodb_buffer_pool_size';
    }

    if (isset($vars['innodb_additional_mem_pool_size'])) {
        $calc['server_buffers'] += $vars['innodb_additional_mem_pool_size'];
        $server_buffers .= ' + innodb_additional_mem_pool_size';
    }

    if (isset($vars['innodb_log_buffer_size'])) {
        $calc['server_buffers'] += $vars['innodb_log_buffer_size'];
        $server_buffers .= ' + innodb_log_buffer_size';
    }

    if (isset($vars['query_cache_size'])) {
        $calc['server_buffers'] += $vars['query_cache_size'];
        $server_buffers .= ' + query_cache_size';
    }
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_GBUFFERS"),
        "KPI_VALUE" => FormatSize($calc['server_buffers']),
        "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_GBUFFERS", array("#VALUE#" => "<span class=\"perfmon_code\">" . $server_buffers . "</span>")),
    );

    // Per thread
    $calc['per_thread_buffers'] = $vars['read_buffer_size'] + $vars['read_rnd_buffer_size'] + $vars['sort_buffer_size'] + $vars['thread_stack'] + $vars['join_buffer_size'];
    $per_thread_buffers = 'read_buffer_size + read_rnd_buffer_size + sort_buffer_size + thread_stack + join_buffer_size';
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_CBUFFERS"),
        "KPI_VALUE" => FormatSize($calc['per_thread_buffers']),
        "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_CBUFFERS", array("#VALUE#" => "<span class=\"perfmon_code\">" . $per_thread_buffers . "</span>")),
    );

    $max_connections = 'max_connections';
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_CONNECTIONS"),
        "KPI_VALUE" => $vars['max_connections'],
        "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_CONNECTIONS", array("#VALUE#" => "<span class=\"perfmon_code\">" . $max_connections . "</span>")),
    );

    // Global memory
    $calc['total_possible_used_memory'] = $calc['server_buffers'] + ($calc['per_thread_buffers'] * $vars['max_connections']);
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_MEMORY"),
        "KPI_VALUE" => FormatSize($calc['total_possible_used_memory']),
        "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_MEMORY"),
    );

    // Key buffers
    $total_myisam_indexes = 0;
    if ($arVersion[1] >= 5) {
        $ar = $mysql->createCommand("SELECT IFNULL(SUM(INDEX_LENGTH),0) IND_SIZE FROM information_schema.TABLES WHERE TABLE_SCHEMA NOT IN ('information_schema') AND ENGINE = 'MyISAM'")->queryOne();
        if ($ar["IND_SIZE"] > 0) {
            $total_myisam_indexes = $ar["IND_SIZE"];
            $calc['total_myisam_indexes'] = FormatSize($ar["IND_SIZE"]);
            $rec = GetMessage("PERFMON_KPI_REC_MYISAM_IND");
        } else {
            $calc['total_myisam_indexes'] = GetMessage("PERFMON_KPI_NO");
            $rec = GetMessage("PERFMON_KPI_REC_MYISAM_NOIND");
        }
    } else {
        $calc['total_myisam_indexes'] = '<span class="errortext">N/A</span>';
        $rec = GetMessage("PERFMON_KPI_REC_MYISAM4_IND");
    }
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_MYISAM_IND"),
        "KPI_VALUE" => $calc['total_myisam_indexes'],
        "KPI_RECOMMENDATION" => $rec,
    );

    if ($total_myisam_indexes > 0) {
        if ($stat['Key_read_requests'] > 0)
            $calc['pct_keys_from_disk'] = round($stat['Key_reads'] / $stat['Key_read_requests'] * 100, 2);
        else
            $calc['pct_keys_from_disk'] = 0;
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_KEY_MISS"),
            "IS_OK" => $calc['pct_keys_from_disk'] <= 5,
            "KPI_VALUE" => $calc['pct_keys_from_disk'] . "%",
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_KEY_MISS", array(
                "#PARAM_VALUE#" => FormatSize($vars["key_buffer_size"]),
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">key_buffer_size</span>",
            )),
        );
    }

    // Query cache
    if ($vars['query_cache_size'] < 1)
        $rec = GetMessage("PERFMON_KPI_REC_QCACHE_ZERO_SIZE", array(
            "#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_size</span>",
            "#PARAM_VALUE_LOW#" => "8M",
            "#PARAM_VALUE_HIGH#" => "128M",
        ));
    elseif ($vars['query_cache_size'] > 128 * 1024 * 1024)
        $rec = GetMessage("PERFMON_KPI_REC_QCACHE_TOOLARGE_SIZE", array(
            "#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_size</span>",
            "#PARAM_VALUE_HIGH#" => "128M",
        ));
    else
        $rec = GetMessage("PERFMON_KPI_REC_QCACHE_OK_SIZE", array(
            "#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_size</span>",
        ));
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_QCACHE_SIZE"),
        "IS_OK" => $vars['query_cache_size'] > 0 && $vars['query_cache_size'] <= 128 * 1024 * 1024,
        "KPI_VALUE" => FormatSize($vars['query_cache_size']),
        "KPI_RECOMMENDATION" => $rec,
    );

    if ($vars['query_cache_size'] > 0 && (($stat['Com_select'] - $stat['Qcache_not_cached']) + $stat['Qcache_hits']) > 0) {
        if ($stat['Com_select'] == 0) {
            $value = "&nbsp;";
            $rec = GetMessage("PERFMON_KPI_REC_QCACHE_NO");
        } else {
            $calc['query_cache_efficiency'] = round($stat['Qcache_hits'] / (($stat['Com_select'] - $stat['Qcache_not_cached']) + $stat['Qcache_hits']) * 100, 2);
            $value = $calc['query_cache_efficiency'] . "%";
            $rec = GetMessage("PERFMON_KPI_REC_QCACHE", array(
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_limit</span>",
                "#PARAM_VALUE#" => FormatSize($vars['query_cache_limit']),
                "#GOOD_VALUE#" => "20%",
            ));
        }
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_QCACHE"),
            "IS_OK" => $stat['Com_select'] > 0 && $calc['query_cache_efficiency'] >= 20,
            "KPI_VALUE" => $value,
            "KPI_RECOMMENDATION" => $rec,
        );

        if ($stat['Com_select'] > 0) {
            $data[0]["ITEMS"][] = array(
                "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_QCACHE_PRUNES"),
                "KPI_VALUE" => perfmon_NumberFormat($stat['Qcache_lowmem_prunes'], 0),
                "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_QCACHE_PRUNES", array(
                    "#STAT_NAME#" => "<span class=\"perfmon_code\">Qcache_lowmem_prunes</span>",
                    "#PARAM_VALUE#" => FormatSize($vars['query_cache_size']),
                    "#PARAM_NAME#" => "<span class=\"perfmon_code\">query_cache_size</span>",
                    "#PARAM_VALUE_HIGH#" => "128M",
                )),
            );
        }
    }
    // Sorting
    $calc['total_sorts'] = $stat['Sort_scan'] + $stat['Sort_range'];
    $total_sorts = 'Sort_scan + Sort_range';
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_SORTS"),
        "KPI_VALUE" => perfmon_NumberFormat($calc['total_sorts'], 0),
        "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_SORTS", array(
            "#STAT_NAME#" => "<span class=\"perfmon_code\">" . $total_sorts . "</span>",
        )),
    );

    if ($calc['total_sorts'] > 0) {
        $calc['pct_temp_sort_table'] = round(($stat['Sort_merge_passes'] / $calc['total_sorts']) * 100, 2);
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_SORTS_DISK"),
            "IS_OK" => $calc['pct_temp_sort_table'] <= 10,
            "KPI_VALUE" => $calc['pct_temp_sort_table'] . "%",
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_SORTS_DISK", array(
                "#STAT_NAME#" => "<span class=\"perfmon_code\">Sort_merge_passes / (Sort_scan + Sort_range)</span>",
                "#GOOD_VALUE#" => "10",
                "#PARAM1_VALUE#" => FormatSize($vars['sort_buffer_size']),
                "#PARAM1_NAME#" => "<span class=\"perfmon_code\">sort_buffer_size</span>",
                "#PARAM2_VALUE#" => FormatSize($vars['read_rnd_buffer_size']),
                "#PARAM2_NAME#" => "<span class=\"perfmon_code\">read_rnd_buffer_size</span>",
            )),
        );
    }

    // Joins
    $calc['joins_without_indexes'] = $stat['Select_range_check'] + $stat['Select_full_join'];
    $calc['joins_without_indexes_per_day'] = intval($calc['joins_without_indexes'] / ($stat['Uptime'] / 86400));
    if ($calc['joins_without_indexes_per_day'] > 250) {
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_JOINS"),
            "KPI_VALUE" => perfmon_NumberFormat($calc['joins_without_indexes'], 0),
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_JOINS", array(
                "#STAT_NAME#" => "<span class=\"perfmon_code\">Select_range_check + Select_full_join</span>",
                "#PARAM_VALUE#" => FormatSize($vars['join_buffer_size']),
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">join_buffer_size</span>",
            )),
        );
    }

    // Temporary tables
    if ($stat['Created_tmp_tables'] > 0) {
        $calc['tmp_table_size'] = ($vars['tmp_table_size'] > $vars['max_heap_table_size']) ? $vars['max_heap_table_size'] : $vars['tmp_table_size'];
        if ($stat['Created_tmp_disk_tables'] > 0)
            $calc['pct_temp_disk'] = round(($stat['Created_tmp_disk_tables'] / ($stat['Created_tmp_tables'] + $stat['Created_tmp_disk_tables'])) * 100, 2);
        else
            $calc['pct_temp_disk'] = 0;
        $pct_temp_disk = 30;

        if ($calc['pct_temp_disk'] > $pct_temp_disk && $calc['tmp_table_size'] < 256 * 1024 * 1024) {
            $is_ok = false;
            $value = $calc['pct_temp_disk'] . "%";
            $rec = GetMessage("PERFMON_KPI_REC_TMP_DISK_1", array(
                "#STAT_NAME#" => "<span class=\"perfmon_code\">Created_tmp_disk_tables / (Created_tmp_tables + Created_tmp_disk_tables)</span>",
                "#STAT_VALUE#" => $pct_temp_disk . "%",
                "#PARAM1_NAME#" => "<span class=\"perfmon_code\">tmp_table_size</span>",
                "#PARAM1_VALUE#" => FormatSize($vars['tmp_table_size']),
                "#PARAM2_NAME#" => "<span class=\"perfmon_code\">max_heap_table_size</span>",
                "#PARAM2_VALUE#" => FormatSize($vars['max_heap_table_size']),
            ));
        } elseif ($calc['pct_temp_disk'] > $pct_temp_disk && $calc['tmp_table_size'] >= 256) {
            $is_ok = false;
            $value = $calc['pct_temp_disk'] . "%";
            $rec = GetMessage("PERFMON_KPI_REC_TMP_DISK_2", array(
                "#STAT_NAME#" => "<span class=\"perfmon_code\">Created_tmp_disk_tables / (Created_tmp_tables + Created_tmp_disk_tables)</span>",
                "#STAT_VALUE#" => $pct_temp_disk . "%",
            ));
        } else {
            $is_ok = true;
            $value = $calc['pct_temp_disk'] . "%";
            $rec = GetMessage("PERFMON_KPI_REC_TMP_DISK_3", array(
                "#STAT_NAME#" => "<span class=\"perfmon_code\">Created_tmp_disk_tables / (Created_tmp_tables + Created_tmp_disk_tables)</span>",
                "#STAT_VALUE#" => $pct_temp_disk . "%",
            ));
        }
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_TMP_DISK"),
            "IS_OK" => $is_ok,
            "KPI_VALUE" => $value,
            "KPI_RECOMMENDATION" => $rec,
        );
    }

    // Thread cache
    if ($vars['thread_cache_size'] == 0) {
        $is_ok = false;
        $value = $vars['thread_cache_size'];
        $rec = GetMessage("PERFMON_KPI_REC_THREAD_NO_CACHE", array(
            "#PARAM_VALUE#" => 4,
            "#PARAM_NAME#" => "<span class=\"perfmon_code\">thread_cache_size</span>",
        ));
    } else {
        $calc['thread_cache_hit_rate'] = round(100 - (($stat['Threads_created'] / $stat['Connections']) * 100), 2);
        $is_ok = $calc['thread_cache_hit_rate'] > 50;
        $value = $calc['thread_cache_hit_rate'] . "%";
        $rec = GetMessage("PERFMON_KPI_REC_THREAD_CACHE", array(
            "#STAT_NAME#" => "<span class=\"perfmon_code\">1 - Threads_created / Connections</span>",
            "#GOOD_VALUE#" => "50%",
            "#PARAM_VALUE#" => $vars['thread_cache_size'],
            "#PARAM_NAME#" => "<span class=\"perfmon_code\">thread_cache_size</span>",
        ));
    }
    $data[0]["ITEMS"][] = array(
        "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_THREAD_CACHE"),
        "IS_OK" => $is_ok,
        "KPI_VALUE" => $value,
        "KPI_RECOMMENDATION" => $rec,
    );

    // Table cache
    if ($stat['Open_tables'] > 0) {
        if ($stat['Opened_tables'] > 0)
            $calc['table_cache_hit_rate'] = round($stat['Open_tables'] / $stat['Opened_tables'] * 100, 2);
        else
            $calc['table_cache_hit_rate'] = 100;
        if (array_key_exists('table_cache', $vars))
            $table_cache = 'table_cache';
        else
            $table_cache = 'table_open_cache';
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_TABLE_CACHE"),
            "IS_OK" => $calc['table_cache_hit_rate'] >= 20,
            "KPI_VALUE" => $calc['table_cache_hit_rate'] . "%",
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_TABLE_CACHE", array(
                "#STAT_NAME#" => "<span class=\"perfmon_code\">Open_tables / Opened_tables</span>",
                "#GOOD_VALUE#" => "20%",
                "#PARAM_VALUE#" => $vars[$table_cache],
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">" . $table_cache . "</span>",
            )),
        );
    }

    // Open files
    if ($vars['open_files_limit'] > 0) {
        $calc['pct_files_open'] = round($stat['Open_files'] / $vars['open_files_limit'] * 100, 2);
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_OPEN_FILES"),
            "IS_OK" => $calc['pct_files_open'] <= 85,
            "KPI_VALUE" => $calc['pct_files_open'] . "%",
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_OPEN_FILES", array(
                "#STAT_NAME#" => "<span class=\"perfmon_code\">Open_files / open_files_limit</span>",
                "#GOOD_VALUE#" => "85%",
                "#PARAM_VALUE#" => $vars['open_files_limit'],
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">open_files_limit</span>",
            )),
        );
    }

    // Table locks
    if ($stat['Table_locks_immediate'] > 0) {
        if ($stat['Table_locks_waited'] == 0)
            $calc['pct_table_locks_immediate'] = 100;
        else
            $calc['pct_table_locks_immediate'] = round($stat['Table_locks_immediate'] / ($stat['Table_locks_waited'] + $stat['Table_locks_immediate']) * 100, 2);
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_LOCKS"),
            "KPI_VALUE" => (
            $calc['pct_table_locks_immediate'] >= 95 ?
                $calc['pct_table_locks_immediate'] . "%" :
                '<span class="errortext">' . $calc['pct_table_locks_immediate'] . '%</span>'
            ),
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_LOCKS", array(
                "#STAT_NAME#" => "<span class=\"perfmon_code\">Table_locks_immediate / (Table_locks_waited + Table_locks_immediate)</span>",
                "#GOOD_VALUE#" => "95%",
            )),
        );
    }

    // Performance options
    if ($vars['concurrent_insert'] == "OFF") {
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_INSERTS"),
            "KPI_VALUE" => "<span class=\"errortext\">OFF</span>",
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INSERTS", array(
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">concurrent_insert</span>",
                "#REC_VALUE#" => "'ON'",
            )),
        );
    } elseif ($vars['concurrent_insert'] == "0") {
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_INSERTS"),
            "KPI_VALUE" => "<span class=\"errortext\">0</span>",
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INSERTS", array(
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">concurrent_insert</span>",
                "#REC_VALUE#" => "1",
            )),
        );
    }

    // Aborted connections
    if ($stat['Connections'] > 0) {
        $calc['pct_aborted_connections'] = round(($stat['Aborted_connects'] / $stat['Connections']) * 100, 2);
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_CONN_ABORTS"),
            "IS_OK" => $calc['pct_aborted_connections'] <= 5,
            "KPI_VALUE" => $calc['pct_aborted_connections'] . "%",
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_CONN_ABORTS"),
        );
    }

    // InnoDB
    if (/*$vars['have_innodb'] == "YES"*/true) {
        if ($stat['Innodb_buffer_pool_reads'] > 0 && $stat['Innodb_buffer_pool_read_requests'] > 0) {
            $calc['innodb_buffer_hit_rate'] = round((1 - $stat['Innodb_buffer_pool_reads'] / $stat['Innodb_buffer_pool_read_requests']) * 100, 2);
            $data[0]["ITEMS"][] = array(
                "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_INNODB_BUFFER"),
                "IS_OK" => $calc['innodb_buffer_hit_rate'] > 95,
                "KPI_VALUE" => $calc['innodb_buffer_hit_rate'] . "%",
                "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INNODB_BUFFER", array(
                    "#STAT_NAME#" => "<span class=\"perfmon_code\">1 - Innodb_buffer_pool_reads / Innodb_buffer_pool_read_requests</span>",
                    "#GOOD_VALUE#" => 95,
                    "#PARAM_NAME#" => "<span class=\"perfmon_code\">innodb_buffer_pool_size</span>",
                    "#PARAM_VALUE#" => FormatSize($vars['innodb_buffer_pool_size']),
                )),
            );
        }
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => "innodb_flush_log_at_trx_commit",
            "IS_OK" => $vars['innodb_flush_log_at_trx_commit'] == 2 || $vars['innodb_flush_log_at_trx_commit'] == 0,
            "KPI_VALUE" => strlen($vars['innodb_flush_log_at_trx_commit']) ? $vars['innodb_flush_log_at_trx_commit'] : GetMessage("PERFMON_KPI_EMPTY"),
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INNODB_FLUSH_LOG", array(
                "#GOOD_VALUE#" => 2,
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">innodb_flush_log_at_trx_commit</span>",
            )),
        );
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => "sync_binlog",
            "IS_OK" => $vars['sync_binlog'] == 0 || $vars['sync_binlog'] >= 1000,
            "KPI_VALUE" => intval($vars['sync_binlog']),
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_SYNC_BINLOG", array(
                "#GOOD_VALUE_1#" => 0,
                "#GOOD_VALUE_2#" => 1000,
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">sync_binlog</span>",
            )),
        );
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => "innodb_flush_method",
            "IS_OK" => $vars['innodb_flush_method'] == "O_DIRECT",
            "KPI_VALUE" => strlen($vars['innodb_flush_method']) ? $vars['innodb_flush_method'] : GetMessage("PERFMON_KPI_EMPTY"),
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INNODB_FLUSH_METHOD", array(
                "#GOOD_VALUE#" => "O_DIRECT",
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">innodb_flush_method</span>",
            )),
        );
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => "transaction-isolation",
            "IS_OK" => $vars['tx_isolation'] == "READ-COMMITTED",
            "KPI_VALUE" => strlen($vars['tx_isolation']) ? $vars['tx_isolation'] : GetMessage("PERFMON_KPI_EMPTY"),
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_TX_ISOLATION", array(
                "#GOOD_VALUE#" => "READ-COMMITTED",
                "#PARAM_NAME#" => "<span class=\"perfmon_code\">transaction-isolation</span>",
            )),
        );
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_INNODB_LOG_WAITS"),
            "KPI_VALUE" => $stat["Innodb_log_waits"],
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_INNODB_LOG_WAITS", array("#VALUE#" => FormatSize($vars["innodb_log_file_size"]))),
        );
        $data[0]["ITEMS"][] = array(
            "KPI_NAME" => GetMessage("PERFMON_KPI_NAME_BINLOG"),
            "KPI_VALUE" => $stat["Binlog_cache_disk_use"],
            "KPI_RECOMMENDATION" => GetMessage("PERFMON_KPI_REC_BINLOG", array("#VALUE#" => FormatSize($vars["binlog_cache_size"]))),
        );
    }
}
echo $data[0]['TITLE'];
echo '<table border=1>';
echo '<tr>';
foreach ($data[0]['HEADERS'] AS $line) {
    echo '<td>' . $line['content'] . '</td>';
}
echo '</tr>';
foreach ($data[0]['ITEMS'] AS $line) {
    $color = 'black';
    if (isset($line['IS_OK']) && $line['IS_OK'] == 0) {
        $color = 'red';
    }
    echo '<tr><td>' . $line['KPI_NAME'] . '</td><td style="color: ' . $color . '">' . $line['KPI_VALUE'] . '</td><td>' . $line['KPI_RECOMMENDATION'] . '</td></tr>';
}
echo '</table>';

function GetMessage($id, $param = null)
{
    require 'perfmon_db_server_msg.php';
    @$mess = $MESS[$id];
    if (is_array($param) && count($param) > 0) {
        foreach ($param as $key => $par) {
            $mess = str_replace($key, $par, $mess);
        }
    }
    return $mess;
}

function FormatSize($bytes, $precision = 2)
{
    $unit = ["B", "KB", "MB", "GB"];
    $exp = floor(log($bytes, 1024)) | 0;
    return round($bytes / (pow(1024, $exp)), $precision) . ' ' . $unit[$exp];
}

function perfmon_NumberFormat($str)
{
    return $str;
}
