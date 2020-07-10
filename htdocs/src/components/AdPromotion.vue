<template>
	<site-layout @publicReady="publicReady">
		<el-row :span="24" class="geshop-Activity-tit">
			<span class="geshop-Activity-title">活动推广管理</span>
		</el-row>
		<el-row class="geshop-Activity-btn">
			<el-col>
				<el-button class="geshop-Activity-btn-add" @click="createActivity">
					<span class="icon-geshop-pack-up"></span>
					<span class="geshop-icon-add-text">新增活动</span>
				</el-button>
			</el-col>
			<!-- <el-col>
				<el-button class="geshop-Activity-btn-refresh" @click="refreshHeadFoot">
					<span class="icon-geshop-reset"></span>
					<span class="geshop-icon-refresh-text">一键刷新头尾部</span>
				</el-button>
			</el-col> -->
			<el-col>
				<el-form  class="geshop-form-inline" :inline="true" >
					<el-form-item label="活动名称">
						<el-input v-model="searchWord" placeholder=""></el-input>
					</el-form-item>
					<el-form-item label="创建者">
						<el-input v-model="createName" placeholder=""></el-input>
					</el-form-item>
					<el-form-item>
						<el-button type="primary"  @click="doSearch">搜索</el-button>
					</el-form-item>
				</el-form>
			</el-col>
		</el-row>
		<el-row class="geshop-port-switch">
			<template>
				<el-tabs v-model="activityTabName" type="card" @tab-click="handleActivityTabClick" class="tab-header">
					<el-tab-pane v-for="(item,key) in places" :key="key" :label="item.platform_name" :name="key"></el-tab-pane>
				</el-tabs>
			</template>
			<el-col :span="24" class="geshop-activity-lists">
				<el-table :data="activityList" @expand-change="handleExpandChange" style="width: 100%" :row-key="getRowKey" :expand-row-keys="expandRowKeys"
				  @row-click="handleExpandChange">
					<el-table-column type="expand">
						<template slot-scope="scope">
							<el-card class="box-card geshop-activity-child-pages" v-for="list in scope.row.children" :key="list.key">
								<div>
									<div class="geshop-activity-child-pages-banner">
										<img :src="list.preview_pic_url" class="child-pages-image">
										<div class="child-page-flag-green" v-if="(list.status == 2)">上线</div>
										<div class="child-page-flag-red" v-if="(list.status == 4)">下线</div>
									</div>
									<div class="child-pages-hover">
										<el-tooltip content="装修" placement="bottom" effect="light">
											<el-button class="icon-geshop-decorate" @click="decorate_redirect(list.design_url,list.is_lock,list.activity_create_user)"></el-button>
										</el-tooltip>
										<el-tooltip content="上线" placement="bottom" effect="light">
											<el-button class="icon-geshop-online" v-if="(list.status == 1 || list.status == 4)" @click="verifyPage(list.id, list.activity_id, 2, list.is_lock , list.activity_create_user)"></el-button>
										</el-tooltip>
										<el-tooltip content="下线" placement="bottom" effect="light">
											<el-button class="icon-geshop-offline" v-if="list.status == 2" @click="verifyPage(list.id, list.activity_id, 4, list.is_lock, list.activity_create_user)"></el-button>
										</el-tooltip>
										<!-- <el-tooltip content="预览" placement="bottom" effect="light">
											<el-button class="icon-geshop-search" @click="redirect(list.preview_url)"></el-button>
										</el-tooltip> -->
										<el-tooltip content="转M端" placement="bottom" effect="light">
											<el-button class="icon-geshop-mobile advertisement-icon-geshop-mobile" @click="convertM(list.id, list.is_lock, list.activity_create_user, list.group_info, list.group_languages)" v-if="list.activity_type == 1 && list.hasToWap == true"></el-button>
										</el-tooltip>
										<!-- <el-tooltip content="转APP端" placement="bottom" effect="light">
											<el-button class="icon-geshop-mobile advertisement-icon-geshop-mobile" @click="convertAPP(list.id, list.is_lock, list.activity_create_user, list.group_info, list.group_languages)" v-if="list.activity_type == 2"></el-button>
										</el-tooltip> -->
									</div>
								</div>
								<div class="child-pages-title">{{ list.title }}</div>
								<div class="child-pages-time">{{ parseInt(list.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}</div>
								<div class="child-pages-id-name">ID: {{ list.id }} <span>{{ list.create_name }}</span></div>
								<div>
									<a @click="viewPages(list.id)" class="child-pages-link">查看访问链接</a>
									<el-dropdown split-button style="margin-left:207px;">
										<el-dropdown-menu slot="dropdown">
											<el-dropdown-item type="primary" size="small" @click.native="editPage(list, list.is_lock, list.activity_create_user, scope.row.group_info.lang_list, scope.row.group_info.platform_list)">编辑</el-dropdown-item>
											<el-dropdown-item type="danger" size="small" @click.native="removePage(list, list.is_lock, list.activity_create_user)">删除</el-dropdown-item>
										</el-dropdown-menu>
									</el-dropdown>
								</div>
							</el-card>
							<el-card class="box-card geshop-activity-child-pages">
								<el-col>
									<img src="/resources/images/default/banner_default.png" class="child-pages-image" style="height:112px;width:100%;display:block;">
									<el-button class="icon-geshop-add-big" @click="createPage(scope.row.id, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user)"
									  style="font-size:40px;margin-top:28px;margin-left:100px;padding:0px 0px;"></el-button>
									<p class="child-pages-add">添加子页面</p>
								</el-col>
							</el-card>
						</template>
					</el-table-column>
					<el-table-column prop="id" label="ID" width="100"></el-table-column>
					<el-table-column prop="name" label="活动名称" width="260"></el-table-column>
					<el-table-column prop="update_time" label="操作时间">
						<template slot-scope="scope">
							<span>{{ parseInt(scope.row.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
					<el-table-column prop="create_name" label="创建者"></el-table-column>
					<el-table-column label="状态">
						<template slot-scope="scope">
							<span v-if="scope.row.status == 1">
								<em class="geshop-icon-online-stay"></em>
								<em style="font-style:normal;">待上线</em>
							</span>
							<span v-else-if="scope.row.status == 2">
								<em class="geshop-icon-online"></em>
								<em style="font-style:normal;">已上线</em>
							</span>
							<span v-else-if="scope.row.status == 4">
								<em class="geshop-icon-offline"></em>
								<em style="font-style:normal;">已下线</em>
							</span>
						</template>
					</el-table-column>
					<!-- 活动操作 -->
					<el-table-column label="操作" width="450" class="geshop-activity-more" class-name="ge-activity-list-operating">
						<template slot-scope="scope" style="line-height:80px;">
							<el-tooltip content="新增子页面" placement="bottom" effect="light">
								<el-button class="icon-geshop-add-small" style="font-size:24px;" v-if="scope.row.is_lock == 0" @click.stop="createPage(scope.row.id, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user)"></el-button>
							</el-tooltip>
							<el-button class="icon-geshop-add-small is-lock" v-if="scope.row.is_lock == 1" @click.stop="createPage(scope.row.id, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user)"></el-button>
							<el-tooltip content="上线" placement="bottom" effect="light">
								<el-button class="icon-geshop-online" style="font-size:24px;" v-show="scope.row.is_lock == 0" @click.stop="verifyActivity(scope.row.id, 2, scope.row.is_lock, scope.row.create_user)"
							  	v-if="permissions.includes('activity/activity/verify') && [1, 4].includes(scope.row.status)">
								</el-button>
							</el-tooltip>
							<el-tooltip content="上线" placement="bottom" effect="light">
								<el-button class="icon-geshop-online" style="font-size:24px;color:#9E9E9E;background-color:#ffffff;" v-show="scope.row.is_lock == 1"
							  	@click.stop="verifyActivity(scope.row.id, 2, scope.row.is_lock, scope.row.create_user)" v-if="permissions.includes('activity/activity/verify') && [1, 4].includes(scope.row.status)">
								</el-button>
							</el-tooltip>
							<el-tooltip content="下线" placement="bottom" effect="light">
								<el-button class="icon-geshop-offline" style="font-size:24px;" v-show="scope.row.is_lock == 0" @click.stop="verifyActivity(scope.row.id, 4, scope.row.is_lock, scope.row.create_user)"
							  v-if="permissions.includes('activity/activity/verify') && scope.row.status == 2"></el-button>
							</el-tooltip>
							<el-tooltip content="下线" placement="bottom" effect="light">
								<el-button class="icon-geshop-offline" style="font-size:24px;color:#9E9E9E;background-color:#ffffff;" v-show="scope.row.is_lock == 1"
							  @click.stop="verifyActivity(scope.row.id, 4, scope.row.is_lock, scope.row.create_user)" v-if="permissions.includes('activity/activity/verify') && scope.row.status == 2"></el-button>
							</el-tooltip>
							<el-dropdown split-button style="margin-left:10px;">
								<el-dropdown-menu slot="dropdown">
									<el-dropdown-item type="primary" size="small" @click.native="editActivity(scope.row, scope.row.is_lock, scope.row.create_user)">编辑</el-dropdown-item>
									<el-dropdown-item type="danger" size="small" @click.native="removeActivity(scope.row.id, scope.row.is_lock, scope.row.create_user)">删除</el-dropdown-item>
									<el-dropdown-item type="primary" @click.native="viewDetail(scope.row)" size="small">查看二维码</el-dropdown-item>
								</el-dropdown-menu>
							</el-dropdown>
							<span style="margin-left:10px;line-height:80px;">锁定</span>
							<el-switch v-model="scope.row.lock_status" @change="lockingActivity(scope.row.id, scope.row.is_lock, scope.row.create_user, $event)"></el-switch>
						</template>
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>

		<el-row v-if="total > 10">
			<el-col :span="24" class="text-right geshop-article-page">
				<el-pagination layout="prev, pager, next" :page-size="Number(10)" :current-page="currentPage" :total="total" @current-change="handleCurrentChange"></el-pagination>
			</el-col>
		</el-row>

		<el-dialog :visible.sync="isDetailActive" width="360px">
			<el-card>
				<div slot="header" class="text-center">{{ currentActivityRow.name }}</div>
				<div>
					<el-row v-if="Boolean(currentActivityRow.qrcode) || Boolean(currentActivityRow.preview)">
						<el-col :span="24">
							<h3>活动预览地址：</h3>
						</el-col>
						<el-col :span="24" class="text-center" v-if="Boolean(currentActivityRow.qrcode)"><img alt="二维码" :src="currentActivityRow.qrcode" width="120"></el-col>
						<el-col :span="24" class="text-center" v-if="Boolean(currentActivityRow.preview)">
							<a :href="currentActivityRow.preview" class="activity-detail-link">{{ currentActivityRow.name }}</a>
						</el-col>
					</el-row>
				</div>
			</el-card>
		</el-dialog>

		<el-dialog :title="activityForm.dialogTitle" :visible.sync="dialogActivityVisible" class="geshop-new-activities">
			<el-form :model="activityForm" :rules="activityRules" ref="activityForm">
				<!-- 新增状态应用端口 -->
				<el-form-item label="应用端口" prop="place" class="geshop-new-activities-place" v-if="activityForm.status==1">
					<el-checkbox-group v-model="activityForm.place">
						<el-checkbox v-for="(item,key) in places" :disabled="key == activityForm.site_code" :label="key" :key="key">{{ item.platform_name }}</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<!-- 编辑状态应用端口 -->
				<el-form-item label="应用端口" prop="place" class="geshop-new-activities-place" v-if="activityForm.status==2">
					<el-checkbox-group v-model="activityForm.editPlace">
						<el-checkbox v-for="(item,key) in editPlaces" disabled :label="item.code" :key="key">{{ item.name }}</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<!-- 新增状态语言 -->
				<el-form-item label="语言" prop="lang" class="geshop-new-activities-lang" v-if="activityForm.status==1">
					<el-checkbox-group v-model="activityForm.lang">
						<el-checkbox v-for="item in supportLangs" :label="item.key" :key="item.key" >{{ item.name }}</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<p style="margin:0;color:#b7b7b7;">注：请选择装修需要的最多语种，不同端无此语种，不装修即可</p>
				<!-- 编辑状态语言 -->
				<el-form-item label="语言" prop="lang" class="geshop-new-activities-lang" v-if="activityForm.status==2">
					<el-checkbox-group v-model="activityForm.editSupportLang">
						<el-checkbox v-for="item in editSupportLangs" disabled :label="item.key" :key="item.key">{{ item.name }}</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<el-form-item label="名称" prop="name" class="geshop-new-activities-name" v-on:keyup.native="handleNameKeyup">
					<el-input v-model="activityForm.name"></el-input>
					<span class="count-tip-box">{{ actNameCount }}/100</span>
				</el-form-item>
				<el-form-item label="简介" prop="description" class="geshop-new-activities-introduction" v-on:keyup.native="handleIntroductionKeyup">
					<el-input type="textarea" v-model="activityForm.description" :rows="4" placeholder="请简单描述一下这个活动......"></el-input>
					<span class="count-tip-box">{{ actIntroductionCount }}/200</span>
				</el-form-item>
				<p style="margin:5px 0 5px 0;color:#b7b7b7;">注：勾选应用端口及语言提交后不可再修改了哦！</p>
				<el-form-item class="geshop-new-activities-btn">
					<el-button @click="resetForm('activityForm')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('activityForm')" size="small" :loading="submitLoading">确定</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>
		<!-- 新增子页面 -->
		<el-dialog :title="pageForm.dialogTitle" :visible.sync="dialogPageVisible" class="geshop-new-child-page" @close="subDialogClose">
			<el-form :model="publicPageForm" :rules="publicPageRules" ref="publicPageForm">
				<el-form-item label="已选应用端口" prop="place" class="gs-col-all child-page-place">
					<el-checkbox-group v-model="publicPageForm.place">
						<el-checkbox v-for="(item,key) in pagePlaces" :label="item.code" disabled :key="key">{{ item.name }}</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
			</el-form>
			
			<el-tabs type="card" @tab-click="handleTabClick" v-model="currentLanguage">
				<el-tab-pane v-for="item in langList" :label="item.name" :name="item.key" :key="item.key"></el-tab-pane>
			</el-tabs>

			<el-form :model="pageForm" :rules="pageRules" ref="pageForm" class="geshop-new-child-page-title">
				<el-form-item label="专题活动名称" prop="title" class="gs-col-all">
					<el-input v-model="pageForm.title" @change="updatePageTitle" v-on:keyup.native="handleTitleKeyup"></el-input>
				</el-form-item>
				<el-form-item label="主商品sku" prop="goods_sn" class="child-page-link goods_sn-input">
					<el-row :gutter="20">
						<el-col :span="15" style="width:200px;margin-right:20px;">
							<el-input v-model="pageForm.goods_sn" @change="updatePagegoods_sn" @keyup.native="handleGoodsSkuKeyup" debounce style="width:200px;"></el-input>
							<!-- <el-input v-model="pageForm.goods_sn" @change="updatePagegoods_sn" @blur="handleGoodsSkuLink" v-on:keyup.native="handleGoodsSkuKeyup" style="width:200px;"></el-input> -->
							<!-- <el-input v-model="pageForm.goods_sn" @change="updatePagegoods_sn" v-on:keyup.native="handleGoodsSkuLink" debounce="300" style="width:200px;"></el-input> -->
						</el-col>
						<span v-if="pageForm.page_url != ''" style="display: inline-block;max-width: 400px;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;">推广链接：{{ pageForm.page_url }}</span>
						<el-button type="primary" v-if="pageForm.page_url != ''" style="display:inline-block;margin-left:15px;vertical-align:top;" @click="handleCopySkuLink"><span v-if="pageForm.copyStatus == 1">复制</span><span v-else>已复制</span></el-button>
						<el-col class="child-page-note">
							<span>备注：推广链接地址，当添加完sku后，自动生成且不可以修改</span>
						</el-col>
					</el-row>
				</el-form-item>
				<el-form-item label="PC端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.pc_status" class="child-page-model">
					<el-button type="primary" size="small" :disabled="Boolean(pageForm.id)" @click="handleModelTempSelect('pc')">选择模板</el-button>
					<el-tag type="info">{{ pageForm.tpl_name }}</el-tag>
				</el-form-item>
				<el-form-item label="M端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.m_status" class="child-page-model">
					<el-button type="primary" size="small" :disabled="Boolean(pageForm.id)" @click="handleModelTempSelect('wap')">选择模板</el-button>
					<el-tag type="info">{{ pageForm.m_tpl_name }}</el-tag>
				</el-form-item>
				<el-form-item label="统计代码" prop="statistics_code" class="ad-child-page-statistical-code">
					<el-input type="textarea" v-model="pageForm.statistics_code" v-on:keyup.native="handleCodeKeyup" :rows="4" @change="updatePageStatisticsCode"></el-input>
					<span class="count-tip-box">{{ codeCount }}/500</span>
				</el-form-item>
				<el-form-item class="child-page-btns" style="clear:both">
					<el-button @click="resetForm('pageForm')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('pageForm')" size="small" :loading="submitLoading">确定</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>

		<el-dialog title="活动访问地址" :visible.sync="dialogLinksVisible">
			<el-row>
				<el-button v-for="item in pageLinks" type="primary" :key="item.lang" @click="redirect(item.page_url)">{{ item.lang_name }}</el-button>
				<p>{{ tips }}
					<el-button @click="redistribution()" v-if="tips" type="primary">重新发布</el-button>
				</p>
			</el-row>
		</el-dialog>

		<el-dialog title="转化为APP页面" :visible.sync="dialogConvertAPP">
			<el-form :model="convertForm" :rules="convertRules" ref="convertForm" label-width="100px">
				<el-form-item label="APP活动" prop="activity_id" v-if="convertForm.is_group==0">
					<el-select v-model="convertForm.activity_id" @change="getAppPages">
						<el-option v-for="item in appActivities" :label="item.name" :value="item.id" :key="item.id"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="APP页面" prop="page_id" v-if="convertForm.is_group==0">
					<el-select v-model="convertForm.page_id" placeholder="请选择APP页面">
						<el-option v-for="item in appPages" :label="item.title" :value="item.id" :key="item.id"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="选择语言" prop="lang">
					<el-select v-model="convertForm.lang" placeholder="请选择语言">
						<el-option v-for="item in convertLangs" :label="item.name" :value="item.key" :key="item.key"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="选择方式">
					<el-radio-group v-model="convertForm.model">
						<el-radio label="1">在所选子页面后追加</el-radio>
						<el-radio label="2">覆盖子页面内容</el-radio>
					</el-radio-group>
					<el-alert title="此操作不会删除原APP活动页面内容，仅在页面最后增加。" type="warning" v-if="convertForm.model == 1" :closable="false"></el-alert>
					<el-alert title="此操作直接覆盖所选子页面的原有装修效果，且不可还原，请慎用此选项。" type="warning" v-if="convertForm.model == 2" :closable="false"></el-alert>
				</el-form-item>
				<el-form-item>
					<el-button @click="resetForm('convertForm')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('convertForm')" size="small" :loading="submitLoading">确定</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>

		<el-dialog title="转化为M端活动" :visible.sync="dialogConvertM">
			<el-form :model="convertForm_M" :rules="convertRules_M" ref="convertForm_M" label-width="140px">
				<el-form-item label="选择M端活动" prop="activity_id" placeholder="请选择活动" v-if="convertForm_M.is_group==0">
					<el-select v-model="convertForm_M.activity_id" @change="getMPages">
						<el-option v-for="item in mActivities" :label="item.name" :value="item.id" :key="item.id"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="选择活动子页面" prop="page_id" v-if="convertForm_M.is_group==0">
					<el-select v-model="convertForm_M.page_id" placeholder="请选择子页面">
						<el-option v-for="item in mPages" :label="item.title" :value="item.id" :key="item.id"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="选择语言" prop="lang">
					<el-select v-model="convertForm_M.lang" placeholder="请选择语言">
						<el-option v-for="item in convertLangs" :label="item.name" :value="item.key" :key="item.key"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="选择方式">
					<el-radio-group v-model="convertForm_M.model">
						<el-radio label="1">在所选子页面后追加</el-radio>
						<el-radio label="2">覆盖子页面内容</el-radio>
					</el-radio-group>
					<el-alert title="此操作不会删除原M活动页面内容，仅在页面最后增加。" type="warning" v-if="convertForm_M.model == 1" :closable="false"></el-alert>
					<el-alert title="此操作直接覆盖所选子页面的原有装修效果，且不可还原，请慎用此选项。" type="warning" v-if="convertForm_M.model == 2" :closable="false"></el-alert>
				</el-form-item>
				<el-form-item>
					<el-button @click="resetForm('convertForm_M')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('convertForm_M')" size="small" :loading="submitLoading">确定</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>

		<el-dialog title="子页面新增" :visible.sync="modelInfo.visible" @close="handleModelClose" append-to-body class="geshop-page-template">
			<div class="geshop-page-template-title">
				<span class="icon-geshop-backward"></span>
				<span class="geshop-page-template-word">选择页面模板</span>
			</div>
			<el-row>
				<el-tabs type="border-card" class="model-dialog" @tab-click="tplTabClick" v-model="modelInfo.tabActive" v-loading="tplInfo.loading">
					<el-tab-pane label="我的模板" name="2" ref="tpl0">
						<div class="model-box" v-for="(item,index) in pageTemplateList" :key="index">
							<el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect" v-if="item.create_user == siteInfo.userName"
							  @change="updateModelSelect">
								<div class="model-item">
									<span>{{item.name}}</span>
									<img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
									<div class="icon-geshop-search" @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"></div>
								</div>
							</el-radio>
						</div>
					</el-tab-pane>
					<el-tab-pane label="共用模板" name="1" ref="tpl1">
						<div class="model-box" v-for="(item,index) in pageTemplateList" :key="index">
							<el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect" v-if="item.create_user != siteInfo.userName && item.tpl_type == 1"
							  @change="updateModelSelect">
								<div class="model-item">
									<span>{{item.name}}</span>
									<img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
									<div class="icon-geshop-search" @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"></div>
								</div>
							</el-radio>
						</div>
					</el-tab-pane>
					<el-row v-if="pageTemplateList.length == 0|| pageTemplateList.length>0 && (modelInfo.tabActive == '2' && modelInfo.tempLength1 == 0 || modelInfo.tabActive == '1' && modelInfo.tempLength2 == 0)">
						<el-col :span="24" style="text-align: center;margin: 20px 0;">{{pageTemplateListWarn}}</el-col>
					</el-row>
				</el-tabs>
			</el-row>
			<div slot="footer" class="dialog-footer">
				<el-button @click="handleCancelSelectedModel">取 消</el-button>
				<el-button type="primary" @click="handleSureModel">确定</el-button>
			</div>
		</el-dialog>

		<el-dialog title="转换成APP端活动" :visible.sync="dialogConvertVisible" width="30%">
			<span><img src="/resources/images/moblie.png" alt="" style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
			<p style="text-align:center;font-size:16px">已转换完成</p>
			<p style="text-align:center;font-size:14px；color:black">您可点击下方按钮进入APP端装修页查看</p>
			<span slot="footer" class="ToM">
				<el-button style="text-align:center" type="primary" @click="convertRedirect()">进入APP端装修页面</el-button>
			</span>
		</el-dialog>

		<el-dialog title="转换成M端活动" :visible.sync="dialogConvertMVisible" width="30%">
			<span><img src="/resources/images/moblie.png" alt="" style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
			<p style="text-align:center;font-size:16px">已转换完成</p>
			<p style="text-align:center;font-size:14px；color:black">您可点击下方按钮进入M端装修页查看</p>
			<span slot="footer" class="ToM">
				<el-button style="text-align:center" type="primary" @click="convertMRedirect()">进入M端装修页面</el-button>
			</span>
		</el-dialog>

		<el-dialog title="页面模板" class="geshop-template-model" :visible.sync="viewModel.visible" @close="viewModelClose" :width="viewModel.sideWidth">
			<el-row v-loading="pageLoading">
				<el-col class="imgPreview text-center" style="height:100%;">
					<iframe frameborder="0" :src="viewModel.src" class="iframePreview" style="width:100%;height:100%;"></iframe>
				</el-col>
			</el-row>
		</el-dialog>
	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import {
	getAdvertisementList,
	getAdvertisementPages,
	addAdvertisement,
	editAdvertisement,
	deleteAdvertisement,
	addAdvertisementPage,
	editAdvertisementPage,
	getAdGoodsUrl,

	getPageList, 
	addActivity, 
	updateActivity, 
	deleteActivity, 
	verifyActivity,
	advertisementVerifyActivity, 
	lockingActivity, 
	refreshSite, 
	refreshSelete,
	batchEditPage, 
	deletePage, 
	verifyPage, 
	advertisementVerifyPage,
	getLangKeyList, 
	getPageTemplateList, 
	getAppActivityList, 
	getMActivityList, 
	convertToAppPage, 
	convertToMPage,
	advertisementConvertToMPage,
	getAccessLink,
	advertisementGetAccessLink,
	actReleased, 
	adActReleased, 
	batchAddPage
} from '../plugin/api'
import { getCookie } from '../plugin/mUtils'
import bus from '../store/bus-index.js'
import '../../resources/stylesheets/activityManagement.css'
import '../../resources/stylesheets/icon.css'
import '../../resources/fonts/svg-fonts/style.css'

export default {
	components: { siteLayout },
	data() {
		return {
			createName: '',
			options: [],
			pageIds: this.pageIds,
			logId: '',
			submitLoading: false,
			pageTemplateList: [],
			pageTemplateList1: [],
			supportLangs: [],
			expandRowKeys: [],
			permissions: [],
			searchWord: '',
			currentPage: 1,

			// pc,m,app
			activityTabName: 'pc',
			places: [],
			editPlaces: [],
			pc_status: true,
			m_status: false,
			app_status: false,
			pageFormPlace: ['pc'],
			templateSelectPlace: 'pc',
			pc_activity_title: '',
			m_activity_title: '',
			app_activity_title: '',
			getTmpListStatus: false,
			getTmpListValue: 'pc',
			allSupportLangArrs: [],
			editSupportLangs: [],
			pagePlaces: [],
			goods_sn_diff: '',
			skuStatus: true,

			publicPageForm: {
				place: ['']
			},

			total: 0,
			activityList: [],
			isDetailActive: false,
			currentActivityRow: {
				start_time: new Date().getTime() / 1000,
				end_time: new Date().getTime() / 1000
			},
			activityForm: {
				id: '',
				type: '',
				lang: ['en'],
				place: ['pc'],
				editPlace: [],
				editSupportLang: [],
				name: '',
				url_name: '',
				description: '',
				range_time: [],
				start_time: '',
				end_time: '',
				dialogTitle: '新增活动',
				status: 1,
				miss_count: 1,
				miss_count_status: 1
			},
			activityRules: {
				type: [
					{ required: true, message: '请输入类型', trigger: 'change' }
				],
				name: [
					{ required: true, message: '请输入名称', trigger: 'blur' },
					{ max: 100, message: '长度不能超过100个字符', trigger: 'blur' }
				],
				lang: [
					{ required: true, message: '请至少选择一种语言', trigger: 'change' }
				],
				place: [
					{ required: true, message: '请至少选择一个应用端口', trigger: 'change' }
				],
				url_name: [
					{ required: true, message: '请输入网址', trigger: 'blur' },
					{ max: 64, min: 3, message: '长度在3-64个字符之间', trigger: 'blur' }
				],
				// description: [
				// 	{ required: true, message: '请输入简介', trigger: 'blur' }
				// ],
				// range_time: [
				// 	{ required: true, message: '请输入时间', trigger: 'blur' }
				// ]
			},
			dialogActivityVisible: false,
			currentPageRow: {},
			pageForm: {
				id: '',
				title: '',
				goods_sn: '',
				page_id: '',
				page_url: '',
				// page_url_view: '',
				statistics_code: '',
				copyStatus: 1,
				tpl_id: '0',
				m_tpl_id: '0',
				tpl_name: '',
				m_tpl_name: '',
				pc_status: false,
				m_status: false,
				data: {},
				refresh_time: 0,
				dialogTitle: '子页面新增',
			},
			dialogLinksVisible: false,
			pageLinks: [],
			tips: '',
			urlID: '',
			// 公共表单page rules
			publicPageRules: {
				place: [
					{ required: true, message: '请至少选择一个应用端口', trigger: 'change' }
				],
			},
			pageRules: {
				title: [
					{ required: true, message: '请输入名称', trigger: 'blur' },
					{ max: 100, min: 1, message: '长度在100个字符以内', trigger: 'blur' }
				],
				goods_sn: [
					{ required: true, message: '请输入主商品sku', trigger: 'blur' }
				]
			},
			pickerOptions1: {
				disabledDate(time) {
					let currentDate = new Date(),
						year = currentDate.getFullYear(),
						month = currentDate.getMonth() + 6,
						day = currentDate.getDate(),
						hours = currentDate.getHours(),
						min = currentDate.getMinutes(),
						second = currentDate.getSeconds()
					if (month > 12) {
						month = month - 12
						year += 1
					}
					let lastDateTime = new Date(year + '-' + month + '-' + day + ' ' + hours + ':' + min + ':' + second).getTime()
					return (time.getTime() > lastDateTime) || (time.getTime() < currentDate.getTime() - 86400)
				}
			},
			dialogPageVisible: false,
			currentLanguage: '',//en
			currentSiteUrl: '',
			langList: [],
			tplId: '',
			urlName: '',
			end_time: '',
			refreshTime: 0,
			siteInfo: '',
			sitePlat: '',
			convertForm: {
				id: '',
				activity_id: '',
				page_id: '',
				model: '1',
				is_group: 0,
				source_id: 0,				
				target_id: 0,
				lang: ''
			},
			convertRules: {
				activity_id: [
					{ required: true, message: '请选择活动', trigger: 'change' }
				],
				page_id: [
					{ required: true, message: '请选择页面', trigger: 'change' }
				]
			},
			dialogConvertAPP: false,
			convertForm_M: {
				id: '',
				activity_id: '',
				page_id: '',
				model: '1',
				is_group: 0,
				source_id: 0,
				target_id: 0,
				lang: ''
			},
			convertRules_M: {
				activity_id: [
					{ required: true, message: '请选择活动', trigger: 'change' }
				],
				page_id: [
					{ required: true, message: '请选择页面', trigger: 'change' }
				]
			},
			dialogConvertM: false,
			appActivityList: [],
			appActivities: [],
			appPages: [],
			mActivityList: [],
			mActivities: [],
			mPages: [],
			activityDefaultTime: ['00:00:00', '23:59:59'],
			modelInfo: {
				visible: false,
				tabActive: '2',
				modelSelect: '0',
				tempLength1: 0,
				tempLength2: 0
			},
			/* 模板提示 */
			pageTemplateListWarn: '当前没有可用模板',
			dialogConvertVisible: false,
			dialogConvertMVisible: false,
			convertUrl: '',
			convertMUrl: '',
			// currentTemplate: '未选中模板',
			tplInfo: {
				pageNo: 1,
				pageSize: 100,
				loading: false
			},
			viewModel: {
				visible: false,
				html: '',
				sideType: 'pc',
				sideWidth: '100%',
				src: ''
			},
			pageLoading: false,
			actNameCount: 0,
			actIntroductionCount: 0,
			titleCount: 0,
			urlCount: 0,
			codeCount: 0,
			pageIntroductionCount: 0,
			convertLangs: []
		}
	},
	computed: {
		firstLanguage() {
			if (this.langList.length > 0) {
				var firstLangu = this.langList[0].key
				return firstLangu
			} else {
				return ''
			}
		}
	},
	watch: {
		currentLanguage: function (val) {
			let supportLangs = this.supportLangs,
				currentLanguage = this.currentLanguage
			supportLangs.forEach((item, index) => {
				if (item.key == currentLanguage) {
					this.currentSiteUrl = item.url
				}
			})
			this.currentPlat = val.toLowerCase()
		}
	},
	mounted() {

	},
	methods: {
		async getPageTemplates(scrollType) {
			this.tplInfo.loading = true
			let _this = this
			let pageNo = scrollType == 'scroll' ? this.tplInfo.pageNo : 1
			let type = this.modelInfo.tabActive == '1' ? 1 : 0

			let params = {
				place: 3, // place为3对应活动推广管理模块页面模板
				type: type,
				pageNo: pageNo,
				pageSize: this.tplInfo.pageSize
			}

			// 如果是选择模板
			if (this.getTmpListStatus) {
				params.site_code = getCookie('site_group_code') + '-' + this.getTmpListValue
			} else {
				params.site_code = getCookie('site_group_code') + '-' + this.activityTabName
			}

			let res = await getPageTemplateList(params)

			let data = res.data.list
			this.tplInfo.totalCount = res.data.totalCount
			this.tplInfo.maxPageNo = Math.ceil(res.data.totalCount / this.tplInfo.pageSize)
			if (scrollType == 'scroll' && pageNo > 1) {
				let oldList = this.pageTemplateList
				this.pageTemplateList = oldList.concat(data)
			} else {
				this.pageTemplateList = data
			}

			this.checkCurrentPageForm()

			setTimeout(function () {
				_this.tplInfo.loading = false
			}, 200)
		},

		/**
		 * 获取广告列表
		 */
		async getAdvertisements() {
			let params = {
					pageNo: this.currentPage,
					pageSize: 10,
					name: this.searchWord,
					create_name: this.createName,
					type: this.activityType,
					site_code: getCookie('site_group_code') + '-' + this.activityTabName
				},
				res = await getAdvertisementList(params)

			this.activityList = res.data.list
			var array = []
			for (var index in this.activityList) {
				array.push(this.activityList[index].id)
			}
			this.pageIds = array
			this.total = res.data.pagination.totalCount

			let length = res.data.list.length

			for (index = 0; index < length; index++) {
				if (res.data.list[index].is_lock == 0) {
					res.data.list[index].lock_status = false
				} else if (res.data.list[index].is_lock == 1) {
					res.data.list[index].lock_status = true
				}
			}
		},
		
		async getAppActivityList() {
			let res = await getAppActivityList({})

			if (res.code == 0) {
				this.appActivityList = res.data

				this.getAppActivities()
				this.getAppPages()
			}
		},
		//获取当前用户在当前站点wap端下的所有活动及页面列表
		async getMActivityList() {
			let res = await getMActivityList()

			if (res.code == 0) {
				this.mActivityList = res.data

				this.getMActivities()
				this.getMPages()
			}
		},
		// PC，M，APP切换
		handleActivityTabClick(event) {
			this.activityTabName = event.name
			this.currentPage = 1
			this.getAdvertisements()
		},
		handlePCPlaceActivity(val) {
			this.pageForm.pc_activity_id = val
			this.pageForm.data[this.currentLanguage].pc_activity_id = val

			let pc_activity_title
			this.pageForm.pc_activity_list.forEach((item) => {
				if (item.id == val) {
					pc_activity_title = item.name
				}
			})
			this.pageForm.pc_activity_title = pc_activity_title
			this.pageForm.data[this.currentLanguage].pc_activity_title = pc_activity_title
		},
		handleMPlaceActivity(val) {

			this.pageForm.m_activity_id = val
			this.pageForm.data[this.currentLanguage].m_activity_id = val

			let m_activity_title
			this.pageForm.m_activity_list.forEach((item) => {
				if (item.id == val) {
					m_activity_title = item.name
				}
			})
			this.m_activity_title = m_activity_title

			this.pageForm.m_activity_title = m_activity_title
			this.pageForm.data[this.currentLanguage].m_activity_title = m_activity_title
		},
		handleAppPlaceActivity(val) {

			this.pageForm.app_activity_id = val
			this.pageForm.data[this.currentLanguage].app_activity_id = val

			let app_activity_title
			this.pageForm.app_activity_list.forEach((item) => {
				if (item.id == val) {
					app_activity_title = item.name
				}
			})
			this.app_activity_title = app_activity_title

			this.pageForm.app_activity_title = app_activity_title
			this.pageForm.data[this.currentLanguage].app_activity_title = app_activity_title
		},

		handleNameKeyup() {
			var data = this.activityForm.name.split('')
			var length = data.length

			if (length > 100) {
				data.splice(100, length - 99)
				this.activityForm.name = data.join('')
			}

			this.actNameCount = data.length
		},
		handleIntroductionKeyup() {
			var data = this.activityForm.description.split('')
			var length = data.length

			if (length > 200) {
				data.splice(200, length - 199)
				this.activityForm.description = data.join('')
			}

			this.actIntroductionCount = data.length
		},
		// 新增子页面
		handleTitleKeyup() {
			var data = this.pageForm.title.split('')
			var length = data.length

			if (length > 100) {
				data.splice(100, length - 99)
				this.pageForm.title = data.join('')
			}

			this.titleCount = data.length
		},
		handleUrlKeyup() {
			var data = this.pageForm.url_name.split('')
			var length = data.length

			if (length > 64) {
				data.splice(64, length - 63)
				this.pageForm.url_name = data.join('')
			}

			this.urlCount = data.length
		},
		handleCodeKeyup() {
			var data = this.pageForm.statistics_code.split('')
			var length = data.length

			if (length > 500) {
				data.splice(499, length - 499)
				this.pageForm.statistics_code = data.join('')
			}

			this.codeCount = data.length
		},
		handleDescriptionKeyup() {
			var data = this.pageForm.description.split('')
			var length = data.length

			if (length > 200) {
				data.splice(200, length - 199)
				this.pageForm.description = data.join('')
			}

			this.pageIntroductionCount = data.length
		},
		createActivity() {
			// 当前为新增活动状态
			this.activityForm.status = 1
			this.activityForm.id = ''
			this.activityForm.type = ''
			this.activityForm.lang = ['en']
			this.activityForm.start_time = ''
			this.activityForm.end_time = ''
			this.activityForm.name = ''
			this.activityForm.description = ''
			this.activityForm.range_time = ['', '']
			this.actNameCount = 0
			this.actIntroductionCount = 0
			this.activityForm.dialogTitle = '新增活动'
			this.activityForm.miss_count = 1
			this.activityForm.miss_count_status = 1

			this.activityForm.site_code = ''
			this.activityForm.place = ['pc'] // 新增活动默认选中pc

			let date = new Date()
			this.activityDefaultTime[0] = date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds()

			this.dialogActivityVisible = true
		},
		async refreshHeadFoot() {
			// if (this.siteInfo.isSuper != 1) {
			// this.$message('该操作只有超级管理员才有权限!')
			// } else {
			this.confirm('一键头尾刷新中，请在【系统日志】-任务日志里对刷新详情进行查看，是否前往查看？', async (vm) => {
				let res = await refreshSite({
					// site_code: getCookie('SITECODE')
					site_code: getCookie('site_group_code') + '-' + this.activityTabName
				})

				vm.isDetailActive = false

				if (res.code === 0) {
					window.location.href = '/base/task-log/index'
				} else {
					vm.$message({
						// type: 'warning',
						message: res.message
					})
				}
			})
			// }
		},
		editActivity(row, is_lock, create_user) {
			if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				// 当前为编辑活动状态
				this.activityForm.status = 2
				this.activityForm.id = row.id
				this.activityForm.type = String(row.type)
				// this.activityForm.lang = String(row.lang).split(',')
				// this.activityForm.start_time = row.start_time * 1000
				// this.activityForm.end_time = row.end_time * 1000
				this.activityForm.name = row.name

				this.activityForm.site_code = row.site_code.split('-')[1]
				this.activityForm.place = Array(row.site_code.split('-')[1])

				// 新增活动时已选应用端口列表
				this.editPlaces = row.group_info.platform_list
				this.editSupportLangs = row.group_info.lang_list

				// 主活动已勾选端口
				let editPlaceArr = []
				row.group_info.platform_list.forEach((item) => {
					editPlaceArr.push(item.code)
				})
				this.activityForm.editPlace = editPlaceArr

				// 主活动已勾选语言
				let editSupportLangArr = []
				row.group_info.lang_list.forEach((item) => {
					editSupportLangArr.push(item.key)
				})
				this.activityForm.editSupportLang = editSupportLangArr

				this.activityForm.description = row.description
				// this.activityForm.range_time = [this.activityForm.start_time, this.activityForm.end_time]
				this.activityForm.refresh_time = row.refresh_time
				this.activityForm.dialogTitle = '编辑活动'
				this.actNameCount = this.activityForm.name.split('').length
				this.actIntroductionCount = this.activityForm.description.split('').length
				this.dialogActivityVisible = true
			}
		},

		/**
		 * 新增子页面
		 */
		createPage(activityId, langList, placeList, is_lock, create_user) {

			if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {

				this.currentPageRow = {}

				// 显示活动语言
				this.langList = langList
				
				// 新增活动时勾选的端口
				this.pagePlaces = placeList

				this.currentLanguage = this.langList[0].key //'en'
				this.tplId = '0'
				this.refreshTime = 0
				this.pageForm.id = ''
				this.pageForm.activity_id = activityId
				this.pageForm.title = ''
				this.pageForm.goods_sn = ''
				this.pageForm.page_id = ''
				this.pageForm.page_url = ''
				// this.pageForm.page_url_view = ''
				this.pageForm.copyStatus = 1
				this.titleCount = 0
				this.pageForm.statistics_code = ''
				this.codeCount = 0
				this.pageForm.refresh_time = 0
				this.pageForm.tpl_id = '0' // 每个语种都能选择自己的模板
				this.pageForm.m_tpl_id = '0' // 每个语种都能选择自己的模板
				this.pageForm.tpl_name = '未选中模板'
				this.pageForm.m_tpl_name = '未选中模板'
				this.skuStatus = true

				this.supportLangs.forEach((item, index) => {
					if (item.key == this.currentLanguage) {
						this.currentSiteUrl = item.url
					}
				})

				let data = {}
				let _this = this

				this.langList.forEach(function (element) {
					data[element.key] = {
						title: '',	
						goods_sn: '',	
						page_id: '',	
						page_url: '',	
						// page_url_view: '',	
						statistics_code: '',
						tpl_id: '0', // 模板id
						m_tpl_id: '0',
						tpl_name: '未选中模板', // 模板名称
						m_tpl_name: '未选中模板',
						activity_id: activityId,
					}
				})
				this.pageForm.data = data

				// 根据当前活动已勾选端口显示对应“跳转链接”和“页面模板”
				let pagePlacesArr = []
				placeList.forEach((item) => {
					pagePlacesArr.push(item.code)
				})
				
				this.publicPageForm.place = pagePlacesArr
				
				if (pagePlacesArr.indexOf('pc') != -1) {
					this.pageForm.pc_status = true
				} else {
					this.pageForm.pc_status = false
				}
				if (pagePlacesArr.indexOf('wap') != -1) {
					this.pageForm.m_status = true
				} else {
					this.pageForm.m_status = false
				}

				this.currentTemplate = '未选中模板'

				this.pageForm.dialogTitle = '新增子页面'
				this.dialogPageVisible = true
			}
		},

		/**
		 * 编辑子页面
		 */
		editPage(row, is_lock, activity_create_user, langList, placeList) {

			let _this = this
			if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				this.currentLanguage = this.langList[0].key
				this.tplId = ''

				let lang = this.currentLanguage
				this.currentPageRow = row

				// 主活动语言
				this.langList = langList

				// 当前子页面支持语言
				// let langList = this.langList

				// 主活动端口
				this.pagePlaces = placeList

				this.pageForm.copyStatus = 1
				this.skuStatus = true

				let data = {}
				langList.forEach(function (element) {
					data[element.key] = {
						title: '',
						goods_sn: '',
						page_id: '',
						page_url: '',
						// page_url_view: '',
						statistics_code: ''
					}
				})

				// k - pc, wap
				// key - en, es, ...
				let group_languages = this.currentPageRow.group_languages
				for(let k in group_languages) {
					for(let key in group_languages[k]) {
						data[key].title = group_languages[k][key].title
						data[key].goods_sn = group_languages[k][key].goods_sn
						data[key].page_id = this.currentPageRow.id * 1
						data[key].page_url = group_languages[k][key].page_url
						// data[key].page_url_view = group_languages[k][key].page_url_view
						data[key].statistics_code = group_languages[k][key].statistics_code
					}
				}

				this.pageForm.id = this.currentPageRow.id

				this.pageForm.data = data

				// 根据当前活动已勾选端口显示对应“跳转链接”和“页面模板”
				let pagePlacesArr = []
				placeList.forEach((item) => {
					pagePlacesArr.push(item.code)
				})
				this.publicPageForm.place = pagePlacesArr
				
				if (pagePlacesArr.indexOf('pc') != -1) {
					this.pageForm.pc_status = true
				} else {
					this.pageForm.pc_status = false
				}
				if (pagePlacesArr.indexOf('wap') != -1) {
					this.pageForm.m_status = true
				} else {
					this.pageForm.m_status = false
				}

				this.pageForm.title = this.pageForm.data[lang].title
				this.titleCount = this.pageForm.data[lang].title.split('').length
				this.pageForm.goods_sn = this.pageForm.data[lang].goods_sn
				this.pageForm.page_id = this.pageForm.data[lang].page_id
				this.pageForm.page_url = this.pageForm.data[lang].page_url
				
				this.goods_sn_diff = this.pageForm.data[lang].goods_sn

				this.pageForm.statistics_code = this.pageForm.data[lang].statistics_code
				this.codeCount = this.pageForm.data[lang].statistics_code.split('').length

				this.pageForm.activity_id = this.currentPageRow.activity_id
				this.pageForm.refresh_time = this.currentPageRow.refresh_time
			
				this.refreshTime = this.currentPageRow.refresh_time
				this.tplId = this.currentPageRow.tplId

				this.pageForm.dialogTitle = '编辑子页面'
				this.dialogPageVisible = true
			}
		},
		async getPages(activityId) {
			let params = {
					activity_id: activityId
				},
				// res = await getPageList(params)
				res = await getAdvertisementPages(params)

			let position, data, pages, isEnglishSetted = false

			pages = res.data.list
			let firstLang = res.data.langList[0].key

			pages.forEach(function (element) {

				element.pageLanguages.forEach(function (item) {
					// if (item.lang === 'en') {
					if (item.lang === firstLang) {
						element.title = item.title
						isEnglishSetted = true
					}
				})

				if (!isEnglishSetted) {
					element.title = element.pageLanguages[0].title
				}
			})

			this.activityList.forEach(function (element, index) {
				if (element.id == activityId) {
					element.children = pages
					data = element
					position = index
				}
			})

			this.$set(this.activityList, position, data)
		},
		viewDetail(row) {
			this.currentPageRow = {}
			this.currentActivityRow = row
			this.isDetailActive = true
			this.langList = row.langList
		},
		handleExpandChange(row) {
			if (!row.children) {
				this.getPages(row.id)
			}

			this.langList = row.langList

			if (this.expandRowKeys.indexOf(row.id) === -1) {
				this.expandRowKeys = [row.id]
			} else {
				this.expandRowKeys = []
			}
		},
		handleCurrentChange(currentPage) {
			this.currentPage = currentPage
			this.getAdvertisements()
		},
		/**
		 * 删除数组指定元素
		 */
		handleSpliceArrayItem(arr, key) {
			let arrays = arr
			let index = arrays.indexOf(key)
			if (index > -1) {
				arrays = arrays.splice(index, 1)
			}
			return arrays
		},
		/**
		 * 表单提交
		 */
		async submitForm(formName) {
			if (formName == 'activityForm') {
				this.setActivityTime()

				if (this.activityForm.start_time == '' || this.activityForm.end_time == '') {
					this.activityForm.range_time = ''
				}
			}

			this.submitLoading = true

			this.$refs[formName].validate(async (valid) => {
				if (valid) {
					let params, res
					let activityFormObj = {}
					let platformData = {}

					if (formName == 'activityForm') {
						params = {
							type: this.activityForm.type,
							name: this.activityForm.name,
							description: this.activityForm.description,
							start_time: this.formatTimestamp(this.activityForm.start_time),
							end_time: this.formatTimestamp(this.activityForm.end_time)
						}

						// 新增活动组装数据
						this.activityForm.place.forEach((item) => {
							activityFormObj[item] = {
								type: this.activityForm.type,
								lang: this.activityForm.lang.join(','),
								name: this.activityForm.name,
								description: this.activityForm.description,
								start_time: this.formatTimestamp(this.activityForm.start_time),
								end_time: this.formatTimestamp(this.activityForm.end_time),
								site_code: getCookie('site_group_code') + '-' + item
							}
						})

						platformData['platform_list'] = JSON.stringify(activityFormObj)
						
						// 保存分“新增”和“编辑”两种逻辑
						if (this.activityForm.id == '') {
							// 首次提交code返回101时
							if (this.activityForm.miss_count_status > 1) {
								platformData['miss_count'] = ++this.activityForm.miss_count
								res = await addAdvertisement(platformData)
							} else {
								res = await addAdvertisement(platformData)
							}
						} else {
							params.id = this.activityForm.id
							res = await editAdvertisement(params)
						}

						if (res.code == 0) {
							this.getAdvertisements()
						} 
						// code - 101 端口没有相关语言仍要提交状态
						else if (res.code == 101) {
							this.activityForm.miss_count_status = 2
							this.activityForm.miss_count = res.data.miss_count
						}
						
						if (params.id) {
							this.expandRowKeys = []
						}
					} else if (formName == 'pageForm') {

						// 如果没有生成推广链接则禁止提交
						if (this.pageForm.page_url == '' || !this.skuStatus) {
							this.$message.error('请填写正确的商品sku')
							this.submitLoading = false
							return false
						}	

						let activityFormObj = {}
						let jsonObject = {}

						// 遍历当前语言列表
						this.langList.forEach((item) => {
							let param = {}
							let key = item['key'] // { key: 'en', name: '英文' }
							let formData = this.pageForm.data[key]

							// 遍历当前端口 - 需要过滤掉“页面模板”之外的所有字段
							this.pagePlaces.forEach((it) => {
								let k = it['code'] // { code: 'pc', name: 'PC' }
								param[k] = JSON.parse(JSON.stringify(formData))
								
								// 删除无用字段
								delete param[k].title 
								delete param[k].goods_sn 
								delete param[k].page_url 
								delete param[k].page_id 
								delete param[k].statistics_code 
								delete param[k].activity_id
								
								// 根据当前端口是pc，m或app保留对应字段值
								if (k == 'wap') {
									param[k].tpl_id = param[k].m_tpl_id
									param[k].tpl_name = param[k].m_tpl_name
								}

								// 重命名后删除字段
								delete param[k].m_tpl_id
								delete param[k].m_tpl_name
							})

							activityFormObj[key] = {
								title: this.pageForm.data[key].title,
								goods_sn: this.pageForm.data[key].goods_sn,
								// page_id: this.pageForm.data[key].page_id ? this.pageForm.data[key].page_id : 0,
								page_url: this.pageForm.data[key].page_url,
								statistics_code: this.pageForm.data[key].statistics_code,
								platform: param
							}
						})

						let postData = {}

						for (let key in activityFormObj) {
							postData[key] = activityFormObj[key]
						}
						jsonObject['lang_list'] = JSON.stringify(postData)

						// 编辑
						if (this.pageForm.id != '') {
							
							// 编辑子页面传page_id
							jsonObject['page_id'] = this.pageForm.id
							// res = await batchEditPage(jsonObject)
							res = await editAdvertisementPage(jsonObject)
						}
						// 新增
						else {
							let flag = true
							for (let item in this.pageForm.data) {
								if (this.pageForm.data[item].title == '') {
									this.$message.error('请确认所有语言所有必填项已经填写，再提交！')
									if (flag) {
										flag = false
									}
								}
							}
							if (!flag) {
								this.submitLoading = false
								return false
							}
							// 新增子页面传activity_id
							jsonObject['activity_id'] = this.pageForm.activity_id
							res = await addAdvertisementPage(jsonObject)
						}

						if (res.code == 0) {
							this.getPages(this.pageForm.activity_id)
						}
					} else if (formName == 'convertForm') {
						params = {
							// source_id: this.convertForm.id,
							// target_id: this.convertForm.page_id,
							model: this.convertForm.model
						}
						
						let source_id = this.convertForm.source_id
						let target_id = this.convertForm.target_id
						if (source_id !=0 && target_id !=0) {
							params.source_id = source_id
							params.target_id = target_id
						} else {
							params.source_id = this.convertForm.id
							params.target_id = this.convertForm.page_id
						}

						params.lang = this.convertForm.lang
						res = await convertToAppPage(params)

						if (res.code == 0) {
							this.convertUrl = res.data.redirectUrl
							this.dialogConvertVisible = true
						} else {
							this.convertUrl = ''
						}
					} else {
						
						params = {
							// source_id: this.convertForm_M.id,
							// target_id: this.convertForm_M.page_id,
							model: this.convertForm_M.model
						}

						let source_id = this.convertForm_M.source_id
						let target_id = this.convertForm_M.target_id
						if (source_id !=0 && target_id !=0) {
							params.source_id = source_id
							params.target_id = target_id
						} else {
							params.source_id = this.convertForm_M.id
							params.target_id = this.convertForm_M.page_id
						}
						params.lang = this.convertForm_M.lang

						// res = await convertToMPage(params)
						res = await advertisementConvertToMPage(params)

						if (res.code == 0) {
							this.convertMUrl = res.data.redirectUrl
							this.siteCode = res.data.siteCode

							// site_group_code显示是站点名称，三端合并版本PC、M、APP转换不需要传site code
							// setCookie('site_group_code', this.siteCode)
							// setCookie('SITECODE', this.siteCode)

							this.dialogConvertMVisible = true
						} else {
							this.convertMUrl = ''
						}
					}

					this.isDetailActive = false

					if (res.code == 0) {
						this.resetFields(formName)
						this.closeDialog(formName)

					} else {
						this.submitLoading = false
					}
				} else {
					this.submitLoading = false
				}
			})
		},
		resetForm(formName) {
			this.resetFields(formName)
			this.closeDialog(formName)
		},
		resetFields(formName) {
			this.$refs[formName].resetFields()
		},
		closeDialog(formName) {
			if (formName == 'activityForm') {
				this.dialogActivityVisible = false
			} else if (formName == 'pageForm') {
				this.dialogPageVisible = false
			} else if (formName == 'convertForm') {
				this.dialogConvertAPP = false
			} else {
				this.dialogConvertM = false
			}
			this.submitLoading = false
		},
		async verifyActivity(id, status, is_lock, create_user) {
			if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				var tip = ''

				if (status == 2) {
					tip = '确认上线该活动吗？'
				} else {
					tip = '确认下线该活动？下线后，该活动的所有页面将下线'
				}

				this.confirm(tip, async (vm) => {
					let params = {
						id: id,
						status: status
					}
					
					// await verifyActivity(params)
					await advertisementVerifyActivity(params)
					vm.isDetailActive = false
					vm.getAdvertisements()
					vm.expandRowKeys = []
				})
			}
		},
		/**
		 * 删除推广活动
		 */
		async removeActivity(id, is_lock, create_user) {
			if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				this.confirm('活动删除后，其所有的子页面也将被删除，确认删除？', async (vm) => {
					let params = {
							id: id
						},
						// res = await deleteActivity(params)
						res = await deleteAdvertisement(params)

					vm.isDetailActive = false
					vm.getAdvertisements()

					if (res.code === 0) {
						vm.$message({
							type: 'success',
							message: res.message
						})
					} else {
						vm.$message({
							type: 'error',
							message: res.message
						})
					}
				})
			}
		},
		removePage(row, is_lock, activity_create_user) {
			if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				this.confirm('确认删除该页面?', async (vm) => {
					let params = {
							id: row.id
						},
						res = await deletePage(params)
					
					vm.getPages(row.activity_id)
					if (res.code === 0) {
						vm.$message({
							type: 'success',
							message: res.message
						})
					} else {
						vm.$message({
							type: 'error',
							message: res.message
						})
					}
				})
			}
		},
		confirm(message, callback) {
			this.$confirm(message, '提示', {
				confirmButtonText: '确定',
				cancelButtonText: '取消',
				type: 'warning'
			}).then(() => {
				if (typeof callback == 'function') {
					callback(this)
				}
			}).catch(() => {
				this.$message({
					type: 'info',
					message: '已取消操作!'
				})
			})
		},
		formatTimestamp(timestamp) {
			return parseInt(timestamp / 1000)
		},
		// viewPages (urls) {
		// 	this.pageLinks = urls
		// 	this.dialogLinksVisible = true
		// },
		async viewPages(id) {
			let params = {
					id: id
				},
				// res = await getAccessLink(params)
				res = await advertisementGetAccessLink(params)
			if (res.code == 0) {
				this.dialogLinksVisible = true
				this.pageLinks = res.data.list
				this.tips = res.data.tips
				this.urlID = id
			}
		},
		// 重新发布
		async redistribution() {
			let params = {
					page_id: this.urlID
				},
				// res = await actReleased(params)
				res = await adActReleased(params)
			this.dialogLinksVisible = false
		},
		doSearch() {
			this.currentPage = 1
			this.getAdvertisements()
		},
		redirect(url) {
			window.open(url)
		},
		decorate_redirect(url, is_lock, activity_create_user) {
			if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				window.open(url)
			}
		},
		/**
		 * 三端合并后，PC转M端，M转APP端不再需要传siteCode值
		 */
		convertRedirect() {
			window.location.href = this.convertUrl
		},
		//PC转M端 进入装修页面
		convertMRedirect () {
			window.location.href = this.convertMUrl
		},
		handleTabClick() {

			this.resetFields('pageForm')

			let lang = this.currentLanguage
			this.prevLanguage = this.currentLanguage

			this.pageForm.title = this.pageForm.data[lang].title
			this.titleCount = this.pageForm.data[lang].title.split('').length

			this.pageForm.goods_sn = this.pageForm.data[lang].goods_sn
			this.pageForm.copyStatus = 1
			// this.pageForm.page_id = this.pageForm.data[lang].page_id
			this.pageForm.page_id = this.pageForm.id
			this.pageForm.page_url = this.pageForm.data[lang].page_url
			// this.pageForm.page_url_view = this.pageForm.data[lang].page_url_view

			this.pageForm.statistics_code = this.pageForm.data[lang].statistics_code
			this.codeCount = this.pageForm.data[lang].statistics_code.split('').length
			

			//判断当前是否有英语（切换同步）
			if (this.langList[0].key == 'en') {
				//当前语言不是英语
				if (lang != 'en') {
					//判断当前语言是否为空
					if (this.pageForm.data[lang].goods_sn == '') {
						this.pageForm.goods_sn = this.pageForm.data.en.goods_sn
						this.pageForm.data[lang].goods_sn = this.pageForm.data.en.goods_sn
						this.pageForm.page_url = this.pageForm.data.en.page_url
						this.pageForm.data[lang].page_url = this.pageForm.data.en.page_url
					} else {
						this.pageForm.goods_sn = this.pageForm.data[lang].goods_sn//不同语言goods_sn
						this.pageForm.page_url = this.pageForm.data[lang].page_url//不同语言goods_sn
					}
				} else {
					this.pageForm.goods_sn = this.pageForm.data[lang].goods_sn//不同语言goods_sn
					this.pageForm.page_url = this.pageForm.data[lang].page_url//不同语言goods_sn
				}

			} else {
				this.pageForm.goods_sn = this.pageForm.data[lang].goods_sn//不同语言goods_sn
				this.pageForm.page_url = this.pageForm.data[lang].page_url//不同语言goods_sn
			}


			this.pageForm.refresh_time = this.refreshTime
			
			this.pageForm.tpl_id = this.pageForm.data[lang].tpl_id
			this.pageForm.tpl_name = this.pageForm.data[lang].tpl_name
			this.pageForm.m_tpl_id = this.pageForm.data[lang].m_tpl_id
			this.pageForm.m_tpl_name = this.pageForm.data[lang].m_tpl_name

			this.pageForm.id = this.currentPageRow.id ? this.currentPageRow.id : ''

			if (this.currentPageRow.activity_id) {
				this.pageForm.activity_id = this.currentPageRow.activity_id
			}
		},
		getRowKey(row) {
			return row.id
		},
		verifyPage(id, activityId, status, is_lock, activity_create_user) {
			if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				var tip = ''

				if (status == 4) {
					tip = '确认下线该页面？'
				} else if (status == 2) {
					tip = '确认上线该页面？'
				}

				this.confirm(tip, async (vm) => {
					let params = {
							id: id,
							status: status
						},
						// res = await verifyPage(params)
						res = await advertisementVerifyPage(params)

					if (res.code === 0) {
						vm.$message({
							message: res.message,
							type: 'success'
						})
						vm.getAdvertisements()
						vm.expandRowKeys = []
					} else {
						vm.$message({
							message: res.message,
							type: 'warning'
						})
					}
				})
			}
		},
		lockingActivity(id, is_lock, create_user, $event) {
			event.stopPropagation()
			if ((create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
				this.$message('只有活动创建者才具有此权限!')
			} else {
				var tip = ''
				if (is_lock == 1) {
					tip = '该活动解锁后，其他用户将拥有与您一样的操作权限，是否解锁？'
				} else if (is_lock == 0) {
					tip = '该活动加锁后，其他用户将不能操作此活动及其相关所有页面，是否加锁？'
				}

				this.$confirm(tip, '提示', {
					confirmButtonText: '是',
					cancelButtonText: '否',
					type: 'warning'
				}).then(async () => {
					let params = {
							id: id
						},
						res = await lockingActivity(params)

					if (res.code === 0) {
						this.getAdvertisements()
						this.$message({
							type: 'success',
							message: res.message
						})
					} else {
						this.$message({
							type: 'error',
							message: res.message
						})
					}
				}).catch(() => {
					this.activityList.forEach(function (element) {
						if (element.is_lock == 0) {
							element.lock_status = false
						} else if (element.is_lock == 1) {
							element.lock_status = true
						}
					})
				})
			}
		},
		updatePageTitle(value) {
			this.pageForm.data[this.currentLanguage].title = value
		},
		updatePagegoods_sn(value) {
			this.pageForm.data[this.currentLanguage].goods_sn = value
		},
		/**
		 * 主商品sku输入框keyup
		 */
		handleGoodsSkuKeyup() {
			this.skuStatus = false
			if (this.pageForm.goods_sn.length == 0) return false
			this.throttle(this.getGoodsSkuLink)
		},
		/**
		 * 节流函数
		 */
		throttle (method, context) {
			clearTimeout(method.tId)
			method.tId = setTimeout(() => {
				method.call(context)
			}, 300)
		},

		handleGoodsSkuLink () {
			if (this.pageForm.goods_sn.length == 0) return false
			this.getGoodsSkuLink()
		},
		/**
		 * 生成page_url
		 * @param goods_sn - 产品编码
		 */
		async getGoodsSkuLink () {
			let param = {
					goods_sn: this.pageForm.goods_sn,
					page_id: this.pageForm.id ? this.pageForm.id * 1 : 0,
					lang: this.currentLanguage,
					platform: `${getCookie('site_group_code')}-${this.activityTabName}`
				},
				res = await getAdGoodsUrl(param)
			if (res.code == 0) {
				this.$message({
					type: 'success',
					duration: 3000,
					message: '已生成推广链接，可点击复制按钮复制链接'
				})
				this.pageForm.data[this.currentLanguage].page_url = res.data.url
				this.pageForm.page_url = res.data.url
				// this.pageForm.page_url_view = this.pageForm.data[this.currentLanguage].page_url
				this.skuStatus = true
			} else {
				this.pageForm.data[this.currentLanguage].page_url = ''
				this.pageForm.page_url = this.pageForm.data[this.currentLanguage].page_url
				this.skuStatus = false
			}
		},

		/**
		 * 复制sku
		 */
		handleCopySkuLink () {
			const input = document.createElement('input')
			let value = this.pageForm.data[this.currentLanguage].page_url
			// if (value.length == 0) {
			// 	this.$message({
			// 		type: 'warning',
			// 		message: '没有可复制推广链接'
			// 	})
			// 	return false
			// }
			input.setAttribute('readonly', 'readonly')
			input.setAttribute('value', value)
			document.body.appendChild(input)
			input.setSelectionRange(0, 9999)
			input.select()
			if (document.execCommand('copy')) {
				document.execCommand('copy')
			}
			document.body.removeChild(input)
			this.pageForm.copyStatus = 2
		},

		updatePageDescription(value) {
			this.pageForm.data[this.currentLanguage].description = value
		},
		updatePageStatisticsCode(value) {
			this.pageForm.data[this.currentLanguage].statistics_code = value
		},
		updateRefreshTime(value) {
			this.pageForm.refresh_time = value
			this.refreshTime = this.pageForm.refresh_time
		},

		unique(arr, key) {
			var n = [arr[0]]
			for (var i = 1; i < arr.length; i++) {
				if (key === undefined) {
					if (n.indexOf(arr[i]) == -1) n.push(arr[i])
				} else {
					inner: {
						var has = false
						for (var j = 0; j < n.length; j++) {
							if (arr[i][key] == n[j][key]) {
								has = true
								break inner
							}
						}
					}
					if (!has) {
						n.push(arr[i])
					}
				}
			}
			return n
		},

		async getSupportLangs() {
			let res = await getLangKeyList()
			this.allSupportLangArrs = res.data
			
			let supportLangArrs = []
			for (var key in res.data) {
				res.data[key].forEach(item => {
					// delete item.url
					supportLangArrs.push(item)
				})
			}
			supportLangArrs = JSON.parse(JSON.stringify(supportLangArrs))
			supportLangArrs = this.unique(supportLangArrs, 'name')

			this.supportLangs = supportLangArrs

			this.currentLanguage = this.supportLangs[0].key//设置第一种语言

			supportLangArrs.forEach((item, index) => {
				// if (item.key == 'en') {
				if (item.key == this.currentLanguage) {

					this.currentSiteUrl = item.url
				}
			})
		},
		publicReady() {
			this.getAdvertisements()
			this.getSupportLangs()
			this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data

			bus.$on('giveData', data => {
				this.siteInfo = data
			})
			this.sitePlat = this.siteInfo.site.split('-')[1]
			// 设置当前站点信息，活动推广管理只需要PC，M端
			let sites = JSON.parse(localStorage.currentSites).sites
			delete sites.app
			this.places = sites
		},
		setActivityTime() {
			let nowDate = new Date()
			let now = nowDate.getTime() - nowDate.getHours() * 3600000 - nowDate.getMinutes() * 60000 - nowDate.getMinutes() * 1000

			// if (this.activityForm.range_time[0] < now) {
			// 	this.$message.error('开始时间不能小于当前时间')

			// 	this.activityForm.range_time = ['', '']
			// } else if (new Date(this.activityForm.range_time[1]).getDate() <= nowDate.getDate() &&
			// 	new Date(this.activityForm.range_time[1]).getHours() <= nowDate.getHours()) {
			// 	this.$message.error('结束时间不能小于当前时间')

			// 	this.activityForm.range_time = ['', '']
			// } else if (new Date(this.activityForm.range_time[1]).getDate() == new Date(this.activityForm.range_time[0]).getDate() &&
			// 	new Date(this.activityForm.range_time[1]).getHours() <= new Date(this.activityForm.range_time[0]).getHours()) {
			// 	this.$message.error('结束时间不能小于开始时间')

			// 	this.activityForm.range_time = ['', '']
			// }
			if (new Date(this.activityForm.range_time[1]).getDate() <= nowDate.getDate() &&
				new Date(this.activityForm.range_time[1]).getHours() <= nowDate.getHours()) {
				this.$message.error('结束时间不能小于当前时间')

				this.activityForm.range_time = ['', '']
			} else if (new Date(this.activityForm.range_time[1]).getDate() == new Date(this.activityForm.range_time[0]).getDate() &&
				new Date(this.activityForm.range_time[1]).getHours() <= new Date(this.activityForm.range_time[0]).getHours()) {
				this.$message.error('结束时间不能小于开始时间')

				this.activityForm.range_time = ['', '']
			}

			this.activityForm.start_time = this.activityForm.range_time[0]
			this.activityForm.end_time = this.activityForm.range_time[1]
		},
		getAppActivities() {
			let list = []

			this.appActivityList.forEach(function (element) {
				list.push({
					id: element.id,
					name: element.name
				})
			})

			this.appActivities = list
		},
		getAppPages(val) {
			let list = []
			let languages = []

			this.convertForm.page = ''
			this.appActivityList.forEach(function (element) {
				if (element.id == val) {
					element.pageList.forEach(function (page, index) {
						list.push({
							id: page.id,
							title: page.title
						})
						if (index == 0) {
							page.langList.forEach(function (lang) {
								languages.push({
									key: lang.key,
									name: lang.name
								})
							})
						}
					})
				}
			})

			this.convertLangs = languages
			this.appPages = list
		},
		convertAPP(id, is_lock, activity_create_user, group_info, group_languages) {
			if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				this.convertForm.id = id
				this.convertForm.activity_id = ''
				this.convertForm.page_id = ''
				this.convertForm.model = '1'

				// 如果有app端关联，则不需要手动选择主活动和子页面
				if (group_info.platform_list.app) {
					let languages = []

					this.convertForm.is_group = 1
					this.convertForm.source_id = group_info.platform_list.wap.page_id
					this.convertForm.target_id = group_info.platform_list.app.page_id

					Object.keys(group_languages.app).forEach(function (key) {
						languages.push({
							key: group_languages.app[key].lang,
							name: group_languages.app[key].langName
						})
					})

					this.convertLangs = languages
				} else {
					this.convertForm.is_group = 0
					this.convertForm.source_id = 0
					this.convertForm.target_id = 0

					this.getAppActivityList()
				}

				this.dialogConvertAPP = true
			}
		},
		//一键转M
		getMActivities() {
			let list = []

			this.mActivityList.forEach(function (element) {
				list.push({
					id: element.id,
					name: element.name
				})
			})

			this.mActivities = list
		},
		getMPages(val) {
			let list = []
			let languages = []

			this.convertForm_M.page = ''
			this.mActivityList.forEach(function (element) {
				if (element.id == val) {
					element.pageList.forEach(function (page, index) {
						list.push({
							id: page.id,
							title: page.title
						})
						if (index == 0) {
							page.langList.forEach(function (lang) {
								languages.push({
									key: lang.key,
									name: lang.name
								})
							})
						}
					})
				}
			})

			this.convertLangs = languages
			this.mPages = list
		},
		convertM(id, is_lock, activity_create_user, group_info, group_languages) {
			if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
				this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
			} else {
				this.convertForm_M.id = id
				this.convertForm_M.activity_id = ''
				this.convertForm_M.page_id = ''
				this.convertForm_M.model = '1'

				// 如果有wap端关联，则不需要手动选择主活动和子页面
				if (group_info.platform_list.wap) {
					let languages = []

					this.convertForm_M.is_group = 1
					this.convertForm_M.source_id = group_info.platform_list.pc.page_id
					this.convertForm_M.target_id = group_info.platform_list.wap.page_id
					
					Object.keys(group_languages.wap).forEach(function (key) {
						languages.push({
							key: group_languages.wap[key].lang,
							name: group_languages.wap[key].langName
						})
					})

					this.convertLangs = languages
				} else {
					this.convertForm_M.is_group = 0
					this.convertForm_M.source_id = 0
					this.convertForm_M.target_id = 0

					this.getMActivityList()
				}

				this.dialogConvertM = true
			}
		},
		// 选中模板
		handleModelTempSelect(val) {

			// 新增子页面模板选择区分来自PC、M或APP
			this.templateSelectPlace = val

			// PC、M、APP页面模板数据获取
			if (this.templateSelectPlace == 'pc') {
				this.getTmpListValue = 'pc'
			} else if (this.templateSelectPlace == 'wap') {
				this.getTmpListValue = 'wap'
			}
			this.getTmpListStatus = true

			this.getPageTemplates()

			let _this = this
			this.modelInfo.visible = true
			this.modelInfo.modelSelect = this.pageForm.tpl_id

			setTimeout(function () {
				_this.handlePanelScroll()
			}, 100)

		},
		handleModelClose() {
			this.tplInfo.pageNo = 1
		},
		handleSureModel() {
			let currentPlace = this.templateSelectPlace

			let selected = this.modelInfo.modelSelect

			if (currentPlace == 'pc') {
				this.pageForm.tpl_id = selected
			} else if (currentPlace == 'wap') {
				this.pageForm.m_tpl_id = selected
			}

			this.modelInfo.visible = false

			this.pageForm.data[this.currentLanguage].tpl_id = this.pageForm.tpl_id
			this.pageForm.data[this.currentLanguage].m_tpl_id = this.pageForm.m_tpl_id

			let _this = this

			// let template = '无页面模板', m_template = '无页面模板'
			this.pageTemplateList.forEach(function (element) {
				if (element.id == selected) {
					if (currentPlace == 'pc') {
						_this.pageForm.tpl_name = element.name
					} else if (currentPlace == 'wap') {
						_this.pageForm.m_tpl_name = element.name
					}
				}
			})

			this.pageForm.data[this.currentLanguage].tpl_name = this.pageForm.tpl_name
			this.pageForm.data[this.currentLanguage].m_tpl_name = this.pageForm.m_tpl_name

		},
		handleCancelSelectedModel() {
			this.modelInfo.visible = false
		},
		subDialogClose() {
			this.resetForm('pageForm')
			this.pageForm.tpl_id = '0'
			this.modelInfo.tabActive = '2'
			this.end_time = ''
		},
		updateModelSelect() { },
		tplTabClick() {
			this.tplInfo.pageNo = 1
			let contContainer = document.getElementById('pane-2').parentNode
			contContainer.removeEventListener('scroll', this.handlePanelScroll)

			if (this.templateSelectPlace == 'pc') {
				this.getTmpListValue = 'pc'
			} else if (this.templateSelectPlace == 'wap') {
				this.getTmpListValue = 'wap'
			}
			this.getTmpListStatus = true

			this.getPageTemplates('scroll')
		},
		handlePanelScroll() {
			let panelCont0 = document.getElementById('pane-2').parentNode,
				_this = this
			// num = this.tplInfo.pageNo,
			// maxPageNo = this.tplInfo.maxPageNo
			let timer
			panelCont0.addEventListener('scroll', function () {
				if (timer) clearTimeout(timer)
				timer = setTimeout(function () {
					if (panelCont0.clientHeight + panelCont0.scrollTop == panelCont0.scrollHeight) {
						var tempNum = _this.tplInfo.pageNo + 1
						if (tempNum <= _this.tplInfo.maxPageNo) {
							_this.tplInfo.pageNo = tempNum


							if (_this.templateSelectPlace == 'pc') {
								_this.getTmpListValue = 'pc'
							} else if (_this.templateSelectPlace == 'wap') {
								_this.getTmpListValue = 'wap'
							}
							_this.getTmpListStatus = true

							_this.getPageTemplates('scroll')
						}
					}
				}, 600)
			})
		},
		// 查看模板大图
		async seeTemplate(pid, lang, id, site_code) {
			if (!pid) {
				this.$message('活动pid不存在')
				return false
			}
			this.viewModel.visible = true
			this.pageLoading = true
			let langDefualt = lang || 'en'
			this.viewModel.src = '/activity/page-tpl/preview?pid=' + pid + '&lang=' + langDefualt + '&id=' + id + '&site_code=' + site_code + ''

			// let sideType = site_code.split('-')[1], sideWidth
			// sideWidth = '100%'
			let sideType = site_code.split('-')[1], sideWidth
			if (sideType != 'pc') {
				sideWidth = '400px'
			} else {
				sideWidth = '100%'
			}

			this.viewModel.sideType = sideType
			this.viewModel.sideWidth = sideWidth
			this.pageLoading = false
			/* 预览类型 */
			/* 			let res = await getModelHtml({ pid: pid, lang: lang || 'en', id: id, site_code: site_code })
						if (res.code == 0) {
							let sideType = site_code.split('-')[1], sideWidth

							sideWidth = '100%'

							this.viewModel.sideType = sideType
							this.viewModel.sideWidth = sideWidth
							this.viewModel.html = res.data.pageHtml
						} */
		},
		viewModelClose() {
			this.viewModel.visible = false
			this.viewModel.html = ''
			this.viewModel.src = ''
		},
		handleRowClick(row) {
			if (this.expandRowKeys.length > 0 && this.expandRowKeys[0] == row.id) {
				this.expandRowKeys = []
			} else {
				this.expandRowKeys = [row.id]
			}
		},
		/* 校验当前模板列表 */
		checkCurrentPageForm() {
			let pageTemplateList = this.pageTemplateList,
				tabActive = this.modelInfo.tabActive,
				siteInfo = this.siteInfo,
				tempLength1 = 0,
				tempLength2 = 0

			let pageTemplateListWarn = tabActive == '2' ? '您还没有自己的模板' : '暂无页面模板供使用'
			this.pageTemplateListWarn = pageTemplateListWarn

			pageTemplateList.forEach(function (item, index) {
				if (item.create_user == siteInfo.userName) {
					tempLength1 += 1
				} else if (item.create_user != siteInfo.userName && item.tpl_type == 1) {
					tempLength2 += 1
				}
			})
			this.modelInfo.tempLength1 = tempLength1
			this.modelInfo.tempLength2 = tempLength2
		}
	},
	created() {
		var _this = this

		bus.$on('giveData', data => {
			this.siteInfo = data
		})

		refreshSelete().then(function (res) {
			_this.options = res.data
		})
	},
}
</script>

