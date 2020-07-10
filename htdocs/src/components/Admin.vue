<template>
    <site-layout>
        <el-row>
            <el-col :span="6">
                <el-input v-model="options.keyword" size="small" class="search-inputSelect">
                    <el-select filterable clearable v-model="options.field" slot="prepend" placeholder="请选择关键字类型">
                        <el-option label="姓名" value="realname"></el-option>
                        <el-option label="用户名" value="username"></el-option>
                    </el-select>
                </el-input>
            </el-col>
            <el-col :span="6" :offset="1">
                <el-cascader filterable change-on-select class="department-tree" :options="departmentOpt" :props="prop" v-model="options.department_id"
                  size="small" placeholder="请选择部门" :disabled="is_super!='1'"></el-cascader>
            </el-col>
        </el-row>
        <el-row>
            <el-col :span="4">
                <el-select filterable v-model="options.is_super" class="search-full" size="small" placeholder="请选择是否管理员">
                    <el-option v-for="(item, index) in isSuperOpt" :key="index" :value="index" :label="item"></el-option>
                </el-select>
            </el-col>
            <el-col :span="4" :offset="1">
                <el-select filterable v-model="options.status" class="search-full" size="small" placeholder="请选择用户状态">
                    <el-option v-for="(item, index) in statusOpt" :key="index" :value="index" :label="item"></el-option>
                </el-select>
            </el-col>
            <!-- <el-col :span="6" :offset="1">
                <el-date-picker v-model="options.editDate" type="datetimerange" :editable="false" value-format="timestamp" class="search-full" size="small" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
            </el-col> -->
            <el-col :span="6" :offset="9" class="text-right">
                <el-button type="primary" size="small" @click="handleSearch()">搜索</el-button>
                <el-button type="danger" size="small" @click="resetSearch()">清空</el-button>
            </el-col>
        </el-row>

        <el-row>
            <el-col :span="24">
                <div class="activity-list-main">
                    <div class="activity-list-box">
                        <el-row>
                            <el-col :span="24">
                                <el-table :data="dataSource" @expand-change="handleExpandChange" v-loading="loading">
                                    <el-table-column prop="username" label="用户名"></el-table-column>
                                    <el-table-column prop="realname" label="姓名"></el-table-column>
                                    <!-- <el-table-column prop="email" label="邮箱"></el-table-column> -->
                                    <el-table-column prop="department_name" label="部门"></el-table-column>
                                    <!-- <el-table-column prop="" label="角色"></el-table-column> -->
                                    <!-- <el-table-column prop="update_user" label="最后修改人"></el-table-column> -->
                                    <el-table-column label="超级管理员">
                                        <template slot-scope="scope">
                                            <span v-if="scope.row.is_super == 1">是</span>
                                            <span v-else-if="scope.row.is_super == 0">不是</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="部门负责人">
                                        <template slot-scope="scope">
                                            <span v-if="scope.row.is_leader == 1">是</span>
                                            <span v-else-if="scope.row.is_leader == 0">否</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="用户状态">
                                        <template slot-scope="scope">
                                            <span v-if="scope.row.status == 1">启用</span>
                                            <span v-else-if="scope.row.status == 0">禁用</span>
                                            <span v-else-if="scope.row.status == 2">关闭</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="首页渠道权限"  class-name="channel_text">
                                        <template slot-scope="scope">
                                            {{ Object.values(scope.row.home_permissions).join(',') }}
                                        </template>
                                    </el-table-column>
																	<el-table-column label="活动页渠道权限"  class-name="channel_text">
																		<template slot-scope="scope">
																			{{ Object.values(scope.row.special_permissions).join(',') }}
																		</template>
																	</el-table-column>
                                    <el-table-column label="操作" min-width="260" v-if="is_super == 1">
                                        <template slot-scope="scope">
                                            <el-button size="small" @click="editItem(scope.row)" v-if="userName !=scope.row.username && '1' != scope.row.is_super">编辑</el-button>
                                            <el-button size="small" @click="editItem(scope.row,0)" v-else>查看</el-button>
																						<el-button size="small" @click="newChannelPermission(scope.row,'home')">新增首页渠道权限</el-button>
																						<el-button size="small" @click="newChannelPermission(scope.row,'special')" >新增活动页渠道权限</el-button>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="操作" min-width="260" v-if="is_super == 0">
                                        <template slot-scope="scope">
                                            <el-button size="small" @click="editItem(scope.row)" v-if="permissions.includes('base/admin/edit') && userName !=scope.row.username && scope.row.is_super == 0 && isLeader =='1' && scope.row.is_leader !='1'">编辑</el-button>
                                            <el-button size="small" @click="editItem(scope.row,0)" v-else>查看</el-button>
																						<el-button size="small" @click="newChannelPermission(scope.row,'home')">新增首页渠道权限</el-button>
																						<el-button size="small" @click="newChannelPermission(scope.row,'special')" >新增活动页渠道权限</el-button>
                                            <!-- <el-button size="small" @click="editItem(scope.row,0)" v-if="permissions.includes('base/admin/info') && userName == scope.row.username">查看</el-button> -->
                                        </template>
                                    </el-table-column>
                                </el-table>
                            </el-col>
                        </el-row>

                        <el-row v-if="total > options.pageSize">
                            <el-col :span="24" class="text-right">
                                <el-pagination layout="prev, pager, next" :page-size="options.pageSize" :total="total" :current-page.sync="options.pageNo"
                                  @current-change="handleCurrentChange"></el-pagination>
                            </el-col>
                        </el-row>
                    </div>

                </div>
            </el-col>
        </el-row>

        <!-- 新增渠道权限 -->
        <el-dialog
            title="权限配置"
            :visible.sync="permissionDialogVisible"
            width="40%">
            <div>
                <h3>站点选择</h3>
                <el-checkbox-group v-model="permissionSiteCheckList">
                    <el-checkbox label="zf">{{ website_name }}</el-checkbox>
                </el-checkbox-group>
                <h3>渠道选择</h3>
                <template>
                    <el-checkbox :indeterminate="isIndeterminate" v-model="permissionCheckAll" @change="handleCheckAllPermissionChannel">全选</el-checkbox>
                    <div style="margin: 15px 0;"></div>
                    <el-checkbox-group v-model="checkedPermissionChannel" @change="handleCheckedPermissionChannelChange">
                        <el-checkbox v-for="item,key in permissionChannelList" :label="key" :key="key" class="generate-check-item">{{ item }}</el-checkbox>
                    </el-checkbox-group>
                </template>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="permissionDialogVisible = false">取 消</el-button>
                <el-button type="primary" @click="handleSubmitPermissChannel">确 定</el-button>
            </span>
        </el-dialog>

        <el-dialog :title="dialogFormInfo.title" :visible.sync="dialogVisible" class="user-edit" width="70%" @close="dialogFormClose">
            <el-form :model="dialogForm" :rules="editRules" ref="dialogForm" label-width="100px" :disabled="dialogFormInfo.disabled">
                <el-form-item label="用户名" prop="username">
                    <span>{{dialogForm.username}}</span>
                </el-form-item>
                <el-form-item label="姓名" prop="realname">
                    <span>{{dialogForm.realname}}</span>
                </el-form-item>
                <el-form-item label="邮箱" prop="email">
                    <span>{{dialogForm.email}}</span>
                </el-form-item>
                <!-- <el-form-item label="超级管理员" prop="is_super" :rules="[{required:true,message:'该项不能为空'}]">
          <el-radio-group v-model="childOption.is_super">
            <el-radio label="1" :disabled="defaultAdmin=='1'">是</el-radio>
            <el-radio label="0" :disabled="defaultAdmin=='1'">否</el-radio>
          </el-radio-group>
        </el-form-item> -->
                <el-form-item label="部门负责人" prop="is_leader">
                    <el-radio-group v-model="dialogForm.is_leader">
                        <el-radio label="1">是</el-radio>
                        <el-radio label="0">否</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="用户状态" prop="status">
                    <el-radio-group v-model="dialogForm.status">
                        <el-radio label="1">启用</el-radio>
                        <el-radio label="0">禁用</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="数据权限" v-if="childOption.is_super=='0'">
                    <div>
                        <p style="margin:0;">请勾选部门可以访问的站点</p>
                        <div class="department-site">
                            <div class="title">
                                <el-checkbox :disabled="editable" v-model="checkSite" :indeterminate="checkSiteMid" @change="checkAll($event,siteOpt)">全选</el-checkbox>
                            </div>
                            <el-checkbox-group v-model="childOption.sites" calss="site" @change="changeSites">
                                <el-checkbox :disabled="editable || item.has_auth == 0" v-for="item in siteOpt" :key="item.site_code" :label="item.site_code">{{item.site_code}}</el-checkbox>
                            </el-checkbox-group>
                        </div>
                        <!-- <p v-if="childOption.sites.length>0">请配置已选站点的分类访问权限</p> -->
                        <el-tabs class="department-tab" v-if="childOption.sites.length>0" :value="current">
                            <el-tab-pane v-for="item in childOption.sites" :key="item" :label="item" :name="item">
                                <!-- <div class="department-site cat">
                    <div class="title">
                      <el-checkbox :disabled="editable" v-model="siteOpt[item].status" :indeterminate="siteOpt[item].status==0?false:true" :true-label="1" :false-label="0" @change="checkAllCat($event, 'tree_' + item, siteOpt[item])">全选</el-checkbox>
                    </div>
                    <el-tree :data="siteOpt[item].categorys" :props="childProps" show-checkbox :default-checked-keys="siteOpt[item].categorysSelected" node-key="id" :ref="'tree_'+item"></el-tree>
                  </div> -->
                                <p>请为站点{{item}}配置角色</p>
                                <div class="department-site">
                                    <div class="title">
                                        <el-checkbox :disabled="editable" v-model="siteOpt[item].checkAll" :indeterminate="siteOpt[item].checkPart" @change="checkAllRole($event, siteOpt[item])">全选</el-checkbox>
                                    </div>
                                    <el-checkbox-group v-model="siteOpt[item].rolesSelected" class="site" @change="changeRoles($event,siteOpt[item])">
                                        <el-checkbox :disabled="editable || role.has_auth == 0" v-for="role in siteOpt[item].roles" :key="role.role_id" :label="role.role_id">{{role.role_name}}</el-checkbox>
                                    </el-checkbox-group>
                                </div>
                            </el-tab-pane>
                        </el-tabs>
                    </div>
                </el-form-item>
                <el-form-item>
                    <el-button @click="resetForm('dialogForm')" size="small">取消</el-button>
                    <el-button type="primary" @click="submitForm('dialogForm')" size="small" :loading="submitLoading">确定</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import { getAdminList, getAdminInfo, adminDel, adminEdit, getDepartmentPublic, getPermissionChannel, updatePermissChannel } from '../plugin/api'
