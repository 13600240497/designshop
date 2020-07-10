<template>
	<site-layout>

	</site-layout>
</template>

<script>
import siteLayout from './layouts/Layout.vue'

export default {
	components: { siteLayout },
	data () {
		return {
			dataLists: [],
			searchInfo: {},
			pageInfo: {
				pageSize: 20,
				pageNo: 1,
				totalCount: 0
			},
			loading: false
		}
	},
	created () {

	},
	methods: {

	}
}
</script>

<style lang="less" scoped>
</style>

