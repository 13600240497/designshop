import fetch from './fetchApi'
import { getCookie } from './mUtils'

let site_code = ''
const site_group_code = getCookie('site_group_code')
if (site_group_code) {
    switch (String(site_group_code)) {
        case 'dl':
            site_code = 'dl/'
            break
        default:
            site_code = ''
            break
    }
}

const apizza = ''

// const apizza = 'https://dsn.apizza.net/mock/6a61c62f7a6af49c20eeedc8ba615f88'

// 语言包模块 - start

/**
 * 获取语言Key列表
 */
export const getLangKeyList = (param) => fetch.get('/admin/language/lang-list', param)

/**
 * zaful专题活动、首页国家站平台，渠道，语言列表
 * @param {String} - activity_type=1 专题活动
 * @param {String} - activity_type=2 首页
 */
export const ZF_getCountrySiteList = (param) => fetch.get('/admin/language/country-site-list', param)

/**
 * 获取语言包列表
 */
export const getLangList = (param) => fetch.get('/admin/language/list', param)

/**
 * 获取语言包详情
 */
export const getLangInfo = (param) => fetch.get('/admin/language/info', param)

/**
 * 新增语言包
 */
export const addLang = (param) => fetch.post('/admin/language/add', param)

/**
 * 更新语言包
 */
export const updateLang = (param) => fetch.post('/admin/language/edit', param)

/**
 * 删除语言包
 */
export const deleteLang = (param) => fetch.post('/admin/language/delete', param)

/**
 * 更新语言包缓存文件
 */
export const updateLangCache = () => fetch.get('/admin/language/build')

// 语言包模块 - end
// 活动模块 - start

/**
 * 统一校验
 */
export const get_siteVerificationVerify = (param) => fetch.post('/common/site-verification/verify', param, { messageOff: true, successOff: true});

/**
 *  APP 装修页 again
 * **/

/**
 * 获取访问链接
 */

export const ZF_getNewestUrls = (param) => fetch.get('/activity/zf/page/pipeline-newest-urls', param)


export const ZF_designRelease = (param) => fetch.post('/activity/zf/design/release', param)

/**
 * 切换页面模板
 */

export const ZF_confirmTpl = (param) => fetch.post('/activity/zf/page-tpl/confirm-tpl', param, { successOff: true})


/**
 * 移动端切换页面模板
 */

export const ZF_nativeConfirmTpl = (param) => fetch.post('/activity/zf/native-page-tpl/confirm-tpl', param, { successOff: true})

/**
 * 获取商品sku
 */

export const ZF_goodsTplList = (param) => fetch.get('/activity/zf/goods/tpl-goods-list', param)

/**
 * 获取同步其他渠道信息
 */

export const ZF_getCopyPipeline = (param) => fetch.get('/activity/zf/native-design/get-native-copy-pipeline', param)

/**
 * 同步其他渠道信息
 */

export const ZF_CopyPipeline = (param) => fetch.post('/activity/zf/native-design/copy-native-pipeline', param, { successOff: true})

/**
 * 增加SKU
 */

export const ZF_getTplGoodsExists = (param) => fetch.get('/activity/zf/goods/tpl-goods-exists', param, { successOff: true})

/**
 * 新增页面模板
 */

const ZF_getPageTplAdd = (param) => fetch.post('/activity/zf/native-page-tpl/add', param, { successOff: true})


/**
 * 新增上传图片
 */

const ZF_uploadPic = (param) => fetch.post('/activity/zf/native-page-tpl/upload-pic', param, { successOff: true})

/**
 * 获取活动列表
 */
export const getActivityList = (param) => fetch.get('/activity/activity/list', param)

/**
 * DL获取活动列表
 */
export const DL_getActivityList = (param) => fetch.get('/activity/dl/activity/list', param)

/**
 * zaful获取活动列表
 */
export const getZFActivityList = (param) => fetch.get('/activity/zf/activity/list', param)

/**
 * 添加活动
 */
export const addActivity = (param) => fetch.post('/activity/activity/add', param)

/**
 * 添加活动
 */
export const DL_addActivity = (param) => fetch.post('/activity/dl/activity/add', param)

/**
 * zaful国家站 - 添加活动
 */
export const ZF_addActivity = (param) => fetch.post('/activity/zf/activity/add', param)

/**
 * 更新活动
 */
export const updateActivity = (param) => fetch.post('/activity/activity/edit', param)

/**
 * 更新活动
 */
export const DL_updateActivity = (param) => fetch.post('/activity/dl/activity/edit', param)

/**
 * 更新活动
 */
export const ZF_updateActivity = (param) => fetch.post('/activity/zf/activity/edit', param, {timeout: 300000})

/**
 * 删除活动
 */
export const deleteActivity = (param) => fetch.get('/activity/activity/delete', param)

/**
 * D网删除活动
 */
export const DL_deleteActivity = (param) => fetch.get('/activity/dl/activity/delete', param)

/**
 * zaful删除活动
 */
export const ZF_deleteActivity = (param) => fetch.get('/activity/zf/activity/delete', param)

/**
 * 专题与活动管理 - 审核活动上线/下线
 */
export const verifyActivity = (param) => fetch.post('/activity/activity/verify', param)

/**
 * D网专题与活动管理 - 审核活动上线/下线
 */
export const DL_verifyActivity = (param) => fetch.post('/activity/dl/activity/verify', param)

/**
 * zaful专题与活动管理 - 审核活动上线/下线
 */
export const ZF_verifyActivity = (param) => fetch.post('/activity/zf/activity/verify', param)

/**
 * 活动推广管理 - 审核活动上线/下线
 */
export const advertisementVerifyActivity = (param) => fetch.post('/advertisement/advertisement/verify', param)

/**
 * 获取活动页面列表
 */
export const getPageList = (param) => fetch.get('/activity/page/list', param)

/**
 * 获取活动页面列表
 */
export const DL_getPageList = (param) => fetch.get('/activity/dl/page/list', param)

/**
 * 获取活动页面列表
 */
export const ZF_getPageList = (param) => fetch.get('/activity/zf/page/list', param)

/**
 * 获取 App 活动列表
 */
export const getAppActivityList = (param) => fetch.get('/convert/creator-app-lists', param)

/**
 * 获取 App 活动列表
 */
export const DL_getAppActivityList = (param) => fetch.get('/activity/dl/convert/creator-app-lists', param)

/**
 * 获取 App 活动列表
 */
export const ZF_getAppActivityList = (param) => fetch.get('/activity/zf/convert/creator-app-lists', param)

/**
 * M 端页面转 APP 端页面
 */
export const convertToAppPage = (param) => fetch.post('/convert/wap-convert-app', param)

/**
 * WEB 端页面转 APP 端页面
 */
export const DL_convertToAppPage = (param) => fetch.post('/activity/dl/convert/web-convert-app', param)

/**
 * M 端页面转 APP 端页面
 */
export const ZF_convertToAppPage = (param) => fetch.post('/activity/zf/convert/wap-convert-app', param)

/**
 * 批量编辑页面属性
 */
