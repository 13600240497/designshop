<!--加价购-->
<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
			<el-row :gutter="20">
				<el-col :span="12">
					<el-form-item label="每个Tab显示商品数量">
						<span class="design-gray-tip">(建议输入4的倍数，且不超过100)</span>
						<el-input type="number" v-model="formData.productsNum"></el-input>
					</el-form-item>
				</el-col>
			</el-row>
			<el-row v-for="(item,index) in formDataOriginal.goodsId" :key="index" :gutter="20">
				<el-col :span="12">
					<el-form-item :label="'分类'+(index+1)">
						<el-input v-model="item.name" v-valid:goodsId.name="{'message':'请输入分类名称','dataFrom':'formDataOriginal','structure':{'firstKey':'goodsId','index':index,'secondKey':'ids'}}"></el-input>
					</el-form-item>
				</el-col>
				<el-col :span="12">
					<el-form-item label="商品数据ID">
						<goods-sku :elObj="{type:'number',name:'goodsId',model:item.ids,
					structure:{dataName:'formDataOriginal','target':'goodsId','index':index,'name':'ids'},
				api:'increasebuylist',
				content:{'lang':'en','activityid':item.ids,'pageno':1,'pagesize':20}}" @apiCallback="apiCallback(index)"></goods-sku>
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
		components: { uiLayout, goodsSku },
		props: ['componentIndex', 'formDataCurrent', 'component_id', 'tab_count'],
		data () {
			return {
				formData: { 'productsNum': '', 'goodsID':'' },
				formDataOriginal:{'goodsId': [{'ids':'','name':''}],},
				//validStart 是否开始校验证
				validStart: false,
				//是否完成校验 '0','1'
				validStatus: '0',
				pageInfo: {
					submitLoading: false
				},
			};
		},
		created () {

		},
		mounted () {
			let formDataCurrent = this.formDataCurrent;
			if (formDataCurrent) {
				if(formDataCurrent.goodsID){
					this.formDataOriginal['goodsId'] = JSON.parse(formDataCurrent.goodsID)
				}
				this.formData = formDataCurrent;
			} else if (this.tab_count && this.tab_count > 1) {
				this.formDataOriginal.goodsId = tabCountGenerate(this.formDataOriginal.goodsId,this.tab_count)
			}
		},
		methods: {
			submit(){
				this.formData.goodsID = JSON.stringify(this.formDataOriginal.goodsId.filter(item => item.ids && item.name))
			},
			apiCallback(index){
				this.formDataOriginal['goodsId'][index]['ids'] = ''
				this.$forceUpdate()
			}
		}
	};
</script>

<style lang="less" scoped>
</style>

