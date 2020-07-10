<template>
    <el-form :inline="true" :model="formdata">

        <el-form-item label="组件编码">
            <el-input
                v-model="formdata.key"
                maxlength="7"
                @keyup.enter.native="handleSearch"></el-input>
        </el-form-item>

        <el-form-item label="组件名称">
            <el-input
                v-model="formdata.word"
                @keyup.enter.native="handleSearch"></el-input>
        </el-form-item>

        <el-form-item label="启用状态">
            <el-select v-model="formdata.status" placeholder="请选择">
                <el-option label="所有" value=""></el-option>
                <el-option label="是" value="1"></el-option>
                <el-option label="否" value="0"></el-option>
            </el-select>
        </el-form-item>
        
        <el-form-item label="组件类型">
            <el-select v-model="formdata.type" placeholder="请选择">
            <el-option label="内容" value="2"></el-option>
            <el-option label="布局" value="1"></el-option>
            </el-select>
        </el-form-item>

        <el-form-item label="应用环境">
            <el-select v-model="formdata.place" placeholder="请选择">
                <el-option label="活动页" value="1"></el-option>
                <el-option label="首页" value="2"></el-option>
            </el-select>
        </el-form-item>

        <el-form-item label="应用站点">
            <el-select v-model="formdata.siteGroup" placeholder="请选择">  
                <el-option label="所有站点" value=""></el-option>
                <el-option
                    v-for="item in options.sites"
                    :label="item.name"
                    :value="item.code"
                    :key="item.code">
                </el-option>
            </el-select>
        </el-form-item>

        <el-form-item label="应用终端">
            <el-select v-model="formdata.range" placeholder="请选择">
                <el-option label="所有" value=""></el-option>
                <el-option label="桌面端" value="1"></el-option>
                <el-option label="移动端" value="2"></el-option>
                <el-option label="响应式" value="3"></el-option>
                <el-option label="原生App" value="4"></el-option>
            </el-select>
        </el-form-item>
        
        <el-button type="primary" @click="handleSearch">搜索</el-button>
        <el-button type="danger" icon="el-icon-plus" @click="handleAdd">新增</el-button>
    </el-form>
</template>

<script>
/**
 * $emit functions:
 * handleAdd 点击新增按钮的回调函数
 * handleConfirm 点击搜索的回调函数
 */
export default {
    props: ['data'],
    data () {
        return {
            formdata: {
				word: '', // 组件名
				type: '2', // 组件类型, 1=布局，2=内容
				place: '1', // 组件应用环境, 1=活动页，2=首页
				siteGroup: '', // 站点的code, [zf/rg/rw/dl/gb]
                key: '', // 组件唯一编码 U00000
                status: '1', // 启用状态，空=所有， 1=启用，0=下线,
                range: '' // 默认所有终端
            },
            options: {
                sites: [] // 站点简码列表
            }
        };
    },
    methods: {
        // 搜索按钮
        handleSearch () {
            this.$emit('handleSearch');
        },
        // 新增按钮
        handleAdd () {
            this.$emit('handleAdd');
        }
    },
    created () {
        // 获取站点编码列表
        this.options.sites = JSON.parse(localStorage.getItem("supportSites")).data;
    }
}
</script>

<style lang="less" scoped>
    .el-input {
        width: 194px;
    }
</style>
