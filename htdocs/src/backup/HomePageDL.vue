<template>
	<site-layout @publicReady="publicReady">
		<el-row :span="24" class="geshop-index-tit">
			<span class="geshop-index-title">首页管理</span>
		</el-row>
		<el-row class="geshop-index-btn">
			<el-col>
				<el-button class="geshop-index-btn-add" @click="addIndex">
					<span class="icon-geshop-pack-up"></span>
					<span class="geshop-icon-add-text">新增首页</span>
				</el-button>
			</el-col>
			<el-col>
				<el-button disabled class="geshop-index-btn-refresh" @click="refreshHomePage">
					<span class="icon-geshop-reset"></span>
					<span class="geshop-icon-refresh-text">一键刷新头尾部</span>
				</el-button>
			</el-col>
			<el-col>
				<el-input class="geshop-index-btn-search" v-model="searchWord" placeholder="请输入首页名称" @change="doSearch">
					<i slot="suffix" class="geshop-icon-search-gray"></i>
				</el-input>
			</el-col>
		</el-row>
		<el-row>
			<el-col :span="24" class="geshop-index-lists">
				<template>
					<el-tabs v-model="homeTabName" type="card" @tab-click="handleHomeTabClick" class="tab-header">
						<el-tab-pane v-for="(item,key) in places" :label="item.platform_name" :name="key+''" :key="key"></el-tab-pane>
					</el-tabs>
				</template>
				<el-table :data="homeList" style="width: 100%">
					<el-table-column width="48"></el-table-column>
					<el-table-column prop="id" label="ID" width="100"></el-table-column>
					<el-table-column label="首页名称" width="240" class-name="zf-home-page-title">
						<template slot-scope="scope">
							<span class="page-title-tip">
                                <i class="home-tip home_a" v-if="scope.row.status == '2' && scope.row.home_type == '0'">A首页</i>
                                <i class="home-tip home_b" v-if="scope.row.status == '2' && scope.row.home_type == '1'">B首页</i><span>{{ scope.row.page_languages[0].title }}</span>
                                <i class="new-channel" v-if="scope.row.has_new_pipeline">有新增渠道</i></span>
						</template>
					</el-table-column>
					<el-table-column prop="status_name" label="状态"></el-table-column>
                    <el-table-column prop="create_name" align="center" label="创建人" width="120"></el-table-column>
					<el-table-column label="创建时间" width="160">
						<template slot-scope="scope">
							<span>{{ parseInt(scope.row.create_time)|moment('YYYY-MM-DD HH:mm:ss')}}</span>
						</template>
					</el-table-column>
                    <el-table-column prop="update_user" label="最后操作人" align="center" width="130"></el-table-column>
					<el-table-column label="最后操作时间" align="center" width="160">
						<template slot-scope="scope">
							<span>{{ parseInt(scope.row.update_time)|moment('YYYY-MM-DD HH:mm:ss')}}</span>
						</template>
					</el-table-column>
					<el-table-column label="操作" width="450" class="geshop-index-more" class-name="ge-home-list-operating">
						<template slot-scope="scope">
							<el-tooltip content="装修" placement="bottom" effect="light">
								<el-button class="icon-geshop-decorate" v-if="scope.row.is_lock == 1" @click="decorate_redirect(scope.row.design_url, scope.row.is_lock,scope.row.create_user,has_permissions)" style="font-size: 24px;"></el-button>
							</el-tooltip>
							<el-tooltip content="装修" placement="bottom" effect="light">
								<el-button class="icon-geshop-decorate is-lock" v-if="scope.row.is_lock == 2" @click="decorate_redirect(scope.row.design_url, scope.row.is_lock,scope.row.create_user,has_permissions)" style="font-size: 24px;"></el-button>
							</el-tooltip>
                            <!-- 关闭发布按钮 -->
<!--							<el-tooltip content="发布" placement="bottom" effect="light">-->
<!--								<el-button class="icon-geshop-online" v-if="scope.row.status == 1" @click="releasePage(scope.row.id)" style="font-size: 24px;"></el-button>-->
<!--							</el-tooltip>-->
                            <!-- 关闭设为首页按钮 -->
<!--							<el-tooltip content="首页" placement="bottom" effect="light">
								<el-button class="icon-geshop-home-page" v-if="scope.row.status == 3 || scope.row.status == 5 || scope.row.status == 8" @click="setAsHomePage(scope.row)" style="font-size: 24px;"></el-button>
							</el-tooltip>-->
