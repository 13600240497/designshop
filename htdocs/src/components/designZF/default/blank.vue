<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
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
		methods: {}
	};
</script>

<style lang="less" scoped>
</style>

