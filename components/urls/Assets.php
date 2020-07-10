<?php

namespace app\components\urls;

/**
 * 静态资源url组件
 */
class Assets extends BaseUrl
{
    /**
     * @var array
     */
    protected $manifest;
    
    /**
     * 返回css文件路径
     *
     * css路径默认加上版本号
     *
     * @param string $file
     *
     * @return string
     */
    public function css($file)
    {
        if (isset($this->manifest[ $file ])) {
            return $this->to('dist/' . $this->manifest[ $file ]);
        }
        
        return $this->to($file, app()->params['css_version']);
    }
    
    /**
     * 返回js文件路径
     *
     * js路径默认加上版本号
     *
     * @param string $file
     *
     * @return string
     */
    public function js($file)
    {
        if (isset($this->manifest[ $file ])) {
            return $this->to('dist/' . $this->manifest[ $file ]);
        }
        
        return $this->to($file, app()->params['js_version']);
    }

    /**
     * @param string $file 文件名称
     * @param boolean $isPublish 是否发布模式
     * @return string
     */
    public function clientJs($file, $isPublish = false)
    {
        if (app()->env->isDev() && !$isPublish) {
            $manifest = json_decode(
                file_get_contents(\Yii::getAlias('@app/htdocs/develop/vue-ssr-client-manifest.json')),
                true
            );
    
            $filePath = ltrim($manifest['publicPath'], '/') . current($manifest[ $file ]);
            return $this->to($filePath);
        } else {
            $manifest = json_decode(
                file_get_contents(\Yii::getAlias('@app/htdocs/resource/vue-ssr-client-manifest.json')),
                true
            );
            return $manifest['publicPath'] . current($manifest[ $file ]);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->manifest = json_decode(
            file_get_contents(\Yii::getAlias('@app/htdocs/dist/manifest.json')),
            true
        );
    }
    
    /**
     * @inheritdoc
     */
    protected function setBaseUrl()
    {
        $this->baseUrl = app()->params['url']['admin'];
    }
}
