<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
			<el-row :gutter="20">
				<el-col :span="12">
					<el-form-item label="默认商品显示数量">
						<span class="design-gray-tip">(建议输入4的倍数，且不超过100)</span>
						<el-input type="number" v-model="formData.defaultGoodsCount"></el-input>
					</el-form-item>
				</el-col>
				<el-col :span="12">
					<el-form-item label="固定商品显示数量">
						<span class="design-gray-tip">(建议输入4的倍数，且不超过100)</span>
						<el-input type="number" v-model="formData.fixedGoodsCount"></el-input>
					</el-form-item>
				</el-col>
			</el-row>
			<el-form-item label="SKU">
				<goods-sku :elObj="{type:'textarea',name:'goodsSKU',model:formData.goodsSKU}"></goods-sku>
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
				formData: {"goodsSKU":'',"defaultGoodsCount":"","fixedGoodsCount":""},
			};
		},
		created () {
		}
	};
</script>

<style lang="less" scoped>
</style>

