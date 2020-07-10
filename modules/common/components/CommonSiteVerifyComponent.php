<?php
namespace app\modules\common\components;

use ego\base\JsonResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Cookie\CookieJar;

/**
 * 站点公共验证
 */
class CommonSiteVerifyComponent extends Component
{
    const CHECK_TYPE_GOODS      = 'goods';
    const CHECK_TYPE_CATEGORY   = 'category';
    const CHECK_TYPE_COUPON     = 'coupon';
	const CHECK_TYPE_PRICE      = 'price_sys';

    /**
     * @param array $params GET参数
     * - 格式：
     * site_code    string 站点简码，如 zf-pc/rg-wap
     * lang         string 语言简码,如 en/ru
     * check_type   string 验证类型(goods: 商品SKU验证 category 商品分类验证 coupon 优惠码验证)
     * check_rules  string 验证规则列表，多个规则用英文逗号分隔。具体看规则说明
     * pipeline     string 只限ZF(zaful )站点使用，国家站编码，如：ZF/ZFRU
     * client       string 只限DL(dresslily )站点使用，客户端，默认 web。 如：web/app
     * goods_sku    string 当验证类型为 goods 时使用商品SKU列表，多个SKU用英文逗号分隔
     * category_id  string 当验证类型为 category 时使用商品分类ID列表，多个分类ID用英文逗号分隔
     * coupon_code  string 当验证类型为 coupon 时使用优惠码列表，多个优惠码用英文逗号分隔
     * coupon_type  string 当需要验证优惠码类型时，需传入参数。
     *
     * @throws JsonResponseException
     * @return array
     */
    public static function verify($params)
    {
        if (!isset($params['site_code'], $params['lang'], $params['check_type'], $params['check_rules'])) {
            throw new JsonResponseException(1, '缺失参数！');
        }

        $siteCode = $params['site_code'];
        $checkType = $params['check_type'];

        $apiUrl = app()->params['sites'][ $siteCode ]['common_verify']['url'] ?? null;
        if (empty($apiUrl)) {
            throw new JsonResponseException(1, '无效 site_code 参数！');
        }

        if (isZufulSite($siteCode)) {
            unset($params['client']);
            if (!isset($params['pipeline'])) {
                throw new JsonResponseException(1, '缺失 pipeline 参数！');
            }
            // 站点使用http访问(走内网)
            $apiUrl = ges_str_replace_once('https://', 'http://', $apiUrl);

        } elseif (isDresslilySite($siteCode)) {
            unset($params['pipeline']);
            $params['client'] = $params['client'] ?? 'web';
        } else {
            unset($params['pipeline'], $params['client']);
        }

        $allowTypes = [self::CHECK_TYPE_GOODS, self::CHECK_TYPE_CATEGORY, self::CHECK_TYPE_COUPON, self::CHECK_TYPE_PRICE];
        if (!in_array($checkType, $allowTypes)) {
            throw new JsonResponseException(1, '无效 check_type 参数！');
        }

        if (self::CHECK_TYPE_GOODS == $checkType) {
            if (!isset($params['goods_sku'])) {
                throw new JsonResponseException(1, '缺失 goods_sku 参数！');
            }
        } elseif (self::CHECK_TYPE_CATEGORY == $checkType) {
            if (!isset($params['category_id'])) {
                throw new JsonResponseException(1, '缺失 category_id 参数！');
            }
        } elseif (self::CHECK_TYPE_COUPON == $checkType) {
            if (!isset($params['coupon_code']) && !isset($params['coupon_id'])) {
                throw new JsonResponseException(1, '缺失 coupon_code/coupon_id 参数！');
            }
        }

        $allowParamKeys = [
            'lang', 'check_type', 'check_rules', 'pipeline', 'client', 'goods_sku', 'category_id',
            'coupon_code', 'coupon_id', 'coupon_type'
        ];

        foreach ($params as $key => $value) {
            if (!in_array($key, $allowParamKeys)) {
                unset($params[$key]);
            }
            $params[$key] = trim(trim($value, ','));
        }

        // 请求站点接口
        $result = static::requestApi($siteCode, $apiUrl, $params);
        return app()->helper->arrayResult($result['code'], $result['message'], $result['data'] ?? []);
    }

