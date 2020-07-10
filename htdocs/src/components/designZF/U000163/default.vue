<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
			<el-row>
				<el-col :span="12">
					<el-form-item label="商品SKU显示数量">
						<span class="design-gray-tip">(建议输入4的倍数，且不超过100)</span>
						<el-input type="number" v-model="formData.skuLimit"></el-input>
					</el-form-item>
				</el-col>
			</el-row>
			<el-form-item label="SKU">
				<goods-sku :elObj="{type:'textarea',name:'goodsSKU',model:formData.goodsSKU,
				api:'prepromotion',
				content:{'lang':'en','goodsSn':formData.goodsSKU}}"></goods-sku>
			</el-form-item>
		</el-form>
	</ui-layout>
</template>

<script>
	import extend from '../common/data_extend';

	export default {
		extends:extend,
		data () {
			return {
				formData: { "goodsSKU": '', "skuLimit": '' },
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

		},
		methods: {}
	};
</script>

<style lang="less" scoped>
</style>

