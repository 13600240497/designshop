<template>
	<ui-layout :componentIndex="componentIndex" :component_id="component_id">
		<el-form class="geshop-design-component-form">
			<el-form-item label="说明">
				<el-input></el-input>
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
				formData: {},
				pageInfo: {
					submitLoading: false
				}
			};
		},
		created () {

		},
		methods: {}
	};
</script>

<style lang="less" scoped>
</style>