    /**
     * 请求站点验证接口
     *
     * @param string $siteCode 站点简码
     * @param string $apiUrl 接口URL
     * @param array $params 接口参数
     */
    protected static function requestApi($siteCode, $apiUrl, $params)
    {
        $options = [
            //RequestOptions::HEADERS => ['Content-type' => 'x-www-form-urlencoded'],
            RequestOptions::VERIFY => false,
        ];

        if (app()->env->isPreRelease()) {
            $_domain = app()->params['sites'][ $siteCode ]['domain'];
            $cookieJar = CookieJar::fromArray(
                ['staging' => 'true'],
                mb_substr($_domain, stripos($_domain, '.'), strlen($_domain))
            );

            $options[RequestOptions::COOKIES] = $cookieJar;
        }

        $checkRules = explode(',', $params['check_rules']);

        // 分批请求接口
        $chunkParams = [];
        if (isset($params['goods_sku']) && !empty($params['goods_sku'])) {
            $skuList = explode(',', $params['goods_sku']);

            // 由于是分批请求，多个SKU会分配到不同请求，这样同款检查就不准确，这个规则自行检测
            if (in_array('GOODS_VALIDATE_SAME_SPU', $checkRules)) {
                $sameSpuSkuList = self::checkSameSpu($skuList);
                if (!empty($sameSpuSkuList)) {
                    return [
                        'code' => 1,
                        'message' => 'fail',
                        'data' => [
                            'lang' => $params['lang'],
                            'check_type' => $params['check_type'],
                            'fail_rule' => 'GOODS_VALIDATE_SAME_SPU',
                            'invalid_data' => $sameSpuSkuList
                        ]
                    ];
                }
            }


            $chunkSkuList = array_chunk($skuList, 200);
            foreach ($chunkSkuList as $_skuList) {
                $params['goods_sku'] = join(',', $_skuList);
                $chunkParams[] = $params;
            }
        } else {
            $chunkParams[] = $params;
        }

        $chunkResults = [];
        foreach ($chunkParams as $apiParams) {
            $options[RequestOptions::FORM_PARAMS] = $apiParams;
            $chunkResults[] = self::callApi($apiUrl, $options);
        }

        // 处理分批请求结果集
        if (count($chunkResults) === 1) {
            return $chunkResults[0];
        } else {
            $failRules = [];
            foreach ($chunkResults as $result) {
                if ((int)$result['code'] === 1) {
                    $rule = $result['data']['fail_rule'];
                    if (!isset($failRules[$rule])) {
                        $failRules[$rule] = [];
                    }

                    $failRules[$rule] = array_merge($failRules[$rule], $result['data']['invalid_data']);
                }
            }

            if (!empty($failRules)) {
                foreach ($checkRules as $checkRule) {
                    if (isset($failRules[$checkRule])) {
                        $apiResult = $chunkResults[0];
                        $apiResult['data']['fail_rule'] = $checkRule;
                        $apiResult['data']['invalid_data'] = $failRules[$checkRule];
                        return $apiResult;
                    }
                }
            }

            return $chunkResults[0];
        }
    }

    /**
     * 检查同款SKU, SKU前7位相同为同款
     *
     * @param array $skuList SKU列表
     * @return array
     */
    private static function checkSameSpu($skuList) {
        $spuList = [];
        $failSkuList = [];
        foreach ($skuList as $sku) {
            if (empty($sku))
                continue;

            $spu = substr($sku, 0, 7);
            if (isset($spuList[$spu])) {
                $failSkuList[] = $sku;
            } else {
                $spuList[$spu] = true;
            }
        }

        return $failSkuList;
    }

    /**
     * 请求站点商品详情接口
     *
     * @param string $apiUrl 接口URL
     * @param array $options GuzzleHttp 请求选项
     * @return array
     * @throws JsonResponseException
     */
    private static function callApi($apiUrl, $options) {
        try {
            $response = (new Client())->request('POST', $apiUrl, $options);
            $data = $response->getBody()->getContents();
            $result = json_decode($data, true);
        } catch (\Exception $e) {
            $format = "%s in %s line %d trace:\n%s";
            $message = sprintf($format, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
            throw new JsonResponseException(1, '站点验证接口请求异常！', ['msg' => $message]);
        }

        if (false === $result || !is_array($result) || !array_key_exists('code', $result)
            || !array_key_exists('message', $result)
        ) {
            throw new JsonResponseException(1, '站点验证接口返回不是标准格式！');
        }

        return $result;
    }
}
