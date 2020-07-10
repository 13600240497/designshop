<template>
  <el-dialog title="模板编辑" :visible.sync="publicData.dialogVisible" @close="resetForm('form')">
    <el-form :model="form" :rules="rules" ref="form" label-width="100px">
      <el-form-item label="模板名称" prop="name">
        <el-input v-model="form.name"></el-input>
      </el-form-item>
      <el-form-item label="模板类型">
        <el-select v-model="form.view_type" placeholder="请选择">
          <el-option label="公有模板" value="1">公有模板</el-option>
          <el-option label="私有模板" value="2">私有模板</el-option>
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button @click="resetForm('form')" size="small">取消</el-button>
        <el-button type="primary" @click="submitForm('form')" size="small" :loading="publicData.submitLoading">确定</el-button>
      </el-form-item>
    </el-form>
  </el-dialog>
</template>

<script>
import { getCookie  } from '../../plugin/mUtils';
import { editPageUiTplList, DL_editPageUiTplList } from '../../plugin/api';

export default {
  /**
   * @description props
   * @param { Array } form 模板编辑表单数据
   */
  props: {
    form: {
      type: Object
    },
    publicData: {
      type: Object
    },
    getPageUiTemplateList: {
      type: Function
    }
  },
  data() {
    return {
      rules: {
				name: [
					{ required: true, message: '请输入名称', trigger: 'change' }
				]
			},
    }
  },
  methods: {
    resetForm (formName) {
			this.$refs[formName].resetFields()
			this.publicData.dialogVisible = false
			this.publicData.submitLoading = false
			this.publicData.uploadedImg = ''
			this.publicData.fileList = []
    },
    /**
     * @description 编辑组件模板列表
     * @param { String } formName 表单提交模块名字
     * @param { Int } id 组件模板ID
     * @param { String } name 
     * @param { Int } view_type 
     */
    async submitForm (formName) {
      this.publicData.submitLoading = true;
      
      /**
       * 数据校验
       * @param {Boolean} valid [true/false]
       */
			this.$refs[formName].validate(async (valid) => {
				if (valid) {
					let params = {
              id: this.form.id,
              name: this.form.name,
              view_type: this.form.view_type
          };

          let res;
          let site = getCookie('site_group_code');
          // 判断站点
          switch (site) {
            case 'rg':
                res = await editPageUiTplList(params);
                break;
            case 'zf':
                res = await editPageUiTplList(params);
                break;
            default:
                res = await DL_editPageUiTplList(params);
                break;
          }

          // 判断接口是否正确
					if (res.code == 0) {
						this.resetForm('form');
						this.getPageUiTemplateList();
					} else {
						this.publicData.submitLoading = false;
					}
				} else {
					this.publicData.submitLoading = false;
				}
			});
		}
  }
}
</script>