export const batchEditPage = (param) => fetch.post('/activity/page/batch-edit', param)

/**
 * 批量编辑页面属性
 */
export const DL_batchEditPage = (param) => fetch.post('/activity/dl/page/batch-edit', param)

/**
 * 批量编辑页面属性
 */
export const ZF_batchEditPage = (param) => fetch.post('/activity/zf/page/batch-edit', param)

/**
 *
 * @param {addPage} param
 */
export const batchAddPage = (param) => fetch.post('/activity/page/add', param)

/**
 *
 * @param {addPage} param
 */
export const DL_batchAddPage = (param) => fetch.post('/activity/dl/page/add', param)

/**
 *
 * @param {addPage} param
 */
export const ZF_batchAddPage = (param) => fetch.post('/activity/zf/page/add', param)

/**
 * 删除活动页面
 */
export const deletePage = (param) => fetch.get('/activity/page/delete', param)

/**
 * D网删除活动页面
 */
// export const DL_deletePage = (param, options) => fetch.get('/activity/dl/page/delete', param, options)
export const DL_deletePage = (param) => fetch.get('/activity/dl/page/delete', param)

/**
 * zaful删除活动页面
 */
export const ZF_deletePage = (param) => fetch.get('/activity/zf/page/del-pipeline-all-pages', param)

/**
 * 专题与活动管理 - 上下线活动页面
 */
export const verifyPage = (param) => fetch.post('/activity/page/verify', param)

/**
 * D网专题与活动管理 - 上下线活动页面
 */
export const DL_verifyPage = (param) => fetch.post('/activity/dl/page/verify', param)

/**
 * zaful专题与活动管理 - 上下线活动页面
 */
export const ZF_verifyPage = (param) => fetch.post('/activity/zf/page/verify', param)

/**
 * zaful专题与活动管理 - 上下线活动页面获取渠道数据
 * @param { String } group_id
 */
export const ZF_getChannelPageInfo = (param) => fetch.get('/activity/zf/page/pipeline-info', param)

/**
 * 活动推广管理 - 上下线活动页面
 */
export const advertisementVerifyPage = (param) => fetch.post('/advertisement/page/verify', param)

/**
 * 获取自动刷新时间下拉列表
 */
export const refreshSelete = () => fetch.get('/activity/page/refresh-list')

/**
 * 获取自动刷新时间下拉列表
 */
export const DL_refreshSelete = () => fetch.get('/activity/dl/page/refresh-list')

/**
 * 获取 A/B Test B 页面访问链接
 */
export const getBHomePages = (param) => fetch.get('/home/' + site_code + 'page/get-homeb-newest-urls', param)

/**
 * 一键刷新头尾部
 */
export const refreshSite = (param) => fetch.post('/activity/page/refresh-site-page', param)

/**
 * 一键刷新头尾部
 */
export const DL_refreshSite = (param) => fetch.post('/activity/dl/page/refresh-site-page', param)

/**
 * 一键刷新头尾部
 */
export const ZF_refreshSite = (param) => fetch.post('/activity/zf/page/refresh-site-page', param)

/**
 * 一键刷新首页头尾部
 */
export const refreshHomePage = (param) => fetch.post('/home/page/refresh-site-page', param)

/**
 * 加解锁活动页面
 */
export const lockingActivity = (param) => fetch.get('/activity/activity/lock', param)

/**
 * D网加解锁活动页面
 */
export const DL_lockingActivity = (param) => fetch.get('/activity/dl/activity/lock', param)

/**
 * zaful加解锁活动页面
 */
export const ZF_lockingActivity = (param) => fetch.get('/activity/zf/activity/lock', param)

/**
 * 获取页面模板列表
 */
export const getPageTemplateList = (param) => fetch.get('/activity/'+ site_code +'page-tpl/list', param)

/**
 * 获取页面模板列表
 */
export const DL_getPageTemplateList = (param) => fetch.get('/activity/dl/page-tpl/list', param)

/**
 * 获取页面模板列表
 */
export const ZF_getPageTemplateList = (param) => fetch.get('/activity/zf/page-tpl/list', param)


/**
 * 获取原生页面模板列表
 */
export const ZF_getNativePageTemplateList = (param) => fetch.get('/activity/zf/native-page-tpl/list', param)

/**
 * 移动端获取页面模板列表
 */
export const ZF_getPageTemplateListNative = (param) => fetch.get('/activity/zf/native-page-tpl/list', param)

/**
 * 查看页面ID
 * @param {*} param 
 */
export const ZF_get_page_id = (param) => fetch.get('/activity/zf/page/page-id-list', param);

/**
 * 查看所有原生页面ID
 */
export const ZF_getNativeDeepLink = (param) => fetch.get('/activity/zf/page/native-deep-link', param)

/**
 * 编辑页面模板
 */
export const updatePageTemplate = (param) => fetch.post('/activity/page-tpl/edit', param)

/**
 * 编辑页面模板
 */
export const DL_updatePageTemplate = (param) => fetch.post('/activity/dl/page-tpl/edit', param)

/**
 * 编辑页面模板
 */
export const ZF_updatePageTemplate = (param) => fetch.post('/activity/zf/page-tpl/edit', param)


/**
 * 原生--编辑页面模板
 */
export const ZF_updateNativePageTemplate = (param) => fetch.post('/activity/zf/native-page-tpl/edit', param)

/**
 * 删除页面模板
 */
export const deletePageTemplate = (param) => fetch.get('/activity/page-tpl/delete', param)

/**
 * 删除页面模板
 */
export const DL_deletePageTemplate = (param) => fetch.get('/activity/dl/page-tpl/delete', param)

/**
 * 删除页面模板
 */
export const ZF_deletePageTemplate = (param) => fetch.get('/activity/zf/page-tpl/delete', param)

/**
 * 删除页面模板
 */
export const ZF_deletePageTemplateNative = (param) => fetch.get('/activity/zf/native-page-tpl/delete', param)

/**
 * 设置默认模板
 */
export const setDefaultPageTemplate = (param) => fetch.get('/activity/page-tpl/change-tpl', param)

/**
 * 设置默认模板
 */
export const DL_setDefaultPageTemplate = (param) => fetch.get('/activity/dl/page-tpl/change-tpl', param)

// 活动模块 - end
// 组件模块 - start

/**
 * 获取组件列表
 */
export const getComponentList = (param) => fetch.get('/component/index/list', param)

/**
 * 增加组件
 */
export const addComponent = (param) => fetch.post('/component/index/add', param)

/**
 * 更新组件
 */
export const updateComponent = (param) => fetch.post('/component/index/edit', param)

/**
 * 上下线组件
 */
export const changeComponentStatus = (param) => fetch.post('/component/index/change-status', param)

// 组件模块 - end

// 组件分类模块 - start

/**
 * 组件分类列表
 */
export const getCategoryList = (param) => fetch.get('/component/category/list', param)

/**
 * 新增组件分类
 */
export const addCategory = (param) => fetch.post('/component/category/add', param)

/**
 * 编辑组件分类
 */
