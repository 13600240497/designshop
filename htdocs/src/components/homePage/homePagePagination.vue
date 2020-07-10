<template>
    <div>
        <el-row v-if="oListData.total > 10">
            <el-col :span="24" class="text-right geshop-article-page">
                <el-pagination layout="sizes, prev, pager, next"
                       :page-size="oListData.pageSize"
                       :total="oListData.total"
                       :current-page.sync="oListData.currentPage"
                       @current-change="handleCurrentChange"
                       @size-change="handleSizeChange"></el-pagination>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    export default {
        name: 'homePagePagination',
        props: {
            oListData: {
                type: Object
            },
            getIndexList: {
                type: Function
            }
        },
        data () {
            return {}
        },
        methods: {
            handleCurrentChange (currentPage) {
                this.oListData.currentPage = currentPage
                this.getIndexList()
            },
            handleSizeChange(pageSize) {
                this.oListData.pageSize = pageSize
                this.oListData.currentPage = 1
                this.getIndexList()
            }
        }
    }
</script>

<style scoped>

</style>