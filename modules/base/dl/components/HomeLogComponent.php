<?php
namespace app\modules\base\dl\components;

use app\base\Pagination;
use app\modules\base\models\AdminModel;
use app\modules\common\dl\models\ActivityModel;
use app\modules\common\dl\models\PageLanguageModel;
use app\modules\common\dl\models\PageModel;
use app\modules\common\dl\models\PagePublishLogModel;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * 首页发布日志组件
 */
class HomeLogComponent extends Component
{
    /**
     * @var PagePublishLogModel 页面发布日志模型
     */
    protected $model;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->model = new PagePublishLogModel();
    }

    /**
     * 列表
     * @param string $siteCode
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function lists(string $siteCode)
    {
        $query = PagePublishLogModel::find()->alias('ppl')
            ->select('ppl.*, pl.title, u.realname as create_name, pl.page_url, group_concat(ppl.lang) as langList')
            ->leftJoin(PageModel::tableName() . ' as p', 'ppl.page_id = p.id')
            ->leftJoin(PageLanguageModel::tableName() . ' as pl', 'pl.page_id = ppl.page_id AND pl.lang = "' . app()->params['en_lang'] . '"')
            ->leftJoin(AdminModel::tableName() . ' as u', 'ppl.create_user = u.username')
            ->where([
                'p.activity_id' => 0,
                'ppl.log_type' => PagePublishLogModel::LOG_TYPE_PUBLISH,
                'ppl.action_type' => PagePublishLogModel::ACTION_TYPE_ONLINE,
                'ppl.site_code' => $siteCode,
                'ppl.file_type' => 'html'
            ])->groupBy('ppl.version');

        $count = $query->count();
        $pagination = Pagination::new($count);

        $list = $query->orderBy('ppl.id desc')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->all();

        if ($list) {
            $list = ArrayHelper::toArray($list);
            $domain = app()->params['sites'][$siteCode]['home_secondary_domain'][$list[0]['lang']];
            foreach ($list as $key => $item) {
                $langList = array_unique(explode(',', $item['langList']));
                $list[$key]['detail'] = '首页发布上线';
                $list[$key]['ip'] = long2ip($list[$key]['ip']);
                $list[$key]['page_url'] = $domain . $item['page_url'];
                $list[$key]['langList'] = ActivityModel::getLangListByLangString(implode(',', $langList));
                /** @var array[][] $list */
                foreach ($list[$key]['langList'] as $langKey => $langItem) {
                    $list[$key]['langList'][$langKey]['viewUrl'] = Url::to([
                        '/home/dl/page/view-version',
                        'page_id' => $item['page_id'],
                        'version' => $item['version'],
                        'lang' => $langItem['key']
                    ], true);
                }
            }
        }

        return app()->helper->arrayResult(
            $this->codeSuccess,
            $this->msgSuccess,
            [
                'list' => $list ?? [],
                'pagination' => [
                    $pagination->pageParam     => (int) $pagination->page + 1,
                    $pagination->pageSizeParam => (int) $pagination->pageSize,
                    'totalCount'               => (int) $pagination->totalCount
                ]
            ]
        );
    }
}
