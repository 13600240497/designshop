<?php
namespace Globalegrow\Base;

/**
 * 树数组与普通数组互转组件
 */
class ArrayTree extends Component
{
    /**
     * @var array tree2array将树数组转化为普通数组数据
     */
    protected $tree2array = [];

    /**
     * 将普通数组转化为树数组
     *
     * @param array $array 待转化数组
     * @param bool $preserveKeys **true**时保持原数组索引关系
     * @param string $idField
     * @param string $parentField
     * @return array 转化后的树数组
     */
    public function array2tree(array $array, $preserveKeys = false, $idField = 'id', $parentField = 'parent_id')
    {
        $array = Arr::toArray($array);
        $tree = [];
        $refer = [];
        foreach ($array as $key => $data) {
            $id = $data[$idField];
            $array[$key]['treeInfo']['leaf'] = true;
            $refer[$id] = &$array[$key];
        }

        foreach ($array as $key => $data) {
            $parentId = $data[$parentField];

            if ($parentId && isset($refer[$parentId])) {
                $parent = &$refer[$parentId];
                $parent['treeInfo']['leaf'] = false;
                if ($preserveKeys) {
                    $parent['children'][$key] = &$array[$key];
                } else {
                    $parent['children'][] = &$array[$key];
                }
            } elseif ($preserveKeys) {
                $tree[$key] = &$array[$key];
            } else {
                $tree[] = &$array[$key];
            }
            unset($array[$key]);
        }
        return $tree;
    }

    /**
     * 将树数组转化为普通数组
     *
     * @param array $tree 待转化树数组
     * @param bool $idAsKey **true**时id的值作为结果数组的索引
     * @param \Closure $map
     * @param int $level 层次，默认0
     * @param string $idField
     * @return array 转化后的普通数组
     */
    public function tree2array(array $tree, $idAsKey = false, $idField = 'id', \Closure $map = null, $level = 0)
    {
        $tree = Arr::toArray($tree);
        if (0 == $level) {
            $this->tree2array = [];
        }
        foreach ($tree as $key => $value) {
            $children = isset($value['children']) ? $value['children'] : null;
            $value['treeInfo']['level'] = $level + 1;
            unset($value['children']);

            if ($map) {
                $value = $map($key, $value, $value['treeInfo']['level']);
            }

            if ($idAsKey) {
                $this->tree2array[$value[$idField]] = $value;
            } else {
                $this->tree2array[] = $value;
            }

            if ($children) {
                $this->tree2array($children, $idAsKey, $idField, $map, $level + 1);
            }
        }

        return $this->tree2array;
    }

    /**
     * 将只有**id**,**parent_id**的数组添加**node**节点关系
     *
     * @param array $data
     * @param string $idField
     * @param string $parentField
     * @return array
     */
    public function addNode(array $data, $idField = 'id', $parentField = 'parent_id')
    {
        // parent_id 升序
        uasort($data, function($a, $b) use ($parentField) {
            if ($a[$parentField] > $b[$parentField]) {
                return 1;
            }
            return $a[$parentField] == $b[$parentField] ? 0 : -1;
        });

        // 首次循环
        // 1. 过滤掉父id不存在的数据
        // 2. 得到一二级数据node节点关系（甚至更多级）
        $result = [];
        foreach ($data as $key => $item) {
            $id = $item[$idField];
            $parentId = $item[$parentField];
            if (0 == $parentId) { // 一级
                $item['node'] = $id;
                $result[$id] = $item;
                unset($data[$key]);
            } elseif (isset($result[$parentId]['node'])) { // 存在父节点
                $item['node'] = $result[$parentId]['node'] . ',' . $id;
                $result[$id] = $item;
                unset($data[$key]);
            } elseif (!isset($data[$parentId])) {
                unset($data[$key]);
            }
        }

        // 剩余的N级数据
        while (count($data)) {
            foreach ($data as $key => $item) {
                $id = $item[$idField];
                $parentId = $item[$parentField];
                if (isset($result[$parentId])) {
                    $item['node'] = $result[$parentId]['node'] . ',' . $id;
                    $result[$id] = $item;
                    unset($data[$key]);
                }
            }
        }
        return $result;
    }
}
