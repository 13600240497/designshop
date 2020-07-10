<?php
namespace app\modules\admin\models;

use app\models\ActiveRecord;
use ego\models\UnlimitedTrait;

/**
 * 资源模型
 *
 * @property static[] $crumbs
 */
class ResourceModel extends ActiveRecord
{
    use UnlimitedTrait;

    /** @var int 资源类型-目录 */
    const RESOURCE_TYPE_FOLDER = 0;

    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'name';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'type' => '资源类型',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['size', 'height', 'width', 'url', 'thumbnail_url'], 'safe'],
            [['type', 'name', 'parent_id'], 'required'],
            ['type', 'in', 'range' => [0, 1, 2, 3], 'message' => '{attribute}只能为1图片2视频3音频'],
            ['name', 'validateName'],
//            ['url', 'validateUrl'],
//            ['thumbnail_url', 'validateUrl'],
            ['type','validateType'],
            ['is_delete', 'in', 'range' => [0, 1], 'message' => '{attribute}只能为0和1'],
        ];
    }

    /**
     * 验证Name
     *
     * @return bool
     */
    public function validateName()
    {
        /** @var static $item */
        $item = static::find()
            ->select('id,name')
            ->where([
                'name' => $this->name,
                'parent_id' => $this->parent_id,
                'is_delete' => 0,
                'create_user' => app()->user->username
                ])->one();
        if (!$item || $this->id == $item->id) {
            return true;
        }
        $this->addError('name', '"'. $item->name . '"已经存在,请换成其他名称');
        return false;
    }

    /**
     * 验证类型
     * @return bool
     */
    public function validateType()
    {
        if (0 == $this->parent_id && 0 != $this->type) {
            $this->addError(
                'type',
                $this->getAttributeLabel('type') . '错误：主资源目录下禁止上传资源，请先新建资源目录后上传'
            );
            return false;
        }
        return true;
    }

    /**
     * 获取面包屑数据
     *
     * @return static[]
     */
    public function getCrumbs()
    {
        $ids = explode(',', $this->node);
        $result = [];
        foreach ($ids as $id) {
            $result[] = static::find()->where(['id' => $id])->asArray()->one();
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->clearDomainToUrl();
        if ($insert) {
            $this['create_user'] = app()->user->username ?? '';
        }
        return parent::beforeSave($insert);
    }

    /**
     * 填充url
     */
    public function includeS3DomainUrl()
    {
        $domain = app()->params['s3UploadsConf']['staticDomain'];
        $this->url && $this->url = $domain .  $this->url;
        $this->thumbnail_url && $this->thumbnail_url = $domain . $this->thumbnail_url;
    }

    /**
     * 缓存填充属性
     *
     * @param $val
     */
    public function setThumbnailUrl($val)
    {
        $this->thumbnail_url = $val;
    }

    /**
     * 保存前清除网站域名。
     */
    private function clearDomainToUrl()
    {
        $domain = app()->params['s3UploadsConf']['staticDomain'];
        $len = strlen($domain);
        if (isset($this->url) && 0 == strncmp($this->url, $domain, $len)) {
            $this->url = substr($this->url, $len);
            $this->thumbnail_url = substr($this->thumbnail_url, $len);
        }
    }
}
