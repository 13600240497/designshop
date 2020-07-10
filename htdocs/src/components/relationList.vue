<template>
  <site-layout>
    <el-row>
      <el-col :span="18">
        <el-form :inline="true" :model="addForm">
          <el-form-item label="PC端组件名称">
            <el-input v-model="searchPC.pc_component_name" placeholder="请输入PC端组件名称"></el-input>
          </el-form-item>
          <el-form-item label="M端组件名称">
            <el-input v-model="searchM.wap_component_name" placeholder="请输入M端组件名称"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="doSearch">搜索</el-button>
          </el-form-item>
        </el-form>
      </el-col>
      <el-col :span="6" class="text-right">
        <el-button type="danger" icon="el-icon-plus" @click="addRelation()">新增</el-button>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="24">
        <el-table :data="relationList" style="width: 100%">
          <el-table-column prop="id" label="ID"></el-table-column>
          <el-table-column prop="component_name" label="PC端组件名称"></el-table-column>
          <el-table-column prop="tpl_name" label="PC端模板名称"></el-table-column>
          <el-table-column prop="relation_component_name" label="M端组件名称"></el-table-column>
          <el-table-column prop="relation_tpl_name" label="M端模板名称"></el-table-column>
          <el-table-column label="创建时间">
						<template slot-scope="scope">
							<span>{{ scope.row.create_time | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
          <el-table-column label="更新时间">
						<template slot-scope="scope">
							<span>{{ scope.row.update_time | moment('YYYY-MM-DD HH:mm:ss') }}</span>
						</template>
					</el-table-column>
          <el-table-column label="操作">
            <template slot-scope="scope">
              <el-button type="primary" size="small" @click="editRelation(scope.row)">编辑</el-button>
							<el-button type="primary" size="small" @click="disableEnable(scope.row.id, 1)" v-if="scope.row.status == 0">启用</el-button>
              <el-button type="danger" size="small" @click="disableEnable(scope.row.id, 0)" v-if="scope.row.status == 1">禁用</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-col>
    </el-row>

    <el-row v-if="total > 10">
      <el-col :span="24" class="text-right">
        <el-pagination layout="prev, pager, next" page-size="10" :total="total" @current-change="handleCurrentChange"></el-pagination>
      </el-col>
    </el-row>

    <el-dialog title="新增组件关系" :visible.sync="addVisible">
      <el-form label-width="80px" :model="addForm" :rules="rules" ref="addForm">
        <el-row>
          <el-col :span="10">
            <el-form-item label="PC组件" prop="namePC">
              <el-select placeholder="请选择PC端的组件" v-model="addForm.namePC" @change="getTplsPC">
                <el-option v-for="item in componentListPC" :key="item.id" :value="item.id" :label="item.name"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="模板" prop="tplPC">
              <el-select placeholder="请选择PC端的组件模板" v-model="addForm.tplPC">
                <el-option v-for="item in tplListPC" :key="item.id" :value="item.id" :label="item.name"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="4">
            <p>至M端</p>
            <p><i class="fa fa-long-arrow-right" style="font-size:36px; margin-left:5px"></i></p>
          </el-col>
          <el-col :span="10">
            <el-form-item label="M组件" prop="nameM">
              <el-select placeholder="请选择M端的组件" v-model="addForm.nameM" @change="getTplsM">
                <el-option v-for="item in componentListM" :key="item.id" :value="item.id" :label="item.name"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="模板" prop="tplM">
              <el-select placeholder="请选择M端的组件模板" v-model="addForm.tplM">
                <el-option v-for="item in tplListM" :key="item.id" :value="item.id" :label="item.name"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <div class="el-form-item__content" style="margin-left: 75%; margin-top：20px">
          <button type="button" class="el-button el-button--small" @click="resetForm('addForm')">取消</button> 
          <button type="button" class="el-button el-button--primary el-button--small" @click="addSubmit('addForm')">确定</button>
        </div>
      </el-form>
    </el-dialog>

    <el-dialog title="编辑组件关系" :visible.sync="editVisible">
      <el-form label-width="80px" :model="editForm" :rules="rules" ref="editForm">
        <el-row>
          <el-col :span="10">
            <el-form-item label="PC组件" prop="namePC">
              <el-select placeholder="请选择PC端的组件" v-model="editForm.namePC" @change="getTplsPC">
                <el-option v-for="item in componentListPC" :key="item.id" :value="item.id" :label="item.name"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="模板" prop="tplPC">
              <el-select placeholder="请选择PC端的组件模板" v-model="editForm.tplPC">
                <el-option v-for="item in tplListPC" :key="item.id" :value="item.id" :label="item.name"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="4">
            <p>至M端</p>
            <p><i class="fa fa-long-arrow-right" style="font-size:36px; margin-left:5px"></i></p>
          </el-col>
          <el-col :span="10">
            <el-form-item label="M组件" prop="nameM">
              <el-select placeholder="请选择M端的组件" v-model="editForm.nameM" @change="getTplsM">
                <el-option v-for="item in componentListM" :key="item.id" :value="item.id" :label="item.name"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="模板" prop="tplM">
              <el-select placeholder="请选择M端的组件模板" v-model="editForm.tplM">
                <el-option v-for="item in tplListM" :key="item.id" :value="item.id" :label="item.name"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-form-item style="margin-left:610px;">
            <template slot-scope="scope">
              <el-button type="normal" size="small" @click="resetForm('editForm')">取消</el-button>
              <el-button type="primary" size="small" @click="editSubmit('editForm')">确定</el-button>
            </template>   
          </el-form-item>
        </el-row> 
      </el-form>
    </el-dialog>
  </site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'
import { getRelationList, getComponentList, addRelationList, editRelationList, disableRelationList } from '../plugin/api'

export default {
	components: { siteLayout },
	data () {
		return {
			page: 1,
			relationList: [],
			currentPage: 1,
			addVisible: false,
			editVisible: false,
			total: '',
			addForm: {
				id: '',
				idPC: '',
				namePC: '',
				tplListPC: [],
				idM: '',
				nameM: '',
				tplListM:[],
				range: '',
				status: 1,
				tplPC: '',
				tplM: ''
			},
			editForm: {
				id: '',
				idPC: '',
				namePC: '',
				tplListPC: [],
				idM: '',
				nameM: '',
				tplListM:[],
				range: '',
				status: 1,
				tplPC: '',
				tplM: ''
			},
			componentListPC: [],
			componentListM: [],
			tplListPC: [],
			tplListM: [],
			rules: {
				namePC: [
					{ required: true, message: '请选择组件', trigger: 'change' }
				],
				tplPC: [
					{ required: true, message: '请选择组件模板', trigger: 'change' }
				],
				nameM: [
					{ required: true, message: '请选择组件', trigger: 'change' }
				],
				tplM: [
					{ required: true, message: '请选择组件模板', trigger: 'change' }
				]
			},
			searchPC: {
				pc_component_name: '',
				type: 1
			},
			searchM: {
				wap_component_name: '',
				type: 2
			}
		}
	},

	methods: {
		async getRelationList () {
			let params = {
				pageNo: this.currentPage,
				pageSize: 100,
				pc_component_name: this.searchPC.pc_component_name,
				wap_component_name: this.searchM.wap_component_name
			}
			let res = await getRelationList(params)

			this.relationList = res.data.list
			this.total = res.data.totalCount
		},
		async getComponents () {
			let params = {
				key:'',
				pageNo: 1,
				pageSize: 999,
				type: 2,
				status: 1,
				place: 1
			}
			let res = await getComponentList(params)
			let PCComponents = []
			let MComponents = []
      
			res.data.list.forEach(function(element) {
				if (element.range == 1) {
					PCComponents.push(element)
				} else if (element.range == 2) {
					MComponents.push(element)
				}
			})

			this.componentListPC = PCComponents
			this.componentListM = MComponents
		},
		getTplsPC (val) {
			this.componentListPC.forEach(item => {
				if (item.id == val) {
					this.tplListPC = item.tplList
					this.idPC = item.tpl_id

					this.addForm.tplPC = ''
					this.editForm.tplPC = ''
				}
			})
		},
		getTplsM (val) {
			this.componentListM.forEach(item => {
				if (item.id == val) {
					this.tplListM = item.tplList
					this.idM = item.tpl_id

					this.addForm.tplM = ''
					this.editForm.tplM = ''
				}
			})
		},
		addRelation () {
			this.addForm.namePC = ''
			this.addForm.nameM = ''
			this.addForm.tplPC = ''
			this.addForm.tplM = ''

			this.addVisible = true
		},
		addSubmit (formName) {
			this.$refs[formName].validate(async (valid) => {
				if (valid) {
					let params = {
						tpl_id: this.addForm.tplPC,
						relation_tpl_id: this.addForm.tplM
					}
					let res = await addRelationList(params)
					if (res.code == 0) {
						this.addVisible = false
						this.getRelationList()
					} else {
						this.$message.error(res.message)
					}
				} else {
					return false
				}
			})
		},
		editRelation (row) {
			this.getTplsPC(row.component_id)
			this.getTplsM(row.relation_component_id)
      
			this.editForm.id = Number(row.id)
			this.editForm.namePC = Number(row.component_id)
			this.editForm.tplPC = Number(row.tpl_id)
			this.editForm.nameM = Number(row.relation_component_id)
			this.editForm.tplM = Number(row.relation_tpl_id)

			this.editVisible = true
		},
		editSubmit (formName) {
			this.$refs[formName].validate(async (valid) => {
				if (valid) {
					let params = {
						id: this.editForm.id,
						tpl_id: this.editForm.tplPC,
						relation_tpl_id: this.editForm.tplM
					}
					let res = await editRelationList(params)

					if (res.code == 0) {
						this.editVisible = false
						this.getRelationList()
					} else {
						this.$message.error(res.message)
					}
				} else {
					return false
				}
			})
		},
		resetForm(formName) {
			this.$refs[formName].resetFields()

			if (formName == 'addForm') {
				this.addVisible = false
			} else if (formName == 'editForm') {
				this.editVisible = false
			}
		},
		doSearch () {
			this.currentPage = 1
			this.getRelationList()
		},
		handleCurrentChange(currentPage) {
			this.currentPage = currentPage
			this.getComponentList()
		},
		async disableEnable (id, status) {
			let params = {
				id: id,
				status: status
			}
			let res = await disableRelationList(params)

			if (res.code == 0) {
				this.getRelationList()
			} else {
				this.$message.error(res.message)
			}
		}
	},
	created () {
		this.getRelationList()
		this.getComponents()
	},
}
</script>
