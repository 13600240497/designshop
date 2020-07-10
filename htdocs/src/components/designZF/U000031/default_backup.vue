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
	import uiLayout from '../common/ui_layout.vue';
	import goodsSku from '../common/goods_sku.vue';

	export default {
		components: { uiLayout,goodsSku },
		props: ['componentIndex','formDataCurrent','component_id'],
		data () {
			return {
				formData: {"goodsSKU":''},
				pageInfo: {
					submitLoading: false
				}
			};
		},
		created () {
		},
		mounted () {
			let formDataCurrent = this.formDataCurrent;
			if (formDataCurrent) {
				this.formData = formDataCurrent;
			}
		},
		methods: {}
	};
</script>

<style lang="less" scoped>
</style>

