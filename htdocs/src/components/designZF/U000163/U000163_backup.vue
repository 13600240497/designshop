<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
			<el-row>
				<el-col :span="12">
					<el-form-item label="商品SKU显示数量">
						<span class="design-gray-tip">(建议输入4的倍数，且不超过100)</span>
						<!--<el-input type="number" v-model="formData.skuLimit" v-valid:skuLimit.goodsSKU="{'message':'请补充商品SKU显示数量'}"></el-input>-->
						<el-input type="number" v-model="formData.skuLimit">
						</el-input>
						<small class="design-error-tip" v-if="validShow && !validList.skuLimit">请补充商品SKU显示数量</small>
					</el-form-item>
				</el-col>
			</el-row>
			<el-form-item label="SKU">
				<el-input type="textarea" v-model="formData.goodsSKU"></el-input>
			</el-form-item>
		</el-form>
	</ui-layout>
</template>

<script>
	import uiLayout from '../common/ui_layout.vue';

	export default {
		components: { uiLayout },
		props: ['componentIndex','formDataCurrent','component_id'],
		data () {
			return {
				formData: { "goodsSKU": '', "skuLimit": '' },
				pageInfo: {
					submitLoading: false
				},
				validShow: false,
				validList: {
					skuLimit: false
				}
			};
		},
		created () {

		},
		watch:{
			'formData.skuLimit':function(newVal){
				this.formValid()
			}
		},
		mounted () {
			let formDataCurrent = this.formDataCurrent;
			if (formDataCurrent) {
				this.formData = formDataCurrent;
			}
		},
		methods: {
			formValid () {
				let formData = this.formData;
				this.validList.skuLimit = !(formData.goodsSKU && !formData.skuLimit);
				const validList = this.validList;
				var validArr = Object.getOwnPropertyNames(validList);
				try {
					validArr.forEach(item => {
						if (validList[item] === false) {
							this.validShow = true
							throw new Error('EndIterative');
						}
					});
				} catch (e) {
					if (e.message !== 'EndIterative') throw e;
				}

			}
		}
	};
</script>

<style lang="less" scoped>
</style>