<!--							<el-tooltip content="预览" placement="bottom" effect="light">
								<el-button class="icon-geshop-search" @click="previewPages(scope.row.group_languages)" style="font-size: 24px;"></el-button>
							</el-tooltip>-->
                            <el-tooltip content="线上预览" placement="bottom" effect="light" v-if="scope.row.status == 2 || scope.row.status == 3 ">
                                <el-button class="icon-geshop-search" @click="previewPagesNew(scope.row.id, scope.row.status,scope.row.urls)" style="font-size: 24px;"></el-button>
                            </el-tooltip>
							<el-tooltip content="查看访问链接" placement="bottom" effect="light">
								<el-button class="icon-geshop-view-link-2" v-if="scope.row.status == 2 || scope.row.status == 7 || scope.row.status == 8" @click="viewPages(scope.row.id, scope.row.status)" style="font-size: 24px;"></el-button>
							</el-tooltip>
							<el-dropdown split-button style="margin-left:10px">
								<el-dropdown-menu slot="dropdown">
									<el-dropdown-item type="primary" size="small" @click.native="editIndex(scope.row, scope.row.is_lock, scope.row.create_user)">编辑</el-dropdown-item>
									<el-dropdown-item type="danger" size="small" v-if="Number(scope.row.status) !== 2" @click.native="removeIndex(scope.row.group_id, scope.row.is_lock, scope.row.create_user)">删除</el-dropdown-item>
									<el-dropdown-item type="primary" @click.native="viewDetail(scope.row)" size="small">查看二维码</el-dropdown-item>
								</el-dropdown-menu>
							</el-dropdown>
							<el-switch v-model="scope.row.lock_status" inactive-text="锁定" @change="lockingIndex(scope.row.group_id, scope.row.is_lock, scope.row.create_user)" style="margin-left: 10px;"></el-switch>
						</template>
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>
		<el-dialog title="首页访问地址" custom-class="geshop-index-access-dialog" :visible.sync="dialogLinksVisible" class="geshop-index-preview-address">
			<el-row>
				<el-button v-for="(item,index) in pageLinks" type="primary" :key="index" @click="redirect(item.page_url)">{{ item.name }}</el-button>
				<!-- <p>{{ tips }}
					<el-button @click="redistribution()" v-if="tips" type="primary">重新发布</el-button>
				</p> -->
			</el-row>
		</el-dialog>
		<el-dialog title="首页预览地址" :visible.sync="dialogPreviewLinksVisible" class="geshop-index-preview-address geshop-index-preview-dialog">
			<el-row>
                <!-- 改用语言列表预览 -->
                <!--<el-button v-for="item in previewLinks" type="primary" :key="item.pipeCode" @click="redirect(item.page_url)">{{ item.pipelineName }}</el-button>-->
                <el-button v-for="item in previewLinksNew" type="primary" :key="item.code" @click="redirect(item.page_url)">{{ item.name }}</el-button>
			</el-row>
		</el-dialog>
		<el-dialog :visible.sync="isDetailActive" width="360px" @close='detailClose'>
			<el-card>
				<div slot="header" class="text-center">{{ currentActivityRow.page_languages[0].title }}</div>
				<div>
					<el-row v-if="Boolean(currentActivityRow.qrcode) || Boolean(currentActivityRow.preview)">
						<el-col :span="24">
							<h3>活动预览地址：</h3>
						</el-col>
						<el-col :span="24" class="text-center" v-if="Boolean(currentActivityRow.qrcode)"><img alt="二维码" :src="currentActivityRow.qrcode" width="120"></el-col>
						<el-col :span="24" class="text-center" v-if="Boolean(currentActivityRow.preview)">
							<a :href="currentActivityRow.preview" class="index-detail-link">{{ currentActivityRow.page_languages[0].title }}</a>
						</el-col>
					</el-row>
				</div>
			</el-card>
		</el-dialog>
		<el-row v-if="total > 10">
			<el-col :span="24" class="text-right geshop-article-page">
				<el-pagination layout="sizes, prev, pager, next" :page-size="pageSize" :total="total" :current-page.sync="options.pageNo" @current-change="handleCurrentChange" @size-change="handleSizeChange"></el-pagination>
			</el-col>
		</el-row>

        <el-dialog :title="dialogName" :visible.sync="dialogAddVisible" @close="subDialogClose"
                   class="geshop-new-index">
            <el-tag type="danger" v-if="Boolean(indexForm.id) && this.has_new_pipeline">有新增渠道信息请补充完整</el-tag>
            <el-form :model="publicIndexForm" :rules="publicPageRules" ref='publicIndexForm'>
                <el-form-item label="页面名称" prop="title">
                    <el-input @change="updateIndexTitle" v-model="publicIndexForm.title"
                              v-on:keyup.native="handleTitleKeyup" placeholder="请输入页面名称"></el-input>
                    <span class="count-tip-box">{{ titleCount }}/100</span>
                </el-form-item>
            </el-form>
            <el-form :model="indexForm" :rules="pageRules" ref='indexForm'>
                <el-form-item class="home-page-place" label="应用端口" prop="place" v-if="!Boolean(indexForm.id)">
                    <div>
                        <el-checkbox-group :disabled="placesDisabled" @change="handleCheckedPlacesChange"
                                           v-model="indexForm.place">
                            <!-- <el-checkbox v-for="(item,key) in places" :disabled="key == indexForm.site_code" :label="item.platform_name" :name="key"></el-checkbox> -->
                            <el-checkbox :disabled="key == indexForm.site_code" :key="key"
                                         :label="key" :name="key" v-for="(item,key) in places">{{ item.platform_name }}
                            </el-checkbox>
                        </el-checkbox-group>
                    </div>
                </el-form-item>
            </el-form>
            <el-form class="home_add_piepeline">
                <el-form-item class="pipeline_list" label="渠道">
                    <el-tabs @tab-click="handleTabClick($event,'channel')" type="card" v-model="currentPipelineCode">
                        <!-- 编辑时显示该首页支持的语言 -->
                        <template v-if="indexForm.id != ''">
                            <el-tab-pane
                                :key="item.code"
                                :label="item.name"
                                :name="item.code"
                                v-for="item in editPipelineList">
                                <el-form-item class="pipeline_list_lang" label="语言">
                                    <!--<p style="display:inline">{{ currentLanguageName }}</p>-->
                                    <el-tabs @tab-click="handleTabClick($event,'lang')" type="card"
                                             v-model="currentLanguageCode">
                                        <el-tab-pane :key="childKey"
                                                     :label="childItem.lang_name || childItem.name"
                                                     :name="childItem.lang || childItem.code"
                                                     v-for="(childItem,childKey,childIndex) in item.lang_list">
                                        </el-tab-pane>
                                    </el-tabs>
                                </el-form-item>
                            </el-tab-pane>
                        </template>
                        <!-- 新增时显示所有语言 -->
                        <template v-if="indexForm.id == ''">
                            <el-tab-pane
                                :key="item.code"
                                :label="item.name"
                                :name="item.code"
                                v-for="item in allSupportPipelines">
                                <el-form-item class="pipeline_list_lang" label="语言">
                                    <!--<p style="display:inline">{{ currentLanguageName }}</p>-->
                                    <el-tabs @tab-click="handleTabClick($event,'lang')" type="card"
                                             v-model="currentLanguageCode">
                                        <el-tab-pane :key="childIndex"
                                                     :label="childItem.name" :name="childItem.code"
                                                     v-for="(childItem,childKey,childIndex) in item.lang_list">
                                        </el-tab-pane>
                                    </el-tabs>
                                </el-form-item>
                            </el-tab-pane>
                        </template>
                    </el-tabs>
                </el-form-item>

            </el-form>

            <el-form :model="indexForm" :rules="pageRules" ref='indexForm'>

                <!-- <p style="margin:0 0 5px 0;color:#b7b7b7;" v-if="!Boolean(indexForm.id)">备注：此处的语言为站点的所有语言，如果某端口无对应的语言时，同步时，会自动过滤</p> -->
                <!-- <el-form-item label="名称" prop="title" class="new-index-title"> -->


                <!-- <el-form-item label="页面模板" prop="model" class="new-index-model"> -->
                <el-form-item class="new-index-model" label="页面模板" prop="model"
                              style="margin-top:10px" v-if="this.pc_status && !Boolean(indexForm.id)">
                    <el-button @click="handleModelTempSelect('web')" size="small" type="primary">选择模板</el-button>
                    <el-tag type="info">{{ indexForm.tpl_name }}</el-tag>
                </el-form-item>
                <el-form-item class="new-index-model" label="M端页面模板" prop="model"
                              v-if="this.m_status && !Boolean(indexForm.id)">
                    <el-button @click="handleModelTempSelect('app')" size="small" type="primary">选择模板</el-button>
                    <el-tag type="info">{{ indexForm.m_tpl_name }}</el-tag>
                </el-form-item>
                <el-form-item label="SEO标题" prop="seo_title" style="margin-top:5px;">
                    <el-input @change="updateIndexSeoTitle" placeholder="有利于SEO优化" v-model="indexForm.seo_title"
                              v-on:keyup.native="handleSEOTitleKeyup"></el-input>
                    <span class="count-tip-box">{{ SEOTitleCount }}/200</span>
                </el-form-item>
                <el-form-item label="SEO关键字" prop="keywords" style="margin-top:5px">
                    <el-input @change="updateIndexSeoKeywords" placeholder="有利于SEO优化" v-model="indexForm.keywords"
                              v-on:keyup.native="handleSEOKeywordsKeyup"></el-input>
                    <span class="count-tip-box">{{ SEOKeywordsCount }}/200</span>
                </el-form-item>
                <!-- <el-form-item label="SEO 标题" prop="seo_title" class="new-index-seo-title">
                    <el-input type="text" v-model="indexForm.seo_title" placeholder="有利于SEO优化" @change='handleSEOTitle' v-on:keyup.native="handleSEOTitleKeyup"></el-input>
                    <span class="count-tip-box">{{ SEOTitleCount }}/100</span>
                </el-form-item>
                <el-form-item label="SEO 关键字" prop="keywords" class="new-index-keywords">
                    <el-input type="text" v-model="indexForm.keywords" placeholder="有利于SEO优化" @change='handleSEOKeywords' v-on:keyup.native="handleSEOKeywordsKeyup"></el-input>
                    <span class="count-tip-box">{{ SEOKeywordsCount }}/100</span>
                </el-form-item> -->
                <el-form-item class="new-index-introduction" label="SEO 简介" prop="description">
                    <!-- <el-input type="textarea" v-model="indexForm.description" :rows="4" placeholder="有利于SEO优化" @change='handleSEODescription' v-on:keyup.native="handleSEODescriptionKeyup"></el-input> -->
                    <el-input :rows="4" @change='updateIndexSeoDescription' placeholder="有利于SEO优化" type="textarea"
                              v-model="indexForm.description"
                              v-on:keyup.native="handleSEODescriptionKeyup"></el-input>
                    <span class="count-tip-box">{{ SEODescriptionCount }}/200</span>
                </el-form-item>
                <el-form-item class="new-index-btns">
                    <el-button @click="resetForm('indexForm')" size="small">取消</el-button>
                    <el-button :loading="submitLoading" @click="submitForm('indexForm')" size="small" type="primary">
                        确定
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
        <el-dialog :visible.sync="modelInfo.visible" append-to-body class="geshop-template-model" title="页面模板">
            <div class="geshop-template-model-title">
                <span class="icon-geshop-backward"></span>
                <span class="geshop-template-model-word">选择页面模板</span>
            </div>
            <el-row>
                <el-tabs @tab-click="tplTabClick" class="model-dialog" type="border-card" v-loading="tplInfo.loading"
                         v-model="modelInfo.tabActive">
                    <el-tab-pane label="我的模板" name="2">
                        <div :key="index" class="model-box" v-for="(item,index) in pageTemplateList">
                            <el-radio :label="item.id" name="modelSelect" v-if="item.create_user == siteInfo.userName"
                                      v-model="modelInfo.modelSelect">
                                <div class="model-item">
                                    <img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
                                    <span>{{item.name}}</span>
                                    <div @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"
                                         class="icon-geshop-search"></div>
                                </div>
                            </el-radio>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="共用模板" name="1">
                        <div :key="index" class="model-box" v-for="(item,index) in pageTemplateList">
                            <el-radio :label="item.id" name="modelSelect"
                                      v-if="item.create_user != siteInfo.userName && item.tpl_type == 1"
                                      v-model="modelInfo.modelSelect">
                                <div class="model-item">
                                    <img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
                                    <span>{{item.name}}</span>
                                    <div @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"
                                         class="icon-geshop-search"></div>
                                </div>
                            </el-radio>
                        </div>
                    </el-tab-pane>
                    <el-row
                        v-if="pageTemplateList.length == 0|| pageTemplateList.length>0 && (modelInfo.tabActive == '2' && modelInfo.tempLength1 == 0 || modelInfo.tabActive == '1' && modelInfo.tempLength2 == 0)">
                        <el-col :span="24" style="text-align: center;margin: 20px 0;">{{pageTemplateListWarn}}</el-col>
                    </el-row>
                </el-tabs>
            </el-row>
            <div class="dialog-footer" slot="footer">
                <el-button @click="handleCancelSelectedModel" size="small">取消</el-button>
                <el-button @click="handleSureModel" size="small" type="primary">确定</el-button>
            </div>
        </el-dialog>
        <!-- 查看模板大图 -->
        <el-dialog :fullscreen="true" :visible.sync="viewModel.visible" @close="viewModelClose"
                   class="geshop-template-pic"
                   title="页面模板" width="100%">
            <el-row v-loading="pageLoading">
                <el-col class="imgPreview text-center" style="height:100%;">
                    <iframe :src="viewModel.src" class="iframePreview" frameborder="0"
                            style="width:100%;height:100%;"></iframe>
                </el-col>
            </el-row>
        </el-dialog>
        <!-- 设为首页弹窗模板 -->
        <el-dialog :visible.sync="chouseRelaseType.visible" @close="viewModelClose"
                   class="geshop-index-preview-address showChoseIndexType" title="请选择设为首页类型"
                   width="30%">
            <el-row class="channel" v-if="indexPageRecord.group_status != 3">
                <h3 class="title">渠道</h3>
                <el-checkbox-group v-model="chouseRelaseType.channelArr">
                    <el-checkbox :key="key" :label="key" v-for="item,key in home_permissions">{{ item }}</el-checkbox>
                </el-checkbox-group>
            </el-row>
            <el-row class="items">
                <el-col>
                    <el-radio-group size="medium" v-model="chouseRelaseType.indexType">
                        <el-radio :disabled="chouseRelaseType.disabledRadioAStatus" label="1">A首页</el-radio>
                        <el-radio :disabled="chouseRelaseType.radioStatus" label="2">B首页</el-radio>
                    </el-radio-group>
                </el-col>
            </el-row>
            <el-row class="tips_msg">
                <el-col>
                    A首页为正常首页，B首页为测试首页，设为A首页以后，不可以转换为B首页！
                </el-col>
            </el-row>
            <div class="dialog-footer" slot="footer">
                <el-button @click="handleCancelReleaseIndexType" size="small">取消</el-button>
                <el-button :loading="abLoading" @click="setHomePage" size="small" type="primary">确定</el-button>
            </div>
        </el-dialog>
    </site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue';
