<?php

namespace app\modules\admin\models;

use app\models\ActiveRecord;

/**
 * 多语言内容表
 *
 * @property int id
 * @property int language_id
 * @property string lang
 * @property string content
 * @property string update_user
 * @property string create_user
 * @property int create_time
 * @property int update_time
 */
class LanguageTransModel extends ActiveRecord
{
    /**
     * 初始化日志配置logConfig
     */
    public function init()
    {
        parent::init();
        $this->logConfig['nameField'] = 'content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['language_id', 'lang', 'content'], 'required'],
            ['language_id', 'validateLang'],
        ];
    }

    /**
     * 验证language_id和lang
     * @return bool
     */
    public function validateLang()
    {
        if ((int)$this->language_id <= 0) {
            $this->addError('language_id', 'language_id必须大于0');
            return false;
        }

        if (($item = static::findOne([
                'language_id' => $this->language_id,
                'lang' => $this->lang
            ]))
            && (!$this->id || (int)$item->id !== (int)$this->id)
        ) {
            $this->addError('language_id', '该语言项已存在翻译记录（主键重复）');
            return false;
        }

        return true;
    }

    /**
     * 查询当前语言下的所有翻译记录
     * @param string $lang 语言
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findRecordByLang($lang)
    {
        return static::find()->where([
            'lang' => $lang,
        ])->asArray()->all();
    }

    /**
     * 根据原文记录ID和lang查询记录
     * @param int $id 原文记录ID
     * @param string $lang 语言代码简称
     * @return LanguageTransModel|null
     */
    public static function findOneByLang($id, $lang)
    {
        return static::findOne([
            'language_id' => $id,
            'lang' => $lang
        ]);
    }

    /**
     * 获取设置了的所有语言
     * @return array
     */
    public static function getAllLang()
    {
        $data = self::find()->select('lang')->groupBy('lang')->asArray()->all();

        return empty($data) ? [] : array_column($data, 'lang');
    }

    /**
     * 获取当前内容所属key
     * @return \yii\db\ActiveQuery
     */
    public function getKey()
    {
        return $this->hasOne(LanguageModel::class, ['id' => 'language_id']);
    }
}