export const updateCategory = (param) => fetch.post('/component/category/edit', param)

// 组件分类模块 - end

// 组件模板模块 - start

/**
 * 组件模板列表
 */
export const getTemplateList = (param) => fetch.get('/component/component-tpl/list', param)

/**
 * 新增组件模板
 */
export const addTemplate = (param) => fetch.post('/component/component-tpl/add', param)

/**
 * 编辑组件模板
 */
export const updateTemplate = (param) => fetch.post('/component/component-tpl/edit', param)

/**
 * 删除组件模板
 */
export const deleteTemplate = (param) => fetch.get('/component/component-tpl/delete', param)

/**
 * 查看组件模板
 */
export const seeTemplate = (param) => fetch.get('', param)

/**
* 更新组件模板状态
*/
export const changeTemplateStatus = (param) => fetch.post('/component/component-tpl/change-status', param)

// 组件模板模块 - end

/**
 * 获取菜单列表sidebar
 */
export const getPublicMenus = (param) => fetch.get('/base/menu/public-menus', param)

/**
 * 获取角色列表
 */
export const getRoleList = (param) => fetch.get('/base/role/list', param)

/**
 * 增加角色
 */
export const addRole = (param) => fetch.get('/base/role/add', param)

/**
 * 编辑角色
 */
export const editRole = (param) => fetch.get('/base/role/edit', param)

/**
 * 获取角色信息
 */
export const getRoleInfo = (param) => fetch.get('/base/role/info', param)

/**
 * 获取角色可选权限
 */
export const getAvalprivileges = (param) => fetch.get('/base/role/available-privileges', param)

/**
 * 获取角色下用户列表
 */

export const getRoleDetailList = (param) => fetch.get('/base/role/user-list', param)

/**
 * 获取个人素材资源列表
 */
export const getResourceList = (param) => fetch.get('/admin/resource/list', param)

/**
 * 获取素材资源列表
 */
export const getResourceListAll = (param) => fetch.get('/admin/resource/list-all', param)

/**
 * 获取素材资源面包屑
 */
export const getResourceCrumbs = (param) => fetch.get('/admin/resource/crumbs', param)

/**
 * 素材资源删除
 */
export const resourceDel = (param) => fetch.post('/admin/resource/delete', param)

/**
 * 素材资源批量删除
 */
export const resourceDelList = (param) => fetch.post('/admin/resource/delete-ids', param)

/**
 * 素材个人资源搜索
 */
export const resourceSearch = (param) => fetch.get('/admin/resource/search', param)

/**
 * 素材资源搜索
 */
export const resourceSearchAll = (param) => fetch.get('/admin/resource/search-all', param)

/**
 * 素材资源添加
 */
export const resourceAdd = (param) => fetch.get('/admin/resource/add', param)

/**
 * 素材资源编辑
 */
export const resourceEdit = (param) => fetch.post('/admin/resource/edit', param)

/**
 * 获取素材详情
 */
export const getResourceInfo = (param) => fetch.get('/admin/resource/info', param)

/**
 * 素材资源上传
 */
export const resourceUpload = (param, options) => fetch.post('/admin/resource/upload', param, options)

/**
 * 素材多文件上传
 */
export const resourceMultiUpload = (param, options) => fetch.post('/admin/resource/multi-file-upload', param, options)

/**
 * 素材移动
 */
export const resourceMove = (param) => fetch.post('/admin/resource/move-resource', param)

/**
 * 系统日志列表
 */
export const adminLogList = (param) => fetch.get('/base/admin-log/list', param)

/**
 * 系统日志详情
 */
export const adminLogDetail = (param) => fetch.get('/base/admin-log/detail', param)

/**
 * 获取菜单列表
 */
export const getMenuList = () => fetch.get('/base/menu/list')

/**
 * 获取菜单下拉分类列表
 */
export const getMenuSelectOp = () => fetch.get('/base/menu/public-select-options')

/**
 * 菜单列表删除
 */
export const menuDel = (param) => fetch.post('/base/menu/delete', param)

/**
 * 获取部门列表
 */
export const getDepartmentList = (param) => fetch.get('/base/department/list', param)

/**
 * 获取部门下拉选项
 */
export const getDepartmentPublic = () => fetch.get('/base/department/public-outline')

/**
 * 获取部门详情
 */
export const getDepartmentInfo = (param) => fetch.get('/base/department/info', param)

/**
 * 部门详情编辑
 */
export const departmentEdit = (param) => fetch.post('/base/department/edit', param)

/**
 * 获取系统用户管理
 */
export const getAdminList = (param) => fetch.get('/base/admin/list', param)

/**
 * 获取渠道权限列表
 * @param { String } website_code - zf
 */
export const getPermissionChannel = (param) => fetch.get('/base/admin/site-privileges', param)

/**
 * 渠道权限提交
 * @param { String } user_id - 1
 * @param { String } website_code - zf
 * @param { String } permissions - ['ZF','ZFES','ZFFR']
 */
export const updatePermissChannel = (param) => fetch.post('/base/admin/save-user-site-privilege', param)

/**
 * 获取系统用户详情
 */
export const getAdminInfo = (param) => fetch.get('/base/admin/info', param)

/**
 * 系统用户删除
 */
export const adminDel = (param) => fetch.post('/base/menu/delete', param)

/**
 * 系统用户编辑
 */
export const adminEdit = (param) => fetch.post('/base/admin/edit', param)

/**
 * 一键更新站点头尾部日志列表
 */
export const refreshLogList = (param) => fetch.get('/activity/page/refresh-site-log-list', param)

/**
 * 一键更新站点头尾部日志详情
 */
export const refreshLogDetail = (param) => fetch.get('/activity/page/refresh-site-log-detail', param)

/**
 * 一键更新站点头尾部日志详情
 */
export const ZF_refreshLogDetail = (param) => fetch.get('/activity/zf/page/refresh-site-log-detail', param)

/**
 * 一键更新站点头尾部日志详情
 */
export const DL_refreshLogDetail = (param) => fetch.get('/activity/dl/page/refresh-site-log-detail', param)

/**
 * 一键更新站点头尾部日志详情
 */
export const GB_refreshLogDetail = (param) => fetch.get('/activity/gb/page/refresh-site-log-detail', param)

/**
 * 查看模版详情
 */
export const getModelHtml = (param) => fetch.get('/activity/page-tpl/preview', param)

/**
 * 查看模版详情
 */
export const DL_getModelHtml = (param) => fetch.get('/activity/dl/page-tpl/preview', param)

/**
 * 商品管理列表
 */
export const getGoodMgList = (param) => fetch.get('/activity/goods-manage/list', param)

/**
 * 当前站点下所有端活动列表
 */
export const getGoodActivityList = (param) => fetch.get('/activity/goods-manage/activity-list', param)

/**
 * 单个活动下所有页面列表
 */
export const getGoodManagePageList = (param, options) => fetch.get('/activity/goods-manage/activity-page-list', param, options)

/**
 * 获取不同端（PC、M、APP）专题活动列表
 * @param { String } site_code - 站点简称
 */
