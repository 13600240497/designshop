<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
			<el-form-item label="商品数据来源">
				<el-radio-group v-model="formData.goodsDataSource" class="element-full" @change="handleRadioChange">
					<el-radio label="1">调用ZAFUL网站新品榜</el-radio>
					<el-radio label="2">调用ZAFUL网站热卖榜</el-radio>
					<el-radio label="3">调用ZAFUL网站折扣榜</el-radio>
				</el-radio-group>
			</el-form-item>
			<el-row :gutter="20">
				<el-col :span="12">
					<el-form-item label="商品分类ID	">
						<goods-sku :elObj="{type:'number',name:'cateId',model:formData.cateId,
						api:'getrankdetail',
						content:{'type': Number(formData.goodsDataSource),'lang':'en','cateid':Number(formData.cateId),'pageno':1,'pagesize':20}}"
											 @apiCallback=apiCallback ref="goods_sku"></goods-sku>
					</el-form-item>
				</el-col>
			</el-row>

		</el-form>
	</ui-layout>
</template>

<script>
	import extend from '../common/data_extend';

	export default {
		extends: extend,
		data () {
			return {
				formData: { 'goodsDataSource': '1', 'cateId': '' },
			};
		},
		created () {

		},
		mounted () {

		},
		methods: {
			handleRadioChange () {
				this.$refs.goods_sku.triggerBlur();
			},
			apiCallback () {
				this.formData.cateId = '';
			}
		}
	};
</script>

<style lang="less" scoped>
</style>

