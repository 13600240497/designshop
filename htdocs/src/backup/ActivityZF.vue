<template>
    <site-layout @publicReady="publicReady" :footLink="1">
        <el-row :span="24" class="geshop-Activity-tit">
            <span class="geshop-Activity-title">活动管理</span>
        </el-row>
        <el-row class="geshop-Activity-btn">
            <el-col>
                <el-button class="geshop-Activity-btn-add" @click="createActivity">
                    <span class="icon-geshop-pack-up"></span>
                    <span class="geshop-icon-add-text">新增活动</span>
                </el-button>
            </el-col>
            <el-col>
				<el-button class="geshop-Activity-btn-refresh" @click="handle_show_updateHead">
					<span class="icon-geshop-reset"></span>
					<span class="geshop-icon-refresh-text">一键刷新头尾部</span>
				</el-button>
			</el-col>
            <el-col class="geshop-activity-actionbar-col">
                <el-form class="geshop-form-inline geshop-page-list-header" :inline="true">
                    <el-form-item label="自定义URL">
                        <el-input v-model="customUrl" class="header-input" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-input size="medium" placeholder="" v-model="id" style="width:165px" class="input-with-select">
                            <el-select v-model="searchType" slot="prepend" placeholder="请选择">
                                <el-option label="专题ID" value="1"></el-option>
                                <el-option label="子活动ID" value="2"></el-option>
                            </el-select>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="应用渠道">
                        <el-select v-model="fallen" class="header-input" clearable placeholder="请选择">
                            <el-option
                                v-for="item in allFallenSiteList"
                                :key="item.code"
                                :label="item.name"
                                :value="item.code">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="活动名称">
                        <el-input v-model="searchWord" class="header-input" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="创建者">
                        <el-input v-model="createName" class="header-input" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="常用活动筛选" width="100px">
                        <el-select v-model="oSearch.is_frequently">
                            <el-option label="全部活动" :value="0"></el-option>
                            <el-option label="常用活动" :value="1"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="doSearch">搜索</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>

        <!-- 小屏幕换行显示 start -->
        <div class="geshop-Activity-btn geshop-activity-actionbar" style="height:72px;min-width: 915px;">
            <el-form class="geshop-form-inline geshop-page-list-header" :inline="true" style="left:20px;">
            <el-row >
                <el-col>
                        <el-form-item label="自定义URL">
                            <el-input v-model="customUrl" class="header-input" placeholder=""></el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input size="medium" placeholder="" v-model="id" style="width:165px" class="input-with-select">
                                <el-select v-model="searchType" slot="prepend" placeholder="请选择">
                                    <el-option label="专题ID" value="1"></el-option>
                                    <el-option label="子活动ID" value="2"></el-option>
                                </el-select>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="应用渠道">
                            <el-select v-model="fallen" class="header-input" clearable placeholder="请选择">
                                <el-option
                                    v-for="item in allFallenSiteList"
                                    :key="item.code"
                                    :label="item.name"
                                    :value="item.code">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="活动名称">
                            <el-input v-model="searchWord" class="header-input" placeholder=""></el-input>
                        </el-form-item>
                        <el-form-item label="创建者">
                            <el-input v-model="createName" class="header-input" placeholder=""></el-input>
                        </el-form-item>
                        <el-form-item label="常用活动筛选" width="100px">
                            <el-select v-model="oSearch.is_frequently">
                                <el-option label="全部活动" :value="0"></el-option>
                                <el-option label="常用活动" :value="1"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="doSearch">搜索</el-button>
                        </el-form-item>
                </el-col>
            </el-row>
            </el-form>
        </div>

        <!-- 小屏幕换行显示 end -->

        <el-row class="geshop-port-switch">
            <template>
                <el-tabs v-model="activityTabName" type="card" @tab-click="handleActivityTabClick" class="tab-header">
                    <el-tab-pane v-for="(item,key) in places" :key="key" :label="item.platform_name" :name="key"></el-tab-pane>
                </el-tabs>
            </template>
            <el-col :span="24" class="geshop-activity-lists">
                <el-table :data="activityList" @expand-change="handleExpandChange" style="width: 100%" :row-key="getRowKey"
                          :expand-row-keys="expandRowKeys" @row-click="handleExpandChange"
                          :row-class-name="tableRowClassName">
                    <el-table-column type="expand">
                        <template slot-scope="scope">
                            <el-card class="box-card geshop-activity-child-pages" v-for="list in scope.row.children" :key="list.key">
                                <div>
                                    <div class="geshop-activity-child-pages-banner">
                                        <img :src="list.preview_pic_url" class="child-pages-image">
                                        <img v-if="list.is_native == 1 && list.site_code != 'zf-pc'" class="child-page-flag-app"
                                             src="/resources/images/default/app_icont.png" />
                                        <div class="child-page-flag-green" v-if="(list.pipeline_info.group_status == 2)">上线</div>
                                        <div class="child-page-flag-red" v-if="(list.pipeline_info.group_status == 4)">下线</div>
                                        <div class="child-page-flag-warn" v-if="(list.pipeline_info.group_status == 10)">部分渠道上线</div>
                                    </div>
                                    <div class="child-pages-hover">
                                        <div class="child-pages-container">
                                            <el-tooltip content="装修" placement="bottom" effect="light">
                                                <el-button class="icon-geshop-decorate" @click="decorate_redirect(list.design_url,list.is_lock,list.activity_create_user, list)"></el-button>
                                            </el-tooltip>

                                            <!-- 上线或下线状态 -->
                                            <el-tooltip content="上线" placement="bottom" effect="light" v-if="(list.status == 1 || list.status == 4) && list.pipeline_info.group_status != 10 && list.is_native == 0">
                                                <el-button class="icon-geshop-online" @click="ZF_verifyPage(list.id, list.activity_id, 2, list.is_lock , list.activity_create_user, list)"></el-button>
                                            </el-tooltip>
                                            <el-tooltip content="下线" placement="bottom" effect="light" v-if="list.status == 2 && list.pipeline_info.group_status != 10 && list.is_native == 0">
                                                <el-button class="icon-geshop-offline" @click="ZF_verifyPage(list.id, list.activity_id, 4, list.is_lock, list.activity_create_user, list)"></el-button>
                                            </el-tooltip>
                                            <!-- 部分渠道上下线状态 -->
                                            <el-tooltip content="上线" placement="bottom" effect="light" v-if="list.pipeline_info.group_status == 10 && list.is_native == 0">
                                                <el-button class="icon-geshop-online" @click="ZF_verifyPage(list.id, list.activity_id, 2, list.is_lock , list.activity_create_user, list)"></el-button>
                                            </el-tooltip>
                                            <el-tooltip content="下线" placement="bottom" effect="light" v-if="list.pipeline_info.group_status == 10 && list.is_native == 0">
                                                <el-button class="icon-geshop-offline" @click="ZF_verifyPage(list.id, list.activity_id, 4, list.is_lock, list.activity_create_user, list)"></el-button>
                                            </el-tooltip>

                                            <el-tooltip content="预览" placement="bottom" effect="light">
                                                <el-button class="icon-geshop-search" @click="handlePreviewUrl(list.pipeline_info.page_list)"></el-button>
                                            </el-tooltip>
                                            <el-tooltip content="转M端" placement="bottom" effect="light">
                                                <el-button class="icon-geshop-mobile" @click="convertM(list.id, list.is_lock, list.activity_create_user, list.group_info, list.group_languages, scope.row.pipelineList, list.group_id, list.activity_id)"
                                                           v-if="list.activity_type == 1 && list.hasToWap == true"></el-button>
                                            </el-tooltip>
                                            <el-tooltip content="转APP端" placement="bottom" effect="light">
                                                <el-button class="icon-geshop-mobile" @click="convertAPP(list.id, list.is_lock, list.activity_create_user, list.group_info, list.group_languages, scope.row.pipelineList, list.group_id, list.activity_id)"
                                                           v-if="list.activity_type == 2 && list.is_native == 0"></el-button>
                                            </el-tooltip>
                                        </div>
                                    </div>
                                </div>
                                <div class="child-pages-title">{{ list.page_languages[0] ? list.page_languages[0].title : '' }}</div>
                                <div class="child-pages-time">创建时间：{{ parseInt(list.create_time) | moment('YYYY-MM-DD HH:mm:ss') }} {{list.create_name}}</div>
                                <div class="child-pages-time">修改时间：{{ parseInt(list.update_time) | moment('YYYY-MM-DD HH:mm:ss') }} {{list.update_user}}</div>
                                <div class="child-pages-id-name">
                                    ID: {{ list.id }}
                                    <a class="view-page-ids" v-if="list.is_native == 1 && list.site_code != 'zf-pc'" @click="handle_show_pageid(list)">查看所有ID</a>
                                    <span>{{ list.create_name }}</span>
                                </div>

                                <div>
                                    <a
                                        class="child-pages-link"
                                        @click="viewPages(list.group_id, list.activity_id, list, scope.row.pipelineList)">
                                        查看访问链接
                                    </a>

                                    <el-dropdown split-button style="margin-left:192px;">
                                        <el-dropdown-menu slot="dropdown">
                                            <el-dropdown-item type="primary" size="small" @click.native="editPage(list, list.is_lock, list.activity_create_user, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.pipelineList)">编辑</el-dropdown-item>
                                            <el-dropdown-item type="danger" size="small" @click.native="removePage(list, list.is_lock, list.activity_create_user)">删除</el-dropdown-item>

                                            <!--<el-dropdown-item type="danger" size="small" @click.native="openSyncLog(list.id)">多端数据绑定日志</el-dropdown-item>-->

                                        </el-dropdown-menu>
                                    </el-dropdown>
                                </div>
                            </el-card>
                            <el-card class="box-card geshop-activity-child-pages">
                                <el-col>
                                    <img src="/resources/images/default/banner_default.png" class="child-pages-image" style="height:112px;width:100%;display:block;">
                                    <el-button class="icon-geshop-add-big" @click="createPage(scope.row.id, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user, scope.row.group_info.pipeline_list, scope.row)"
                                               style="font-size:40px;margin-top:28px;margin-left:100px;padding:0px 0px;"></el-button>
                                    <p class="child-pages-add">添加子页面</p>
                                </el-col>
                            </el-card>
                        </template>
                    </el-table-column>
                    <el-table-column prop="id" label="ID" width="100"></el-table-column>
                    <el-table-column prop="name" label="活动名称" width="180"></el-table-column>
                    <el-table-column prop="pipeline" align="center" label="所属渠道" width="150">
                        <template slot-scope="scope">
                            <span>{{ Object.keys(scope.row.pipeline_list).map((item) => { return (scope.row.pipeline_list[item]['name']) }).slice(0,2).join(',') }}</span>
                            <el-tooltip placement="top" v-if="scope.row.pipeline.split(',').length>2">
                                <div slot="content">{{ Object.keys(scope.row.pipeline_list).map((item) => { return (scope.row.pipeline_list[item]['name']) }).join(',') }}</div>
                                <span>>></span>
                            </el-tooltip>
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" align="center" label="创建时间" width="160">
                        <template slot-scope="scope">
                            <span>{{ parseInt(scope.row.create_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_name" align="center" label="创建者" width="120"></el-table-column>
                    <el-table-column prop="update_time" align="center" label="最后操作时间" width="160">
                        <template slot-scope="scope">
                            <span>{{ parseInt(scope.row.update_time) | moment('YYYY-MM-DD HH:mm:ss') }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="update_user" align="center" label="最后操作人" width="130"></el-table-column>
                    <el-table-column label="状态" align="center" class-name="ge-activity-list-bellrudder">
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
                    <el-table-column label="操作" align="center" width="450" class-name="ge-activity-list-operating">
                        <template slot-scope="scope" style="line-height:80px;">
                            <el-tooltip content="新增子页面" placement="bottom" effect="light">
                                <el-button class="ge-activity-list-icon icon-geshop-add-small" style="font-size:24px;" v-if="scope.row.is_lock == 0" @click.stop="createPage(scope.row.id, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user, scope.row.group_info.pipeline_list, scope.row)"></el-button>
                            </el-tooltip>
                            <el-button class="ge-activity-list-icon icon-geshop-add-small is-lock" v-if="scope.row.is_lock == 1" @click.stop="createPage(scope.row.id, scope.row.group_info.lang_list, scope.row.group_info.platform_list, scope.row.is_lock, scope.row.create_user, scope.row.group_info.pipeline_list, scope.row)"></el-button>
                            <el-tooltip content="上线" placement="bottom" effect="light">
                                <el-button class="ge-activity-list-icon icon-geshop-online" style="font-size:24px;" v-show="scope.row.is_lock == 0" @click.stop="ZF_verifyActivity(scope.row.id, 2, scope.row.is_lock, scope.row.create_user)"
                                           v-if="permissions.includes('activity/activity/verify') && [1, 4].includes(scope.row.status)">
                                </el-button>
                            </el-tooltip>
                            <el-tooltip content="上线" placement="bottom" effect="light">
                                <el-button class="ge-activity-list-icon icon-geshop-online" style="font-size:24px;color:#9E9E9E;background-color:#ffffff;" v-show="scope.row.is_lock == 1"
                                           @click.stop="ZF_verifyActivity(scope.row.id, 2, scope.row.is_lock, scope.row.create_user)" v-if="permissions.includes('activity/activity/verify') && [1, 4].includes(scope.row.status)">
                                </el-button>
                            </el-tooltip>
                            <el-tooltip content="下线" placement="bottom" effect="light">
                                <el-button class="ge-activity-list-icon icon-geshop-offline" style="font-size:24px;" v-show="scope.row.is_lock == 0" @click.stop="ZF_verifyActivity(scope.row.id, 4, scope.row.is_lock, scope.row.create_user)"
                                           v-if="permissions.includes('activity/activity/verify') && scope.row.status == 2"></el-button>
                            </el-tooltip>
                            <el-tooltip content="下线" placement="bottom" effect="light">
                                <el-button class="ge-activity-list-icon icon-geshop-offline" style="font-size:24px;color:#9E9E9E;background-color:#ffffff;" v-show="scope.row.is_lock == 1"
                                           @click.stop="ZF_verifyActivity(scope.row.id, 4, scope.row.is_lock, scope.row.create_user)" v-if="permissions.includes('activity/activity/verify') && scope.row.status == 2"></el-button>
                            </el-tooltip>
                            <el-dropdown split-button style="margin-left:10px;">
                                <el-dropdown-menu slot="dropdown">
                                    <el-dropdown-item type="primary" size="small" @click.native="editActivity(scope.row, scope.row.is_lock, scope.row.create_user)">编辑</el-dropdown-item>
                                    <el-dropdown-item type="danger" size="small" @click.native="removeActivity(scope.row.id, scope.row.is_lock, scope.row.create_user)">删除</el-dropdown-item>
                                    <el-dropdown-item type="primary" @click.native="viewDetail(scope.row)" size="small">查看二维码</el-dropdown-item>
                                    <el-dropdown-item type="primary" @click.native="handleActivityLabel(scope.row)" size="small" v-if="scope.row.is_frequently === 0">设置常用</el-dropdown-item>
                                    <el-dropdown-item type="primary" @click.native="handleActivityLabel(scope.row)" size="small" v-if="scope.row.is_frequently === 1">移除常用</el-dropdown-item>
                                </el-dropdown-menu>
                            </el-dropdown>
                            <span style="margin-left:10px;line-height:80px;">锁定</span>
                            <el-switch v-model="scope.row.lock_status" @change="ZF_lockingActivity(scope.row.id, scope.row.is_lock, scope.row.create_user, $event)"></el-switch>
                        </template>
                    </el-table-column>
                </el-table>
            </el-col>
        </el-row>

        <el-row v-if="total > 10">
            <el-col :span="24" class="text-right geshop-article-page">
                <el-pagination layout="sizes, prev, pager, next" :page-size="10" :current-page="currentPage" :total="total"
                               @current-change="handleCurrentChange" @size-change="handleSizeChange"></el-pagination>
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
                        <el-col :span="24" class="text-center" v-if="Boolean(currentActivityRow.qrcode)"><img alt="二维码" :src="currentActivityRow.qrcode"
                                                                                                              width="120"></el-col>
                        <el-col :span="24" class="text-center" v-if="Boolean(currentActivityRow.preview)">
                            <a :href="currentActivityRow.preview" class="activity-detail-link">{{ currentActivityRow.name }}</a>
                        </el-col>
                    </el-row>
                </div>
            </el-card>
        </el-dialog>

        <!-- 新增编辑活动 start -->
        <el-dialog
            :title="activityForm.dialogTitle"
            :visible.sync="dialogActivityVisible"
            class="geshop-new-activities"
            @close="closeDialogActivity">
            <el-form :model="activityForm" :rules="activityRules" ref="activityForm" v-loading="obsLoading" class="home_add_piepeline pipe_tab_pane">

                <el-form-item label="名称" prop="name" class="geshop-new-activities-name" v-on:keyup.native="handleNameKeyup">
                    <el-input v-model="activityForm.name"></el-input>
                    <span class="count-tip-box">{{ actNameCount }}/100</span>
                </el-form-item>

                <el-form-item label="简介" prop="description" class="geshop-new-activities-introduction" v-on:keyup.native="handleIntroductionKeyup">
                    <el-input type="textarea" v-model="activityForm.description" :rows="4" placeholder="请简单描述一下这个活动......"></el-input>
                    <span class="count-tip-box">{{ actIntroductionCount }}/200</span>
                </el-form-item>

                <!-- 新增编辑状态应用端口 -->
                <p class="geshop-activity-place-channel-tabs-title"><span class="icon">*</span>应用端口</p>
                <template>
                    <el-tabs v-model="activityForm.place" class="geshop-activity-place-channel-tabs" @tab-click="handlePlaceChannelTabClick">
                        <el-tab-pane v-for="platform in allPlatforms" :disabled="activityForm.status==2 && activityForm.currentDisabledPlatform.indexOf(platform.code) != -1" :label="platform.name" :name="platform.code" :key="platform.code">
                            <!-- 新增渠道 -->
                            <el-checkbox v-model="activityForm.all_pipe_selected[platform.code]['check_all_pipe']"
                                         :indeterminate="activityForm.all_pipe_selected[platform.code]['check_indeterminate']"
                                         @change="handlePlatCheckAll" style="margin-right:10px">全选</el-checkbox>

                            <!-- 新增渠道 START -->
                            <el-form-item
                                label="渠道"
                                class="geshop-new-activities-lang activity-place-channel-item pipeline_list is-required"
                                v-if="activityForm.status==1">

                                <el-tabs v-model="activityForm.all_pipe_selected[platform.code].currentChannel">
                                    <template>
                                        <el-tab-pane
                                            v-for="(item,index) in supportPipelines[platform.code]"
                                            :key="index"
                                            :label="item.name"
                                            :name="item.code">

                                            <!-- 渠道语言选择 -->
                                            <el-form-item
                                                label="语言"
                                                class="pipeline_list_lang is-required"
                                                v-if="activityForm.all_pipe_selected[platform.code].pipeList[item.code]">

                                                <!-- checkbox -->
                                                <dialogCreateLanguagePicker
                                                    :platform="platform.code"
                                                    :pipeline="item.code"
                                                    :check_all.sync="activityForm.all_pipe_selected[platform.code].pipeList[item.code]['check_all']"
                                                    :indeterminate.sync="activityForm.all_pipe_selected[platform.code].pipeList[item.code]['indeterminate']"
                                                    :value.sync="activityForm.all_pipe_selected[platform.code].pipeList[item.code]['value']"
                                                    :lang_list="item.lang_list"
                                                />

                                            </el-form-item>

                                        </el-tab-pane>
                                    </template>
                                </el-tabs>

                            </el-form-item>
                            <!-- 新增渠道 END -->

                            <!-- 编辑渠道 START-->
                            <el-form-item
                                label="渠道"
                                class="geshop-new-activities-lang activity-place-channel-item pipeline_list is-required"
                                v-if="activityForm.status == 2">

                                <el-tabs v-model="activityForm.all_pipe_selected[platform.code].currentChannel">
                                    <template>
                                        <el-tab-pane
                                            v-for="(item,index) in supportPipelines[platform.code]"
                                            :key="index"
                                            :label="item.name"
                                            :name="item.code">

                                            <el-form-item
                                                class="pipeline_list_lang is-required"
                                                label="语言"
                                                v-if="activityForm.all_pipe_selected[platform.code].pipeList[item.code]">

                                                <!-- checkbox -->
                                                <dialogCreateLanguagePicker
                                                    :platform="platform.code"
                                                    :pipeline="item.code"
                                                    :check_all.sync="activityForm.all_pipe_selected[platform.code].pipeList[item.code]['check_all']"
                                                    :indeterminate.sync="activityForm.all_pipe_selected[platform.code].pipeList[item.code]['indeterminate']"
                                                    :value.sync="activityForm.all_pipe_selected[platform.code].pipeList[item.code]['value']"
                                                    :disabled="activityForm.all_pipe_original[platform.code].pipeList[item.code]['value']"
                                                    :lang_list="item.lang_list"
                                                />

                                            </el-form-item>
                                        </el-tab-pane>
                                    </template>
                                </el-tabs>

                            </el-form-item>
                            <!-- 编辑渠道 END -->

                            <!-- 编辑增加语言 显示同步 -->
                            <template
                                v-if="activityForm.all_pipe_edit_info[platform.code]['sync_show'] === true">
                                <el-form-item label="默认同步信息" prop="syncChannelValue" class="geshop-new-activities-lang geshop-add-edit-activity-form" style="margin-top:15px;">
                                    <el-radio-group v-model="activityForm.all_pipe_edit_info[platform.code].pipe_selected">
                                        <el-radio v-for="(item, index) in activityForm.all_pipe_edit_info[platform.code].pipe_arr" :key="index" :label="item">{{ item.name }}</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                                <p style="margin:0;color:#b7b7b7;">勾选后会同步该渠道下的子活动信息，请尽快去“编辑子页面”进行信息更新</p>
                            </template>

                        </el-tab-pane>
                    </el-tabs>
                </template>

                <el-form-item class="geshop-new-activities-btn">
                    <el-button @click="resetForm('activityForm')" size="small">取消</el-button>
                    <el-button type="primary" @click="submitForm('activityForm')" size="small" :loading="submitLoading">确定</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
        <!-- 新增编辑活动 end -->

        <!-- 新增编辑子页面 start -->
        <el-dialog :title="pageForm.dialogTitle" :visible.sync="dialogPageVisible" class="geshop-new-child-page" @close="subDialogClose">
            <el-form :model="publicPageForm" :rules="publicPageRules" ref="publicPageForm">
                <el-form-item label="专题活动名称" prop="title" class="gs-col-all">
                    <el-input v-model="publicPageForm.title" @change="updatePageTitle"></el-input>
                </el-form-item>
                <el-form-item label="下线时间" prop="end_time" class="gs-col-all end-time" style="margin:10px 0px 10px">
                    <el-date-picker v-model="publicPageForm.end_time" type="datetime" :disabled="this.currentPipeline != this.firstChannel"
                                    v-on:change="setChildEndTime" :picker-options="pickerOptions1" value-format="timestamp"></el-date-picker>
                </el-form-item>
                <el-form-item label="已选应用端口" prop="place" class="gs-col-all child-page-place">
                    <el-checkbox-group v-model="publicPageForm.place">
                        <el-checkbox v-for="(item,key) in pagePlaces" :label="item.code" disabled :key="key">{{ item.name }}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>

                <template v-if="site_group_code.indexOf('zf') >= 0">
                    <el-form-item label="是否应用原生专题模式" v-if="publicPageForm.is_native_show">
                        <el-radio-group v-model="publicPageForm.is_native_theme" :disabled="nativeDisabled" @change="nativeThemeChange">
                            <el-radio :label="1">是</el-radio>
                            <el-radio :label="0">否</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item label="国家站活动链接是否需要自动跳转">
                        <el-radio v-model="publicPageForm.is_redirect_country" :label="1">是</el-radio>
                        <el-radio v-model="publicPageForm.is_redirect_country" :label="0">否</el-radio>
                    </el-form-item>

                    <el-form-item>
                        <el-radio-group v-model="linkType" :disabled="radioDisabled" @change="linkTypeChange">
                            <el-radio :label="0">专题链接</el-radio>
                            <el-radio :label="1">博客链接</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </template>

            </el-form>
            <el-form :model="pageForm" :rules="pagePipeRules" class="home_add_piepeline" ref="pagePipeRules">
                <!-- 子活动渠道 -->
                <el-form-item class="pipeline_list is-required" label="渠道">
                    <el-tabs type="card" @tab-click="handleTabClick('pipe')" v-model="currentPipeline">
                        <el-tab-pane v-for="item in channelList" :label="item.name" :name="item.code" :key="item.code"></el-tab-pane>
                    </el-tabs>
                </el-form-item>

                <!-- 渠道下公共-->
                <el-form-item :label="linkLabel" prop="url_name" class="gs-col-all child-page-url">
                    <label class="current-site-url">{{currentSiteUrl}}/</label>
                    <el-input v-model="pageForm.url_name" @change="updatePageUrlName" v-on:keyup.native="handleUrlKeyup" style="max-width: 745px;"></el-input>
                    <span class="count-tip-box">{{ urlCount }}/64</span>
                    <label>.html</label>
                </el-form-item>

                <el-form-item label="PC活动结束后跳转链接" v-if="pageForm.pc_status" prop="end_url" class="child-page-link">
                    <el-row :gutter="20">
                        <el-col :span="15" style="width:400px;">
                            <el-input v-model="pageForm.pc_end_url" placeholder="请输入url链接" @change="updatePCEndUrl"></el-input>
                        </el-col>
                        <el-col class="child-page-note">
                            <span>备注：不填默认为跳转至首页</span>
                        </el-col>
                    </el-row>
                </el-form-item>
                <el-form-item label="M活动结束后跳转链接" v-if="pageForm.m_status" prop="end_url" class="child-page-link">
                    <el-row :gutter="20">
                        <el-col :span="15" style="width:400px;">
                            <el-input v-model="pageForm.m_end_url" placeholder="请输入url链接" @change="updateMEndUrl"></el-input>
                        </el-col>
                        <el-col class="child-page-note">
                            <span>备注：不填默认为跳转至首页</span>
                        </el-col>
                    </el-row>
                </el-form-item>

                <!-- 子活动语言 -->
                <el-form-item class="pipeline_list pipeline_list_lang is-required" label="语言">
                    <el-tabs type="card" @tab-click="handleTabClick('lang')" v-model="currentLanguage" v-if="pageForm.channelLanguageArr && pageForm.channelLanguageArr.lang_list">
                        <el-tab-pane v-for="(item,index) in pageForm.channelLanguageArr.lang_list" :label="item.name" :name="item.code" :key="index"></el-tab-pane>
                    </el-tabs>
                </el-form-item>
            </el-form>


            <el-form :model="pageForm" :rules="pageRules" ref="pageForm" class="geshop-new-child-page-title">
                <!--<el-form-item label="语言" class="gs-col-all"><p class="channel-language-text">{{ pageForm.channelLanguage }}</p></el-form-item>-->
                <el-form-item label="PC端自动跳转M端链接" prop="redirect_url" class="gs-col-all" v-show="pageForm.need_redirect">
                    <el-input v-model="pageForm.redirect_url" placeholder="PC端自动跳转M端链接" @change="updatePageField($event,'redirect_url')"></el-input>
                </el-form-item>
                <el-form-item label="PC端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.pc_status" class="child-page-model">
                    <el-button type="primary" size="small" :disabled="Boolean(pageForm.id)" @click="handleModelTempSelect('pc')">选择模板</el-button>
                    <el-tag type="info">{{ pageForm.tpl_name }}</el-tag>
                </el-form-item>

                <el-form-item v-if="publicPageForm.is_native_theme == 0">
                    <el-form-item label="M端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.m_status" class="child-page-model">
                        <el-button type="primary" size="small" :disabled="Boolean(pageForm.id)" @click="handleModelTempSelect('wap')">选择模板</el-button>
                        <el-tag type="info">{{ pageForm.m_tpl_name }}</el-tag>
                    </el-form-item>
                    <el-form-item label="APP端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.app_status" class="child-page-model">
                        <el-button type="primary" size="small" :disabled="Boolean(pageForm.id)" @click="handleModelTempSelect('app')">选择模板</el-button>
                        <el-tag type="info">{{ pageForm.app_tpl_name }}</el-tag>
                    </el-form-item>
                </el-form-item>

                <el-form-item v-if="publicPageForm.is_native_theme == 1">
                    <el-form-item label="移动端页面模板" prop="model" v-if="!Boolean(pageForm.id) && pageForm.native_status" class="child-page-model">
                        <el-button type="primary" size="small" :disabled="Boolean(pageForm.id)" @click="handleModelTempSelect('native')">选择模板</el-button>
                        <el-tag type="info">{{ pageForm.native_tpl_name }}</el-tag>
                    </el-form-item>
                </el-form-item>

                <el-form-item label="SEO标题" class="child-page-keywords is-required">
                    <el-input v-model="pageForm.seo_title" @change="updatePageField($event,'seo_title')" placeholder=""></el-input>
                </el-form-item>
                <el-form-item label="SEO关键字" prop="keywords" class="child-page-keywords">
                    <el-input v-model="pageForm.keywords" @change="updatePageField($event,'keywords')" placeholder="有利于SEO优化"></el-input>
                </el-form-item>
                <el-form-item label="统计代码" prop="statistics_code" class="child-page-statistical-code">
                    <el-input type="textarea" v-model="pageForm.statistics_code" v-on:keyup.native="handleCodeKeyup" :rows="4" @change="updatePageField($event,'statistics_code')"></el-input>
                    <span class="count-tip-box">{{ codeCount }}/200</span>
                </el-form-item>
                <el-form-item label="SEO简介" prop="description" class="child-page-introduction">
                    <el-input type="textarea" v-model="pageForm.description" v-on:keyup.native="handleDescriptionKeyup" :rows="4"
                              @change="updatePageField($event,'description')" placeholder="有利于SEO优化"></el-input>
                    <span class="count-tip-box">{{ pageIntroductionCount }}/200</span>
                </el-form-item>
                <!-- 分享模块 start -->
                <div class="share-container" style="clear:both">
                    <el-form-item style="clear:both;">
                        <h3 style="border-bottom:1px solid #EEE;">导航分享信息</h3>
                    </el-form-item>
                    <el-form-item label="分享入口" prop="share_place" class="geshop-new-activities-place">
                        <el-checkbox-group v-model="pageForm.share_place" @change="handleSharePlaceChange">
                            <el-checkbox v-for="item in pageForm.share_places" :label="item" :key="item">{{ item }}</el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                    <el-form-item label="分享图片" prop="share_image" class="child-page-keywords" style="position:relative;">
                        <el-upload action="/component/index/upload-logo" name="files" style="position:absolute;top:41px;z-index:9;" accept="image/jpg,image/jpeg,image/png"
                                   :on-progress="handleUploadProgress"
                                   :on-success="handleUploadSuccess"
                                   :on-exceed="handleUploadExceed"
                                   :on-error="handleUploadError"
                                   :show-file-list="false"
                                   :before-upload="handleBeforeUpload">
                            <el-button class="el-icon-picture" :plain="true"></el-button>
                            <!-- <div slot="tip" class="el-upload__tip">只能上传jpeg/png文件，且不超过3M</div> -->
                        </el-upload>
                        <el-input v-model="pageForm.share_image" @change="updatePageField($event,'share_image')" style="padding-left:50px;box-sizing: border-box;" placeholder=""></el-input>
                    </el-form-item>
                    <el-progress :percentage="pageForm.uploadpercent" v-if="pageForm.uploadloading"></el-progress>
                    <el-form-item label="分享标题" prop="share_title" class="child-page-keywords">
                        <el-input v-model="pageForm.share_title" @change="updatePageField($event,'share_title')" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="分享描述" prop="share_desc" class="child-page-keywords">
                        <el-input v-model="pageForm.share_desc" @change="updatePageField($event,'share_desc')" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="分享链接" prop="share_link" class="child-page-keywords">
                        <el-input v-model="pageForm.share_link"  @change="updatePageField($event,'share_link')" placeholder=""></el-input>
                    </el-form-item>
                </div>
                <!-- 分享模块 end -->
                <el-form-item class="child-page-btns" style="clear:both">
                    <el-button @click="resetForm('pageForm')" size="small">取消</el-button>
                    <el-button type="primary" @click="submitForm('pageForm')" size="small" :loading="submitLoading">确定</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
        <!-- 新增编辑子页面 end -->

        <!-- 查看访问链接 -->
        <dialogReleaseURL ref="dialogReleaseURL" />

        <!-- M转APP start -->
        <el-dialog title="M转APP" :visible.sync="dialogConvertAPP">
            <el-form :model="convertForm" :rules="convertRules" ref="convertForm" class="home_add_piepeline pipe_tab_pane">
                <el-row>
                    <el-col>
                        <span class="gb-label-title gb-label-bt gb-line-inline" style="margin: 0 0 15px;">被同步信息</span>
                    </el-col>
                </el-row>

                <el-form-item label="被转化的渠道" prop="source_channelCurrent">
                    <el-select
                        v-model="convertForm.source_channelCurrent"
                        clearable
                        placeholder="请选择"
                        @change="handleChangeConvertAppPipe">
                        <el-option
                            v-for="item in convertForm.wap_supportChannel"
                            :key="item.code"
                            :value="item.code"
                            :label="item.name">
                        </el-option>
                    </el-select>
                </el-form-item>
                <!-- 被同步语言 -->
                <el-form-item label="被转化的语言页面" prop="source_langCurrent">
                    <el-select v-model="convertForm.source_langCurrent" clearable placeholder="请选择"
                               v-if="convertForm.wap_supportChannel[convertForm.source_channelCurrent]">
                        <el-option v-for="item in convertForm.wap_supportChannel[convertForm.source_channelCurrent]['lang_list']"
                                   :key="item.code"
                                   :value="item.code"
                                   :label="item.name"></el-option>
                    </el-select>
                </el-form-item>

                <el-row>
                    <el-col>
                        <span class="gb-label-title gb-label-bt" style="margin: 0 0 15px;">转化到的页面内容</span>
                    </el-col>
                </el-row>

                <el-form-item label="选择APP端活动" prop="activity_id" placeholder="请选择活动" v-if="convertForm.is_group==0">
                    <el-select
                        v-model="convertForm.activity_id"
                        @change="getAppPages($event)">
                        <el-option
                            v-for="item in appActivities"
                            :label="item.name"
                            :value="item.id"
                            :key="item.id">
                        </el-option>
                    </el-select>
                </el-form-item>

                <el-form-item label="选择活动子页面" prop="page_id" v-if="convertForm.is_group==0">
                    <el-select
                        v-model="convertForm.page_id"
                        placeholder="请选择子页面"
                        @change="handleChangeConvertAppPges">
                        <el-option
                            v-for="item in appPages"
                            :label="item.title"
                            :value="item.id"
                            :key="item.id">
                        </el-option>
                    </el-select>
                </el-form-item>
                <!-- 有关联+无关联 同步到的渠道-->
                <el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place pipeline_list is-required">
                    <el-tabs v-model="convertForm.target_channelCurrent">
                        <el-tab-pane v-for="(item,key) of convertForm.app_supportChannel" :label="item.name" :name="item.key"
                                     :key="item.key">
                            <el-form-item label="语言" prop="lang" class="geshop-new-activities-lang is-required"
                                          v-if="Object.keys(convertForm.target_channelLang).length > 0">
                                <el-checkbox v-model="convertForm.target_channelLang[item.key].check_all"
                                             :indeterminate="convertForm.target_channelLang[item.key].indeterminate"
                                             @change="handleConvertCheckAll($event,'convertForm',item)"
                                             style="margin-right:10px">全选</el-checkbox>
                                <el-checkbox-group v-model="convertForm.target_channelLang[key]['value']"
                                                   @change="checked =>handleConvertChange(checked,item.key,'convertForm')"
                                                   class="group_lang_list">
                                    <el-checkbox v-for="childItem of item.language" :label="childItem.key?childItem.key:childItem.lang"
                                                 :key="childItem.key?childItem.key:childItem.lang">
                                        {{childItem.name?childItem.name:childItem.langName}}
                                    </el-checkbox>
                                </el-checkbox-group>
                            </el-form-item>

                        </el-tab-pane>
                    </el-tabs>
                </el-form-item>

                <el-row>
                    <el-col>
                        <span class="gb-label-title gb-label-bt" style="margin: 0 0 15px;">同步方式</span>
                    </el-col>
                </el-row>
                <el-form-item label="选择方式">
                    <el-radio-group v-model="convertForm.model">
                        <el-radio label="1">在所选子页面后追加</el-radio>
                        <el-radio label="2">覆盖子页面内容</el-radio>
                    </el-radio-group>
                    <el-alert title="此操作不会删除原APP活动页面内容，将转化过去的内容放至原有页面后面" type="warning" v-if="convertForm.model == 1"
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
        <!-- M转APP end -->

        <!-- PC转M start -->
        <el-dialog title="PC转化至M" :visible.sync="dialogConvertM" @close=closeConvertM>
            <el-form :model="convertForm_M" :rules="convertRules_M" ref="convertForm_M" class="home_add_piepeline pipe_tab_pane">
                <el-row>
                    <el-col>
                        <span class="gb-label-title gb-label-bt gb-line-inline" style="margin: 0 0 15px;">被转化的页面信息</span>
                    </el-col>
                </el-row>
                <el-form-item label="被转化的渠道" prop="source_channelCurrent">
                    <el-select v-model="convertForm_M.source_channelCurrent" clearable placeholder="请选择"
                               @change="handleChangeConvertPipeChange($event)">
                        <el-option v-for="item in convertForm_M.pc_supportChannel" :key="item.code" :value="item.code"
                                   :label="item.name"></el-option>
                    </el-select>
                </el-form-item>
                <!-- 被同步语言 -->
                <el-form-item label="被转化的语言页面" prop="source_langCurrent">
                    <el-select v-model="convertForm_M.source_langCurrent" clearable placeholder="请选择"
                               v-if="convertForm_M.pc_supportChannel[convertForm_M.source_channelCurrent]">
                        <el-option v-for="(item,index) in convertForm_M.pc_supportChannel[convertForm_M.source_channelCurrent]['lang_list']"
                                   :key="item.code"
                                   :value="item.code" :label="item.name"></el-option>
                    </el-select>
                </el-form-item>
                <el-row>
                    <el-col>
                        <span class="gb-label-title gb-label-bt" style="margin: 0 0 15px;">转化到的页面内容</span>
                    </el-col>
                </el-row>

                <el-form-item label="选择M端活动" prop="activity_id" placeholder="请选择活动" v-if="convertForm_M.is_group==0">
                    <el-select v-model="convertForm_M.activity_id" @change="getMPages($event)">
                        <el-option v-for="item in mActivities" :label="item.name" :value="item.id" :key="item.id"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="选择活动子页面" prop="page_id" v-if="convertForm_M.is_group==0">
                    <el-select v-model="convertForm_M.page_id" placeholder="请选择子页面" @change="handleChangeConvertMPges">
                        <el-option v-for="item in mPages" :label="item.title" :value="item.id" :key="item.id"></el-option>
                    </el-select>
                </el-form-item>

                <!-- 有无关联关系 -->
                <el-form-item label="渠道" prop="channelLang" class="geshop-gb-activities-place pipeline_list is-required">
                    <el-tabs v-model="convertForm_M.target_channelCurrent">
                        <el-tab-pane v-for="(item,key) of convertForm_M.wap_supportChannel" :label="item.name" :name="item.key"
                                     :key="item.key">
                            <el-form-item label="语言" prop="lang" class="geshop-new-activities-lang is-required"
                                          v-if="Object.keys(convertForm_M.target_channelLang).length > 0">
                                <el-checkbox v-model="convertForm_M.target_channelLang[item.key].check_all"
                                             :indeterminate="convertForm_M.target_channelLang[item.key].indeterminate"
                                             @change="handleConvertCheckAll($event,'convertForm_M',item)"
                                             style="margin-right:10px">全选</el-checkbox>
                                <el-checkbox-group v-if="Object.keys(convertForm_M.target_channelLang).length > 0"
                                                   v-model="convertForm_M.target_channelLang[key]['value']"
                                                   @change="checked =>handleConvertChange(checked,item.key,'convertForm_M')"
                                                   class="group_lang_list">
                                    <el-checkbox v-for="childItem of item.language"
                                                 :label="childItem.key?childItem.key:childItem.lang"
                                                 :key="childItem.key?childItem.key:childItem.lang">
                                        {{childItem.name?childItem.name:childItem.langName}}
                                    </el-checkbox>
                                </el-checkbox-group>
                            </el-form-item>

                        </el-tab-pane>
                    </el-tabs>
                </el-form-item>

                <el-row>
                    <el-col>
                        <span class="gb-label-title gb-label-bt" style="margin: 0 0 15px;">同步方式</span>
                    </el-col>
                </el-row>
                <el-form-item label="选择方式">
                    <el-radio-group v-model="convertForm_M.model">
                        <el-radio label="1">在所选子页面后追加</el-radio>
                        <el-radio label="2">覆盖子页面内容</el-radio>
                    </el-radio-group>
                    <el-alert title="此操作不会删除原M活动页面内容，将转化过去的内容放至原有页面后面" type="warning" v-if="convertForm_M.model == 1"
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
        <!-- PC转M end -->

        <!-- 页面模版选择 -->
        <el-dialog title="子页面新增" :visible.sync="modelInfo.visible" @close="handleModelClose" append-to-body class="geshop-page-template">
            <div class="geshop-page-template-title">
                <span class="icon-geshop-backward"></span>
                <span class="geshop-page-template-word">选择页面模板</span>
            </div>
            <el-row>
                <el-tabs type="border-card" class="model-dialog" @tab-click="tplTabClick" v-model="modelInfo.tabActive" v-loading="tplInfo.loading">
                    <el-tab-pane label="我的模板" name="2" ref="tpl0">
                        <div class="model-box" v-for="(item,index) in pageTemplateList" :key="index">
                            <el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect" v-if="item.create_user == siteInfo.userName">
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
                            <el-radio name="modelSelect" :label="item.id" v-model="modelInfo.modelSelect" v-if="item.create_user != siteInfo.userName && item.tpl_type == 1">
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
        <!-- 页面模版选择 END -->

        <!-- M转PP -->
        <el-dialog title="转换成APP端活动" :visible.sync="dialogConvertVisible" width="30%">
            <span><img src="/resources/images/moblie.png" alt="" style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
            <p style="text-align:center;font-size:16px">已转换完成</p>
            <p style="text-align:center;font-size:14px;color:black">您可点击下方按钮进入APP端装修页查看</p>
            <span slot="footer" class="ToM">
				<el-button style="text-align:center" type="primary" @click="convertRedirect()">进入APP端装修页面</el-button>
			</span>
        </el-dialog>
        <!-- M转APP -->

        <!-- PC转M成功 -->
        <el-dialog title="转换成M端活动" :visible.sync="dialogConvertMVisible" width="30%">
            <span><img src="/resources/images/moblie.png" alt="" style="display:block;margin-left:50%;transform: translateX(-50%);"></span>
            <p style="text-align:center;font-size:16px">已转换完成</p>
            <p style="text-align:center;font-size:14px;color:black">您可点击下方按钮进入M端装修页查看</p>
            <span slot="footer" class="ToM">
				<el-button style="text-align:center" type="primary" @click="convertMRedirect()">进入M端装修页面</el-button>
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

        <!-- common dialog start -->

        <!-- 选择预览页面 -->
        <previewComponent
            :data="previewData"
            @handleClose="previewData.dialogVisible = false" />

        <!-- 选择渠道上下线页面 -->
        <onlineOfflineComponent
            :data="onlineOfflineData"
            :submitCallback="handleClickPageChannel"
            :handleChangeCallback="handleChangeAPageChannel" />

        <!-- 三端日志 -->
        <sync-log
            @handleSyncLogChange="handleSyncLogChange"
            @handleSyncLogClose="handleSyncLogClose"
            :data="syncLogData" />

        <!-- common dialog end -->

        <!-- 一键刷新头尾部 -->
        <updateHeadFoot ref="updateHeadFoot" />

        <!--查看所有页面id-->
        <view-page-id ref="viewPageIds" />

    </site-layout>
</template>


<script>
import siteLayout from './layouts/Layout.vue'
import previewComponent from './activityZF/preview.vue'
import onlineOfflineComponent from './activityZF/online-offline.vue'
import syncLog from './activityZF/syncLog.vue'
import {
    getZFActivityList,
    ZF_getPageList,
    ZF_addActivity,
    ZF_updateActivity,
    ZF_deleteActivity,
    ZF_verifyActivity,
    ZF_lockingActivity,
    ZF_refreshSite,
    refreshSelete,
    ZF_batchEditPage,
    ZF_deletePage,
    ZF_verifyPage,
    ZF_getCountrySiteList,
    ZF_getPageTemplateList,
    ZF_getPageTemplateListNative,
    ZF_getAppActivityList,
    ZF_getMActivityList,
    ZF_convertToAppPage,
    ZF_convertToMPage,
    ZF_getAccessLink,
    actReleased,
    ZF_batchAddPage,
    obsLevel1,
    obsLevel2,
    ZF_viewPipelineNewestUrl,
    ZF_checkPipelinePages,
    ZF_getChannelPageInfo,
    ZF_syncOperateLog,
    ZF_getFrequently
} from '../plugin/api'
import { getCookie,getHasPermissionChannel,clone_simple } from '../plugin/mUtils'
import bus from '../store/bus-index.js'
import '../../resources/stylesheets/activityManagement.css'
import '../../resources/stylesheets/activityZF.less'
import '../../resources/stylesheets/icon.css'
import '../../resources/fonts/svg-fonts/style.css'
import '../../resources/stylesheets/frequently.less'

// 新增活动弹窗（部分功能）
import dialogCreateLanguagePicker from '../components/activityZF/dialog-create/language-selector.vue';
import dialogReleaseURL from '../components/activityZF/dialog-release-url.vue';

// 一键刷新头尾部
import updateHeadFoot from './activityZF/update-head-foot.vue';

// 查看所有页面ID
import viewPageId from './activityZF/pageId.vue'

export default {
    components: {
        siteLayout,
        previewComponent,
        onlineOfflineComponent,
        syncLog,
        updateHeadFoot,
        viewPageId,
        dialogCreateLanguagePicker,
        dialogReleaseURL
    },
    name: 'carrousel',
    data() {
        return {
            // 搜索
            site_group_code: getCookie('site_group_code'),
            oSearch:{
                is_frequently: 0 // 是否常用活动 0，1
            },
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
            customUrl: '',
            currentPage: 1,
            pageSize: 10,
            // 选择页面预览
            previewData: {
                dialogVisible: false,
                list: [],
            },

            // 选择渠道上下线页面
            onlineOfflineData: {
                title: '',
                dialogReleaseVisible: false,
                activityPageChannelValue: [],
                activityPageChannelList: [],
                activityPageChannelID: '',
                activityPageChannelStatus: '',
                fullscreenLoading: false
            },

            // 应用渠道
            fallen: '',
            allFallenSiteList: [],

            // 各自端口下的渠道信息
            supportPipelines: {},
            allPlatforms: [],
            originalAllPlatforms: [],

            channelList: [],
            channelObject: {},
            currentPipeline: '',
            editChannelList: [],
            // allSupportCountrySites: [],

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
            isShowAllCheckbox: {
                pc: false,
                wap: false,
                app: false
            },
            isIndeterminate: {
                pc: true,
                wap: true,
                app: true
            },
            channelCheckboxcheckAll: {
                pc: false,
                wap: false,
                app: false
            },

            publicPageForm: {
                place: [''],
                title: '',
                end_time: '',
                is_redirect_country: 0,
                is_native_theme: 0,
                is_native_show: false
            },
            share_entrance: [],

            total: 0,
            activityList: [],
            activityPagePermission:{
                has_design_permission:0,
                special_permissions:[],
                all_special_permissions:{}
            },
            isDetailActive: false,
            currentActivityRow: {
                start_time: new Date().getTime() / 1000,
                end_time: new Date().getTime() / 1000
            },
            activityForm: {
                id: '',
                type: '',
                lang: ['en'],
                place: 'pc',
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
                miss_count_status: 1,

                // 应用渠道
                channelValue: {
                    'pc': [],
                    'wap': [],
                    'app': []
                },
                originChannelValue: {
                    'pc': [],
                    'wap': [],
                    'app': []
                },
                channelValueLength: {
                    'pc': 0,
                    'wap': 0,
                    'app': 0
                },
                currentPlaceTabName: 'pc',
                currentSelectedLanguages: [],
                syncChannelInfo: {
                    'pc': {},
                    'wap': {},
                    'app': {}
                },
                syncChannelValue: {
                    'pc': '',
                    'wap': '',
                    'app': ''
                },
                place_channel_list: {},
                syncChannelStatus: {
                    'pc': 0,
                    'wap': 0,
                    'app': 0
                },
                fallenValue: [],
                editfallenValue: [],
                checkedFallenAll: false,
                isIndeterminate: true,
                channel_lang_obj: {},
                currentSelectedLangs: [],
                currentDisabledPlatform: [],
                // 新增活动选中数据
                all_pipe_selected: {
                    'pc': {'currentChannel':'', 'check_all_pipe': true, 'check_indeterminate': false, 'pipeList':{}},
                    'wap': {'currentChannel':'', 'check_all_pipe': true, 'check_indeterminate': false, 'pipeList':{}},
                    'app': {'currentChannel':'', 'check_all_pipe': true, 'check_indeterminate': false, 'pipeList':{}}
                },
                // 编辑渠道初始值
                all_pipe_original: {
                    'pc': {'currentChannel':'', 'check_all_pipe': true, 'check_indeterminate': false, 'pipeList':{}},
                    'wap': {'currentChannel':'', 'check_all_pipe': true, 'check_indeterminate': false, 'pipeList':{}},
                    'app': {'currentChannel':'', 'check_all_pipe': true, 'check_indeterminate': false, 'pipeList':{}}
                },
                // 编辑同步信息
                all_pipe_edit_info: {
                    'pc': { 'pipe_arr':[], 'pipe_selected':{}, 'sync_show': false },
                    'wap': { 'pipe_arr':[], 'pipe_selected':{}, 'sync_show': false },
                    'app': { 'pipe_arr':[], 'pipe_selected':{}, 'sync_show': false },
                },
                all_pipe_status: 'add'
            },
            activityRules: {
                type: [{
                    required: true,
                    message: '请输入类型',
                    trigger: 'change'
                }],
                name: [{
                    required: true,
                    message: '请输入名称',
                    trigger: 'blur'
                },
                    {
                        max: 100,
                        message: '长度不能超过100个字符',
                        trigger: 'blur'
                    }
                ],
                syncChannelValue: [
                    { required: true, message: '请选择一种渠道', trigger: 'change' }
                ],
                url_name: [
                    { required: true, message: '请输入网址', trigger: 'blur' },
                    { max: 64, min: 3, message: '长度在3-64个字符之间', trigger: 'blur' }
                ],
            },
            dialogActivityVisible: false,
            currentPageRow: {},
            searchType: '1',
            id: '',
            pageForm: {
                id: '',
                keywords: '',
                url_name: '',
                description: '',
                statistics_code: '',
                tpl_id: '0',
                m_tpl_id: '0',
                app_tpl_id: '0',
                native_tpl_id: '0',
                tpl_name: '',
                m_tpl_name: '',
                native_tpl_name: '',
                app_tpl_name: '',
                seo_title: '',
                pc_status: false,
                m_status: false,
                app_status: false,
                native_status: false,
                pc_end_url: '',
                m_end_url: '',

                share_image: '',
                share_title: '',
                share_desc: '',
                share_link: '',
                share_place: ['FB', 'Twitter', 'Google+'],
                share_places: ['FB', 'Twitter', 'Google+', 'Pinterest', 'Snapchat', 'Messenger'],
                uploadloading: false,
                uploadpercent: 0,

                // 渠道
                channelLanguage: [],
                channelLanguageArr: [],
                channelLanguageCode: '',

                data: {},
                refresh_time: 0,
                end_url: '',
                dialogTitle: '子页面新增',
                redirect_url: '',
                obsPage: {
                    selected: {},
                },
                need_redirect: false
            },
            dialogLinksVisible: false,
            dialogReleaseVisible: false,
            dialogPreviewVisible: false,
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
                ],
                end_time: [
                    { required: true, message: '请选择下线时间', trigger: 'blur' }
                ]
            },
            pageRules: {
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
                    { required: true, message: '请输入SEO标题', trigger: 'blur' }
                ],
                pc_activity_title: [{
                    required: true,
                    message: '请选择PC端活动专题名称',
                    trigger: 'change'
                }],
                m_activity_title: [{
                    required: true,
                    message: '请选择M端活动专题名称',
                    trigger: 'change'
                }],
                app_activity_title: [{
                    required: true,
                    message: '请选择APP端活动专题名称',
                    trigger: 'change'
                }],
                seo_title: [{
                    required: true,
                    message: '请输入SEO标题',
                    trigger: 'blur'
                }],
                url_name: [{
                    required: true,
                    message: '请输入有效url地址',
                    trigger: 'blur'
                },
                    {
                        pattern: /(?!^(\d+|[A-Za-z]+|[-]+)$)^[\w-]{3,64}$/,
                        message: '请输入3-64位的英文字母，-，数字的两种及以上组合',
                        trigger: 'blur'
                    }
                ],
                keywords: [{
                    required: false,
                    message: '有利于SEO优化',
                    trigger: 'blur'
                },
                    {
                        max: 200,
                        min: 0,
                        message: '长度在200个字符以内',
                        trigger: 'blur'
                    }
                ],
                description: [
                    { required: false, message: '有利于SEO优化', trigger: 'blur' },
                    { max: 200, min: 0, message: '长度在200个字符以内', trigger: 'blur' }
                ]
            },
            pagePipeRules: {
                url_name: [{
                    required: true,
                    message: '请输入有效url地址',
                    trigger: 'blur'
                },
                    {
                        pattern: /(?!^(\d+|[A-Za-z]+|[-]+)$)^[\w-]{3,64}$/,
                        message: '请输入3-64位的英文字母，-，数字的两种及以上组合',
                        trigger: 'blur'
                    }
                ]
            },
            pickerOptions1: {
                disabledDate(time) {
                    let currentDate = new Date(),
                        year = currentDate.getFullYear(),
                        month = currentDate.getMonth() + 7,
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
            currentLanguage: '', //en
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
                lang: '',
                wap_supportChannel: {},
                app_supportChannel: {},
                source_channelLang: {},
                source_channelCurrent: '',
                target_channelLang: {},
                app_target_channelCurrent: [],
                //单渠道下单语言选中
                source_langCurrent: '',
                group_platform_list: {},
                source_group_platform_list: {}
            },
            convertRules: {
                activity_id: [{
                    required: true,
                    message: '请选择活动',
                    trigger: 'change'
                }],
                page_id: [{
                    required: true,
                    message: '请选择页面',
                    trigger: 'change'
                }],
                source_channelCurrent: [{
                    required: true,
                    message: '请选择被转化渠道',
                    trigger: 'change'
                }],
                source_langCurrent: [{
                    required: true,
                    message: '请选择被转化的语言页面',
                    trigger: 'change'
                }],
                app_target_channelCurrent: [{
                    required: true,
                    message: '请选择同步渠道',
                    trigger: 'change'
                }],
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
                lang: '',
                //渠道语言列表
                pc_supportChannel: {},
                wap_supportChannel: {},
                source_channelLang: {},
                target_channelLang: {},
                source_channelCurrent: '',
                wap_target_channelCurrent: [],
                //单渠道下单语言选中
                source_langCurrent: '',
                group_platform_list: {},
                source_group_platform_list: {},
                pc_group_languages: {}
            },
            convertRules_M: {
                activity_id: [{
                    required: true,
                    message: '请选择活动',
                    trigger: 'change'
                }],
                page_id: [{
                    required: true,
                    message: '请选择页面',
                    trigger: 'change'
                }],
                source_channelCurrent: [{
                    required: true,
                    message: '请选择渠道',
                    trigger: 'change'
                }],
                source_langCurrent: [{
                    required: true,
                    message: '请选择语言',
                    trigger: 'change'
                }],
                wap_target_channelCurrent: [{
                    required: true,
                    message: '请选择同步渠道',
                    trigger: 'change'
                }]
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
            convertLangs: [],
            /* OBS选品 */
            obs: {
                selected: {
                    value: '',
                    id: ''
                },
                data: []
            },
            obsPage: {
                selected: {
                    value: '',
                    id: ''
                },
                data: [],
                pageLanguages: []
            },
            obsLoading: false,
            linkType: 0,
            linkLabel: '专题url',
            radioDisabled: false,
            nativeDisabled: false,
            /* 三端日志 */
            syncLogData:{
                visible:false,
                loading:false,
                page_id:'',
                lists:[],
                pagination:{
                    pageSize:10,
                    pageNo:1,
                    totalCount:0
                }
            },
            timer:null
        }
    },
    computed: {
        firstChannel() {
            if (this.channelList.length > 0) {
                const firstChannel = this.channelList[0].code
                return firstChannel
            } else {
                return ''
            }
        }
    },
    mounted() {
        this.radioDisabled = false;
    },
    watch: {
        currentPipeline: function (val) {
            let channelList = this.channelList,
                currentPipeline = this.currentPipeline,
                currentChannelLanguageCode = this.pageForm.channelLanguageCode

            channelList.forEach((item, index) => {
                if (item.code == currentPipeline) {
                    var urlTemp = currentChannelLanguageCode && item['lang_list'][currentChannelLanguageCode]['url_prefix']
                    if(this.linkType === 1){
                        this.linkLabel = '博客url';
                        this.currentSiteUrl = urlTemp.replace('promotion','blog');
                    }else if(this.linkType === 0){
                        this.linkLabel = '专题url';
                        this.currentSiteUrl = urlTemp.replace('blog','promotion');
                    }
                }
            })
            this.currentPlat = val.toLowerCase()
        },
        // 监听新增编辑活动渠道
        'activityForm.all_pipe_selected':{
            handler(old){
                clearTimeout(this.timer);
                this.timer = setTimeout(()=>{
                    this.handleAllPipe();
                    if(this.activityForm.all_pipe_status === 'edit'){
                        this.handleCompareAdd();
                    }
                },100)
            },
            deep:true
        }
    },
    methods: {
        async getPageTemplates(scrollType) {
            this.tplInfo.loading = true
            let _this = this
            let pageNo = scrollType == 'scroll' ? this.tplInfo.pageNo : 1
            let type = this.modelInfo.tabActive == '1' ? 1 : 0

            let params = {
                place: 1,
                type: type,
                pageNo: pageNo,
                pageSize: this.tplInfo.pageSize
            }

            // 如果是选择模板
            if (this.getTmpListStatus) {
                if (this.getTmpListValue == 'native') {
                    params.site_code = `${getCookie('site_group_code')}-${this.activityTabName}`
                } else {
                    params.site_code = `${getCookie('site_group_code')}-${this.getTmpListValue}`
                }
            } else {
                params.site_code = `${getCookie('site_group_code')}-${this.activityTabName}`
            }

            let res;
            if (this.getTmpListValue == 'native') {
                res = await ZF_getPageTemplateListNative(params)
            } else {
                res = await ZF_getPageTemplateList(params)
            }

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
         * 获取活动页的信息
         */
        async getActivities() {
            let params = {
                    pageNo: this.currentPage,
                    pageSize: this.pageSize,
                    name: this.searchWord,
                    create_name: this.createName,
                    type: this.activityType,
                    site_code: `${getCookie('site_group_code')}-${this.activityTabName}`,
                    pipeline: this.fallen,
                    searchType: this.searchType,
                    id: this.id,
                    url_name: this.customUrl,
                    is_frequently: this.oSearch.is_frequently
                },
                res = await getZFActivityList(params)

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

        /**
         * 获取活动页的列表信息
         */
        async ZF_getAppActivityList() {
            let res = await ZF_getAppActivityList({})

            if (res.code == 0) {
                this.appActivityList = res.data

                this.getAppActivities()
                // 选中第一个活动
                this.convertForm.activity_id = this.appActivityList[0].id;
                this.getAppPages(this.appActivityList[0].id)
            }
        },

        /**
         * 获取当前用户在当前站点wap端下的所有活动及页面列表
         */
        async ZF_getMActivityList() {
            let res = await ZF_getMActivityList()

            if (res.code == 0) {
                this.mActivityList = res.data

                this.getMActivities();
                // 选中第一个活动
                this.convertForm_M.activity_id = this.mActivityList[0].id;
                this.getMPages(this.mActivityList[0].id);
            }
        },

        /**
         * PC，M，APP切换
         */
        handleActivityTabClick(event) {
            this.activityTabName = event.name
            this.currentPage = 1
            this.getActivities()
        },

        /**
         * 选择渠道预览
         */
        handlePreviewUrl (dataList) {
            // console.log(dataList);
            let list = []
            Object.keys(dataList).forEach(item =>{
                Object.keys(dataList[item].lang_list).forEach(aLang =>{
                    if(dataList[item].lang_list[aLang].preview_url){
                        list.push(Object.assign({},dataList[item].lang_list[aLang],{name:dataList[item].name + '——' + dataList[item].lang_list[aLang].name}))
                    }
                })
            })
            if(list.length > 0){
                this.previewData.list = list
                // 显示弹窗
                this.previewData.dialogVisible = true
            }else{
                this.$message.warning('无可预览页面')
            }

        },

        /**
         * 上下线勾选应用渠道
         */
        handleChangeAPageChannel (val) {
            this.onlineOfflineData.activityPageChannelValue = val
        },

        /**
         * @desc 渠道展开收起
         */
        handleClickCheckboxItem (place) {
            switch(place) {
                case 'pc':
                    let isPcShowAllCheckbox = !this.isShowAllCheckbox.pc
                    this.isShowAllCheckbox.pc = isPcShowAllCheckbox
                    break
                case 'wap':
                    let isWapShowAllCheckbox = !this.isShowAllCheckbox.wap
                    this.isShowAllCheckbox.wap = isWapShowAllCheckbox
                    break
                case 'app':
                    let isAppShowAllCheckbox = !this.isShowAllCheckbox.app
                    this.isShowAllCheckbox.app = isAppShowAllCheckbox
                    break
                default:
                    break
            }
        },

        /**
         * @desc 渠道全选
         * @param { Boolean } isCheckAll - 是否全选参数
         * @param { String } place - 当前端口 pc,wap,app
         */
        handleCheckChannelAllChange (isCheckAll, place) {
            if (isCheckAll) {
                this.activityForm.channelValue[place] = Object.keys(this.supportPipelines[place])
            } else {
                this.activityForm.channelValue[place] = []
            }
            this.isIndeterminate[place] = false

            // 应用端口切换到wap端时同步pc端勾选的渠道信息
            if (place == 'pc') {
                // 新增活动状态
                if (this.activityForm.status == 1) {
                    this.handleSyncPcToWap()
                    this.channelCheckboxcheckAll.wap = isCheckAll
                }
            }

            let channelValueLength = this.activityForm.channelValue[place].length,
                originChannelVLength = this.activityForm.channelValueLength[place]

            // 编辑活动状态 - 全选显示“默认同步信息”
            if (this.activityForm.status == 2 && channelValueLength != originChannelVLength) {
                this.activityForm.syncChannelStatus[place] = 1
            } else {
                this.activityForm.syncChannelStatus[place] = 0
            }

            this.handleFilterChannelLang()
            this.handleFilterChannelList()
        },

        /**
         * 新增编辑活动应用端口切换
         */
        handlePlaceChannelTabClick (tab) {
            const tabName = tab.name
            this.activityForm.currentPlaceTabName = tabName

            this.handleFilterChannelLang()
            this.handleAllPipe()
        },

        /**
         * 切换到wap端同步pc端勾选的应用端口（这都是啥变态交互！！！）
         */
        handleSyncPcToWap () {
            // pc端勾选的渠道
            let pc_channelValue = this.activityForm.channelValue.pc
            // wap端所有支持的渠道
            let wap_supportChannel = this.supportPipelines.wap
            let res = []

            // key - ZF, ZFES
            // code - ZF, ZFES
            Object.keys(wap_supportChannel).forEach((key) => {
                pc_channelValue.forEach((code) => {
                    // 从wap端所支持的渠道过滤出pc端勾选的渠道
                    if (code == key) {
                        res.push(code)
                    }
                })
            })
            // 给wap端渠道赋值
            this.activityForm.channelValue.wap = res
        },

        /**
         * @description 新增活动渠道表单触发
         * @param { Array } value - 选中的值
         */
        handleChangePlaceChannel (value) {

            // pc, wap, app
            let key = this.activityForm.currentPlaceTabName,
                checkedCount = value.length,
                currentSupportPipelines = Object.keys(this.supportPipelines[key])
            this.channelCheckboxcheckAll[key] = checkedCount === currentSupportPipelines.length
            this.isIndeterminate[key] = checkedCount > 0 && checkedCount < currentSupportPipelines.length


            // 应用端口切换到wap端时同步pc端勾选的渠道信息
            if (key == 'pc') {
                this.handleSyncPcToWap()
                this.channelCheckboxcheckAll.wap = checkedCount === currentSupportPipelines.length
            }

            this.handleFilterChannelLang()
            this.handleFilterChannelList()
        },

        /**
         * @description 编辑活动渠道表单触发
         * @param { Array } value - 选中的值
         */
        handleEditChangePlaceChannel (value) {
            // 有新勾选渠道时才显示“默认同步信息”
            // pc, wap, app
            let key = this.activityForm.currentPlaceTabName,
                checkedCount = value.length,
                currentSupportPipelines = Object.keys(this.supportPipelines[key]),
                channelValueLength = this.activityForm.channelValue[key].length,
                originChannelVLength = this.activityForm.channelValueLength[key]

            this.channelCheckboxcheckAll[key] = checkedCount === currentSupportPipelines.length
            this.isIndeterminate[key] = checkedCount > 0 && checkedCount < currentSupportPipelines.length

            if (channelValueLength != originChannelVLength) {
                this.activityForm.syncChannelStatus[key] = 1
            } else {
                this.activityForm.syncChannelStatus[key] = 0
            }

            this.handleFilterChannelLang()
            this.handleFilterChannelList()
        },

        /**
         * 从端口所有支持渠道中过滤出选择的渠道
         */
        handleFilterChannelList () {
            let	place_channel_list = {
                'pc': [],
                'wap': [],
                'app': []
            }

            let channelValue = this.activityForm.channelValue
            // 遍历选中的渠道
            // key - pc,wap,app
            Object.keys(channelValue).forEach((key) => {
                if (typeof channelValue[key] === 'undefined') {
                    return false
                }
                // currentSupportPipeline - 渠道对象 { code: 'ZF', lang_list: {en: { code: 'en', name: '英文', url_prefix: '...' }}, name: '全球站' }
                let currentSupportPipeline = this.supportPipelines[key]
                // item - 渠道key值 ["ZF", "ZFES"]
                Object.keys(currentSupportPipeline).map((item) => {
                    // channel - ZF, ZFES
                    channelValue[key].forEach(channel => {
                        if (channel === item) {
                            place_channel_list[key].push(currentSupportPipeline[item])
                        }
                    })
                })
            })

            this.activityForm.place_channel_list = place_channel_list
        },

        /**
         * 过滤勾选渠道的语言
         * @param { Object } supportPipelines - 端口支持的渠道信息
         * @param { Array } currentSelectedChannel - 端口勾选的渠道
         * @returns { Array }
         */
        handleFilterChannelLang () {
            // 新增编辑应用端口当前tab值
            let tabName = this.activityForm.currentPlaceTabName
            // 当前端口支持的渠道信息
            let supportPipelines = this.supportPipelines[tabName]
            // 当前端口选择的渠道
            let currentSelectedChannel = this.activityForm.channelValue[tabName]
            let res = []

            // 遍历支持的渠道信息
            // key - ZF, ZFES, ...
            // code - ZF, ZFES, ...
            Object.keys(supportPipelines).forEach((key) => {
                currentSelectedChannel.forEach((code) => {

                    if (code == key) {
                        let lang_list = supportPipelines[key]['lang_list']
                        let first_lang = Object.keys(lang_list)[0]
                        let first_lang_name = lang_list[first_lang] ? lang_list[first_lang]['name'] : '';
                        res.push(first_lang_name)
                    }
                })
            })
            this.activityForm.currentSelectedLanguages = res
        },

        /**
         * 上下线接口请求
         */
        async handleClickPageChannel () {

            // 判断是否勾选渠道
            if (this.onlineOfflineData.activityPageChannelValue.length == 0) {
                this.$message({
                    message: '请勾选渠道',
                    type: 'warning'
                })
                return false
            }

            this.onlineOfflineData.fullscreenLoading = true
            let activityPageChannelArr = [], pipelineArr = []
            this.onlineOfflineData.activityPageChannelValue.forEach((item) => {
                this.onlineOfflineData.activityPageChannelList.map((array) => {
                    if (item == array.code) {
                        return activityPageChannelArr.push(array)
                    }
                })
            })

            activityPageChannelArr.forEach((item) => {
                let pipe = {}
                pipe.pipeline = item.code
                pipe.lang = Object.keys(item.lang_list).toString()
                pipelineArr.push(pipe)
            })
            let params = {
                    id: this.onlineOfflineData.activityPageChannelID,
                    status: this.onlineOfflineData.activityPageChannelStatus,
                    batch_data: JSON.stringify(pipelineArr)
                },
                res = await ZF_verifyPage(params)

            if (res.code === 0) {
                this.$message({
                    message: res.message,
                    type: 'success'
                })
                this.onlineOfflineData.fullscreenLoading = false
                this.onlineOfflineData.dialogReleaseVisible = false
                this.getActivities()
                this.expandRowKeys = []
            } else {
                this.$message({
                    message: res.message,
                    type: 'warning'
                })
                this.onlineOfflineData.fullscreenLoading = false
                this.onlineOfflineData.dialogReleaseVisible = true
            }

        },

        /**
         * PC转M
         * change 选中渠道下第一个语言
         */
        handleChangeConvertPipeChange(val) {
            if(val){
                this.convertForm_M.source_channelCurrent = val
                let langList = this.convertForm_M['pc_supportChannel'][val].lang_list
                this.convertForm_M.source_langCurrent = Object.keys(langList)[0];
            }
        },

        handleChangeConvertMTarget(val) {
            if(val){
                this.convertForm_M.wap_target_channelCurrent = val
            }
        },

        /**
         * 2019/07
         * M转APP
         */
        handleChangeConvertAppPipe(val) {
            if(val){
                this.convertForm.source_channelCurrent = val
                let langList = this.convertForm['wap_supportChannel'][val].lang_list
                this.convertForm.source_langCurrent = Object.keys(langList)[0];
            }
        },

        handleChangeConvertAppTarget(val) {
            this.convertForm.app_target_channelCurrent = val
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

        handleUrlKeyup () {
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

        /**
         * 新增活动
         */
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

            this.isShowAllCheckbox = {
                pc: false,
                wap: false,
                app: false
            }

            this.isIndeterminate = {
                pc: true,
                wap: true,
                app: true
            }

            this.channelCheckboxcheckAll = {
                pc: false,
                wap: false,
                app: false
            }

            this.activityForm.site_code = ''

            // 全部端口
            let originAllPlatform = this.originalAllPlatforms
            this.allPlatforms = originAllPlatform

            // 当前端口
            let platforms = this.allPlatforms
            this.activityForm.place = platforms[0]['code'] // 新增活动默认选中第一个端口
            this.activityForm.currentPlaceTabName = platforms[0]['code']

            // 初始化“默认同步信息”模板
            this.activityForm.syncChannelStatus.pc = 0
            this.activityForm.syncChannelStatus.wap = 0
            this.activityForm.syncChannelStatus.app = 0

            // 渠道、语言
            this.activityForm.channel_lang_obj = {}

            // 初始化不同应用端口下的渠道勾选
            // console.log(this.supportPipelines);
            // 初始化都默认勾选第一个
            let supportPipelines = this.supportPipelines
            let pc_supportPipelines = supportPipelines.pc,
                wap_supportPipelines = supportPipelines.wap,
                app_supportPipelines = supportPipelines.app

            let first_pc_pipeline = pc_supportPipelines[Object.keys(pc_supportPipelines)[0]],
                first_wap_pipeline = wap_supportPipelines[Object.keys(wap_supportPipelines)[0]],
                first_app_pipeline = app_supportPipelines && app_supportPipelines[Object.keys(app_supportPipelines)[0]];

            // 初始化渠道
            var obj = {};
            for(let key in supportPipelines) {
                obj[key] = [Object.keys(supportPipelines[key])[0]]
            }
            this.activityForm.channelValue = obj

            // 初始化语言
            this.activityForm.currentSelectedLanguages = [first_pc_pipeline['lang_list']['en']['name']]

            let date = new Date()
            this.activityDefaultTime[0] = date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds()

            // 初始化activityForm.place_channel_list字段
            this.handleFilterChannelList()

            // 初始化端下渠道选中 all_pipe_selected
            this.initActivityGroup('add');

            this.dialogActivityVisible = true
        },

        /**
         * 2019/07/01
         * 初始化新增编辑渠道
         * 默认语言全选中
         */
        initActivityGroup(type='add'){
            let originAllPlatform = this.originalAllPlatforms;
            let supportPipelines = this.supportPipelines;
            originAllPlatform.forEach((item) =>{
                let pipe_list = {};
                this.activityForm.all_pipe_selected[item.code].currentChannel = Object.keys(supportPipelines[item.code])[0];
                Object.keys(supportPipelines[item.code]).map(aPipe=>{
                    if(type === 'add'){
                        pipe_list[aPipe] = {value:Object.keys(supportPipelines[item.code][aPipe].lang_list),check_all:true,indeterminate:false};
                    }else if(type === 'edit'){
                        pipe_list[aPipe] = {value:[],check_all:false,indeterminate:false};
                    }

                });
                this.activityForm.all_pipe_selected[item.code]['pipeList'] = pipe_list
            })
            this.activityForm.all_pipe_status = type
        },

        /**
         * 2019/07/01
         * 初始化编辑渠道 all_pipe_selected
         */
        initEditActivityGroup(row){
            let row_support_list = row.group_info.support_list;
            let plat_lists = Object.keys(row_support_list);
            let all_pipe_selected = clone_simple(this.activityForm.all_pipe_selected);
            plat_lists.forEach(aPlat =>{
                let platObj = row_support_list[aPlat]
                Object.keys(platObj).forEach(aPipe =>{
                    if(platObj[aPipe] && all_pipe_selected[aPlat]['pipeList'][aPipe]){
                        all_pipe_selected[aPlat]['pipeList'][aPipe]['value'] = Object.keys(platObj[aPipe]['lang_list'])
                    }
                })
            })
            this.activityForm.all_pipe_original = clone_simple(all_pipe_selected);
            this.activityForm.all_pipe_selected = all_pipe_selected;

            // 默认同步信息
            let all_pipe_original = this.activityForm.all_pipe_original;
            let supportPipelines = this.supportPipelines;
            Object.keys(all_pipe_original).forEach( aPlat =>{
                let plat_pipe_arr = [];
                Object.keys(all_pipe_original[aPlat]['pipeList']).forEach(aPipe =>{
                    all_pipe_original[aPlat]['pipeList'][aPipe]['value'].forEach( aLang =>{
                        let pipe_item = supportPipelines[aPlat][aPipe];
                        let pipe_lang = Object.keys(pipe_item['lang_list']).length > 0 && pipe_item['lang_list'][aLang] ? pipe_item['lang_list'][aLang].name : '';
                        let aName = `${ pipe_item.name }—${ pipe_lang }`
                        plat_pipe_arr.push({
                            pipe: aPipe,
                            lang: aLang,
                            name: aName
                        })
                    })
                })
                this.activityForm.all_pipe_edit_info[aPlat] = Object.assign({},this.activityForm.all_pipe_edit_info[aPlat],{
                    pipe_selected : plat_pipe_arr[0],
                    pipe_arr : plat_pipe_arr
                })
            })
        },

        /**
         * 2019/07/02
         * 新增活动判断渠道是否全选
         * @returns {boolean[全选，半选]}
         * @constructor
         */
        IsSelectAllPipe(){
            let {all_pipe_selected,currentPlaceTabName} = this.activityForm;
            let currentPipeList = all_pipe_selected[currentPlaceTabName];
            let booleanValue = true;
            let booleanIndeterminate = false;
            try {
                Object.keys(currentPipeList.pipeList).forEach(aPipe =>{
                    if(currentPipeList.pipeList[aPipe]['check_all'] !== true){
                        booleanValue = false;
                        throw new Error('EndIterative');
                    }
                })
            } catch(e) {
                if(e.message!=='EndIterative') throw e;
            }

            try {
                Object.keys(currentPipeList.pipeList).forEach(aPipe =>{
                    if(currentPipeList.pipeList[aPipe]['value'].length !== 0){
                        booleanIndeterminate = true;
                        throw new Error('EndIterative');
                    }
                })
            } catch(e) {
                if(e.message!=='EndIterative') throw e;
            }
            return [booleanValue,booleanValue === true ? false : booleanIndeterminate]
        },

        /**
         * 新增活动端全选check
         */
        handleAllPipe(){
            let {all_pipe_selected,currentPlaceTabName} = this.activityForm;
            this.$set(this.activityForm.all_pipe_selected[currentPlaceTabName],'check_all_pipe',this.IsSelectAllPipe()[0])
            this.$set(this.activityForm.all_pipe_selected[currentPlaceTabName],'check_indeterminate',this.IsSelectAllPipe()[1])
        },

        /**
         * 新增活动 端下渠道全选
         * @param value
         */
        handlePlatCheckAll(value){
            let {all_pipe_selected,currentPlaceTabName} = this.activityForm;
            let a_pipe_selected = all_pipe_selected[currentPlaceTabName];
            let supportPlatPipe = this.supportPipelines[currentPlaceTabName];
            if(value){
                Object.keys(a_pipe_selected.pipeList).forEach((aPipeCode)=>{
                    a_pipe_selected.pipeList[aPipeCode] = {
                        check_all: true,
                        indeterminate: false,
                        value: Object.keys(supportPlatPipe[aPipeCode]['lang_list'])
                    }
                })
            }else{
                Object.keys(a_pipe_selected.pipeList).forEach((aPipeCode)=>{
                    a_pipe_selected.pipeList[aPipeCode] = {
                        check_all: false,
                        indeterminate: false,
                        value: []
                    }
                })
            }
        },

        /**2019/07/04
         * 编辑模式判断新增了语言
         */
        handleCompareAdd () {
            let {
                all_pipe_original,
                all_pipe_selected,
                all_pipe_edit_info,
                currentPlaceTabName
            } = this.activityForm;
            let plat_pipeList = all_pipe_selected[currentPlaceTabName];
            let original_plat_pipeList = all_pipe_original[currentPlaceTabName];
            let plat_eiditInfo = all_pipe_edit_info[currentPlaceTabName];
            try{
                Object.keys(plat_pipeList.pipeList).forEach( aPipe =>{
                    if (_diffs(plat_pipeList.pipeList[aPipe].value, original_plat_pipeList.pipeList[aPipe].value)) {
                        plat_eiditInfo['sync_show'] = true;
                        throw new Error('EndIterative')
                    } else {
                        plat_eiditInfo['sync_show'] = false;
                    }
                })
            } catch(e) {
                if(e.message !== 'EndIterative') throw e;
            }

            /**
             * 判断target arr2存在差异
             * */
            function _diffs(arr1,arr2){
                return arr1.some(val => arr2.indexOf(val) === -1)
            }
        },

        /**
         * 编辑活动
         */
        editActivity(row, is_lock, create_user) {
            if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
                this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            } else {
                // 当前为编辑活动状态
                this.activityForm.status = 2
                this.activityForm.id = row.id
                this.activityForm.type = String(row.type)
                this.activityForm.name = row.name

                this.isShowAllCheckbox = {
                    pc: false,
                    wap: false,
                    app: false
                }

                this.channelCheckboxcheckAll = {
                    pc: false,
                    wap: false,
                    app: false
                }

                this.activityForm.site_code = row.site_code.split('-')[1]
                this.activityForm.place = row.site_code.split('-')[1]

                // 初始化“默认同步信息”模板
                this.activityForm.syncChannelStatus.pc = 0
                this.activityForm.syncChannelStatus.wap = 0
                this.activityForm.syncChannelStatus.app = 0

                // 端下的渠道信息
                let support_list = row.group_info.support_list,
                    pc_channel_value = support_list.pc && Object.keys(support_list.pc),
                    wap_channel_value = support_list.wap && Object.keys(support_list.wap),
                    app_channel_value = support_list.app && Object.keys(support_list.app)

                // 初始化不可用端口（默认都可用）
                this.activityForm.currentDisabledPlatform = []

                // 如果PC、M或APP任何一端下没有渠道信息，则将该端口删除
                if (typeof pc_channel_value === 'undefined') {
                    this.activityForm.currentDisabledPlatform.push('pc')
                }
                if (typeof wap_channel_value === 'undefined') {
                    this.activityForm.currentDisabledPlatform.push('wap')
                }
                if (typeof app_channel_value === 'undefined') {
                    this.activityForm.currentDisabledPlatform.push('app')
                }

                // 初始化端口下渠道信息
                this.activityForm.channelValue.pc = pc_channel_value
                this.activityForm.channelValue.wap = wap_channel_value
                this.activityForm.channelValue.app = app_channel_value
                this.activityForm.channelValueLength.pc = pc_channel_value && pc_channel_value.length
                this.activityForm.channelValueLength.wap = wap_channel_value && wap_channel_value.length
                this.activityForm.channelValueLength.app = app_channel_value && app_channel_value.length

                // 编辑时渠道值（新增活动时提交的数据）
                this.activityForm.originChannelValue.pc = pc_channel_value
                this.activityForm.originChannelValue.wap = wap_channel_value
                this.activityForm.originChannelValue.app = app_channel_value

                // 默认同步信息赋值
                this.activityForm.syncChannelInfo = support_list

                this.activityForm.syncChannelValue.pc = pc_channel_value && pc_channel_value[0]
                this.activityForm.syncChannelValue.wap = wap_channel_value && wap_channel_value[0]
                this.activityForm.syncChannelValue.app = app_channel_value && app_channel_value[0]

                // 初始化语言（默认取第一个端口）
                let channel_name_arr = [],
                    default_platam = Object.keys(support_list)[0],
                    first_channel_support_list = support_list[default_platam],
                    channel_value = {}

                if (default_platam == 'pc') {
                    channel_value = pc_channel_value
                } else if (default_platam == 'wap') {
                    channel_value = wap_channel_value
                } else if (default_platam == 'app') {
                    channel_value = app_channel_value
                }

                channel_value.forEach((channel) => {
                    let lang_list = first_channel_support_list[channel]['lang_list'],
                        first_pc_lang_code = Object.keys(lang_list)[0]
                    channel_name_arr.push(lang_list[first_pc_lang_code]['name'])
                })
                this.activityForm.currentSelectedLanguages = channel_name_arr

                // 编辑活动默认选中第一个端口
                this.activityForm.place = default_platam
                this.activityForm.currentPlaceTabName = default_platam

                // 新增活动时已选应用端口列表
                this.editPlaces = row.group_info.platform_list
                this.editSupportLangs = row.group_info.lang_list

                // 主活动已勾选端口
                let editPlaceArr = []
                row.group_info.platform_list.forEach((item) => {
                    editPlaceArr.push(item.code)
                })
                this.activityForm.editPlace = editPlaceArr

                // 主活动已勾选渠道信息
                let editChannelListArr = [],
                    pipelineList = row.pipeline_list
                Object.keys(pipelineList).map((key) => {
                    let pipeObj = {}
                    pipeObj['code'] = pipelineList[key]['code']
                    pipeObj['name'] = pipelineList[key]['name']
                    return editChannelListArr.push(pipeObj)
                })
                this.editChannelList = editChannelListArr
                this.activityForm.editfallenValue = Object.keys(pipelineList)

                // 初始化activityForm.place_channel_list字段
                this.handleFilterChannelList()

                this.activityForm.description = row.description
                this.activityForm.refresh_time = row.refresh_time
                this.activityForm.dialogTitle = '编辑活动'
                this.actNameCount = this.activityForm.name.split('').length
                this.actIntroductionCount = this.activityForm.description.split('').length

                // 初始化端下渠道选中 all_pipe_selected
                this.initActivityGroup('edit');
                this.initEditActivityGroup(row);

                this.dialogActivityVisible = true
            }
        },

        /**
         * 添加子页面 - 渠道语言关联
         */
        handleChannelLangAssociate(pipeObject, pipe) {
            let lang_list = pipeObject[pipe]['lang_list']
            let lang_arr = []
            Object.keys(lang_list).forEach((item) => {
                lang_arr.push(lang_list[item])
            })
            return lang_arr
        },

        /**
         * 新增子页面
         */
        createPage(activityId, langList, placeList, is_lock, create_user, pipeline_list, row) {
            this.radioDisabled = false
            this.nativeDisabled = false
            this.linkType = 0;
            if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
                this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            } else {
                this.currentPageRow = {}

                // 当前活动渠道信息
                let current_pipeline_list = row.group_info.pipeline_list
                this.channelObject = current_pipeline_list

                // 活动所选渠道信息 { Array } channelList - [{ code: 'ZF', lang_list: {...}, name: '...' } }]
                let channelArray = []
                Object.keys(current_pipeline_list).map((item) => {
                    channelArray.push(current_pipeline_list[item])
                })
                this.channelList = channelArray

                // 新增活动时勾选的端口
                this.pagePlaces = placeList
                // 默认第一个渠道
                let currentPipeline = Object.keys(current_pipeline_list)[0]
                this.currentPipeline = currentPipeline
                let channelLanguage_item = this.handleChannelLangAssociate(current_pipeline_list, currentPipeline)
                this.pageForm.channelLanguageArr = this.channelObject[this.currentPipeline]
                let lang = channelLanguage_item[0]['name']
                this.pageForm.channelLanguage = lang

                this.tplId = '0'
                this.urlName = ''
                this.refreshTime = 0
                this.pageForm.id = ''
                this.pageForm.activity_id = activityId
                this.pageForm.seo_title = ''
                this.titleCount = 0
                this.pageForm.keywords = ''
                this.pageForm.url_name = ''
                this.urlCount = 0
                this.pageForm.description = ''
                this.pageIntroductionCount = 0
                this.pageForm.statistics_code = ''
                this.codeCount = 0
                this.pageForm.redirect_url = ''
                this.pageForm.refresh_time = 0
                this.pageForm.tpl_id = '0' // 每个语种都能选择自己的模板
                this.pageForm.m_tpl_id = '0'
                this.pageForm.app_tpl_id = '0'
                this.pageForm.native_tpl_id = '0'
                this.pageForm.tpl_name = '未选中模板'
                this.pageForm.m_tpl_name = '未选中模板'
                this.pageForm.app_tpl_name = '未选中模板'
                this.pageForm.native_tpl_name = '未选中模板'

                this.pageForm.pc_end_url = ''
                this.pageForm.m_end_url = ''

                this.pageForm.end_url = ''
                this.pageForm.need_redirect = false

                this.pageForm.share_place = ['FB', 'Twitter', 'Google+']
                this.share_entrance = ['FB', 'Twitter', 'Google+']

                this.pageForm.share_image = ''
                this.pageForm.share_title = ''
                this.pageForm.share_desc = ''
                this.pageForm.share_link = ''

                this.publicPageForm.title = ''
                this.publicPageForm.end_time = ''
                this.publicPageForm.is_redirect_country = 0
                this.publicPageForm.is_native_theme = 0

                if (this.siteInfo.site == 'gb') {
                    this.pageForm.obsId = ''
                    this.pageForm.obsName = ''
                }

                let data = {}
                let _this = this

                // 遍历渠道信息
                this.channelList.forEach(function (element) {
                    data[element.code] = {}
                    // 渠道下公共数据
                    data[element.code].channel_common_info = {
                        url_name: '',
                        pc_end_url: '',
                        m_end_url: ''
                    }
                    let lang_list = element.lang_list
                    Object.keys(lang_list).forEach((lang) => {
                        data[element.code][lang] = {
                            keywords: '',
                            url_name: '',
                            description: '',
                            statistics_code: '',
                            redirect_url: '',
                            tpl_id: '0', // 模板id
                            m_tpl_id: '0',
                            app_tpl_id: '0',
                            native_tpl_id: '0',
                            tpl_name: '未选中模板', // 模板名称
                            m_tpl_name: '未选中模板',
                            app_tpl_name: '未选中模板',
                            native_tpl_name: '未选中模板',
                            activity_id: activityId,
                            seo_title: '',
                            end_url: '',
                            pc_end_url: '',
                            m_end_url: '',
                            share_image: '',
                            share_title: '',
                            share_desc: '',
                            share_link: '',

                            // 渠道
                            channelLanguageArr: {},
                            channelLanguageCode: ''
                        }
                    })
                })
                this.pageForm.data = data

                // 默认tab项渠道语言
                this.pageForm.data[currentPipeline].channelLanguageArr = this.channelObject[this.currentPipeline]
                this.pageForm.data[currentPipeline].channelLanguageCode = channelLanguage_item[0]['code']
                this.pageForm.channelLanguageCode = channelLanguage_item[0]['code']
                // 当前语言默认为第一个语言
                this.currentLanguage = channelLanguage_item[0]['code'];

                this.channelList.forEach((item, index) => {
                    if (item.code == this.currentPipeline) {
                        this.currentSiteUrl = item['lang_list'][this.pageForm.channelLanguageCode]['url_prefix']
                    }
                })

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
                if (pagePlacesArr.indexOf('app') != -1) {
                    this.pageForm.app_status = true
                } else {
                    this.pageForm.app_status = false
                }

                this.publicPageForm.is_native_show = false;

                if (pagePlacesArr.indexOf('wap') != -1 && pagePlacesArr.indexOf('app') != -1) {
                    // 是否显示原生专题
                    this.publicPageForm.is_native_show = true
                }

                this.pageForm.native_status = true

                this.currentTemplate = '未选中模板'

                this.pageForm.dialogTitle = '新增子页面'

                /* gb回填 */
                if (this.siteInfo.site == 'gb' && row && row.themeList) {
                    this.getObsPage(row.themeList.theme_id)
                    this.obs.selected = {
                        id: Number(row.themeList.theme_id),
                        name: row.themeList.theme_name
                    }
                }

                let platformKeys = []

                row.group_info.platform_list.forEach(function (element) {
                    platformKeys.push(element.code)
                })

                if (platformKeys.indexOf('pc') != -1 && platformKeys.indexOf('wap') == -1) {
                    this.pageForm.need_redirect = true
                }

                this.dialogPageVisible = true
            }
        },

        /**
         * 编辑子页面
         */
        editPage(row, is_lock, activity_create_user, langList, placeList, pipelineObject) {
            let _this = this
            if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            } else {

                this.channelObject = pipelineObject

                // let lang = this.pageForm.channelLanguageCode ? this.pageForm.channelLanguageCode : row.default_lang
                // let lang = row.default_lang
                let lang = Object.keys(pipelineObject[Object.keys(pipelineObject)[0]].lang_list)[0]
                _this.linkType = row.is_blog;


                let pipelineList = []
                Object.keys(pipelineObject).forEach((key) => {
                    pipelineList.push(pipelineObject[key])
                })

                let currentPipeline = pipelineList[0]['code']
                this.currentPipeline = currentPipeline
                this.pageForm.channelLanguage = pipelineList[0]['lang_list'][lang] ? pipelineList[0]['lang_list'][lang]['name'] : ''
                this.pageForm.channelLanguageCode = pipelineList[0]['lang_list'][lang] ? pipelineList[0]['lang_list'][lang]['code'] : ''
                this.pageForm.channelLanguageArr = this.channelObject[this.currentPipeline]
                this.tplId = ''

                // let lang = this.currentPipeline
                this.currentPageRow = row

                // 主活动语言
                // this.langList = langList

                // 当前子页面支持语言
                // let langList = this.langList

                pipelineList.forEach((item, index) => {
                    if (item.code == currentPipeline) {
                        this.currentSiteUrl = item['lang_list'][lang] ? item['lang_list'][lang]['url_prefix'] : ''
                    }
                })

                /* 数据回填 */
                if(this.linkType === 1){
                    this.linkLabel = '博客url';
                    this.currentSiteUrl = this.currentSiteUrl.replace('promotion','blog');
                }else if(this.linkType === 0){
                    this.linkLabel = '专题url';
                    this.currentSiteUrl = this.currentSiteUrl.replace('blog','promotion');
                }

                let channelLanguage_item = pipelineList.filter((item) => {
                    return item['code'] == currentPipeline
                })

                // 当前语言默认为第一个语言
                this.currentLanguage = Object.keys(channelLanguage_item[0].lang_list)[0]

                // 渠道信息
                this.channelList = pipelineList

                // 主活动端口
                this.pagePlaces = placeList

                let data = {}
                pipelineList.forEach(function (element) {
                    data[element.code] = {}
                    // 渠道下公共数据
                    data[element.code].channel_common_info = {
                        url_name: '',
                        pc_end_url: '',
                        m_end_url: ''
                    }
                    let langList = element.lang_list
                    Object.keys(langList).forEach((k) => {
                        data[element.code][k] = {
                            title: '',
                            url_name: '',
                            seo_title: '',
                            keywords: '',
                            description: '',
                            statistics_code: '',
                            pc_end_url: '',
                            m_end_url: '',
                            redirect_url: '',
                            share_place: [],
                            share_image: '',
                            share_title: '',
                            share_desc: '',
                            share_link: '',
                            // 渠道
                            channelLanguageArr: {},
                            channelLanguageCode: '',
                            // 渠道下公共数据
                            channel_common_info : {
                                url_name: '',
                                pc_end_url: '',
                                m_end_url: ''
                            }
                        }
                    })
                })

                let group_languages = this.currentPageRow.group_languages
                let key = this.activityTabName
                let activityTabName = this.activityTabName

                // key pc,wap
                // k ZF, ZFDE, ...
                // for (let key in group_languages) {
                //
                // }
                for (let k in group_languages[key]) {
                    // 生成渠道下公共数据form
                    let $pipeForm = group_languages[key][k];
                    let $firstLang = Object.keys($pipeForm['language'])[0];
                    let $firstLangData = $pipeForm['language'][$firstLang];
                    let $channelForm = data[k];
                    $channelForm.channel_common_info.url_name = $firstLangData['url_name']
                    if(key === 'pc'){
                        $channelForm.channel_common_info.pc_end_url = $firstLangData['end_url']
                    }else if(key === 'wap'){
                        $channelForm.channel_common_info.m_end_url = $firstLangData['end_url']
                    }
                    // 遍历回填各语言信息
                    for (let lang in group_languages[key][k]['language']) {
                        let $language = group_languages[key][k]['language'][lang],
                            $langForm = data[k][lang]
                        if (typeof $language != 'undefined') {
                            $langForm.title = $language && $language['title']
                            $langForm.url_name = $language && $language['url_name']
                            $langForm.seo_title = $language && $language['seo_title']
                            $langForm.keywords = $language && $language['keywords']
                            $langForm.description = $language && $language['description']
                            $langForm.statistics_code = $language && $language['statistics_code']
                            $langForm.redirect_url = $language && $language['redirect_url']
                            if (key == 'pc') {
                                $langForm.pc_end_url = $language && $language.end_url
                            } else if (key == 'wap') {
                                $langForm.m_end_url = $language && $language.end_url
                            }
                            let share_place = $language && $language.share_place
                            share_place = share_place && share_place.toString().replace(/\"/g, '')
                            $langForm.share_place = share_place.split(',')
                            $langForm.share_image = $language && $language.share_image
                            $langForm.share_title = $language && $language.share_title
                            $langForm.share_desc = $language && $language.share_desc
                            $langForm.share_link = $language && $language.share_link
                        }
                    }

                }

                this.pageForm.id = this.currentPageRow.id
                this.pageForm.need_redirect = false
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
                if (pagePlacesArr.indexOf('app') != -1) {
                    this.pageForm.app_status = true
                } else {
                    this.pageForm.app_status = false
                }

                this.publicPageForm.is_native_show = false;
                if (pagePlacesArr.indexOf('wap') != -1 && pagePlacesArr.indexOf('app') != -1) {
                    // 是否显示原生专题
                    this.publicPageForm.is_native_show = true
                }

                if(this.pageForm.data[currentPipeline][lang]){
                    this.titleCount = this.pageForm.data[currentPipeline][lang].title && this.pageForm.data[currentPipeline][lang].title.split('').length

                    this.pageForm.keywords = this.pageForm.data[currentPipeline][lang].keywords
                    this.pageForm.redirect_url = this.pageForm.data[currentPipeline][lang].redirect_url

                    this.publicPageForm.title = this.pageForm.data[currentPipeline][lang].title

                    this.pageForm.url_name = this.pageForm.data[currentPipeline][lang].url_name
                    this.urlCount = this.pageForm.data[currentPipeline][lang].url_name && this.pageForm.data[currentPipeline][lang].url_name.split('').length

                    this.pageForm.description = this.pageForm.data[currentPipeline][lang].description
                    this.pageIntroductionCount = (this.pageForm.data[currentPipeline][lang].description && this.pageForm.data[currentPipeline][lang].description.split('').length) || 0

                    this.pageForm.statistics_code = this.pageForm.data[currentPipeline][lang].statistics_code
                    this.codeCount = (this.pageForm.data[currentPipeline][lang].statistics_code && this.pageForm.data[currentPipeline][lang].statistics_code.split('').length) || 0

                    this.pageForm.activity_id = this.currentPageRow.activity_id
                    this.pageForm.refresh_time = this.currentPageRow.refresh_time
                    this.publicPageForm.end_time = this.currentPageRow.end_time == '0' ? '' : this.currentPageRow.end_time * 1000 // end_time设为空日期选择器从当前时间选择，设为0不会从当前时间选择！
                    this.publicPageForm.is_redirect_country = this.currentPageRow.is_redirect_country

                    this.pageForm.pc_end_url = this.pageForm.data[currentPipeline][lang].pc_end_url
                    this.pageForm.m_end_url = this.pageForm.data[currentPipeline][lang].m_end_url

                    this.pageForm.seo_title = this.pageForm.data[currentPipeline][lang].seo_title

                    this.pageForm.share_place = this.pageForm.data[currentPipeline][lang].share_place
                    this.share_entrance = this.pageForm.data[currentPipeline][lang].share_place
                    this.pageForm.share_image = this.pageForm.data[currentPipeline][lang].share_image
                    this.pageForm.share_title = this.pageForm.data[currentPipeline][lang].share_title
                    this.pageForm.share_desc = this.pageForm.data[currentPipeline][lang].share_desc
                    this.pageForm.share_link = this.pageForm.data[currentPipeline][lang].share_link

                    // 是否应用原生专题模式
                    this.publicPageForm.is_native_theme = row.is_native;
                }


                this.urlName = this.currentPageRow.url_name
                this.refreshTime = this.currentPageRow.refresh_time
                this.tplId = this.currentPageRow.tplId
                this.end_time = this.currentPageRow.end_time == '0' ? '' : this.currentPageRow.end_time * 1000

                this.pageForm.dialogTitle = '编辑子页面'
                this.radioDisabled = true
                this.nativeDisabled = true

                let platformKeys = Object.keys(row.group_info.platform_list)

                if (platformKeys.indexOf('pc') != -1 && platformKeys.indexOf('wap') == -1) {
                    this.pageForm.need_redirect = true
                }

                this.dialogPageVisible = true
            }
        },

        /**
         * 获取子页面列表
         */
        async getPages(activityId) {
            let params = {
                    activity_id: activityId
                },
                res = await ZF_getPageList(params)

            let position, data, pages, pipelineList, isEnglishSetted = false

            pages = res.data.page_list
            pipelineList = res.data.pipeline_list

            this.activityList.forEach(function (element, index) {
                if (element.id == activityId) {
                    element.children = pages
                    element.pipelineList = pipelineList
                    data = element
                    position = index
                }
            })

            this.$set(this.activityList, position, data)
            this.activityPagePermission = {
                has_design_permission:res.data.has_design_permission,
                special_permissions: res.data.special_permissions,
                all_special_permissions: res.data.all_special_permissions
            }
        },

        viewDetail(row) {
            this.currentPageRow = {}
            this.currentActivityRow = row
            this.isDetailActive = true
            this.langList = row.langList
        },

        handleExpandChange(row) {
            this.getPages(row.id)
            // if (!row.children) {
            // 	this.getPages(row.id)
            // }

            this.langList = row.langList

            if (this.expandRowKeys.indexOf(row.id) === -1) {
                this.expandRowKeys = [row.id]
            } else {
                this.expandRowKeys = []
            }
        },

        handleCurrentChange(currentPage) {
            this.currentPage = currentPage
            this.getActivities()
        },

        handleSizeChange(pageSize) {
            this.pageSize = pageSize;
            this.getActivities()
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

        _uniqueArr(data=[]){
            return Array.from(new Set(data));
        },

        /**
         * 表单提交
         */
        async submitForm (formName) {
            // 新增活动表单验证
            if (formName == 'activityForm') {
                this.setActivityTime()

                if (this.activityForm.start_time == '' || this.activityForm.end_time == '') {
                    this.activityForm.range_time = ''
                }
                let placeChannelValue = this.activityForm.channelValue
                /*// 判断是否勾选渠道
                let placeChannelValue = this.activityForm.channelValue
                if ((placeChannelValue.pc && placeChannelValue.pc.length == 0) && (placeChannelValue.wap && placeChannelValue.wap.length == 0) && (placeChannelValue.app && placeChannelValue.app.length == 0)) {
                    this.$message.error('请勾选渠道！')
                    return false
                }*/

                // 编辑活动（由于编辑状态已有渠道不可操作与全选操作交互冲突，在提交时做校验，若全选取消全部渠道则不提交）
                if (this.activityForm.id && placeChannelValue) {
                    if ((placeChannelValue.pc && placeChannelValue.pc.length == 0) || (placeChannelValue.wap && placeChannelValue.wap.length == 0) || (placeChannelValue.app && placeChannelValue.app.length == 0)) {
                        this.$message.error('不可取消端口下已存在的渠道！')
                        return false
                    }
                }
            }

            this.submitLoading = true

            this.$refs[formName].validate(async (valid) => {
                if (valid) {
                    window.GESHOP_FULL_LOADING = this.$loading({
                        lock: true,
                        text: 'Loading',
                        spinner: 'el-icon-loading',
                        background: 'rgba(0, 0, 0, 0.8)'
                    })

                    let params, res
                    let activityFormObj = {}
                    let platformData = {}
                    let pipeline_list = {}

                    // 新增活动
                    if (formName == 'activityForm') {
                        params = {
                            type: this.activityForm.type,
                            name: this.activityForm.name,
                            description: this.activityForm.description,
                            start_time: this.formatTimestamp(this.activityForm.start_time),
                            end_time: this.formatTimestamp(this.activityForm.end_time)
                        }

                        // 渠道、语言数据处理
                        let place_channel_obj_list = this.activityForm.place_channel_list
                        // 支持的端下渠道语言列表
                        let support_channel_obj_list = this.supportPipelines;

                        // 已选中的端渠道语言列表
                        let all_pipe_selected = this.activityForm.all_pipe_selected;
                        // 编辑前的端渠道语言列表
                        let all_pipe_original = this.activityForm.all_pipe_original;


                        // this.activityForm.channelValue - { pc: ['ZF', 'ZFES'], wap: ['ZFDE'], app: ['ZFAU'] }
                        let channelValue = this.activityForm.channelValue


                        // key - pc, wap, app
                        Object.keys(support_channel_obj_list).forEach((key) => {
                            /*if (typeof channelValue[key] === 'undefined' || channelValue[key].length == 0) {
                                return false
                            }

                            let place_channel_object = {}
                            // item - { code: 'ZF', lang_list: {...}, name: '全球站' }
                            place_channel_obj_list[key].forEach((item) => {
                                place_channel_object[item['code']] = Object.keys(item['lang_list'])
                            })*/

                            let place_channel_object = {}
                            // item - { code: 'ZF', lang_list: {...}, name: '全球站' }
                            Object.keys(support_channel_obj_list[key]).forEach((item) => {
                                let element = all_pipe_selected[key]['pipeList'][item]['value'];
                                if(this.activityForm.id != ''){
                                    let element1 = all_pipe_original[key]['pipeList'][item]['value'];
                                    if(element1.length > 0 || element.length > 0){
                                        place_channel_object[item] = this._uniqueArr([...element,...element1])
                                    }
                                }else{
                                    if(element.length > 0){
                                        place_channel_object[item] = element
                                    }
                                }

                            })
                            if(Object.keys(place_channel_object).length === 0){
                                return false;
                            }

                            // 端数据
                            activityFormObj[key] = {
                                type: this.activityForm.type,
                                name: this.activityForm.name,
                                description: this.activityForm.description,
                                start_time: this.formatTimestamp(this.activityForm.start_time),
                                end_time: this.formatTimestamp(this.activityForm.end_time),
                                site_code: `${getCookie('site_group_code')}-${key}`,
                                pipeline_list: place_channel_object
                            }

                            // 默认同步信息
                            if (this.activityForm.syncChannelStatus[key] && this.activityForm.id != '') {
                                activityFormObj[key]['sync_pipeline'] = this.activityForm.syncChannelValue[key]
                            } else if(!this.activityForm.syncChannelStatus[key] && this.activityForm.id != '') {
                                activityFormObj[key]['sync_pipeline'] = ''
                            }

                            // 默认同步信息 2019/07/04
                            let allPipeEditInfo = this.activityForm.all_pipe_edit_info;
                            if(this.activityForm.id !== '' && allPipeEditInfo[key].sync_show){
                                activityFormObj[key]['sync_pipeline'] = allPipeEditInfo[key] ? allPipeEditInfo[key]['pipe_selected']['pipe'] : '';
                                activityFormObj[key]['sync_lang'] = allPipeEditInfo[key] ? allPipeEditInfo[key]['pipe_selected']['lang'] : '';
                            }
                        })

                        if(Object.keys(activityFormObj).length === 0 ){
                            this.$message.error('请先选择活动包含的渠道和语言信息')
                            window.GESHOP_FULL_LOADING.close();
                            this.submitLoading = false
                            return false;
                        }

                        platformData['platform_list'] = JSON.stringify(activityFormObj)

                        // 保存分“新增”和“编辑”两种逻辑
                        if (this.activityForm.id == '') {
                            // 首次提交code返回101时
                            if (this.activityForm.miss_count_status > 1) {
                                platformData['miss_count'] = ++this.activityForm.miss_count
                                res = await ZF_addActivity(platformData)
                            } else {
                                res = await ZF_addActivity(platformData)
                            }
                        } else {
                            platformData.id = this.activityForm.id
                            res = await ZF_updateActivity(platformData)
                        }

                        if (res.code == 0) {
                            // 关闭弹层
                            this.dialogActivityVisible = false
                            this.submitLoading = false
                            window.GESHOP_FULL_LOADING.close();
                            this.getActivities()
                        }
                        // code - 101 端口没有相关语言仍要提交状态
                        else if (res.code == 101) {
                            this.activityForm.miss_count_status = 2
                            this.activityForm.miss_count = res.data.miss_count
                        }

                        if (params.id) {
                            this.expandRowKeys = []
                        }
                    }
                    // 新增子页面
                    else if (formName == 'pageForm') {
                        this.$refs['publicPageForm'].validate(async (valid) => {
                            if(!valid){
                                this.fnCallback();
                                this.submitLoading = false;
                                window.GESHOP_FULL_LOADING.close();
                                return false;
                            };
                            this.$refs['pagePipeRules'].validate(async (valid) => {
                                if(!valid){
                                    this.fnCallback();
                                    this.submitLoading = false;
                                    window.GESHOP_FULL_LOADING.close();
                                    return false;
                                };

                                // 活动名称和下线时间必填
                                if (this.publicPageForm.title == '' || this.publicPageForm.end_time == '') {
                                    this.$message.error('请填写活动名称和页面下线时间！')
                                    this.submitLoading = false
                                    window.GESHOP_FULL_LOADING.close();
                                    return false
                                }

                                let first_channel = this.channelList[0].code;
                                let first_lang = Object.keys(this.channelList[0].lang_list)[0];
                                let first_data = this.pageForm.data[first_channel][first_lang];

                                if( first_data.seo_title === ''){
                                    this.$message.error('请填写活动SEO标题');
                                    this.submitLoading = false;
                                    window.GESHOP_FULL_LOADING.close();
                                    return false;
                                }

                                let activityFormObj = {}
                                let jsonObject = {}
                                // 遍历当前渠道列表
                                this.channelList.forEach((item) => {
                                    let key = item['code'] // { code: 'ZF', lang_list: {...}, name: '全球站' }
                                    let languages = {}
                                    let langList = item['lang_list']
                                    let default_lang = Object.keys(langList)[0];
                                    let formData = {}
                                    let channel_common_info = this.pageForm.data[key].channel_common_info

                                    Object.keys(langList).forEach((lang) => {
                                        let param = {}

                                        formData = this.pageForm.data[key][lang]
                                        // 修改 this.pageForm.data 中 渠道语言下的值
                                        formData.pc_end_url = channel_common_info.pc_end_url;
                                        formData.m_end_url = channel_common_info.m_end_url;

                                        // 遍历当前端口 - 需要过滤掉“页面模板”和“活动结束跳转链接”之外的所有字段
                                        this.pagePlaces.forEach((it) => {
                                            let k = it['code'] // { code: 'pc', name: 'PC' }
                                            param[k] = JSON.parse(JSON.stringify(formData))

                                            // 删除无用字段
                                            delete param[k].url_name
                                            delete param[k].seo_title
                                            delete param[k].keywords
                                            delete param[k].statistics_code
                                            delete param[k].description
                                            delete param[k].redirect_url
                                            delete param[k].activity_id
                                            delete param[k].share_place
                                            delete param[k].share_image
                                            delete param[k].share_title
                                            delete param[k].share_desc
                                            delete param[k].share_link

                                            // 根据当前端口是pc，m或app保留对应字段值
                                            if (k == 'pc') {
                                                param[k].end_url = param[k].pc_end_url // 将pc端的end_url重命名
                                            } else if (k == 'wap') {
                                                if (this.publicPageForm.is_native_theme == 1) {
                                                    param[k].tpl_id = param[k].native_tpl_id
                                                    param[k].tpl_name = param[k].native_tpl_name
                                                } else {
                                                    param[k].end_url = param[k].m_end_url // 将m端的end_url重命名
                                                    param[k].tpl_id = param[k].m_tpl_id
                                                    param[k].tpl_name = param[k].m_tpl_name
                                                }
                                            } else if (k == 'app') {
                                                if (this.publicPageForm.is_native_theme == 1) {
                                                    param[k].tpl_id = param[k].native_tpl_id
                                                    param[k].tpl_name = param[k].native_tpl_name
                                                } else {
                                                    param[k].tpl_id = param[k].app_tpl_id
                                                    param[k].tpl_name = param[k].app_tpl_name
                                                }
                                            }

                                            // 重命名后删除字段
                                            delete param[k].m_end_url
                                            delete param[k].pc_end_url
                                            delete param[k].m_tpl_id
                                            delete param[k].m_tpl_name
                                            delete param[k].app_tpl_id
                                            delete param[k].app_tpl_name
                                            delete param[k].native_tpl_id
                                            delete param[k].native_tpl_name
                                            //删除obs选中字段
                                            delete param[k].obsPage
                                            // 删除渠道字段
                                            delete param[k].channelLanguageArr
                                            delete param[k].channelLanguageCode
                                            delete param[k].channel_common_info
                                        })

                                        let $langSelect = this.pageForm.data[key][lang];
                                        let $first_channel = this.channelList[0].code;
                                        let $first_lang = Object.keys(this.channelList[0].lang_list)[0];
                                        let $first_data = this.pageForm.data[$first_channel][$first_lang];
                                        // 语言对象赋值
                                        languages[lang] = {
                                            url_name: channel_common_info.url_name ? channel_common_info.url_name : this.pageForm.data[$first_channel].channel_common_info.url_name,
                                            seo_title: $langSelect.seo_title ? $langSelect.seo_title : $first_data.seo_title,
                                            keywords: $langSelect.keywords,
                                            statistics_code: $langSelect.statistics_code,
                                            description: $langSelect.description,
                                            redirect_url: $langSelect.redirect_url,
                                            share_place: this.share_entrance,
                                            share_image: $langSelect.share_image,
                                            share_title: $langSelect.share_title,
                                            share_desc: $langSelect.share_desc,
                                            share_link: $langSelect.share_link,
                                            platform: param
                                        }
                                    })

                                    activityFormObj[key] = {
                                        default_lang: default_lang,
                                        languages: languages
                                    }

                                })

                                jsonObject['end_time'] = this.publicPageForm.end_time / 1000
                                jsonObject['title'] = this.publicPageForm.title
                                jsonObject['is_redirect_country'] = this.publicPageForm.is_redirect_country

                                let postData = {}

                                for (let key in activityFormObj) {
                                    postData[key] = activityFormObj[key]
                                }
                                jsonObject['lang_list'] = JSON.stringify(postData)

                                // 应用端口
                                let platformsArr = []
                                this.pagePlaces.map((item) => {
                                    return platformsArr.push(item.code)
                                })

                                jsonObject['platforms'] = platformsArr.join(',')

                                // 是否应用原生专题模式
                                jsonObject['is_native_theme'] = this.publicPageForm.is_native_theme
                                // 编辑
                                if (this.pageForm.id != '') {
                                    // 编辑子页面传page_id
                                    jsonObject['page_id'] = this.pageForm.id
                                    res = await ZF_batchEditPage(jsonObject)
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
                                        window.GESHOP_FULL_LOADING.close();
                                        return false
                                    }
                                    // 新增子页面传activity_id
                                    jsonObject['activity_id'] = this.pageForm.activity_id
                                    jsonObject['is_blog'] = this.linkType

                                    res = await ZF_batchAddPage(jsonObject)
                                }

                                if (res.code == 0) {
                                    this.getPages(this.pageForm.activity_id)
                                }
                                this.fnCallback(res,formName)

                            })

                        })

                    }
                    // M转APP
                    else if (formName == 'convertForm') {
                        params = {
                            model: this.convertForm.model
                        }

                        // source
                        let source = {}
                        let target = {}

                        // 2019/07
                        let $form = this.convertForm;
                        let source_id = $form.source_id;
                        let target_id = $form.target_id;
                        let source_channelLang = $form.source_channelLang,
                            target_channelLang = $form.target_channelLang,
                            source_channel_keys = Object.keys(source_channelLang),
                            target_Channel_keys = Object.keys(target_channelLang),
                            source_channelLangObj = {},
                            target_channelLangObj = {},
                            source_langCurrent = $form.source_langCurrent
                        // 有关联关系
                        if (this.convertForm.is_group == 1) {

                            // 被转化的渠道信息
                            let sourceChannelList = this.convertForm.wap_supportChannel
                            // 被转化渠道当前选中code值
                            let source_channelCurrent = this.convertForm.source_channelCurrent

                            // 同步到的渠道信息
                            let targetChannelList = this.convertForm.app_supportChannel
                            // 同步到的渠道当前选中数组
                            let targetChannelCurrentList = this.convertForm.app_target_channelCurrent

                            // let group_pipelineList = this.convertForm_M.group_platform_list
                            let source_group_pipelineList = this.convertForm.source_group_platform_list

                            let targetPipelineObj = {}
                            let sourcePipelineArr = []

                            // 通过被选中渠道code值筛选
                            // key - ZF, ZFDE
                            Object.keys(sourceChannelList).forEach(key => {
                                if (key === source_channelCurrent) {
                                    targetPipelineObj = sourceChannelList[key]
                                }
                            })

                            // 通过被选中同步渠道值筛选数据
                            // key - ZF, ZFDE
                            // item - ZF, ZFDE
                            Object.keys(targetChannelList).forEach(key => {
                                targetChannelCurrentList.forEach(item => {
                                    if (key === item) {
                                        sourcePipelineArr.push(targetChannelList[item])
                                    }
                                })

                            })

                            // let lang = Object.keys(targetPipelineObj.lang_list)[0]
                            source[source_channelCurrent] = {
                                id: source_group_pipelineList[source_channelCurrent]['page_id'],
                                lang: source_langCurrent
                            }

                            /*sourcePipelineArr.forEach((item) => {
                                target[item.key] = {
                                    id: item.page_id,
                                    lang_list: Object.keys(item.language)
                                }
                            })*/
                            target_Channel_keys.forEach((item, index) => {
                                if (target_channelLang[item].value.length > 0) {
                                    target_channelLangObj[item] = target_channelLang[item].value;
                                    target[item] = {
                                        id: target_id[item].page_id,
                                        lang_list: target_channelLang[item].value
                                    };
                                }
                            });

                            params.source = JSON.stringify(source)
                            params.target = JSON.stringify(target)
                        }
                        // 无关联关系
                        else {
                            // 被同步渠道信息
                            let wap_group_languages = this.convertForm.wap_group_languages
                            // 被同步渠道key值
                            let source_channelCurrent = this.convertForm.source_channelCurrent

                            // 同步到的渠道总信息
                            let app_supportChannel = this.convertForm.app_supportChannel
                            // 选中的同步到的渠道key值
                            let app_target_channelCurrent = this.convertForm.app_target_channelCurrent

                            let current_wap_group_languages = wap_group_languages[this.activityTabName][source_channelCurrent]['language']
                            // let s_lang = Object.keys(current_wap_group_languages)[0]
                            let s_id = current_wap_group_languages[source_langCurrent]['page_id']
                            source[source_channelCurrent] = {
                                id: s_id,
                                lang: source_langCurrent
                            }

                            // 筛选同步渠道数据
                            /*let targetPipelineArr = []
                            app_supportChannel.forEach((item) => {
                                app_target_channelCurrent.forEach((it) => {
                                    if (it == item.code) {
                                        targetPipelineArr.push(item)
                                    }
                                })
                            })

                            // target值
                            targetPipelineArr.forEach((item) => {
                                // let lang_list = []
                                // lang_list.push(item.lang_list[0]['code'])
                                target[item.code] = {
                                    id: item['page_id'],
                                    lang_list: [item.lang_list[0]['code']]
                                }
                            })*/
                            // 2019/07 遍历target_channellang 语言列表
                            target_Channel_keys.forEach((item, index) => {
                                if (target_channelLang[item].value.length > 0) {
                                    target_channelLangObj[item] = target_channelLang[item].value;
                                    target[item] = {
                                        id: target_id[item].page_id,
                                        lang_list: target_channelLang[item].value
                                    };
                                }
                            });

                            params.source = JSON.stringify(source)
                            params.target = JSON.stringify(target)

                        }
                        // 转化语言必选提示
                        if (!$form.source_langCurrent || Object.keys(target_channelLangObj).length <= 0) {
                            this.$message({
                                message: '请选择转化到的页面内容',
                                type: 'warning'
                            });
                            this.submitLoading = false;
                            window.GESHOP_FULL_LOADING.close()
                            return false;
                        }

                        res = await ZF_convertToAppPage(params)

                        if (res.code == 0) {
                            this.convertUrl = res.data.redirectUrl
                            this.dialogConvertVisible = true
                        } else {
                            this.convertUrl = ''
                        }
                        this.fnCallback(res,'convertForm')
                        // this.successCallback(res,'convertForm')
                    }
                    // PC转M
                    else {

                        params = {
                            model: this.convertForm_M.model
                        }

                        let source = {}
                        let target = {}

                        // 2019/07 TODO: 删除多余字段
                        let $form = this.convertForm_M;
                        let source_id = $form.source_id;
                        let target_id = $form.target_id;
                        let source_channelLang = $form.source_channelLang,
                            target_channelLang = $form.target_channelLang,
                            source_channel_keys = Object.keys(source_channelLang),
                            wap_Channel_keys = Object.keys(target_channelLang),
                            source_channelLangObj = {},
                            target_channelLangObj = {},
                            source_langCurrent = $form.source_langCurrent;

                        // 有关联关系
                        if (this.convertForm_M.is_group == 1) {

                            // 被转化的渠道信息
                            let sourceChannelList = this.convertForm_M.pc_supportChannel
                            // 被转化渠道当前选中code值
                            let source_channelCurrent = this.convertForm_M.source_channelCurrent

                            // 同步到的渠道信息
                            let targetChannelList = this.convertForm_M.wap_supportChannel
                            // 同步到的渠道当前选中数组
                            let targetChannelCurrentList = this.convertForm_M.wap_target_channelCurrent

                            // let group_pipelineList = this.convertForm_M.group_platform_list
                            let source_group_pipelineList = this.convertForm_M.source_group_platform_list

                            let targetPipelineObj = {}
                            let sourcePipelineArr = []

                            // 通过被选中渠道code值筛选
                            // key - ZF, ZFDE
                            Object.keys(sourceChannelList).forEach(key => {
                                if (key === source_channelCurrent) {
                                    targetPipelineObj = sourceChannelList[key]
                                }
                            })

                            // 通过被选中同步渠道值筛选数据
                            // key - ZF, ZFDE
                            // item - ZF, ZFDE
                            Object.keys(targetChannelList).forEach(key => {
                                targetChannelCurrentList.forEach(item => {
                                    if (key === item) {
                                        sourcePipelineArr.push(targetChannelList[item])
                                    }
                                })

                            })

                            // let lang = Object.keys(targetPipelineObj.lang_list)[0]
                            source[source_channelCurrent] = {
                                id: source_group_pipelineList[source_channelCurrent]['page_id'],
                                lang: source_langCurrent
                            }

                            /*sourcePipelineArr.forEach((item) => {
                                target[item.key] = {
                                    id: item.page_id,
                                    lang_list: Object.keys(item.language)
                                }
                            })*/

                            wap_Channel_keys.forEach((item, index) => {
                                if (target_channelLang[item].value.length > 0) {
                                    target_channelLangObj[item] = target_channelLang[item].value;
                                    target[item] = {
                                        id: target_id[item].page_id,
                                        lang_list: target_channelLang[item].value
                                    };
                                }
                            });

                            params.source = JSON.stringify(source)
                            params.target = JSON.stringify(target)

                        }
                        // 无关联关系
                        else {

                            // 被同步渠道信息
                            let pc_group_languages = this.convertForm_M.pc_group_languages
                            // 被同步渠道key值
                            let source_channelCurrent = this.convertForm_M.source_channelCurrent

                            // 同步到的渠道总信息
                            let wap_supportChannel = this.convertForm_M.wap_supportChannel
                            // 选中的同步到的渠道key值
                            let wap_target_channelCurrent = this.convertForm_M.wap_target_channelCurrent

                            let current_pc_group_languages = pc_group_languages[this.activityTabName][source_channelCurrent]['language']
                            // let s_lang = Object.keys(current_pc_group_languages)[0]
                            let s_id = current_pc_group_languages[source_langCurrent]['page_id']
                            source[source_channelCurrent] = {
                                id: s_id,
                                lang: source_langCurrent
                            }

                            // 筛选同步渠道数据
                            /*let targetPipelineArr = []
                            wap_supportChannel.forEach((item) => {
                                wap_target_channelCurrent.forEach((it) => {
                                    if (it == item.code) {
                                        targetPipelineArr.push(item)
                                    }
                                })
                            })

                            // target值
                            targetPipelineArr.forEach((item) => {
                                target[item.code] = {
                                    id: item['page_id'],
                                    lang_list: [item.lang_list[0]['code']]
                                }
                            })*/

                            wap_Channel_keys.forEach((item, index) => {
                                if (target_channelLang[item].value.length > 0) {
                                    target_channelLangObj[item] = target_channelLang[item].value;
                                    target[item] = {
                                        id: target_id[item].page_id,
                                        lang_list: target_channelLang[item].value
                                    };
                                }
                            });

                            params.source = JSON.stringify(source)
                            params.target = JSON.stringify(target)
                        }

                        // 转化语言必选提示
                        if (!$form.source_langCurrent || Object.keys(target_channelLangObj).length <= 0) {
                            this.$message({
                                message: '请选择转化到的页面内容',
                                type: 'warning'
                            });
                            this.submitLoading = false;
                            window.GESHOP_FULL_LOADING.close()
                            return false;
                        }

                        res = await ZF_convertToMPage(params)

                        if (res.code == 0) {
                            this.convertMUrl = res.data.redirectUrl
                            this.siteCode = res.data.siteCode

                            this.dialogConvertMVisible = true
                        } else {
                            this.convertMUrl = ''
                        }
                        // this.successCallback(res,'convertForm_M')
                        this.fnCallback(res,'convertForm_M')
                    }

                    this.isDetailActive = false

                } else {
                    this.submitLoading = false
                }
            })
        },
        
        fnCallback (res, formName) {
            window.GESHOP_FULL_LOADING.close()
            if (res && res.code == 0) {
                this.resetFields(formName)
                this.closeDialog(formName)

            } else {
                this.submitLoading = false
            }
        },

        successCallback(res,formName){
            if (res && res.code == 0) {
                this.resetFields(formName)
                this.closeDialog(formName)

            } else {
                this.submitLoading = false
            }
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

        /* 链接类型切换 */
        linkTypeChange(radioVal){
            if(radioVal === 1){
                this.linkLabel = '博客url';
                this.currentSiteUrl = this.currentSiteUrl.replace('promotion','blog');
            }else if(radioVal === 0){
                this.linkLabel = '专题url';
                this.currentSiteUrl = this.currentSiteUrl.replace('blog','promotion');
            }
        },

        // 是否应用原生专题模式
        nativeThemeChange (val) {
            if(val === 1){
                this.publicPageForm.is_native_theme = 1
            } else if(val === 0){
                this.publicPageForm.is_native_theme = 0
            }
        },

        async ZF_verifyActivity(id, status, is_lock, create_user) {
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

                    await ZF_verifyActivity(params)
                    vm.isDetailActive = false
                    vm.getActivities()
                    vm.expandRowKeys = []
                })
            }
        },

        /**
         * 删除活动
         */
        async removeActivity(id, is_lock, create_user) {
            if ((is_lock == 1 && create_user != this.siteInfo.userName) && this.siteInfo.isSuper != 1) {
                this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            } else {
                this.confirm('活动删除后，其所有的子页面也将被删除，确认删除？', async (vm) => {
                    let params = {
                            id: id
                        },
                        res = await ZF_deleteActivity(params)

                    vm.isDetailActive = false
                    vm.getActivities()

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

        /**
         * 删除子页面
         */
        removePage(row, is_lock, activity_create_user) {
            if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            } else {
                this.confirm('确认删除该页面?', async (vm) => {
                    let params = {
                            group_id: row.group_id,
                            activity_id: row.activity_id
                        },
                        res = await ZF_deletePage(params)

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

        /**
         * 查看访问链接
         * @param {} group_id
         * @param {} activity_id
         */
        async viewPages (group_id, activity_id) {
            this.$refs.dialogReleaseURL.show(group_id, activity_id);
        },

        // 重新发布
        redistribution() {
            this.dialogReleaseVisible = true
        },

        doSearch() {
            this.currentPage = 1
            this.getActivities()
        },

        redirect(url) {
            window.open(url)
        },

        decorate_redirect(url, is_lock, activity_create_user, list) {
            let has_design_permission = this.activityPagePermission.has_design_permission
            if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            }else if(has_design_permission === 0){
                this.$message('暂无装修该活动权限')
            } else {
                // 存储当前子活动字段，activity_id
                let oData = {
                    activity_id: list.activity_id,
                    group_id: list.group_id,
                    page_id: list.id,
                    site_code: list.site_code
                }
                localStorage.setItem('ZF_activityData', JSON.stringify(oData));
                window.open(url);
            }
        },

        /**
         * 三端合并后，PC转M端，M转APP端不再需要传siteCode值
         */
        convertRedirect() {
            window.location.href = this.convertUrl
        },

        //PC转M端 进入装修页面
        convertMRedirect() {
            window.location.href = this.convertMUrl
        },

        // 分享入口表单操作
        handleSharePlaceChange(value) {
            this.share_entrance = value
            this.pageForm.share_place = value
        },

        /**
         * 图片上传
         */
        handleUploadSuccess(response, file) {
            if (response.code == 0) {
                this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].share_image = response.data.url
                this.pageForm.share_image = response.data.url
                this.pageForm.uploadpercent = file.percentage
                setTimeout(() => {
                    this.pageForm.uploadloading = false
                }, 800)
            } else {
                this.$message(response.data.message)
            }
        },

        handleUploadProgress(event, file, fileList) {
            this.pageForm.uploadloading = true
            let percentage = Math.ceil(Math.random() * 50)
            this.pageForm.uploadpercent = percentage
        },

        handleUploadExceed() {
            this.$message('只允许上传一张图片！')
        },

        handleUploadError() {
            this.$message('文件上传失败！')
        },

        handleBeforeUpload(file) {
            if (['image/jpeg', 'image/png'].indexOf(file.type) == -1) {
                this.$message({
                    type: 'info',
                    message: '请选择合适的图片格式！'
                })

                return false
            }

            if (file.size >= 3 * 1024 * 1024) {
                this.$message({
                    type: 'info',
                    message: '请选择合适的图片大小！'
                })

                return false
            }
        },

        /**
         * 子页面tab切换
         */
        handleTabClick(type='pipe') {
            this.resetFields('pageForm')

            this.prevLanguage = this.currentPipeline

            let channel = this.currentPipeline

            // 渠道、语言
            let channelLanguage_item = this.handleChannelLangAssociate(this.channelObject, this.currentPipeline)
            this.pageForm.channelLanguage = channelLanguage_item[0]['name']
            this.pageForm.channelLanguageArr = this.channelObject[this.currentPipeline]
            this.pageForm.data[channel].channelLanguageArr = this.channelObject[this.currentPipeline]
            this.pageForm.data[channel].channelLanguageCode = channelLanguage_item[0]['code']
            this.pageForm.channelLanguageCode = this.pageForm.data[channel].channelLanguageCode

            // 当前语言修改
            if(type === 'pipe'){
                this.currentLanguage = this.pageForm.data[channel].channelLanguageCode
            }

            // 当前渠道
            // let lang = this.pageForm.channelLanguageCode
            let lang = this.currentLanguage

            this.titleCount = this.pageForm.data[channel][lang].title && this.pageForm.data[channel][lang].title.split('').length

            this.pageForm.keywords = this.pageForm.data[channel][lang].keywords
            this.pageForm.description = this.pageForm.data[channel][lang].description
            this.pageIntroductionCount = this.pageForm.data[channel][lang].description && this.pageForm.data[channel][lang].description.split('').length

            this.pageForm.statistics_code = this.pageForm.data[channel][lang].statistics_code
            this.codeCount = this.pageForm.data[channel][lang].statistics_code && this.pageForm.data[channel][lang].statistics_code.split('').length

            // 默认渠道
            let currentPipeline = this.currentPipeline,
                currentLanguageCode = this.pageForm.channelLanguageCode,
                prevChannel = this.channelList[0].code,
                prevLang = Object.keys(this.channelList[0].lang_list)[0]

            // 判断专题url同步（切换同步） 2019/07/05
            let firstChannel = this.channelList[0].code
            if( channel !== firstChannel){
                // 判断当前渠道下公共专题链接为空
                if( this.pageForm.data[channel].channel_common_info.url_name == '' ){
                    this.pageForm.url_name = this.pageForm.data[firstChannel].channel_common_info.url_name
                    this.pageForm.data[channel].channel_common_info.url_name = this.pageForm.data[firstChannel].channel_common_info.url_name
                } else {
                    this.pageForm.url_name = this.pageForm.data[channel].channel_common_info.url_name
                }
            }else{
                this.pageForm.url_name = this.pageForm.data[channel].channel_common_info.url_name
            }
            this.urlCount = this.pageForm.data[channel].channel_common_info.url_name && this.pageForm.data[channel].channel_common_info.url_name.split('').length


            /*this.urlCount = this.pageForm.data[channel][lang].url_name && this.pageForm.data[channel][lang].url_name.split('').length*/

            this.pageForm.refresh_time = this.refreshTime
            this.publicPageForm.end_time = this.end_time

            this.pageForm.pc_end_url = this.pageForm.data[channel].channel_common_info.pc_end_url
            this.pageForm.m_end_url = this.pageForm.data[channel].channel_common_info.m_end_url

            // 模板字段
            this.pageForm.tpl_id = this.pageForm.data[channel][lang].tpl_id
            this.pageForm.tpl_name = this.pageForm.data[channel][lang].tpl_name
            this.pageForm.m_tpl_id = this.pageForm.data[channel][lang].m_tpl_id
            this.pageForm.m_tpl_name = this.pageForm.data[channel][lang].m_tpl_name
            this.pageForm.app_tpl_id = this.pageForm.data[channel][lang].app_tpl_id
            this.pageForm.app_tpl_name = this.pageForm.data[channel][lang].app_tpl_name
            this.pageForm.native_tpl_id = this.pageForm.data[channel][lang].native_tpl_id
            this.pageForm.native_tpl_name = this.pageForm.data[channel][lang].native_tpl_name

            this.pageForm.seo_title = this.pageForm.data[channel][lang].seo_title
            this.pageForm.redirect_url = this.pageForm.data[channel][lang].redirect_url

            // 分享字段
            this.pageForm.share_place = this.share_entrance
            this.pageForm.share_image = this.pageForm.data[channel][lang].share_image
            this.pageForm.share_title = this.pageForm.data[channel][lang].share_title
            this.pageForm.share_desc = this.pageForm.data[channel][lang].share_desc
            this.pageForm.share_link = this.pageForm.data[channel][lang].share_link

            this.pageForm.id = this.currentPageRow.id ? this.currentPageRow.id : ''

            if (this.currentPageRow.activity_id) {
                this.pageForm.activity_id = this.currentPageRow.activity_id
            }

            /* obs页面语言切换 */
            this.pageForm.obsPage = {
                selected: this.pageForm.data[channel][lang].obsPage && this.pageForm.data[channel][lang].obsPage.selected ? this.pageForm.data[channel][lang].obsPage.selected : {}
            }

        },

        getRowKey(row) {
            return row.id
        },

        /**
         * 过滤筛选上下线渠道数据
         * @type { Number } 1 - 上线  2 - 下线
         * @returns { Array } 上下线渠道数据
         */
        handleFilterOnOffLinePipelines (type, pipelineList) {
            let res = []
            // pipe - 渠道code值 ZF,ZFES
            Object.keys(pipelineList).forEach((pipe) => {
                if (type == 1 && (pipelineList[pipe]['status'] == 1 || pipelineList[pipe]['status'] == 4)) {
                    res.push(pipelineList[pipe])
                } else if (type == 2 && (pipelineList[pipe]['status'] == 2)) {
                    res.push(pipelineList[pipe])
                }
            })
            return res
        },

        /**
         * 页面上下线
         */
        async ZF_verifyPage (id, activityId, status, is_lock, activity_create_user, list) {
            if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            } else {
                // 下线
                if (status == 1 || status == 4) {
                    // 获取下线页面渠道数据
                    let params = {
                            group_id: list.group_id
                        },
                        res = await ZF_getChannelPageInfo(params)

                    let page_list = res.data.page_list

                    // 过滤下线渠道数据
                    let activityPageChannelList = this.handleFilterOnOffLinePipelines(2, page_list)

                    // online offline 弹窗组件数据
                    this.onlineOfflineData.activityPageChannelID = id
                    this.onlineOfflineData.activityPageChannelStatus = status
                    this.onlineOfflineData.title = '请选择要下线的渠道页面'
                    this.onlineOfflineData.dialogReleaseVisible = true
                    this.onlineOfflineData.activityPageChannelValue = [activityPageChannelList[0]['code']]
                    this.onlineOfflineData.activityPageChannelList = activityPageChannelList
                }
                // 上线
                else if (status == 2) {
                    // 获取上线页面渠道数据
                    let params = {
                            group_id: list.group_id
                        },
                        res = await ZF_getChannelPageInfo(params)

                    let page_list = res.data.page_list

                    // 过滤上线渠道数据
                    let activityPageChannelList = this.handleFilterOnOffLinePipelines(1, page_list)

                    // online offline 弹窗组件数据
                    this.onlineOfflineData.activityPageChannelID = id
                    this.onlineOfflineData.activityPageChannelStatus = status
                    this.onlineOfflineData.title = '请选择要上线的渠道页面'
                    this.onlineOfflineData.dialogReleaseVisible = true
                    this.onlineOfflineData.activityPageChannelValue = [activityPageChannelList[0]['code']]
                    this.onlineOfflineData.activityPageChannelList = activityPageChannelList

                }

            }
        },

        ZF_lockingActivity(id, is_lock, create_user, $event) {
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
                        res = await ZF_lockingActivity(params)

                    if (res.code === 0) {
                        this.getActivities()
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
        /**
         * 子页面各渠道数据修改 data value
         * name 属性名
         * param 参数名
         */
        updatePageField (data, name, param) {
            if (!param) {
                this.pageForm.data[this.currentPipeline][this.currentLanguage][name] = data;
            } else {
                this.pageForm.data[this.currentPipeline][this.currentLanguage][name][param] = data;
            }

        },

        updatePageTitle (value) {
            this.publicPageForm.title = value
        },

        updatePageKeywords(value) {
            this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].keywords = value
        },

        updatePageSeoTitle(value) {
            this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].seo_title = value
        },

        updatePageRedirectUrl(value) {
            this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].redirect_url = value
        },

        updateShareImage(value) {
            this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].share_image = value
        },

        updateShareTitle(value) {
            this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].share_title = value
        },

        updateShareDesc(value) {
            this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].share_desc = value
        },

        updateShareLink(value) {
            this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].share_link = value
        },

        updatePageUrlName(value) {
            var reg = /(?!^(\d+|[A-Za-z]+|[-]+)$)^[\w-]{3,64}$/
            var result = value.match(reg)
            if (result != null) {
                // this.pageForm.url_name = value
                // this.urlName = this.pageForm.url_name
                /* this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].url_name = value */
                this.pageForm.data[this.currentPipeline].channel_common_info.url_name = value
            }
        },

        setChildEndTime(value) {
            if (value != null) {
                this.end_time = value
                this.publicPageForm.end_time = value // end_time为公共数据
            }
        },

        updatePCEndUrl(value) {
            /*this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].pc_end_url = value*/
            this.pageForm.data[this.currentPipeline].channel_common_info.pc_end_url = value
        },

        updateMEndUrl(value) {
            /*this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].m_end_url = value*/
            this.pageForm.data[this.currentPipeline].channel_common_info.m_end_url = value
        },

        updatePageDescription(value) {
            this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].description = value
        },

        updatePageStatisticsCode(value) {
            this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].statistics_code = value
        },

        updateRefreshTime(value) {
            this.pageForm.refresh_time = value
            this.refreshTime = this.pageForm.refresh_time
        },

        /* obs page update */
        updateOBSPage(item) {
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
                            }
                        }
                        this.pageForm.obsPage.selected = {
                            id: item.id ? Number(item.id) : '',
                            name: item.name
                        }
                        this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].obsPage.selected = {
                            id: item.id ? Number(item.id) : '',
                            name: item.name
                        }

                        this.pageForm.obsPage.oldSelected = this.pageForm.obsPage.selected

                        this.$refs.ref_obsPage.blur()
                    })
                    .catch(action => {
                        this.pageForm.obsPage.selected = this.pageForm.obsPage.oldSelected
                        this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].obsPage.selected = this.pageForm.obsPage.oldSelected
                        this.$refs.ref_obsPage.blur()
                        this.$message({
                            type: 'info',
                            message: action === 'cancel' ?
                                '已取消操作' :
                                '停留在当前页面'
                        })
                    })

            } else {
                this.pageForm.data[this.currentPipeline][this.pageForm.channelLanguageCode].obsPage = {
                    selected: {
                        id: Number(item.id),
                        name: item.name
                    }
                }
            }

        },

        /**
         * 去重
         * @param { Array } arr - 原始数组
         * @param { String } key - 选择去重的字段
         */
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

        /**
         * 获取-页面渠道信息数据
         */
        async getSupportCountrySites () {
            let param = {
                    activity_type: 1
                },
                res = await ZF_getCountrySiteList(param),
                supportPipelines = res.data.support_pipelines
            // allPlatforms = res.data.all_platforms

            // 取出支持的渠道，以code字段去重
            let supportCountrySites = []
            for (var key in supportPipelines) {
                for (let k in supportPipelines[key]) {
                    supportCountrySites.push(supportPipelines[key][k])
                }
            }
            supportCountrySites = JSON.parse(JSON.stringify(supportCountrySites))
            supportCountrySites = this.unique(supportCountrySites, 'code')
            this.allFallenSiteList = supportCountrySites

            // 各自端口下的渠道
            this.originalAllPlatforms = res.data.all_platforms
            this.supportPipelines = supportPipelines
            this.allPlatforms = res.data.all_platforms
        },

        /**
         * 页面加载完毕执行的函数
         */
        publicReady() {
            // 获取页面信息
            this.getActivities()

            // 获取国家站信息
            this.getSupportCountrySites()

            // 获取权限信息
            this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data

            bus.$on('giveData', data => {
                this.siteInfo = data
            })
            this.sitePlat = this.siteInfo.site.split('-')[1]
            // 设置当前站点信息
            this.places = JSON.parse(localStorage.currentSites).sites
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
            let activityIndex = 0 // 选中的活动序列

            this.convertForm.page = ''
            this.convertForm.page_id = ''
            this.convertForm.app_supportChannel = []
            this.appActivityList.forEach(function (element,key) {
                if (element.id == val) {
                    activityIndex = key;
                    element.page_ist.forEach(function (page, index) {
                        list.push({
                            id: page.group_id,
                            title: page.page_title,
                            pipeline_list: page.pipeline_list
                        })
                        // if (index == 0) {
                        // 	page.langList.forEach(function (lang) {
                        // 		languages.push({
                        // 		key: lang.key,
                        // 		name: lang.name
                        // 		})
                        // 	})
                        // }
                    })
                }
            })

            // this.convertLangs = languages
            this.appPages = list

            // 生成同步渠道语言列表信息 wap_supportChannel
            if(list && list.length > 0 ){
                let first_page_pipelist = list[0].pipeline_list
                let channel_pipeline_list = {}
                /* 获取wap_supportChannel {'ZF':'key':'ZF','name':'全球站','page_id':'111','language':{'en':{},'fr':{}}} */
                function getSupportChannelFromPipeline(first_page_pipelist){
                    first_page_pipelist.forEach(item =>{
                        let lang_list = {}
                        item.lang_list.forEach(aLang =>{
                            lang_list[aLang.code] = {
                                lang: aLang.code,
                                langName: aLang.name
                            }
                        })
                        channel_pipeline_list[item.code] = {
                            key: item.code,
                            name: item.name,
                            page_id: item.page_id,
                            language: lang_list
                        }
                    })
                    return channel_pipeline_list
                }
                // 同步到的渠道信息赋值
                this.convertForm.app_supportChannel = getSupportChannelFromPipeline(first_page_pipelist)
                // 选择同步活动 生成被转化target渠道列表
                this.convertForm.target_channelLang = this.channelLangInit(first_page_pipelist);
                this.convertForm.target_channelCurrent = first_page_pipelist[0] ? first_page_pipelist[0].code : '';
            }
        },

        /**
         * M转APP - 异步获取被转化渠道信息
         * @param group_id
         * @param activity_id
         */
        async checkAppPipelinePages(group_id, activity_id) {
            let params = {
                    group_id: group_id,
                    activity_id: activity_id
                },
                res = await ZF_checkPipelinePages(params)

            if (res.code == 0) {
                if (res.data.length == 0) {
                    this.$message({
                        message: '被转化渠道没有装修页面',
                        type: 'warning'
                    })
                } else {
                    // 被转化的渠道信息
                    this.convertForm.wap_supportChannel = res.data
                    // 2019/07 选中第一个渠道及渠道下第一个语言
                    this.handleChangeConvertAppPipe(Object.keys(res.data)[0])
                }

            } else {
                this.$message.error(res.message)
            }
        },

        /**
         * M端转APP端
         */
        convertAPP(id, is_lock, activity_create_user, group_info, group_languages, pipelineList, group_id, activity_id) {
            if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            } else {

                // 初始化被转化渠道信息
                this.convertForm.wap_supportChannel = []
                this.convertForm.source_channelCurrent = ''

                // 异步获取被转化渠道信息
                this.checkAppPipelinePages(group_id, activity_id)

                // 不同端下的渠道信息
                this.convertForm.wap_group_languages = group_languages

                // m转app看是否存在wap字段，有则说明存在关联关系 wap: {ZF: {page_id: 158}, ZFES: {page_id: 159}, ZFDE: {page_id: 160}}
                // 同步到的渠道信息id值，用做勾选同步到的渠道筛选取出相应id值
                let group_platform_list = group_info.platform_list.app ? group_info.platform_list.app : ''

                // pc转m源信息 pc: {ZF: {page_id: 155}, ZFES: {page_id: 156}, ZFDE: {page_id: 157}}
                let source_group_platform_list = group_info.platform_list.wap ? group_info.platform_list.wap : ''
                // 同步到的渠道信息 app: {ZF: {key: "ZF", name: "全球站", page_id: "158",…}, ZFES: {key: "ZFES", name: "西班牙站", page_id: "159",…},…}
                let app_source_group_platform_list = group_languages.app ? getHasPermissionChannel(group_languages.app,this.activityPagePermission.all_special_permissions.app) : ''

                // 2019/07 初始化同步的空白渠道语言列表
                this.convertForm.target_channelLang = this.channelLangInit(app_source_group_platform_list);
                this.convertForm.target_channelCurrent = Object.keys(app_source_group_platform_list)[0];

                this.convertForm.id = id
                this.convertForm.activity_id = ''
                this.convertForm.page_id = ''
                this.convertForm.model = '1'
                this.convertForm.wap_target_channelCurrent = []

                // 如果有app端关联，则不需要手动选择主活动和子页面
                if (group_platform_list) {
                    if(Object.keys(app_source_group_platform_list).length === 0){
                        this.$message('无同步渠道权限')
                        return false;
                    }

                    // 同步到的渠道信息
                    this.convertForm.app_supportChannel = app_source_group_platform_list

                    // this.convertForm.group_platform_list = group_platform_list

                    this.convertForm.source_group_platform_list = source_group_platform_list

                    // 2019/07 source target 下渠道page_id {ZF: {page_id: 155}, ZFES: {page_id: 156}, ZFDE: {page_id: 157}}
                    this.convertForm.source_id = source_group_platform_list;
                    this.convertForm.target_id = group_platform_list;

                    this.convertForm.is_group = 1

                } else {
                    this.convertForm.is_group = 0
                    // 2019/07 更新渠道下page_id
                    this.convertForm.source_id = source_group_platform_list;
                    this.convertForm.target_id = 0;

                    this.ZF_getAppActivityList()
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

        // 转化为M端活动 选择活动子页面筛选同步渠道信息
        handleChangeConvertMPges(id) {
            /**
            * 生成 target_id
            */
            let mPages = this.mPages
            let target_id = {}
            mPages.forEach( item =>{
                if( id === item.id){
                    item.pipeline_list.forEach( aPipe =>{
                        target_id[aPipe.code] = {
                            page_id: aPipe.page_id
                        }
                    })
                }
            })
            this.convertForm_M.target_id = target_id
        },

        // 转化为APP端活动 选择活动子页面筛选同步渠道信息
        handleChangeConvertAppPges(id) {
            let appPages = this.appPages
            let target_id = {}
            appPages.forEach( item =>{
                if( id === item.id){
                    item.pipeline_list.forEach( aPipe =>{
                        target_id[aPipe.code] = {
                            page_id: aPipe.page_id
                        }
                    })
                }
            })
            this.convertForm.target_id = target_id
        },

        getMPages(val) {
            let list = []
            let languages = []
            let activityIndex = 0 // 选中的活动序列

            this.convertForm_M.page = ''
            this.convertForm_M.page_id = ''
            // _this.convertForm_M.wap_supportChannel = []
            this.mActivityList.forEach(function (element,key) {
                if (element.id == val) {
                    activityIndex = key;
                    element.page_ist.forEach(function (page, index) {
                        list.push({
                            id: page.group_id,
                            title: page.page_title,
                            pipeline_list: page.pipeline_list
                        })

                        // if (index == 0) {
                        // 	page.langList.forEach(function (lang) {
                        // 		languages.push({
                        // 		key: lang.key,
                        // 		name: lang.name
                        // 		})
                        // 	})
                        // }
                    })
                }
            })

            // this.convertLangs = languages
            this.mPages = list

            // 生成同步渠道语言列表信息 wap_supportChannel
            if(list && list.length > 0 ){
                let first_page_pipelist = list[0].pipeline_list
                let channel_pipeline_list = {}
                /* 获取wap_supportChannel {'ZF':'key':'ZF','name':'全球站','page_id':'111','language':{'en':{},'fr':{}}} */
                function getWapSupportChannelFromPipeline(first_page_pipelist){
                    first_page_pipelist.forEach(item =>{
                        let lang_list = {}
                        item.lang_list.forEach(aLang =>{
                            lang_list[aLang.code] = {
                                lang: aLang.code,
                                langName: aLang.name
                            }
                        })
                        channel_pipeline_list[item.code] = {
                            key: item.code,
                            name: item.name,
                            page_id: item.page_id,
                            language: lang_list
                        }
                    })
                    return channel_pipeline_list
                }
                // 同步到的渠道信息赋值
                this.convertForm_M.wap_supportChannel = getWapSupportChannelFromPipeline(first_page_pipelist)
                // 选择同步活动 生成被转化target渠道列表
                this.convertForm_M.target_channelLang = this.channelLangInit(first_page_pipelist);
                this.convertForm_M.target_channelCurrent = first_page_pipelist[0] ? first_page_pipelist[0].code : '';
            }

        },

        /**
         * PC转M异步获取被转化渠道信息
         * @param group_id
         * @param activity_id
         */
        async checkMPipelinePages(group_id, activity_id) {
            let params = {
                    group_id: group_id,
                    activity_id: activity_id
                },
                res = await ZF_checkPipelinePages(params)

            if (res.code == 0) {
                if (res.data.length == 0) {
                    this.$message({
                        message: '被转化渠道没有装修页面',
                        type: 'warning'
                    })
                } else {
                    // 被转化的渠道信息
                    this.convertForm_M.pc_supportChannel = res.data
                    // 2019/07 选中第一个渠道及渠道下第一个语言
                    this.handleChangeConvertPipeChange(Object.keys(res.data)[0])
                    // this.convertForm_M.source_channelCurrent = Object.keys(res.data)[0]
                }

            } else {
                this.$message.error(res.message)
            }
        },

        // PC转M
        convertM(id, is_lock, activity_create_user, group_info, group_languages, pipelineList, group_id, activity_id) {
            if (is_lock == 1 && activity_create_user != this.siteInfo.userName && this.siteInfo.isSuper != 1) {
                this.$message('该活动已被创建者锁定，需创建者解锁后其他用户才能操作')
            } else {

                // 初始化被转化的渠道信息
                this.convertForm_M.pc_supportChannel = []
                this.convertForm_M.source_channelCurrent = ''

                // 异步获取被转化渠道信息
                this.checkMPipelinePages(group_id, activity_id)

                // 不同端下的渠道信息
                this.convertForm_M.pc_group_languages = group_languages

                // pc转m看是否存在wap字段，有则说明存在关联关系 wap: {ZF: {page_id: 158}, ZFES: {page_id: 159}, ZFDE: {page_id: 160}}
                // 同步到的渠道信息id值，用做勾选同步到的渠道筛选取出相应id值
                let group_platform_list = group_info.platform_list.wap ? group_info.platform_list.wap : ''

                // pc转m源信息 pc: {ZF: {page_id: 155}, ZFES: {page_id: 156}, ZFDE: {page_id: 157}}
                let source_group_platform_list = group_info.platform_list.pc ? group_info.platform_list.pc : ''

                // 同步到的渠道信息 wap: {ZF: {key: "ZF", name: "全球站", page_id: "158",…}, ZFES: {key: "ZFES", name: "西班牙站", page_id: "159",…},…}
                let wap_source_group_platform_list = group_languages.wap ? getHasPermissionChannel(group_languages.wap,this.activityPagePermission.all_special_permissions.wap) : ''
                // 2019/07 初始化同步的空白渠道语言列表
                this.convertForm_M.target_channelLang = this.channelLangInit(wap_source_group_platform_list);
                this.convertForm_M.target_channelCurrent = Object.keys(wap_source_group_platform_list)[0];
                this.convertForm_M.id = id
                this.convertForm_M.activity_id = ''
                this.convertForm_M.page_id = ''
                this.convertForm_M.model = '1'

                // 初始化同步到的渠道
                this.convertForm_M.wap_target_channelCurrent = []

                // 如果有wap端关联，则不需要手动选择主活动和子页面
                if (group_platform_list) {
                    if(Object.keys(wap_source_group_platform_list).length === 0){
                        this.$message('无同步渠道权限')
                        return false;
                    }
                    // 同步到的渠道信息赋值
                    this.convertForm_M.wap_supportChannel = wap_source_group_platform_list
                    // 同步到的渠道id数据
                    // this.convertForm_M.group_platform_list = group_platform_list

                    // 被转化渠道 查找相关源渠道page_id
                    this.convertForm_M.source_group_platform_list = source_group_platform_list

                    // 2019/07 source target 下渠道page_id {ZF: {page_id: 155}, ZFES: {page_id: 156}, ZFDE: {page_id: 157}}
                    this.convertForm_M.source_id = source_group_platform_list;
                    this.convertForm_M.target_id = group_platform_list;

                    // 是否具有关联关系标识字段
                    this.convertForm_M.is_group = 1

                } else {
                    // 是否具有关联关系标识字段
                    this.convertForm_M.is_group = 0
                    // 2019/07 更新渠道下page_id
                    this.convertForm_M.source_id = group_info.platform_list.pc ?　group_info.platform_list.pc : '';
                    this.convertForm_M.target_id = 0;

                    this.ZF_getMActivityList()
                }

                this.dialogConvertM = true
            }
        },

        //PC转M 转APP关闭
        closeConvertM () {
            this.convertForm_M.source_langCurrent = '';
        },

        /**
         * 2019/07/09
         * PC转M M转App 渠道checkbox change
         * @param checked
         * @param pipeCode
         * @param type ['add' | 'edit']
         * @return {boolean}
         */
        handleConvertChange(checked,pipeCode,formName){
            if(!pipeCode ||　!formName){
                return false;
            }
            /* arr1 中是否存在arr2所有元素*/
            function _hasAll(arr1, arr2) {
                if(arr1 && arr1.length > 0 && arr2 && arr2.length > 0){
                    return arr2.every(val => arr1.includes(val));
                }else{
                    return false;
                }

            }

            let $form = this[formName];
            let target_supportChannel = formName === 'convertForm_M' ? $form['wap_supportChannel'] : $form['app_supportChannel'];
            let {target_channelLang} = $form;
            let pipe_langs = Object.keys(target_supportChannel[pipeCode].language);
            let clone_target_channelLang = clone_simple(target_channelLang);
            if(_hasAll(checked,pipe_langs)){
                clone_target_channelLang[pipeCode].check_all = true;
                clone_target_channelLang[pipeCode].indeterminate = false;
            }else{
                clone_target_channelLang[pipeCode].check_all = false;
                clone_target_channelLang[pipeCode].indeterminate = checked.length > 0 ? true : false;
            }
            this.$set(this[formName],'target_channelLang',clone_target_channelLang)
        },

        /**
         * 2019/07/09
         * PC转M，M转App 端下渠道全选
         * @param value true|false
         * @param formName  'convertForm_M' | 'convertForm'
         * @param item 渠道对象
         * @return {boolean}
         */
        handleConvertCheckAll(value,formName,item){
            if(!formName){return false;}
            let $form = this[formName];
            let target_supportChannel = formName === 'convertForm_M' ? $form['wap_supportChannel'] : $form['app_supportChannel'];
            let {target_channelLang} = $form;
            let pipe_langs = Object.keys(target_supportChannel[item.key].language);
            if(value){
                target_channelLang[item.key] = {
                    check_all: true,
                    indeterminate: false,
                    value: pipe_langs
                }
            }else{
                target_channelLang[item.key] = {
                    check_all: false,
                    indeterminate: false,
                    value: []
                }
            }
        },

        /**
         * 2019/07
         * 生成空白渠道数据对象
         * @param dataFrom
         */
        channelLangInit(dataFrom){
            let channelLangList = dataFrom;
            let channelLang = {};
            if(Object.prototype.toString.call(dataFrom) === '[object Object]'){
                Object.keys(channelLangList).forEach((element) => {
                    channelLang[element] = {
                        check_all: false,
                        check_indeterminate: false,
                        value: []
                    };
                });
            }else if(Object.prototype.toString.call(dataFrom) === '[object Array]'){
                channelLangList.forEach((element) => {
                    channelLang[element.code] = {
                        check_all: false,
                        check_indeterminate: false,
                        value: []
                    };
                });
            }

            return channelLang;
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
            } else if (this.templateSelectPlace == 'app') {
                this.getTmpListValue = 'app'
            } else if (this.templateSelectPlace == 'native') {
                this.getTmpListValue = 'native'
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

        /**
         * 模板选中数据回填
         */
        handleSureModel() {
            let currentPlace = this.templateSelectPlace

            let selected = this.modelInfo.modelSelect

            if (currentPlace == 'pc') {
                this.pageForm.tpl_id = selected
            } else if (currentPlace == 'wap') {
                this.pageForm.m_tpl_id = selected
            } else if (currentPlace == 'app') {
                this.pageForm.app_tpl_id = selected
            } else if (currentPlace == 'native') {
                this.pageForm.native_tpl_id = selected
            }

            this.modelInfo.visible = false
            // this.tplId = this.pageForm.tplId

            let currentLanguage = this.currentLanguage || this.pageForm.channelLanguageCode;
            this.pageForm.data[this.currentPipeline][currentLanguage].tpl_id = this.pageForm.tpl_id
            this.pageForm.data[this.currentPipeline][currentLanguage].m_tpl_id = this.pageForm.m_tpl_id
            this.pageForm.data[this.currentPipeline][currentLanguage].app_tpl_id = this.pageForm.app_tpl_id
            this.pageForm.data[this.currentPipeline][currentLanguage].native_tpl_id = this.pageForm.native_tpl_id

            let _this = this

            // let template = '无页面模板', m_template = '无页面模板'
            this.pageTemplateList.forEach(function (element) {
                if (element.id == selected) {
                    if (currentPlace == 'pc') {
                        // template = element.name
                        _this.pageForm.tpl_name = element.name
                    } else if (currentPlace == 'wap') {
                        // m_template = element.name
                        _this.pageForm.m_tpl_name = element.name
                    } else if (currentPlace == 'app') {
                        // m_template = element.name
                        _this.pageForm.app_tpl_name = element.name
                    } else if (currentPlace == 'native') {
                        // m_template = element.name
                        _this.pageForm.native_tpl_name = element.name
                    }
                }
            })

            // this.currentTemplate = template
            this.pageForm.data[this.currentPipeline][currentLanguage].tpl_name = this.pageForm.tpl_name
            this.pageForm.data[this.currentPipeline][currentLanguage].m_tpl_name = this.pageForm.m_tpl_name
            this.pageForm.data[this.currentPipeline][currentLanguage].app_tpl_name = this.pageForm.app_tpl_name
            this.pageForm.data[this.currentPipeline][currentLanguage].native_tpl_name = this.pageForm.native_tpl_name

            // this.pageForm.tpl_name = template
            // this.pageForm.m_tpl_name = m_template
        },

        handleCancelSelectedModel() {
            this.modelInfo.visible = false
        },

        subDialogClose() {
            this.resetForm('pageForm')
            this.pageForm.tpl_id = '0'
            this.modelInfo.tabActive = '2'
            this.end_time = ''
            if (this.siteInfo.site == 'gb') {
                this.obsPage.selected = {}
                this.obsPage.data = []
                this.obs.selected = {}
                this.pageForm.obsPage = {
                    selected: {}
                }
            }
        },

        tplTabClick() {
            this.tplInfo.pageNo = 1
            let contContainer = document.getElementById('pane-2').parentNode
            contContainer.removeEventListener('scroll', this.handlePanelScroll)

            if (this.templateSelectPlace == 'pc') {
                this.getTmpListValue = 'pc'
            } else if (this.templateSelectPlace == 'wap') {
                this.getTmpListValue = 'wap'
            } else if (this.templateSelectPlace == 'app') {
                this.getTmpListValue = 'app'
            } else if (this.templateSelectPlace == 'native') {
                this.getTmpListValue = 'native'
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
                            } else if (_this.templateSelectPlace == 'app') {
                                _this.getTmpListValue = 'app'
                            }  else if (_this.templateSelectPlace == 'native') {
                                _this.getTmpListValue = 'native'
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
            this.viewModel.src = '/activity/zf/page-tpl/preview?pid=' + pid + '&lang=' + langDefualt + '&id=' + id + '&site_code=' + site_code + ''

            // let sideType = site_code.split('-')[1], sideWidth
            // sideWidth = '100%'
            let sideType = site_code.split('-')[1],
                sideWidth
            if (sideType != 'pc') {
                sideWidth = '400px'
            } else {
                sideWidth = '100%'
            }

            this.viewModel.sideType = sideType
            this.viewModel.sideWidth = sideWidth
            this.pageLoading = false
        },

        viewModelClose() {
            this.viewModel.visible = false
            this.viewModel.html = ''
            this.viewModel.src = ''
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
        },

        /* OBS */
        async getObsTheme() {
            this.obsLoading = true
            let res = await obsLevel1().catch(() => {
                this.obsLoading = false
            })
            this.obsLoading = false

            if (res.code == 0) {
                this.obs.data = res.data
            }
        },

        async getObsPage(id) {
            this.obsLoading = true
            const params = {
                theme_id: id,
                page_id: this.pageForm.id,
                platform: this.activityTabName
                // lang: this.currentLanguage
            }
            let res = await obsLevel2(params).catch(() => {
                this.obsLoading = false
            })
            this.obsLoading = false
            if (res && res.code == 0) {
                this.obsPage.data = res.data
            }
        },

        obsThemeChange(item) {
            // this.confirm('修改主题后，该活动已经关联的页面和板块内容将发生变化，即此活动关联的页面原来所选内容将会被清空，是否继续更换？？', async (vm) => {
            // 	this.obs.selected = item;
            // })
            let _this = this
            if (this.activityForm.id) {
                this.$confirm('修改主题后，该活动已经关联的页面和板块内容将发生变化，即此活动关联的页面原来所选内容将会被清空，是否继续更换？', '提示', {
                    type: 'warning',
                    distinguishCancelAndClose: true,
                    confirmButtonText: '是',
                    cancelButtonText: '否'
                })
                    .then(() => {
                        this.$refs.ref_obsTheme.blur()
                        this.obs.oldSelected = this.obs.selected
                    })
                    .catch(action => {
                        this.obs.selected = this.obs.oldSelected
                        this.$refs.ref_obsTheme.blur()
                        this.$message({
                            type: 'info',
                            message: action === 'cancel' ?
                                '已取消操作' :
                                '停留在当前页面'
                        })
                    })
            }

        },

        obsThemeBlur(item) {
            if (!this.obs.oldSelected) {
                this.obs.oldSelected = this.obs.selected
            }
            // this.obs.oldSelected = this.obs.selected;
        },

        obsPageBlur(item) {
            // let currentLang = this.currentLanguage
            // let langObsPage = this.pageForm.data[currentLang].obsPage
            // if (!langObsPage.oldSelected) {
            // 	langObsPage.oldSelected = langObsPage.selected;
            // }
        },

        //obs theme 下拉
        obsThemeVisible() {
            if (this.obs.visibleChange) {
                return false
            }
            this.getObsTheme()
            this.obs.visibleChange = true
        },

        closeDialogActivity() {
            this.obs = {
                data: [],
                selected: {}
            }
            let { all_pipe_edit_info,all_pipe_original,all_pipe_selected } = this.$options.data().activityForm;
            let all_pipe_initial = {
                all_pipe_edit_info: all_pipe_edit_info,
                all_pipe_original: all_pipe_original,
                all_pipe_selected: all_pipe_selected,
            }
            this.activityForm = Object.assign({},this.$data.activityForm,all_pipe_initial)
        },

        openSyncLog(page_id){
            this.syncLogData.visible = true;
            this.syncLogData.page_id = page_id;
            this.handleSyncLogLists();
        },

        async handleSyncLogLists () {
            this.syncLogData.loading = true
            let pagination = this.syncLogData.pagination
            let params = {
                "page_id": this.syncLogData.page_id,
                "pageNo": pagination.pageNo,
                "pageSize": pagination.pageSize
            };
            try{
                let res = await ZF_syncOperateLog(params,{successOff:true});
                this.syncLogData.lists = res.data.list
                this.syncLogData.pagination = res.data.pagination
                this.syncLogData.loading = false
            }catch(err){
                this.syncLogData.loading = false
            }

        },

        handleSyncLogChange(currentPage){
            this.syncLogData.pagination.pageNo = currentPage;
            this.handleSyncLogLists();
        },

        handleSyncLogClose(){
            this.syncLogData.pagination = {
                "pageNo":1,
                "pageSize":10,
                "totalCount":0
            }
            this.syncLogData.lists = []
        },

        // 展示 一键刷新头尾部 弹窗
        handle_show_updateHead () {
            const platform = this.activityTabName || 'pc';
            this.$refs.updateHeadFoot.open(platform);
        },

        /**
         * 查看所有id
         * @param {string} id 页面ID
         * @param {string} group_id 分组ID
         * @param {string} site_code 渠道编码
         */
        handle_show_pageid (params) {
            // 获取对应需要的参数
            this.$refs.viewPageIds.show({
                page_id: params.id,
                group_id: params.group_id,
                site_code: params.site_code
            });
        },

        /**
         * 返回行class
         * @param row
         * @param rowIndex
         */
        tableRowClassName({row,rowIndex}){
            return row.is_frequently === 1 ? 'commonly-label' : 'commonly-img'
        },
        /**
         * 设置常用活动，取消常用活动
         * @param row
         */
        async handleActivityLabel(row){
            try{
                const res = await ZF_getFrequently({
                    id: row.id
                })
                const successTip = row.is_frequently === 0 ? '设置成功' : '移除成功';
                if(res.code === 0){
                    this.$message.success(successTip);
                    this.getActivities();
                }
            }catch(err){}

        }
    },

    created () {
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
    .view-page-ids {
        padding-left: 10px;
        color: #9E9E9E;
    }
    .view-page-ids:hover {
        color: #1E9FFF;
        cursor: pointer;
    }

</style><style lang="less">
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
    }

    .gs-obs-select {
        max-width: 200px;
    }

    .input-with-select .el-select .el-input {
        width: 100px;
    }

    .geshop-view-link-page .view-link-button--primary {
        margin-bottom: 10px;
        width: calc(25% - 12px);
        margin-left: 10px;
    }

    .channel-checkbox-wraper {
        position: relative;
        line-height: 1.4;
    }

    .channel-checkbox-wraper .pc-channel-checkbox-item,
    .channel-checkbox-wraper .wap-channel-checkbox-item,
    .channel-checkbox-wraper .app-channel-checkbox-item {
        margin-right: 10px;
    }

    .pc-part-channel-visible .pc-channel-checkbox-item:nth-of-type(7) ~ .pc-channel-checkbox-item,
    .wap-part-channel-visible .wap-channel-checkbox-item:nth-of-type(7) ~ .wap-channel-checkbox-item,
    .app-part-channel-visible .app-channel-checkbox-item:nth-of-type(7) ~ .app-channel-checkbox-item {
        display: none;
    }
    .geshop-sync-dialog-container{
        min-width:1000px;
    }
    .geshop-sync-dialog-container .geshop-sync-log-table .el-table__body-wrapper{
        height:600px;
        overflow-y:scroll;
    }

    .geshop-activity-child-pages {
        .child-page-flag-app{
            position: absolute;
            top: 0;
            left: 0;
        }
    }

    .geshop-new-child-page .gs-col-all .count-tip-box {
        right: 105px;
    }
</style>