export const getPlaceActivityList = (param, options) => fetch.get('/activity/user-activity-list', param, options)

/**
 * 商品管理列表
 * siteCode 站点简码
 * lang  语言简码
 * skuList sku列表
 */
export const getGoodCheckExists = (param, options) => fetch.post('/activity/goods-manage/check-goods-exists', param, options)

/**
 * 添加商品管理数据
 */
export const goodManageAddPost = (param) => fetch.post('/activity/goods-manage/add', param)

/**
 * 查看页面商品sku
 * gmpId 商品管理页面表ID
 * string	否	商品SKU查看方式，默认1，1分标题查看；2统一查看		1
 */
export const getGoodManageSkuList = (param) => fetch.get('/activity/goods-manage/sku-list', param)

/*首页列表*/
export const indexList = (param) => fetch.get('/home/' + site_code + 'page/list', param)

/*首页列表*/
export const ZF_indexList = (param) => fetch.get('/home/zf/page/list', param)

/*编辑首页*/
export const editIndex = (param) => fetch.post('/home/' + site_code + 'page/batch-edit', param)

/*编辑首页*/
export const ZF_editIndex = (param) => fetch.post('/home/zf/page/batch-edit', param)

/**
 * 三端合并新增首页接口
 */
export const addIndex = (param) => fetch.post('/home/' + site_code + 'page/add', param)

/**
 * 三端合并新增首页接口
 */
export const ZF_addIndex = (param) => fetch.post('/home/zf/page/add', param)

/**
 * 编辑商品管理活动组
 * groupId 	商品管理组ID
 */
export const getGoodManageGroup = (param) => fetch.get('/activity/goods-manage/group', param)

/**
 * 编辑商品管理数据
 * groupId 	商品管理组ID
 * content	提交数据json格式
 */
export const goodManageEdit = (param) => fetch.post('/activity/goods-manage/edit', param)

/**
 * 商品管理活动组页面预览
 */
export const goodManageGroupPreview = (param) => fetch.get('/activity/goods-manage/group-preview', param)

/**
 * 商品管理活动组新增/编辑预览
 */
export const goodManageSaveAndPreview = (param) => fetch.post('/activity/goods-manage/save-and-preview', param)

/**
 * 商品管理活动组删除
 */
export const goodManageDelete = (param) => fetch.get('/activity/goods-manage/delete', param)

/**
 * 获取组件关联关系列表
 */
export const getRelationList = (param) => fetch.get('/component/relation/list', param)

/**
 * 新增组件关系列表
 */
export const addRelationList = (param) => fetch.post('/component/relation/add', param)

/*首页加解锁*/
export const handlerLock = (param) => fetch.get('/home/'+ site_code +'page/lock', param)

/*首页加解锁*/
export const ZF_handlerLock = (param) => fetch.get('/home/zf/page/pipeline-lock', param)

/*首页删除*/
export const deleteIndex = (param) => fetch.get('/home/'+ site_code +'page/delete', param)

/*首页删除*/
export const ZF_deleteIndex = (param) => fetch.get('/home/zf/page/pipeline-delete', param)

/*获取列表ID的详细信息*/
export const indexDetail = (param) => fetch.get('/home/page/get', param)

/**
 * 获取 M端 活动列表
 */
export const getMActivityList = (param) => fetch.get('/activity/convert/creator-wap-lists', param)

/**
 * 获取 M端 活动列表
 */
export const DL_getMActivityList = (param) => fetch.get('/activity/dl/convert/creator-wap-lists', param)

/**
 * 获取 M端 活动列表
 */
export const ZF_getMActivityList = (param) => fetch.get('/activity/zf/convert/creator-wap-lists', param)

/**
 * 专题与活动管理 - pc 端页面转 M 端页面
 */
export const convertToMPage = (param) => fetch.post('/activity/convert/pc-convert-wap', param)

/**
 * 专题与活动管理 - pc 端页面转 M 端页面
 */
export const ZF_convertToMPage = (param) => fetch.post('/activity/zf/convert/pc-convert-wap', param)

/**
 * 活动推广管理 - pc 端页面转 M 端页面
 */
export const advertisementConvertToMPage = (param) => fetch.post('/advertisement/convert/pc-convert-wap', param)

/**
 * 编辑组件关系列表
 */
export const editRelationList = (param) => fetch.post('/component/relation/edit', param)

/**
 * 启用/禁用组件模板关联关系
 */
export const disableRelationList = (param) => fetch.post('/component/relation/change-status', param)

/**
 * 查看活动页访问链接
 */
export const getAccessLink = (param) => fetch.get('/activity/page/get-page-newest-urls', param)

/**
 * 查看活动页访问链接
 */
export const ZF_getAccessLink = (param) => fetch.get('/activity/zf/page/get-page-newest-urls', param)

/**
 * 查看活动页访问链接
 */
export const DL_getAccessLink = (param) => fetch.get('/activity/dl/page/get-page-newest-urls', param)

/**
 * 活动推广管理 - 查看活动页访问链接
 */
export const advertisementGetAccessLink = (param) => fetch.get('/advertisement/page/get-page-newest-urls', param)

/**
 * 专题与活动管理 - 活动页发布页面
 */
export const actReleased = (param) => fetch.post('/activity/design/release', param)

/**
 * 专题与活动管理 - 活动页发布页面
 */
export const DL_actReleased = (param) => fetch.post('/activity/dl/design/release', param)

/**
 * 活动推广管理 - 活动页发布页面
 */
export const adActReleased = (param) => fetch.post('/advertisement/design/release', param)

/**
 * 查看首页访问链接
 */
export const getHomeLink = (param) => fetch.get('/home/'+ site_code +'page/get-page-newest-urls', param)

/**
 * 查看首页访问链接
 */
export const ZF_getHomeLink = (param) => fetch.get('/home/zf/page/pipeline-newest-urls', param)

/**
 * 首页发布页面
 */
export const homeReleased = (param) => fetch.post('/home/'+ site_code +'design/release', param)

/**
 * 首页发布页面
 */
export const ZF_homeReleased = (param) => fetch.post('/home/zf/design/release', param)

/**
 * 上线首页A(上线到线上)
 */
export const setAsHomePageA = (param) => fetch.post('/home/'+ site_code +'design/online', param)

/**
 * 上线首页B(上线到测试)
 */
export const setAsHomePageB = (param) => fetch.post('/home/'+ site_code +'design/online-b', param)

/**
 * 上线首页A(上线到线上)
 */
export const ZF_setAsHomePageA = (param) => fetch.post('/home/zf/design/online', param)

/**
 * 上线首页B(上线到测试)
 */
export const ZF_setAsHomePageB = (param) => fetch.post('/home/zf/design/online-b', param)

/**
 * 一键刷新头尾部
 */
export const refreshHome = (param) => fetch.post('/home/page/refresh-site-page', param)

/**
 * IPS活动列表1
 * site_code
 * activity_id
 */
export const getIPSActivityLevel0 = (param) => fetch.get('/soa/ips/activity-list', param)

