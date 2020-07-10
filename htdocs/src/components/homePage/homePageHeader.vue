<template>
    <div :class="prfClass">
        <el-row :span="24" class="geshop-index-tit">
            <span class="geshop-index-title">首页管理</span>
        </el-row>
        <el-row class="geshop-index-btn">
            <el-col>
                <el-button class="geshop-index-btn-add" @click="handleAddHomeIndex">
                    <span class="icon-geshop-pack-up"></span>
                    <span class="geshop-icon-add-text">新增首页</span>
                </el-button>
            </el-col>
            <el-col v-if="oCommonData.site_code != 'dl'">
                <el-button class="geshop-index-btn-refresh" @click="handleRefreshHomePage">
                    <span class="icon-geshop-reset"></span>
                    <span class="geshop-icon-refresh-text">一键刷新头尾部</span>
                </el-button>
            </el-col>
            <el-col>
                <el-input class="geshop-index-btn-search" v-model="searchWord" placeholder="请输入首页名称" @change="handleDoSearch">
                    <i slot="suffix" class="geshop-icon-search-gray"></i>
                </el-input>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    export default {
        name: 'homePageHeader',
        props: {
            /**
             * 新增首页
             * **/
            addHomeIndex: {
                type: Function
            },
            oCommonData: {
                type: Object
            },
            refreshHomePage: {
                type: Function
            }
        },
        data () {
            return {
                prfClass : 'home-page-header',
                searchWord: ''
            }
        },
        methods: {
            handleAddHomeIndex () {
                this.addHomeIndex();
            },
            handleRefreshHomePage () {
                this.refreshHomePage();
            },
            handleDoSearch () {
                this.$emit('doSearch', this.searchWord);
            }
        }
    }
</script>

<style scoped>

</style>