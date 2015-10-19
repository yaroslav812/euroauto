<?php
require ('pdo.class.php');

class AjaxData
{
    private $db;

    public function __construct(){
        $this->db = Database::getInst();
    }

    public function removeCategory($id){
        if(!is_int($id)){ //  проверка на SQL инъекцию
            return 0;
        }
        // удаляем категорию + все ее дочерние категории
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
            )
        ';
        try {
            echo json_encode(array('deleted'=>$this->db->exec($sql)));
        } catch(Exception $e){
            echo json_encode(array('error'=>$e->getMessage()));
        }
    }

    public function getAllCategories(){
        // строим дерево категорий
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
                        tree.deep+1
                    FROM
                        tree
                    JOIN category AS cat ON cat.parent_category_id = tree.id
                    ORDER BY name
                ) AS child
            )
            SELECT * FROM tree;
        ';
        /* Для минимизации объема передачи данных используем PDO::FETCH_NUM
            где:
            [0] => id каталога
            [1] => id родительского каталога
            [2] => Имя каталога
            [3] => deep (глубина)
        */
        try {
            $dir_array = $this->db->queryFetchAllNum($sql);
            echo json_encode($dir_array, JSON_UNESCAPED_UNICODE);
        } catch(Exception $e){
            echo json_encode(array('error'=>$e->getMessage()));
        }
    }
}