/**
 * IPS活动列表2
 * activity_id
 */
export const getIPSActivityLevel1 = (param) => fetch.get('/soa/ips/activity-group-list', param)

/**
 * IPS活动列表3
 * activity_child_group_id
 */
export const getIPSActivityLevel2 = (param) => fetch.get('/soa/ips/activity-child-list', param)


/**
 * 首页日志
 */

/** 首页日志列表 */
export const homeLogList = (param) => fetch.get('/base/home-log/list', param)
/* 首页日志列表 Zaful */
export const homeLogListWithZaful = (param) => fetch.get('/base/zf/home-log/list', param)

/* 首页日志列表 Dresslily */
export const DL_homeLogList = (param) => fetch.get('/base/dl/home-log/list', param)

/* 首页版本回滚 */
export const rollBackPage = (param) => fetch.post('/home/page/rollback', param)
/* 首页版本回滚 Zaful */
export const rollBackPageWithZaful = (param) => fetch.post('/home/zf/page/rollback', param)

/* 首页版本回滚 DressLily */
export const DL_rollBackPage = (param) => fetch.post('/home/dl/page/rollback', param)

/**
 * 首页版本回滚列表
 * @param {string} site_code 站点简码
 * @param {string} page_id 页面id
 * @param {string} pipeline 渠道简码
 * @param {string} lang 语言简码
 * @returns {*|Promise|Promise<any>}
 */
export const rollBackLists_ZF = (param) => fetch.get('/base/zf/home-log/history-version',param)

/**
 * 首页版本回滚选择
 * @param {number} log_id 日志id
 * @returns {*|Promise|Promise<any>}
 */
export const rollBackSelect_ZF = (param) => fetch.get('/home/zf/page/rollback',param)
/**
 * 数据统计
 * @param { String } - platform - 终端维度：pc,m,ios,android,others,all（选填）
 * @param { int } - is_new - 新老客标识:0 , 1 , 2 [说明:2是整体的数据,1 代表新客,0 代表老客]（选填）
 * @param { String } - site_code - 站点简称（rolegal,zaful,rosewholesale）（必填）
 * @param { String } - start_time - 开始时间（选填）
 * @param { String } - end_time - 结束时间（选填）
 * @param { int } - pageSize - 每页显示数（选填）
 * @param { int } - pageNo - 页码（选填）
 */
export const getDataReport = (param) => fetch.get('/base/report/index', param)

/**
 * 获取首页列表
 * @param { String } site_code - 站点编码
 * @param { String } keyword - 搜索关键字
 */
export const getHomePageList = (param) => fetch.get('/base/report/home-page-list', param)

/**
 * 获取活动页列表
 * @param { String } site_code - 站点编码
 * @param { String } keyword - 搜索关键字
 */
export const getActivityPageList = (param) => fetch.get('/base/report/special-page-list', param)

/**
 * 获取活动页列表 platform=pc&show_type=1&buyer_identity=2&page_id=0&start_time=0&end_time=0
 * @param { String } platform - 平台 pc,m,all
 * @param { Number } show_type - 汇总或明细 1,2
 * @param { Number } buyer_identity - 整体、新客或老客 0,1,2
 * @param { Number } page_id - 页面id
 * @param { String } start_time - 开始时间
 * @param { String } end_time - 结束时间
 */
export const getReportActivityList = (param) => fetch.get('/base/report/special-page-total-data-list', param)

/**
 * 获取广告活动页列表
 * @param { String } page_id - 子页面ID
 */
export const getActivityPageComponentList = (param) => fetch.get('/base/report/special-page-component-list', param)

/**
 * 获取广告活动页列表
 * @param { String } platform - 平台 pc,m,all
 * @param { Number } view_type - 广告位或坑位 1,2
 * @param { Number } page_id - 页面id
 * @param { String } start_time - 开始时间
 * @param { String } end_time - 结束时间
 */
export const getReportActivityAdList = (param) => fetch.get('/base/report/special-page-detail-data-list', param)

/**
 * 获取首页列表 platform=pc&show_type=1&buyer_identity=2&page_id=0&start_time=0&end_time=0
 * @param { String } platform - 平台 pc,m,all
 * @param { Number } show_type - 汇总或明细 1,2
 * @param { Number } buyer_identity - 整体、新客或老客 0,1,2
 * @param { String } start_time - 开始时间
 * @param { String } end_time - 结束时间
 */
export const getReportHOmeList = (param) => fetch.get('/base/report/home-page-total-data-list', param)

/**
 * 获取广告首页列表
 * @param { String } platform - 平台 pc,m,all
 * @param { Number } view_type - 广告位或坑位 1,2
 * @param { Number } page_id - 页面id
 * @param { String } start_time - 开始时间
 * @param { String } end_time - 结束时间
 */
export const getReportHomeAdList = (param) => fetch.get('/base/report/home-page-detail-data-list', param)

/**
 * 获取广告首页列表
 * @param { String } page_id - 子页面ID
 */
export const getHomePageComponentList = (param) => fetch.get('/base/report/home-page-component-list', param)

/**
 * OBS选品
 */

/* 主题列表 */
export const obsLevel1 = (param) => fetch.get('/soa/obs/theme-list', param)

/* 主题下所有可用页面 */
export const obsLevel2 = (param) => fetch.get('/soa/obs/page-list', param)

/* 页面下的所有可用版块 */
export const obsLevel3 = (param) => fetch.get('/soa/obs/section-list', param)

/**
 * 活动推广 - 广告列表
 */
export const getAdvertisementList = (param) => fetch.get('/advertisement/advertisement/list', param)

/**
 * 活动推广 - 广告活动列表子页面
 */
export const getAdvertisementPages = (param) => fetch.get('/advertisement/page/list', param)

/**
 * 活动推广 - 广告活动添加
 */
export const addAdvertisement = (param) => fetch.post('/advertisement/advertisement/add', param)

/**
 * 活动推广 - 广告活动修改
 */
export const editAdvertisement = (param) => fetch.post('/advertisement/advertisement/edit', param)

/**
 * 活动推广 - 广告活动删除
 */
export const deleteAdvertisement = (param) => fetch.get('/advertisement/advertisement/delete', param)

/**
 * 活动推广 - 广告活动添加子页面
 */
export const addAdvertisementPage = (param) => fetch.post('/advertisement/page/add', param)

/**
 * 活动推广 - 广告活动编辑子页面
 */
export const editAdvertisementPage = (param) => fetch.post('/advertisement/page/batch-edit', param)

/**
 * 活动推广 - 获取产品推广地址
 */
export const getAdGoodsUrl = (param) => fetch.get('/advertisement/page/get-goods-url', param)


/**
 * GB活动推广 - 落地页列表
 */
export const gbAdGetActivityList = (param) => fetch.get('/gbad/activity/list', param)


/**
 * GB活动推广 - 添加活动
 */
export const gbAdAddActivity = (param) => fetch.post('/gbad/activity/add', param)

/**
 * GB活动推广 - 更新活动
 */
