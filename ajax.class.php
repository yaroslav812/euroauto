<?php
require ('pdo.class.php');

class AjaxData
{
    private $db;
    private $start_time;

    public function __construct()
    {
        $this->db = Database::getInst();
        $this->start_time = microtime(true);
    }

    /**
     * Проверка строки на число
     *
     * @param $str
     * @return bool
     */
    private function str_is_int($str)
    {
        $var = intval($str);
        return ("$str" == "$var");
    }

    /**
     * Универсальная функция для обработки $_POST данных.
     * Используется, чтобы не зависеть от настроек magic_quotes
     *
     * @param $str - данные из $_POST[]
     * @return string
     */
    public function mystrip($str)
    {
        if (get_magic_quotes_gpc()) {
            return trim(stripslashes($str));
        } else return trim($str);
    }

    /**
     * Удаление категории с ее веткой дочерних подкатегорий
     *
     * @param $id - идентификатор удаляемой категории
     */
    public function removeCategory($id)
    {
        if (!$this->str_is_int($id)) { //  проверка на SQL инъекцию
            return 0;
        }
        $sql = '
            DELETE FROM category WHERE id IN
            (
                WITH RECURSIVE tree AS (
                    SELECT
                        ' . $id . ' AS id,
                        0 AS parent_category_id

                    UNION ALL

                    SELECT
                        cat.id,
                        cat.parent_category_id
                    FROM
                        tree
                    JOIN category AS cat ON cat.parent_category_id = tree.id
                )
                SELECT id FROM tree
            );
        ';
        try {
            $affected_rows = $this->db->exec($sql);
            $time_work = (int)((microtime(true) - $this->start_time) * 1000);
            echo json_encode(array('affected' => $affected_rows, 'time' => $time_work));
        } catch (Exception $e) {
            echo json_encode(array('errmsg' => $e->getMessage()));
        }
    }

    private function runSQL_and_SendJsonTree($sql)
    {
        try {
            $dir_array = array();
            foreach ($this->db->query($sql) as $row) {
                $dir_array[] = array(
                    $row['id'],
                    $row['parent_category_id'],
                    htmlspecialchars($row['name']), // защита от XSS инъекций
                    $row['deep']
                );
            }
            /*
                [0] => id каталога
                [1] => id родительского каталога
                [2] => Имя каталога
                [3] => deep (глубина)
            */
            $time_work = (int)((microtime(true) - $this->start_time) * 1000);
            echo json_encode(array('time' => $time_work, 'dirlist' => $dir_array), JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            echo json_encode(array('errmsg' => $e->getMessage()));
        }
    }

    /**
     * Cчитываем дерево категорий и отправляем данные на клиент
     */
    public function getAllCategories()
    {
        $sql = '
            WITH RECURSIVE tree AS
            (
                SELECT root.*
                FROM (
                    SELECT
                        id,
                        0 AS parent_category_id,
                        name,
                        1 AS deep
                    FROM
                        category
                    WHERE
                        parent_category_id IS NULL
                    ORDER BY name
                ) AS root

                UNION ALL

                SELECT child.*
                FROM (
                    SELECT
                        cat.id,
                        cat.parent_category_id,
                        cat.name,
                        tree.deep + 1
                    FROM
                        tree
                    JOIN category AS cat ON cat.parent_category_id = tree.id
                    ORDER BY name
                ) AS child
            )
            SELECT * FROM tree;
        ';
        $this->runSQL_and_SendJsonTree($sql);
    }


    public function getFilteredCategories($filter) {
        $filter = $this->db->quote('%'.$filter.'%');
        $sql = '
            -- Берем все id категорий соответствующих фильтру
            WITH filter_ids AS (
                SELECT id
                FROM category
                WHERE name ILIKE '.$filter.'
            ),
            -- Составляем список id отфильтрованных категорий, которые поместим в корень
            tree_root_id AS (
                SELECT T.* FROM
                (
                    WITH RECURSIVE tree AS
                    (
                        -- Cтроим дерево категорий, отсекая лишних потомков
                        SELECT id, parent_category_id, name
                        FROM category
                        WHERE parent_category_id IS NULL

                        UNION ALL

                        SELECT cat.id, cat.parent_category_id, cat.name
                        FROM tree
                        JOIN category AS cat ON cat.parent_category_id = tree.id
                        WHERE cat.parent_category_id NOT IN (SELECT * FROM filter_ids)
                    )
                    SELECT id
                    FROM tree
                    WHERE id IN (SELECT * FROM filter_ids)
                ) AS T
            )
            SELECT T.* FROM
            (
                -- Cтроим дерево, из корневых отфильтрованных категорий
                WITH RECURSIVE tree AS
                (
                    SELECT id, parent_category_id, name, 1 AS deep
                    FROM category
                    WHERE id IN (SELECT * FROM tree_root_id)

                    UNION ALL

                    SELECT cat.id, cat.parent_category_id, cat.name, tree.deep + 1
                    FROM tree
                    JOIN category AS cat ON cat.parent_category_id = tree.id
                )
                SELECT * FROM tree
            )  AS T
        ';
        $this->runSQL_and_SendJsonTree($sql);
    }
}




