<?php

namespace app\services\validation;

use app\services\validation\exceptions\TestSelValidateException;
use PHPSQLParser\PHPSQLCreator;
use PHPSQLParser\PHPSQLParser;

class TestSelValidationService
{
    private const LIMIT_OP = 'LIMIT';
    private const MAX_LIMIT = 20000;

    private $blockKeywords = [
        'update',
        'delete',
        'insert',
        'replace',
        'call',
        'do',
        'alter',
        'like',
    ];

    /**
     * Проверяет sql запрос если есть ошибки вызывается TestSelValidateException
     *
     * @param string $sql
     * @return string
     * @throws TestSelValidateException
     */
    public function validateSql(string $sql): string
    {
        // // $this->checkBlockKeywords($sql);

        $tree = $this->parseSql($sql);
        $tree = $this->handleLimit($tree);

        try {
            $processedSql = $this->toSql($tree);
        } catch (\Exception $e) {
            throw new TestSelValidateException('Не получилось сформировать SQL запрос', 0, $e);
        }

        return $processedSql;
    }

    /**
     * Ищет в sql запрещенные слова, если найдены, то выкидывает исключение
     *
     * @param string $sql
     * @return void
     * @throws TestSelValidateException
     */
    private function checkBlockKeywords(string $sql)
    {
        foreach ($this->blockKeywords as $blockKeyword) {
            if (preg_match('/(\s|\W|^)' . $blockKeyword . '(\s|\W|$)/i', $sql)) {
                throw new TestSelValidateException('Запрещенное слово: ' . $blockKeyword);
            }
        }
    }

    /**
     * Устанавливает или ограничивает лимит в запросе
     *
     * @param array $tree
     * @return array
     */
    private function handleLimit(array $tree): array
    {
        if (isset($tree[self::LIMIT_OP])) {
            // Если лимит установлен и больше максимума, то ограничиваем

            $limit = $tree[self::LIMIT_OP]['rowcount'];
            if ($limit > self::MAX_LIMIT) {
                $tree[self::LIMIT_OP]['rowcount'] = self::MAX_LIMIT;
            }
        } else {
            // Если лимит не установлен, то ставим максимальный лимит

            $tree[self::LIMIT_OP] = [
                'offset' => '',
                'rowcount' => self::MAX_LIMIT,
            ];
        }

        return $tree;
    }

    /**
     * Разбивает sql запрос в дерево
     *
     * @param string $sql
     * @return array
     */
    private function parseSql(string $sql): array
    {
        $parser = new PHPSQLParser();
        return $parser->parse($sql);
    }

    /**
     * Преобразовывает дерево в sql запрос
     *
     * @param array $tree
     * @return string
     * @throws \PHPSQLParser\exceptions\UnsupportedFeatureException
     */
    private function toSql(array $tree): string
    {
        $creator = new PHPSQLCreator();
        return $creator->create($tree);
    }
}