export const gbAdUpdateActivity = (param) => fetch.post('/gbad/activity/edit', param)

/**
 * GB活动推广 - 删除活动
 */
export const gbAdDeleteActivity = (param) => fetch.get('/gbad/activity/delete', param)

/**
 * GB活动推广 - 专题与活动管理 - 审核活动上线/下线
 */
export const gbAdVerifyActivity = (param) => fetch.post('/gbad/activity/verify', param)


/**
 * GB活动推广 - 获取活动页面列表
 */
export const gbAdGetPageList = (param) => fetch.get('/gbad/page/list', param)

/**
 * GB活动推广 - 获取 App 活动列表
 */
export const gbAdGetAppActivityList = (param) => fetch.get('/convert/creator-app-lists', param)

/**
 * GB活动推广 - M 端页面转 APP 端页面
 */
export const gbAdConvertToAppPage = (param) => fetch.post('/convert/wap-convert-app', param)

/**
 * GB活动推广 - 批量编辑页面属性
 */
export const gbAdBatchEditPage = (param) => fetch.post('/gbad/page/batch-edit', param)

/**
 *
 * GB活动推广 - @param {addPage} param
 */
export const gbAdBatchAddPage = (param) => fetch.post('/gbad/page/add', param)

/**
 * GB活动推广 - 删除活动页面
 */
export const gbAdDeletePage = (param) => fetch.get('/gbad/page/delete', param)

/**
 * GB活动推广 - 专题与活动管理 - 上下线活动页面
 */
export const gbAdVerifyPage = (param) => fetch.post('/gbad/page/verify', param)

/**
 * GB活动推广 - 获取自动刷新时间下拉列表
 */
export const gbAdRefreshSelete = () => fetch.get('/gbad/page/refresh-list')


/**
 * GB活动推广 - 一键刷新头尾部
 */
export const gbAdRefreshSite = (param) => fetch.post('/gbad/page/refresh-site-page', param)

/**
 * GB活动推广 - 一键刷新首页头尾部
 */
export const gbAdRefreshHomePage = (param) => fetch.post('/home/page/refresh-site-page', param)

/**
 * GB活动推广 - 加解锁活动页面
 */
export const gbAdLockingActivity = (param) => fetch.get('/gbad/activity/lock', param)

/**
 * GB活动推广 - 获取页面模板列表
 */
export const gbAdGetPageTemplateList = (param) => fetch.get('/gbad/page-tpl/list', param)

/**
 * GB活动推广 - 编辑页面模板
 */
export const gbAdUpdatePageTemplate = (param) => fetch.post('/gbad/page-tpl/edit', param)

/**
 * GB活动推广 - 删除页面模板
 */
export const gbAdDeletePageTemplate = (param) => fetch.get('/gbad/page-tpl/delete', param)

/**
 * GB活动推广 - 设置默认模板
 */
export const gbAdSetDefaultPageTemplate = (param) => fetch.get('/gbad/page-tpl/change-tpl', param)

/**
 * GB活动推广 - 查看活动页访问链接
 */
export const gbAdGetAccessLink = (param) => fetch.get('/gbad/page/get-page-newest-urls', param)

/**
 * GB活动推广 - 活动页发布页面
 */
export const gbAdActReleased = (param) => fetch.post('/gbad/design/release', param)

/**
 * GB活动推广 - 获取 M端 活动列表
 */
export const gbAdGetMActivityList = (param) => fetch.get('/gbad/convert/creator-wap-lists', param)

/**
 * GB活动推广 - pc 转 M
 */
export const gbAdConvertToMPage = (param) => fetch.post('/gbad/convert/pc-convert-wap', param)



/**
 * GB活动列表 获取渠道key列表
 */
export const GB_getChannelKeyList = () => fetch.get('/admin/language/pipeline-list')

/**
 * GB活动列表 获取渠道key列表
 */
export const GB_getChannelLangList = param => fetch.get('/activity/gb/page/pipeline-list', param)


/**
 * GB活动列表 删除对应渠道
 */
export const GB_getChannelDelList = param => fetch.post('/activity/gb/page/batch-delete', param)


/**
 * GB活动列表 - 落地页列表
 */
export const GB_getActivityList = (param) => fetch.get('/activity/gb/activity/list', param)


/**
 * GB活动列表 - 添加活动
 */
export const GB_addActivity = (param) => fetch.post('/activity/gb/activity/add', param)

/**
 * GB活动列表 - 更新活动
 */
export const GB_updateActivity = (param) => fetch.post('/activity/gb/activity/edit', param)

/**
 * GB活动列表 - 删除活动
 */
export const GB_deleteActivity = (param) => fetch.get('/activity/gb/activity/delete', param)

/**
 * GB活动列表 - 专题与活动管理 - 审核活动上线/下线
 */
export const GB_verifyActivity = (param) => fetch.post('/activity/gb/activity/verify', param)


/**
 * GB活动列表 - 获取活动页面列表
 */
export const GB_getPageList = (param) => fetch.get('/activity/gb/page/list', param)

/**
 * GB活动列表 - 获取 App 活动列表
 */
export const GB_getAppActivityList = (param) => fetch.get('/activity/gb/convert/creator-app-lists', param)

/**
 * 专题与活动管理 - pc 端页面转 M 端页面
 */
export const GB_convertToMPage = (param) => fetch.post('/activity/gb/convert/pc-convert-wap', param)

/**
 * GB活动列表 - M 端页面转 APP 端页面
 */
export const GB_convertToAppPage = (param) => fetch.post('/activity/gb/convert/wap-convert-app', param)

/**
 * GB活动列表 - IOS,Android端转换
 */
export const GB_convertToIosOrAndroid = (param) => fetch.post('/activity/gb/convert/android-convert-ios', param)

/**
 * GB活动列表 - 批量编辑页面属性
 */
export const GB_batchEditPage = (param) => fetch.post('/activity/gb/page/batch-edit', param)

/**
 *
 * GB活动列表 - @param {addPage} param
 */
export const GB_batchAddPage = (param) => fetch.post('/activity/gb/page/add', param)

/**
 * GB活动列表 - 删除活动页面
 */
export const GB_deletePage = (param) => fetch.get('/activity/gb/page/delete', param)

/**
 * GB活动列表 - 专题与活动管理 - 上下线活动页面
 */
export const GB_verifyPage = (param) => fetch.post('/activity/gb/page/verify', param)

/**
 * GB活动列表 - 获取自动刷新时间下拉列表
 */
export const GB_refreshSelete = () => fetch.get('/activity/gb/page/refresh-list')


/**
 * GB活动列表 - 一键刷新头尾部
 */
export const GB_refreshSite = (param) => fetch.post('/activity/gb/page/refresh-site-page', param)

/**
 * GB活动列表 - 一键刷新首页头尾部
 */
export const GB_refreshHomePage = (param) => fetch.post('/home/page/refresh-site-page', param)

/**
 * GB活动列表 - 加解锁活动页面
 */
export const GB_lockingActivity = (param) => fetch.get('/activity/gb/activity/lock', param)