import { getCookie } from '../plugin/mUtils'

export default {
    components: { siteLayout },
    data () {
        return {
            dataSource: [],
            total: 0,
            isActive: false,
            options: {
                keyword: '',
                field: '',
                publicOutline: {},
                department_id: [],
                pageNo: 1,
                pageSize: 10
            },
            statusOpt: {
                '0': '禁用',
                '1': '启用',
                // '2': '关闭'
            },
            isSuperOpt: {
                '1': '是',
                '0': '否'
            },
            //部门级联
            defaultIds: [],
            departmentOpt: [],
            prop: {
                value: 'id',
                label: 'name',
                children: 'children'
            },
            //编辑option
            childOption: {
                is_super: '0',
                status: '',
                sites: [],
            },
            editable: false,
            defaultAdmin: '',
            checkSite: false,
            checkSiteMid: false,
            siteOpt: {},
            current: '',
            childProps: {
                label: (data, node) => {
                    return node.data.categoryMultiLang.categoryName
                },
                children: 'children',
                disabled: (data, node) => {
                    return (!node.data.selected && node.data.parentNode) || self.editable
                }
            },

            // 权限
            permissionDialogVisible: false,
            permissionSiteCheckList: ['zf'],
            permissionCheckAll: false,
            checkedPermissionChannel: ['ZF'],
            permissionChannelList: [],
            isIndeterminate: true,
            currentUserId: 0,
            website_code: 'zf',
            website_name: 'ZAFUL',
					  permissionPlace:'home',

            //dialog
            dialogForm: {
                department: {},
                site: {},
                sites: [],
                is_super: '0'
            },
            dialogFormInfo: {
                title: '',
                submitType: 'add',
                parent_id: '0',
                node: 0,
                disabled: true
            },
            dialogVisible: false,
            editRules: {
                username: [{ required: true, message: '请输入用户名' }],
                realname: [{ required: true, message: '请选择状态', trigger: 'change' }]
            },
            loading: false,
            submitLoading: false,
            permissions: []
        }
    },
    filters: {

    },
    computed: {
        is_super () {
            return localStorage.getItem('isSuper')
        },
        isLeader () {
            return localStorage.getItem('isLeader')
        },
        userName () {
            return localStorage.getItem('userName')
        }
    },
    created () {
        this.permissions = JSON.parse(localStorage.getItem('actionPermissions')).data
        this.website_code = getCookie('site_group_code')
        this.handlePublicLine()
    },
    methods: {
        handleSearch () {
            this.options.pageNo = 1
            this.handleLists()
        },
        //按部门查询
        handleLists (currentPage) {
            if (
                (this.options.keyword && !this.options.field) ||
                (!this.options.keyword && this.options.field)
            ) {
                this.$message.warning('关键词必须匹配搜索类型')
                return
            }
            //修改时间
            // let timeArr = this.options.editDate
            // if (timeArr && timeArr.length > 0) {
            //  this.options.update_time_start = timeArr[0] / 1000
            //  this.options.update_time_end = timeArr[1] / 1000
            // } else {
            //  this.options.update_time_start = ''
            //  this.options.update_time_end = ''
            // }
            let params = {}
            for (let i in this.options) {
                if (i !== 'editDate') {
                    params[i] = this.options[i]
                }
            }
            if (currentPage) {
                params.pageNo = currentPage
            }
            this.loading = true
            getAdminList(params).then(res => {
                this.loading = false
                if (res.code == '0') {
                    this.total = Number(res.data.total)
                    this.dataSource = res.data.list
                } else {
                    this.$message(res.message)
                }
            })
        },
        handlePublicLine () {
            getDepartmentPublic().then(res => {
                if (res.code == '0') {
                    let data = res.data
                    if (data.department_ids && data.department_ids.length > 0) {
                        this.options.department_id = data.department_ids
                        this.defaultIds = data.department_ids
                        // this.setDisabled(data.outline, data.department_ids.length);
                    }
                    this.departmentOpt = data.outline
                    this.handleLists()
                } else {
                    this.$message(res.message)
                }
            }).catch(() => {
                this.handleLists()
            })
        },
        setDisabled (arr, times) {
            if (times > 1) {
                arr[0].disabled = true
                this.setDisabled(arr[0].children, --times)
            }
            return
        },
        handleCurrentChange (currentPage) {
            this.handleLists(currentPage)
        },
        handleExpandChange () {

        },
        addOption () { },

        /**
         * @description 新增渠道权限 - 获取渠道列表
         */
        async getPermissionChannel () {
            let website_code = getCookie('site_group_code'),
                params = {
                    website_code: website_code
                },
                res = await getPermissionChannel(params)
            this.permissionChannelList = res.data.permissions
            this.website_name = res.data.name;
        },

        /**
         * @description 新增渠道权限
         *	place : home 首页，special 活动页
         */
        newChannelPermission (row,place) {
            this.currentUserId = row.id
            this.permissionSiteCheckList = ['zf']
            this.getPermissionChannel()
            let checkedPermissChannel = (place === 'home' ? Object.keys(row.home_permissions) : Object.keys(row.special_permissions))
            if (checkedPermissChannel.length) {
                this.checkedPermissionChannel = checkedPermissChannel
            } else {
                this.checkedPermissionChannel = ['ZF']
            }
						this.permissionPlace = place
            this.permissionDialogVisible = true
        },

        handleCheckAllPermissionChannel(val) {
            this.checkedPermissionChannel = val ? Object.keys(this.permissionChannelList) : [];
            this.isIndeterminate = false;
        },

        handleCheckedPermissionChannelChange(value) {
            let checkedCount = value.length, permissionChannelArr = Object.keys(this.permissionChannelList)
            this.permissionCheckAll = checkedCount === permissionChannelArr.length;
            this.isIndeterminate = checkedCount > 0 && checkedCount < permissionChannelArr.length;
        },

        /**
         * @description 新增渠道权限提交
         */
        async handleSubmitPermissChannel () {
            if (this.permissionSiteCheckList.length == 0 || this.checkedPermissionChannel.length == 0) {
                this.$message({
                    type: 'warning',
                    message: '请选择站点和渠道'
                })
                return false
            }
            let params = {
                    user_id: this.currentUserId,
                    website_code: getCookie('site_group_code'),
                    permissions: JSON.stringify(this.checkedPermissionChannel),
										place: this.permissionPlace
                },
                res = await updatePermissChannel(params)
            if (res.code == 0) {
                this.permissionDialogVisible = false
                this.handleLists()
            }
        },

        //编辑
        editItem (row, type) {
            // this.dialogForm = row;
            this.dialogFormInfo.title = '用户编辑'
            this.dialogFormInfo.submitType = 'edit'
            this.childOption.sites = []
            if (type == 0) {
                this.dialogFormInfo.disabled = true
            } else {
                this.dialogFormInfo.disabled = false
            }
            getAdminInfo({ id: row.id }).then(res => {
                if (res.code == '0') {
                    let data = res.data
                    this.dialogForm = data.info
                    this.dialogVisible = true
                    this.childOption.status = data.info.status
                    this.childOption.is_super = data.info.is_super
                    this.defaultAdmin = data.info.is_super

                    let temp = [],
                        tempObj = {}
                    data.site.forEach(item => {
                        //角色选中
                        if (item.rolesSelected.length === item.roles.length) {
                            item.checkAll = true
                            item.checkPart = false
                        } else if (item.rolesSelected.length > 0 && item.rolesSelected.length < item.roles.length) {
                            item.checkAll = false
                            item.checkPart = true
                        } else {
                            item.checkAll = false
                            item.checkPart = false
                        }

                        if (item.selected) {
                            this.childOption.sites.push(item.site_code)
                        }
                        temp.push({
                            site_code: item.site_code,
                            has_auth: item.has_auth
                        })

                        let length = item.categorysSelected ? item.categorysSelected.length : 0
                        item.status = 0
                        if (length == 0) {
                            item.status = 0
                        } else if (length < item.categoryLength) {
                            item.status = 2
                        } else {
                            item.status = 1
                        }

                        item.topLevel = []
                        if (item.category) {
                            item.category.forEach(cat => {
                                item.topLevel.push(cat.id)
                            })
                        }

                        tempObj[item.site_code] = JSON.parse(JSON.stringify(item))
                    })

                    //data
                    this.siteOpt = tempObj
                    this.maxSiteArr = temp

                    //init
                    let siteLength = this.childOption.sites.length
                    if (siteLength > 0) {
                        this.current = this.childOption.sites[0]
                    }

                    if (siteLength == data.site.length) {
                        this.checkSite = true
                        this.checkSiteMid = false
                    } else if (siteLength < data.site.length && siteLength > 0) {
                        this.checkSiteMid = true
                        this.checkSite = false
                    } else {
                        this.checkSiteMid = false
                        this.checkSite = false
                    }

                } else {
                    this.$message(res.message)
                }
            })
        },
        delOne (id) {
            var _this = this
            this.$confirm('是否删除该项', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
            })
                .then(function () {
                    adminDel({ id: id }).then(() => {
                        _this.handleLists()
                    })
                })
                .catch(function () {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    })
                })
        },
        changeSites (val) {
            let length = val.length
            this.checkSite = length === this.maxSiteArr.length
            this.checkSiteMid = length > 0 && length < this.maxSiteArr.length
            this.current = val[0]
        },
        checkAll (val, item) {
            //增加has_auth判断
            let _this = this,siteOpt = this.siteOpt
            let newSitesArr = []
            if (val) {
                this.maxSiteArr.forEach(site => {
                    if (site.has_auth == 1 || site.has_auth == 0 && siteOpt[site.site_code].selected == true) {
                        newSitesArr.push(site.site_code)
                    }
                })
                _this.childOption.sites = newSitesArr
                // this.childOption.sites = this.maxSiteArr
                this.current = this.childOption.sites[0]
            } else {
                for (const current in item) {
                    if (item[current].has_auth == 0 && item[current].selected) {
                        newSitesArr.push(item[current].site_code)
                    }

                }

                this.childOption.sites = newSitesArr
                // this.childOption.sites = []
            }
            this.checkSiteMid = false
        },
        checkAllCat (val, refName, data) {
            if (val) {
                data.status = 1
                this.$refs[refName][0].setCheckedKeys(data.topLevel)
            } else {
                data.status = 0
                this.$refs[refName][0].setCheckedKeys([])
            }
        },
        checkAllRole (val, item) {
            //增加has_auth 判断
            const newRoleArr = []
            if (val) {
                if (item.roles.length > 0) {
                    item.roles.forEach(role => {
                        if (role.has_auth == 1 || role.has_auth == 0 && role.selected == 1) {
                            newRoleArr.push(role.role_id)
                        }
                    })
                } else if (item.roles.length == undefined) {
                    for (var role in item.roles) {
                        if (item.roles[role].has_auth == 1 || item.roles[role].has_auth == 0 && item.roles[role].selected == 1) {
                            newRoleArr.push(item.roles[role].role_id)
                        }

                    }
                }
                item.rolesSelected = newRoleArr

            } else {
                for (var roleItem in item.roles) {
                    if (item.roles[roleItem].has_auth == 0 && item.roles[roleItem].selected) {
                        newRoleArr.push(item.roles[roleItem].role_id)
                    }

                }
                item.rolesSelected = newRoleArr
            }
            item.checkPart = false
        },
        changeRoles (val, item) {
            let length = val.length
            item.checkAll = length === item.roles.length
            item.checkPart = length > 0 && length < item.roles.length
        },
        submitForm (formName) {
            this.$refs[formName].validate(valid => {
                if (valid) {
                    var params = {
                        id: this.dialogForm.id,
                        is_super: this.childOption.is_super,
                        is_leader: this.dialogForm.is_leader,
                        status: this.dialogForm.status,
                    }
                    if (params.is_super == '0') {
                        let temp = []
                        this.childOption.sites.forEach(code => {
                            let item = this.siteOpt[code]
                            let ids = []
                            let list = []
                            if (item.categorys) {
                                list = this.refs['tree_' + item.site_code][0].getCheckedNodes()
                                list.forEach(node => {
                                    if (!node.parentNode && node.selected) {
                                        ids.push(node.id)
                                    }
                                })
                            }

                            temp.push({
                                site_code: code,
                                categorys: ids,
                                roles: this.siteOpt[code].rolesSelected
                            })
                        })
                        params.sites = temp
                    }
                    this.submitLoading = true
                    adminEdit(params).then(res => {
                        this.submitLoading = false
                        if (res.code == 0) {
                            this.$message({
                                message: '编辑完成',
                                type: 'success',
                            })
                            this.closeDialog(formName)
                            this.handleLists()
                        } else {
                            this.$message(res.message)
                        }
                    }).catch(() => {
                        this.submitLoading = false
                    })
                    // this.resetFields(formName)
                }
            })
        },
        resetForm (formName) {
            this.resetFields(formName)
            this.closeDialog(formName)
        },
        resetFields (formName) {
            this.$refs[formName].resetFields()
        },
        resetSearch () {
            // this.$refs['searchForm'].resetFields()
            this.options = {
                pageNo: 1,
                pageSize: 10,
                field: '',
                keyword: '',
                department_id: this.defaultIds
            }
        },
        closeDialog (formName) {
            if (formName == 'dialogForm') {
                this.dialogVisible = false
            }
        },
        /* dialog关闭 */
        dialogFormClose () {
            this.resetFields('dialogForm')
        }
    }
}
</script>

<style lang="less" scoped>
.el-select {
  width: 200px;
}

.department-tree {
  width: 500px;
}

.user-edit {
  .department-site {
    border: 1px solid #ccc;
    padding: 30px;
    position: relative;
    margin-top: 30px;
    margin-bottom: 30px;
    .title {
      position: absolute;
      line-height: 36px;
      top: -18px;
      left: 50px;
      background: #fff;
      padding: 0 15px;
    }
    .el-checkbox-group {
      &.site {
        .el-checkbox {
          margin-left: 60px;
        }
      }
    }
  }
}
	.generate-check-item{
		width: 100px;
		margin-left: 0 !important;
		padding: 0 6px;
	}
</style>
<style>
	.channel_text .cell{
		white-space: nowrap;
	}
</style>
