<template>
	<site-layout>
		<el-row :span="24" class="geshop-goods-tit">
			<span class="geshop-goods-title">商品数据管理</span>
		</el-row>
		<el-row class="geshop-goods-btn">
			<el-col>
				<el-button class="geshop-goods-btn-add" @click="handleAddOne">
					<span class="icon-geshop-pack-up"></span>
					<span class="geshop-icon-add-text">新增商品数据</span>
				</el-button>
			</el-col>
		</el-row>
		<el-row class="geshop-goods-lists">
			<el-col :span="24">
				<template>
					<el-tabs v-model="goodManageTabName" type="card" @tab-click="handleGoodManageTabClick">
						<el-tab-pane v-for="(item,key) in places" :label="item.platform_name" :name="key" :key="key"></el-tab-pane>
					</el-tabs>
				</template>
				<el-table :data="dataSource" style="width: 100%" v-loading="loading">
					<el-table-column prop="id" label="ID" width="100">
					</el-table-column>
					<el-table-column prop="activity_name" label="活动名称">
					</el-table-column>
					<el-table-column prop="page_title" label="子页面名称">
					</el-table-column>
					<el-table-column prop="update_time" label="操作时间">
						<template slot-scope="scope">
							<span>{{ Number(scope.row.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
					<!-- <el-table-column prop="start_time" label="开始时间">
						<template slot-scope="scope">
							<span>{{ Number(scope.row.start_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
					<el-table-column prop="end_time" label="结束时间">
						<template slot-scope="scope">
							<span>{{ Number(scope.row[_this.activeLangDefault]d_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column> -->
					<el-table-column prop="create_name" label="创建者">
					</el-table-column>
					<el-table-column prop="status" label="子页面状态">
						<template slot-scope="scope">
							<span v-if="scope.row.status=='2'">已上线</span>
							<span v-else-if="scope.row.status=='1'">待上线</span>
							<span v-else-if="scope.row.status=='4'">已下线</span>
						</template>
					</el-table-column>
					<el-table-column label="操作" min-width="200">
						<template slot-scope="scope">
							<el-tooltip content="预览子页面" placement="bottom" effect="light">
								<el-button class="icon-geshop-search" @click="handlePreview(scope.row.group_id)"></el-button>
							</el-tooltip>
							<el-tooltip content="查看SKU" placement="bottom" effect="light">
								<el-button class="icon-geshop-ckeckSku" @click="handleSkuLists(scope.row.id)"></el-button>
							</el-tooltip>
							<el-tooltip content="编辑" placement="bottom" effect="light">
								<el-button class="icon-geshop-edit" @click="handleEditOne(scope.row.group_id)"></el-button>
							</el-tooltip>
							<el-tooltip content="删除" placement="bottom" effect="light">
								<el-button class="icon-geshop-del" @click="handleDelOne(scope.row.id)"></el-button>
							</el-tooltip>
						</template>
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>
		<el-row v-if="pageInfo.totalCount>pageInfo.pageSize">
			<el-col :span="24" class="text-right geshop-article-page">
				<el-pagination layout="prev, pager, next" :page-size="pageInfo.pageSize" :total="pageInfo.totalCount" :current-page.sync="pageInfo.pageNo"
				  @current-change="handleCurrentChange"></el-pagination>
			</el-col>
		</el-row>
		<!-- 新增编辑 -->
		<el-dialog :title="goodsFormInfo.title" width="800" custom-class="goods-form-first geshop-goods-new" :visible.sync="dialogDefaultVisible"
		  ref="firstStepDialog" @close="closeDialog('goodManage')">
			<el-tabs type="card" @tab-click="handleTabPlatClick" v-model="currentPlatUp">
				<el-tab-pane v-for="(element,key) in plantLists" :key="key" :label="element.toUpperCase()" :name="element.toUpperCase()">
					<el-row>
						<el-col>活动名称</el-col>
					</el-row>
					<el-cascader :options="activePlatListsAll[element]" @active-item-change="handleItemChange" :props="activeSelect.props" @change="handleCasChange"
					  :ref="element" name="activePc" class="cas-activity" popper-class="cas-good-platList" placeholder="请选择">
					</el-cascader>
				</el-tab-pane>
				<el-row style="padding:40px 0;height:220px;">
					<span style="display:block;margin-bottom: 10px;">同步环境</span>
					<el-row v-for="(item,index) of activePlatSelected" :key="index" v-if="item.casArrText">
						<el-col :span="12" class="border-box border-col-ellipsis">
							<el-col :span="22" class="col-ellipsis">
								<span>{{item.casArrText}}</span>
							</el-col>
							<el-col :span="2">
								<i class="el-icon-close" @click="handlePlatClose(index)"></i>
							</el-col>
						</el-col>
					</el-row>
				</el-row>
			</el-tabs>
			<!-- <el-row>
				<el-col :span="3">
					<span style="display:block;">应用环境</span>
					<el-checkbox label="全选" v-model="platformSelectAll" @change="platformSelectAllChange"></el-checkbox>
				</el-col>
				<el-col :span="7">活动名称</el-col>
				<el-col :span="7">子页面名称</el-col>
				<el-col :span="7">活动创建者</el-col>
			</el-row> -->
			<!-- 应用环境列表 -->
			<!-- <template>
				<el-row v-for="(element,key) in plantLists" :key="key">
					<el-col :span="3">
						<el-checkbox :label="element.toUpperCase()" v-model="platSelectBox2[element]" @change="plaformCheckChange"></el-checkbox>
					</el-col>
					<el-col :span="7">
						<el-select filterable placeholder="请选择" v-model="activePlatSelected[element].id" @change="getPages(element,activePlatSelected[element].id)">
							<el-option v-for="(item, index) in activePlatListsAll[element]" :key="index" :label="item.name" :value="item.id"></el-option>
						</el-select>
					</el-col>
					<el-col :span="7">
						<el-select filterable placeholder="请选择" v-model="activePlatSelected[element].page_id" v-if="activePlatListsAll[element][activeAllIndex[element]]" @change="handleChildPage(element,activePlatSelected[element].page_id)">
							<el-option v-for="(item, index) in activePlatListsAll[element][activeAllIndex[element]].children" :key="index" :label="item.title" :value="item.id"></el-option>
						</el-select>
						<el-select filterable placeholder="请选择" v-model="activePlatSelected[element].page_id" v-else>
						</el-select>
					</el-col>
					<el-col :span="7">{{activePlatSelected[element].create_name}}</el-col>
				</el-row>
			</template> -->
			<span slot="footer" class="dialog-footer">
				<el-button @click="closeDialog('goodManage')">取消</el-button>
				<el-button type="primary" :disabled="activePlatForm.nextDisabled" @click="handleNextStep">下一步</el-button>
			</span>
		</el-dialog>

		<!-- 商品数据管理dialog -->
		<el-dialog :title="goodsFormInfo.title" width="800" :visible.sync="dialogStatu.goodListVisible" v-if="dialogStatu.goodListVisible"
		  @close="closeDialog('goodManage')" custom-class="goodManageDialog geshop-Commodity-data-management">
			<el-tabs type="card" @tab-click="handleTabClick" v-model="currentLanguage">
				<el-tab-pane v-for="item in activeLangList" :label="item.name" :name="item.key" :key="item.key">
					<!-- <el-row v-if="item.key !=='en'">
						<el-col>
							<el-button type="primary" size="small" @click="syncEnData(item.key)">同步英文界面SKU</el-button>
						</el-col>
					</el-row> -->
					<el-row class="goodListsAddForm">
						<el-row :span="24" v-for="(item,index) in activeGoodsList" :key="index" class="goodListsItem" v-if="item[currentLanguage]">
							<el-row>
								<el-col :span="5">不同端数据是否一致</el-col>
								<el-col :span="5" v-if="item[currentLanguage].radio">
									<el-radio v-model="item[currentLanguage].radio" label="1" @change="handleRadioChange($event,index)">是</el-radio>
									<el-radio v-model="item[currentLanguage].radio" label="2" @change="handleRadioChange($event,index)">否</el-radio>
								</el-col>
								<el-col :span="5" :offset="9" class="goodItemTools text-right">
									<i class="icon el-icon-setting" @click="goodsUnitInit(index,item[currentLanguage].common.goods_sku,'common')" v-if="item[currentLanguage].radio == '1'"></i>
									<i class="icon el-icon-arrow-up" @click="goodItemUp(index)"></i>
									<i class="icon el-icon-arrow-down" @click="goodItemDown(index)"></i>
									<i class="icon el-icon-delete" @click="goodItemDel(index)"></i>
								</el-col>
							</el-row>
							<!-- common -->
							<template v-if="item[currentLanguage].radio == 1">
								<el-row>
									<el-col>商品数据列表{{index+1}}</el-col>
								</el-row>
								<el-row :gutter="20" align="middle" type="flex">
									<el-col :span="12">
										<el-input type="text" placeholder="请输入商品分类的标题名称" :name="`category_title${index}`" v-model="item[currentLanguage].common.category_title"></el-input>
									</el-col>
									<el-col :span="12">
										<el-input type="text" placeholder="请输入view more 链接" v-model="item[currentLanguage].common.more_url"></el-input>
									</el-col>
								</el-row>

								<!-- common select goods -->
								<el-row>
									<el-col :span="5">商品SKU</el-col>
									<!-- <el-col :span="5">商品数据</el-col>
									<el-col :span="10" v-if="item[currentLanguage].common.sku_from">
										<el-radio v-model="item[currentLanguage].common.sku_from" label="2" @change="handleSelectRadioChange($event,index)">选品系统</el-radio>
										<el-radio v-model="item[currentLanguage].common.sku_from" label="1" @change="handleSelectRadioChange($event,index)">商品SKU</el-radio>
									</el-col> -->

								</el-row>

								<!-- 								<template v-if="item[currentLanguage].common.sku_from == 2">
									<el-row :gutter="10">
										<el-col :span="6">
											<el-select filterable placeholder="请选择一级活动" value-key="value" v-model="item[currentLanguage].common.ips.gsSelectLevel0" @change="gsFirstChange($event,index,'common')">
												<el-option v-for="(gsItem,gsIndex) in item[currentLanguage].common.ips.level0List" :key="gsIndex" :label="gsItem.label" :value="gsItem"></el-option>
											</el-select>
										</el-col>
										<el-col :span="6">
											<el-select filterable placeholder="请选择二级活动" value-key="value" v-model="item[currentLanguage].common.ips.gsSelectLevel1" @change="gsSecondChange($event,index,'common')"
											  @visible-change="gsVisibleSecond($event,index,'common')">
												<el-option v-for="(gsItem,gsIndex) in item[currentLanguage].common.ips.level1List" :key="gsIndex" :label="gsItem.label" :value="gsItem"></el-option>
											</el-select>
										</el-col>
										<el-col :span="6">
											<el-select filterable placeholder="请选择三级活动" value-key="value" v-model="item[currentLanguage].common.ips.gsSelectLevel2" @change="gsThirdChange($event,index,'common')"
											  @visible-change="gsVisibleThird($event,index,'common')">
												<el-option v-for="(gsItem,gsIndex) in item[currentLanguage].common.ips.level2List" :key="gsIndex" :label="gsItem.label" :value="gsItem"></el-option>
											</el-select>
										</el-col>
									</el-row>
								</template> -->
								<!-- <template v-if="item[currentLanguage].common.sku_from == 1"> -->
								<template>
									<el-row :gutter="20" align="middle" type="flex">
										<el-col :span="24">
											<el-input type="textarea" :rows="2" placeholder="请输入商品分类下的SKU信息" v-model="item[currentLanguage].common.goods_sku" @change="handleCheckSiteSku([site_current,currentLanguage,item[currentLanguage].common.goods_sku, index,'common'])"></el-input>
										</el-col>
									</el-row>
								</template>

							</template>

							<!-- list -->

							<template v-if="item[currentLanguage].radio == 2">
								<!-- plat list -->
								<div v-for="(element,key) of item[currentLanguage].list" :key="key" :class="element.need_sync ==1?'need-sync':''">
									<el-row>
										<el-col :span="10">{{(key.toUpperCase())}} 商品数据列表{{index+1}}</el-col>
										<el-col :span="4" :offset="10" class="goodItemTools text-right">
											<i class="icon el-icon-setting" @click="goodsUnitInit(index,element.goods_sku,key)"></i>
										</el-col>
									</el-row>
									<el-row :gutter="20" align="middle" type="flex">
										<el-col :span="12">
											<el-input type="text" placeholder="请输入商品分类的标题名称key" v-model="element.category_title"></el-input>
										</el-col>
										<el-col :span="12">
											<el-input type="text" placeholder="请输入view more 链接" v-model="element.more_url"></el-input>
										</el-col>
									</el-row>
									<!-- list ips -->
									<el-row>
										<el-col :span="5">商品SKU</el-col>
										<!-- <el-col :span="5">商品数据</el-col>
										<el-col :span="10" v-if="element.sku_from">
											<el-radio v-model="element.sku_from" label="2" @change="handleSelectRadioChange($event,index)">选品系统</el-radio>
											<el-radio v-model="element.sku_from" label="1" @change="handleSelectRadioChange($event,index)">商品SKU</el-radio>
										</el-col> -->

									</el-row>
									<!-- <template v-if="element.sku_from == 2">
										<el-row :gutter="10">
											<el-col :span="6">
												<el-select filterable placeholder="请选择一级活动" value-key="value" v-model="element.ips.gsSelectLevel0" @change="gsFirstChange($event,index,'list',key)">
													<el-option v-for="(gsItem,gsIndex) in element.ips.level0List" :key="gsIndex" :label="gsItem.label" :value="gsItem"></el-option>
												</el-select>
											</el-col>
											<el-col :span="6">
												<el-select filterable placeholder="请选择二级活动" value-key="value" v-model="element.ips.gsSelectLevel1" @change="gsSecondChange($event,index,'list',key)"
												  @visible-change="gsVisibleSecond($event,index,'list',key)">
													<el-option v-for="(gsItem,gsIndex) in element.ips.level1List" :key="gsIndex" :label="gsItem.label" :value="gsItem"></el-option>
												</el-select>
											</el-col>
											<el-col :span="6">
												<el-select filterable placeholder="请选择三级活动" value-key="value" v-model="element.ips.gsSelectLevel2" @change="gsThirdChange($event,index,'list',key)"
												  @visible-change="gsVisibleThird($event,index,'list',key)">
													<el-option v-for="(gsItem,gsIndex) in element.ips.level2List" :key="gsIndex" :label="gsItem.label" :value="gsItem"></el-option>
												</el-select>
											</el-col>
										</el-row>
									</template> -->
									<!-- <template v-if="element.sku_from == 1"> -->
									<template>
										<el-row :gutter="20" align="middle" type="flex">
											<el-col :span="24">
												<el-input type="textarea" :rows="2" placeholder="请输入商品分类下的SKU信息" v-model="element.goods_sku" @change="handleCheckSiteSku([activePlatSelected[key].site_code,currentLanguage,element.goods_sku,index,key])"></el-input>
											</el-col>
										</el-row>
									</template>

								</div>
							</template>
							<!-- <el-row :gutter="20" align="middle" type="flex">
								<el-col :span="22" :offset="1">
									<el-row :gutter="20" align="middle" type="flex" v-for="(element,key) of item[currentLanguage].list" :key="key" :class="element.need_sync ==1?'need-sync':''">
										<el-col :span="3">{{(key.toUpperCase())}} 商品数据列表{{index+1}}</el-col>
										<el-col :span="4">
											<el-input type="text" placeholder="请输入商品分类的标题名称key" v-model="element.category_title"></el-input>
										</el-col>
										<el-col :span="5">
											<el-input type="text" placeholder="请输入view more 链接" v-model="element.more_url"></el-input>
										</el-col>
										<el-col :span="8">
											<el-input type="textarea" :rows="2" placeholder="请输入商品分类下的SKU信息" v-model="element.goods_sku" @change="handleCheckSiteSku([activePlatSelected[key].site_code,currentLanguage,element.goods_sku,index,key])"></el-input>
										</el-col>
										<el-col :span="2" class="goodItemTools">
											<i class="icon el-icon-setting" @click="goodsUnitInit(index,element.goods_sku,key)"></i>
										</el-col>
									</el-row>
								</el-col>
								<el-col :span="2" class="goodItemTools">
									<i class="icon el-icon-arrow-up" @click="goodItemUp(index)"></i>
									<i class="icon el-icon-arrow-down" @click="goodItemDown(index)"></i>
									<i class="icon el-icon-delete" @click="goodItemDel(index)"></i>
								</el-col>
							</el-row> -->

						</el-row>

					</el-row>
				</el-tab-pane>
			</el-tabs>

			<input type="hidden" ref="goodsTemp">

			<span slot="footer" class="dialog-footer good-manage-footer good-manage-shadow" :class="(activeGoodsList.length>1)?'footer-shadow':''">
				<el-row>
					<el-col :span="12" class="text-left">
						<button type="button" class="el-button good-add-btn" @click="handleAddNewGood('pure')">
							<i class="icon el-icon-circle-plus-outline add-plus"></i>
							<span>增加商品数据</span>
						</button>

					</el-col>
				</el-row>
				<el-row>
					<el-col :span="12" class="text-left">
						<el-button type="primary" @click="submitForm(goodsFormInfo.statu,true)">查看子页面预览</el-button>
					</el-col>
					<el-col :span="12" class="text-right">
						<el-button @click="dialogStatu.goodListVisible=false">上一步</el-button>
						<el-button @click="dialogStatu.goodListVisible=false">取消</el-button>
						<!-- <el-button @click="closeDialog('secondStep')">取消</el-button> -->
						<el-button type="primary" @click="submitForm(goodsFormInfo.statu)">确定</el-button>
					</el-col>
				</el-row>
			</span>
		</el-dialog>
		<!-- 查看sku -->
		<el-dialog title="查看SKU" :visible.sync="dialogStatu.preViewSkuVisible" width="800" custom-class="good-view-sku-dialog geshop-goods-sku"
		  @close="skuForm.activeName='1';skuForm.lists=[],skuForm.all_sku='';skuForm.activeLang=''" v-loading="pageLoading">
			<el-tabs type="card" @tab-click="handleSkuTabClick" v-model="skuForm.activeLang">
				<el-select v-model="skuForm.activeName" @change="handleViewTypeChange" style="padding:10px 0 40px;">
					<el-option label="分标题查看" value="1"></el-option>
					<el-option label="统一查看" value="2"></el-option>
				</el-select>
				<el-tab-pane v-for="(langItem,langIndex) in skuForm.lang_list" :label="langItem.name" :name="langItem.key" :key="langIndex"
				  style="max-height: 300px;
    overflow-y: auto;overflow-x:hidden;">
					<el-row :gutter="20" align="middle" type="flex" v-for="(item,index) in skuForm.lists[langItem.key]" :key="index" v-if="skuForm.activeName == '1'">
						<el-col :span="4" class="break-title">{{item.title}}</el-col>
						<el-col :span="20" class="view-col break-title">
							<span>{{item.sku}}</span>
						</el-col>
					</el-row>
					<el-row v-if="skuForm.activeName == '2'">
						<el-col :span="24" class="view-col break-title">
							<span>{{skuForm.all_sku[langItem.key]}}</span>
						</el-col>
					</el-row>
				</el-tab-pane>
				<!-- <el-tab-pane label="分标题查看" name="1">
					<el-row :gutter="20" v-for="(item,index) in skuForm.lists" :key="index">
						<el-col :span="2" :offset="4">{{item.title}}</el-col>
						<el-col :span="10">
							<span>{{item.sku}}</span>
							<el-input type='textarea' v-model="item.sku" disabled></el-input>
						</el-col>
					</el-row>
				</el-tab-pane>
				<el-tab-pane label="统一查看" name="2">
					<el-row>
						<el-col :span="12" :offset="6" class="view-col">
							<span>{{skuForm.all_sku}}</span>
							<el-input type="textarea" v-model="skuForm.all_sku" disabled></el-input>
						</el-col>
					</el-row>
				</el-tab-pane> -->
			</el-tabs>
		</el-dialog>
		<!-- 查看预览 -->
		<el-dialog title="预览子页面" :visible.sync="viewModel.visible" width="80%" @close="viewModelClose" class="geshop-goods-template">
			<el-row>
				<el-col :span="6">
					<el-select v-model="viewModel.selected.value" placeholder="请选择站点" @change="viewModelChange">
						<el-option v-for="(item,index) in viewModel.data" :key="index" :label="index" :value="item"></el-option>
					</el-select>
				</el-col>
			</el-row>
			<el-row v-loading="pageLoading">
				<el-col :span="24" class="imgPreview text-center">
					<iframe :src="viewModel.selected.value" frameborder="0" class="iframePreview" :class="viewModel.selected.label!='pc'?'preview-wap':'preview-pc'"></iframe>
				</el-col>
			</el-row>
		</el-dialog>
		<input type="hidden" name="tempSku" ref="tempSku" id="tempSku" @change="tempSkuChange" v-model="tempSku">
		<div id="goodsLayerWrapper" ref="goodsUnit"></div>
	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import {
	getGoodMgList,
	getGoodActivityList,
	getGoodManagePageList,
	getGoodCheckExists,
	goodManageAddPost,
	getGoodManageSkuList,
	goodManageEdit,
	goodManageDelete,
	getGoodManageGroup,
	goodManageGroupPreview,
	goodManageSaveAndPreview
	, getIPSActivityLevel0
	, getIPSActivityLevel1
	, getIPSActivityLevel2
} from '../plugin/api'
import { getCookie } from '../plugin/mUtils'
import '../../resources/stylesheets/GoodManage.css'

export default {
	components: { siteLayout },
	data () {
		return {
			dataSource: [],
			searchInfo: {},
			pageInfo: {
				pageSize: 10,
				pageNo: 1,
				totalCount: 0
			},

			// pc,m,app
			goodManageTabName: 'pc',
			places: [],

			loading: false,
			pageLoading: false,
			submitLoading: false,
			/* dialog */
			dialogStatu: {
				editVisible: false,
				goodListVisible: false,
				preViewSkuVisible: false
			},
			dialogDefaultVisible: false,
			platformSelectAll: false,
			platSelectBox2: {
				pc: false,
				wap: false,
				app: false
			},
			// platSelectBox: [
			// 	{ label: 'pc', value: false },
			// 	{ label: 'wap', value: false },
			// 	{ label: 'app', value: false },
			// ],
			goodsFormInfo: {
				title: '新增商品数据',
				title1: '新增商品数据',
				title2: '编辑商品数据',
				statu: 'addOne'
			},
			/* 所有活动列表 */
			plantLists: ['pc', 'wap', 'app'],
			activeSelect: {
				props: {
					id: 'id',
					label: 'name',
					value: 'id',
					children: 'children'
				}
			},
			activePlatListsAll: {
				pc: [],
				wap: [],
				app: []
			},
			activePlatSelected: {
				pc: {
					id: '',
					page_id: '',
					site_code: '',
					casArrText: ''
				},
				wap: {
					id: '',
					page_id: '',
					site_code: '',
					casArrText: ''
				},
				app: {
					id: '',
					page_id: '',
					site_code: '',
					casArrText: ''
				}
			},
			activeLangList: [],
			activeLangDefault: 'en',
			/* 选中的子页面 */
			activeAllIndex: {
				pc: '',
				wap: '',
				app: ''
			},
			activePlatForm: {
				nextDisabled: true,
				need_syncTip: 0
			},
			currentLanguage: 'en',
			currentPlat: 'pc',
			currentPlatUp: 'PC',
			activeGoodsList: [],
			/* 编辑原始数据page_list */
			activeGoodsListOriginal: [],
			/* 查看sku */
			skuForm: {
				activeLang: '',
				activeName: '1',
				all_sku: '',
				lists: []
			},
			/* 商品管理sku选中 */
			tempSku: '',
			selectSkuInfo: {},
			/* 查看 */
			viewModel: {
				visible: false,
				data: '',
				selected: ''
			},
			globalCheckStatu: '0',
			/* 选品第一级 */
			gsSelectLevel0List: []
		}
	},
	created () {
		this.handleList()
	},
	mounted () {
		this.site_current = getCookie('site_group_code') + '-' + this.goodManageTabName
		// 设置当前站点信息
		this.places = JSON.parse(localStorage.currentSites).sites
	},
	watch: {
		platSelectBox2: {
			handler (newValue) {
				Object.keys(newValue).forEach((item) => {
					if (newValue[item] == false) {
						this.activeAllIndex[item] = ''
					}
				})
			},
			deep: true
		},
		currentPlatUp: function (val) {
			this.currentPlat = val.toLowerCase()
		},
		goodManageTabName: function (val) {
			this.site_current = getCookie('site_group_code') + '-' + val
		}
	},
	methods: {
		async handleList (page) {
			let param = {
				pageNo: page || this.pageInfo.pageNo || 1,
				pageSize: this.pageInfo.pageSize,
				site_code: getCookie('site_group_code') + '-' + this.goodManageTabName
			}
			this.loading = true
			let res = await getGoodMgList(param)
			this.loading = false
			if (res.code == 0) {
				this.dataSource = res.data.list
				this.pageInfo = res.data.pagination
			}
		},
		async handleSkuLists (id) {
			let viewType = this.skuForm.activeName
			let _this = this
			if (
				(viewType == 1 && this.skuForm.lists.length > 0) ||
				(viewType == 2 && this.skuForm.all_sku)
			) {
				return false
			} else {
				let res = await getGoodManageSkuList({
					gmpId: id,
					viewType: viewType
				})
				if (viewType == 1) {
					this.skuForm.lists = res.data.sku_list
				} else {
					this.skuForm.all_sku = res.data.all_sku
				}
				this.skuForm.lang_list = res.data.lang_list
				this.skuForm.id = id
				if (!this.skuForm.activeLang) {
					_this.skuForm.activeLang = this.skuForm.lang_list[0].key
				}
				this.dialogStatu.preViewSkuVisible = true
			}
		},
		// PC，M，APP切换
		handleGoodManageTabClick (event) {
			this.goodManageTabName = event.name
			this.pageInfo.pageNo = 1
			this.handleList()
		},
		handleInfo () { },
		handleAddOne () {
			this.goodsFormInfo.statu = 'addOne'
			this.goodsFormInfo.title = this.goodsFormInfo.title1
			this.handleActiveLists()
			//获取一级选品
			// this.getIPSActivityLevel0()
		},
		handleDelOne (id) {
			this.$confirm('删除该条商品管理数据后，管理的子页面的商品数据将会被删除，是否删除?', '确认信息', {
				confirmButtonText: '是',
				cancelButtonText: '否',
				type: 'warning'
			}).then(async () => {
				let res = await goodManageDelete({ id: id });
				this.handleList()
			})

		},
		async handleEditOne (groupId) {
			this.goodsFormInfo.statu = 'editOne'
			this.goodsFormInfo.title = this.goodsFormInfo.title2
			this.goodsFormInfo.groupId = groupId

			this.loading = true

			this.dialogDefaultVisible = true

			//获取一级选品
			// this.getIPSActivityLevel0()

			let res = await getGoodManageGroup({ groupId: groupId }),
				data = res.data,
				activity_list_keys = Object.keys(data.activity_list),
				_this = this
			this.loading = false
			this.activePlatListsAll = data.activity_list
			this.activeGoodsListOriginal = data.page_list
			this.maxDataBlockSize = data.maxDataBlockSize

			activity_list_keys.forEach((activityItem, activityIndex) => {
				if (data.activity_list[activityItem][0] && data.activity_list[activityItem][0].site_code) {
					this.activePlatSelected[activityItem].site_code = data.activity_list[activityItem][0].site_code
				}
				// this.activePlatSelected[activityItem].casArrText = ''
			})
			// this.activePlatSelected.pc.site_code = data.activity_list.pc[0].site_code
			// this.activePlatSelected.wap.site_code = data.activity_list.wap[0].site_code
			// this.activePlatSelected.app.site_code = data.activity_list.app[0].site_code

			if (Object.keys(data.selected).length > 0) {
				this.activePlatSelected = Object.assign(
					this.activePlatSelected,
					data.selected
				)

				Object.keys(this.activeAllIndex).forEach((item) => {
					if (data.selected[item]) {
						this.activePlatSelected[item].id = data.selected[item].activity_id
						this.activePlatSelected[item].create_name = data.selected[item].create_name
						this.platSelectBox2[item] = true

						// this.handleChildPage(item, data.selected[item].page_id)

						/* 回填 */
						let val = [data.selected[item].id, Number(data.selected[item].page_id)]
						_this.$refs[item][0].currentValue = val
						_this.getPages(item, data.selected[item].activity_id, 'false', {
							fn: _this.getCasValue,
							item: item,
							val: val
						})
						// _this.getCasValue(item, val)
					}
				})
				this.activePlatForm.nextDisabled = false
			}
			// this.dialogDefaultVisible = true;
		},
		/* 获取所有端活动列表 */
		async handleActiveLists (groupId) {
			this.loading = true
			let param = groupId ? { groupId: groupId } : {}
			let res = await getGoodActivityList(param),
				data = res.data,
				activity_list_keys = Object.keys(data.activity_list)
			this.loading = false
			this.activePlatListsAll = data.activity_list
			if (data.selected.length > 0) {
				this.activePlatSelected = data.selected
			}
			activity_list_keys.forEach((activityItem, activityIndex) => {
				if (data.activity_list[activityItem][0] && data.activity_list[activityItem][0].site_code) {
					this.activePlatSelected[activityItem].site_code = data.activity_list[activityItem][0].site_code
				}
				this.activePlatSelected[activityItem].casArrText = ''
			})
			// this.activePlatSelected.pc.site_code = data.activity_list.pc[0].site_code
			// this.activePlatSelected.wap.site_code = data.activity_list.wap[0].site_code
			// this.activePlatSelected.app.site_code = data.activity_list.app[0].site_code
			// this.activePlatSelected.pc.casArrText = ''
			// this.activePlatSelected.wap.casArrText = ''
			// this.activePlatSelected.app.casArrText = ''

			this.dialogDefaultVisible = true
		},
		async getPages (platform, activityId, selectFirst, callback) {
			//编辑选中 selectFirst:''false
			if (!selectFirst) {
				this.activePlatSelected[platform].page_id = ''
				//回填数据之后选中提示
				if (this.goodsFormInfo.statu == 'editOne') {
					this.$message.warning(
						'改变内容，商品列表信息提交后，将添加在新的页面内，原有页面内容不会做改变'
					)
				}
			} else {
				this.activePlatSelected[platform].page_id = Number(
					this.activePlatSelected[platform].page_id
				)
			}

			let params = { activity_id: activityId },
				activeSelectLists = this.activePlatListsAll[platform],
				res = await getGoodManagePageList(params, { parallel: true })

			let position,
				data,
				pages,
				langList,
				isEnglishSetted = false

			pages = res.data.list //子页面列表
			langList = res.data.langList

			pages.forEach(function (element) {
				element.pageLanguages.forEach(function (item) {
					if (item.lang === 'en') {
						element.title = item.title
						element.name = item.title
						isEnglishSetted = true
					}
				})

				if (!isEnglishSetted) {
					element.title = element.pageLanguages[0].title
					element.name = element.pageLanguages[0].title
				}
			})

			activeSelectLists.forEach(function (element, index) {
				if (element.id == activityId) {
					element.children = pages
					data = element
					position = index
				}
			})

			this.$set(this.activePlatListsAll[platform], position, data) //子页面列表
			this.activeAllIndex[platform] = position //子页面序号
			this.activePlatSelected[platform].langList = langList //活动对应langList

			//默认选中子页面第一个
			if (!selectFirst && pages[0]) {
				this.activePlatSelected[platform].page_id = pages[0].id
				this.activePlatSelected[platform].create_name = pages[0].create_name
			}

			if (callback && callback.item && callback.val) {
				callback['fn'](callback.item, callback.val)
			}
		},
		handleChildPage (platform, page_id) {
			if (this.goodsFormInfo.statu == 'editOne') {
				this.$message.warning(
					'改变内容，商品列表信息提交后，将添加在新的页面内，原有页面内容不会做改变'
				)
			}

			let _this = this,
				childLists = this.activePlatListsAll[platform][
					this.activeAllIndex[platform]
				].children
			childLists.map(item => {
				if (item.id == page_id) {
					_this.activePlatSelected[platform].create_name = item.create_name
				}
			})
		},
		handleTabPlatClick () { },
		/* cascader */
		handleItemChange (val) {
			this.handleCasChange(val)
		},
		handleCasChange (val) {
			let currentPlat = this.currentPlat,
				platSelectBox2 = this.platSelectBox2,
				activePlatListsAll = this.activePlatListsAll,
				num = 0,
				is_managed = 0,
				casArrText = '',
				casArrTextTemp = ''
			if (val.length == 0) {
				this.activeAllIndex[currentPlat] = ''
				this.platSelectBox2[currentPlat] = false
				this.activePlatSelected[currentPlat].id = ''
				this.activePlatSelected[currentPlat].page_id = ''
				this.activePlatSelected[currentPlat].casArrText = ''
				return false
			}
			this.getPages(currentPlat, val[0], true)
			if (val[0] && val[1]) {

				/* 保存name */
				activePlatListsAll[currentPlat].forEach(
					(currentPlatItem, currentPlatIndex) => {
						if (currentPlatItem.id == val[0]) {
							currentPlatItem.children.forEach(
								(currentChildItem, currentChildIndex) => {
									if (currentChildItem.id == val[1]) {
										casArrText =
											this.currentPlatUp +
											' / ' +
											currentPlatItem.name +
											' / ' +
											currentChildItem.name
										if (currentChildItem.is_managed == 1) {
											is_managed = 1
											casArrTextTemp = currentChildItem.name
										}

									}
								}
							)
						}
					}
				)
				if (is_managed) {
					if (this.goodsFormInfo.statu == 'addOne') {
						this.activeAllIndex[currentPlat] = ''
						this.platSelectBox2[currentPlat] = false
						this.activePlatSelected[currentPlat].casArrText = ''
						this.$refs[currentPlat][0].currentValue = []
					}

					this.$message.warning(`${casArrTextTemp}页面已被管理`)
					return false
				}
				this.platSelectBox2[currentPlat] = true
				this.activePlatSelected[currentPlat].id = val[0]
				this.activePlatSelected[currentPlat].page_id = val[1]
				this.activePlatSelected[currentPlat].casArrText = casArrText
			}
			if (!val[1]) {
				this.activeAllIndex[currentPlat] = ''
				this.platSelectBox2[currentPlat] = false
				this.activePlatSelected[currentPlat].page_id = ''
			}

			Object.keys(platSelectBox2).forEach(item => {
				if (platSelectBox2[item] == true) {
					num += 1
				}
			})
			this.activePlatForm.nextDisabled = num > 0 ? false : true

			this.$forceUpdate()
		},
		handlePlatClose (key) {
			let activePlatSelected = this.activePlatSelected
			activePlatSelected[key].casArrText = ''
			activePlatSelected[key].id = ''
			activePlatSelected[key].page_id = ''

			this.platSelectBox2[key] = false
			this.$refs[key][0].currentValue = []
			this.getNextDisabled()
			this.$forceUpdate()
		},
		/* 获取下一步disabled状态 */
		getNextDisabled () {
			let num = 0,
				platSelectBox2 = this.platSelectBox2
			Object.keys(platSelectBox2).forEach(item => {
				if (platSelectBox2[item] == true) {
					num += 1
				}
			})
			this.activePlatForm.nextDisabled = num > 0 ? false : true
		},
		/* 获取casvalue */
		getCasValue (currentPlat, val) {
			let casArrText = '',
				activePlatListsAll = this.activePlatListsAll
			activePlatListsAll[currentPlat].forEach(
				(currentPlatItem, currentPlatIndex) => {
					if (currentPlatItem.id == val[0]) {
						currentPlatItem.children.forEach(
							(currentChildItem, currentChildIndex) => {
								if (currentChildItem.id == val[1]) {
									casArrText =
										currentPlat.toUpperCase() +
										' / ' +
										currentPlatItem.name +
										' / ' +
										currentChildItem.name
								}
							}
						)
					}
				}
			)
			this.activePlatSelected[currentPlat].casArrText = casArrText
		},
		handleAddNewGood (type) {
			let selectedActiveList = this.activePlatSelected,
				lang_list = this.activeLangList,
				newGood = {},
				platKeys = Object.keys(selectedActiveList),
				activeGoodsList = this.activeGoodsList,
				platSelectBox2 = this.platSelectBox2,
				_this = this
			/* 初始化radio */
			let initialLastRadio = activeGoodsList[activeGoodsList.length - 1][_this.activeLangDefault]
				? activeGoodsList[activeGoodsList.length - 1][_this.activeLangDefault].radio
				: '1'
			let itemSame = initialLastRadio == '1' ? '1' : '0'

			lang_list.map((langItem) => {
				newGood[langItem.key] = {
					radio: initialLastRadio,
					common: {
						category_title: '',
						more_url: '',
						goods_sku: '',
						is_same: itemSame,
						ips: {
							gsSelectLevel0: '',
							gsSelectLevel1: '',
							gsSelectLevel2: '',
							level0List: _this.gsSelectLevel0List,
							level1List: [],
							level2List: []
						},
						sku_from: '1',
					},
					list: {}
				}

				platKeys.map((item) => {
					if (platSelectBox2[item] && selectedActiveList[item].page_id) {
						newGood[langItem.key].list[item] = {
							category_title: '',
							more_url: '',
							goods_sku: '',
							is_same: itemSame,
							ips: {
								gsSelectLevel0: '',
								gsSelectLevel1: '',
								gsSelectLevel2: '',
								level0List: _this.gsSelectLevel0List,
								level1List: [],
								level2List: []
							},
							sku_from: '1',
						}
					}
				})
			})
			let activeGoodsListNew = this.activeGoodsList
			activeGoodsListNew.push(newGood)
			this.activeGoodsList = activeGoodsListNew

			if (type == 'pure') {
				let _this = this
				setTimeout(function () {
					_this.handleAddNewGoodCallback()
				}, 100)
			}

		},
		/* 下一步 并初始化 activeGoodsList */
		handleNextStep () {
			let _this = this,
				currentStatu = this.goodsFormInfo.statu, //处理类型
				selectedActiveList = this.activePlatSelected,
				platSelectBox2 = this.platSelectBox2,
				goodsArr = [],
				newGood = {},
				platKeys = Object.keys(selectedActiveList),
				platSelectKeys = Object.keys(selectedActiveList)
			// platSelectBoxKeys = Object.keys(platSelectBox2),
			// activeAllIndex = this.activeAllIndex

			/* 初始化radio */
			let initialRadio = this.goodsFormInfo.statu == 'addOne' ? '1' : '2'

			let platNum = 0 //选中子页面数
			// platSelectBoxKeys.forEach((item, index) => {
			// 	if (platSelectBox2[item] == false) {
			// 		selectedActiveList[item].page_id = ''
			// 	}
			// })

			/* 多平台活动总语言数 */
			let lang_list = [],
				lang_keys = []

			platSelectKeys.forEach((item) => {
				if (platSelectBox2[item]) {
					if (selectedActiveList[item].page_id) {
						platNum += 1
					}
					if (selectedActiveList[item].langList) {
						selectedActiveList[item].langList.forEach(
							(platLangItem, platIndex) => {
								if (lang_keys.indexOf(platLangItem.key) == '-1') {
									lang_keys.push(platLangItem.key)
									lang_list.push(platLangItem)
								}
							}
						)
					}
				}
			})

			this.activeLangList = lang_list
			this.activeLangDefault = lang_list[0].key
			this.currentLanguage = lang_list[0].key

			if (platNum == 0) {
				this.$message.warning('请选择活动页面')
				return false
			}

			lang_list.forEach((langItem) => {
				newGood[langItem.key] = {
					radio: initialRadio,
					common: {
						category_title: '',
						more_url: '',
						goods_sku: '',
						is_same: '1',
						ips: {
							gsSelectLevel0: '',
							gsSelectLevel1: '',
							gsSelectLevel2: '',
							level0List: _this.gsSelectLevel0List,
							level1List: [],
							level2List: []
						},
						sku_from: '1',
					},
					list: {}
				}

				platKeys.map((item) => {
					if (platSelectBox2[item] && selectedActiveList[item].page_id) {
						newGood[langItem.key].list[item] = {
							category_title: '',
							more_url: '',
							goods_sku: '',
							is_same: '1',

							ips: {
								gsSelectLevel0: '',
								gsSelectLevel1: '',
								gsSelectLevel2: '',
								level0List: _this.gsSelectLevel0List,
								level1List: [],
								level2List: []
							},
							sku_from: '1',
						}
					}
				})
			})
			goodsArr.push(newGood)

			if (currentStatu == 'editOne') {
				this.activeGoodsList = goodsArr
				let max_num = this.maxDataBlockSize
				for (let i = 0; i < max_num - 1; i++) {
					this.handleAddNewGood()
				}

				let transData = this.transDataToGood(goodsArr)
				this.activeGoodsList = transData
			} else {
				this.activeGoodsList = goodsArr
			}
			this.dialogStatu.goodListVisible = true
			if (this.activePlatForm.need_syncTip == 1) {
				this.$confirm(
					'红框内的内容代表在装修页面中被删除，是否立即恢复数据？',
					'提示',
					{
						confirmButtonText: '是',
						cancelButtonText: '否',
						type: 'warning'
					}
				)
					.then(() => {
						this.activeGoodsList.forEach((activeItem, activeIndex) => {
							Object.keys(activeItem).forEach((langItem, langIndex) => {
								let activeLangItem = activeItem[langItem]
								if (activeLangItem.common.need_sync == 1) {
									_this.activeGoodsList[activeIndex][
										langItem
									].common.need_sync = 0
								}
								let langLists = activeLangItem.list
								Object.keys(langLists).forEach((platItem, platIndex) => {
									if (langLists[platItem].need_sync == 1) {
										// langLists[platItem].need_sync = 0
										_this.activeGoodsList[activeIndex][langItem].list[
											platItem
										].need_sync = 0
									}
								})
							})
						})
					})
					.catch(() => { })
			}
		},
		/* 格式化数据块列表 */
		transDataToGood (activeGoodsList) {
			let page_list = this.activeGoodsListOriginal,
				activePlatSelected = this.activePlatSelected,
				need_syncTip = this.activePlatForm.need_syncTip,
				_this = this

			page_list.forEach((dataItem, dataIndex) => {
				let dataItemPlat = dataItem.site_code.split('-')[1], //pc
					lang_list = dataItem.lang_list, //lang_list
					lang_listKeys = Object.keys(dataItem.lang_list), //[en,es]
					lang_page_id = dataItem.page_id,
					lang_activity_id = dataItem.activity_id

				/* 遍历语言下条数,增加总条数 */
				lang_listKeys.forEach((langItem, langIndex) => {
					let lang_current_list = lang_list[langItem] //langItem 'en'


					lang_current_list.forEach((item, index) => {
						if (activeGoodsList[index] && activeGoodsList[index][langItem]) {
							activeGoodsList[index][langItem].radio = '2'

							/* 填充数据 */
							if (activeGoodsList[index][langItem].list[dataItemPlat]) {
								activeGoodsList[index][langItem].list[dataItemPlat] = {
									category_title: item.category_title,
									more_url: item.more_url,
									goods_sku: item.goods_sku,
									need_sync: item.need_sync,
									id: item.id,
									is_same: item.is_same,
									ips: {
										gsSelectLevel0: item.ips.gsSelectLevel0,
										gsSelectLevel1: item.ips.gsSelectLevel1,
										gsSelectLevel2: item.ips.gsSelectLevel2,
										level0List: _this.gsSelectLevel0List,
										level1List: [item.ips.gsSelectLevel1],
										level2List: [item.ips.gsSelectLevel2]
									},
									sku_from: item.sku_from,

								}
								if (item.need_sync && need_syncTip != 1) {
									_this.activePlatForm.need_syncTip = 1
								}
							}

							/* radio选中 */
							if (item.is_same == '1') {
								activeGoodsList[index][langItem].radio = '1'
								activeGoodsList[index][langItem].common = {
									category_title: item.category_title,
									more_url: item.more_url,
									goods_sku: item.goods_sku,
									is_same: '1',
									ips: {
										gsSelectLevel0: item.ips.gsSelectLevel0,
										gsSelectLevel1: item.ips.gsSelectLevel1,
										gsSelectLevel2: item.ips.gsSelectLevel2,
										level0List: _this.gsSelectLevel0List,
										level1List: [item.ips.gsSelectLevel1],
										level2List: [item.ips.gsSelectLevel2]
									},
									sku_from: item.sku_from,
								}
							} else {
								activeGoodsList[index][langItem].radio = '2'
							}

							// /* 检查是否变更活动或活动子页面 */
							Object.keys(activePlatSelected).forEach(
								(platActiveItem) => {
									if (
										lang_activity_id !=
										activePlatSelected[dataItemPlat].activity_id ||
										lang_page_id != activePlatSelected[dataItemPlat].page_id
									) {
										if (activeGoodsList[index][langItem].list[dataItemPlat]) {
											activeGoodsList[index][langItem].list[dataItemPlat] = {
												category_title: '',
												more_url: '',
												goods_sku: '',
												ips: {
													gsSelectLevel0: '',
													gsSelectLevel1: '',
													gsSelectLevel2: '',
													level0List: _this.gsSelectLevel0List,
													level1List: [],
													level2List: []
												},
												sku_from: '1',
											}
										}
									}
								}
							)
						}
					})

					/* 需清空空白数据 */
					activeGoodsList.forEach((newActiveItem, newActiveIndex) => {
						if (newActiveIndex + 1 > lang_current_list.length) {
							delete activeGoodsList[newActiveIndex][langItem]
						}

					})

				})
			})
			return activeGoodsList
		},
		async submitForm (type, preview) {
			this.submitPageStatu = true
			if (this.globalCheckStatu == '1') {
				return false
			}


			let _this = this
			let contArr = [],
				langList = [],
				activeGoodsList = this.activeGoodsList,
				activePlatSelected = this.activePlatSelected,
				activePlatSelectedKeys = Object.keys(activePlatSelected)

			/* 创建param content  */
			activePlatSelectedKeys.forEach((item, index) => {
				let itemList = activePlatSelected[item]
				if (itemList.page_id && this.platSelectBox2[item]) {
					let current = {
						site_code: itemList.site_code,
						page_id: itemList.page_id,
						lang_list: {}
					}
					/* 选中的站点 */
					if (itemList.langList) {
						contArr.push(current)
						itemList.langList.forEach((langItem, langIndex) => {
							current.lang_list[langItem.key] = []
							if (langList.indexOf(langItem.key) == -1) {
								langList.push(langItem.key)
							}
						})
					}
				}
			})

			/* 按站点语言划分 */
			activeGoodsList.forEach((activeItem, activeIndex) => {
				let enGroup = activeItem[_this.activeLangDefault] //英文数据
				let activeLangKeys = Object.keys(activeItem)
				activeLangKeys.forEach((langItem, langIndex) => {
					let langOne = activeItem[langItem]
					let radio = langOne.radio,
						listPlat = langOne.list,
						listPlatKeys = []
					let langCurrent = langItem
					let commonOne = langOne.common

					if (listPlat) {
						listPlatKeys = Object.keys(langOne.list)
					}

					/* 选品新增 ips 删除level列表 */
					let commonOneWithSelect = {
						category_title: commonOne.category_title,
						more_url: commonOne.more_url,
						goods_sku: commonOne.goods_sku,
						is_same: commonOne.is_same,
						ips: {
							gsSelectLevel0: commonOne.ips.gsSelectLevel0,
							gsSelectLevel1: commonOne.ips.gsSelectLevel1,
							gsSelectLevel2: commonOne.ips.gsSelectLevel2,
						},
						sku_from: commonOne.sku_from
					}

					/* 删除level列表 end */


					/* 是否需要同步radio */
					// langItem !== 'en' && type == 'addOne'
					if (langItem !== 'en') {
						if (enGroup) {
							if (enGroup.radio == '1') {
								if (enGroup.radio == radio && langOne.common.goods_sku == '') {
									// commonOne= enGroup.common
									commonOneWithSelect = {
										category_title: enGroup.common.category_title,
										more_url: enGroup.common.more_url,
										goods_sku: enGroup.common.goods_sku,
										is_same: enGroup.common.is_same,
										ips: {
											gsSelectLevel0: enGroup.common.ips.gsSelectLevel0,
											gsSelectLevel1: enGroup.common.ips.gsSelectLevel1,
											gsSelectLevel2: enGroup.common.ips.gsSelectLevel2,
										},
										sku_from: enGroup.common.sku_from
									}

									_this.activeGoodsList[activeIndex][langItem].common = {
										category_title: enGroup.common.category_title,
										more_url: enGroup.common.more_url,
										goods_sku: enGroup.common.goods_sku,
										is_same: enGroup.common.is_same,
										ips: {
											gsSelectLevel0: enGroup.common.ips.gsSelectLevel0,
											gsSelectLevel1: enGroup.common.ips.gsSelectLevel1,
											gsSelectLevel2: enGroup.common.ips.gsSelectLevel2,
											level0List: enGroup.common.ips.level0List,
											level1List: enGroup.common.ips.level1List,
											level2List: enGroup.common.ips.level2List
										},
										sku_from: enGroup.common.sku_from,
									}
								}
								// if (enGroup.radio !== radio) {
								// 	radio = '1'
								// 	_this.activeGoodsList[activeIndex][langItem].radio = '1'
								// }
							}
							if (enGroup.radio == '2' && type == 'addOne' || (enGroup.radio == '2' && type == 'editOne' && _this.activeGoodsList[activeIndex][langItem].common.category_title == '')) {
								if (enGroup.radio !== radio) {
									radio = '2'
									_this.activeGoodsList[activeIndex][langItem].radio = '2'
								}
							}
						}
					}

					listPlatKeys.forEach((platItem) => {
						let site_code = activePlatSelected[platItem].site_code,
							platCurrent = listPlat[platItem]
						let formOne = {
							category_title: platCurrent.category_title,
							more_url: platCurrent.more_url,
							goods_sku: platCurrent.goods_sku,
							is_same: platCurrent.is_same,
							ips: {
								gsSelectLevel0: platCurrent.ips.gsSelectLevel0,
								gsSelectLevel1: platCurrent.ips.gsSelectLevel1,
								gsSelectLevel2: platCurrent.ips.gsSelectLevel2,
							},
							sku_from: platCurrent.sku_from
						}
						/* 新增模式判断是否需要同步语种数据 */
						if (enGroup) {
							if (radio == '2' && formOne.goods_sku == '' && enGroup.list[platItem].goods_sku || (radio == '2' && formOne.category_title == '' && enGroup.list[platItem].category_title)) {
								/* formOne without ips */
								// formOne = enGroup.list[platItem]
								/* formOne with ips */
								let enGroupCurrent = enGroup.list[platItem]
								formOne = {
									category_title: enGroupCurrent.category_title,
									more_url: enGroupCurrent.more_url,
									goods_sku: enGroupCurrent.goods_sku,
									is_same: enGroupCurrent.is_same,
									ips: {
										gsSelectLevel0: enGroupCurrent.ips.gsSelectLevel0,
										gsSelectLevel1: enGroupCurrent.ips.gsSelectLevel1,
										gsSelectLevel2: enGroupCurrent.ips.gsSelectLevel2,
									},
									sku_from: enGroupCurrent.sku_from
								}

								_this.activeGoodsList[activeIndex][langItem].list[platItem] = {
									category_title: formOne.category_title,
									more_url: formOne.more_url,
									goods_sku: formOne.goods_sku,
									is_same: formOne.is_same,
									ips: {
										gsSelectLevel0: formOne.ips.gsSelectLevel0,
										gsSelectLevel1: formOne.ips.gsSelectLevel1,
										gsSelectLevel2: formOne.ips.gsSelectLevel2,
									},
									sku_from: formOne.sku_from
								}
							}
						}

						contArr.map((arrItem, arrIndex) => {
							let siteCode = arrItem.site_code
							if (site_code == siteCode && arrItem.lang_list[langCurrent]) {
								if (radio == 1) {
									arrItem.lang_list[langCurrent].push(commonOneWithSelect)
								} else {
									arrItem.lang_list[langCurrent].push(formOne)
								}
							}
						})
					})
				})
			})

			let activeGroupId = this.goodsFormInfo.groupId
			if (activeGroupId) {
				type = 'editOne'
			}

			let fnSelect = type == 'addOne' ? goodManageAddPost : goodManageEdit
			let fnParam =
				type == 'addOne'
					? { content: JSON.stringify(contArr) }
					: {
						content: JSON.stringify(contArr),
						groupId: this.goodsFormInfo.groupId
					}

			if (preview) {
				fnSelect = goodManageSaveAndPreview
			}
			let res = await fnSelect(fnParam)
			if (res.code == 0) {
				if (preview) {
					let preview_urls = res.data.preview_urls
					this.viewModel.visible = true
					this.viewModel.data = preview_urls
					this.viewModel.selected = {
						label: Object.keys(preview_urls)[0],
						value: preview_urls[Object.keys(preview_urls)[0]]
					}
					if (res.data.groupId) {
						this.goodsFormInfo.groupId = res.data.groupId
					}
					return false
				} else {
					this.dialogStatu.goodListVisible = false
					this.dialogDefaultVisible = false
					this.handleList(1)
				}
			}
		},
		/* 同步英文SKU */
		syncEnData (lang) {
			let _this = this,
				activeGoodsList = this.activeGoodsList
			this.$confirm('是否同步英文界面SKU？', '确认信息', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(() => {
				activeGoodsList.forEach((activeItem, activeIndex) => {
					let enItem = activeItem[_this.activeLangDefault]

					_this.activeGoodsList[activeIndex][lang].radio = enItem.radio
					_this.activeGoodsList[activeIndex][lang].common.goods_sku =
						enItem.common.goods_sku

					// _this.activeGoodsList[activeIndex][lang].common = enItem.common
					Object.keys(enItem.list).forEach((item, index) => {
						let enPlatItem = enItem.list[item]
						_this.activeGoodsList[activeIndex][lang].list[item].goods_sku =
							enPlatItem.goods_sku
					})
				})
			})
		},
		/* dialog弹窗关闭
		 */
		closeDialog (step) {
			if (step == 'goodManage') {
				let plantLists = this.plantLists
				plantLists.forEach((platItem) => {
					this.$refs[platItem][0].currentValue = []
				})

				let initData = {
					dataSource: this.dataSource,
					pageInfo: this.pageInfo,
					site_current: this.site_current,
					places: this.places,
					goodManageTabName: this.goodManageTabName
				}
				Object.assign(this.$data, this.$options.data(), initData)
			}
		},
		/* 平台类型全选 已弃用 */
		platformSelectAllChange (statu, value) {
			let platformSelectAll = statu === 'off' ? value : this.platformSelectAll
			let keyArr = Object.keys(this.platSelectBox2)
			keyArr.map(item => {
				return (this.platSelectBox2[item] = platformSelectAll)
			})

			this.activePlatForm.nextDisabled =
				platformSelectAll == true ? false : true
		},
		plaformCheckChange (value) {
			let _this = this
			let selectTime = 0
			// let activePlatSelected = this.activePlatSelected
			let keyArr = Object.keys(this.platSelectBox2)
			keyArr.map(item => {
				if (_this.platSelectBox2[item] == true) {
					return (selectTime += 1)
				}
			})
			if (!value) {
				this.platformSelectAll = false
			} else {
				if (selectTime == keyArr.length) {
					this.platformSelectAll = true
				}
			}
			/* 选中平台 */
			this.activePlatForm.nextDisabled = selectTime > 0 ? false : true
		},
		/* 商品数据-删除 */
		goodItemDel (index) {
			this.$confirm('删除后，对应的信息将被清空，是否依旧删除？', '确认信息', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(() => {
				// this.activeGoodsList.splice(index, 1)
				let currentLanguage = this.currentLanguage,
					activeItem = this.activeGoodsList[index]
				// activeItem[currentLanguage] = {}
				delete activeItem[currentLanguage]
				this.activeGoodsList[index] = activeItem
				this.$forceUpdate()
			})
		},
		goodItemUp (index) {
			if (index != 0) {
				let current = this.activeGoodsList[index],
					prev = this.activeGoodsList[index - 1]
				this.activeGoodsList[index] = prev
				this.activeGoodsList[index - 1] = current
				this.$forceUpdate()
			}
		},
		goodItemDown (index) {
			if (index + 1 < this.activeGoodsList.length) {
				let current = this.activeGoodsList[index],
					next = this.activeGoodsList[index + 1]
				this.activeGoodsList[index] = next
				this.activeGoodsList[index + 1] = current
				this.$forceUpdate()
			}
		},
		/* 语言tab切换 */
		handleTabClick () {
			this.$forceUpdate()
			let activeGoodsList = this.activeGoodsList,
				_this = this

			/**
			 * 数据同步
			 * langEnCurrent 每条数据下英文数据
			 * currentLangRadio 当前语言下数据
			 */
			activeGoodsList.forEach((activeItem, activeIndex) => {
				let langEnCurrent = activeItem[_this.activeLangDefault],
					activeLangKeys = Object.keys(activeItem)
				activeLangKeys.forEach((langItem, langKey) => {
					let currentLang = activeItem[langItem]
					if (langItem != 'en' && currentLang && currentLang.list) {
						let currentLangRadio = currentLang.radio,
							currentLangLists = currentLang.list,
							currentLangPlatListsKeys = Object.keys(currentLang.list)

						if (currentLangRadio && langEnCurrent && langEnCurrent.radio) {
							if (langEnCurrent.radio == '1') {
								if (currentLangRadio == '1') {
									if (!currentLang.common.category_title && !currentLang.common.goods_sku) {
										this.activeGoodsList[activeIndex][langItem].common.category_title = langEnCurrent.common.category_title
										this.activeGoodsList[activeIndex][langItem].common.goods_sku = langEnCurrent.common.goods_sku
										/* 增加选品同步 */
										this.activeGoodsList[activeIndex][langItem].common.sku_from = langEnCurrent.common.sku_from
										this.activeGoodsList[activeIndex][langItem].common.ips = {
											gsSelectLevel0: langEnCurrent.common.ips.gsSelectLevel0,
											gsSelectLevel1: langEnCurrent.common.ips.gsSelectLevel1,
											gsSelectLevel2: langEnCurrent.common.ips.gsSelectLevel2,
											level0List: langEnCurrent.common.ips.level0List,
											level1List: langEnCurrent.common.ips.level1List,
											level2List: langEnCurrent.common.ips.level2List
										}
										// this.activeGoodsList[activeIndex][langItem].common.ips = langEnCurrent.common.ips
									}
									if (!currentLang.common.more_url) {
										this.activeGoodsList[activeIndex][langItem].common.more_url = langEnCurrent.common.more_url
									}
									if (
										!currentLang.common.goods_sku &&
										currentLang.common.category_title &&
										currentLang.common.category_title ==
										this.activeGoodsList[activeIndex][langItem].common.category_title
									) {
										this.activeGoodsList[activeIndex][langItem].common.goods_sku = langEnCurrent.common.goods_sku
									}
								} else {
									currentLangPlatListsKeys.forEach((platItem, platIndex) => {
										let platObj = currentLangLists[platItem]
										if (!platObj.category_title && !platObj.goods_sku) {
											this.activeGoodsList[activeIndex][langItem].list[platItem].category_title = langEnCurrent.common.category_title
											this.activeGoodsList[activeIndex][langItem].list[platItem].goods_sku = langEnCurrent.common.goods_sku

											/* 增加选品同步 */
											this.activeGoodsList[activeIndex][langItem].list[platItem].sku_from = langEnCurrent.common.sku_from
											this.activeGoodsList[activeIndex][langItem].list[platItem].ips = {
												gsSelectLevel0: langEnCurrent.common.ips.gsSelectLevel0,
												gsSelectLevel1: langEnCurrent.common.ips.gsSelectLevel1,
												gsSelectLevel2: langEnCurrent.common.ips.gsSelectLevel2,
												level0List: langEnCurrent.common.ips.level0List,
												level1List: langEnCurrent.common.ips.level1List,
												level2List: langEnCurrent.common.ips.level2List
											}
											// this.activeGoodsList[activeIndex][langItem].list[platItem].ips = langEnCurrent.common.ips
										}
										if (!platObj.more_url) {
											this.activeGoodsList[activeIndex][langItem].list[platItem].more_url = langEnCurrent.common.more_url
										}
									})
								}
							} else if (langEnCurrent.radio == '2') {
								if (currentLangRadio == '1') {
									if (!currentLang.common.category_title && !currentLang.common.goods_sku) {
										this.activeGoodsList[activeIndex][langItem].radio = '2'

										currentLangPlatListsKeys.forEach((platItem, platIndex) => {
											let platObj = currentLangLists[platItem]
											if (!platObj.category_title && !platObj.goods_sku) {
												this.activeGoodsList[activeIndex][langItem].list[platItem].category_title = langEnCurrent.list[platItem].category_title
												this.activeGoodsList[activeIndex][langItem].list[platItem].goods_sku = langEnCurrent.list[platItem].goods_sku
												/* 增加选品同步 */
												this.activeGoodsList[activeIndex][langItem].list[platItem].sku_from = langEnCurrent.list[platItem].sku_from
												this.activeGoodsList[activeIndex][langItem].list[platItem].ips = {
													gsSelectLevel0: langEnCurrent.list[platItem].ips.gsSelectLevel0,
													gsSelectLevel1: langEnCurrent.list[platItem].ips.gsSelectLevel1,
													gsSelectLevel2: langEnCurrent.list[platItem].ips.gsSelectLevel2,
													level0List: langEnCurrent.list[platItem].ips.level0List,
													level1List: langEnCurrent.list[platItem].ips.level1List,
													level2List: langEnCurrent.list[platItem].ips.level2List
												}

											}
											if (!platObj.more_url) {
												this.activeGoodsList[activeIndex][langItem].list[platItem].more_url = langEnCurrent.list[platItem].more_url
											}
											/* 商品标题存在,sku不存在的情况 */
											if (
												!platObj.goods_sku && platObj.category_title && platObj.category_title == this.activeGoodsList[activeIndex][langItem].list[platItem].category_title
											) {
												this.activeGoodsList[activeIndex][langItem].list[platItem].goods_sku = langEnCurrent.list[platItem].goods_sku
											}
										})
									}
								} else {
									currentLangPlatListsKeys.forEach((platItem, platIndex) => {
										let platObj = currentLangLists[platItem]
										if (!platObj.category_title && !platObj.goods_sku) {
											this.activeGoodsList[activeIndex][langItem].list[platItem].category_title = langEnCurrent.list[platItem].category_title
											this.activeGoodsList[activeIndex][langItem].list[platItem].goods_sku = langEnCurrent.list[platItem].goods_sku
											/* 增加选品同步 */
											this.activeGoodsList[activeIndex][langItem].list[platItem].sku_from = langEnCurrent.list[platItem].sku_from
											this.activeGoodsList[activeIndex][langItem].list[platItem].ips = {
												gsSelectLevel0: langEnCurrent.list[platItem].ips.gsSelectLevel0,
												gsSelectLevel1: langEnCurrent.list[platItem].ips.gsSelectLevel1,
												gsSelectLevel2: langEnCurrent.list[platItem].ips.gsSelectLevel2,
												level0List: langEnCurrent.list[platItem].ips.level0List,
												level1List: langEnCurrent.list[platItem].ips.level1List,
												level2List: langEnCurrent.list[platItem].ips.level2List
											}

										}
										if (!platObj.more_url) {
											this.activeGoodsList[activeIndex][langItem].list[platItem].more_url = langEnCurrent.list[platItem].more_url
										}
										/* 商品标题存在,sku不存在的情况 */
										if (!platObj.goods_sku && platObj.category_title && platObj.category_title == this.activeGoodsList[activeIndex][langItem].list[platItem].category_title) {
											this.activeGoodsList[activeIndex][langItem].list[platItem].goods_sku = langEnCurrent.list[platItem].goods_sku
										}
									})
								}
							}
						}
					}
				})
			})
		},
		handleSkuTabClick () {
			let id = this.skuForm.id
			this.handleSkuLists(id)
		},
		handleViewTypeChange (value) {
			let id = this.skuForm.id
			this.handleSkuLists(id)
		},
		async handlePreview (groupId) {
			this.viewModel.visible = true
			this.pageLoading = true
			let res = await goodManageGroupPreview({ groupId: groupId })
			this.viewModel.data = res.data
			let siteListKeys = Object.keys(res.data)
			if (siteListKeys.length > 0) {
				this.viewModel.selected = {
					label: siteListKeys[0],
					value: res.data[siteListKeys[0]]
				}
			}

			this.pageLoading = false
		},
		goodsTitleChange (index, key, element) {
			this.activeGoodsList[index].plat[key].form.category_title =
				element.form.category_title
		},
		handleCurrentChange (currentPage) {
			this.pageInfo.pageNo = currentPage
			this.handleList(currentPage)
		},
		/* 不同数据端 */
		handleRadioChange (data, key) {
			let value = data,
				activeGoodsList = this.activeGoodsList,
				currentLanguage = this.currentLanguage,
				is_same = (value == '1') ? '1' : '0',
				listKeys = Object.keys(activeGoodsList[key][currentLanguage].list),
				_this = this
			listKeys.forEach((item) => {
				_this.activeGoodsList[key][currentLanguage].list[item].is_same = is_same
			})
			_this.activeGoodsList[key][currentLanguage].common.is_same = is_same
			this.$forceUpdate()
		},
		viewModelClose () {
			this.viewModel.visible = false
		},
		viewModelChange (value) {
			let viewModelData = this.viewModel.data
			Object.keys(viewModelData).forEach((item, index) => {
				if (value == viewModelData[item]) {
					this.viewModel.selected.label = item
					this.viewModel.selected.value = viewModelData[item]
				}
			})
		},
		/**
		 * 选品系统
		 */
		async getIPSActivityLevel0 () {

			let param = { site_code: this.site_current }
			let res = await getIPSActivityLevel0(param),
				data = res.data
			if (!(data && data.list)) {
				return false;
			}
			this.gsSelectLevel0List = this.IPSDataFormat(data)
		},
		/**
		 * 选品二级选择
		 */
		async getIPSActivityLevel1 (id, activeIndex, type, place) {
			let param = { activity_id: id }
			let currentLanguage = this.currentLanguage
			if (type == 'common') {
				this.activeGoodsList[activeIndex][currentLanguage][type].ips.gsSelectLevel1 = ''
				this.activeGoodsList[activeIndex][currentLanguage][type].ips.gsSelectLevel2 = ''
			} else {
				this.activeGoodsList[activeIndex][currentLanguage][type][place].ips.gsSelectLevel1 = ''
				this.activeGoodsList[activeIndex][currentLanguage][type][place].ips.gsSelectLevel2 = ''
			}

			let res = await getIPSActivityLevel1(param),
				data = res.data,
				levelArr = this.IPSDataFormat(data)

			if (type == 'common') {
				this.activeGoodsList[activeIndex][currentLanguage][type].ips.level1List = levelArr
			} else {
				this.activeGoodsList[activeIndex][currentLanguage][type][place].ips.level1List = levelArr
			}
		},
		async getIPSActivityLevel2 (id, activeIndex, type, place) {
			let param = { activity_child_group_id: id }
			let currentLanguage = this.currentLanguage

			if (type == 'common') {
				this.activeGoodsList[activeIndex][currentLanguage][type].ips.gsSelectLevel2 = ''
			} else {
				this.activeGoodsList[activeIndex][currentLanguage][type][place].ips.gsSelectLevel2 = ''
			}

			let res = await getIPSActivityLevel2(param),
				data = res.data
			let levelArr = this.IPSDataFormat(data)
			if (type == 'common') {
				this.activeGoodsList[activeIndex][currentLanguage][type].ips.level2List = levelArr
			} else {
				this.activeGoodsList[activeIndex][currentLanguage][type][place].ips.level2List = levelArr
			}
		},
		handleSelectRadioChange (value) {

		},
		/* 获取IPS第一级活动 type 'common','list' */
		gsFirstChange (data, activeIndex, type, place) {
			this.getIPSActivityLevel1(data.value, activeIndex, type, place)
		},
		gsSecondChange (data, activeIndex, type, place) {
			this.getIPSActivityLevel2(data.value, activeIndex, type, place)
		},
		gsThirdChange (data, activeIndex, type, place) {

		},
		/* 选品下拉出现 */
		gsVisibleSecond (data, activeIndex, type, place) {
			if (!data || this.goodsFormInfo.statu == 'addOne') { return false }
			let currentLang = this.currentLanguage,
				ips = ""
			if (type == 'common') {
				ips = this.activeGoodsList[activeIndex][currentLang].common.ips
			} else {
				ips = this.activeGoodsList[activeIndex][currentLang].list[place].ips
			}
			if (ips.visibleGet1) { return false }

			this.getIPSActivityLevel1(ips.gsSelectLevel0.value, activeIndex, type, place)
			ips.visibleGet1 = true
		},
		gsVisibleThird (data, activeIndex, type, place) {
			if (!data || this.goodsFormInfo.statu == 'addOne') { return false }
			let currentLang = this.currentLanguage,
				ips = ""
			if (type == 'common') {
				ips = this.activeGoodsList[activeIndex][currentLang].common.ips
			} else {
				ips = this.activeGoodsList[activeIndex][currentLang].list[place].ips
			}
			if (ips.visibleGet2) { return false }

			this.getIPSActivityLevel2(ips.gsSelectLevel1.value, activeIndex, type, place)
			ips.visibleGet2 = true
		},

		/* select数据格式化 */
		IPSDataFormat (data) {
			return data.list.map(function (element, index) {
				let obj = {}
				obj.value = element.activity_id || element.activity_child_group_id || element.activity_child_id;
				obj.label = element.activity_title || element.activity_child_group_title || element.activity_child_title;
				return obj;
			})
		},
		/* product sku manage */
		tempSkuChange (event) {
			let selectSkuInfo = this.selectSkuInfo,
				// currentSkuText,
				newValue = event.target.value
			this.tempSku = event.target.value
			if (selectSkuInfo.plat != 'current' && selectSkuInfo.plat != 'common') {
				this.activeGoodsList[selectSkuInfo.index][this.currentLanguage].list[selectSkuInfo.plat].goods_sku = newValue
			} else {
				this.activeGoodsList[selectSkuInfo.index][this.currentLanguage].common.goods_sku = newValue
			}
		},
		/*
		* 站点sku检查
		* activeItem 条下标,plat 平台
		*siteCode, lang, skuList, activeKey, activeKeyPlat
		* sku check */
		async handleCheckSiteSku (options) {
			let siteCode = options[0]
			let lang = options[1],
				skuList = options[2],
				activeKey = options[3],
				activeKeyPlat = options[4]
			if (!skuList) {
				this.$message.warning('请补充SKU信息')
				return false
			}
			let statu = false
			this.globalCheckStatu = '1'	//检查中
			let res = await getGoodCheckExists(
				{
					siteCode: siteCode,
					lang: lang,
					skuList: skuList
				},
				{ messageOff: true, successOff: true }
			)
			if (res.code == '0') {
				statu = true
				this.globalCheckStatu = '0'
			} else {
				this.validateSKUStatu = false
				let delSkuArr = res.message.split(' ')[1].split(','),
					skuListArr = skuList.split(',')
				this.$confirm(`${res.message},是否清空`, '提示', {
					confirmButtonText: '是',
					cancelButtonText: '否',
					type: 'warning',
					customClass: 'gs-sku-tip'
				}).then(() => {
					let newSku = ''
					delSkuArr.forEach(delItem => {
						skuListArr.forEach((skuItem, skuIndex) => {
							if (delItem == skuItem) {
								skuListArr.splice(skuIndex, 1)
							}
						})
					})

					newSku = skuListArr.toString()
					if (activeKeyPlat == 'common') {
						this.activeGoodsList[activeKey][
							this.currentLanguage
						].common.goods_sku = newSku
					} else {
						this.activeGoodsList[activeKey][this.currentLanguage].list[
							activeKeyPlat
						].goods_sku = newSku
					}
					this.globalCheckStatu = '0'
				}).catch(() => {
					this.globalCheckStatu = '0'
				})
			}
			return statu
		},
		/*
		* SKU列表管理
		* index
		* key 站点key
		*/
		async goodsUnitInit (index, skus, key) {
			let siteCode =
				key != 'common'
					? this.activePlatSelected[key].site_code
					: this.site_current,
				lang = this.currentLanguage,
				page_id,
				_this = this
			if (key != 'common') {
				page_id = this.activePlatSelected[key].page_id
			} else {
				Object.keys(this.platSelectBox2).forEach((item) => {
					if (this.platSelectBox2[item] == true) {
						page_id = this.activePlatSelected[item].page_id
					}
				})
			}

			sessionStorage.setItem('currentComponentId', page_id)
			let checkStatu = await this.handleCheckSiteSku([
				siteCode,
				lang,
				skus,
				index,
				key
			])
			if (!checkStatu) {
				return false
			}

			let $layerWrap = $('#goods_layer_wrap')

			this.selectSkuInfo = {
				index: index,
				plat: key
			}
			// $trIndex
			if (!$layerWrap.length) {
				$('body').append(
					'<div id="goods_layer_wrap"><input type="hidden" name="tempGoodsArr"></input><table id="goods_layer_table" lay-filter="gs_goodstable"></table>' +
					'<div id="goods_toolbar"><a class="layui-btn layui-btn-xs" lay-event="class-edit" data-type="class-edit">替换</a>' +
					'<a class="layui-btn layui-btn-xs" lay-event="class-add" data-type="class-add">增加</a><a class="layui-btn layui-btn-xs" lay-event="class-del" data-type="class-del">删除</a>' +
					'<a class="layui-btn layui-btn-xs" lay-event="class-up" data-type="class-up" title="上移">上移</a><a class="layui-btn layui-btn-xs" lay-event="class-down" data-type="class-down">下移</a></div></div>'
				)
			}

			var url = '/activity/goods/tpl-goods-list'
			var Table = layui.table
			var pageId = page_id || '6550'
			Table.render({
				elem: '#goods_layer_table',
				height: 400,
				// , width: 1180
				url: url,
				where: { lang: lang || 'en', skus: skus, pageId: pageId },
				// , page: true
				limit: 20,
				id: 'gs_goodstable',
				cols: [
					[
						{ type: 'checkbox', fixed: 'left' },
						{
							field: 'numbers',
							title: '序号',
							width: 80,
							templet: function (d) {
								var value = d.LAY_INDEX
								return (
									'<input type="number" name="goods_item_orders" value="' +
									value +
									'" data-order="' +
									value +
									'" style="width: 100%;"> '
								)
							}
						},
						{ field: 'goods_id', title: '商品ID', width: 100 },
						{ field: 'goods_sn', title: '商品SKU', width: 140 },
						{ field: 'goods_title', title: '商品标题', width: 100 },
						{
							field: 'goods_img',
							title: '商品图片',
							width: 100,
							height: 100,
							templet: function (d) {
								return '<img src="' + d.goods_img + '">'
							}
						},
						{
							field: 'is_on_sale',
							title: '上架',
							width: 80,
							templet: function (d) {
								var text = d.is_on_sale == '1' ? '上架' : '下架'
								return text
							}
						},
						{ field: 'goods_number', title: '库存', width: 80 },
						{ field: 'promte_percent', title: '促销利润比', width: 100 },
						{
							field: 'left_time',
							title: '促销时间',
							width: 200,
							templet: function (d) {
								return d.promote_start_date
									? d.promote_start_date + ' 至 ' + d.promote_end_date
									: ''
							}
						},
						{ field: 'activity_number', title: '活动库存', width: 100 },
						{
							field: 'activity_volume_number',
							title: '活动库存销量',
							width: 120
						},
						{
							fixed: 'right',
							width: 250,
							align: 'center',
							toolbar: '#goods_toolbar'
						}
					]
				],
				done: function (res, curr, count) {
					if (res.code !== 0 && res.message) {
						/* 清除wrap */
						$('#goods_layer_wrap')
							.next('.layui-form')
							.remove()
						$('#goods_layer_wrap').remove()
						layer.msg(res.message)
						return falseR
					}

					// /* goods_layer存在 */
					if ($('#goods_layer').length > 0) {
						var $newForm = $('#goods_layer_table').next('.layui-form'),
							$newFixBody = $newForm.find('.layui-table-main tbody'),
							$newFixLt = $newForm.find(
								'.layui-table-fixed-l .layui-table-body tbody'
							),
							$newFixRt = $newForm.find(
								'.layui-table-fixed-r .layui-table-body tbody'
							),
							$newHeader = $newForm.find('.layui-table-header:eq(0) thead')

						$('#goods_layer')
							.find('.layui-table-main tbody')
							.html($newFixBody.html())
						$('#goods_layer')
							.find('.layui-table-fixed-l .layui-table-body tbody')
							.html($newFixLt.html())
						$('#goods_layer')
							.find('.layui-table-fixed-r .layui-table-body tbody')
							.html($newFixRt.html())
						$('#goods_layer')
							.find('.layui-table-header:eq(0) thead')
							.html($newHeader.html())
						$(
							'.layui-table-fixed-r .layui-table-body,.layui-table-fixed-l .layui-table-body',
							'#goods_layer'
						).css('max-height', '383px')
						layui.form.render('checkbox')
						// GsManager.init()
					} else {
						layer.open({
							title: '商品管理',
							type: 1,
							skin: 'layui-goods-list',
							id: 'goods_layer',
							closeBtn: 1,
							anim: 5,
							area: '1200px',
							shade: 0.3,
							shadeClose: true,
							content: $('#goods_layer_table').next('.layui-form'),
							btn: ['保存', '取消', '批量删除'],
							yes: function (index, layero) {
								var goodsOrder = $(
									'input[name=tempGoodsArr]',
									'#goods_layer_wrap'
								).val()
								layer.close(index)
								$('input[name=tempSku]').val(goodsOrder)
								$('input[name=tempSku]')[0].dispatchEvent(new Event('change'))
								// $targetInput.val(goodsOrder)
							},
							btn2: function (index, layero) { },
							btn3: function (index, layero) {
								layer.confirm(
									'确定批量删除商品吗？删除后，商品将不再列表中展示！',
									{ icon: 3, title: '提示' },
									function (index) {
										$('#goods_layer')
											.find('.layui-table-fixed-l tbody .layui-form-checked')
											.each(function () {
												var $realBody = $(
													'#goods_layer .layui-table-main tbody'
												),
													$fixTr = $(this).parents('tr:eq(0)'),
													$index = $fixTr.attr('data-index'),
													$tr = $realBody.find('tr[data-index=' + $index + ']')
												$fixTr.remove()
												$tr.remove()
												$('#goods_layer .layui-table-fixed-r tbody ')
													.find('tr[data-index=' + $index + ']')
													.remove()
											})
										layer.close(index)
										GsManager.countNum()
									}
								)

								return false
							},
							success: function (layero, index) {
								GsManager.init()
							},
							end: function (index, layero) {
								/* 清除wrap */
								$('#goods_layer_wrap')
									.next('.layui-form')
									.remove()
								$('#goods_layer_wrap').remove()
							}
						})
					}
				}
			})
		},

		/* 新增聚焦 */
		handleAddNewGoodCallback () {
			let content = document.getElementById('pane-en').parentNode,
				contH = content.clientHeight,
				contScrollH = content.scrollHeight,
				distance = contScrollH - content.scrollTop - contH
			if (distance > 600) {
				content.scrollTop = contScrollH - contH
			} else {
				pageScroll()
			}

			function pageScroll () {
				content.scrollBy(0, 10)
				let scrollDelay
				if (scrollDelay || content.scrollTop == content.scrollHeight - content.clientHeight) {
					clearTimeout(scrollDelay)
				} else {
					scrollDelay = setTimeout(function () {
						pageScroll()
					}, 20)
				}

			}
		},
	}
}
</script>

<style lang="less" scoped>
.cas-activity {
  width: 60%;
}

.fistStepDialog {
  text-align: center;
}
.text-left {
  text-align: left;
}
.text-right {
  text-align: right;
}
.goodListsAddForm {
  // border-top: 1px solid #ddd;
  .goodListsItem {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
  }
}
.border-box {
  border-radius: 5px;
  border: 1px solid #ddd;
  padding: 8px 10px;
}
.border-col-ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.col-ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.footer-shadow {
  box-shadow: 0 -2px 5px #ccc;
}
.good-add-btn {
  color: #409eff;
  cursor: pointer;
  border: none;
  background-color: transparent !important;
  span {
    height: 25px;
    vertical-align: middle;
  }
}
.add-plus {
  font-size: 20px;
  vertical-align: middle;
}
.iframePreview {
  border: 0;
  width: 100%;
  height: 600px;
}
.preview-wap {
  width: 350px;
}

.need-sync {
  border: 1px solid red !important;
  border-radius: 5px;
}

/* sku view */
.view-col {
  // border: 1px solid #dcdfe6;
  padding: 10px;
}

.break-title {
  word-break: break-all;
}

.icon-geshop-del {
  font-size: 24px;
}
/* footer */
</style>

<style>
/* dialog goodsManage */
.goods-form-first,
.good-view-sku-dialog,
.goodManageDialog {
  width: 800px;
}
.goods-form-first {
  height: 566px;
}
.goods-form-first .el-icon-close {
  color: #dcdfe6;
}
.goods-form-first .el-tabs__content {
  padding: 0 10px;
}
.goodManageDialog .el-tabs__content {
  max-height: 480px;
  overflow-y: scroll;
  padding: 0 10px;
}
.goodManageDialog .el-dialog__footer {
  padding: 0 !important;
}

.goodManageDialog .good-manage-footer {
  display: block;
  padding: 10px 20px 20px;
}

.gs-sku-tip .el-message-box__message p {
  word-break: break-all;
}
/* 级联宽度 */
.cas-good-platList .el-cascader-menu__item {
  max-width: 300px;
}

.good-view-sku-dialog ::-webkit-scrollbar,
.goodManageDialog ::-webkit-scrollbar {
  width: 10px;
  height: 10px;
  background-color: rgba(108, 114, 125, 0.5);
}

.good-view-sku-dialog ::-webkit-scrollbar-track,
.goodManageDialog ::-webkit-scrollbar-track {
  background-color: #f5f5f5;
}

.good-view-sku-dialog ::-webkit-scrollbar-thumb,
.goodManageDialog ::-webkit-scrollbar-thumb {
  border-radius: 8px;
  background-color: rgba(108, 114, 125, 0.5);
}
</style>