/**
 * GB活动列表 - 获取页面模板列表
 */
export const GB_getPageTemplateList = (param) => fetch.get('/activity/gb/page-tpl/list', param)

/**
 * GB活动列表 - 编辑页面模板
 */
export const GB_updatePageTemplate = (param) => fetch.post('/activity/gb/page-tpl/edit', param)

/**
 * GB活动列表 - 删除页面模板
 */
export const GB_deletePageTemplate = (param) => fetch.get('/activity/gb/page-tpl/delete', param)

/**
 * GB活动列表 - 设置默认模板
 */
export const GB_setDefaultPageTemplate = (param) => fetch.get('/activity/gb/page-tpl/change-tpl', param)

/**
 * GB活动列表 - 查看活动页访问链接
 */
export const GB_getAccessLink = (param) => fetch.get('/activity/gb/page/get-page-newest-url-list', param)

/**
 * GB活动列表 - 活动页发布页面
 */
export const GB_actReleased = (param) => fetch.post('/activity/gb/design/release', param)

/**
 * GB活动列表 - 获取 M端 活动列表
 * platform {pc,wap,ios,android}
 */
export const GB_getChannelUrlList = param => fetch.get('/activity/gb/design/preview-list', param)

export const GB_getGbadChannelUrlList = (param) => fetch.get('/gbad/design/preview-list', param)

/**
 * GB活动列表 - 获取 M端 活动列表
 * platform {pc,wap,ios,android}
 */
export const GB_getMActivityList = (param, options) => fetch.get('/activity/gb/convert/creator-wap-lists', param, options)


/**
 * GB活动列表 - 新增渠道信息
 * @param param 传参对象
 */
export const GB_editAdd = (param) => fetch.post('/activity/gb/activity/edit-add', param)


/**
 * zaful国家站 上下线页面
 * group_id
 * activity_id  /activity/zf/page/pipeline-newest-urls?group_id=ecd77737e7fbe69f9fcc313f9377f986&activity_id=11
 */
export const ZF_viewPipelineNewestUrl = (param, options) => fetch.get('/activity/zf/page/pipeline-newest-urls', param, options)

/**
 * zaful国家站 pc，m，app转化获取被转化的渠道信息
 * @param group_id=xx
 * @param activity_id=1
 */
export const ZF_checkPipelinePages = (param) => fetch.get('/activity/zf/convert/check-pipeline-pages-design', param)


/**
 * @description 获取组件模板 模板列表
 * @param { String } site_code 站点简码
 */
export const getUiComponentList = (param) => fetch.get('/activity/page-ui-tpl/ui-component-list', param)

/**
 * @description 获取组件模板列表
 * @param { String } site_code 站点简码
 * @param { Int } view_type
 * @param { Int } place_type
 * @param { Int } ui_key
 * @param { Int } pageNo
 * @param { Int } pageSize
 */
export const getPageUiTplList = (param) => fetch.get('/activity/page-ui-tpl/list', param)

/**
 * @description 编辑组件模板列表
 * @param { Int } id 组件模板ID
 * @param { String } name
 * @param { Int } view_type
 */
export const editPageUiTplList = (param) => fetch.post('/activity/zf/page-ui-tpl/edit', param)

/**
 * @description 删除组件模板列表
 * @param { Int } id 组件模板ID
 */
export const deletePageUiTplList = (param) => fetch.post('/activity/page-ui-tpl/delete', param)

/**
 * @description 获取组件模板 模板列表
 * @param { String } site_code 站点简码
 */
export const ZF_getUiComponentList = (param) => fetch.get('/activity/zf/page-ui-tpl/ui-component-list', param)

/**
 * @description 获取组件模板列表
 * @param { String } site_code 站点简码
 * @param { Int } view_type
 * @param { Int } place_type
 * @param { Int } ui_key
 * @param { Int } pageNo
 * @param { Int } pageSize
 */
export const ZF_getPageUiTplList = (param) => fetch.get('/activity/zf/page-ui-tpl/list', param)

/**
 * @description 删除组件模板列表
 * @param { Int } id 组件模板ID
 */
export const ZF_deletePageUiTplList = (param) => fetch.post('/activity/zf/page-ui-tpl/delete', param)

/**
 * @description 获取组件模板 模板列表
 * @param { String } site_code 站点简码
 */
export const DL_getUiComponentList = (param) => fetch.get('/activity/dl/page-ui-tpl/ui-component-list', param)

/**
 * @description 获取组件模板列表
 * @param { String } site_code 站点简码
 * @param { Int } view_type
 * @param { Int } place_type
 * @param { Int } ui_key
 * @param { Int } pageNo
 * @param { Int } pageSize
 */
export const DL_getPageUiTplList = (param) => fetch.get('/activity/dl/page-ui-tpl/list', param)

/**
 * @description 编辑组件模板列表
 * @param { Int } id 组件模板ID
 * @param { String } name
 * @param { Int } view_type
 */
export const DL_editPageUiTplList = (param) => fetch.post('/activity/dl/page-ui-tpl/edit', param)

/**
 * @description 删除组件模板列表
 * @param { Int } id 组件模板ID
 */
export const DL_deletePageUiTplList = (param) => fetch.post('/activity/dl/page-ui-tpl/delete', param)

/**
 * @description 装修页三端渠道数据
 * @param { Int } id 组件模板ID
 */
export const ZF_getDesignPlatForm = (param) => fetch.get('/activity/zf/sync-platform/platform-options',param)


/**
 * @description 装修页获取组件列表sync-platform
 * @param { Int } id 组件模板ID
 */
export const ZF_designcomponentSelect = (param) => fetch.get('/activity/zf/sync-platform/component-select',param)

/**
 * @description 装修页三端保存
 * @param
 */
export const ZF_designBatchSave = (param) => fetch.post('/activity/zf/sync-platform/batch-save-from',param)

/**
 * @description 装修页三端保存
 * @param
 */
export const ZF_designBatchBind = (param) => fetch.post('	/activity/zf/sync-platform/batch-bind',param)

/**
 * @description 装修页三端获取配置
 * @param
 */
export const ZF_designBatchFrom = (param,options) => fetch.post('/activity/zf/sync-platform/get-batch-from-data',param,options)


/**
 * @description 三端删除组件绑定
 * @param
 */
export const ZF_syncPlatformDelBind = (param,options) => fetch.post('/activity/zf/sync-platform/delete-bind',param)


/**
 * @description 三端SKU校验
 * @param
 */
export const ZF_syncCheckGoodsExists = (param,options) => fetch.post('/activity/zf/sync-platform/check-goods-exists',param,options)

/**
 * @description 三端SKU校验
 * @param
 */
export const ZF_syncOperateLog = (param,options) => fetch.post('/activity/zf/page/get-operate-log-list',param,options)

/**
 * 根据站点获取语种列表
 * @param {string} param.site_code 站点编码，例如：zf-pc
 */
const getLangCodeList = (param) => fetch.get('/base/language-package/list', param)

/**
 * 新增语种
 * @param {string} options.site_code 站点编码
 * @param {string} options.name 语言名称
 * @param {string} options.code 语言简码
 */
