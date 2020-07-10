<?php
namespace app\base;

use app\modules\base\models\AdminModel;
use yii\base\Component;
use yii\web\IdentityInterface;

/**
 * 用户身份登录验证
 *
 * @property AdminModel $admin
 */
class Identity extends Component implements IdentityInterface
{
    /**
     * @var AdminModel|null
     */
    public $admin;

    /**
     * 构造方法
     *
     * @param AdminModel $admin
     */
    public function __construct(AdminModel $admin)
    {
        parent::__construct([]);
        $this->admin = $admin;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $admin = AdminModel::getById($id);
        return $admin ? new static($admin) : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->admin ? $this->admin->id : null;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
    }
}