<style scoped>
html {
  overflow: hidden !important;
}
.activity-detail-content {
  width: 360px;
}

.activity-detail-created-time {
  padding-bottom: 15px;
  border-top: 1px solid #ebeef5;
  padding-top: 15px;
}

.activity-detail-updated-time {
  border-bottom: 1px solid #ebeef5;
  padding-bottom: 15px;
}

.activity-detail-link {
  color: #409eff;
}

.model-item img {
  max-width: 100%;
  width: 150px;
  height: 150px;
  display: block;
  margin: 10px auto;
}
.el-table tr {
  height: 80px;
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
.gs-col-all {
  width: 100% !important;
}
.activityPageDialog {
  .el-form-item {
    width: 500px;
  }
  .el-date-editor {
    width: 100%;
  }
}
.geshop-activity-child-pages:hover .child-pages-hover {
  z-index: 100;
  display: block;
}
.child-pages-hover {
  display: none;
}
.child-page-name .count-tip-box {
  position: absolute;
  right: 458px;
  top: 250px;
  z-index: 100;
}
.gs-col-all .count-tip-box {
  position: absolute;
  right: 65px;
  top: 42px;
}
.child-page-statistical-code .count-tip-box {
  position: absolute;
  right: 8px;
  bottom: 0px;
}
.child-page-introduction .count-tip-box {
  position: absolute;
  right: 0px;
  bottom: 0px;
}
.geshop-new-activities-name .count-tip-box {
  position: absolute;
  top: 42px;
  right: 10px;
}
.geshop-new-activities-introduction .count-tip-box {
  position: absolute;
  top: 101px;
  right: 10px;
}
.geshop-new-activities-lang .el-form-item__error {
  position: absolute;
  top: 0;
  left: 44px;
}
.geshop-new-activities-place .el-form-item__error {
  position: absolute;
  top: 0;
  left: 70px;
}
.geshop-activity-lists .has-gutter th {
  background-color: #f4f4f4 !important;
  padding: 8px 0px !important;
}
.geshop-activity-lists .el-table__header-wrapper {
  height: 40px !important;
}
.geshop-form-inline {
  display: block;
  height: 40px;
  border-radius: 4px;
  position: absolute;
  right: 24px;
  top: 16px;
}
</style>