const postAddLang = (param) => fetch.post('/base/language-package/add-lang', param)

/**
 * 设置/取消常用活动接口 ZF
 * @param {string} param.id 活动ID
 */
export const ZF_getFrequently = (param) => fetch.get('/activity/zf/activity/frequently', param)

/**
 * 设置/取消常用活动接口 DL
 * @param {string} param.id 活动ID
 */
export const DL_getFrequently = (param) => fetch.get('/activity/dl/activity/frequently', param)

/**
 * 设置/取消常用活动接口 RG
 * @param {string} param.id 活动ID
 */
export const getFrequently = (param) => fetch.get('/activity/activity/frequently', param)

/**
 * 获取语言键值对的数据
 * @param {string} param.lang 语言简码  
 */
export const getLangKeysList = (param) => fetch.get('/base/language-data/list', param);

/**
 * 获取APP跳转类型，站点信息
 * @param param
 */
export const getDeepLinkData = (param) => fetch.get('/base/tools/edm-deep-link-ui-data',param);

/**
 * 合并生成Deeplink链接
 * @param param
 */
export const postDeepLink = (param) => fetch.post('/base/tools/generate-edm-deep-link-url',param);

/**
 * 导出语言包
 * @param {string} param.lang 选择的语种 en,es,de
 * @param {string} param.export_data 下载导入错误语言包时传此参数,内容为到导入接口返回的data内容
 */
const exportLangPackage = (param) => fetch.postDownLoad('/base/language-data/export-package', param);

/**
 * 新增多语言KEY
 * @param {string} param.key 多语言健名,   add,sell,done
 * @param {string} param.lang_zn 多语言值名，  增加,售卖,完成
 */
const addLangKeys = (param) => fetch.post('/base/language-data/add-keys', param);

/**
 * 导入语言包
 * @param {string} param.lang 选择的语种 en,es,de
 * @param {string} param.siteCode 站点编码
 */
const importLangKeys = (param) => fetch.post('/base/language-data/import-package', param, { type: 'file' });

/**
 * 
 * @param {string} param.key 键名
 * @param {string} param.value 键值
 * @param {string} param.lang 语种
 * @param {string} param.site_code 站点编码
 */
const editLangKeys = (param) => fetch.post('/base/language-data/edit-key', param);

/**
 * 获取组件绑定的UI组件模板
 * @param {string} param.key 语言键值
 */
const getLangKey4Component = (param) => fetch.get('/base/language-data/key-component', param);


/**
 * 获取装修页面数据
 * @param {string} group_id 页面ID
 * @param {string} pipeline 渠道。例如：ZF
 * @param {string} lang 语言，例如：en
 */
export const design_get_page_info = (param) => fetch.get('/activity/zf/native-design/native-index', param);

/**
 * 装修页-保存页面数据
 * @param {string} group_id 页面ID
 * @param {string} pipeline 渠道。例如：ZF
 * @param {string} lang 语言，例如：en
 * @param {array} layouts 布局数据
 * @param {array} components 组件数据
 */
export const design_save_page_info = (param) => fetch.post('/activity/zf/ui-design/save-native-form', param);

/**
 * 装修页，素材管理，获取目录列表
 * @param {*} param 
 */
const design_material_folder = (param) => fetch.get('/admin/resource/folder-tree', param);

/**
 * 装修页，素材管理，获取目录内容
 * @param {string} param.id 目录的ID
 */
const design_material_folder_detail = (param) => fetch.get('/admin/resource/list', param);

/**
 * 装修页，素材管理，创建目录
 * @param {*} param 
 */
const design_material_folder_add = (param) => fetch.post('/admin/resource/add', param);

/**
 * 装修页，发布，勾选渠道推送信息
 * @param {string} page_id 页面ID 8029
 * @param {string} group_id d9f45612b3982f8af61870dfebea9d7d
 * @param {string} pipeline 渠道 ZFIT
 */
const design_reload_release = (param) => fetch.get('/activity/zf/design/preload-release', param, { parallel: true });
const design_release = (param) => fetch.post('/activity/zf/native-design/batch-release', param, { timeout: 300000 });
const design_release_again = (param) => fetch.post('/activity/zf/native-design/release', param);


/**
 * 装修页， 权限控制，发送续命请求
 * @param {String} page_id 页面ID
 */
export const design_auth_udpate_status = (param) => fetch.get('/activity/zf/native-design/acquire-design-lock', param);

/**
 * 装修页面，根据SKU，或者规则获取对应的商品数据
 * @param {*} page_id 11745
 * @param {*} site_code zf-wap
 * @param {*} pipeline zf
 * @param {*} lang en
 * @param {*} sku_info [{"id":"1573012330632","type":"1","component_id":"1573012279841","sku":"222799103,269004202,255989505,209931301"}]
 */
export const design_get_goods_by_type = (param) => fetch.post('/geshop/design/goodsInfo', param);

/**
 * SOA 选品系统，规则列表
 * @param {string} param.site_code 当前站点编码（包括渠道）， 例如 zf（全球站）
 * @param {string} param.keyword 搜索关键字
 */
export const soa_rule_list = (param) => fetch.get('/soa/sop/rule-list', param);




/**
 * 获取下线页面数量
 * return {"code":0,"message":"success","data":{"page-num":1000}
 * @param {Number} offline 是否下线页面，查询数据不用传这个参数
 * @param {String} param.page_creat_time 页面创建时长，1：一年前创建的页面，2:六个月前创建的页面，3：三个月后下线页面
 * @param {String} param.port 页面所属平台，可以取值pc，wap，app
 * @param {String} param.piple 页面所属渠道，取之渠道简码
 */
export const get_clear_page = (param) => fetch.get('/base/tools/get-page-number', param);


/**
 * 页面下线
 * return {"code":0,"message":"success","data":{"page-num":1000}}
 * @param {String} param.page_creat_time 页面创建时长，1：一年前创建的页面，2:六个月前创建的页面，3：三个月后下线页面
 * @param {String} param.port 页面所属平台，可以取值pc，wap，app
 * @param {String} param.piple 页面所属渠道，取之渠道简码
 * @param {Number} offline 是否下线页面，查询数据不用传这个参数
 */
export const send_clear_page = (param) => fetch.get('/base/tools/get-page-number', param);



export default {
    getLangCodeList,
    postAddLang,
    getLangKeysList,
    exportLangPackage,
    addLangKeys,
    importLangKeys,
    editLangKeys,
    getLangKey4Component,
    ZF_getPageTplAdd,
    ZF_uploadPic,
    design_material_folder,
    design_material_folder_detail,
    design_material_folder_add,
    design_reload_release,
    design_release,
    design_release_again,
    design_auth_udpate_status
}

/* 清除页面缓存 */
export const clear_url_cache = (param) => fetch.get('/base/tools/link-cache-clear-api', param);

/**
 * 获取装修页的PHP接口
 * @param {*} param 
 */
export const get_native_apis = (param) => fetch.get('/activity/zf/native-design/native', param);