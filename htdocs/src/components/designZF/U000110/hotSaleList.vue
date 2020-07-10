<!--排行榜-->
<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
			<el-row :gutter="20">
				<el-col :span="12">
					<el-form-item label="每个Tab显示商品数量">
						<span class="design-gray-tip">(最多显示100个)</span>
						<el-input type="number" v-model="formData.goodsNum"></el-input>
					</el-form-item>
				</el-col>
			</el-row>
			<el-row v-for="(item,index) in formDataOriginal.goodsIds" :key="index" :gutter="20">
				<el-col :span="12">
					<el-form-item :label="'Tab'+(index+1)+'分类'">
						<el-input v-model="item.category"
											v-valid:goodsIds.category="{'message':'请输入TAB分类名称','dataFrom':'formDataOriginal','structure':{'firstKey':'goodsIds','index':index,'secondKey':'cateid'}}"></el-input>
					</el-form-item>
				</el-col>
				<el-col :span="12">
					<el-form-item label="商品数据ID">
						<el-input v-model="item.cateid"></el-input>
					</el-form-item>
				</el-col>
				<el-col :span="12" style="margin-top:5px;">
					<el-form-item :label="'Tab'+(index+1)+'默认状态图标'">
						<span class="design-gray-tip">(请上传图片尺寸：30x30px)</span>
						<el-input v-model="item.defaultIco">
						</el-input>
					</el-form-item>
				</el-col>
				<el-col :span="12" style="margin-top:5px;">
					<el-form-item :label="'Tab'+(index+1)+'选中状态图标'">
						<span class="design-gray-tip">(请上传图片尺寸：30x30px)</span>
						<el-input v-model="item.checkIco"></el-input>
					</el-form-item>
				</el-col>
			</el-row>
			<button class="geshop-design-ui_hide" v-checkSubmit ref="submit"></button>
		</el-form>
	</ui-layout>
</template>

<script>
	import uiLayout from '../common/ui_layout.vue';
	import goodsSku from '../common/goods_sku.vue';
	import {tabCountGenerate} from '../../../plugin/mUtils'

	export default {
		components: {uiLayout, goodsSku},
		props: ['componentIndex', 'formDataCurrent', 'component_id', 'tab_count'],
		data() {
			return {
				formData: {'goodsNum': '', 'goodsIds': ''},
				formDataOriginal: {'goodsIds': [{'category': '', 'cateid': '', 'defaultIco': '', 'checkIco': ''}],},
				validShow: false,
				//validStart 是否开始校验证
				validStart: false,
				//是否完成校验 '0','1'
				validStatus: '0',
			};
		},
		created() {

		},
		mounted() {
			let formDataCurrent = this.formDataCurrent;
			if (formDataCurrent) {
				if (formDataCurrent.goodsIds) {
					this.formDataOriginal['goodsIds'] = JSON.parse(formDataCurrent.goodsIds)
				}
				this.formData = formDataCurrent;
			} else if (this.tab_count && this.tab_count > 1) {
				this.formDataOriginal.goodsIds = tabCountGenerate(this.formDataOriginal.goodsIds, this.tab_count)
			}
		},
		methods: {
			submit() {
				this.formData.goodsIds = JSON.stringify(this.formDataOriginal.goodsIds)
			}
		}
	};
</script>

<style lang="less" scoped>
</style>

