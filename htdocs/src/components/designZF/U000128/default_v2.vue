<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
			<el-row :gutter="20">
				<el-col :span="12">
					<el-form-item label="每个Tab显示商品数量">
						<el-input type="number" v-model="formData.productsNum"></el-input>
					</el-form-item>
				</el-col>
			</el-row>
			<el-row v-for="(item,index) in formDataOriginal.goodsId" :key="index">
				<el-col :span="12">
					<el-form-item :label="'满赠数据'+(index+1)+' ID'">
						<goods-sku :elObj="{type:'number',name:'goodsId',model:item,
					structure:{dataName:'formDataOriginal','target':'goodsId','index':index},
				api:'fullgiftlist',
				content:{'lang':'en','activityid':item,'pageno':1,'pagesize':20}}" @apiCallback="apiCallback(index)"></goods-sku>
					</el-form-item>
				</el-col>
			</el-row>
			<!--<button class="geshop-design-ui_hide" v-checkSubmit ref="submit"></button>-->
		</el-form>
	</ui-layout>
</template>

<script>
	import uiLayout from '../common/ui_layout.vue';
	import goodsSku from '../common/goods_sku.vue';
	import { tabCountGenerate } from '../../../plugin/mUtils'

	export default {
		components: { uiLayout, goodsSku },
		props: ['componentIndex', 'formDataCurrent', 'component_id', 'tab_count'],
		data () {
			return {
				formData: { 'productsNum': '', 'goodsID':'' },
				formDataOriginal:{'goodsId': [''],},
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
				this.formData.goodsID = JSON.stringify(this.formDataOriginal.goodsId)
			},
			apiCallback(index){
				this.$set(this.formDataOriginal['goodsId'],index,'')
			}
		}
	};
</script>

<style lang="less" scoped>
</style>