import {
    refreshHome,
    ZF_addIndex,
    ZF_deleteIndex,
    ZF_editIndex,
    ZF_getCountrySiteList,
    ZF_getHomeLink,
    ZF_getPageTemplateList,
    ZF_handlerLock,
    ZF_homeReleased,
    ZF_indexList,
    ZF_setAsHomePageA,
    ZF_setAsHomePageB
} from '../plugin/api';
import bus from '../store/bus-index.js';
import {clone_simple, getCookie, mapPipeLineArr} from '../plugin/mUtils';
import '../../resources/stylesheets/homePage.css';
import '../../resources/fonts/svg-fonts/style.css';
import '../../resources/stylesheets/homePageZF.less';

export default {
    components: { siteLayout },
    data () {
        return {
            // 当前点击操作的记录
            indexPageRecord: {},

            // 设为首页的类型：1为A，2为B
            chouseRelaseType: {
                visible: false,
                indexType: '1',
                radioStatus: false,
                disabledRadioAStatus: false,
                channelArr: []
            },
            searchWord: '',
            options: {
                pageNo: 1,
                pageSize: 10
            },
            homeList: [],
            langIndexEn: '0',
            dialogAddVisible: false, //默认不弹出新增首页的弹窗
            total: 0, //默认列表的总页数为0
            currentTemplate: '未选中模板',

            // 权限
            has_permissions: 0,
            has_all_permissions: 0,
            home_permissions: {},

            // pc,m,app
            homeTabName: 'web',
            places: ['web', 'app'],
            pc_status: true,
            m_status: false,
            indexFormPlace: GESHOP_PLOATFORMS,
            templateSelectPlace: GESHOP_PLOATFORMS[0],
            getTmpListStatus: false,
            getTmpListValue: GESHOP_PLOATFORMS[0],
            SEOTitleCount: 0,
            SEOKeywordsCount: 0,
            SEODescriptionCount: 0,

            has_new_pipeline: false,

            tplInfo: {
                pageNo: 1,
                pageSize: 100,
                loading: false
            },
            siteInfo: '',
            pageTemplateList: [],
            pageLink: [],
            dialogName: '',
            dialogLinksVisible: false,
            pageLinks: [],
            previewLinks: [],
            previewLinksNew: [],
            previewLinks_lang:[],
            tips: '',
            currentPage: 1, //默认查找首页的数据
            pageSize: 10,
            langList: [{ code: '', name: '', key: '' }], //首页所支持的语言

            // zaful国家站渠道字段
            allSupportPipelines: [], // 所有渠道信息
            editPipelineList: [], // 编辑渠道信息
            currentPipelineCode: '',
            currentLanguageName: '',
            currentLanguageCode: '',
            publicIndexForm: {
                title: ''
            },
            allSupportChannelLang_res: {}, // 所有渠道不同端数据
            dynamicPipeLineObj_activity: {}, // 动态多端渠道对象
            placesDisabled: true,
            indexForm: {
                id: '0',
                seo_title: '',
                keywords: '',
                tpl_id: '0',
                tpl_name: '未选中模板',
                m_tpl_id: '0',
                m_tpl_name: '未选中模板',
                description: '',
                data: {},
                place: [GESHOP_PLOATFORMS[0]],
                version: 1
            },
            currentActivityRow: {
                page_languages: [{ title: '' }]
            },
            modelInfo: {
                visible: false,
                tabActive: '2',
                modelSelect: '0',
                tempLength1: 0,
                tempLength2: 0
            },
            /* 模板提示 */
            pageTemplateListWarn: '当前没有可用模板',
            isDetailActive: false,
            permissions: [],
            submitLoading: false,
            publicPageRules: {
                title: [
                    { required: true, message: '请输入名称', trigger: 'blur' },
                    { max: 100, message: '长度不能超过100个字符', trigger: 'blur' }
                ]
            },
            pageRules: {
                place: [
                    { required: true, message: '请至少选择一个应用端口', trigger: 'change' }
                ],
                seo_title: [
                    { required: true, message: '请输入SEO标题', trigger: 'blur' }
                ],
                description: [
                    { max: 200, message: '长度在1-200个字符之间', trigger: 'blur' }
                ]
            }, //弹出框的一些配置
            titleCount: 0,
            descriptionCount: 0,
            dialogPreviewLinksVisible: false,
            viewModel: {
                visible: false,
                html: '',
                sideType: GESHOP_PLOATFORMS[0],
                sideWidth: '100%',
                src: ''
            },
            pageLoading: false,
            abLoading: false,
            // 设为首页配置
            setHome:{
                isIndeterminate:false,
                checkAll:false,
            }
        };
    },
    watch: {
/*        // 新增首页端口监听
        'indexForm.place': {
            handler (newValue) {
                if (newValue && newValue.length > 0 && typeof newValue[0] === 'string') {
                    this.dynamicPipeLineObj_activity = this.mergePlatPipeline(newValue);
                } else {
                    this.dynamicPipeLineObj_activity = {};
                }
            },
            deep: true
        }*/
    },
    created: function () {
        bus.$on('giveData', data => {
            this.siteInfo = data;
        });
        // this.getPageTemplates();
    },
    methods: {
        /**
         * 获取首页国家站平台信息
         * activity_type = 2
         */
        async getCountrySiteList () {
            let params = {
                    activity_type: 2
                },
                res = await ZF_getCountrySiteList(params);

            let allSupportPipelines = [], supportPipelines = res.data.support_pipelines;
/*            // ZF首页只需要获取PC，WAP端的数据即可
            Object.keys(supportPipelines).map(platform => {
                if (platform == 'web' || platform == 'app') {
                    Object.keys(supportPipelines[platform]).forEach(item => {
                        allSupportPipelines.push(supportPipelines[platform][item]);
                    });
                }
            });
            allSupportPipelines = JSON.parse(JSON.stringify(allSupportPipelines));
            allSupportPipelines = this.unique(allSupportPipelines, 'code');

            // 所有渠道信息
            this.allSupportPipelines = allSupportPipelines;*/

            // 不同端渠道数据
            this.allSupportChannelLang_res = supportPipelines;
            // 默任选中端下渠道语言列表汇总
            allSupportPipelines = this.mergePlatPipeline(this.indexFormPlace);
            this.allSupportPipelines = allSupportPipelines;
            // 默认渠道
            this.currentPipelineCode = allSupportPipelines[0]['code'];

            let defaultLangList = allSupportPipelines[0]['lang_list'],
                defaultLangCode = Object.keys(defaultLangList)[0];
            // 默认语言
            this.currentLanguageName = defaultLangList[defaultLangCode]['name'];
            this.currentLanguageCode = defaultLangList[defaultLangCode]['code'];
        },

        /**
         * 获取首页列表
         * @returns {Promise<void>}
         */
        async getIndexList () {
            let params = {
                    pageNo: this.currentPage,
                    pageSize: this.pageSize,
                    keywords: this.searchWord,
                    site_code: `${getCookie('site_group_code')}-${this.homeTabName}`
                },
                res = await ZF_indexList(params),
                list = []

            list = res.data.list;

            if (res.data.top_page.length > 0) {
                let top_page = res.data.top_page.filter(item=> item.status == '2');
                if(top_page[1]){
                    list.unshift(top_page[0],top_page[1]);
                }else{
                    list.unshift(top_page[0]);
                }
            }

            if(list.length > 0 ){
                list.map(item => {
                    item.page_languages.map((content, index) => {
                        if (content.lang == 'en') {
                            item.page_languages.splice(index, 1)
                            item.page_languages.unshift(content)
                        }
                    })
                })
            }

            let length = list.length
            let index

            for (index = 0; index < length; index++) {
                if (list[index].is_lock == 1) {
                    list[index].lock_status = false
                } else if (list[index].is_lock == 2) {
                    list[index].lock_status = true
                }
            }
            ;
            this.homeList = res.data.list

            // 处理返回语言列表数据结构
            // let langList = res.data.langList
            // let resArr = []
            // langList.forEach(item => {
            // 	let resObject = {}
            // 	resObject['key'] = item.key
            // 	resObject['code'] = item.name.code
            // 	resObject['name'] = item.name.name
            // 	resArr.push(resObject)
            // })
            // this.langList = resArr

            this.total = parseInt(res.data.total);
            this.has_permissions = res.data.has_permissions;
            this.has_all_permissions = res.data.has_all_permissions;
            this.home_permissions = res.data.home_permissions;
        },
        /**
         * 获取所有语言列表
         */
        // async getSupportLangs () {
        // 	let res = await getLangKeyList()

        // 	let supportLangArrs = []
        // 	for (var key in res.data) {
        // 		res.data[key].forEach(item => {
        // 			// delete item.url
        // 			supportLangArrs.push(item)
        // 		})
        // 	}
        // 	supportLangArrs = JSON.parse(JSON.stringify(supportLangArrs))
        // 	supportLangArrs = this.unique(supportLangArrs, 'name')

        // 	this.supportLangs = supportLangArrs
        // },
        /**
         * 语言列表去重
         */
        unique (arr, key) {
            var n = [arr[0]];
            for (var i = 1; i < arr.length; i++) {
                if (key === undefined) {
                    if (n.indexOf(arr[i]) == -1) n.push(arr[i]);
                } else {
                    inner: {
                        var has = false;
                        for (var j = 0; j < n.length; j++) {
                            if (arr[i][key] == n[j][key]) {
                                has = true;
                                break inner;
                            }
                        }
                    }
                    if (!has) {
                        n.push(arr[i]);
                    }
                }
            }
            return n;
        },
        publicReady () {
            this.getIndexList();
            // this.getSupportLangs()

            // 获取首页国家站平台信息
            this.getCountrySiteList();

            this.permissions = JSON.parse(
                localStorage.getItem('actionPermissions')
            ).data;
            bus.$on('giveData', data => {
                this.siteInfo = data;
            });
            // 设置当前站点信息
            let places = JSON.parse(localStorage.currentSites).sites;
            delete places.app;
            this.places = places;
        },
        handleCurrentChange (currentPage) {
            this.currentPage = currentPage;
            this.getIndexList();
        },

        handleSizeChange (pageSize) {
            this.pageSize = pageSize;
            this.currentPage = 1;
            this.getIndexList();
        },

        // PC，M，APP切换
        handleHomeTabClick (event) {
            this.homeTabName = event.name;
            this.currentPage = 1;
            this.options.pageNo = 1;
            this.getIndexList();
        },

        async viewPages (id, status) {
            let params = {
                    id: id
                },
                res;

            if (Number(status) == 8) {
                // res = await getBHomePages(params)
                res = await ZF_getHomeLink(params);
            } else {
                res = await ZF_getHomeLink(params);
            }

            if (res.code == 0) {
                this.dialogLinksVisible = true;
                let pageLinks = [];
                res.data.pipeline_list.forEach((item) =>{
                    item.lang_list.forEach((langItem) =>{
                        pageLinks.push({
                            name: item.name + '——' + langItem.lang_name,
                            pipeCode: item.code,
                            page_url: langItem.page_url
                        })
                    })
                })
                this.pageLinks = pageLinks;
                // this.tips = res.data.tips
                this.urlID = id;
            }
        },
        // 重新发布
        async redistribution () {
            let params = {
                page_id: this.urlID
            };

            await ZF_homeReleased(params);
            this.dialogLinksVisible = false;
        },
        doSearch () {
            this.currentPage = 1;
            this.getIndexList();
        },
        /**
         * 弹出编辑首页
         * @param row 行
         * @param is_lock 是否锁定
         * @param create_user 创建者
         */
        editIndex (row, is_lock, create_user) {
            if (
                is_lock == 2 &&
                create_user != this.siteInfo.userName &&
                this.siteInfo.isSuper != 1
            ) {
                this.$message('该首页已被创建者锁定，需创建者解锁后其他用户才能操作');
            } else {
                this.dialogName = '编辑首页';
                this.indexForm.id = row.id;
                this.indexForm.version = row.version || 1;
                this.dialogAddVisible = true;
                let data = {};

                // 是否有新增渠道标识
                this.has_new_pipeline = row.has_new_pipeline;

                // 渠道信息
                // pipelineList - { ZF: {code: "ZF", name: "全球站", page_id: "136", lang_list: {en: {...}}}, ZFDE: {code: "ZFDE", name: "德国站", page_id: "139", lang_list: {en: {...}}}
                let pipelineList = row.group_languages;
                // 设置编辑首页活动信息
                this.editPipelineList = pipelineList;

                let firstPipelineCode = Object.keys(pipelineList)[0],
                    firstLanguageCode = Object.keys(pipelineList[firstPipelineCode]['lang_list'])[0],
                    firstLanguageName = pipelineList[firstPipelineCode]['lang_list'][firstLanguageCode]['lang_name'];

                // 初始化渠道信息字段
                // currentPipelineCode - 当前渠道code值
                // currentLanguageCode - 当前语言code值
                // currentLanguageName - 当前语言name值
                this.currentPipelineCode = firstPipelineCode;
                this.currentLanguageName = firstLanguageName;
                this.currentLanguageCode = firstLanguageCode;

                // 遍历渠道信息
                // pipeCode - 渠道code值
                Object.keys(pipelineList).forEach(pipeCode => {
                    // 当前渠道信息 {ZF: { code: '', lang_list: { en: {} }, name: '', page_id: '' }}
                    const pipeItem = pipelineList[pipeCode];
                    const pipeLangListItem = pipeItem['lang_list'];
                    // 遍历当前渠道信息语言列表（当前为单语言）
                    // lang - 语言code值
                    let langObj = {};
                    Object.keys(pipeLangListItem).forEach(lang => {
                        langObj[lang] = {
                            page_id: pipeLangListItem[lang]['page_id'],
                            seo_title: pipeLangListItem[lang]['seo_title'],
                            keywords: pipeLangListItem[lang]['keywords'],
                            description: pipeLangListItem[lang]['description']
                        };
                    });
                    data[pipeCode] = langObj;
                });

                this.indexForm.data = data;

                this.publicIndexForm.title = row.page_languages[0].title;
                this.titleCount = this.publicIndexForm.title.split('').length;
                this.SEOTitleCount = this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].seo_title.split('').length;
                this.SEOKeywordsCount = this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].keywords.split('').length;
                this.SEODescriptionCount = this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].description.split('').length;

                this.indexForm.description = this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].description;

                this.indexForm.seo_title = this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].seo_title;
                this.indexForm.keywords = this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].keywords;

                this.descriptionCount = this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].description.split('').length;
                let template = '未选中模板';
                let selected = this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].tpl_id;

                this.pageTemplateList.forEach(function (element) {
                    if (element.id == selected) {
                        template = element.name;
                    }
                });
                this.modelInfo.modelSelect = selected;
                this.indexForm.tpl_id = selected;
                this.currentTemplate = template;
            }
        },
        /**
         * 打开新增首页
         */
        addIndex () {
            this.indexForm.id = '';
            this.indexForm.version = 1;
            this.titleCount = 0;
            this.descriptionCount = 0;
            this.dialogName = '新增首页';
            let data = {};
            this.indexForm.place = GESHOP_PLOATFORMS; // 新增首页默认选中pc
            this.indexForm.keywords = '';
            this.pc_status = true;
            this.m_status = false;
            this.publicIndexForm.title = '';

            // firstPipelines - 第一个渠道信息
            // firstPipeLangList 第一个渠道信息的lang_list字段
            // firstPipeLangListCode 第一个渠道信息的code值
            let firstPipelines = this.allSupportPipelines[0], firstPipeLangList = firstPipelines['lang_list'],
                firstPipeLangListCode = Object.keys(firstPipeLangList)[0];
            this.currentPipelineCode = firstPipelines['code'];
            this.currentLanguageCode = firstPipeLangListCode;
            this.currentLanguageName = firstPipeLangList[firstPipeLangListCode]['name'];

            // 遍历所有渠道
            this.allSupportPipelines.forEach(function (element) {
                let code = element.code, lang_list = element.lang_list;
                data[code] = {};

                Object.keys(lang_list).forEach((lang) => {
                    data[code][lang] = {
                        tpl_id: '0',
                        tpl_name: '未选中模板',
                        m_tpl_id: '0',
                        m_tpl_name: '未选中模板',
                        pc_status: true,
                        m_status: false,
                        seo_title: '',
                        keywords: '',
                        description: ''
                    };
                });
            });
            this.indexForm.data = data;
            this.dialogAddVisible = true;
        },
        async getPageTemplates (scrollType) {
            this.tplInfo.loading = true;
            let _this = this;
            let pageNo = scrollType == 'scroll' ? this.tplInfo.pageNo : 1;
            let type = this.modelInfo.tabActive == '1' ? 1 : 0;

            let params = {
                place: 2,
                type: type,
                pageNo: pageNo,
                pageSize: this.tplInfo.pageSize,
                lang: this.currentLanguageCode
            };

            // 如果是选择模板
            if (this.getTmpListStatus) {
                params.site_code = getCookie('site_group_code') + '-' + this.getTmpListValue;
            } else {
                params.site_code = getCookie('site_group_code') + '-' + this.homeTabName;
            }

            let res = await ZF_getPageTemplateList(params);

            let data = res.data.list;
            this.tplInfo.totalCount = res.data.totalCount;
            this.tplInfo.maxPageNo = Math.ceil(
                res.data.totalCount / this.tplInfo.pageSize
            );
            if (scrollType == 'scroll' && pageNo > 1) {
                let oldList = this.pageTemplateList;
                this.pageTemplateList = oldList.concat(data);
            } else {
                this.pageTemplateList = data;
            }

            this.checkCurrentPageForm();

            setTimeout(function () {
                _this.tplInfo.loading = false;
            }, 200);
        },
        // 带操作内容的弹窗
        choseRelease () {
            this.chouseRelaseType.visible = true;
        },
        // confirm 弹窗
        confirm (message, callback) {
            this.$confirm(message, '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            })
                .then(() => {
                    if (typeof callback == 'function') {
                        callback(this);
                    }
                })
                .catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消操作!'
                    });
                });
        },
        decorate_redirect (url, is_lock, activity_create_user, has_permissions) {
            if (has_permissions <= 0) {
                this.$message.error('你没有此操作权限');
                return false;
            }
            if (
                is_lock == 2 &&
                activity_create_user != this.siteInfo.userName &&
                this.siteInfo.isSuper != 1
            ) {
                this.$message('该首页已被创建者锁定，需创建者解锁后其他用户才能操作');
            } else {
                window.open(url);
            }
        },
        redirect (url) {
            window.open(url);
        },
        //选中模板
        handleModelTempSelect (val) {
            let _this = this;
            // let val = window.GESHOP_PLOATFORMS[item]
            // 新增子页面模板选择区分来自PC、M或APP
            this.templateSelectPlace = val;

            // PC、M、APP页面模板数据获取
            // if (this.templateSelectPlace == 'web') {
            //     this.getTmpListValue = 'web';
            // } else if (this.templateSelectPlace == 'app') {
            //     this.getTmpListValue = 'app';
            // }

            this.getTmpListValue = val;
            this.getTmpListStatus = true;

            this.getPageTemplates();

            this.modelInfo.visible = true;
            this.modelInfo.modelSelect = this.indexForm.tpl_id;
            setTimeout(function () {
                _this.handlePanelScroll();
            }, 100);
        },
        handleInfor (value) {
            this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].description = value;
        },
        detailClose () {
            this.isDetailActive = false;
        },
        handleCancelSelectedModel () {
            this.modelInfo.visible = false;
        },
        handleCancelReleaseIndexType () {
            this.chouseRelaseType.visible = false;
        },
        handleSureModel () {

            let currentPlace = this.templateSelectPlace;

            let selected = this.modelInfo.modelSelect;

            // this.indexForm.tpl_id = selected;
            if (currentPlace == 'web' || currentPlace == 'web' ) {
                this.indexForm.tpl_id = selected;
            } else if (currentPlace == 'app') {
                this.indexForm.m_tpl_id = selected;
            }

            this.modelInfo.visible = false;

            this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].tpl_id = this.indexForm.tpl_id;
            this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].m_tpl_id = this.indexForm.m_tpl_id;

            let _this = this;

            // let template = "未选中模板";
            this.pageTemplateList.forEach(function (element) {
                if (element.id == selected) {
                    if (currentPlace == 'web') {
                        _this.indexForm.tpl_name = element.name;
                    } else if (currentPlace == 'app') {
                        _this.indexForm.m_tpl_name = element.name;
                    }
                    // template = element.name;
                }
            });
            // this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].tpl_id = selected;
            // this.currentTemplate = template;
            this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].tpl_name = this.indexForm.tpl_name;
            this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].m_tpl_name = this.indexForm.m_tpl_name;
        },
        lockingIndex (group_id, is_lock, create_user) {
            if (create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                this.$message('只有首页创建者才具有此权限！');
                this.homeList.forEach(function (element) {
                    if (element.is_lock == 1) {
                        element.lock_status = false;
                    } else if (element.is_lock == 2) {
                        element.lock_status = true;
                    }
                });
            } else {
                var tip = '';
                if (is_lock == 2) {
                    tip = '解锁后，其他用户可以操作此活动';
                } else if (is_lock == 1) {
                    tip = '加锁首页后，其他的用户只能查看首页二维码';
                }

                this.$confirm(tip, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                })
                    .then(async () => {
                        let params = {
                                group_id: group_id
                            },
                            res = await ZF_handlerLock(params);

                        if (res.code === 0) {
                            this.getIndexList();
                            this.$message({
                                type: 'success',
                                message: res.message
                            });
                        } else {
                            this.$message({
                                type: 'error',
                                message: res.message
                            });
                        }
                    })
                    .catch(() => {
                        this.homeList.forEach(function (element) {
                            if (element.is_lock == 1) {
                                element.lock_status = false;
                            } else if (element.is_lock == 2) {
                                element.lock_status = true;
                            }
                        });
                    });
            }
        },
        async removeIndex (group_id, is_lock, create_user) {
            if (
                is_lock == 2 &&
                create_user != this.siteInfo.userName &&
                this.siteInfo.isSuper != 1
            ) {
                this.$message('该首页已被创建者锁定，需创建者解锁后其他用户才能操作');
            } else {
                this.confirm('确定删除首页列表信息吗？删除后，不可恢复，请谨慎操作！', async vm => {
                    let params = {
                            group_id: group_id
                        },
                        res = await ZF_deleteIndex(params);

                    if (res.code === 0) {
                        this.getIndexList();
                        vm.$message({
                            type: 'success',
                            message: res.message
                        });
                    } else {
                        vm.$message({
                            type: 'error',
                            message: res.message
                        });
                    }
                });
            }
        },
        async submitForm () {
            this.submitLoading = true;
            this.$refs['publicIndexForm'].validate(async valid => {
                this.submitLoading = false;

                if (valid) {
                    // for (var i in this.indexForm.data) {
                    // 	if (this.indexForm.data[i].title == '') {
                    // 		return this.$message.error(
                    // 			'请确认所有语言所有必填项已经填写，再提交！'
                    // 		)
                    // 	}
                    // }
                    let indexFormData = this.indexForm.data;
                    let firstChannel = Object.keys(indexFormData)[0];
                    let firstLang = (Object.keys(indexFormData[firstChannel]))[0];
                    let firstData = indexFormData[firstChannel][firstLang];
                    if(!firstData.seo_title){
                        this.$message.error('请输入SEO标题');
                        return false;
                    }


                    if(!this.publicIndexForm.title){
                        this.$message.error('页面名称为必填项');
                        return false;
                    }
                    // 编辑
                    if (this.indexForm.id != '') {
                        let params = {
                            site_code: `${getCookie('site_group_code')}-${this.homeTabName}`,
                            version: this.indexForm.version
                        };

                        // 渠道信息
                        let pipelineList = this.indexForm.data, pipelines = {};

                        // 遍历渠道信息
                        // pipeCode - 渠道code值
                        Object.keys(pipelineList).forEach(pipeCode => {
                            let pipelineLangItem = pipelineList[pipeCode];
                            pipelines[pipeCode] = {};
                            // 遍历渠道信息下的语言列表（当前为单语言）
                            // lang - 渠道信息对应语言字段
                            let langObj = {};
                            Object.keys(pipelineLangItem).forEach(lang => {
                                langObj[lang] = pipelineLangItem[lang];
                                langObj[lang]['seo_title'] = langObj[lang]['seo_title'] ? langObj[lang]['seo_title'] : firstData.seo_title;
                                pipelines[pipeCode]['list'] = langObj;
                                pipelines[pipeCode]['default_lang'] = lang;
                                pipelines[pipeCode]['page_id'] = langObj[lang]['page_id'];
                                pipelines[pipeCode]['title'] = this.publicIndexForm.title;
                            });
                        });

                        params['pipeline_list'] = JSON.stringify(pipelines);

                        let res = await ZF_editIndex(params);
                        if (res.code == 0) {
                            this.getIndexList();
                            this.subDialogClose();
                            this.submitLoading = false;
                        } else {
                            this.submitLoading = false;
                        }
                    }
                    // 新增
                    else {
                        let indexFormObj = {};
                        let jsonObject = {};

                        // 遍历端口 place - pc,wap
                        this.indexFormPlace.forEach((place) => {

                            let param = {};

                            // pipeCode 渠道code值
                            // langCode 渠道对应语言值
                            Object.keys(indexFormData).forEach((pipeCode) => {
                                let pipeItem = indexFormData[pipeCode];
                                let listLang = {};
                                Object.keys(pipeItem).forEach((langCode) => {
                                    param[pipeCode] = {
                                        page_id: this.indexForm.id,
                                        title: this.publicIndexForm.title,
                                        default_lang: langCode
                                    };
                                    let pipeLangItem = pipeItem[langCode];
                                    pipeLangItem.seo_title = pipeLangItem.seo_title ? pipeLangItem.seo_title : firstData.seo_title;

                                    listLang[langCode] = pipeLangItem;
                                });
                                param[pipeCode]['list'] = listLang;
                            });

                            indexFormObj[place] = JSON.parse(JSON.stringify(param));

                        });

                        // 根据端口过滤数据
                        // place - pc,wap
                        // pipe - ZF,ZFES,...
                        // lang - en,es,...
                        Object.keys(indexFormObj).forEach(place => {
                            Object.keys(indexFormObj[place]).forEach(pipe => {
                                Object.keys(indexFormObj[place][pipe]['list']).forEach(lang => {
                                    if ( place == 'web') {
                                        delete indexFormObj[place][pipe]['list'][lang]['m_tpl_id'];
                                        delete indexFormObj[place][pipe]['list'][lang]['m_tpl_name'];
                                        delete indexFormObj[place][pipe]['list'][lang]['m_status'];
                                        delete indexFormObj[place][pipe]['list'][lang]['pc_status'];
                                    } else if (place == 'app') {
                                        delete indexFormObj[place][pipe]['list'][lang]['tpl_id'];
                                        delete indexFormObj[place][pipe]['list'][lang]['tpl_name'];
                                        indexFormObj[place][pipe]['list'][lang]['tpl_id'] = indexFormObj[place][pipe]['list'][lang]['m_tpl_id'];
                                        indexFormObj[place][pipe]['list'][lang]['tpl_name'] = indexFormObj[place][pipe]['list'][lang]['m_tpl_name'];
                                        delete indexFormObj[place][pipe]['list'][lang]['m_tpl_id'];
                                        delete indexFormObj[place][pipe]['list'][lang]['m_tpl_name'];
                                        delete indexFormObj[place][pipe]['list'][lang]['m_status'];
                                        delete indexFormObj[place][pipe]['list'][lang]['pc_status'];
                                    }
                                });
                            });
                        });

                        for (let key in indexFormObj) {
                            jsonObject[key] = JSON.stringify(indexFormObj[key]);
                        }

                        let res = await ZF_addIndex(jsonObject);
                        if (res.code == 0) {
                            this.getIndexList();
                            this.subDialogClose();
                            this.submitLoading = false;
                        } else {
                            this.submitLoading = false;
                        }
                    }
                }
            });
        },
        /**
         * 根据当前渠道code值取对应渠道和语言信息
         * @param { String } currentPipelineCode - 当前渠道code值
         * @param { Array } allSupportPipelines - 所有渠道信息
         * @returns { Object } code 渠道code值，name渠道name值，lang渠道对应单个语言值， langName语言name值 { code: '', name: '', lang: '', langName: '' }
         */
        transformPipelineCode () {
            let currentPipelineCode = this.currentPipelineCode,
                allSupportPipelines = this.allSupportPipelines,
                currentPipeline = {};

            allSupportPipelines.forEach((item) => {
                if (item.code == currentPipelineCode) {
                    currentPipeline = item;
                }
            });

            let lang = Object.keys(currentPipeline.lang_list)[0];

            return {
                code: currentPipeline.code,
                name: currentPipeline.name,
                lang: lang,
                langName: currentPipeline['lang_list'][lang]['name']
            };
        },
        /**
         * 新增子页面切换渠道与语言
         * @param tab
         * @param type 'channel|lang' > '渠道|语言'
         */
        handleTabClick (tab, type) {
            this.resetFields('indexForm');

            let pipeCode = this.currentPipelineCode,
                pipeInfo = this.transformPipelineCode(),
                langName = pipeInfo.langName,
                langCode = pipeInfo.lang;
            // 设置当前显示语言
            if (type === 'channel') {
                this.currentLanguageName = langName;
                this.currentLanguageCode = langCode;
            } else if (type === 'lang') {
                langCode = this.currentLanguageCode;
            }


            // this.prevLanguage = this.currentLanguage
            // this.indexForm.tpl_id = this.indexForm.data[lang].tpl_id;
            this.indexForm.seo_title = this.indexForm.data[pipeCode][langCode].seo_title;
            this.indexForm.keywords = this.indexForm.data[pipeCode][langCode].keywords;
            this.indexForm.description = this.indexForm.data[pipeCode][langCode].description;

            this.indexForm.tpl_id = this.indexForm.data[pipeCode][langCode].tpl_id;
            this.indexForm.tpl_name = this.indexForm.data[pipeCode][langCode].tpl_name;
            this.indexForm.m_tpl_id = this.indexForm.data[pipeCode][langCode].m_tpl_id;
            this.indexForm.m_tpl_name = this.indexForm.data[pipeCode][langCode].m_tpl_name;

            this.indexForm.place = this.indexFormPlace; // 新增首页tab切换时，保存应用端口状态
            // this.indexForm.place = this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].place // 新增子页面tab切换时，保存应用端口状态

            this.descriptionCount = this.indexForm.description.split('').length;
            this.SEOTitleCount = this.indexForm.seo_title.split('').length;
            this.SEOKeywordsCount = this.indexForm.keywords.split('').length;
            this.SEODescriptionCount = this.indexForm.description.split('').length;
        },
        /**
         * 更新当前渠道当前语言name值为value
         * data value
         * name 属性名
         * param 参数名
         */
        updatePageField (data, name, param) {
            if (!param) {
                this.pageForm.data[this.currentChannel][this.currentLanguage][name] = data;
            } else {
                this.pageForm.data[this.currentChannel][this.currentLanguage][name][param] = data;
            }

        },
        updateIndexTitle (value) {
            this.publicIndexForm.title = value;
        },
        updateIndexSeoTitle (value) {
            this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].seo_title = value;
        },
        updateIndexSeoKeywords (value) {
            this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].keywords = value;
        },
        updateIndexSeoDescription (value) {
            this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].description = value;
        },
        handleCheckedPlacesChange (val) {
            let arr = val;
            this.indexFormPlace = val;
            // this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].place = val // 应用端口在每种语言下都可独立选择
            if (arr.indexOf('web') != -1) {
                this.pc_status = true;
                this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].pc_status = true;
            } else {
                this.pc_status = false;
                this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].pc_status = false;
            }

            if (arr.indexOf('app') != -1) {
                this.m_status = true;
                this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].m_status = true;
            } else {
                this.m_status = false;
                this.indexForm.data[this.currentPipelineCode][this.currentLanguageCode].m_status = false;
            }
        },
        resetForm (forName) {
            this.currentLanguage = 'en';
            this.resetFields(forName);
            this.dialogAddVisible = false;
        },
        resetFields (forName) {
            this.$refs[forName].resetFields();
        },
        viewDetail (row) {
            this.currentActivityRow = row;
            this.isDetailActive = true;
            // this.langList = this.langList
            // this.pipelineList
        },
        subDialogClose () {
            this.resetForm('indexForm');
            this.indexForm.id = '0';
            this.indexForm.tpl_id = '0';
            this.publicIndexForm.title = '';
            this.indexForm.description = '';
            this.indexForm.seo_title = '';
            this.modelInfo.tabActive = '2';
            this.currentTemplate = '未选中模板';
            this.modelInfo.modelSelect = '0';
            this.indexFormPlace = ['web','app'];
            this.getIndexList();
        },
        tplTabClick () {
            this.tplInfo.pageNo = 1;
            let contContainer = document.getElementById('pane-2').parentNode;
            contContainer.removeEventListener('scroll', this.handlePanelScroll);

            if (this.templateSelectPlace == 'web') {
                this.getTmpListValue = 'web';
            } else if (this.templateSelectPlace == 'app') {
                this.getTmpListValue = 'app';
            } else if (this.templateSelectPlace == 'app') {
                this.getTmpListValue = 'app';
            }

            this.getTmpListStatus = true;

            this.getPageTemplates('scroll');
        },
        handlePanelScroll () {
            let panelCont0 = document.getElementById('pane-2').parentNode,
                _this = this,
                timer;
            panelCont0.addEventListener('scroll', function () {
                if (timer) clearTimeout(timer);
                timer = setTimeout(function () {
                    if (
                        panelCont0.clientHeight + panelCont0.scrollTop ==
                        panelCont0.scrollHeight
                    ) {
                        var tempNum = _this.tplInfo.pageNo + 1;
                        if (tempNum <= _this.tplInfo.maxPageNo) {
                            _this.tplInfo.pageNo = tempNum;

                            if (_this.templateSelectPlace == 'web') {
                                _this.getTmpListValue = 'web';
                            } else if (_this.templateSelectPlace == 'app') {
                                _this.getTmpListValue = 'app';
                            } else if (_this.templateSelectPlace == 'app') {
                                _this.getTmpListValue = 'app';
                            }

                            _this.getTmpListStatus = true;

                            _this.getPageTemplates('scroll');
                        }
                    }
                }, 600);
            });
        },
        handleTitleKeyup () {
            var data = this.publicIndexForm.title.split('');
            var length = data.length;

            if (length > 100) {
                data.splice(100, length - 99);
                this.publicIndexForm.title = data.join('');
            }

            this.titleCount = data.length;
        },
        handleSEOTitleKeyup () {
            var data = this.indexForm.seo_title.split('');
            var length = data.length;

            if (length > 200) {
                data.splice(200, length - 199);
                this.indexForm.seo_title = data.join('');
            }

            this.SEOTitleCount = data.length;
        },
        handleSEOKeywordsKeyup () {
            var data = this.indexForm.keywords.split('');
            var length = data.length;

            if (length > 200) {
                data.splice(200, length - 199);
                this.indexForm.keywords = data.join('');
            }

            this.SEOKeywordsCount = data.length;
        },
        handleSEODescriptionKeyup () {
            var data = this.indexForm.description.split('');
            var length = data.length;

            if (length > 200) {
                data.splice(200, length - 199);
                this.indexForm.description = data.join('');
            }

            this.SEODescriptionCount = data.length;
        },
        // 确认设置为首页
        setHomePage () {
            this.setIndexPage();
        },
        async setIndexPage () {
            this.abLoading = true;
            const indexType = this.chouseRelaseType.indexType;
            const record = this.indexPageRecord;
            const pipeline = this.chouseRelaseType.channelArr.join(',');

            if (record.group_status != 3 && this.chouseRelaseType.channelArr.length == 0) {
                this.$message({
                    type: 'warning',
                    message: '请选择渠道'
                });
                this.abLoading = false;
                return false;
            }

            const params = { page_id: record.id };

            if (record.group_status != 3) {
                params.pipeline = pipeline;
            }

            const res = parseInt(indexType) === 1 ? await ZF_setAsHomePageA(params) : await ZF_setAsHomePageB(params);
            if (res.code === 0) {
                this.getIndexList();
                this.$message({
                    type: 'success',
                    message: res.message
                });
                this.chouseRelaseType.visible = false;
            } else {
                this.$message({
                    type: 'error',
                    message: res.message
                });
            }
            this.abLoading = false;
        },

        // 处理设置为首页的按钮点击事件
        setAsHomePage (record) {
            const recordStatus = this.indexPageRecord.status;

            let o_group_languages = {};
            let group_languages = record.group_languages;
            // pipeline - ZF,ZFAR
            Object.keys(group_languages).forEach((pipeline) => {
                if (group_languages[pipeline]['page_id'] != -1) {
                    o_group_languages[pipeline] = group_languages[pipeline];
                }
            });
            record.o_group_languages = o_group_languages;
            this.indexPageRecord = record;
            if (recordStatus !== 5) {
                this.chouseRelaseType.visible = true;
            } else {
                this.$message({
                    message: '首页正在生成中，勿需重复操作!',
                    type: 'warning'
                });
            }
            if (recordStatus === 2 || recordStatus === 7) {
                this.chouseRelaseType.radioStatus = true;
            }

            // 如果当前为B首页并且没有设置A首页权限则选中B首页
            if ((record.status == 3 || record.status == 8) && this.has_all_permissions == 0) {
                this.chouseRelaseType.disabledRadioAStatus = true;
                this.chouseRelaseType.indexType = '2';
                // if (record.status == 8) {
                // }
            }
        },
        async refreshHomePage () {
            if (this.siteInfo.isSuper != 1) {
                this.$message('该操作只有超级管理员才有权限!');
            } else {
                this.confirm(
                    '一键头尾刷新中，请在【系统日志】-任务日志里对刷新详情进行查看，是否前往查看？',
                    async vm => {
                        let params = {
                                // site_code: getCookie("SITECODE")
                                site_code: getCookie('site_group_code') + '-' + this.homeTabName
                            },
                            res = await refreshHome(params);

                        if (res.code == 0) {
                            window.location.href = '/base/task-log/index';
                        } else {
                            vm.$message.error(res.message);
                        }
                    }
                );
            }
        },
        previewPages (group_languages) {
            let links = [];

            // key - 渠道code值
            // lang - 渠道对应语言值
            Object.keys(group_languages).forEach(key => {
                if(Number(group_languages[key].page_id) > 0){
                    let langList = group_languages[key]['lang_list'];
                    Object.keys(langList).forEach(lang => {
                        links.push({
                            page_url: `/home/zf/design/preview?pid=${group_languages[key]['pid']}&pipeline=${key}&lang=${lang}`,
                            pipelineName: group_languages[key]['name'] + '——' + langList[lang]['lang_name'],
                            pipeCode: key
                        });
                    });
                }
            });

            this.previewLinks = links;
            this.dialogPreviewLinksVisible = true;
        },

        /**
         * 首页预览地址
         * @param id
         * @param status
         * @param urls
         * @returns {Promise<boolean>}
         */
        async previewPagesNew(id,status,urls){
            let params = {
                id: id,
                preview: 1
            }
            let res = await ZF_getHomeLink(params);
            let previewLinksNew = [];
            if (res.code === 0) {
                if(res.data.pipeline_list && res.data.pipeline_list.length > 0){
                    res.data.pipeline_list.forEach((aPipe,index)=>{
                        let lang_list = aPipe.lang_list;
                        lang_list.forEach((aLang,aIndex)=>{
                            previewLinksNew.push({
                                page_url: aLang.page_url,
                                name: aPipe['name'] + '——' + aLang['lang_name'],
                                pipeCode: aPipe.code
                            })
                        })
                    })
                    this.previewLinksNew = previewLinksNew;
                }else{
                    this.previewLinksNew = [];
                    this.$message.warning('不存在可预览的渠道语言');
                    return false;
                }
                this.dialogPreviewLinksVisible = true;
            }
            /*		    if(urls.length === 0 ){
                            this.$message.warning('不存在可预览的渠道语言');
                            return false;
                        }
                        this.previewLinksNew = urls;
                        this.dialogPreviewLinksVisible = true;*/
        },
        async releasePage (id) {
            this.confirm('确定要发布首页?', async vm => {
                let params = {
                        page_id: id
                    },
                    res = await ZF_homeReleased(params);

                if (res.code == 0) {
                    this.getIndexList();
                    vm.$message({
                        message: res.message,
                        type: 'success'
                    });
                } else {
                    vm.$message.error(res.message);
                }
            });
        },
        /* 校验当前模板列表 */
        checkCurrentPageForm () {
            let pageTemplateList = this.pageTemplateList,
                tabActive = this.modelInfo.tabActive,
                siteInfo = this.siteInfo,
                tempLength1 = 0,
                tempLength2 = 0;

            let pageTemplateListWarn =
                tabActive == '2' ? '您还没有自己的模板' : '暂无页面模板供使用';
            this.pageTemplateListWarn = pageTemplateListWarn;

            pageTemplateList.forEach(function (item) {
                if (item.create_user == siteInfo.userName) {
                    tempLength1 += 1;
                } else if (
                    item.create_user != siteInfo.userName &&
                    item.tpl_type == 1
                ) {
                    tempLength2 += 1;
                }
            });
            this.modelInfo.tempLength1 = tempLength1;
            this.modelInfo.tempLength2 = tempLength2;
        },
        // 查看模板大图
        async seeTemplate (pid, lang, id, site_code) {
            if (!pid) {
                this.$message('活动pid不存在');
                return false;
            }
            this.viewModel.visible = true;
            this.pageLoading = true;
            let langDefualt = lang || 'en';
            this.viewModel.src = '/home/zf/page-tpl/preview?pid=' + pid + '&lang=' + langDefualt + '&id=' + id + '&site_code=' + site_code + '';
            let sideType = site_code.split('-')[1], sideWidth;
            sideWidth = '100%';

            this.viewModel.sideType = sideType;
            this.viewModel.sideWidth = sideWidth;

            /* 预览类型 */
            /* 			let res = await getModelHtml({ pid: pid, lang: lang || 'en', id: id, site_code: site_code })
                        if (res.code == 0) {
                            let sideType = site_code.split('-')[1], sideWidth

                            sideWidth = '100%'

                            this.viewModel.sideType = sideType
              this.viewModel.sideWidth = sideWidth
              this.viewModel.html = res.data.pageHtml
                        } */


            this.pageLoading = false;
        },
        viewModelClose () {
            this.viewModel.visible = false;
            this.viewModel.html = '';
            this.viewModel.src = '';
        },
        /**
         * 合并多渠道下语言列表
         * @param newValue ['web','app','app']
         * @return {{}}
         */
        mergePlatPipeline(newValue){
            if(!newValue || !(newValue instanceof Array) || newValue.length === 0){
                return {}
            }
            let allSupportChannelLang_res = clone_simple(this.allSupportChannelLang_res);
            let dynamicPipeLineObj_activity = mapPipeLineArr(allSupportChannelLang_res, newValue, 'lang_list');
            return dynamicPipeLineObj_activity;
        }
    }
};
</script>
<style lang="less" scoped>
    .model-item img {
        max-width: 100%;
        width: 150px;
        height: 150px;
        display: block;
        margin: 10px auto;
    }

    .count-tip-box {
        position: absolute;
        bottom: 5px;
        right: 5px;
        font-size: 12px;
        background-color: #ffffff;
        line-height: 1;
    }

    .showChoseIndexType {
        .dialog-footer, .items, .tips_msg {
            text-align: center;
        }

        .items {
            margin-bottom: 20px;
        }

        .channel {
            .title {
                margin-top: 0;
                margin-bottom: 15px;
            }

            .el-checkbox {
                margin-right: 30px;
                margin-left: 0;
            }
        }
    }
    .geshop-new-index .el-form-item{
        margin-bottom:5px;
    }
    .geshop-index-preview-dialog,.geshop-index-access-dialog{
        .el-dialog__body .el-button--primary{
            width: calc(25% - 12px);
            margin-left:10px;
        }
    }
</style>
<style lang="less">
    .model-box {
        width: 50%;
        float: left;
        text-align: center;

        .el-radio {
            position: relative;
            max-width: 100%;
        }

        .el-radio__input {
            position: absolute;
            right: 20px;
            top: 36px;
        }
    }

    .model-dialog .el-tabs__content {
        height: 400px;
        overflow-y: scroll;
    }

    .geshop-index-lists .has-gutter th {
        background-color: #f4f4f4 !important;
        padding: 8px 0px !important;
    }

    .geshop-index-lists .el-table__header-wrapper {
        height: 40px !important;
    }

    /* .geshop-new-index .el-dialog {
      height: 600px;
    }*/
</style>
