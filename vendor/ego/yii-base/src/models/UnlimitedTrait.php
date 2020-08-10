<?php
namespace ego\models;

use ArrayObject;

/**
 * 无限级分类模型trait
 *
 * @property int $id
 * @property int $parent_id
 * @property string $node
 * @property string $name
 * @property int $create_time
 * @property int $update_time
 */
trait UnlimitedTrait
{
    use CacheTrait;

    /**
     * @var bool 使用了`UnlimitedTrait`，在`afterSave`后自动更新**node**字段
     */
    protected static $useUnlimitedTrait = true;

    /**
     * 获取完整名称
     *
     * @param static|int $id
     * @return string
     */
    public static function getFullName($id)
    {
        $item = is_numeric($id) ? static::getById($id) : $id;
        if ($item->node == $id) {
            return $item->name;
        }

        $names = [];
        foreach (explode(',', $item->node) as $node) {
            $names[] = static::getById($node)->name;
        }
        return join('>', $names);
    }

    /**
     * 获取下拉框选项数据
     *
     * @return array
     */
    public static function getSelectOptionsData()
    {
        $items = static::find()
            ->indexBy('id')
            ->select('id,name,parent_id')
            ->asArray()
            ->all();
        $items = app()->arrayTree->tree2array(
            app()->arrayTree->array2tree($items)
        );
        foreach ($items as &$item) {
            $item['name'] = static::getFullName($item['id']);
        }
        return $items;
    }

    /**
     * 更新节点关系
     *
     * @param array $old
     * @param array $new
     * @return bool|int|string
     */
    public static function updateNode(array $old, array $new)
    {
        $old = new ArrayObject($old, ArrayObject::ARRAY_AS_PROPS);
        $new = new ArrayObject($new, ArrayObject::ARRAY_AS_PROPS);

        // 新增
        if (!$old->count()) {
            return static::updateNodeInternal($new->id, $new->parent_id);
        } elseif ($old->parent_id == $new->parent_id) {
            return true;
        } else {
            $node = static::updateNodeInternal($old->id, $new->parent_id);
        }

        /*
         * 所属分类不相同，修改其下子类节点及层级
         * 如将
         * id   node
         * 1    1
         * 2    1,2
         * 10   1,10
         * 11   1,10,11
         *
         * id=10移到id=2下，新node关系为
         *
         * id   node
         * 1    1
         * 2    1,2
         * 10   1,2,10
         * 11   1,2,10,11
         */
        $len = strlen($old->node);
        /** @var \stdClass[] $items */
        $items = static::find()->select('id,node,name')
            ->where('node LIKE :node', ['node' => $old['node'] . ',%'])
            ->indexBy('id')
            ->all();
        foreach ($items as $id => $item) {
            $newNode = substr_replace($item->node, $node, 0, $len);
            static::updateAll(['node' => $newNode], ['id' => $id]);
        }
        $items[$old->id] = $old->id;
        static::clearCache(array_keys($items));
        return $node;
    }

    /**
     * 更新节点
     *
     * @param int $id
     * @param int $parentId
     * @return string
     */
    protected static function updateNodeInternal($id, $parentId)
    {
        // 节点等于自身id
        if (0 == $parentId) {
            $node = $id;
        } else {
            $node = static::find()->where(['id' => $parentId])->select('node')->one()->node;
            $node .= ',' . $id;
        }
        static::updateAll(['node' => $node], ['id' => $id]);
        return $node;
    }
}