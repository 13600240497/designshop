<template>
	<site-layout @publicReady="publicReady">
		<el-row :span="24" class="geshop-Activity-tit">
			<span class="geshop-Activity-title">活动管理</span>
		</el-row>
		<!-- search tool -->
		<el-row class="geshop-Activity-btn">
			<el-col :span="24" class="gb-search-form" style="min-width:320px">
				<el-button type="primary" @click="createActivity" style="width:140px;">
					<span class="icon-geshop-pack-up"></span>
					<span class="geshop-icon-add-text">新增专题活动</span>
				</el-button>
				<el-button @click="refreshHeadFoot" style="left:180px;">
					<span class="icon-geshop-reset"></span>
					<span class="geshop-icon-refresh-text">一键刷新头尾部</span>
				</el-button>
			</el-col>
			<el-col :span="24" class="gb-search-form" style="text-align:right;margin-left:0;">
				<el-form :inline="true">
					<el-form-item label="渠道">
						<el-select v-model="searchInfo.searchChannel" multiple filterable collapse-tags placeholder="请选择">
							<el-option v-for="item in channelOptions" :key="item.key" :label="item.name"
												 :value="item.key"></el-option>
						</el-select>
					</el-form-item>
					<el-form-item label="名称">
						<el-input v-model="searchWord" placeholder="请输入专题活动名称"></el-input>
					</el-form-item>
					<el-form-item label="活动主题">
						<el-input v-model="searchInfo.searchObs" placeholder="请输入OBS主题"></el-input>
					</el-form-item>
					<el-form-item label="创建者">
						<el-input v-model="createName" placeholder="请输入创建者名称"></el-input>
					</el-form-item>
					<el-form-item label="专题ID">
						<el-input v-model="subpageId" placeholder="请输入专题ID"></el-input>
					</el-form-item>
					<el-form-item>
						<el-button type="primary" @click="doSearch">搜索</el-button>
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
				<el-table :data="activityList" @expand-change="handleExpandChange" style="width: 100%" :row-key="getRowKey"
									:expand-row-keys="expandRowKeys" @row-click="handleExpandChange">
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
											<el-button class="icon-geshop-decorate"
																 @click="decorate_redirect(list.design_url,list.is_lock,list.activity_create_user)"></el-button>
										</el-tooltip>
										<el-tooltip content="上线" placement="bottom" effect="light">
											<el-button class="icon-geshop-online" v-if="(list.status == 1 || list.status == 4)"
																 @click="GB_verifyPage(list.id, list.activity_id, 2, list.is_lock , list.activity_create_user)"></el-button>
										</el-tooltip>
										<el-tooltip content="下线" placement="bottom" effect="light">
											<el-button class="icon-geshop-offline" v-if="list.status == 2"
																 @click="GB_verifyPage(list.id, list.activity_id, 4, list.is_lock, list.activity_create_user)"></el-button>
										</el-tooltip>
										<el-tooltip content="预览" placement="bottom" effect="light">
											<el-button class="icon-geshop-search" @click="openCountryURL(list.id)"></el-button>
										</el-tooltip>
										<el-tooltip content="转M端" placement="bottom" effect="light">
											<el-button class="icon-geshop-mobile"
																 @click="convertM(list.id, list.is_lock, list.activity_create_user, list.group_info, list.group_languages,list)"
																 v-if="list.activity_type == 1 && list.hasToWap == true"></el-button>
										</el-tooltip>
										<el-tooltip content="转APP端" placement="bottom" effect="light">
											<el-button class="icon-geshop-mobile"
																 @click="convertAPP(list.id, list.is_lock, list.activity_create_user, list.group_info, list.group_languages,list)"
																 v-if="list.activity_type == 2 "></el-button>
										</el-tooltip>
										<el-tooltip content="转Android端" placement="bottom" effect="light">
											<el-button class="icon-geshop-mobile"
																 @click="convertNative('ios2android',list.id, list.is_lock, list.activity_create_user, list.group_info, list.group_languages,list)"
																 v-if="list.activity_type == 4 && list.hasToAndroid"></el-button>
										</el-tooltip>
										<el-tooltip content="转IOS端" placement="bottom" effect="light">
											<el-button class="icon-geshop-mobile"
																 @click="convertNative('android2ios',list.id, list.is_lock, list.activity_create_user, list.group_info, list.group_languages,list)"
																 v-if="list.activity_type == 6 && list.hasToIos"></el-button>
										</el-tooltip>
									</div>
								</div>
								<div class="child-pages-title">{{ list.title }}</div>
								<div class="child-pages-time">{{ parseInt(list.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}</div>
								<!--修改为自定义urlid-->
								<div class="child-pages-id-name">专题ID: {{ list.special_id }} <span>{{ list.create_name }}</span></div>
								<!--<div class="child-pages-id-name">专题ID: {{ list.id }} <span>{{ list.create_name }}</span></div>-->
								<div>
									<a @click="viewPages(list.id)" class="child-pages-link">查看访问链接</a>
									<el-dropdown split-button style="margin-left:192px;">
										<el-dropdown-menu slot="dropdown">
											<el-dropdown-item type="primary" size="small"
																				@click.native="editPage(list, list.is_lock, list.activity_create_user, scope.row.group_info.lang_list, scope.row.group_info.platform_list)">
												编辑
											</el-dropdown-item>
											<el-dropdown-item type="danger" size="small"
												@click.native="removePage(list.group_id, list.is_lock, list.activity_create_user)">删除
											</el-dropdown-item>
										</el-dropdown-menu>
									</el-dropdown>
								</div>
							</el-card>
							<el-card class="box-card geshop-activity-child-pages">
								<el-col>
									<img src="/resources/images/default/banner_default.png" class="child-pages-image"
											 style="height:112px;width:100%;display:block;">
									<el-button class="icon-geshop-add-big"
														 @click="createPage(scope.row.id, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user,scope.row)"
														 style="font-size:40px;margin-top:28px;margin-left:100px;padding:0px 0px;"></el-button>
									<p class="child-pages-add">添加子页面</p>
								</el-col>
							</el-card>
						</template>
					</el-table-column>
					<el-table-column prop="id" label="ID" width="100"></el-table-column>
					<el-table-column prop="name" label="活动名称" width="260"></el-table-column>
					<el-table-column prop="pipelineList" label="应用渠道">
						<template slot-scope="scope">
							<span :title="scope.row.pipelineList" class="over-line-clamp">{{scope.row.pipelineList}}</span>
						</template>
					</el-table-column>
					<el-table-column prop="platformList" label="应用平台">
						<template slot-scope="scope">
							<span class="over-line-clamp work-break_word">{{scope.row.platformList.toString().replace(/,/g,'、')}}</span>
						</template>
					</el-table-column>
					<el-table-column prop="theme_name" label="活动主题"></el-table-column>
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
					<el-table-column prop="update_time" label="操作时间">
						<template slot-scope="scope">
							<span>{{ parseInt(scope.row.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
					<el-table-column prop="create_name" label="创建者"></el-table-column>
					<!-- 活动操作 -->
					<el-table-column label="操作" width="450" class="geshop-activity-more" class-name="gb-activity-op">
						<template slot-scope="scope" style="line-height:80px;" id="gbActivityOp">
							<el-tooltip content="新增子页面" placement="bottom" effect="light">
								<el-button class="icon-geshop-add-small" style="font-size:24px;" v-if="scope.row.is_lock == 0"
													 @click.stop="createPage(scope.row.id, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user,scope.row)"></el-button>
							</el-tooltip>
							<el-button class="icon-geshop-add-small is-lock" v-if="scope.row.is_lock == 1"
												 @click.stop="createPage(scope.row.id, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user,scope.row)"></el-button>
							<el-tooltip content="新增活动渠道信息" placement="bottom" effect="light">
								<el-button class="icon-geshop-add-channel" style="font-size:24px;" v-if="scope.row.is_lock == 0"
													 @click.stop="addActivityChannel(scope.row)"></el-button>
							</el-tooltip>
							<el-button class="icon-geshop-add-channel is-lock" v-if="scope.row.is_lock == 1"
												 @click.stop="addActivityChannel(scope.row)"></el-button>

							<el-tooltip content="上线" placement="bottom" effect="light">
								<el-button class="icon-geshop-online" style="font-size:24px;" v-show="scope.row.is_lock == 0"
													 @click.stop="GB_verifyActivity(scope.row.id, 2, scope.row.is_lock, scope.row.create_user)"
													 v-if="permissions.includes('activity/activity/verify') && [1, 4].includes(scope.row.status)">
								</el-button>
							</el-tooltip>
							<el-tooltip content="上线" placement="bottom" effect="light">
								<el-button class="icon-geshop-online" style="font-size:24px;color:#9E9E9E;background-color:#ffffff;"
													 v-show="scope.row.is_lock == 1"
													 @click.stop="GB_verifyActivity(scope.row.id, 2, scope.row.is_lock, scope.row.create_user)"
													 v-if="permissions.includes('activity/activity/verify') && [1, 4].includes(scope.row.status)">
								</el-button>
							</el-tooltip>
							<el-tooltip content="下线" placement="bottom" effect="light">
								<el-button class="icon-geshop-offline" style="font-size:24px;" v-show="scope.row.is_lock == 0"
													 @click.stop="GB_verifyActivity(scope.row.id, 4, scope.row.is_lock, scope.row.create_user)"
													 v-if="permissions.includes('activity/activity/verify') && scope.row.status == 2"></el-button>
							</el-tooltip>
							<el-tooltip content="下线" placement="bottom" effect="light">
								<el-button class="icon-geshop-offline" style="font-size:24px;color:#9E9E9E;background-color:#ffffff;"
													 v-show="scope.row.is_lock == 1"
													 @click.stop="GB_verifyActivity(scope.row.id, 4, scope.row.is_lock, scope.row.create_user)"
													 v-if="permissions.includes('activity/activity/verify') && scope.row.status == 2"></el-button>
							</el-tooltip>
							<el-dropdown split-button style="margin-left:10px;">
								<el-dropdown-menu slot="dropdown">
									<el-dropdown-item type="primary" size="small"
																		@click.native="editActivity(scope.row, scope.row.is_lock, scope.row.create_user)">编辑
									</el-dropdown-item>
									<el-dropdown-item type="danger" size="small"
																		@click.native="removeActivity(scope.row.id, scope.row.is_lock, scope.row.create_user)">
										删除
									</el-dropdown-item>
									<el-dropdown-item type="primary" @click.native="viewDetail(scope.row)" size="small">查看二维码
									</el-dropdown-item>
								</el-dropdown-menu>
							</el-dropdown>
							<span style="margin-left:10px;line-height:80px;">锁定</span>
							<el-switch v-model="scope.row.lock_status"
												 @change="GB_lockingActivity(scope.row.id, scope.row.is_lock, scope.row.create_user, $event)"></el-switch>
						</template>
					</el-table-column>
				</el-table>
			</el-col>
		</el-row>

		<el-row v-if="total > 10">
			<el-col :span="24" class="text-right geshop-article-page">
				<el-pagination layout="prev, pager, next" :page-size="Number(10)" :current-page="currentPage" :total="total"
											 @current-change="handleCurrentChange"></el-pagination>
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
						<el-col :span="24" class="text-center" v-if="Boolean(currentActivityRow.qrcode)"><img alt="二维码"
																																																	:src="currentActivityRow.qrcode"
																																																	width="120"></el-col>
						<el-col :span="24" class="text-center" v-if="Boolean(currentActivityRow.preview)">
							<a :href="currentActivityRow.preview" class="activity-detail-link">{{ currentActivityRow.name }}</a>
						</el-col>
					</el-row>
				</div>
			</el-card>
		</el-dialog>
		<!-- 新增活动 -->
		<el-dialog :title="activityForm.dialogTitle" :visible.sync="dialogActivityVisible"
							 class="geshop-new-activities geshop-new-child-page"
							 @close="closeDialogActivity">
			<el-form :model="activityForm" :rules="activityRules" ref="activityForm" v-loading="obsLoading">
				<!-- 新增状态应用端口 -->
				<el-form-item label="应用端口" prop="place" class="geshop-new-activities-place" v-if="activityForm.status==1">
					<el-checkbox-group v-model="activityForm.place">
						<el-checkbox v-for="(item,key) in places" :disabled="key == activityForm.site_code" :label="key" :key="key">
							{{
							item.platform_name }}
						</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<!-- 编辑状态应用端口 -->
				<el-form-item label="修改的端口" prop="place" class="geshop-new-activities-place" v-if="activityForm.status==2">
					<el-checkbox-group v-model="activityForm.editPlace">
						<el-checkbox v-for="(item,key) in editPlaces" v-if="item.selected"
												 :disabled="item.code == activityForm.site_code"
												 :label="item.code" :key="key">{{
							item.name }}
						</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<!-- 新增状态渠道 -->
				<el-form-item label="渠道" prop="channelLang" class="geshop-new-activities-place" v-if="activityForm.status==1">

					<el-tabs v-model="activityForm.channelCurrent">
						<el-tab-pane v-for="(item,key) in allSupportChannelLang" :label="item.name" :name="item.key"
												 :key="item.key">
							<el-form-item label="语言" prop="lang" class="geshop-new-activities-lang">
								<div>
									<el-checkbox v-if="activityForm.channelLang[key]"
															 :indeterminate="activityForm.channelLang[key].checkPartStatus" label="全选"
															 v-model="activityForm.channelLang[key].selectAll"
															 @change="handleSelectAllLang($event,key)">全选
									</el-checkbox>
								</div>
								<el-checkbox-group v-if="activityForm.channelLang[key] && activityForm.channelLang[key].value"
																	 v-model="activityForm.channelLang[key].value">
									<el-checkbox v-for="childItem of item.language" :label="childItem.key" :key="childItem.key"
															 @change="handleLangCheck($event,activityForm.channelLang[key].value,item)">
										{{childItem.name}}
									</el-checkbox>
								</el-checkbox-group>
							</el-form-item>

						</el-tab-pane>
					</el-tabs>
				</el-form-item>
				<el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place" v-if="activityForm.status==2">

					<el-tabs v-model="activityForm.channelCurrent">
						<el-tab-pane v-for="item in editSupportChannelLang" :label="item.name" :name="item.key" :key="item.key">
							<el-form-item label="语言" prop="lang">
								<el-checkbox-group v-if="activityForm.channelLang[item.key] && activityForm.channelLang[item.key].value"
																	 v-model="activityForm.channelLang[item.key].value">
									<el-checkbox v-for="childItem in item.langList" :label="childItem.key" :key="childItem.key" disabled>
										{{childItem.name}}
									</el-checkbox>
								</el-checkbox-group>
							</el-form-item>

						</el-tab-pane>
					</el-tabs>
				</el-form-item>
				<!-- 新增状态语言 -->
				<!-- <el-form-item label="渠道" prop="lang" class="geshop-new-activities-lang" v-if="activityForm.status==1">
					<el-checkbox-group v-model="activityForm.lang">
						<el-checkbox v-for="item in supportLangs" :label="item.key" :key="item.key">{{ item.name }}</el-checkbox>
					</el-checkbox-group>
				</el-form-item> -->
				<p style="margin:0;color:#b7b7b7;">注：请选择装修需要的最多语种，不同端无此语种，不装修即可</p>
				<!-- 编辑状态语言 -->
				<!-- <el-form-item label="语言" prop="lang" class="geshop-new-activities-lang" v-if="activityForm.status==2">
					<el-checkbox-group v-model="activityForm.editSupportLang">
						<el-checkbox v-for="item in editSupportLangs" disabled :label="item.key" :key="item.key">{{ item.name }}</el-checkbox>
					</el-checkbox-group>
				</el-form-item> -->
				<el-form-item label="名称" prop="name" class="geshop-new-activities-name" v-on:keyup.native="handleNameKeyup">
					<el-input v-model="activityForm.name"></el-input>
					<span class="count-tip-box">{{ actNameCount }}/100</span>
				</el-form-item>

				<!-- GB对接OBS -->
				<template v-if="siteInfo.site == 'gb'">
					<el-form-item label="关联OBS系统的主题" prop="obsName" class="geshop-new-activities-obs-name">
						<el-select v-model="obs.selected" placeholder="请选择主题" value-key="id" popper-class="gs-obs-select" filterable
											 clearable @change="obsThemeChange" @blur="obsThemeBlur" @visible-change="obsThemeVisible"
											 ref="ref_obsTheme">
							<el-option v-for="(item,index) in obs.data" :key="index" :label="item.name" :value="item"></el-option>
						</el-select>
					</el-form-item>
				</template>

				<!-- GB对接OBS END -->
				<el-form-item label="简介" prop="description" class="geshop-new-activities-introduction"
											v-on:keyup.native="handleIntroductionKeyup">
					<el-input type="textarea" v-model="activityForm.description" :rows="4"
										placeholder="请简单描述一下这个活动......"></el-input>
					<span class="count-tip-box">{{ actIntroductionCount }}/200</span>
				</el-form-item>
				<p style="margin:5px 0 5px 0;color:#b7b7b7;">注：勾选应用端口及语言提交后不可再修改了哦！</p>
				<el-form-item class="geshop-new-activities-btn">
					<el-button @click="resetForm('activityForm')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('activityForm')" size="small" :loading="submitLoading">确定
					</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>
		<!-- 新增渠道信息 -->
		<el-dialog title="新增渠道信息" :visible.sync="dialogChannelAddVisible"
							 class="geshop-new-activities geshop-new-child-page"
							 @close="closeDialogActivity">
			<el-form :model="activityChannelForm" ref="activityChannelForm">
				<el-form-item label="修改的端口" prop="place" class="geshop-new-activities-place">
					<el-checkbox-group v-model="activityChannelForm.editPlace">
						<el-checkbox v-for="(item,key) in activityChannelForm.places"
												 :disabled="item.selected == 1?true:false"
												 :label="item.code" :key="key">{{
							item.name }}
						</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place">

					<el-tabs v-model="activityChannelForm.channelCurrent" v-if="dialogChannelAddVisible">
						<el-tab-pane v-for="item in allSupportChannelLang" :label="item.name" :name="item.key" :key="item.key">
							<el-form-item label="语言" prop="lang">
								<el-checkbox-group v-model="activityChannelForm.channelLang[item.key].value">
									<el-checkbox v-for="childItem in item.language" :label="childItem.key" :key="childItem.key"
															 :disabled="activityChannelForm.channelLang_old[item.key].value.indexOf(childItem.key)!=-1">
										{{childItem.name}}
									</el-checkbox>
								</el-checkbox-group>
							</el-form-item>

						</el-tab-pane>
					</el-tabs>
				</el-form-item>
				<el-form-item class="geshop-new-activities-btn">
					<el-button @click="resetForm('activityChannelForm')" size="small">取消</el-button>
					<el-button type="primary" @click="submitChannelEditForm" size="small" :loading="submitLoading">确定
					</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>

		<!-- 新增子页面 -->
		<el-dialog :title="pageForm.dialogTitle" :visible.sync="dialogPageVisible" class="geshop-new-child-page"
							 @close="subDialogClose">
			<!-- <el-form :model="publicPageForm" :rules="publicPageRules" ref="publicPageForm">


			</el-form> -->

			<!-- <el-tabs type="card" @tab-click="handleTabClick" v-model="currentLanguage">
				<el-tab-pane v-for="item in langList" :label="item.name" :name="item.key" :key="item.key"></el-tab-pane>
			</el-tabs> -->
			<!-- 多渠道多语言 start-->
			<el-form :model="pageForm" :rules="pageRules" ref="pageForm" class="geshop-new-child-page-title">
				<el-form-item :label="pageForm.id?'修改的端口':'已选应用端口'" prop="place" class="gs-col-all child-page-place">
					<el-checkbox-group v-model="publicPageForm.place" v-if="!Boolean(pageForm.id)">
						<el-checkbox v-for="(item,key) in pagePlaces" :label="item.code" disabled :key="key">{{ item.name }}
						</el-checkbox>
					</el-checkbox-group>
					<el-checkbox-group v-model="publicPageForm.place" v-else>
						<el-checkbox v-for="(item,key) in pagePlaces" :label="item.code" :key="key"
												 :disabled="item.code == activityTabName">{{
							item.name }}
						</el-checkbox>
					</el-checkbox-group>
				</el-form-item>
				<el-form-item label="专题活动名称" prop="title" class="gs-col-all">
					<el-input v-model="pageForm.title"></el-input>
				</el-form-item>
				<el-form-item label="页面下线时间" prop="end_time" class="" style="margin: 20px 0;">
					<el-date-picker v-model="end_time" type="datetime" v-on:change="setChildEndTime"
													:picker-options="pickerOptions1"
													value-format="timestamp"></el-date-picker>
				</el-form-item>
				<!-- GB对接OBS二级页面 -->
				<el-form-item label="关联OBS系统的页面" prop="obs-name" class="gs-col-all geshop-new-activities-obs-name"
											style="margin: 15px 0;">
					<el-row :gutter="20" v-loading="obsLoading">
						<el-col :span="6">
							<el-input v-model="obs.selected.name" placeholder="请选择obs主题" :disabled="true"></el-input>
						</el-col>
						<el-col :span="8">
							<el-select v-model="pageForm.obsPage.selected" placeholder="请选择页面" value-key="id"
												 popper-class="gs-obs-select"
												 filterable clearable @change="updateOBSPage" ref="ref_obsPage">
								<el-option v-for="(item,index) in obsPage.data" :key="index" :label="item.name"
													 :value="item"></el-option>
							</el-select>
						</el-col>
					</el-row>
				</el-form-item>

				<el-tabs type="card" @tab-click="handleTabClick($event,'channel')" v-model="currentChannel">
					<el-tab-pane v-for="(item,key) in channelGroup" :label="item.name" :name="item.key" :key="key">
						<el-form-item label="页面默认语言" prop="place" class="gs-col-all child-page-place child-page-default-lang">
							<el-radio-group v-if="channelCommon[item.key]" v-model="channelCommon[item.key].defaultLang">
								<el-radio v-for="childItem in item.langList" :label="childItem.key" :key="childItem.key">{{
									childItem.name
									}}
								</el-radio>
							</el-radio-group>
						</el-form-item>
						<el-form-item label="自定义url" prop="url_name" class="child-page-url label-custom-name">
							<label class="current-site-url">{{currentSiteUrl}}/</label>
							<el-input v-model="pageForm.url_name" @change="updatePageUrlName" v-on:keyup.native="handleUrlKeyup"
												style="max-width: 250px;margin-left:10px;"><span class="count-tip-box">{{ urlCount }}/64</span></el-input>
							<label v-if="pageForm.url_name_id">-special-{{pageForm.url_name_id}}.html<span v-if="currentChannel === 'GB'">?lang={{currentLanguage}}</span></label>
							<label v-else>-special-****.html<span v-if="currentChannel === 'GB'">?lang={{currentLanguage}}</span></label>
						</el-form-item>
						<el-form-item label="语言" class="editlang-form-item">
							<el-tabs type="card" @tab-click="handleTabClick($event,'lang')" v-model="currentLanguage">
								<el-tab-pane v-for="childItem in item.langList" :label="childItem.name" :name="childItem.key"
														 :key="childItem.key">
								</el-tab-pane>
								<!-- <el-form :model="pageForm" :rules="pageRules" ref="pageForm" class="geshop-new-child-page-title"> -->
								<!-- <el-form-item label="专题活动名称" prop="title" class="gs-col-all">
								<el-input v-model="pageForm.title" @change="updatePageTitle" v-on:keyup.native="handleTitleKeyup"></el-input>
							</el-form-item> -->
								<!-- <el-form-item label="自定义url" prop="url_name" class="gs-col-all child-page-url">
								<label class="current-site-url">{{currentSiteUrl}}/</label>
								<el-input v-model="pageForm.url_name" @change="updatePageUrlName" v-on:keyup.native="handleUrlKeyup" style="max-width: 745px;"></el-input>
								<span class="count-tip-box">{{ urlCount }}/64</span>
								<label>.html</label>
							</el-form-item> -->
								<!-- GB对接OBS二级页面 -->
								<!-- <template v-if="siteInfo.site == 'gb'">
								<el-form-item label="关联OBS系统的页面" prop="obs-name" class="geshop-new-activities-obs-name">
									<el-row :gutter="20" v-loading="obsLoading">
										<el-col :span="6">
											<el-input v-model="obs.selected.name" placeholder="请选择obs主题" :disabled="true"></el-input>
										</el-col>
										<el-col :span="8">
											<el-select v-model="pageForm.obsPage.selected" placeholder="请选择页面" value-key="id" popper-class="gs-obs-select"
											 filterable clearable @change="updateOBSPage" @blur="obsPageBlur" ref="ref_obsPage">
												<el-option v-for="(item,index) in obsPage.data" :key="index" :label="item.name" :value="item"></el-option>
											</el-select>
										</el-col>
									</el-row>
								</el-form-item>
							</template> -->

								<!-- GB对接OBS END -->
								<!-- 活动结束后跳转链接仅有end_url取消pc_end_url,m_end_url -->
								<el-form-item label="PC活动结束后跳转链接" v-if="pageForm.pc_status" prop="end_url" class="child-page-link">
									<el-row :gutter="20">
										<el-col :span="15" style="width:400px;">
											<el-input v-model="pageForm.end_url" placeholder="请输入url链接"
																@change="updatePageField($event,'end_url')"></el-input>
										</el-col>
										<el-col class="child-page-note">
											<span>备注：不填默认为跳转至首页</span>
										</el-col>
									</el-row>
								</el-form-item>
								<!-- <el-form-item label="M活动结束后跳转链接" v-if="pageForm.m_status" prop="end_url" class="child-page-link">
									<el-row :gutter="20">
										<el-col :span="15" style="width:400px;">
											<el-input v-model="pageForm.m_end_url" placeholder="请输入url链接" @change="updatePageField($event,'m_end_url')"></el-input>
										</el-col>
										<el-col class="child-page-note">
											<span>备注：不填默认为跳转至首页</span>
										</el-col>
									</el-row>
								</el-form-item> -->
								<el-form-item label="PC端自动跳转M端链接" prop="redirect_url" class="gs-col-all"
															v-show="pageForm.need_redirect">
									<el-input v-model="pageForm.redirect_url" placeholder="PC端自动跳转M端链接"
														@change="updatePageField($event,'redirect_url')"></el-input>
									<span class="gb-tips">注：此处填写将此活动PC端的链接放在M端，用于切换至M端的活动链接</span>
								</el-form-item>
								<span class="gb-label-title" v-if="!Boolean(pageForm.id)">页面模板信息</span>
								<el-form-item label="PC端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.pc_status"
															class="child-page-model">
									<el-button type="primary" size="small" :disabled="Boolean(pageForm.id)"
														 @click="handleModelTempSelect('pc')">选择模板
									</el-button>
									<el-tag type="info">{{ pageForm.tpl_name }}</el-tag>
								</el-form-item>
								<el-form-item label="M端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.m_status"
															class="child-page-model">
									<el-button type="primary" size="small" :disabled="Boolean(pageForm.id)"
														 @click="handleModelTempSelect('wap')">选择模板
									</el-button>
									<el-tag type="info">{{ pageForm.m_tpl_name }}</el-tag>
								</el-form-item>
								<el-form-item label="APP端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.app_status"
															class="child-page-model">
									<el-button type="primary" size="small" :disabled="Boolean(pageForm.id)"
														 @click="handleModelTempSelect('app')">选择模板
									</el-button>
									<el-tag type="info">{{ pageForm.app_tpl_name }}</el-tag>
								</el-form-item>
								<el-form-item label="IOS端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.ios_status"
															class="child-page-model">
									<el-button type="primary" size="small" :disabled="Boolean(pageForm.id)"
														 @click="handleModelTempSelect('ios')">选择模板
									</el-button>
									<el-tag type="info">{{ pageForm.ios_tpl_name }}</el-tag>
								</el-form-item>
								<el-form-item label="Android端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.android_status"
															class="child-page-model">
									<el-button type="primary" size="small" :disabled="Boolean(pageForm.id)"
														 @click="handleModelTempSelect('android')">选择模板
									</el-button>
									<el-tag type="info">{{ pageForm.android_tpl_name }}</el-tag>
								</el-form-item>
								<span class="gb-label-title">SEO信息</span>
								<el-form-item label="SEO标题" prop="seo_title" class="child-page-keywords">
									<el-input v-model="pageForm.seo_title" @change="updatePageField($event,'seo_title')"
														placeholder=""></el-input>
								</el-form-item>
								<el-form-item label="SEO关键字" prop="keywords" class="child-page-keywords">
									<el-input v-model="pageForm.keywords" @change="updatePageField($event,'keywords')"
														placeholder="有利于SEO优化"></el-input>
								</el-form-item>
								<el-form-item label="SEO简介" prop="description" class="child-page-keywords">
									<el-input type="textarea" v-model="pageForm.description"
														:rows="4"
														@change="updatePageField($event,'description')" placeholder="有利于SEO优化"></el-input>
									<!--<el-input type="textarea" v-model="pageForm.description" v-on:keyup.native="handleDescriptionKeyup"-->
									<!--:rows="4"-->
									<!--@change="updatePageField($event,'description')" placeholder="有利于SEO优化"></el-input>-->
									<!--<span class="count-tip-box">{{ pageIntroductionCount }}/200</span>-->
								</el-form-item>
								<span class="gb-label-title">导航分享信息</span>
								<el-form-item label="分享入口" prop="share_entry" class="child-page-share-entry"
															style="margin-top: 0;line-height: inherit;">
									<el-checkbox-group v-model="pageForm.share.checked"
																		 @change="updatePageField($event,'share','checked')">
										<el-checkbox v-for="item in shareEntry" :label="item.key" :key="item.key">{{item.name}}
										</el-checkbox>
									</el-checkbox-group>
								</el-form-item>
								<el-form-item label="分享标题" prop="share_title" class="child-page-keywords">
									<el-input v-model="pageForm.share.title" @change="updatePageField($event,'share','title')"></el-input>
								</el-form-item>
								<el-form-item label="分享描述" prop="share_desc" class="child-page-keywords">
									<el-input v-model="pageForm.share.desc" @change="updatePageField($event,'share','desc')"></el-input>
								</el-form-item>
								<el-form-item label="分享图片" prop="share_img" class="child-page-keywords child-page-share_img">
									<el-input v-model="pageForm.share.img" @change="updatePageField($event,'share','img')"></el-input>
									<el-upload action="/activity/page-tpl/upload-pic" name="files" accept="image/jpg,image/jpeg,image/png"
														 :on-success="handleUploadSuccess" :on-exceed="handleUploadExceed"
														 :on-error="handleUploadError" :file-list="pageForm.share.fileList"
														 :before-upload="handleBeforeUpload">
										<el-button size="small" type="primary">点击上传</el-button>
										<div slot="tip" class="el-upload__tip">注：建议上传750x300的jpg\png\图片</div>
										<!-- <div slot="tip" class="el-upload__tip">只能上传jpg/png文件，且不超过3M</div> -->
									</el-upload>

								</el-form-item>
								<el-form-item label="分享送积分" prop="share.integral" class="child-page-keywords">
									<el-input type="number" v-model.number="pageForm.share.integral" auto-complete="off"
														@change="updatePageField($event,'share','integral')"></el-input>
									<span class="gb-tips">注：为空默认不赠送</span>
								</el-form-item>
								<el-form-item label="积分生效时间" prop="" class="child-page-times">
									<el-date-picker v-model="pageForm.share.integral_times" type="datetimerange" range-separator="-"
																	value-format="timestamp"
																	start-placeholder="开始日期" end-placeholder="结束日期"
																	@change="updatePageField($event,'share','integral_times')">
									</el-date-picker>
								</el-form-item>
								<!-- <el-form-item label="单用户送积分次数" prop="" class="child-page-keywords">
									<el-input v-model="share.integral_times"></el-input>
								</el-form-item> -->

								<el-form-item label="统计代码" prop="statistics_code" class="child-page-statistical-code">
									<el-input type="textarea" v-model="pageForm.statistics_code" v-on:keyup.native="handleCodeKeyup"
														:rows="4"
														@change="updatePageField($event,'statistics_code')"></el-input>
									<span class="count-tip-box">{{ codeCount }}/200</span>
								</el-form-item>
								<el-form-item class="child-page-btns" style="clear:both">
									<el-button @click="resetForm('pageForm')" size="small">取消</el-button>
									<el-button type="primary" @click="submitForm('pageForm')" size="small" :loading="submitLoading">确定
									</el-button>
								</el-form-item>
								<!-- </el-form> -->
							</el-tabs>
						</el-form-item>

					</el-tab-pane>
				</el-tabs>
			</el-form>

		</el-dialog>

		<el-dialog title="活动访问地址" :visible.sync="dialogLinksVisible">
			<el-row>
				<el-button v-for="item in pageLinks" type="primary" style="width: 148px; height: 42px; font-size: 14px; margin: 0 0 10px 5px; text-overflow: ellipsis; overflow: hidden;" :key="item.lang_name" @click="redirect(item.page_url)">{{
					item.lang_name }}
				</el-button>
				<p v-if="tips">其它页面发布进行中，请稍后，关闭此弹窗，将自动在后台执行刷新！！！
					<!-- {{ tips }} <el-button @click="redistribution()" v-if="tips" type="primary">重新发布</el-button> -->
				</p>
			</el-row>
		</el-dialog>

		<el-dialog title="列表页点击预览" :visible.sync="dialogChannelVisible" id="gb_preview">
			<el-row>
				<el-button v-for="item in channelUrlList" type="primary" style="width: 148px; height: 42px; font-size: 14px; margin: 0 0 10px 5px; text-overflow: ellipsis; overflow: hidden;" :key="item.name" @click="redirect(item.link)">{{
					item.name }}
				</el-button>
				<!-- <p>{{ tips }}
					<el-button @click="redistribution()" v-if="tips" type="primary">重新发布</el-button>
				</p> -->
			</el-row>
		</el-dialog>

		<el-dialog title="删除页面" :visible.sync="dialogDeleteVisible" class="geshop-new-activities geshop-new-child-page">
			<el-checkbox v-model="checkAll" @change="handleDeleteCheckAll">全选</el-checkbox>
			<div style="margin: 10px 0;"></div>
			<el-checkbox-group class="el-del-checkbox" v-model="checkedCities" @change="handleDeleteChange">
				<el-checkbox v-for="item in deleteChannelList" :label="item.page_id" :key="item.page_id" style="width: 123px; margin-left: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{item.name}}</el-checkbox>
			</el-checkbox-group>
			<div slot="footer" class="dialog-footer">
				<el-button @click="dialogDeleteVisible = false">取 消</el-button>
				<el-button type="primary" @click="deleteVisibleComfirm">确 定</el-button>
			</div>
		</el-dialog>

		<el-dialog title="PC转M" :visible.sync="dialogConvertM" @close=closeConvertM>
			<el-form :model="convertForm_M" :rules="convertRules_M" ref="convertForm_M">
				<el-row>
					<el-col>
						<span class="gb-label-title gb-label-bt gb-line-inline" style="margin: 0 0 15px;">被转化的内容</span>
						<span class="gb-tips gb-line-inline">（勾选多个渠道多个语言时，按渠道和语言一一对应转换）</span>
					</el-col>
				</el-row>
				<el-form-item label="渠道">
					<el-select v-model="convertForm_M.source_channelCurrent" clearable placeholder="请选择"
										 @change="convertPipeChange($event,'convertForm_M','pc')">
						<el-option v-for="item in convertForm_M.pc_supportChannel" :key="item.key" :value="item.key"
											 :label="item.name"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="语言">
					<el-select v-model="convertForm_M.source_langCurrent" clearable placeholder="请选择"
										 v-if="convertForm_M.pc_supportChannel[convertForm_M.source_channelCurrent]">
						<el-option v-for="item in convertForm_M.pc_supportChannel[convertForm_M.source_channelCurrent]['language']"
											 :key="item.lang"
											 :value="item.lang" :label="item.langName"></el-option>
					</el-select>
				</el-form-item>
				<!-- <el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place">
					<el-tabs v-model="convertForm_M.source_channelCurrent">
						<el-tab-pane v-for="(item,key) of convertForm_M.pc_supportChannel" :label="item.name" :name="item.key" :key="item.key">
							<el-form-item label="语言" prop="lang" class="geshop-new-activities-lang">
								<el-checkbox-group v-model="convertForm_M.source_channelLang[key]">
									<el-checkbox v-for="childItem of item.language" :label="childItem.lang" :key="childItem.lang">
										{{childItem.langName}}</el-checkbox>
								</el-checkbox-group>
							</el-form-item>

						</el-tab-pane>
					</el-tabs>
				</el-form-item> -->
				<el-row>
					<el-col>
						<span class="gb-label-title gb-label-bt" style="margin: 0 0 15px;">转化到M端渠道</span>
					</el-col>
				</el-row>
				<el-form-item label="选择M端活动" prop="activity_id" placeholder="请选择活动" v-if="convertForm_M.is_group==0">
					<el-select v-model="convertForm_M.activity_id" @change="getMPages($event)">
						<el-option v-for="item in mActivities" :label="item.name" :value="item.id" :key="item.id"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="选择活动子页面" prop="page_id" v-if="convertForm_M.is_group==0">
					<el-select v-model="convertForm_M.page_id" placeholder="请选择子页面" @change="handleMPageChange($event)">
						<el-option v-for="(item,index) in mPages" :label="item.title" :value="index" :key="index"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place">
					<el-tabs v-model="convertForm_M.target_channelCurrent">
						<el-tab-pane v-for="(item,key) of convertForm_M.wap_supportChannel" :label="item.name" :name="item.key"
												 :key="item.key">
							<el-form-item label="语言" prop="lang" class="geshop-new-activities-lang">
								<el-checkbox-group v-model="convertForm_M.target_channelLang[key]">
									<el-checkbox v-for="childItem of item.language" :label="childItem.key?childItem.key:childItem.lang"
															 :key="childItem.key?childItem.key:childItem.lang">
										{{childItem.name?childItem.name:childItem.langName}}
									</el-checkbox>
								</el-checkbox-group>
							</el-form-item>

						</el-tab-pane>
					</el-tabs>
				</el-form-item>
				<!-- <el-form-item label="选择语言" prop="lang">
					<el-select v-model="convertForm_M.lang" placeholder="请选择语言">
						<el-option v-for="item in convertLangs" :label="item.name" :value="item.key" :key="item.key"></el-option>
					</el-select>
				</el-form-item> -->
				<el-form-item label="选择方式">
					<el-radio-group v-model="convertForm_M.model">
						<el-radio label="1">在所选子页面后追加</el-radio>
						<el-radio label="2">覆盖子页面内容</el-radio>
					</el-radio-group>
					<el-alert title="此操作不会删除原M活动页面内容，仅在页面最后增加。" type="warning" v-if="convertForm_M.model == 1"
										:closable="false"></el-alert>
					<el-alert title="此操作直接覆盖所选子页面的原有装修效果，且不可还原，请慎用此选项。" type="warning" v-if="convertForm_M.model == 2"
										:closable="false"></el-alert>
				</el-form-item>
				<el-form-item>
					<el-button @click="resetForm('convertForm_M')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('convertForm_M')" size="small" :loading="submitLoading">确定
					</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>

		<el-dialog title="M转APP" :visible.sync="dialogConvertAPP" @close=closeConvertApp>
			<el-form :model="convertForm" :rules="convertRules" ref="convertForm">
				<el-row>
					<el-col>
						<span class="gb-label-title gb-label-bt gb-line-inline" style="margin: 0 0 15px;">被转化的内容</span>
						<span class="gb-tips gb-line-inline">（勾选多个渠道多个语言时，按渠道和语言一一对应转换）</span>
					</el-col>
				</el-row>
				<el-form-item label="渠道">
					<el-select v-model="convertForm.source_channelCurrent" clearable placeholder="请选择"
										 @change="convertPipeChange($event,'convertForm','wap')">
						<el-option v-for="item in convertForm.wap_supportChannel" :key="item.key" :value="item.key"
											 :label="item.name"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="语言">
					<el-select v-model="convertForm.source_langCurrent" clearable placeholder="请选择"
										 v-if="convertForm.wap_supportChannel[convertForm.source_channelCurrent]">
						<el-option v-for="item in convertForm.wap_supportChannel[convertForm.source_channelCurrent]['language']"
											 :key="item.lang"
											 :value="item.lang" :label="item.langName"></el-option>
					</el-select>
				</el-form-item>
				<el-row>
					<el-col>
						<span class="gb-label-title gb-label-bt" style="margin: 0 0 15px;">转化到APP端渠道</span>
					</el-col>
				</el-row>
				<template v-if="convertForm.hasToIos">
					<el-row>
						<el-col>
							<span class="gb-label-title gb-label-bt gb-label-title_small"
										style="margin: 0 0 15px;">ios端对应活动的渠道和语言</span>
						</el-col>
					</el-row>
					<!-- 未绑定ios端情况 -->
					<el-form :model="convertForm['platformActivityPage']['ios']" :rules="convertRules">
						<el-form-item label="活动名称" prop="activity_id" placeholder="请选择活动" v-if="convertForm.ios_is_group==0">
							<el-select v-model="convertForm['platformActivityPage']['ios'].activity_id"
												 @change="getMPages($event,'ios')">
								<el-option v-for="item in convertForm['platformActivityPage']['ios'].mActivities" :label="item.name"
													 :value="item.id"
													 :key="item.id"></el-option>
							</el-select>
						</el-form-item>
						<el-form-item label="页面名称" prop="page_id" v-if="convertForm.ios_is_group==0">
							<el-select v-model="convertForm['platformActivityPage']['ios'].page_id" placeholder="请选择子页面"
												 @change="handleMPageChange($event,'ios')">
								<el-option v-for="(item,index) in convertForm['platformActivityPage']['ios'].mPages" :label="item.title"
													 :value="index"
													 :key="index"></el-option>
							</el-select>
						</el-form-item>
					</el-form>
					<!-- 未绑定ios端情况 -->
					<el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place">
						<el-tabs v-model="convertForm.ios_target_channelCurrent">
							<el-tab-pane v-for="(item,key) of convertForm.ios_supportChannel" :label="item.name" :name="item.key"
													 :key="item.key">
								<el-form-item label="语言" prop="lang" class="geshop-gb-activities-lang">
									<el-checkbox-group v-model="convertForm.ios_target_channelLang[key]">
										<el-checkbox v-for="childItem of item.language" :label="childItem.key?childItem.key:childItem.lang"
																 :key="childItem.key?childItem.key:childItem.lang">
											{{childItem.name?childItem.name:childItem.langName}}
										</el-checkbox>
									</el-checkbox-group>
								</el-form-item>

							</el-tab-pane>
						</el-tabs>
					</el-form-item>
				</template>
				<template v-if="convertForm.hasToAndroid">
					<el-row>
						<el-col>
							<span class="gb-label-title gb-label-bt gb-label-title_small"
										style="margin: 0 0 15px;">android端对应活动的渠道和语言</span>
						</el-col>
					</el-row>
					<!-- 未绑定android端情况 -->
					<el-form :model="convertForm['platformActivityPage']['android']" :rules="convertRules">
						<el-form-item label="活动名称" prop="activity_id" placeholder="请选择活动" v-if="convertForm.android_is_group==0">
							<el-select v-model="convertForm['platformActivityPage']['android'].activity_id"
												 @change="getMPages($event,'android')">
								<el-option v-for="item in convertForm['platformActivityPage']['android'].mActivities" :label="item.name"
													 :value="item.id"
													 :key="item.id"></el-option>
							</el-select>
						</el-form-item>
						<el-form-item label="页面名称" prop="page_id" v-if="convertForm.android_is_group==0">
							<el-select v-model="convertForm['platformActivityPage']['android'].page_id" placeholder="请选择子页面"
												 @change="handleMPageChange($event,'android')">
								<el-option v-for="(item,index) in convertForm['platformActivityPage']['android'].mPages" :label="item.title"
													 :value="index"
													 :key="index"></el-option>
							</el-select>
						</el-form-item>
					</el-form>
					<!-- 未绑定android端情况 end-->
					<el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place">
						<el-tabs v-model="convertForm.android_target_channelCurrent">
							<el-tab-pane v-for="(item,key) of convertForm.android_supportChannel" :label="item.name" :name="item.key"
													 :key="item.key">
								<el-form-item label="语言" prop="lang" class="geshop-gb-activities-lang">
									<el-checkbox-group v-model="convertForm.android_target_channelLang[key]">
										<el-checkbox v-for="childItem of item.language" :label="childItem.key?childItem.key:childItem.lang"
																 :key="childItem.key?childItem.key:childItem.lang">
											{{childItem.name?childItem.name:childItem.langName}}
										</el-checkbox>
									</el-checkbox-group>
								</el-form-item>

							</el-tab-pane>
						</el-tabs>
					</el-form-item>
				</template>

				<!-- <el-form-item label="APP活动" prop="activity_id" v-if="convertForm.is_group==0">
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
				</el-form-item> -->
				<el-form-item label="选择方式">
					<el-radio-group v-model="convertForm.model">
						<el-radio label="1">在所选子页面后追加</el-radio>
						<el-radio label="2">覆盖子页面内容</el-radio>
					</el-radio-group>
					<el-alert title="此操作不会删除原APP活动页面内容，仅在页面最后增加。" type="warning" v-if="convertForm.model == 1"
										:closable="false"></el-alert>
					<el-alert title="此操作直接覆盖所选子页面的原有装修效果，且不可还原，请慎用此选项。" type="warning" v-if="convertForm.model == 2"
										:closable="false"></el-alert>
				</el-form-item>
				<el-form-item>
					<el-button @click="resetForm('convertForm')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('convertForm')" size="small" :loading="submitLoading">确定
					</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>
		<!-- IOS转Android -->
		<el-dialog :title="convertForm_ios_android.dialogTitle" :visible.sync="dialogConvertNative"
							 @close=closeConvertIos_android>
			<el-form :model="convertForm_ios_android" :rules="convertRules_M" ref="convertForm_ios_android">
				<el-row>
					<el-col>
						<span class="gb-label-title gb-label-bt gb-line-inline" style="margin: 0 0 15px;">被转化的内容</span>
						<span class="gb-tips gb-line-inline">（勾选多个渠道多个语言时，按渠道和语言一一对应转换）</span>
					</el-col>
				</el-row>
				<el-form-item label="渠道">
					<el-select v-model="convertForm_ios_android.source_channelCurrent" clearable placeholder="请选择"
										 @change="convertPipeChange($event,'convertForm_ios_android','pc')">
						<el-option v-for="item in convertForm_ios_android['source_supportChannel']" :key="item.key"
											 :value="item.key"
											 :label="item.name"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="语言">
					<el-select v-model="convertForm_ios_android.source_langCurrent" clearable placeholder="请选择"
										 v-if="convertForm_ios_android.source_supportChannel[convertForm_ios_android.source_channelCurrent]">
						<el-option
							v-for="item in convertForm_ios_android['source_supportChannel'][convertForm_ios_android.source_channelCurrent]['language']"
							:key="item.lang" :value="item.lang" :label="item.langName"></el-option>
					</el-select>
				</el-form-item>
				<el-row>
					<el-col>
						<span v-if="convertForm_ios_android.submitType == 'android2ios'" class="gb-label-title gb-label-bt"
									style="margin: 0 0 15px;">转化到IOS端渠道</span>
						<span v-else-if="convertForm_ios_android.submitType == 'ios2android'" class="gb-label-title gb-label-bt"
									style="margin: 0 0 15px;">转化到Android端渠道</span>
					</el-col>
				</el-row>

				<el-form-item label="活动名称" prop="activity_id" placeholder="请选择活动" v-if="convertForm_ios_android.is_group==0">
					<el-select v-model="convertForm_ios_android.activity_id" @change="getMPages($event)">
						<el-option v-for="item in mActivities" :label="item.name" :value="item.id" :key="item.id"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="页面名称" prop="page_id" v-if="convertForm_ios_android.is_group==0">
					<el-select v-model="convertForm_ios_android.page_id" placeholder="请选择子页面" @change="handleMPageChange($event)">
						<el-option v-for="(item,index) in mPages" :label="item.title" :value="index" :key="index"></el-option>
					</el-select>
				</el-form-item>

				<el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place">
					<el-tabs v-model="convertForm_ios_android.target_channelCurrent">
						<el-tab-pane v-for="(item,key) of convertForm_ios_android.target_supportChannel" :label="item.name"
												 :name="item.key"
												 :key="item.key">
							<el-form-item label="语言" prop="lang" class="geshop-new-activities-lang">
								<el-checkbox-group v-model="convertForm_ios_android.target_channelLang[key]">
									<el-checkbox v-for="childItem of item.language" :label="childItem.key?childItem.key:childItem.lang"
															 :key="childItem.key?childItem.key:childItem.lang">
										{{childItem.name?childItem.name:childItem.langName}}
									</el-checkbox>
								</el-checkbox-group>
							</el-form-item>

						</el-tab-pane>
					</el-tabs>
				</el-form-item>

				<!-- <el-form-item label="选择M端活动" prop="activity_id" placeholder="请选择活动" v-if="convertForm_ios_android.is_group==0">
					<el-select v-model="convertForm_ios_android.activity_id" @change="getMPages">
						<el-option v-for="item in mActivities" :label="item.name" :value="item.id" :key="item.id"></el-option>
					</el-select>
				</el-form-item>
				<el-form-item label="选择活动子页面" prop="page_id" v-if="convertForm_ios_android.is_group==0">
					<el-select v-model="convertForm_ios_android.page_id" placeholder="请选择子页面">
						<el-option v-for="item in mPages" :label="item.title" :value="item.id" :key="item.id"></el-option>
					</el-select>
				</el-form-item> -->
				<!-- <el-form-item label="选择语言" prop="lang">
					<el-select v-model="convertForm_ios_android.lang" placeholder="请选择语言">
						<el-option v-for="item in convertLangs" :label="item.name" :value="item.key" :key="item.key"></el-option>
					</el-select>
				</el-form-item> -->
				<el-form-item label="选择方式">
					<el-radio-group v-model="convertForm_ios_android.model">
						<el-radio label="1">在所选子页面后追加</el-radio>
						<el-radio label="2">覆盖子页面内容</el-radio>
					</el-radio-group>
					<el-alert title="此操作不会删除原M活动页面内容，仅在页面最后增加。" type="warning" v-if="convertForm_ios_android.model == 1"
										:closable="false"></el-alert>
					<el-alert title="此操作直接覆盖所选子页面的原有装修效果，且不可还原，请慎用此选项。" type="warning" v-if="convertForm_ios_android.model == 2"
										:closable="false"></el-alert>
				</el-form-item>
				<el-form-item>
					<el-button @click="resetForm('convertForm_ios_android')" size="small">取消</el-button>
					<el-button type="primary" @click="submitForm('convertForm_ios_android',convertForm_ios_android['submitType'])"
										 size="small" :loading="submitLoading">确定
					</el-button>
				</el-form-item>
			</el-form>
		</el-dialog>

		<el-dialog title="子页面新增" :visible.sync="modelInfo.visible" @close="handleModelClose" append-to-body
							 class="geshop-page-template">
			<div class="geshop-page-template-title">
				<span class="icon-geshop-backward"></span>
				<span class="geshop-page-template-word">选择页面模板</span>
			</div>
			<el-row>
				<el-tabs type="border-card" class="model-dialog" @tab-click="tplTabClick" v-model="modelInfo.tabActive"
								 v-loading="tplInfo.loading">
					<el-tab-pane label="我的模板" name="2" ref="tpl0">
						<div class="model-box" v-for="(item,index) in pageTemplateList" :key="index">
							<el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect"
												v-if="item.create_user == siteInfo.userName"
												@change="updateModelSelect">
								<div class="model-item">
									<span>{{item.name}}</span>
									<img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
									<div class="icon-geshop-search"
											 @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"></div>
								</div>
							</el-radio>
						</div>
					</el-tab-pane>
					<el-tab-pane label="共用模板" name="1" ref="tpl1">
						<div class="model-box" v-for="(item,index) in pageTemplateList" :key="index">
							<el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect"
												v-if="item.create_user != siteInfo.userName && item.tpl_type == 1"
												@change="updateModelSelect">
								<div class="model-item">
									<span>{{item.name}}</span>
									<img :src="item.pic?item.pic:'/resources/images/default/picture.png'">
									<div class="icon-geshop-search"
											 @click="seeTemplate(item.pid,item.lang.key,item.id,item.site_code)"></div>
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
			<div slot="footer" class="dialog-footer">
				<el-button @click="handleCancelSelectedModel">取 消</el-button>
				<el-button type="primary" @click="handleSureModel">确定</el-button>
			</div>
		</el-dialog>

		<el-dialog title="转换成APP端活动" :visible.sync="dialogConvertVisible" width="30%">
			<span><img src="/resources/images/moblie.png" alt=""
								 style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
			<p style="text-align:center;font-size:16px">已转换完成</p>
			<p style="text-align:center;font-size:14px;color:black">您可点击下方按钮进入APP端装修页查看</p>
			<span slot="footer" class="ToM">
				<el-button style="text-align:center" type="primary" @click="convertRedirect()">进入APP端装修页面</el-button>
			</span>
		</el-dialog>

		<el-dialog title="转换成M端活动" :visible.sync="dialogConvertMVisible" width="30%">
			<span><img src="/resources/images/moblie.png" alt=""
								 style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
			<p style="text-align:center;font-size:16px">已转换完成</p>
			<p style="text-align:center;font-size:14px;color:black">您可点击下方按钮进入M端装修页查看</p>
			<span slot="footer" class="ToM">
				<el-button style="text-align:center" type="primary" @click="convertMRedirect()">进入M端装修页面</el-button>
			</span>
		</el-dialog>

		<el-dialog title="转换成IOS端活动" :visible.sync="dialogConvertIosVisible" width="30%">
			<span><img src="/resources/images/moblie.png" alt=""
								 style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
			<p style="text-align:center;font-size:16px">已转换完成</p>
			<p style="text-align:center;font-size:14px;color:black">您可点击下方按钮进入APP端装修页查看</p>
			<span slot="footer" class="ToM">
				<el-button style="text-align:center" type="primary" @click="convertNativeRedirect('ios')">进入IOS端装修页面</el-button>
			</span>
		</el-dialog>

		<el-dialog title="转换成Android端活动" :visible.sync="dialogConvertAndroidVisible" width="30%">
			<span><img src="/resources/images/moblie.png" alt=""
								 style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
			<p style="text-align:center;font-size:16px">已转换完成</p>
			<p style="text-align:center;font-size:14px;color:black">您可点击下方按钮进入M端装修页查看</p>
			<span slot="footer" class="ToM">
				<el-button style="text-align:center" type="primary"
									 @click="convertNativeRedirect('android')">进入Android端装修页面</el-button>
			</span>
		</el-dialog>

		<el-dialog title="页面模板" class="geshop-template-model" :visible.sync="viewModel.visible" @close="viewModelClose"
							 :width="viewModel.sideWidth">
			<el-row v-loading="pageLoading">
				<el-col class="imgPreview text-center" style="height:100%;">
					<iframe frameborder="0" :src="viewModel.src" class="iframePreview" style="width:100%;height:100%;"></iframe>
				</el-col>
			</el-row>
		</el-dialog>
	</site-layout>
</template>

<script>
	import siteLayout from './layouts/Layout.vue';
	import {
		GB_getActivityList, GB_getPageList, GB_addActivity, GB_updateActivity, GB_deleteActivity, GB_verifyActivity,
		GB_lockingActivity, GB_refreshSite, GB_refreshSelete,
		GB_batchEditPage, GB_deletePage, GB_verifyPage, GB_getChannelKeyList, GB_getPageTemplateList, GB_getAppActivityList,
		GB_getMActivityList, GB_convertToAppPage, GB_convertToIosOrAndroid, GB_convertToMPage,
		GB_getAccessLink, GB_actReleased, GB_batchAddPage, obsLevel1, obsLevel2, GB_getChannelUrlList,
		GB_editAdd,GB_getChannelLangList,GB_getChannelDelList
	} from '../plugin/api';
	import { getCookie, extendDeep } from '../plugin/mUtils';
	import bus from '../store/bus-index.js';
	import '../../resources/stylesheets/activityGB.less';
	import '../../resources/stylesheets/icon.css';
	import '../../resources/fonts/svg-fonts/style.css';

	export default {
		components: { siteLayout },
		data () {
			return {
				dialogDeleteVisible: false,
				checkedCities: [],
				deleteChannelList: [],
				deleteCheckAll: [],
				checkAll: false,
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
				subpageId: '',
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
				editSupportLangs: [],
				pagePlaces: [],
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
					name: '',
					url_name: '',
					description: '',
					range_time: [],
					start_time: '',
					end_time: '',
					dialogTitle: '新增活动',
					status: 1,
					miss_count: 1,
					miss_count_status: 1,
					channelLang: [],
					channelCurrent: ''
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
					channelLang: { required: true, message: '请至少选择一种渠道', trigger: 'change' },
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
				dialogChannelAddVisible: false,
				activityChannelForm: {
					editPlace: [],
					channelLang: [],
					channelLang_old: [],
					channelCurrent: ''
				},
				//editSupportChannelLang > 编辑渠道语言(obj)
				editSupportChannelObj: {},
				//子页面
				currentPageRow: {},
				pageForm: {
					id: '',
					title: '',
					keywords: '',
					url_name: '',
					description: '',
					statistics_code: '',
					tpl_id: '0',
					m_tpl_id: '0',
					app_tpl_id: '0',
					ios_tpl_id: '0',
					android_tpl_id: '0',
					tpl_name: '',
					m_tpl_name: '',
					app_tpl_name: '',
					ios_tpl_name: '',
					android_tpl_name: '',
					seo_title: '',

					pc_status: false,
					m_status: false,
					app_status: false,
					ios_status: false,
					android_status: false,
					pc_end_url: '',
					m_end_url: '',
					end_url: '',
					data: {},
					refresh_time: 0,
					end_time: '',
					dialogTitle: '子页面新增',
					redirect_url: '',
					obsPage: {
						selected: {},
					},
					need_redirect: false,
					//分享信息
					share: {
						checked: [],
						title: '',
						desc: '',
						img: '',
						integral: '',
						integral_times: '',
						fileList: []
					}
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
					title: [
						{ required: true, message: '请输入名称', trigger: 'blur' },
						{ max: 100, min: 1, message: '长度在100个字符以内', trigger: 'blur' }
					]
				},
				pageRules: {
					title: [
						{ required: true, message: '请输入名称', trigger: 'blur' },
						{ max: 100, min: 1, message: '长度在100个字符以内', trigger: 'blur' }
					],
					pc_activity_title: [
						{ required: true, message: '请选择PC端活动专题名称', trigger: 'change' }
					],
					m_activity_title: [
						{ required: true, message: '请选择M端活动专题名称', trigger: 'change' }
					],
					app_activity_title: [
						{ required: true, message: '请选择APP端活动专题名称', trigger: 'change' }
					],
					seo_title: [
						{ required: false, message: '请输入SEO标题', trigger: 'blur' }
					],
					url_name: [
						{ required: true, message: '请输入有效url地址', trigger: 'blur' },
						// {
						// 	pattern: /(?!^(\d+|[A-Za-z]+|[-]+)$)^[\w-]{3,64}$/,
						// 	message: '请输入3-64位的英文字母，-，数字的两种及以上组合',
						// 	trigger: 'blur'
						// }
					],
					keywords: [
						{ required: false, message: '有利于SEO优化', trigger: 'blur' },
						{ max: 200, min: 0, message: '长度在200个字符以内', trigger: 'blur' }
					],
					description: [
						{ required: false, message: '有利于SEO优化', trigger: 'blur' },
						// { max: 200, min: 0, message: '长度在200个字符以内', trigger: 'blur' }
						// { pattern: /[a-z\d-]{3,200}/, message: '请输入3-200位的英文字母，-，数字的两种及以上组合', trigger: 'blur' }
					],
					'share.integral': [
						{ type: 'number', message: '积分必须为数字值', trigger: 'blur', transform (value) { return Number(value); } }
					]
				},
				pickerOptions1: {
					disabledDate (time) {
						let currentDate = new Date(),
							year = currentDate.getFullYear(),
							month = currentDate.getMonth() + 6,
							day = currentDate.getDate(),
							hours = currentDate.getHours(),
							min = currentDate.getMinutes(),
							second = currentDate.getSeconds();
						if (month > 12) {
							month = month - 12;
							year += 1;
						}
						let lastDateTime = new Date(year + '-' + month + '-' + day + ' ' + hours + ':' + min + ':' + second).getTime();
						return (time.getTime() > lastDateTime) || (time.getTime() < currentDate.getTime() - 86400);
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
				dialogConvertAPP: false,
				convertForm_M: {
					id: '',
					activity_id: '',
					page_id: '',
					model: '1',
					is_group: 0,
					source_id: 0,
					target_id: 0,
					lang: '',
					//渠道语言列表
					pc_supportChannel: {},
					wap_supportChannel: {},
					source_channelLang: {},
					target_channelLang: {},
					source_channelCurrent: '',
					target_channelCurrent: '',
					//单渠道下单语言选中
					source_langCurrent: ''
				},
				convertForm: {
					id: '',
					activity_id: '',
					page_id: '',
					model: '1',
					is_group: 0,
					ios_is_group: 0,
					android_is_group: 0,
					source_id: 0,
					target_id: 0,
					ios_target_id: 0,
					android_target_id: 0,
					lang: '',
					wap_supportChannel: {},
					source_channelLang: {},
					source_channelCurrent: '',
					// target_channelLang: {},
					// target_channelCurrent: ''	,
					ios_supportChannel: {},
					ios_target_channelLang: {},
					ios_target_channelCurrent: '',
					android_target_channelLang: {},
					android_target_channelCurrent: '',
					android_supportChannel: {},
					//单渠道下单语言选中
					source_langCurrent: '',
					//ios,android活动列表id
					platformActivityPage: {
						ios: {
							activity_id: '',
							page_id: '',
							mActivityList: '',
							mActivities: '',
							mPages: ''
						},
						android: {
							activity_id: '',
							page_id: '',
							mActivityList: '',
							mActivities: '',
							mPages: ''
						}
					},
					hasToWap: false,
					hasToAndroid: false,
					hasToIos: false
				},
				convertForm_ios_android: {
					dialogTitle: 'IOS转Android',
					id: '',
					activity_id: '',
					page_id: '',
					model: '1',
					is_group: 0,
					source_id: 0,
					target_id: 0,
					lang: '',
					//渠道语言列表
					source_supportChannel: {},
					source_channelLang: {},
					source_channelCurrent: '',
					target_supportChannel: {},
					target_channelLang: {},
					target_channelCurrent: '',
					//单渠道下单语言选中
					source_langCurrent: '',
					submitType: 'ios2android'
				},
				convertRules_M: {
					activity_id: [
						{ required: true, message: '请选择活动', trigger: 'change' }
					],
					page_id: [
						{ required: true, message: '请选择页面', trigger: 'change' }
					]
				},
				convertRules: {
					activity_id: [
						{ required: true, message: '请选择活动', trigger: 'change' }
					],
					page_id: [
						{ required: true, message: '请选择页面', trigger: 'change' }
					],
					"convertForm['platformActivityPage']['ios'].activity_id": [
						{ required: true, message: '请选择活动', trigger: 'change' }
					]
				},
				convertFormCurrent: '',
				dialogConvertM: false,
				dialogConvertNative: false,
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
				dialogConvertIosVisible: false,
				dialogConvertAndroidVisible: false,
				convertUrl: '',
				convertMUrl: '',
				convertIosUrl: '',
				convertAndroidUrl: '',
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
				convertLangs: [],
				/* OBS选品 */
				obs: {
					selected: { value: '', id: '' },
					data: []
				},
				obsPage: {
					selected: { value: '', id: '' },
					data: [],
					pageLanguages: []
				},
				obsLoading: false,
				/* GB活动管理 */
				searchInfo: {
					searchObs: '',
					searchChannel: []
				},
				//所有端渠道语言
				allSupportChannelLang_res: {},
				//所有渠道语言
				allSupportChannelLang: {},
				editSupportChannelLang: [],
				//渠道空白语言列表
				channelLangInitial: {},
				//已选中语言
				editChannelLang: [],
				channelOptions: [
					{ key: 'gl_en', name: '全球渠道' },
					{ key: 'gl_aa', name: '国家站渠道' }
				],
				currentChannel: '',
				channelGroup: [
					{ key: 'gl_en', name: '全球渠道', langList: [] },
					{ key: 'gl_en', name: '国家站渠道', langList: [] }
				],
				channelGroupObj: {},
				publicPageForm: {
					place: [''],
					title: '',
					obsPage: {
						selected: {},
					},
				},
				channelCommon: {},
				/* share Message */
				share: {
					checked: [],
					title: '',
					desc: '',
					integral: '',
					img: '',
					integral_times: '',
					//上传图片
					fileList: [],
				},
				shareEntry: [
					{ key: 'fb', name: 'FB' },
					{ key: 'twitter', name: 'Twitter' },
					{ key: 'google', name: 'Google' },
					{ key: 'reddit', name: 'Reddit' },
					{ key: 'vk', name: 'VK' },
					{ key: 'telegram', name: 'Telegram' }
				],
				convertLangs: [],
				channelUrlList: [],
				dialogChannelVisible: false
			};
		},
		computed: {
			firstLanguage () {
				return '';
				// if (this.langList.length > 0) {
				// 	var firstLangu = this.langList[0].key
				// 	return firstLangu
				// } else {
				// 	return ''
				// }
			}
		},
		watch: {
			currentChannel: function (val) {
				if(this.allSupportChannelLang[val]){
					this.currentSiteUrl = this.allSupportChannelLang[val].url;
				}

			},
			activityTabName:function(val){
				if(this.allSupportChannelLang[this.currentChannel]){
					this.currentSiteUrl = this.allSupportChannelLang[this.currentChannel].url;
				}
			}

		},
		mounted () {

		},
		methods: {
			async getPageTemplates (scrollType) {
				this.tplInfo.loading = true;
				let _this = this;
				let pageNo = scrollType == 'scroll' ? this.tplInfo.pageNo : 1;
				let type = this.modelInfo.tabActive == '1' ? 1 : 0;

				let params = {
					// site_code: getCookie('SITECODE'),
					place: 1,
					type: type,
					pageNo: pageNo,
					pageSize: this.tplInfo.pageSize
				};

				// 如果是选择模板
				if (this.getTmpListStatus) {
					params.site_code = getCookie('site_group_code') + '-' + this.getTmpListValue;
				} else {
					params.site_code = getCookie('site_group_code') + '-' + this.activityTabName;
				}

				let res = await GB_getPageTemplateList(params);

				let data = res.data.list;
				this.tplInfo.totalCount = res.data.totalCount;
				this.tplInfo.maxPageNo = Math.ceil(res.data.totalCount / this.tplInfo.pageSize);
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
			async getActivities () {
				let params = {
						pageNo: this.currentPage,
						pageSize: 10,
						name: this.searchWord,
						create_name: this.createName,
						type: this.activityType,
						searchChannel: this.searchInfo.searchChannel,
						searchObs: this.searchInfo.searchObs,
						site_code: getCookie('site_group_code') + '-' + this.activityTabName,
						special_id: this.subpageId
						// page_id: this.subpageId
					},
					res = await GB_getActivityList(params);

				this.activityList = res.data.list;
				this.expandRowKeys = [this.activityList[0].id];
				this.getPages(this.expandRowKeys[0], this.subpageId);
				var array = [];
				for (var index in this.activityList) {
					array.push(this.activityList[index].id);
				}
				this.pageIds = array;
				this.total = res.data.pagination.totalCount;

				let length = res.data.list.length;

				for (index = 0; index < length; index++) {
					if (res.data.list[index].is_lock == 0) {
						res.data.list[index].lock_status = false;
					} else if (res.data.list[index].is_lock == 1) {
						res.data.list[index].lock_status = true;
					}
				}
			},

			async GB_getAppActivityList () {
				let res = await GB_getAppActivityList({});

				if (res.code == 0) {
					this.appActivityList = res.data;

					this.getAppActivities();
					this.getAppPages();
				}
			},
			//获取当前用户在当前站点platform端下的所有活动及页面列表
			async GB_getMActivityList (platform) {

				let res = await GB_getMActivityList({ platform: platform }, { parallel: true });
				let convertFormCurrent = this.convertFormCurrent;

				if (res.code == 0) {
					if (convertFormCurrent == 'convertForm' && platform) {
						this.convertForm['platformActivityPage'][platform].mActivityList = res.data;
					} else {
						this.mActivityList = res.data;
					}


					this.getMActivities(platform);
					this.getMPages(platform);
				}
			},
			// PC，M，APP切换
			handleActivityTabClick (event) {
				this.activityTabName = event.name;
				this.currentPage = 1;
				this.getActivities();
				//更新端下渠道
				this.getSupportChannels();
			},
			// handleCheckedPlacesChange(val) {
			// 	let arr = val
			// 	this.pageFormPlace = val // 保存当前选择的应用端口
			// 	this.pageForm.data[this.currentLanguage].place = val // 应用端口在每种语言下都可独立选择

			// 	if (arr.indexOf('pc') != -1) {
			// 		this.pc_status = true
			// 		this.pageForm.data[this.currentLanguage].pc_status = true
			// 	} else {
			// 		this.pc_status = false
			// 		this.pageForm.data[this.currentLanguage].pc_status = false
			// 	}

			// 	if (arr.indexOf('wap') != -1) {
			// 		this.m_status = true
			// 		this.pageForm.data[this.currentLanguage].m_status = true
			// 	} else {
			// 		this.m_status = false
			// 		this.pageForm.data[this.currentLanguage].m_status = false
			// 	}

			// 	if (arr.indexOf('app') != -1) {
			// 		this.app_status = true
			// 		this.pageForm.data[this.currentLanguage].app_status = true
			// 	} else {
			// 		this.app_status = false
			// 		this.pageForm.data[this.currentLanguage].app_status = false
			// 	}
			// },
			handlePCPlaceActivity (val) {
				this.pageForm.pc_activity_id = val;
				this.pageForm.data[this.currentLanguage].pc_activity_id = val;

				let pc_activity_title;
				this.pageForm.pc_activity_list.forEach((item) => {
					if (item.id == val) {
						pc_activity_title = item.name;
					}
				});
				this.pageForm.pc_activity_title = pc_activity_title;
				this.pageForm.data[this.currentLanguage].pc_activity_title = pc_activity_title;
			},
			handleMPlaceActivity (val) {

				this.pageForm.m_activity_id = val;
				this.pageForm.data[this.currentLanguage].m_activity_id = val;

				let m_activity_title;
				this.pageForm.m_activity_list.forEach((item) => {
					if (item.id == val) {
						m_activity_title = item.name;
					}
				});
				this.m_activity_title = m_activity_title;

				this.pageForm.m_activity_title = m_activity_title;
				this.pageForm.data[this.currentLanguage].m_activity_title = m_activity_title;
			},
			handleAppPlaceActivity (val) {

				this.pageForm.app_activity_id = val;
				this.pageForm.data[this.currentLanguage].app_activity_id = val;

				let app_activity_title;
				this.pageForm.app_activity_list.forEach((item) => {
					if (item.id == val) {
						app_activity_title = item.name;
					}
				});
				this.app_activity_title = app_activity_title;

				this.pageForm.app_activity_title = app_activity_title;
				this.pageForm.data[this.currentLanguage].app_activity_title = app_activity_title;
			},

			handleNameKeyup () {
				var data = this.activityForm.name.split('');
				var length = data.length;

				if (length > 100) {
					data.splice(100, length - 99);
					this.activityForm.name = data.join('');
				}

				this.actNameCount = data.length;
			},
			handleIntroductionKeyup () {
				var data = this.activityForm.description.split('');
				var length = data.length;

				if (length > 200) {
					data.splice(200, length - 199);
					this.activityForm.description = data.join('');
				}

				this.actIntroductionCount = data.length;
			},
			// 新增子页面
			handleTitleKeyup () {
				var data = this.pageForm.title.split('');
				var length = data.length;

				if (length > 100) {
					data.splice(100, length - 99);
					this.pageForm.title = data.join('');
				}

				this.titleCount = data.length;
			},
			handleUrlKeyup () {
/*				var data = this.pageForm.url_name.split('');
				var length = data.length;

				if (length > 64) {
					data.splice(64, length - 63);
					this.pageForm.url_name = data.join('');
				}

				this.urlCount = data.length;*/
			},
			handleCodeKeyup () {
				var data = this.pageForm.statistics_code.split('');
				var length = data.length;

				if (length > 200) {
					data.splice(200, length - 199);
					this.pageForm.statistics_code = data.join('');
				}

				this.codeCount = data.length;
			},
			handleDescriptionKeyup () {
				var data = this.pageForm.description.split('');
				var length = data.length;

				if (length > 200) {
					data.splice(200, length - 199);
					this.pageForm.description = data.join('');
				}

				this.pageIntroductionCount = data.length;
			},
			createActivity () {
				// 当前为新增活动状态
				this.activityForm.status = 1;
				this.activityForm.id = '';
				this.activityForm.type = '';
				this.activityForm.lang = ['en'];
				this.activityForm.start_time = '';
				this.activityForm.end_time = '';
				this.activityForm.name = '';
				this.activityForm.description = '';
				this.activityForm.range_time = ['', ''];
				this.actNameCount = 0;
				this.actIntroductionCount = 0;
				this.activityForm.dialogTitle = '新增活动';
				this.activityForm.miss_count = 1;
				this.activityForm.miss_count_status = 1;
				this.activityForm.site_code = '';

				let date = new Date();
				this.activityDefaultTime[0] = date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();
				this.activityForm.channelLang = this.channelLangInit(null, 'createActivity');
				/* 获取obs列表 */
				// if (this.siteInfo.site == 'gb') {
				// 	this.getObsTheme()
				// }

				// 新增活动默认选中所有端口
				let placeSelected = [];
				Object.keys(this.places).forEach((element) => {
					placeSelected.push(element);
				});
				this.activityForm.place = placeSelected;

				this.dialogActivityVisible = true;
			},
			async refreshHeadFoot () {
				if (this.siteInfo.isSuper != 1) {
					this.$message('该操作只有超级管理员才有权限!');
				} else {
					this.confirm('一键头尾刷新中，请在【系统日志】-任务日志里对刷新详情进行查看，是否前往查看？', async (vm) => {
						let res = await GB_refreshSite({
							// site_code: getCookie('SITECODE')
							site_code: getCookie('site_group_code') + '-' + this.activityTabName
						});

						vm.isDetailActive = false;

						if (res.code === 0) {
							window.location.href = '/base/task-log/index';
						} else {
							vm.$message({
								// type: 'warning',
								message: res.message
							});
						}
					});
				}
			},
			editActivity (row, is_lock, create_user) {
				if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					// 当前为编辑活动状态
					this.activityForm.status = 2;
					this.activityForm.id = row.id;
					this.activityForm.type = String(row.type);
					// this.activityForm.lang = String(row.lang).split(',')
					// this.activityForm.start_time = row.start_time * 1000
					// this.activityForm.end_time = row.end_time * 1000
					this.activityForm.name = row.name;

					this.activityForm.site_code = row.site_code.split('-')[1];
					this.activityForm.place = Array(row.site_code.split('-')[1]);

					// 新增活动时已选应用端口列表

					this.editPlaces = row.group_info.platform_list;
					// this.editSupportLangs = row.group_info.lang_list
					let editChannelLang = {};
					this.editSupportChannelLang = row.group_info.pipelineList;
					this.activityForm.channelCurrent = row.group_info.pipelineList[0].key;

					row.group_info.pipelineList.forEach((item, index) => {
						editChannelLang[item.key] = { selectAll: true, checkPartStatus: false, value: [] };
						item.langList.forEach((childItem) => {
							editChannelLang[item.key]['value'].push(childItem.key);
						});
					});

					// this.editChannelLang = editChannelLang
					this.activityForm.channelLang = editChannelLang;

					// 主活动已勾选端口
					let editPlaceArr = [];
					row.group_info.platform_list.forEach((item) => {
						if (item.selected == 1) {
							editPlaceArr.push(item.code);
						}
					});
					this.activityForm.editPlace = editPlaceArr;

					// 主活动已勾选语言
					// let editSupportLangArr = []
					// row.group_info.lang_list.forEach((item) => {
					// 	editSupportLangArr.push(item.key)
					// })
					// this.activityForm.editSupportLang = editSupportLangArr

					this.activityForm.description = row.description;
					// this.activityForm.range_time = [this.activityForm.start_time, this.activityForm.end_time]
					this.activityForm.refresh_time = row.refresh_time;
					this.activityForm.dialogTitle = '编辑活动';
					this.actNameCount = this.activityForm.name.split('').length;
					this.actIntroductionCount = this.activityForm.description.split('').length;

					/* obs回填 */
					if (this.siteInfo.site == 'gb') {
						// this.getObsTheme();
						if (row.themeList) {
							this.obs.data = [{ id: Number(row.themeList.theme_id), name: row.themeList.theme_name }];
							this.obs.selected = { id: Number(row.themeList.theme_id), name: row.themeList.theme_name };
						}

					}


					this.dialogActivityVisible = true;
				}
			},
			createPage (activityId, placeList, is_lock, create_user, row) {
				if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					this.currentPageRow = {};

					// 显示活动语言
					// this.langList = langList
					this.transformChannel(row.group_info.pipelineList);
					let channelGroup = this.channelGroup, channelGroupObj = this.channelGroupObj;
					/* end */
					// 新增活动时勾选的端口
					let placeSelected = [];
					let pagePlacesArr = [];
					placeList.forEach((item) => {
						if (item.selected == 1) {
							placeSelected.push(item);
							pagePlacesArr.push(item.code);
						}
					});
					this.pagePlaces = placeSelected;
					// 根据当前活动已勾选端口显示对应“跳转链接”和“页面模板”

					this.publicPageForm.place = pagePlacesArr;


					// this.currentLanguage = this.langList[0].key //'en'

					this.tplId = '0';
					this.urlName = '';
					this.refreshTime = 0;
					this.pageForm.id = '';
					this.pageForm.activity_id = activityId;
					this.pageForm.title = '';
					this.pageForm.seo_title = '';
					this.titleCount = 0;
					this.pageForm.keywords = '';
					this.pageForm.url_name = '';
					this.urlCount = 0;
					this.pageForm.description = '';
					this.pageIntroductionCount = 0;
					this.pageForm.statistics_code = '';
					this.codeCount = 0;
					this.pageForm.redirect_url = '';
					this.pageForm.refresh_time = 0;
					this.pageForm.tpl_id = '0'; // 每个语种都能选择自己的模板
					this.pageForm.m_tpl_id = '0'; // 每个语种都能选择自己的模板
					this.pageForm.app_tpl_id = '0'; // 每个语种都能选择自己的模板
					this.pageForm.ios_tpl_id = '0';
					this.pageForm.android_tpl_id = '0';
					this.pageForm.tpl_name = '未选中模板';
					this.pageForm.m_tpl_name = '未选中模板';
					this.pageForm.app_tpl_name = '未选中模板';
					this.pageForm.ios_tpl_name = '未选中模板';
					this.pageForm.android_tpl_name = '未选中模板';

					this.pageForm.pc_end_url = '';
					this.pageForm.m_end_url = '';

					this.pageForm.end_time = '';
					this.pageForm.end_url = '';
					this.pageForm.need_redirect = false;

					if (this.siteInfo.site == 'gb') {
						this.pageForm.obsId = '';
						this.pageForm.obsName = '';
					}

					this.pageForm.share = {
						entry: [
							{ key: 'FB', name: 'FB' },
							{ key: 'Twitter', name: 'Twitter' },
							{ key: 'Google', name: 'Google' },
							{ key: 'Reddit', name: 'Reddit' },
							{ key: 'VK', name: 'VK' },
							{ key: 'Telegram', name: 'Telegram' }
						],
						checked: [],
						title: '',
						desc: '',
						integral: '',
						img: '',
						integral_times: '',
						//上传图片
						fileList: [],
					};

					this.supportLangs.forEach((item, index) => {
						if (item.key == this.currentLanguage) {
							this.currentSiteUrl = item.url;
						}
					});

					let data = {};
					let _this = this;

					channelGroup.forEach((element, index) => {
						data[element.key] = {};
						element.langList.forEach((childItem) => {
							data[element.key][childItem.key] = {
								title: '',
								keywords: '',
								url_name: '',
								description: '',
								statistics_code: '',
								redirect_url: '',
								tpl_id: '0', // 模板id
								m_tpl_id: '0',
								app_tpl_id: '0',
								ios_tpl_id: '0',
								android_tpl_id: '0',
								tpl_name: '未选中模板', // 模板名称
								m_tpl_name: '未选中模板',
								app_tpl_name: '未选中模板',
								ios_tpl_name: '未选中模板',
								android_tpl_name: '未选中模板',
								activity_id: activityId,
								seo_title: '',
								end_url: '',
								pc_end_url: '',
								m_end_url: '',
								obsId: '',
								obsName: '',
								share: {
									checked: [],
									title: '',
									desc: '',
									img: '',
									integral: '',
									integral_times: '',
									fileList: []
								}
							};
						});
					});

					this.pageForm.data = data;
					/* 获取已选端口模板 */
					let totalPlace = ["pc", "wap", "ios", "android"];
					pagePlacesArr.forEach((item) => {
						let name = item == 'wap' ? 'm' : item;
						let filed = name + '_status';
						if (totalPlace.indexOf(item) != -1) {
							this.pageForm[filed] = true;
						} else {
							this.pageForm[filed] = false;
						}
					});

					this.currentTemplate = '未选中模板';

					this.pageForm.dialogTitle = '新增子页面';

					/* gb回填 */
					if (this.siteInfo.site == 'gb' && row && row.themeList) {
						this.getObsPage(row.themeList.theme_id);
						this.obs.selected = {
							id: Number(row.themeList.theme_id),
							name: row.themeList.theme_name
						};
					}

					let platformKeys = [];

					row.group_info.platform_list.forEach(function (element) {
						if (element.selected) {
							platformKeys.push(element.code);
						}
					});

					if (platformKeys.indexOf('pc') != -1 && platformKeys.indexOf('wap') == -1) {
						this.pageForm.need_redirect = true;
					}

					this.dialogPageVisible = true;
				}
			},
			editPage (row, is_lock, activity_create_user, langList, placeList) {
				let _this = this;
				if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					// this.currentLanguage = this.langList[0].key
					this.tplId = '';

					let lang = this.currentLanguage, channelGroup = this.channelGroup;
					this.currentPageRow = row;


					// 主活动语言
					this.langList = langList;

					// 当前子页面支持语言
					// let langList = this.langList

					// 主活动端口
					/**
					 * placeSelected 主活动端口对象
					 * 主活动对象key
					 */
					let placeSelected = [];
					let pagePlacesArr = [];
					placeList.forEach((item) => {
						if (item.selected == 1) {
							placeSelected.push(item);
							pagePlacesArr.push(item.code);
						}
					});
					this.pagePlaces = placeSelected;

					// 根据当前活动已勾选端口显示对应“跳转链接”和“页面模板”
					this.publicPageForm.place = pagePlacesArr;

					//渠道语言列表内容
					let data = {};
					// langList.forEach(function (element) {
					// 	data[element.key] = {
					// 		title: '',
					// 		url_name: '',
					// 		seo_title: '',
					// 		keywords: '',
					// 		description: '',
					// 		statistics_code: '',
					// 		pc_end_url: '',
					// 		m_end_url: '',
					// 		redirect_url: ''
					// 	}
					// })

					channelGroup.forEach((element, index) => {
						data[element.key] = {};
						element.langList.forEach((childItem) => {
							data[element.key][childItem.key] = {
								title: '',
								url_name: '',
								seo_title: '',
								keywords: '',
								description: '',
								statistics_code: '',
								end_url: '',
								pc_end_url: '',
								m_end_url: '',
								redirect_url: '',
								share: {
									checked: [],
									title: '',
									desc: '',
									img: '',
									integral: '',
									integral_times: '',
									fileList: []
								}
							};
						});
					});


					// k - pc, wap
					// key GB,GBEs,...
					// lang - en, es, ...
					let group_languages = this.currentPageRow.group_languages;
					let k = this.activityTabName;
					for (let key in group_languages[k]) {
						for (let lang in group_languages[k][key]['language']) {
							if (group_languages[k] && group_languages[k][key] && data[key]) {
								let $nowForm = group_languages[k][key]['language'][lang], $langForm = data[key][lang];
								if ($langForm) {
									$langForm.title = $nowForm.title;
									$langForm.url_name = $nowForm.url_name;
									$langForm.seo_title = $nowForm.seo_title;
									$langForm.keywords = $nowForm.keywords;
									$langForm.description = $nowForm.description;
									$langForm.statistics_code = $nowForm.statistics_code;
									$langForm.redirect_url = $nowForm.redirect_url;
									if ($nowForm.share) {
										$langForm.share = $nowForm.share;
									}
									// $langForm.end_time = _this.currentPageRow.end_time == '0' ? '' : _this.currentPageRow.end_time * 1000
									if (k == 'pc') {
										$langForm.pc_end_url = $nowForm.end_url;
									} else if (k == 'wap') {
										$langForm.m_end_url = $nowForm.end_url;
									}
									$langForm.end_url = $nowForm.end_url;
								}
							}

						}

					}


					/* 				for (let k in group_languages) {
									for (let key in group_languages[k]) {
										for (let lang in group_languages[k][key]['language']) {
											if (group_languages[k] && group_languages[k][key] && data[key]) {
												let $nowForm = group_languages[k][key]['language'][lang], $langForm = data[key][lang];
												if ($langForm) {
													$langForm.title = $nowForm.title
													$langForm.url_name = $nowForm.url_name
													$langForm.seo_title = $nowForm.seo_title
													$langForm.keywords = $nowForm.keywords
													$langForm.description = $nowForm.description
													$langForm.statistics_code = $nowForm.statistics_code
													$langForm.redirect_url = $nowForm.redirect_url
													if ($nowForm.share) {
														$langForm.share = $nowForm.share
													}
													// $langForm.end_time = _this.currentPageRow.end_time == '0' ? '' : _this.currentPageRow.end_time * 1000
													if (k == 'pc') {
														$langForm.pc_end_url = $nowForm.end_url
													} else if (k == 'wap') {
														$langForm.m_end_url = $nowForm.end_url
													}
													$langForm.end_url = $nowForm.end_url
												}
											}

										}

									}
								} */

					if (_this.siteInfo.site == 'gb') {

						this.currentPageRow.pageLanguages.forEach(function (element) {

							if (element.themeList && data[element.pipeline][element.lang]) {
								data[element.pipeline][element.lang].obsPage = {
									selected: {
										id: Number(element.themeList.page_id),
										name: element.themeList.page_name
									},
									oldSelected: {
										id: Number(element.themeList.page_id),
										name: element.themeList.page_name
									},
								};
							}
						});
					}


					this.pageForm.id = this.currentPageRow.id;
					this.pageForm.need_redirect = false;
					this.pageForm.data = data;


					/* 获取应用端口模板 */
					let totalPlace = ["pc", "wap", "ios", "android"];
					pagePlacesArr.forEach((item) => {
						let name = item == 'wap' ? 'm' : item;
						let filed = name + '_status';
						if (totalPlace.indexOf(item) != -1) {
							this.pageForm[filed] = true;
						} else {
							this.pageForm[filed] = false;
						}
					});

					let $selectForm;
					// let $selectForm = this.pageForm.data[this.currentChannel][lang]
					if (group_languages[this.activityTabName][this.currentChannel] && group_languages[this.activityTabName][this.currentChannel]['language'][lang]) {
						$selectForm = group_languages[this.activityTabName][this.currentChannel]['language'][lang];
					} else {
						$selectForm = this.pageForm.data[this.currentChannel][lang];
					}


					this.pageForm.title = $selectForm.title;
					this.titleCount = $selectForm.title.split('').length;

					this.pageForm.keywords = $selectForm.keywords;
					this.pageForm.redirect_url = $selectForm.redirect_url;

					this.pageForm.url_name = $selectForm.url_name;
					this.urlCount = $selectForm.url_name.split('').length;

					this.pageForm.description = $selectForm.description;
					this.pageIntroductionCount = $selectForm.description.split('').length;

					this.pageForm.statistics_code = $selectForm.statistics_code;
					this.codeCount = $selectForm.statistics_code.split('').length;

					this.pageForm.activity_id = this.currentPageRow.activity_id;
					this.pageForm.refresh_time = this.currentPageRow.refresh_time;
					this.pageForm.end_time = this.currentPageRow.end_time == '0' ? '' : this.currentPageRow.end_time * 1000; // end_time设为空日期选择器从当前时间选择，设为0不会从当前时间选择！

					this.pageForm.end_url = $selectForm.end_url;
					// this.pageForm.pc_end_url = $selectForm.pc_end_url
					// this.pageForm.m_end_url = $selectForm.m_end_url

					this.pageForm.seo_title = $selectForm.seo_title;

					this.urlName = this.currentPageRow.url_name;
					this.refreshTime = this.currentPageRow.refresh_time;
					this.tplId = this.currentPageRow.tplId;
					this.end_time = this.currentPageRow.end_time == '0' ? '' : this.currentPageRow.end_time * 1000;

					this.pageForm.dialogTitle = '编辑子页面';

					/* 回填分享信息 */
					this.pageForm.share = $selectForm.share;
					this.pageForm.share['fileList'] = [];
					/* 增加自定义url id */
					this.pageForm.url_name_id = row.special_id;

					/* gb回填 子页面只存在英文*/
					if (this.siteInfo.site == 'gb' && row && row.pageLanguages) {
						let channelFirst = this.channelGroup[0].key,
							langFirst = this.channelGroup[0].langList[0].key,
							list;
						// let langFirst = this.langList[0].key
						// row.pageLanguages.forEach(function (item) {
						// 	//item.pipeline === channelFirst && item.lang === langFirst
						// 	if (item.pipeline === channelFirst) {
						// 		list = item.themeList
						// 	}
						// })

						list = row.pageLanguages[0].themeList;
						if (list && list.theme_id) {
							this.getObsPage(list.theme_id);
							this.obs.selected = {
								id: Number(list.theme_id),
								name: list.theme_name
							};
						}

						this.obsPage.pageLanguages = list.pageLanguages;
						this.obsPage.selected.id = Number(list.page_id);
						this.pageForm.obsPage = {
							selected: {
								id: Number(list.page_id),
								name: list.page_name
							},
							oldSelected: {
								id: Number(list.page_id),
								name: list.page_name
							}
						};
					}

					let platformKeys = Object.keys(row.group_info.platform_list);

					if (platformKeys.indexOf('pc') != -1 && platformKeys.indexOf('wap') == -1) {
						this.pageForm.need_redirect = true;
					}

					this.dialogPageVisible = true;
				}
			},
			async getPages (activityId, special_id) {
				let params = {
					activity_id: activityId
				};
				if (special_id) {
					params.special_id = special_id;
				}
				let res = await GB_getPageList(params);

				let position, data, pages, isEnglishSetted = false;

				pages = res.data.list;
				if (pages.length <= 0) {
					return false;
				}
				pages.forEach((element) => {
					element.title = element.pageLanguages[0].title;
				});
				/* 			let firstLang = res.data.langList[0].key

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
						}) */


				this.transformChannel(res.data.langList);

				this.activityList.forEach(function (element, index) {
					if (element.id == activityId) {
						element.children = pages;
						data = element;
						position = index;
					}
				});

				this.$set(this.activityList, position, data);
			},
			viewDetail (row) {
				this.currentPageRow = {};
				this.currentActivityRow = row;
				this.isDetailActive = true;
				this.langList = row.langList;
			},
			handleExpandChange (row) {
				if (!row.children) {
					this.getPages(row.id);
				}

				this.langList = row.langList;

				if (this.expandRowKeys.indexOf(row.id) === -1) {
					this.expandRowKeys = [row.id];
				} else {
					this.expandRowKeys = [];
				}
			},
			handleCurrentChange (currentPage) {
				this.currentPage = currentPage;
				this.getActivities();
			},
			/**
			 * 删除数组指定元素
			 */
			handleSpliceArrayItem (arr, key) {
				let arrays = arr;
				let index = arrays.indexOf(key);
				if (index > -1) {
					arrays = arrays.splice(index, 1);
				}
				return arrays;
			},
			transformChannel (transList) {
				/* 渠道列表 start*/
				/**
				 * transList 需转换的渠道数据对象
				 * channelGroup 渠道语言列表
				 * channelGroupObj 渠道语言列表对象
				 * currentChannel 当前渠道
				 * channelCommon 渠道下公共数据
				 */
				let channelGroup = transList, channelGroupObj = {}, channelCommon = {};
				this.channelGroup = channelGroup;
				this.currentChannel = channelGroup[0].key;
				this.currentLanguage = channelGroup[0].langList.length > 0 ? channelGroup[0].langList[0].key : '';
				channelGroup.forEach((element) => {
					channelGroupObj[element.key] = {
						key: element.key,
						name: element.name,
						langList: {}
					};
					element.langList.forEach((item) => {
						channelGroupObj[element.key].langList[item.key] = item;
					});
				});

				Object.keys(channelGroupObj).forEach((element) => {
					let langFirst = Object.keys(channelGroupObj[element].langList)[0];
					channelCommon[element] = {
						defaultLang: langFirst || '',
						url: ''
					};
				});

				this.channelGroupObj = channelGroupObj;
				this.channelCommon = channelCommon;
			},
			/**
			 * 表单提交
			 * submitType (ios2android,android2ios)
			 */
			async submitForm (formName, submitType) {
				if (formName == 'activityForm') {
					this.setActivityTime();

					if (this.activityForm.start_time == '' || this.activityForm.end_time == '') {
						this.activityForm.range_time = '';
					}
				}

				this.submitLoading = true;

				this.$refs[formName].validate(async (valid) => {
					let pageFormStatus = true;
					if (valid) {
						let _this = this;
						let params, res;
						let activityFormObj = {};
						let platformData = {};

						if (formName == 'activityForm') {
							let platForm = this.activityForm.id == '' ? (this.activityForm.place).toString() : (this.activityForm.editPlace).toString();
							params = {
								type: this.activityForm.type,
								name: this.activityForm.name,
								description: this.activityForm.description,
								start_time: this.formatTimestamp(this.activityForm.start_time),
								end_time: this.formatTimestamp(this.activityForm.end_time),
								platForm: platForm
							};
							//获取已选渠道+语言
							let channelLang = this.activityForm.channelLang,
								channelLangKeys = Object.keys(channelLang),
								channelLangObj = {};
							channelLangKeys.forEach((item, index) => {
								if (channelLang[item].value.length > 0) {
									channelLangObj[item] = channelLang[item].value;
								}
							});
							if (Object.keys(channelLangObj).length <= 0) {
								this.$message({
									message: '请至少选择一种渠道语言',
									type: 'warning'
								});
								this.submitLoading = false;
								return false;
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
									site_code: getCookie('site_group_code') + '-' + item,
									obsId: this.obs.selected.id,
									obsName: this.obs.selected.name,
									pipeline: channelLangObj
								};
							});

							platformData['platform_list'] = JSON.stringify(activityFormObj);
							platformData['platform'] = this.activityForm.place.toString();

							// 保存分“新增”和“编辑”两种逻辑
							if (this.activityForm.id == '') {
								//新增确认提示
								this.$confirm('请确定此活动应用的渠道和端口信息是否填写完整，编辑时不可对活动应用的端口和渠道的及语言信息进行修改', '提示', {
									confirmButtonText: '确定',
									cancelButtonText: '取消',
									type: 'warning'
								}).then(async () => {
									// 首次提交code返回101时
									if (this.activityForm.miss_count_status > 1) {
										platformData['miss_count'] = ++this.activityForm.miss_count;
										res = await GB_addActivity(platformData);
									} else {
										res = await GB_addActivity(platformData);
									}

									if (res.code == 0) {
										this.getActivities();
									}
									// code - 101 端口没有相关语言仍要提交状态
									else if (res.code == 101) {
										this.activityForm.miss_count_status = 2;
										this.activityForm.miss_count = res.data.miss_count;
									}

									if (params.id) {
										this.expandRowKeys = [];
									}
									//补充回调
									this.formSubmitCallback(formName, res);
								}).catch(() => {
									this.$message({
										type: 'info',
										message: '已取消操作!'
									});
									this.submitLoading = false;
									return false;
								});

							} else {
								params.id = this.activityForm.id;
								if (this.siteInfo.site == 'gb') {
									params.obsId = this.obs.selected.id;
									params.obsName = this.obs.selected.name;
								}
								res = await GB_updateActivity(params);

								if (res.code == 0) {
									this.getActivities();
								}
								// code - 101 端口没有相关语言仍要提交状态
								else if (res.code == 101) {
									this.activityForm.miss_count_status = 2;
									this.activityForm.miss_count = res.data.miss_count;
								}

								if (params.id) {
									this.expandRowKeys = [];
								}
							}

						} else if (formName == 'pageForm') {
							/**
							 * 子页面数据
							 */

							//验证端口选中
							if (this.publicPageForm.place.length <= 0) {
								this.$message.warning('端口必选且至少选一个');
								this.submitLoading = false;
								return false;
							}

							let jsonObject = {};
							//遍历渠道
							// this.channelGroup.forEach((element) => {
							// 	let channelItem = element.key
							// 	activityFormObj[channelItem] = {
							// 		defaultLang: '',
							// 		languages: {}
							// 	}
							// })
							this.channelGroup.forEach((element) => {
								let channelItem = element.key;
								let langList = element.langList;

								activityFormObj[channelItem] = {
									defaultLang: '',
									languages: {}
								};
								activityFormObj[channelItem].defaultLang = _this.channelCommon[channelItem].defaultLang;
								/* 遍历当前语言 */
								langList.forEach((lang) => {
									let param = {},
										key = lang['key'],
										formData = this.pageForm.data[channelItem][key];
									formData.obsId = _this.pageForm.obsPage ? _this.pageForm.obsPage.selected.id : '';
									formData.obsName = _this.pageForm.obsPage ? _this.pageForm.obsPage.selected.name : '';

									activityFormObj[channelItem]['languages'][key] = {};
									/* 遍历端口 this.pagePlaces*/
									this.publicPageForm.place.forEach((it) => {
										let code = it;
										param[code] = JSON.parse(JSON.stringify(formData));
										// 删除无用字段
										delete param[code].title;
										delete param[code].url_name;
										delete param[code].seo_title;
										delete param[code].keywords;
										delete param[code].statistics_code;
										delete param[code].description;
										delete param[code].redirect_url;
										delete param[code].activity_id;

										// 根据当前端口是pc，m或app保留对应字段值
										if (code == 'pc') {
											// param[code].end_url = param[code].pc_end_url // 将pc端的end_url重命名
											param[code].end_url = param[code].end_url;
										} else if (code == 'wap') {
											// param[code].end_url = param[code].m_end_url // 将m端的end_url重命名
											param[code].end_url = param[code].end_url;
											param[code].tpl_id = param[code].m_tpl_id;
											param[code].tpl_name = param[code].m_tpl_name;
										} else if (code == 'app') {
											param[code].tpl_id = param[code].app_tpl_id;
											param[code].tpl_name = param[code].app_tpl_name;
										} else if (code == 'ios') {
											param[code].tpl_id = param[code].ios_tpl_id;
											param[code].tpl_name = param[code].ios_tpl_name;
										} else if (code == 'android') {
											param[code].tpl_id = param[code].android_tpl_id;
											param[code].tpl_name = param[code].android_tpl_name;
										}

										// 重命名后删除字段
										delete param[code].m_end_url;
										delete param[code].pc_end_url;
										delete param[code].m_tpl_id;
										delete param[code].m_tpl_name;
										delete param[code].app_tpl_id;
										delete param[code].app_tpl_name;
										delete param[code].ios_tpl_id;
										delete param[code].ios_tpl_name;
										delete param[code].android_tpl_id;
										delete param[code].android_tpl_name;
										//删除obs选中字段
										delete param[code].obsPage;

										//删除分享多余字段
										delete param[code]['share'];
									});
									let $langForm = {};
									let $langSelect = _this.pageForm.data[channelItem][key];
									$langForm = {
										title: _this.pageForm.title,
										url_name: $langSelect.url_name ? $langSelect.url_name : _this.pageForm.url_name,
										seo_title: $langSelect.seo_title ? $langSelect.seo_title : _this.pageForm.seo_title,
										keywords: $langSelect.keywords,
										statistics_code: $langSelect.statistics_code,
										description: $langSelect.description,
										redirect_url: $langSelect.redirect_url,
										platform: param,
										share: $langSelect.share
									};
									if (this.siteInfo.site == 'gb') {
										$langForm.obsId = _this.pageForm.obsPage ? _this.pageForm.obsPage.selected.id : '';
										$langForm.obsName = _this.pageForm.obsPage ? _this.pageForm.obsPage.selected.name : '';
										// $langForm.obsId = $langSelect.obsPage ? $langSelect.obsPage.selected.id : ''
										// $langForm.obsName = $langSelect.obsPage ? $langSelect.obsPage.selected.name : ''
									}

									activityFormObj[channelItem]['languages'][key] = $langForm;
								});
							});


							jsonObject['end_time'] = this.pageForm.end_time / 1000;
							jsonObject['platForm'] = this.publicPageForm.place.toString();

							let postData = {};

							for (let key in activityFormObj) {
								postData[key] = activityFormObj[key];
							}
							jsonObject['lang_list'] = JSON.stringify(postData);

							// 编辑
							if (this.pageForm.id != '') {
								// 编辑子页面传page_id
								jsonObject['page_id'] = this.pageForm.id;
								res = await GB_batchEditPage(jsonObject);
							}
							// 新增
							else {
								let flag = true;
								for (let item in this.pageForm.data) {
									if (this.pageForm.data[item].title == '') {
										this.$message.error('请确认所有语言所有必填项已经填写，再提交！');
										if (flag) {
											flag = false;
										}
									}
								}
								if (!flag) {
									this.submitLoading = false;
									return false;
								}
								// 新增子页面传activity_id
								jsonObject['activity_id'] = this.pageForm.activity_id;
								res = await GB_batchAddPage(jsonObject);
							}

							if (res.code == 0) {
								this.getPages(this.pageForm.activity_id);
							}
						} else if (formName == 'convertForm') {
							let $form = this.convertForm;
							params = {
								// source_id: this.convertForm.id,
								// target_id: this.convertForm.page_id,
								model: $form.model
							};
							/**
							 * 获取已选渠道+语言
							 * source_channel_keys 被转化渠道keys
							 */

							let $source = {}, $target_ios = {}, $target_android = {}, sourceSingle = {};
							let source_id = $form.source_id,
								ios_target_id = $form.ios_target_id,
								android_target_id = $form.android_target_id,


								source_channelLang = $form.source_channelLang,
								ios_target_channelLang = $form.ios_target_channelLang,
								android_target_channelLang = $form.android_target_channelLang,
								source_channel_keys = Object.keys(source_channelLang),
								ios_Channel_keys = Object.keys(ios_target_channelLang),
								android_Channel_keys = Object.keys(android_target_channelLang),
								source_channelLangObj = {},
								ios_target_channelLangObj = {},
								android_target_channelLangObj = {};

							source_channel_keys.forEach((item, index) => {
								if (source_channelLang[item].length > 0) {
									source_channelLangObj[item] = source_channelLang[item];
									$source[item] = {
										id: source_id[item].page_id,
										lang: source_channelLang[item]
									};
								}
							});

							ios_Channel_keys.forEach((item, index) => {
								if (ios_target_channelLang[item].length > 0) {
									ios_target_channelLangObj[item] = ios_target_channelLang[item];
									$target_ios[item] = {
										id: ios_target_id[item].page_id,
										lang: ios_target_channelLang[item]
									};
								}
							});

							android_Channel_keys.forEach((item, index) => {
								if (android_target_channelLang[item].length > 0) {
									android_target_channelLangObj[item] = android_target_channelLang[item];
									$target_android[item] = {
										id: android_target_id[item].page_id,
										lang: android_target_channelLang[item]
									};
								}
							});

							if (!$form.source_langCurrent || $form.hasToIos && Object.keys(ios_target_channelLangObj).length <= 0 || $form.hasToAndroid && Object.keys(android_target_channelLangObj).length <= 0) {
								this.$message({
									message: '请至少选择一种渠道语言',
									type: 'warning'
								});
								this.submitLoading = false;
								return false;
							}

							sourceSingle[$form.source_channelCurrent] = {
								id: source_id[$form.source_channelCurrent].page_id,
								lang: $form.source_langCurrent
							};
							// params.source = JSON.stringify(source)

							params.source = JSON.stringify(sourceSingle);
							params.target = JSON.stringify({
								ios: $target_ios,
								android: $target_android
							});


							res = await GB_convertToAppPage(params);

							if (res.code == 0) {
								this.convertUrl = res.data.redirectUrl;
								this.dialogConvertVisible = true;
							} else {
								this.convertUrl = '';
							}
						} else if (formName == 'convertForm_M') {
							/* PC转M 提交 */
							let $form = this.convertForm_M;
							params = {
								// source_id: this.convertForm_M.id,
								// target_id: this.convertForm_M.page_id,
								model: $form.model
							};

							//获取已选渠道+语言
							let source = {}, target = {}, sourceSingle = {};
							let source_id = $form.source_id;
							let target_id = $form.target_id;
							let source_channelLang = $form.source_channelLang,
								target_channelLang = $form.target_channelLang,
								source_channel_keys = Object.keys(source_channelLang),
								wap_Channel_keys = Object.keys(target_channelLang),
								source_channelLangObj = {},
								target_channelLangObj = {};

							source_channel_keys.forEach((item, index) => {
								if (source_channelLang[item].length > 0) {
									source_channelLangObj[item] = source_channelLang[item];
									source[item] = {
										id: source_id[item].page_id,
										lang: source_channelLang[item]
									};
								}
							});

							wap_Channel_keys.forEach((item, index) => {
								if (target_channelLang[item].length > 0) {
									target_channelLangObj[item] = target_channelLang[item];
									target[item] = {
										id: target_id[item].page_id,
										lang: target_channelLang[item]
									};
								}
							});

							// if (Object.keys(source_channelLangObj).length <= 0 || Object.keys(target_channelLangObj).length <= 0) {
							if (!$form.source_langCurrent || Object.keys(target_channelLangObj).length <= 0) {
								this.$message({
									message: '请至少选择一种渠道语言',
									type: 'warning'
								});
								this.submitLoading = false;
								return false;
							}

							sourceSingle[$form.source_channelCurrent] = {
								id: source_id[$form.source_channelCurrent].page_id,
								lang: $form.source_langCurrent
							};
							// params.source = JSON.stringify(source)
							params.source = JSON.stringify(sourceSingle);
							params.target = JSON.stringify(target);

							res = await GB_convertToMPage(params);

							if (res.code == 0) {
								if (res.data) {
									this.convertMUrl = res.data.redirectUrl;
									this.siteCode = res.data.siteCode;
								}

								// site_group_code显示是站点名称，三端合并版本PC、M、APP转换不需要传site code
								// setCookie('site_group_code', this.siteCode)
								// setCookie('SITECODE', this.siteCode)

								this.dialogConvertMVisible = true;
							} else {
								this.convertMUrl = '';
							}
						} else if (formName == 'convertForm_ios_android') {

							/* PC转M 提交 */
							let $form = this.convertForm_ios_android;
							params = {
								// source_id: this.convertForm_M.id,
								// target_id: this.convertForm_M.page_id,
								model: $form.model
							};

							//获取已选渠道+语言
							let source = {}, target = {}, sourceSingle = {};
							let source_id = $form.source_id;
							let target_id = $form.target_id;
							let source_channelLang = $form.source_channelLang,
								target_channelLang = $form.target_channelLang,
								source_channel_keys = Object.keys(source_channelLang),
								target_Channel_keys = Object.keys(target_channelLang),
								source_channelLangObj = {},
								target_channelLangObj = {};

							source_channel_keys.forEach((item, index) => {
								if (source_channelLang[item].length > 0) {
									source_channelLangObj[item] = source_channelLang[item];
									source[item] = {
										id: source_id[item].page_id,
										lang: source_channelLang[item]
									};
								}
							});

							target_Channel_keys.forEach((item, index) => {
								if (target_channelLang[item].length > 0) {
									target_channelLangObj[item] = target_channelLang[item];
									target[item] = {
										id: target_id[item].page_id,
										lang: target_channelLang[item]
									};
								}
							});

							// if (Object.keys(source_channelLangObj).length <= 0 || Object.keys(target_channelLangObj).length <= 0) {
							if (!$form.source_langCurrent || Object.keys(target_channelLangObj).length <= 0) {
								this.$message({
									message: '请至少选择一种渠道语言',
									type: 'warning'
								});
								this.submitLoading = false;
								return false;
							}

							sourceSingle[$form.source_channelCurrent] = {
								id: source_id[$form.source_channelCurrent].page_id,
								lang: $form.source_langCurrent
							};

							target = submitType == 'ios2android' ? { 'android': target } : { 'ios': target };
							// params.source = JSON.stringify(source)
							params.source = JSON.stringify(sourceSingle);
							params.target = JSON.stringify(target);

							res = await GB_convertToIosOrAndroid(params);

							if (res.code == 0) {
								this.convertIosUrl = res.data.redirectUrl;
								this.convertAndroidUrl = res.data.redirectUrl;
								this.siteCode = res.data.siteCode;

								// site_group_code显示是站点名称，三端合并版本PC、M、APP转换不需要传site code
								// setCookie('site_group_code', this.siteCode)
								// setCookie('SITECODE', this.siteCode)
								if (submitType == 'ios2android') {
									this.dialogConvertAndroidVisible = true;
								} else if (submitType == 'android2ios') {
									this.dialogConvertIosVisible = true;
								}

							} else {
								this.convertIosUrl = '';
								this.convertAndroidUrl = '';
							}
						}

						//活动提交表单回调,非新增活动的回调
						if (formName == 'activityForm' && this.activityForm.id == '') {
							return false;
						}
						this.formSubmitCallback(formName, res);

					} else {
						this.submitLoading = false;
					}
				});
			},
			/**
			 * 活动表单提交后回调
			 */
			formSubmitCallback (formName, res) {
				this.isDetailActive = false;
				if (res.code == 0) {
					this.resetFields(formName);
					this.closeDialog(formName);

				} else {
					this.submitLoading = false;
				}
			},
			resetForm (formName) {
				this.resetFields(formName);
				this.closeDialog(formName);
			},
			resetFields (formName) {
				this.$refs[formName].resetFields();
			},
			closeDialog (formName) {
				let formMap = {
					'activityForm': 'dialogActivityVisible',
					'pageForm': 'dialogPageVisible',
					'convertForm': 'dialogConvertAPP',
					'convertForm_M': 'dialogConvertM',
					'convertForm_ios_android': 'dialogConvertNative',
					'activityChannelForm': 'dialogChannelAddVisible',
				};
				this[formMap[formName]] = false;

				this.submitLoading = false;
			},
			async GB_verifyActivity (id, status, is_lock, create_user) {
				if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					var tip = '';

					if (status == 2) {
						tip = '确认上线该活动吗？';
					} else {
						tip = '确认下线该活动？下线后，该活动的所有页面将下线';
					}

					this.confirm(tip, async (vm) => {
						let params = {
							id: id,
							status: status
						};
						await GB_verifyActivity(params);
						vm.isDetailActive = false;
						vm.getActivities();
						vm.expandRowKeys = [];
					});
				}
			},
			async removeActivity (id, is_lock, create_user) {
				if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					this.confirm('活动删除后，其所有的子页面也将被删除，确认删除？', async (vm) => {
						let params = {
								id: id
							},
							res = await GB_deleteActivity(params);

						vm.isDetailActive = false;
						vm.getActivities();

						if (res.code === 0) {
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
			async removePage (id, is_lock, activity_create_user) {
				if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					let deleteChannelTemList = [];
					let params = {
						group_id: id
					};
					let res = await GB_getChannelLangList (params);
					this.dialogDeleteVisible = true;
					for (let item in res.data) {
						let obj = {
							key: res.data[item].key,
							langList: res.data[item].langList,
							name: res.data[item].name,
							page_id: res.data[item].page_id,
						};
						deleteChannelTemList.push(obj);
					};
					this.deleteChannelList = deleteChannelTemList;
					this.deleteChannelList.forEach(item => {
						this.deleteCheckAll.push(item.page_id);
					})
				}
			},
			handleDeleteCheckAll(val) {
				this.checkedCities = val ? this.deleteCheckAll : [];
			},
			handleDeleteChange(value) {
				let checkedCount = value.length;
				this.checkAll = checkedCount === this.deleteCheckAll.length;
			},
			async deleteVisibleComfirm () {
				if (this.checkedCities.length == 0) {
					 this.$message('请选择需要删除的页面！');
					 return false;
				} else {
					let params = {
						ids: this.checkedCities.join(',')
					};
					let res = await GB_getChannelDelList(params);
					if (res.code == 0) {
						this.dialogDeleteVisible = false;
					}
				}
			},
			confirm (message, callback) {
				this.$confirm(message, '提示', {
					confirmButtonText: '确定',
					cancelButtonText: '取消',
					type: 'warning'
				}).then(() => {
					if (typeof callback == 'function') {
						callback(this);
					}
				}).catch(() => {
					this.$message({
						type: 'info',
						message: '已取消操作!'
					});
				});
			},
			formatTimestamp (timestamp) {
				return parseInt(timestamp / 1000);
			},
			// viewPages (urls) {
			// 	this.pageLinks = urls
			// 	this.dialogLinksVisible = true
			// },
			async viewPages (id) {
				let params = {
						id: id
					},
					res = await GB_getAccessLink(params);
				if (res.code == 0) {
					this.dialogLinksVisible = true;
					this.pageLinks = res.data.list;
					this.tips = res.data.tips;
					this.urlID = id;
				}
			},
			// 重新发布
			async redistribution () {
				let params = {
						page_id: this.urlID
					},
					res = await GB_actReleased(params);
				this.dialogLinksVisible = false;
			},
			doSearch () {
				this.currentPage = 1;
				this.getActivities();
			},
			redirect (url) {
				window.open(url);
			},
			decorate_redirect (url, is_lock, activity_create_user) {
				if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					window.open(url);
				}
			},
			/**
			 * 三端合并后，PC转M端，M转APP端不再需要传siteCode值
			 */
			convertRedirect () {
				window.location.href = this.convertUrl;
			},
			//PC转M端 进入装修页面
			convertMRedirect () {
				window.location.href = this.convertMUrl;
			},
			//PC转M端 进入装修页面
			convertNativeRedirect (type) {

				window.location.href = type == 'ios' ? this.convertIosUrl : this.convertAndroidUrl;
			},
			handleTabClick (tab, type, event) {
				let tabName = tab.name,
					currentChannel = this.currentChannel,
					currentLanguage = this.currentLanguage,
					channelGroupObj = this.channelGroupObj,
					currentChannelLangList = channelGroupObj[currentChannel].langList,
					currentFirstLang = Object.keys(currentChannelLangList)[0],
					currentDefaultLang = this.channelCommon[currentChannel].defaultLang;
				if (!currentChannelLangList[currentLanguage] || type == 'channel') {
					this.currentLanguage = currentFirstLang;
				}

				let lang = this.currentLanguage;
				let $tabSelected = this.pageForm.data[currentChannel][lang];
				let commonData = {
					title: this.pageForm.title,
					obsPage: this.pageForm.obsPage,
					url_name: $tabSelected && $tabSelected.url_name ? $tabSelected.url_name : this.pageForm.url_name
				};

				/* 同渠道同步数据  langSyncData*/
				let $defaultLangForm = this.pageForm.data[currentChannel][currentDefaultLang];
				let langSyncData = {
					tpl_id: $tabSelected.tpl_id && $tabSelected.tpl_id != '0' ? $tabSelected.tpl_id : $defaultLangForm.tpl_id,
					m_tpl_id: $tabSelected.m_tpl_id && $tabSelected.m_tpl_id != '0' ? $tabSelected.m_tpl_id : $defaultLangForm.m_tpl_id,
					ios_tpl_id: $tabSelected.ios_tpl_id && $tabSelected.ios_tpl_id != '0' ? $tabSelected.ios_tpl_id : $defaultLangForm.ios_tpl_id,
					android_tpl_id: $tabSelected.android_tpl_id && $tabSelected.android_tpl_id != '0' ? $tabSelected.android_tpl_id : $defaultLangForm.android_tpl_id,
					tpl_name: $tabSelected.tpl_name && $tabSelected.tpl_name != '未选中模板' ? $tabSelected.tpl_name : $defaultLangForm.tpl_name,
					m_tpl_name: $tabSelected.m_tpl_name && $tabSelected.m_tpl_name != '未选中模板' ? $tabSelected.m_tpl_name : $defaultLangForm.m_tpl_name,
					ios_tpl_name: $tabSelected.ios_tpl_name && $tabSelected.ios_tpl_name != '未选中模板' ? $tabSelected.ios_tpl_name : $defaultLangForm.ios_tpl_name,
					android_tpl_name: $tabSelected.android_tpl_name && $tabSelected.android_tpl_name != '未选中模板' ? $tabSelected.android_tpl_name : $defaultLangForm.android_tpl_name
				};
				this.resetFields('pageForm');
				this.pageForm = Object.assign(this.pageForm, commonData);

				if (type == 'lang') {
					this.pageForm.data[currentChannel][lang] = Object.assign(this.pageForm.data[currentChannel][lang], langSyncData);
				}
				// if (type == 'channel') {
				// 	this.pageForm = Object.assign(this.pageForm, commonData)
				// } else if (type == 'lang') {
				// 	this.pageForm = Object.assign(this.pageForm, commonData, langSyncData)
				// }


				if (!$tabSelected.url_name) {
					$tabSelected.url_name = this.pageForm.url_name;
				}

				// this.prevLanguage = this.lang

				// this.pageForm.title = $tabSelected.title
				this.titleCount = $tabSelected.title.split('').length;

				this.pageForm.keywords = $tabSelected.keywords;
				this.pageForm.description = $tabSelected.description;
				this.pageIntroductionCount = $tabSelected.description.split('').length;

				this.pageForm.statistics_code = $tabSelected.statistics_code;
				this.codeCount = $tabSelected.statistics_code.split('').length;
				// this.pageForm.tplId = this.tplId
				// this.pageForm.url_name = this.urlName

				// this.pageForm.url_name = $tabSelected.url_name

				//判断当前是否有英语（切换同步）
				/* 			if (this.langList[0].key == 'en') {
							//当前语言不是英语
							if (lang != 'en') {
								//判断当前语言是否为空
								if ($tabSelected.url_name == '') {
									this.pageForm.url_name = this.pageForm.data.en.url_name
									$tabSelected.url_name = this.pageForm.data.en.url_name
								} else {
									this.pageForm.url_name = $tabSelected.url_name//不同语言url
								}
							} else {
								this.pageForm.url_name = $tabSelected.url_name//不同语言url
							}

						} else {
							this.pageForm.url_name = $tabSelected.url_name//不同语言url
						} */

				this.urlCount = $tabSelected.url_name.split('').length;

				this.pageForm.refresh_time = this.refreshTime;
				this.pageForm.end_time = this.end_time;

				this.pageForm.end_url = $tabSelected.end_url;
				// this.pageForm.pc_end_url = $tabSelected.pc_end_url
				// this.pageForm.m_end_url = $tabSelected.m_end_url

				// this.pageForm.redirect_url = $tabSelected.redirect_url
				// this.pageForm.tplId = $tabSelected.tplId

				this.pageForm.tpl_id = $tabSelected.tpl_id;
				this.pageForm.tpl_name = $tabSelected.tpl_name;
				this.pageForm.m_tpl_id = $tabSelected.m_tpl_id;
				this.pageForm.m_tpl_name = $tabSelected.m_tpl_name;
				this.pageForm.app_tpl_id = $tabSelected.app_tpl_id;
				this.pageForm.app_tpl_name = $tabSelected.app_tpl_name;
				this.pageForm.ios_tpl_id = $tabSelected.ios_tpl_id;
				this.pageForm.android_tpl_name = $tabSelected.android_tpl_name;

				this.pageForm.seo_title = $tabSelected.seo_title;
				this.pageForm.redirect_url = $tabSelected.redirect_url;

				this.pageForm.id = this.currentPageRow.id ? this.currentPageRow.id : '';

				if (this.currentPageRow.activity_id) {
					this.pageForm.activity_id = this.currentPageRow.activity_id;
				}

				this.pageForm.share = $tabSelected.share;

				/* obs页面语言切换 */
				/* 			this.pageForm.obsPage = {
							selected: $tabSelected.obsPage && $tabSelected.obsPage.selected ? $tabSelected.obsPage.selected : {}
						} */
			},
			getRowKey (row) {
				return row.id;
			},
			GB_verifyPage (id, activityId, status, is_lock, activity_create_user) {
				if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					var tip = '';

					if (status == 4) {
						tip = '确认下线该页面？';
					} else if (status == 2) {
						tip = '确认上线该页面？';
					}

					this.confirm(tip, async (vm) => {
						let params = {
								id: id,
								status: status
							},
							res = await GB_verifyPage(params);

						if (res.code === 0) {
							vm.$message({
								message: res.message,
								type: 'success'
							});
							vm.getActivities();
							vm.expandRowKeys = [];
						} else {
							vm.$message({
								message: res.message,
								type: 'warning'
							});
						}
					});
				}
			},
			GB_lockingActivity (id, is_lock, create_user, $event) {
				event.stopPropagation();
				if ((create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
					this.$message('只有活动创建者才具有此权限!');
				} else {
					var tip = '';
					if (is_lock == 1) {
						tip = '该活动解锁后，其他用户将拥有与您一样的操作权限，是否解锁？';
					} else if (is_lock == 0) {
						tip = '该活动加锁后，其他用户将不能操作此活动及其相关所有页面，是否加锁？';
					}

					this.$confirm(tip, '提示', {
						confirmButtonText: '是',
						cancelButtonText: '否',
						type: 'warning'
					}).then(async () => {
						let params = {
								id: id
							},
							res = await GB_lockingActivity(params);

						if (res.code === 0) {
							this.getActivities();
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
					}).catch(() => {
						this.activityList.forEach(function (element) {
							if (element.is_lock == 0) {
								element.lock_status = false;
							} else if (element.is_lock == 1) {
								element.lock_status = true;
							}
						});
					});
				}
			},
			updatePageUrlName (value) {
				// var reg = /[a-z\d-]{3,64}/;
				// var result = value.match(reg);
				// if (result != null) {
				// 	// this.pageForm.url_name = value
				// 	// this.urlName = this.pageForm.url_name
				// 	this.pageForm.data[this.currentChannel][this.currentLanguage].url_name = value;
				//
				// }
				this.pageForm.data[this.currentChannel][this.currentLanguage].url_name = value;
			},
			setChildEndTime (value) {
				if (value != null) {
					this.end_time = value;
					this.pageForm.end_time = value; // end_time为公共数据
				}
			},
			/**
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
			updatePageFieldTime (data, name) {

			},

			// updatePageTitle (value) {
			// 	this.pageForm.data[this.currentLanguage].title = value
			// },
			// updatePageKeywords (value) {
			// 	this.pageForm.data[this.currentLanguage].keywords = value
			// },
			// updatePageSeoTitle (value) {
			// 	this.pageForm.data[this.currentLanguage].seo_title = value
			// },
			// updatePageRedirectUrl (value) {
			// 	this.pageForm.data[this.currentLanguage].redirect_url = value
			// },
			// updatePCEndUrl (value) {
			// 	this.pageForm.data[this.currentLanguage].pc_end_url = value
			// },
			// updateMEndUrl (value) {
			// 	this.pageForm.data[this.currentLanguage].m_end_url = value
			// },
			// updatePageDescription (value) {
			// 	this.pageForm.data[this.currentLanguage].description = value
			// },
			// updatePageStatisticsCode (value) {
			// 	this.pageForm.data[this.currentLanguage].statistics_code = value
			// },
			updateRefreshTime (value) {
				this.pageForm.refresh_time = value;
				this.refreshTime = this.pageForm.refresh_time;
			},
			/* obs page update */
			updateOBSPage (item) {
				let _this = this;
				if (this.pageForm.id) {

					this.$confirm('修改主题后，该页面已经关联的板块内容将发生变化，即此活动关联的页面原来所选内容将会被清空，是否继更换？', '提示', {
						type: 'warning',
						distinguishCancelAndClose: true,
						confirmButtonText: '是',
						cancelButtonText: '否'
					})
						.then(() => {
							if (!item) {
								item = {
									id: '',
									name: ''
								};
							}
							this.pageForm.obsPage.selected = {
								id: item.id ? Number(item.id) : '',
								name: item.name
							};
							this.pageForm.data[this.currentChannel][this.currentLanguage].obsPage.selected = {
								id: item.id ? Number(item.id) : '',
								name: item.name
							};

							this.pageForm.obsPage.oldSelected = this.pageForm.obsPage.selected;

							this.$refs.ref_obsPage.blur();
						})
						.catch(action => {
							this.pageForm.obsPage.selected = this.pageForm.obsPage.oldSelected;
							// this.pageForm.data[this.currentLanguage].obsPage.selected = this.pageForm.obsPage.oldSelected
							this.$refs.ref_obsPage.blur();
							this.$message({
								type: 'info',
								message: action === 'cancel'
									? '已取消操作'
									: '停留在当前页面'
							});
						});

				} else {
					this.pageForm.obsPage = {
						selected: {
							id: Number(item.id),
							name: item.name
						}
					};
					// this.pageForm.data[this.channelCurrent][this.currentLanguage].obsPage = {
					// 	selected: {
					// 		id: Number(item.id),
					// 		name: item.name
					// 	}
					// }
				}

			},
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
			async getSupportChannelsInit () {
				let activityTabName = this.activityTabName;
				let res = await GB_getChannelKeyList();
				let channelLists = res.data[activityTabName].pipeline;
				if (!channelLists) {
					return false;
				}
				this.allSupportChannelLang_res = res;
				this.getSupportChannels();
			},
			getSupportChannels () {
				let activityTabName = this.activityTabName;
				let res = this.allSupportChannelLang_res;
				// let res = await GB_getChannelKeyList();
				let channelLists = res.data[activityTabName].pipeline;
				if (!channelLists) {
					return false;
				}
				// this.places = res.data.platform
				/**
				 * channelOption 渠道列表数组
				 * channelLang 渠道下语言列表对象
				 */
				this.allSupportChannelLang = res.data[activityTabName].pipeline;
				//更新当前端当前渠道
				this.currentChannel = Object.keys(this.allSupportChannelLang)[0];

				let channelOptions = [];
				let channelLang = {}, channelLangObj = {};
				for (var i in channelLists) {
					channelOptions.push({ 'key': channelLists[i].key, 'name': channelLists[i].name });
					channelLang[i] = [];
					channelLangObj[i] = [];
				}
				this.channelOptions = channelOptions;
				this.activityForm.channelLang = channelLang;
				// this.channelLangInitial = channelLangObj
				this.activityForm.channelCurrent = this.channelOptions[0].key;

				let supportChannelLangArrs = [];

				// let supportLangArrs = []
				// for (var key in res.data) {
				// 	res.data[key].forEach(item => {
				// 		// delete item.url
				// 		supportLangArrs.push(item)
				// 	})
				// }
				// if (supportLangArrs.length > 0) {
				// 	supportLangArrs = JSON.parse(JSON.stringify(supportLangArrs))
				// 	supportLangArrs = this.unique(supportLangArrs, 'name')

				// 	this.supportLangs = supportLangArrs

				// 	this.currentLanguage = this.supportLangs[0].key//设置第一种语言

				// 	supportLangArrs.forEach((item, index) => {
				// 		// if (item.key == 'en') {
				// 		if (item.key == this.currentLanguage) {

				// 			this.currentSiteUrl = item.url
				// 		}
				// 	})
				// }


			},
			/**
			 * dataFrom 默认allSupportChannelLang
			 * type 初始化类型:createActivity
			 * valueSpace 是否空白选中语言
			 * return 空白全渠道对象数组
			 * createActivity 新增活动,默认全选语言
			 */
			channelLangInit (dataFrom, type, valueSpace) {
				let channelLangList = dataFrom ? dataFrom : this.allSupportChannelLang;
				let channelLang = {};

				//新增活动增加全选及默认全选选中
				if (type && type === 'createActivity') {
					Object.keys(channelLangList).forEach((element) => {
						channelLang[element] = {
							selectAll: true,
							checkPartStatus: false,
							value: !valueSpace && channelLangList[element].language ? Object.keys(channelLangList[element].language) : []
						};
					});
				} else {
					Object.keys(channelLangList).forEach((element) => {
						channelLang[element] = [];
					});
				}
				// this.channelLangInitial = channelLang
				return channelLang;
			},
			publicReady () {
				this.getActivities();
				this.getSupportChannelsInit();
				this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data;

				bus.$on('giveData', data => {
					this.siteInfo = data;
				});
				this.sitePlat = this.siteInfo.site.split('-')[1];
				// 设置当前站点信息
				this.places = JSON.parse(localStorage.currentSites).sites;
			},
			setActivityTime () {
				let nowDate = new Date();
				let now = nowDate.getTime() - nowDate.getHours() * 3600000 - nowDate.getMinutes() * 60000 - nowDate.getMinutes() * 1000;

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
					this.$message.error('结束时间不能小于当前时间');

					this.activityForm.range_time = ['', ''];
				} else if (new Date(this.activityForm.range_time[1]).getDate() == new Date(this.activityForm.range_time[0]).getDate() &&
					new Date(this.activityForm.range_time[1]).getHours() <= new Date(this.activityForm.range_time[0]).getHours()) {
					this.$message.error('结束时间不能小于开始时间');

					this.activityForm.range_time = ['', ''];
				}

				this.activityForm.start_time = this.activityForm.range_time[0];
				this.activityForm.end_time = this.activityForm.range_time[1];
			},
			getAppActivities () {
				let list = [];

				this.appActivityList.forEach(function (element) {
					list.push({
						id: element.id,
						name: element.name
					});
				});

				this.appActivities = list;
			},
			getAppPages (val) {
				let list = [];
				let languages = [];

				this.convertForm.page = '';
				this.appActivityList.forEach(function (element) {
					if (element.id == val) {
						element.pageList.forEach(function (page, index) {
							list.push({
								id: page.id,
								title: page.title
							});
							if (index == 0) {
								page.langList.forEach(function (lang) {
									languages.push({
										key: lang.key,
										name: lang.name
									});
								});
							}
						});
					}
				});

				this.convertLangs = languages;
				this.appPages = list;
			},
			/* M转APP
		* ios_is_group ,android_is_group 是否存在绑定关系
		*/
			convertAPP (id, is_lock, activity_create_user, group_info, group_languages, list) {
				if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					this.convertForm.source_channelLang = this.channelLangInit();
					this.convertForm.ios_target_channelLang = this.channelLangInit();
					this.convertForm.android_target_channelLang = this.channelLangInit();

					this.convertForm.source_channelCurrent = Object.keys(this.convertForm.source_channelLang)[0];
					// this.convertForm.target_channelCurrent = Object.keys(this.convertForm.target_channelLang)[0]
					// this.convertForm.ios_target_channelCurrent = Object.keys(this.convertForm.ios_target_channelLang)[0];
					// this.convertForm.android_target_channelCurrent = Object.keys(this.convertForm.android_target_channelLang)[0];
					this.convertForm.ios_target_channelCurrent = group_languages.ios?Object.keys(group_languages.ios)[0]:'';
					this.convertForm.android_target_channelCurrent = group_languages.android?Object.keys(group_languages.android)[0]:'';

					// this.convertForm.pc_supportChannel = group_languages.pc
					this.convertForm.wap_supportChannel = group_languages.wap;
					this.convertForm.ios_supportChannel = group_languages.ios;
					this.convertForm.android_supportChannel = group_languages.android;

					this.convertForm.id = id;
					this.convertForm.activity_id = '';
					this.convertForm.page_id = '';
					this.convertForm.model = '1';

					//是否存在转换
					this.convertForm.hasToAndroid = list.hasToAndroid;
					this.convertForm.hasToIos = list.hasToIos;
					this.convertForm.hasToWap = list.hasToWap;

					// 如果有ios,android端关联，则不需要手动选择主活动和子页面
					if (group_info.platform_list.wap) {
						this.convertForm.source_id = group_info.platform_list.wap;
					} else {
						this.convertForm.source_id = 0;
					}

					if (group_info.platform_list.ios) {
						let languages = [];
						this.convertForm.ios_is_group = 1;
						this.convertForm.ios_target_id = group_info.platform_list.ios;
					} else {
						this.convertForm.ios_is_group = 0;
						this.convertForm.ios_target_id = 0;

						// this.GB_getAppActivityList()
						this.GB_getMActivityList('ios');
					}

					if (group_info.platform_list.android) {
						let languages = [];

						this.convertForm.android_is_group = 1;
						this.convertForm.android_target_id = group_info.platform_list.android;
					} else {
						this.convertForm.android_is_group = 0;
						this.convertForm.android_target_id = 0;

						// this.GB_getAppActivityList()
						this.GB_getMActivityList('android');
					}


					/* 				if (group_info.platform_list.wap) {
									let languages = []

									this.convertForm.is_group = 1
									this.convertForm.source_id = group_info.platform_list.wap
									this.convertForm.target_id = group_info.platform_list.wap
									this.convertForm.ios_target_id = group_info.platform_list.ios
									this.convertForm.android_target_id = group_info.platform_list.android

								} else {
									this.convertForm.is_group = 0
									this.convertForm.source_id = 0
									this.convertForm.target_id = 0

									this.GB_getAppActivityList()
								} */

					/* 				if (group_info.platform_list.wap) {
									let languages = []

									this.convertForm.is_group = 1
									this.convertForm.source_id = group_info.platform_list.wap
									this.convertForm.target_id = group_info.platform_list.wap
									this.convertForm.ios_target_id = group_info.platform_list.ios
									this.convertForm.android_target_id = group_info.platform_list.android

								} else {
									this.convertForm.is_group = 0
									this.convertForm.source_id = 0
									this.convertForm.target_id = 0

									this.GB_getAppActivityList()
								} */
					this.convertFormCurrent = 'convertForm';
					this.dialogConvertAPP = true;


				}
			},

			/* M转ios,android  */
			convertNative (convertType, id, is_lock, activity_create_user, group_info, group_languages, list) {
				if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					let $form = this.convertForm_ios_android;
					$form.source_channelLang = this.channelLangInit();
					$form.target_channelLang = this.channelLangInit();
					$form.source_channelCurrent = Object.keys($form.source_channelLang)[0];
					$form.target_channelCurrent = Object.keys($form.target_channelLang)[0];

					let $platform_ios = group_info.platform_list.ios,
						$platform_android = group_info.platform_list.android;
					let $source_platform, $target_platform, platform;


					if (convertType == 'ios2android') {
						$form.target_channelCurrent = group_languages.android?Object.keys(group_languages.android)[0]:'';


						$form.source_supportChannel = group_languages.ios;
						$form.target_supportChannel = group_languages.android;

						$source_platform = group_info.platform_list.ios;
						$target_platform = group_info.platform_list.android;

						this.convertForm_ios_android.submitType = 'ios2android';
						this.convertForm_ios_android.dialogTitle = 'IOS转Android';
						platform = 'android';
					} else {
						$form.target_channelCurrent = group_languages.android?Object.keys(group_languages.ios)[0]:'';

						$form.source_supportChannel = group_languages.android;
						$form.target_supportChannel = group_languages.ios;

						$source_platform = group_info.platform_list.android;
						$target_platform = group_info.platform_list.ios;

						this.convertForm_ios_android.submitType = 'android2ios';
						this.convertForm_ios_android.dialogTitle = 'Android转IOS';
						platform = 'ios';
					}

					$form.id = id;
					$form.activity_id = '';
					$form.page_id = '';
					$form.model = '1';

					// 如果有wap端关联，则不需要手动选择主活动和子页面

					if ($target_platform) {
						let languages = [];

						$form.is_group = 1;
						$form.source_id = $source_platform;
						$form.target_id = $target_platform;

					} else {
						$form.is_group = 0;
						if (list.hasToAndroid && !list.hasToIos) {
							$form.source_id = group_info.platform_list.ios;
						} else if (!list.hasToAndroid && list.hasToIos) {
							$form.source_id = group_info.platform_list.android;
						} else {
							$form.source_id = 0;
						}

						// $form.source_id = 0
						$form.target_id = 0;

						this.GB_getMActivityList(platform);
					}

					this.convertFormCurrent = 'convertForm_ios_android';
					this.dialogConvertNative = true;
				}
			},
			/* pc 转 m 事件*/
			convertM (id, is_lock, activity_create_user, group_info, group_languages, rowList) {
				if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
				} else {
					this.convertForm_M.source_channelLang = this.channelLangInit();
					this.convertForm_M.target_channelLang = this.channelLangInit();
					this.convertForm_M.source_channelCurrent = Object.keys(this.convertForm_M.source_channelLang)[0];
					// this.convertForm_M.target_channelCurrent = Object.keys(this.convertForm_M.target_channelLang)[0];
					this.convertForm_M.target_channelCurrent = Object.keys(group_languages.wap)[0];
					this.convertForm_M.pc_supportChannel = group_languages.pc;
					this.convertForm_M.wap_supportChannel = group_languages.wap;

					this.convertForm_M.id = id;
					this.convertForm_M.activity_id = '';
					this.convertForm_M.page_id = '';
					this.convertForm_M.model = '1';

					// 如果有wap端关联，则不需要手动选择主活动和子页面
					if (group_info.platform_list.wap) {
						let languages = [];

						this.convertForm_M.is_group = 1;
						this.convertForm_M.source_id = group_info.platform_list.pc;
						this.convertForm_M.target_id = group_info.platform_list.wap;

						/* 					Object.keys(group_languages.wap).forEach(function (key) {
											languages.push({
												key: group_languages.wap[key].lang,
												name: group_languages.wap[key].langName
											})
										})

										this.convertLangs = languages */
					} else {
						// this.convertForm_M.is_group = 0
						// this.convertForm_M.source_id = 0
						// this.convertForm_M.target_id = 0
						this.convertForm_M.is_group = 0;
						this.convertForm_M.source_id = group_info.platform_list.pc;
						this.convertForm_M.target_id = 0;

						this.GB_getMActivityList('wap');
					}

					this.convertFormCurrent = 'convertForm_M';
					this.dialogConvertM = true;
				}
			},
			//一键转M,获取M端活动列表
			getMActivities (platform) {
				let convertFormCurrent = this.convertFormCurrent,
					list = [],
					activityList = [];

				if (convertFormCurrent == 'convertForm' && platform) {
					activityList = this.convertForm['platformActivityPage'][platform].mActivityList;
				} else {
					activityList = this.mActivityList;
				}


				activityList.forEach(function (element) {
					list.push({
						id: element.id,
						name: element.name
					});
				});

				if (convertFormCurrent == 'convertForm' && platform) {
					this.convertForm['platformActivityPage'][platform].mActivities = list;
				} else {
					this.mActivities = list;
				}

				// this.mActivities = list
			},
			/**
			 * 获取M端子活动列表
			 * lsit 子页面列表
			 * activityList 对应表单下的活动列表
			 * platform 获取对应端(pc,wap,ios,android)
			 */
			getMPages (val, platform, $event) {
				let list = [], activityList = [];
				let languages = [], _this = this;
				let convertFormCurrent = this.convertFormCurrent;

				this.convertForm_M.page = '';

				if (convertFormCurrent == 'convertForm' && platform) {
					activityList = this.convertForm['platformActivityPage'][platform].mActivityList;
				} else {
					activityList = this.mActivityList;
				}

				activityList.forEach(function (element) {
					if (element.id == val) {
						element.pageList.forEach(function (pageChannel, index) {
							list.push({
								title: pageChannel.title,
								pipelineList: pageChannel.pipelineList,
							});

						});
						// element.pageList.forEach(function (page, index) {
						// 	list.push({
						// 		id: page.id,
						// 		title: page.title,
						// 		pipeline: page.pipeline,
						// 		langList: page.langList
						// 	});
						//
						// });
						/* target_id 获取目标渠道子页面page_id列表 */
						if (element.pipeline) {
							if (convertFormCurrent) {
								if (convertFormCurrent == 'convertForm') {
									_this[convertFormCurrent][platform + '_target_id'] = element.pipeline;
								} else {
									_this[convertFormCurrent].target_id = element.pipeline;
								}

							} else {
								_this.convertForm_M.target_id = element.pipeline;
							}

							_this.convertForm_ios_android.target_id = element.pipeline;
						}
					}
				});

				if (convertFormCurrent == 'convertForm' && platform) {
					this[convertFormCurrent]['platformActivityPage'][platform].mPages = list;
					//更新子页面列表
					let $obj = this[convertFormCurrent]['platformActivityPage'][platform];
					if ($obj.activity_id && $obj.page_id !== '') {
						this.handleMPageChange($obj.page_id, platform);
					}

				} else {
					this.mPages = list;
					//更新子页面列表
					if (this[convertFormCurrent].activity_id) {
						let $obj = this[convertFormCurrent];
						if ($obj.activity_id && $obj.page_id !== '') {
							this.handleMPageChange($obj.page_id, platform);
						}

					}

				}

				this.convertLangs = languages;

			},

			/**
			 * pc转M 触发子页面切换获取渠道
			 * @param page_id
			 * @param platform
			 * @param $event
			 * channelPipeLine 多渠道下page_id
			 */
			handleMPageChange (page_id, platform, $event) {
				let _this = this;
				let channelPipeLine = {};
				let convertFormCurrent = this.convertFormCurrent;
				let mPages = convertFormCurrent == 'convertForm' ? '' : this.mPages;
				if (convertFormCurrent == 'convertForm' && platform) {
					mPages = this.convertForm['platformActivityPage'][platform].mPages;
				} else {
					mPages = this.mPages;
				}

				mPages.forEach((element, elementIndex) => {
					if (elementIndex == page_id) {
						//更新当前目标渠道
						let pipeLineListKeys = Object.keys(element.pipelineList);
						_this.convertForm_M.target_channelCurrent = pipeLineListKeys[0];

						if (convertFormCurrent == 'convertForm_M') {
							_this.convertForm_M.wap_supportChannel = element.pipelineList;
							_this[convertFormCurrent].target_channelLang = this.channelLangInit();
						} else if (convertFormCurrent == 'convertForm_ios_android') {
							_this[convertFormCurrent].target_supportChannel = element.pipelineList;
							_this[convertFormCurrent].target_channelLang = this.channelLangInit();
						} else if (convertFormCurrent == 'convertForm') {
							_this[convertFormCurrent][platform + '_supportChannel'] = element.pipelineList;
							_this[convertFormCurrent][platform + '_target_channelLang'] = this.channelLangInit();
						} else {
							_this.convertForm_M.wap_supportChannel = element.langList;
						}

						//获取当前子页面渠道page_id
						pipeLineListKeys.forEach((item, index) => {
							channelPipeLine[item] = {
								page_id: element.pipelineList[item].page_id
							};
						});
						// 选中子页面多渠道page_id
						if (convertFormCurrent) {
							if (convertFormCurrent == 'convertForm') {
								_this[convertFormCurrent][platform + '_target_id'] = channelPipeLine;
							} else {
								_this[convertFormCurrent].target_id = channelPipeLine;
							}

						} else {
							_this.convertForm_M.target_id = channelPipeLine;
						}

						_this.convertForm_ios_android.target_id = channelPipeLine;
					}

					/*					if (element.id == page_id) {
                      if (convertFormCurrent == 'convertForm_M') {
                        _this.convertForm_M.wap_supportChannel = element.langList;
                        _this[convertFormCurrent].target_channelLang = this.channelLangInit();
                      } else if (convertFormCurrent == 'convertForm_ios_android') {
                        _this[convertFormCurrent].target_supportChannel = element.langList;
                        _this[convertFormCurrent].target_channelLang = this.channelLangInit();
                      } else if (convertFormCurrent == 'convertForm') {
                        _this[convertFormCurrent][platform + '_supportChannel'] = element.langList;
                        _this[convertFormCurrent][platform + '_target_channelLang'] = this.channelLangInit();
                      } else {
                        _this.convertForm_M.wap_supportChannel = element.langList;
                      }


                      return;
                    }*/
				});

			},
			// 选中模板
			handleModelTempSelect (val) {

				// 新增子页面模板选择区分来自PC、M或APP
				this.templateSelectPlace = val;

				// PC、M、APP页面模板数据获取
				if (this.templateSelectPlace == 'pc') {
					this.getTmpListValue = 'pc';
				} else if (this.templateSelectPlace == 'wap') {
					this.getTmpListValue = 'wap';
				} else if (this.templateSelectPlace == 'app') {
					this.getTmpListValue = 'app';
				} else if (this.templateSelectPlace == 'ios') {
					this.getTmpListValue = 'ios';
				} else if (this.templateSelectPlace == 'android') {
					this.getTmpListValue = 'android';
				}
				this.getTmpListStatus = true;

				this.getPageTemplates();

				let _this = this;
				this.modelInfo.visible = true;
				this.modelInfo.modelSelect = this.pageForm.tpl_id;

				setTimeout(function () {
					_this.handlePanelScroll();
				}, 100);

			},
			handleModelClose () {
				this.tplInfo.pageNo = 1;
			},
			handleSureModel () {
				let currentPlace = this.templateSelectPlace;

				let selected = this.modelInfo.modelSelect;

				if (currentPlace == 'pc') {
					this.pageForm.tpl_id = selected;
				} else if (currentPlace == 'wap') {
					this.pageForm.m_tpl_id = selected;
				} else if (currentPlace == 'app') {
					this.pageForm.app_tpl_id = selected;
				} else if (currentPlace == 'ios') {
					this.pageForm.ios_tpl_id = selected;
				} else if (currentPlace == 'android') {
					this.pageForm.android_tpl_id = selected;
				}

				this.modelInfo.visible = false;
				// this.tplId = this.pageForm.tplId

				let $currentForm = this.pageForm.data[this.currentChannel][this.currentLanguage];

				$currentForm.tpl_id = this.pageForm.tpl_id;
				$currentForm.m_tpl_id = this.pageForm.m_tpl_id;
				$currentForm.app_tpl_id = this.pageForm.app_tpl_id;
				$currentForm.ios_tpl_id = this.pageForm.ios_tpl_id;
				$currentForm.android_tpl_id = this.pageForm.android_tpl_id;

				let _this = this;

				// let template = '无页面模板', m_template = '无页面模板'
				this.pageTemplateList.forEach(function (element) {
					if (element.id == selected) {
						if (currentPlace == 'pc') {
							// template = element.name
							_this.pageForm.tpl_name = element.name;
						} else if (currentPlace == 'wap') {
							// m_template = element.name
							_this.pageForm.m_tpl_name = element.name;
						} else if (currentPlace == 'app') {
							// m_template = element.name
							_this.pageForm.app_tpl_name = element.name;
						} else if (currentPlace == 'ios') {
							// m_template = element.name
							_this.pageForm.ios_tpl_name = element.name;
						} else if (currentPlace == 'android') {
							// m_template = element.name
							_this.pageForm.android_tpl_name = element.name;
						}
					}
				});

				// this.currentTemplate = template
				$currentForm.tpl_name = this.pageForm.tpl_name;
				$currentForm.m_tpl_name = this.pageForm.m_tpl_name;
				$currentForm.app_tpl_name = this.pageForm.app_tpl_name;
				$currentForm.app_tpl_name = this.pageForm.app_tpl_name;
				$currentForm.ios_tpl_name = this.pageForm.ios_tpl_name;
				$currentForm.android_tpl_name = this.pageForm.android_tpl_name;
				// this.pageForm.data[this.currentChannel][this.currentLanguage] = $currentForm

				// this.pageForm.tpl_name = template
				// this.pageForm.m_tpl_name = m_template
			},
			handleCancelSelectedModel () {
				this.modelInfo.visible = false;
			},
			subDialogClose () {
				this.resetForm('pageForm');
				this.pageForm.tpl_id = '0';
				this.modelInfo.tabActive = '2';
				this.end_time = '';
				if (this.siteInfo.site == 'gb') {
					this.obsPage.selected = {};
					this.obsPage.data = [];
					this.obs.selected = {};
					this.pageForm.obsPage = {
						selected: {}
					};
				}
				this.pageForm.pc_status = false;
				this.pageForm.m_status = false;
				this.pageForm.ios_status = false;
				this.pageForm.android_status = false;
				this.pageForm.url_name_id = null;
				this.pageForm.share = {
					entry: [
						{ key: 'FB', name: 'FB' },
						{ key: 'Twitter', name: 'Twitter' },
						{ key: 'Google', name: 'Google' },
						{ key: 'Reddit', name: 'Reddit' },
						{ key: 'VK', name: 'VK' },
						{ key: 'Telegram', name: 'Telegram' }
					],
					checked: [],
					title: '',
					desc: '',
					integral: '',
					img: '',
					integral_times: '',
					//上传图片
					fileList: [],
				};
			},
			updateModelSelect () { },
			tplTabClick () {
				this.tplInfo.pageNo = 1;
				let contContainer = document.getElementById('pane-2').parentNode;
				contContainer.removeEventListener('scroll', this.handlePanelScroll);

				if (this.templateSelectPlace == 'pc') {
					this.getTmpListValue = 'pc';
				} else if (this.templateSelectPlace == 'wap') {
					this.getTmpListValue = 'wap';
				} else if (this.templateSelectPlace == 'app') {
					this.getTmpListValue = 'app';
				} else if (this.templateSelectPlace == 'ios') {
					this.getTmpListValue = 'ios';
				} else if (this.templateSelectPlace == 'android') {
					this.getTmpListValue = 'android';
				}
				this.getTmpListStatus = true;

				this.getPageTemplates('scroll');
			},
			handlePanelScroll () {
				let panelCont0 = document.getElementById('pane-2').parentNode,
					_this = this;
				// num = this.tplInfo.pageNo,
				// maxPageNo = this.tplInfo.maxPageNo
				let timer;
				panelCont0.addEventListener('scroll', function () {
					if (timer) clearTimeout(timer);
					timer = setTimeout(function () {
						if (panelCont0.clientHeight + panelCont0.scrollTop == panelCont0.scrollHeight) {
							var tempNum = _this.tplInfo.pageNo + 1;
							if (tempNum <= _this.tplInfo.maxPageNo) {
								_this.tplInfo.pageNo = tempNum;


								if (_this.templateSelectPlace == 'pc') {
									_this.getTmpListValue = 'pc';
								} else if (_this.templateSelectPlace == 'wap') {
									_this.getTmpListValue = 'wap';
								} else if (_this.templateSelectPlace == 'app') {
									_this.getTmpListValue = 'app';
								} else if (_this.templateSelectPlace == 'ios') {
									_this.getTmpListValue = 'ios';
								} else if (_this.templateSelectPlace == 'android') {
									_this.getTmpListValue = 'android';
								}
								_this.getTmpListStatus = true;

								_this.getPageTemplates('scroll');
							}
						}
					}, 600);
				});
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
				this.viewModel.src = '/activity/page-tpl/preview?pid=' + pid + '&lang=' + langDefualt + '&id=' + id + '&site_code=' + site_code + '';

				// let sideType = site_code.split('-')[1], sideWidth
				// sideWidth = '100%'
				let sideType = site_code.split('-')[1], sideWidth;
				if (sideType != 'pc') {
					sideWidth = '400px';
				} else {
					sideWidth = '100%';
				}

				this.viewModel.sideType = sideType;
				this.viewModel.sideWidth = sideWidth;
				this.pageLoading = false;
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
			viewModelClose () {
				this.viewModel.visible = false;
				this.viewModel.html = '';
				this.viewModel.src = '';
			},
			handleRowClick (row) {
				if (this.expandRowKeys.length > 0 && this.expandRowKeys[0] == row.id) {
					this.expandRowKeys = [];
				} else {
					this.expandRowKeys = [row.id];
				}
			},
			/* 校验当前模板列表 */
			checkCurrentPageForm () {
				let pageTemplateList = this.pageTemplateList,
					tabActive = this.modelInfo.tabActive,
					siteInfo = this.siteInfo,
					tempLength1 = 0,
					tempLength2 = 0;

				let pageTemplateListWarn = tabActive == '2' ? '您还没有自己的模板' : '暂无页面模板供使用';
				this.pageTemplateListWarn = pageTemplateListWarn;

				pageTemplateList.forEach(function (item, index) {
					if (item.create_user == siteInfo.userName) {
						tempLength1 += 1;
					} else if (item.create_user != siteInfo.userName && item.tpl_type == 1) {
						tempLength2 += 1;
					}
				});
				this.modelInfo.tempLength1 = tempLength1;
				this.modelInfo.tempLength2 = tempLength2;
			},
			/* OBS */
			async getObsTheme () {
				this.obsLoading = true;
				let res = await obsLevel1().catch(() => {
					this.obsLoading = false;
				});
				this.obsLoading = false;

				if (res.code == 0) {
					this.obs.data = res.data;
				}
			},
			async getObsPage (id) {
				this.obsLoading = true;
				const params = {
					theme_id: id,
					page_id: this.pageForm.id,
					platform: this.activityTabName
					// lang: this.currentLanguage
				};
				let res = await obsLevel2(params).catch(() => {
					this.obsLoading = false;
				});
				this.obsLoading = false;
				if (res && res.code == 0) {
					this.obsPage.data = res.data;
				}
			},

			async openCountryURL (id) {
				let res = await GB_getChannelUrlList({ page_id: id });
				this.channelUrlList = res.data;
				this.dialogChannelVisible = true;
			},

			obsThemeChange (item) {
				// this.confirm('修改主题后，该活动已经关联的页面和板块内容将发生变化，即此活动关联的页面原来所选内容将会被清空，是否继续更换？？', async (vm) => {
				// 	this.obs.selected = item;
				// })
				let _this = this;
				if (this.activityForm.id) {
					this.$confirm('修改主题后，该活动已经关联的页面和板块内容将发生变化，即此活动关联的页面原来所选内容将会被清空，是否继续更换？', '提示', {
						type: 'warning',
						distinguishCancelAndClose: true,
						confirmButtonText: '是',
						cancelButtonText: '否'
					})
						.then(() => {
							this.$refs.ref_obsTheme.blur();
							this.obs.oldSelected = this.obs.selected;
						})
						.catch(action => {
							this.obs.selected = this.obs.oldSelected;
							this.$refs.ref_obsTheme.blur();
							this.$message({
								type: 'info',
								message: action === 'cancel'
									? '已取消操作'
									: '停留在当前页面'
							});
						});
				}

			},
			obsThemeBlur (item) {
				if (!this.obs.oldSelected) {
					this.obs.oldSelected = this.obs.selected;
				}
				// this.obs.oldSelected = this.obs.selected;
			},
			obsPageBlur (item) {
				// let currentLang = this.currentLanguage
				// let langObsPage = this.pageForm.data[currentLang].obsPage
				// if (!langObsPage.oldSelected) {
				// 	langObsPage.oldSelected = langObsPage.selected;
				// }
			},
			//obs theme 下拉
			obsThemeVisible () {
				if (this.obs.visibleChange) {
					return false;
				}
				this.getObsTheme();
				this.obs.visibleChange = true;
			},
			closeDialogActivity () {
				this.obs = {
					data: [],
					selected: {}
				};
				this.channelLangInit('createActivity');
			},
			/**
			 * 转换类型,渠道改动监听
			 * convertType ('convertForm_M')
			 * place ('pc')
			 */

			convertPipeChange (value, convertType, place) {
				if (!value) {
					return false;
				}
				if (convertType == 'convertForm_M' && place == 'pc') {
					let langList = this.convertForm_M['pc_supportChannel'][value].language;
					this.convertForm_M['source_langCurrent'] = Object.keys(langList)[0];
				} else if (convertType == 'convertForm' && place == 'wap') {
					let langList = this.convertForm['wap_supportChannel'][value].language;
					this.convertForm['source_langCurrent'] = Object.keys(langList)[0];
				} else if (convertType == 'convertForm_ios_android') {
					let langList = this.convertForm_ios_android['source_supportChannel'][value].language;
					this.convertForm_ios_android['source_langCurrent'] = Object.keys(langList)[0];
				}
			},
			//PC转M 转APP关闭
			closeConvertM () {
				this.convertForm_M.source_langCurrent = '';
			},
			closeConvertApp () {
				this.convertForm.source_langCurrent = '';
				this.convertForm = {
					id: '',
					activity_id: '',
					page_id: '',
					model: '1',
					is_group: 0,
					ios_is_group: 0,
					android_is_group: 0,
					source_id: 0,
					target_id: 0,
					ios_target_id: 0,
					android_target_id: 0,
					lang: '',
					wap_supportChannel: {},
					source_channelLang: {},
					source_channelCurrent: '',
					// target_channelLang: {},
					// target_channelCurrent: ''	,
					ios_supportChannel: {},
					ios_target_channelLang: {},
					ios_target_channelCurrent: '',
					android_target_channelLang: {},
					android_target_channelCurrent: '',
					android_supportChannel: {},
					//单渠道下单语言选中
					source_langCurrent: '',
					//ios,android活动列表id
					platformActivityPage: {
						ios: {
							activity_id: '',
							page_id: '',
							mActivityList: '',
							mActivities: '',
							mPages: ''
						},
						android: {
							activity_id: '',
							page_id: '',
							mActivityList: '',
							mActivities: '',
							mPages: ''
						}
					},
					hasToWap: false,
					hasToAndroid: false,
					hasToIos: false
				};

			},
			closeConvertIos_android () {
				this.convertForm_ios_android.source_langCurrent = '';
			},

			handleUploadSuccess (response, file) {
				if (response.code == 0) {
					let $form = this.pageForm, $langForm = this.pageForm.data[this.currentChannel][this.currentLanguage];
					$form.share.img = response.data.url;
					$form.share.fileList = [];
					$form.share.fileList.push({
						name: file.name,
						url: response.data.url
					});

					$langForm.share.img = response.data.url;
					$langForm.share.fileList = [];
					$langForm.share.fileList.push({
						name: file.name,
						url: response.data.url
					});

					this.share.img = response.data.url;
					this.share.fileList = [];
					this.share.fileList.push({
						name: file.name,
						url: response.data.url
					});
				} else {
					this.$message(response.data.message);
				}
			},
			handleUploadExceed () {
				this.$message('只允许上传一张图片！');
			},
			handleUploadError () {
				this.$message('文件上传失败！');
			},
			handleBeforeUpload (file) {
				if (['image/jpeg', 'image/png'].indexOf(file.type) == -1) {
					this.$message({
						type: 'info',
						message: '请选择合适的图片格式！'
					});

					return false;
				}

				if (file.size >= 3 * 1024 * 1024) {
					this.$message({
						type: 'info',
						message: '请选择合适的图片大小！'
					});

					return false;
				}
			},
			/**
			 * 新增活动全选监听
			 * @param value bo0lean
			 * @param currentChannel 当前渠道
			 */
			handleSelectAllLang (value, currentChannel) {
				if (value) {
					let currentLangAll = this.allSupportChannelLang[currentChannel] ? this.allSupportChannelLang[currentChannel].language : {};
					this.activityForm.channelLang[currentChannel].checkPartStatus = false;
					this.activityForm.channelLang[currentChannel].value = Object.keys(currentLangAll);
				} else {
					this.activityForm.channelLang[currentChannel].value = [];
					this.activityForm.channelLang[currentChannel].checkPartStatus = false;
				}
			},
			/**
			 * 新增活动渠道下语言选中监听
			 * @param newVal
			 * @param val
			 * @param channel 渠道
			 */
			handleLangCheck (newVal, val, channelItem) {
				let channel = channelItem.key;
				let channelLang = this.allSupportChannelLang[channel];
				let channelLangList = channelLang['language'] ? Object.keys(channelLang['language']) : [];
				let checkPartCurrent = this.activityForm.channelLang[channel].checkPartStatus;

				if (checkPartCurrent === false && val.length > 0 && val.length < channelLangList.length) {
					this.activityForm.channelLang[channel].checkPartStatus = true;
				} else if (val.length > 0 && val.length === channelLangList.length) {
					this.activityForm.channelLang[channel].selectAll = true;
					this.activityForm.channelLang[channel].checkPartStatus = false;
				} else if (val.length === 0) {
					this.activityForm.channelLang[channel].selectAll = false;
					this.activityForm.channelLang[channel].checkPartStatus = false;
				}
			},
			/**
			 * 新增活动渠道dialog
			 * @param row
			 */
			addActivityChannel (row) {
				if ((row.is_lock == 1 && row.create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
					this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作');
					return false;
				}
				this.activityChannelForm.editPlace = row.group_info.platform_list;
				this.activityChannelForm.activity_id = row.id;
				let editChannelLang = {};
				//空白渠道数据选中
				let channelLangAll_from_blank = this.channelLangInit(null, 'createActivity', true),
					channelLang_old = this.channelLangInit(null, 'createActivity', true),
					editSupportChannelLang = row.group_info.pipelineList;
				// this.activityChannelForm.channelCurrent = row.group_info.pipelineList[0].key;
				this.activityChannelForm.channelCurrent = Object.keys(this.allSupportChannelLang)[0];

				//填充已选渠道
				row.group_info.pipelineList.forEach((item, index) => {
					item.langList.forEach((childItem) => {
						if(channelLangAll_from_blank[item.key]){
							channelLangAll_from_blank[item.key]['value'].push(childItem.key);
						}
						if(channelLang_old[item.key]){
							channelLang_old[item.key]['value'].push(childItem.key);
						}
					});
				});

				this.activityChannelForm.channelLang = channelLangAll_from_blank;
				this.activityChannelForm.channelLang_old = channelLang_old;

				//已选渠道
				/*				row.group_info.pipelineList.forEach((item, index) => {
                            editChannelLang[item.key] = { selectAll: true, checkPartStatus: false, value: [] };
                            item.langList.forEach((childItem) => {
                                editChannelLang[item.key]['value'].push(childItem.key);
                            });
                        });

                        this.activityChannelForm.channelLang = editChannelLang;*/

				// 主活动已勾选端口
				let editPlaceArr = [];
				row.group_info.platform_list.forEach((item) => {
					if (item.selected === 1) {
						editPlaceArr.push(item.code);
					}
				});
				this.activityChannelForm.editPlace = editPlaceArr;
				// 匹配用户可选端
				let currentPlaces = this.places;
				let placeArr = Object.keys(currentPlaces);
				let current_platform_list = row.group_info.platform_list;
				let platUser = [];
				placeArr.forEach((item, index) => {
					current_platform_list.forEach((plat, platIndex) => {
						if (currentPlaces[item].status == 1 && currentPlaces[item].platform_name == plat.name) {
							platUser.push(plat);
						}
					});

				});
				this.activityChannelForm.places = platUser;
				// this.activityChannelForm.places = row.group_info.platform_list;


				this.dialogChannelAddVisible = true;


			},
			/**
			 * 新增渠道提交
			 * @returns {Promise<void>}
			 */
			async submitChannelEditForm () {
				this.submitLoading = true;
				let jsonObject = {
					id: this.activityChannelForm.activity_id
				};
				let editPlace = this.activityChannelForm.editPlace,
					channelLang = this.activityChannelForm.channelLang,
					channelLangSubmit = {},
					platform_list = {};

				Object.keys(channelLang).forEach((item, index) => {
					channelLangSubmit[item] = channelLang[item].value;
				});

				editPlace.forEach((item, index) => {
					platform_list[item] = channelLangSubmit;

				});

				jsonObject.platform_list = JSON.stringify(platform_list);
				try {
					let res = await GB_editAdd(jsonObject);
					if (res.code == 0) {
						this.getActivities();
						let activity_id = this.expandRowKeys[0];
						if (activity_id) {
							this.getPages(activity_id);
						}

					}
					this.formSubmitCallback('activityChannelForm', res);

				} catch (err) {
					this.submitLoading = false;
				}


			}
		},
		created () {
			var _this = this;

			bus.$on('giveData', data => {
				this.siteInfo = data;
			});

			GB_refreshSelete().then(function (res) {
				_this.options = res.data;
			});
		},
	};
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

	#dialogLinkBox .el-button--primary {
		margin-bottom: 10px;
		width: 148px;
		height: 42px;
		font-size: 14px;

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

	.geshop-new-activities-obs-name {
		margin-top: 15px;
	}

	.geshop-new-activities-obs-name .el-form-item__label {
		line-height: 40px;
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
		position: relative;
		display: inline-block;
	}

	.gs-obs-select {
		max-width: 200px;
	}
	
</style>
